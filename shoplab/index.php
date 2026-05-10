<?php
session_start();

// ═══════════════════════════════════════════════════════════════════
// CONFIGURATION — set before deployment
// ═══════════════════════════════════════════════════════════════════
define('QUALTRICS_URL', 'https://REPLACE_WITH_YOUR_QUALTRICS_SURVEY_URL');

// ═══════════════════════════════════════════════════════════════════
// INTERNAL CONSTANTS
// ═══════════════════════════════════════════════════════════════════
define('DATA_DIR',     __DIR__ . '/data');
define('COUNTER_FILE', DATA_DIR . '/session_counter.txt');
define('EVENTS_FILE',  DATA_DIR . '/events.csv');

// ═══════════════════════════════════════════════════════════════════
// PRODUCTS  (ids 1–4 = target; 5–8 = fillers)
// ═══════════════════════════════════════════════════════════════════
$PRODUCTS = [
    1 => ['id'=>1,'name'=>'Wireless Earbuds Pro', 'price'=>39.99,'reviews'=>847, 'rating'=>4.4,'category'=>'Electronics',   'color'=>'#dbeafe','icon'=>'🎧','desc'=>'High-quality wireless earbuds with active noise cancellation and 24-hour battery life. Compatible with iOS and Android.'],
    2 => ['id'=>2,'name'=>'Bamboo Water Bottle',   'price'=>24.95,'reviews'=>312, 'rating'=>4.2,'category'=>'Home & Kitchen','color'=>'#dcfce7','icon'=>'🍶','desc'=>'Eco-friendly insulated bottle, BPA-free. Keeps drinks cold 24 h and hot 12 h. 500 ml, dishwasher safe.'],
    3 => ['id'=>3,'name'=>'Running Shoes X200',    'price'=>89.00,'reviews'=>1203,'rating'=>4.6,'category'=>'Sports',        'color'=>'#fef3c7','icon'=>'👟','desc'=>'Lightweight running shoes with responsive cushioning. Breathable mesh upper, durable rubber outsole. Sizes 36–46.'],
    4 => ['id'=>4,'name'=>'Bamboo Desk Organizer', 'price'=>32.50,'reviews'=>564, 'rating'=>4.3,'category'=>'Home & Kitchen','color'=>'#f5f0eb','icon'=>'🗂','desc'=>'Premium bamboo desk organizer with 6 compartments. Keeps pens, papers and accessories tidy. 30 × 20 × 8 cm.'],
    5 => ['id'=>5,'name'=>'Portable Phone Stand',  'price'=>12.99,'reviews'=>2341,'rating'=>4.7,'category'=>'Electronics',   'color'=>'#f3f4f6','icon'=>'📱','desc'=>'Adjustable aluminium phone stand compatible with all smartphones and tablets. Folds flat for easy storage.'],
    6 => ['id'=>6,'name'=>'Yoga Mat Premium',      'price'=>45.00,'reviews'=>789, 'rating'=>4.4,'category'=>'Sports',        'color'=>'#ede9fe','icon'=>'🧘','desc'=>'Non-slip 6 mm TPE yoga mat with alignment lines and carry strap. Suitable for all yoga styles. 183 × 61 cm.'],
    7 => ['id'=>7,'name'=>'Manual Coffee Grinder', 'price'=>28.75,'reviews'=>156, 'rating'=>4.1,'category'=>'Home & Kitchen','color'=>'#fef9c3','icon'=>'☕','desc'=>'Hand-operated ceramic burr grinder for fresh coffee anywhere. Adjustable coarseness settings, 25 g capacity.'],
    8 => ['id'=>8,'name'=>'Resistance Bands Set',  'price'=>19.99,'reviews'=>1876,'rating'=>4.6,'category'=>'Sports',        'color'=>'#fee2e2','icon'=>'💪','desc'=>'Set of 5 latex resistance bands (2–45 kg). Includes carry bag, door anchor and illustrated exercise guide.'],
];

// ═══════════════════════════════════════════════════════════════════
// SURVEY QUESTIONS (step 6)
// ═══════════════════════════════════════════════════════════════════
$SURVEY_QUESTIONS = [
    ['id'=>'F1', 'text'=>'How often do you shop online in general?',
     'options'=>['1'=>'Never','2'=>'Rarely','3'=>'Sometimes','4'=>'Often','5'=>'Always'],
     'is_val'=>false,'correct'=>null,'flag'=>null],
    ['id'=>'Q9', 'text'=>'For quality control purposes, please select "Never" for this item. How often do you return purchased items to an online store?',
     'options'=>['1'=>'Never','2'=>'Rarely','3'=>'Sometimes','4'=>'Often','5'=>'Always'],
     'is_val'=>true,'correct'=>'1','flag'=>null],
    ['id'=>'F2', 'text'=>'How satisfied are you with online shopping experiences in general?',
     'options'=>['1'=>'Very dissatisfied','2'=>'Dissatisfied','3'=>'Neutral','4'=>'Satisfied','5'=>'Very satisfied'],
     'is_val'=>false,'correct'=>null,'flag'=>null],
    ['id'=>'Q14','text'=>'To verify you are reading carefully: for this item, please select "Strongly agree". I read all instructions before completing a task.',
     'options'=>['1'=>'Strongly disagree','2'=>'Disagree','3'=>'Neutral','4'=>'Agree','5'=>'Strongly agree'],
     'is_val'=>true,'correct'=>'5','flag'=>null],
    ['id'=>'Q15','text'=>'I spend more than 30 hours per week on online shopping.',
     'options'=>['1'=>'Strongly disagree','2'=>'Disagree','3'=>'Neutral','4'=>'Agree','5'=>'Strongly agree'],
     'is_val'=>true,'correct'=>null,'flag'=>['4','5']],
];

// ═══════════════════════════════════════════════════════════════════
// TASK CONFIGURATION
// ═══════════════════════════════════════════════════════════════════
function getTaskCfg(int $step): array {
    return [
        1 => ['condition'=>'standard','product_id'=>1,'label'=>'Standard-A'],
        3 => ['condition'=>'standard','product_id'=>2,'label'=>'Standard-B'],
        5 => ['condition'=>'modified','product_id'=>3,'label'=>'Modified-A'],
        7 => ['condition'=>'modified','product_id'=>4,'label'=>'Modified-B'],
    ][$step];
}

// ═══════════════════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════════════════

function generateSessionId(): string {
    $n = 1;
    if (file_exists(COUNTER_FILE)) {
        $n = max(1, (int)trim(file_get_contents(COUNTER_FILE)) + 1);
    }
    file_put_contents(COUNTER_FILE, (string)$n, LOCK_EX);
    return 'S-2026-' . str_pad($n, 4, '0', STR_PAD_LEFT);
}

function nowIso(): string {
    $dt = new DateTime('now', new DateTimeZone('UTC'));
    $ms = str_pad((string)((int)(microtime(true) * 1000) % 1000), 3, '0', STR_PAD_LEFT);
    return $dt->format('Y-m-d\TH:i:s') . '.' . $ms . 'Z';
}

function msElapsed(): int {
    if (empty($_SESSION['start_microtime'])) return 0;
    return (int)round((microtime(true) - $_SESSION['start_microtime']) * 1000);
}

function appendEvent(array $d): void {
    $cols = ['session_id','timestamp_iso','ms_elapsed','event_type','step','condition',
             'target_product_id','clicked_element','click_x','click_y',
             'time_to_first_click_ms','is_unintended_interaction',
             'question_id','question_text','answer_value',
             'is_validation_question','validation_passed','completed'];
    $row = [];
    foreach ($cols as $c) { $row[] = $d[$c] ?? ''; }
    $new = !file_exists(EVENTS_FILE) || filesize(EVENTS_FILE) === 0;
    $fp  = fopen(EVENTS_FILE, 'a');
    if ($fp && flock($fp, LOCK_EX)) {
        if ($new) fputcsv($fp, $cols);
        fputcsv($fp, $row);
        flock($fp, LOCK_UN);
    }
    if ($fp) fclose($fp);
}

function base(array $extra = []): array {
    return array_merge([
        'session_id'    => $_SESSION['session_id'] ?? '',
        'timestamp_iso' => nowIso(),
        'ms_elapsed'    => msElapsed(),
    ], $extra);
}

function starsHtml(float $r): string {
    $full  = (int)floor($r);
    $half  = ($r - $full) >= 0.25 ? 1 : 0;
    $empty = 5 - $full - $half;
    return str_repeat('<span class="star full">★</span>', $full)
         . str_repeat('<span class="star half">★</span>', $half)
         . str_repeat('<span class="star empty">☆</span>', $empty);
}

function esc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function jsStr(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ═══════════════════════════════════════════════════════════════════
// INITIALISE SESSION ON FIRST VISIT
// Session ID is generated on step 0 so it is visible on the start page.
// ═══════════════════════════════════════════════════════════════════
if (empty($_SESSION['step'])) {
    $_SESSION['step'] = 0;
}
if ($_SESSION['step'] === 0 && empty($_SESSION['session_id'])) {
    $_SESSION['session_id'] = generateSessionId();
}

// ═══════════════════════════════════════════════════════════════════
// POST HANDLER
// ═══════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $cur    = (int)($_SESSION['step'] ?? 0);
    $intr   = !empty($_SESSION['show_interstitial']);

    $task_steps = [1, 3, 5, 7];

    // ── Start experiment ──────────────────────────────────────────
    if ($action === 'start' && $cur === 0 && !$intr) {
        $_SESSION['start_microtime']   = microtime(true);
        $_SESSION['step']              = 1;
        $_SESSION['show_interstitial'] = true;
        appendEvent(base(['event_type'=>'session_start','step'=>0]));
        header('Location: index.php'); exit;
    }

    // ── Continue past interstitial ────────────────────────────────
    if ($action === 'continue' && $intr) {
        $_SESSION['show_interstitial'] = false;
        unset($_SESSION['task_product_view']);   // reset any stale browsing state
        appendEvent(base(['event_type'=>'page_enter','step'=>$cur]));
        header('Location: index.php'); exit;
    }

    // ── View a product within a task step ────────────────────────
    if ($action === 'view_product' && !$intr && in_array($cur, $task_steps, true)) {
        $pid = (int)($_POST['product_id'] ?? 0);
        global $PRODUCTS;
        if (isset($PRODUCTS[$pid])) {
            $_SESSION['task_product_view'] = $pid;
        }
        header('Location: index.php'); exit;
    }

    // ── Back to catalog from product detail ───────────────────────
    if ($action === 'back_to_catalog' && !$intr && in_array($cur, $task_steps, true)) {
        unset($_SESSION['task_product_view']);
        header('Location: index.php'); exit;
    }

    // ── Advance after task or distraction (JS logs events first) ─
    if ($action === 'advance' && !$intr && $cur >= 1 && $cur <= 7) {
        unset($_SESSION['task_product_view']);
        unset($_SESSION['task_started'][$cur]);
        $next = $cur + 1;
        $_SESSION['step'] = $next;
        if ($next === 8) {
            $_SESSION['show_interstitial'] = false;
            appendEvent(base(['event_type'=>'session_complete','step'=>8,'completed'=>1]));
            appendEvent(base(['event_type'=>'page_enter',      'step'=>8]));
        } else {
            $_SESSION['show_interstitial'] = true;
        }
        header('Location: index.php'); exit;
    }

    // ── Survey submission ─────────────────────────────────────────
    if ($action === 'submit_survey' && !$intr && $cur === 6) {
        global $SURVEY_QUESTIONS;
        foreach ($SURVEY_QUESTIONS as $q) {
            $ans = $_POST['q_' . $q['id']] ?? '';
            $vp  = '';
            if ($q['is_val']) {
                if ($q['correct'] !== null) {
                    $vp = ($ans === $q['correct']) ? '1' : '0';
                } elseif ($q['flag'] !== null) {
                    $vp = in_array($ans, $q['flag'], true) ? '0' : '1';
                }
            }
            appendEvent(base([
                'event_type'             => 'survey_response',
                'step'                   => 6,
                'question_id'            => $q['id'],
                'question_text'          => $q['text'],
                'answer_value'           => $ans,
                'is_validation_question' => $q['is_val'] ? '1' : '0',
                'validation_passed'      => $vp,
            ]));
        }
        appendEvent(base(['event_type'=>'page_exit','step'=>6]));
        $_SESSION['step']              = 7;
        $_SESSION['show_interstitial'] = true;
        header('Location: index.php'); exit;
    }

    header('Location: index.php'); exit;
}

// ═══════════════════════════════════════════════════════════════════
// ROUTING
// ═══════════════════════════════════════════════════════════════════
$step = (int)($_SESSION['step'] ?? 0);
$intr = !empty($_SESSION['show_interstitial']);
$sid  = $_SESSION['session_id'] ?? '';

if ($intr && ($step < 1 || $step > 7)) {
    $_SESSION['show_interstitial'] = false;
    $intr = false;
}

// ═══════════════════════════════════════════════════════════════════
// CSS
// ═══════════════════════════════════════════════════════════════════
$CSS = <<<'CSS'
:root {
    --primary:   #2563eb;
    --primary-h: #1d4ed8;
    --orange:    #f97316;
    --orange-h:  #ea6c0e;
    --text:      #111827;
    --muted:     #6b7280;
    --border:    #e5e7eb;
    --bg:        #f9fafb;
    --white:     #ffffff;
    --radius:    8px;
    --sh:        0 1px 3px rgba(0,0,0,.10),0 1px 2px rgba(0,0,0,.06);
    --sh-md:     0 4px 6px rgba(0,0,0,.07),0 2px 4px rgba(0,0,0,.06);
    --sh-lg:     0 10px 25px rgba(0,0,0,.12);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);line-height:1.5;min-height:100vh}

/* Header */
.site-header{background:var(--white);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100;box-shadow:var(--sh)}
.hdr-inner{max-width:1200px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;gap:1.25rem;height:64px}
.logo{font-size:1.4rem;font-weight:800;color:var(--primary);text-decoration:none;flex-shrink:0;letter-spacing:-.5px}
.logo em{color:var(--text);font-style:normal}
.search-wrap{flex:1;position:relative;min-width:0}
.search-wrap input{width:100%;padding:.5rem 1rem .5rem 2.4rem;border:1px solid var(--border);border-radius:20px;font-size:.9rem;background:var(--bg);color:var(--text);outline:none}
.search-ico{position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.95rem;pointer-events:none}
.hdr-nav{display:flex;gap:1.25rem;list-style:none}
.hdr-nav a{color:var(--muted);text-decoration:none;font-size:.875rem;white-space:nowrap}
.hdr-nav a:hover{color:var(--text)}
.cart-btn{background:var(--primary);color:#fff;border:none;padding:.5rem .875rem;border-radius:var(--radius);font-size:.875rem;display:flex;align-items:center;gap:.35rem;font-weight:600;white-space:nowrap;cursor:default}

/* Category bar */
.cat-bar{background:var(--white);border-bottom:1px solid var(--border);overflow-x:auto}
.cat-bar ul{display:flex;list-style:none;max-width:1200px;margin:0 auto;padding:0 1.5rem}
.cat-bar a{display:block;padding:.65rem 1rem;font-size:.85rem;color:var(--muted);text-decoration:none;white-space:nowrap;border-bottom:2px solid transparent}
.cat-bar a:hover{color:var(--text);border-color:var(--primary)}

/* Task banner */
.task-banner{background:#eff6ff;border-left:4px solid var(--primary);margin:1.25rem auto;max-width:1200px;padding:.875rem 1.25rem;border-radius:0 var(--radius) var(--radius) 0}
.task-banner .tag{font-size:.7rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.06em}
.task-banner .msg{font-size:.975rem;font-weight:500;margin-top:.2rem;color:var(--text)}

/* Session ID badge — fixed top-right on experiment pages */
.session-badge{position:fixed;top:70px;right:.875rem;background:rgba(255,255,255,.92);border:1px solid var(--border);border-radius:6px;padding:.2rem .5rem;font-size:.68rem;color:var(--muted);z-index:300;backdrop-filter:blur(4px);box-shadow:var(--sh);line-height:1.4;pointer-events:none}

/* Catalog section */
.section{max-width:1200px;margin:0 auto;padding:1.5rem}
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem}
.section-title{font-size:1.05rem;font-weight:700}
.filter-bar{display:flex;gap:.5rem;flex-wrap:wrap}
.filter-chip{background:var(--white);border:1px solid var(--border);border-radius:20px;padding:.3rem .75rem;font-size:.8rem;color:var(--muted);cursor:default}
.filter-chip.active{background:var(--primary);color:#fff;border-color:var(--primary)}
.product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem}

/* Product card */
.p-card{background:var(--white);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--sh);transition:box-shadow .15s,transform .15s;cursor:pointer;display:block;width:100%;text-align:left;font:inherit;color:inherit;appearance:none;-webkit-appearance:none;padding:0}
.p-card:hover{box-shadow:var(--sh-md);transform:translateY(-2px)}
.p-card-img{width:100%;height:160px;display:flex;align-items:center;justify-content:center;font-size:3.5rem}
.p-card-body{padding:.875rem}
.p-card-cat{font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem}
.p-card-name{font-size:.9rem;font-weight:600;margin-bottom:.3rem;line-height:1.3}
.p-card-stars{font-size:.8rem;margin-bottom:.25rem}
.star.full,.star.half{color:#f59e0b}
.star.empty{color:#d1d5db}
.p-card-row{display:flex;align-items:baseline;gap:.5rem}
.p-card-price{font-size:1.1rem;font-weight:700;color:var(--primary)}
.p-card-rev{font-size:.75rem;color:var(--muted)}

/* Back link */
.back-wrap{max-width:1200px;margin:.875rem auto 0;padding:0 1.5rem}
.back-btn{background:none;border:none;color:var(--muted);font-size:.875rem;cursor:pointer;display:inline-flex;align-items:center;gap:.3rem;padding:.3rem 0;font-family:inherit}
.back-btn:hover{color:var(--primary)}

/* Breadcrumb */
.breadcrumb{max-width:1200px;margin:.5rem auto 0;padding:0 1.5rem;font-size:.8rem;color:var(--muted)}
.breadcrumb a{color:var(--muted);text-decoration:none}
.breadcrumb a:hover{text-decoration:underline}
.breadcrumb span{margin:0 .35rem}

/* Product detail */
.pd-wrap{max-width:1200px;margin:1.25rem auto;padding:0 1.5rem}
.pd-grid{display:grid;grid-template-columns:1fr 1fr;gap:3rem;background:var(--white);border-radius:var(--radius);box-shadow:var(--sh-md);padding:2rem}
.pd-img{border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:7rem;aspect-ratio:1}
.pd-info h1{font-size:1.4rem;font-weight:700;line-height:1.3;margin-bottom:.5rem}
.pd-cat{font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem}
.pd-stars{font-size:.9rem;margin-bottom:.75rem;display:flex;align-items:center;gap:.4rem}
.pd-stars .rev-count{font-size:.8rem;color:var(--muted)}
.pd-price{font-size:1.85rem;font-weight:800;color:var(--primary);margin-bottom:.75rem}
.pd-desc{font-size:.9rem;color:var(--muted);line-height:1.6;margin-bottom:1.5rem;border-top:1px solid var(--border);padding-top:1rem}
.pd-stock{font-size:.8rem;color:#16a34a;font-weight:600;margin-bottom:1rem}
.pd-btn{width:100%;padding:.875rem 1rem;border:none;border-radius:var(--radius);font-size:1rem;font-weight:700;cursor:pointer;transition:background .15s;margin-bottom:.6rem;letter-spacing:.01em}
.pd-btn:active{opacity:.9}
.btn-atc{background:var(--primary);color:#fff}
.btn-atc:hover{background:var(--primary-h)}
.btn-bn{background:var(--orange);color:#fff}
.btn-bn:hover{background:var(--orange-h)}

/* Toast notification */
.toast{position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%) translateY(80px);background:#1e293b;color:#fff;padding:.65rem 1.25rem;border-radius:var(--radius);font-size:.875rem;font-weight:600;opacity:0;transition:transform .25s,opacity .25s;z-index:500;white-space:nowrap;pointer-events:none}
.toast.show{transform:translateX(-50%) translateY(0);opacity:1}

/* Start / end pages */
.pg-center{min-height:calc(100vh - 64px);display:flex;align-items:center;justify-content:center;padding:2rem}
.start-card{background:var(--white);border-radius:16px;padding:2.5rem 2rem;max-width:460px;width:100%;box-shadow:var(--sh-lg);text-align:center}
.start-card h1{font-size:1.6rem;font-weight:800;margin-bottom:.5rem}
.start-card p{font-size:.925rem;color:var(--muted);margin-bottom:1.5rem}
.sid-box{background:var(--bg);border:2px dashed var(--border);border-radius:var(--radius);padding:.875rem 1.25rem;margin-bottom:1.5rem}
.sid-label{font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem}
.sid-value{font-size:1.5rem;font-weight:800;color:var(--primary);letter-spacing:.1em;font-family:monospace}
.btn-primary{display:block;width:100%;background:var(--primary);color:#fff;border:none;border-radius:var(--radius);padding:.875rem 2rem;font-size:1rem;font-weight:700;cursor:pointer;text-decoration:none;transition:background .15s;margin-bottom:.75rem;text-align:center}
.btn-primary:hover{background:var(--primary-h)}
.btn-secondary{display:block;width:100%;background:var(--white);color:var(--primary);border:2px solid var(--primary);border-radius:var(--radius);padding:.75rem 2rem;font-size:1rem;font-weight:700;cursor:pointer;text-decoration:none;transition:all .15s;text-align:center}
.btn-secondary:hover{background:var(--primary);color:#fff}

/* Step dots */
.step-dots{display:flex;gap:.35rem;justify-content:center;margin-bottom:1.75rem}
.s-dot{width:8px;height:8px;border-radius:50%;background:var(--border)}
.s-dot.done{background:var(--primary)}
.s-dot.active{background:var(--primary);box-shadow:0 0 0 3px rgba(37,99,235,.25)}

/* Interstitial */
.intr-card{background:var(--white);border-radius:16px;padding:2.5rem 2rem;max-width:400px;width:100%;box-shadow:var(--sh-lg);text-align:center}
.intr-card .ico{font-size:3rem;margin-bottom:.75rem}
.intr-card h2{font-size:1.25rem;font-weight:700;margin-bottom:.5rem}
.intr-card p{font-size:.9rem;color:var(--muted);margin-bottom:1.5rem}

/* Survey */
.survey-wrap{max-width:680px;margin:2rem auto;padding:0 1.5rem}
.survey-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--sh-md);padding:2rem}
.survey-card h2{font-size:1.2rem;font-weight:700;margin-bottom:.25rem}
.survey-card .sub{font-size:.875rem;color:var(--muted);margin-bottom:1.5rem}
.q-block{margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid var(--border)}
.q-block:last-of-type{border-bottom:none;margin-bottom:0;padding-bottom:0}
.q-text{font-size:.9rem;font-weight:600;margin-bottom:.75rem;line-height:1.5}
.q-opts{display:flex;flex-wrap:wrap;gap:.5rem}
.q-opt label{display:flex;align-items:center;gap:.4rem;background:var(--bg);border:1px solid var(--border);border-radius:6px;padding:.45rem .75rem;font-size:.85rem;cursor:pointer;transition:all .15s}
.q-opt label:hover{border-color:var(--primary);background:#eff6ff}
.q-opt label:has(input:checked){border-color:var(--primary);background:#eff6ff;font-weight:600}
.q-opt input[type=radio]{accent-color:var(--primary)}
.btn-submit{background:var(--primary);color:#fff;border:none;border-radius:var(--radius);padding:.875rem 2rem;font-size:1rem;font-weight:700;cursor:pointer;width:100%;margin-top:1.5rem;transition:background .15s}
.btn-submit:hover{background:var(--primary-h)}

/* Footer */
.site-footer{background:var(--white);border-top:1px solid var(--border);margin-top:3rem;padding:1.5rem;text-align:center;font-size:.8rem;color:var(--muted)}

/* Responsive */
@media(max-width:768px){
    .pd-grid{grid-template-columns:1fr;gap:1.5rem}
    .hdr-nav{display:none}
    .search-wrap{max-width:160px}
    .product-grid{grid-template-columns:repeat(auto-fill,minmax(160px,1fr))}
    .pd-img{font-size:5rem}
    .start-card,.intr-card{padding:2rem 1.25rem}
}
CSS;

// ═══════════════════════════════════════════════════════════════════
// LAYOUT HELPERS
// ═══════════════════════════════════════════════════════════════════

function openPage(string $title): void {
    global $CSS;
    echo '<!DOCTYPE html><html lang="en"><head>';
    echo '<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . esc($title) . ' — ShopLab</title>';
    echo '<style>' . $CSS . '</style>';
    echo '</head><body>';
}

function siteHeader(): void {
    echo '<header class="site-header"><div class="hdr-inner">';
    echo '<a class="logo" href="#">Shop<em>Lab</em></a>';
    echo '<div class="search-wrap"><span class="search-ico">🔍</span>';
    echo '<input type="text" placeholder="Search products…" tabindex="-1" readonly></div>';
    echo '<ul class="hdr-nav"><li><a href="#">Home</a></li><li><a href="#">New arrivals</a></li><li><a href="#">Deals</a></li><li><a href="#">Help</a></li></ul>';
    echo '<button class="cart-btn" tabindex="-1">🛒 Cart <strong id="cart-count">(0)</strong></button>';
    echo '</div></header>';
    echo '<nav class="cat-bar"><ul>';
    foreach (['All','Electronics','Home & Kitchen','Sports','Books','Toys'] as $c) {
        echo '<li><a href="#">' . esc($c) . '</a></li>';
    }
    echo '</ul></nav>';
}

function sessionBadge(string $sid): void {
    if ($sid) echo '<div class="session-badge">Session: <strong>' . esc($sid) . '</strong></div>';
}

function taskBanner(string $msg, int $step): void {
    echo '<div class="task-banner">';
    echo '<div class="tag">Task ' . $step . ' of 7</div>';
    echo '<div class="msg">' . esc($msg) . '</div>';
    echo '</div>';
}

function stepDots(int $current): void {
    echo '<div class="step-dots">';
    for ($i = 1; $i <= 7; $i++) {
        $cls = $i < $current ? 'done' : ($i === $current ? 'active' : '');
        echo '<div class="s-dot ' . $cls . '"></div>';
    }
    echo '</div>';
}

function siteFooter(): void {
    echo '<footer class="site-footer">© 2026 ShopLab Store &nbsp;·&nbsp; All prices include VAT &nbsp;·&nbsp; Free returns within 30 days</footer>';
}

function closePage(): void {
    echo '</body></html>';
}

// ═══════════════════════════════════════════════════════════════════
// PAGE RENDERERS
// ═══════════════════════════════════════════════════════════════════

function renderStart(string $sid): void {
    openPage('Welcome to ShopLab');
    echo '<div class="pg-center"><div class="start-card">';
    echo '<div style="font-size:2.5rem;margin-bottom:.5rem">🛍</div>';
    echo '<h1>Welcome to ShopLab</h1>';
    echo '<p>You are about to complete a short shopping task. Your unique session ID is shown below — you will need to enter it in the survey at the end.</p>';
    echo '<div class="sid-box"><div class="sid-label">Your session ID</div>';
    echo '<div class="sid-value">' . esc($sid) . '</div></div>';
    echo '<form method="POST" action="index.php">';
    echo '<input type="hidden" name="action" value="start">';
    echo '<button class="btn-primary" type="submit">Start experiment →</button>';
    echo '</form>';
    echo '<p style="font-size:.8rem;color:var(--muted);margin-top:.5rem">Your ID will be shown again at the end so you can copy it.</p>';
    echo '</div></div>';
    closePage();
}

function renderInterstitial(int $next_step): void {
    openPage('Ready?');
    echo '<div class="pg-center"><div class="intr-card">';
    stepDots($next_step);
    echo '<div class="ico">⏸</div>';
    echo '<h2>Ready for the next task?</h2>';
    echo '<p>Take a moment to relax, then press Continue when you are ready to proceed.</p>';
    echo '<form method="POST" action="index.php">';
    echo '<input type="hidden" name="action" value="continue">';
    echo '<button class="btn-primary" type="submit">Continue →</button>';
    echo '</form>';
    echo '</div></div>';
    closePage();
}

// ── Task: catalog browse view ──────────────────────────────────────
function renderTaskCatalog(int $step, string $sid): void {
    global $PRODUCTS;
    $cfg      = getTaskCfg($step);
    $target   = $PRODUCTS[$cfg['product_id']];
    $task_msg = 'Browse the store and add "' . $target['name'] . '" to your cart using the correct button.';

    // Mark task as started so task_start is only logged once per step
    $already_started = !empty($_SESSION['task_started'][$step]);
    $_SESSION['task_started'][$step] = true;

    openPage('Browse Products');
    siteHeader();
    sessionBadge($sid);
    taskBanner($task_msg, $step);

    echo '<div class="section">';
    echo '<div class="section-header">';
    echo '<div class="section-title">All Products (' . count($PRODUCTS) . ')</div>';
    echo '<div class="filter-bar">';
    foreach (['All','Electronics','Home & Kitchen','Sports'] as $cat) {
        $active = $cat === 'All' ? ' active' : '';
        echo '<span class="filter-chip' . $active . '">' . esc($cat) . '</span>';
    }
    echo '</div></div>';

    echo '<div class="product-grid">';
    foreach ($PRODUCTS as $p) {
        echo '<form method="POST" action="index.php" style="display:contents">';
        echo '<input type="hidden" name="action" value="view_product">';
        echo '<input type="hidden" name="product_id" value="' . (int)$p['id'] . '">';
        echo '<button type="submit" class="p-card">';
        echo '<div class="p-card-img" style="background:' . esc($p['color']) . '">' . $p['icon'] . '</div>';
        echo '<div class="p-card-body">';
        echo '<div class="p-card-cat">' . esc($p['category']) . '</div>';
        echo '<div class="p-card-name">' . esc($p['name']) . '</div>';
        echo '<div class="p-card-stars">' . starsHtml($p['rating']) . '</div>';
        echo '<div class="p-card-row">';
        echo '<span class="p-card-price">€' . number_format($p['price'], 2) . '</span>';
        echo '<span class="p-card-rev">(' . number_format($p['reviews']) . ' reviews)</span>';
        echo '</div></div></button></form>';
    }
    echo '</div></div>';

    siteFooter();

    $js_step      = $step;
    $js_cond      = $cfg['condition'];
    $js_pid       = $cfg['product_id'];
    $js_sid       = jsStr($sid);
    $js_started   = $already_started ? 'true' : 'false';

    echo <<<JS
<script>
(function(){
    var t0   = performance.now();
    var SID  = "{$js_sid}";
    var STEP = {$js_step};
    var COND = "{$js_cond}";
    var TPID = {$js_pid};

    function post(data){ data.session_id=SID; fetch('log.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}); }
    function beacon(data){ data.session_id=SID; navigator.sendBeacon('log.php',new Blob([JSON.stringify(data)],{type:'application/json'})); }

    // task_start only once per task step
    if (!{$js_started}) {
        post({event_type:'task_start', step:STEP, condition:COND, target_product_id:TPID});
    }
    post({event_type:'page_enter', step:STEP, condition:COND, target_product_id:TPID});

    window.addEventListener('beforeunload', function(){
        beacon({event_type:'page_exit', step:STEP, condition:COND, target_product_id:TPID,
                ms_page:Math.round(performance.now()-t0)});
    });
})();
</script>
JS;
    closePage();
}

// ── Task: product detail view ──────────────────────────────────────
function renderTaskProduct(int $step, int $product_id, string $sid): void {
    global $PRODUCTS;
    $cfg       = getTaskCfg($step);
    $is_target = ($product_id === $cfg['product_id']);
    $p         = $PRODUCTS[$product_id];
    $cond      = $cfg['condition'];

    openPage($p['name']);
    siteHeader();
    sessionBadge($sid);

    $task_msg = 'Browse the store and add "' . $PRODUCTS[$cfg['product_id']]['name'] . '" to your cart using the correct button.';
    taskBanner($task_msg, $step);

    // Back to catalog
    echo '<div class="back-wrap">';
    echo '<form method="POST" action="index.php" style="display:inline">';
    echo '<input type="hidden" name="action" value="back_to_catalog">';
    echo '<button type="submit" class="back-btn">← Back to catalog</button>';
    echo '</form></div>';

    echo '<div class="breadcrumb"><a href="#">Home</a><span>›</span><a href="#">' . esc($p['category']) . '</a><span>›</span>' . esc($p['name']) . '</div>';

    echo '<div class="pd-wrap"><div class="pd-grid">';
    echo '<div class="pd-img" style="background:' . esc($p['color']) . '">' . $p['icon'] . '</div>';
    echo '<div class="pd-info">';
    echo '<div class="pd-cat">' . esc($p['category']) . '</div>';
    echo '<h1>' . esc($p['name']) . '</h1>';
    echo '<div class="pd-stars">' . starsHtml($p['rating']) . ' <span class="rev-count">(' . number_format($p['reviews']) . ' reviews)</span></div>';
    echo '<div class="pd-price">€' . number_format($p['price'], 2) . '</div>';
    echo '<p class="pd-desc">' . esc($p['desc']) . '</p>';
    echo '<div class="pd-stock">✓ In stock — ships within 1–2 business days</div>';

    // Button order: matches the condition for ALL products shown during this step,
    // keeping the UI consistent (as it would be in a real store).
    $btn_atc = '<button class="pd-btn btn-atc" data-btn="add_to_cart">Add to cart</button>';
    $btn_bn  = '<button class="pd-btn btn-bn"  data-btn="buy_now">Buy now</button>';
    echo '<div id="btn-area">';
    if ($cond === 'standard') {
        echo $btn_atc . $btn_bn;
    } else {
        echo $btn_bn . $btn_atc;
    }
    echo '</div>';
    echo '</div></div></div>'; // pd-info / pd-grid / pd-wrap

    echo '<div class="toast" id="toast">🛒 Added to cart!</div>';

    siteFooter();

    $js_step      = $step;
    $js_cond      = $cond;
    $js_tpid      = $cfg['product_id'];
    $js_pid       = $product_id;
    $js_pname     = jsStr($p['name']);
    $js_is_target = $is_target ? 'true' : 'false';
    $js_sid       = jsStr($sid);

    echo <<<JS
<script>
(function(){
    var t0       = performance.now();
    var clicked  = false;
    var SID      = "{$js_sid}";
    var STEP     = {$js_step};
    var COND     = "{$js_cond}";
    var TPID     = {$js_tpid};   // target product id for this task
    var PID      = {$js_pid};    // product currently being viewed
    var IS_TGT   = {$js_is_target};

    function post(data, cb){
        data.session_id = SID;
        fetch('log.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)})
            .then(function(){ if(cb) cb(); }).catch(function(){ if(cb) cb(); });
    }
    function beacon(data){ data.session_id=SID; navigator.sendBeacon('log.php',new Blob([JSON.stringify(data)],{type:'application/json'})); }

    // product_view — fired whenever this detail page loads (target or not)
    post({event_type:'product_view', step:STEP, condition:COND,
          target_product_id:TPID, clicked_element:"{$js_pname}"});
    post({event_type:'page_enter', step:STEP, condition:COND, target_product_id:TPID});

    function onUnload(){
        beacon({event_type:'page_exit', step:STEP, condition:COND, target_product_id:TPID,
                ms_page:Math.round(performance.now()-t0)});
    }
    window.addEventListener('beforeunload', onUnload);

    document.querySelectorAll('#btn-area button').forEach(function(btn){
        btn.addEventListener('click', function(e){
            if (clicked) return;
            var tfc   = Math.round(performance.now() - t0);
            var bname = btn.dataset.btn;
            var bx    = Math.round(e.clientX);
            var by    = Math.round(e.clientY);

            if (IS_TGT) {
                // ── Target product ─────────────────────────────────
                clicked = true;
                var unint = (bname === 'buy_now') ? 1 : 0;
                window.removeEventListener('beforeunload', onUnload);

                post({event_type:'button_click', step:STEP, condition:COND,
                      target_product_id:TPID, clicked_element:bname,
                      click_x:bx, click_y:by, time_to_first_click_ms:tfc,
                      is_unintended_interaction:unint},
                function(){
                    post({event_type:'task_complete', step:STEP, condition:COND,
                          target_product_id:TPID, clicked_element:bname,
                          click_x:bx, click_y:by, time_to_first_click_ms:tfc,
                          is_unintended_interaction:unint, completed:1},
                    function(){
                        beacon({event_type:'page_exit', step:STEP, condition:COND,
                                target_product_id:TPID, ms_page:Math.round(performance.now()-t0)});
                        var f = document.createElement('form');
                        f.method='POST'; f.action='index.php';
                        var inp = document.createElement('input');
                        inp.type='hidden'; inp.name='action'; inp.value='advance';
                        f.appendChild(inp);
                        document.body.appendChild(f);
                        f.submit();
                    });
                });
            } else {
                // ── Non-target product: log but stay on page ───────
                // is_unintended_interaction is left blank for non-target clicks
                post({event_type:'button_click', step:STEP, condition:COND,
                      target_product_id:TPID, clicked_element:bname,
                      click_x:bx, click_y:by, time_to_first_click_ms:tfc});
                // Show "Added to cart" toast
                var toast = document.getElementById('toast');
                toast.classList.add('show');
                // Update cart counter cosmetically
                var cc = document.getElementById('cart-count');
                if (cc) { var n = parseInt(cc.textContent.replace(/\D/g,''))||0; cc.textContent='('+(n+1)+')'; }
                setTimeout(function(){ toast.classList.remove('show'); }, 2000);
                // Allow a second click after toast clears (e.g. if they click the other button)
                setTimeout(function(){ clicked = false; }, 300);
            }
        });
    });
})();
</script>
JS;
    closePage();
}

// ── Task dispatcher ────────────────────────────────────────────────
function renderTask(int $step, string $sid): void {
    $viewing = $_SESSION['task_product_view'] ?? null;
    if ($viewing !== null) {
        renderTaskProduct($step, (int)$viewing, $sid);
    } else {
        renderTaskCatalog($step, $sid);
    }
}

// ── Distraction steps ──────────────────────────────────────────────
function renderDistraction(int $step, string $sid): void {
    global $PRODUCTS;

    $msg = $step === 2
        ? 'Browse the products below. Which product is the cheapest? Click on it to continue.'
        : 'Browse the products below. Which product has the most reviews? Click on it to continue.';

    openPage('Browse Products');
    siteHeader();
    sessionBadge($sid);
    taskBanner($msg, $step);

    echo '<div class="section">';
    echo '<div class="section-header"><div class="section-title">All Products (' . count($PRODUCTS) . ')</div>';
    echo '<div class="filter-bar"><span class="filter-chip active">All</span><span class="filter-chip">Electronics</span><span class="filter-chip">Home & Kitchen</span><span class="filter-chip">Sports</span></div></div>';
    echo '<div class="product-grid">';
    foreach ($PRODUCTS as $p) {
        echo '<button class="p-card" data-pid="' . (int)$p['id'] . '" data-name="' . esc($p['name']) . '" onclick="distractionClick(this,event)">';
        echo '<div class="p-card-img" style="background:' . esc($p['color']) . '">' . $p['icon'] . '</div>';
        echo '<div class="p-card-body">';
        echo '<div class="p-card-cat">' . esc($p['category']) . '</div>';
        echo '<div class="p-card-name">' . esc($p['name']) . '</div>';
        echo '<div class="p-card-stars">' . starsHtml($p['rating']) . '</div>';
        echo '<div class="p-card-row">';
        echo '<span class="p-card-price">€' . number_format($p['price'], 2) . '</span>';
        echo '<span class="p-card-rev">(' . number_format($p['reviews']) . ' reviews)</span>';
        echo '</div></div></button>';
    }
    echo '</div></div>';

    siteFooter();

    $js_step = $step;
    $js_sid  = jsStr($sid);

    echo <<<JS
<script>
(function(){
    var t0   = performance.now();
    var SID  = "{$js_sid}";
    var STEP = {$js_step};
    var done = false;

    function post(data){ data.session_id=SID; fetch('log.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}); }
    function beacon(data){ data.session_id=SID; navigator.sendBeacon('log.php',new Blob([JSON.stringify(data)],{type:'application/json'})); }

    post({event_type:'task_start', step:STEP});
    post({event_type:'page_enter', step:STEP});

    function onUnload(){ beacon({event_type:'page_exit', step:STEP, ms_page:Math.round(performance.now()-t0)}); }
    window.addEventListener('beforeunload', onUnload);

    window.distractionClick = function(btn, e){
        if (done) return;
        done = true;
        var name = btn.dataset.name;
        var bx   = Math.round(e.clientX);
        var by   = Math.round(e.clientY);
        window.removeEventListener('beforeunload', onUnload);
        beacon({event_type:'distraction_click', step:STEP, clicked_element:name, click_x:bx, click_y:by});
        beacon({event_type:'page_exit', step:STEP, ms_page:Math.round(performance.now()-t0)});
        var f = document.createElement('form');
        f.method='POST'; f.action='index.php';
        var ai = document.createElement('input');
        ai.type='hidden'; ai.name='action'; ai.value='advance';
        f.appendChild(ai); document.body.appendChild(f); f.submit();
    };
})();
</script>
JS;
    closePage();
}

// ── Survey ─────────────────────────────────────────────────────────
function renderSurvey(string $sid): void {
    global $SURVEY_QUESTIONS;
    openPage('Short Survey');
    siteHeader();
    sessionBadge($sid);
    taskBanner('Please complete the following short survey. All questions must be answered.', 6);

    echo '<div class="survey-wrap"><div class="survey-card">';
    echo '<h2>Quick Shopping Habits Survey</h2>';
    echo '<p class="sub">This survey has 5 questions about your online shopping habits and should take less than 2 minutes.</p>';
    echo '<form method="POST" action="index.php" id="sform">';
    echo '<input type="hidden" name="action" value="submit_survey">';

    foreach ($SURVEY_QUESTIONS as $i => $q) {
        $num = $i + 1;
        echo '<div class="q-block"><div class="q-text">' . $num . '. ' . esc($q['text']) . '</div>';
        echo '<div class="q-opts">';
        foreach ($q['options'] as $val => $label) {
            $name = 'q_' . $q['id'];
            $id   = $name . '_' . $val;
            echo '<div class="q-opt"><label for="' . esc($id) . '">';
            echo '<input type="radio" name="' . esc($name) . '" id="' . esc($id) . '" value="' . esc((string)$val) . '" required>';
            echo '<span>' . esc($label) . '</span></label></div>';
        }
        echo '</div></div>';
    }
    echo '<button type="submit" class="btn-submit">Submit survey →</button>';
    echo '</form></div></div>';
    siteFooter();

    $js_sid = jsStr($sid);
    echo <<<JS
<script>
(function(){
    var SID = "{$js_sid}";
    function post(d){ d.session_id=SID; fetch('log.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)}); }
    post({event_type:'page_enter', step:6});
    document.getElementById('sform').addEventListener('submit', function(e){
        var blocks = this.querySelectorAll('.q-block');
        for (var i=0; i<blocks.length; i++){
            if (!blocks[i].querySelector('input[type=radio]:checked')){
                e.preventDefault();
                alert('Please answer all questions before submitting.');
                return;
            }
        }
    });
})();
</script>
JS;
    closePage();
}

// ── End page ───────────────────────────────────────────────────────
function renderEnd(string $sid): void {
    openPage('Session Complete');
    $safe_sid = esc($sid);
    $js_sid   = jsStr($sid);
    $q_url    = esc(QUALTRICS_URL);

    echo '<div class="pg-center"><div class="start-card">';
    echo '<div style="font-size:2.5rem;margin-bottom:.5rem">✅</div>';
    echo '<h1>You\'re done!</h1>';
    echo '<p>Thank you for completing the shopping tasks. Please copy your session ID and enter it in the Qualtrics survey to link your responses.</p>';
    echo '<div class="sid-box"><div class="sid-label">Your session ID</div>';
    echo '<div class="sid-value" id="sid-val">' . $safe_sid . '</div></div>';
    echo '<button class="btn-primary" onclick="copyId()" id="copy-btn">📋 Copy session ID</button>';
    echo '<a class="btn-secondary" href="' . $q_url . '">Return to survey →</a>';
    echo '</div></div>';

    echo <<<JS
<script>
(function(){
    var SID = "{$js_sid}";
    fetch('log.php',{method:'POST',headers:{'Content-Type':'application/json'},
        body:JSON.stringify({session_id:SID,event_type:'page_enter',step:8})});
})();
function copyId(){
    var sid = document.getElementById('sid-val').innerText.trim();
    var btn = document.getElementById('copy-btn');
    function confirm(){ btn.textContent='✓ Copied!'; btn.style.background='#16a34a'; setTimeout(function(){ btn.textContent='📋 Copy session ID'; btn.style.background=''; },2500); }
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(sid).then(confirm).catch(function(){ legacyCopy(sid); confirm(); });
    } else { legacyCopy(sid); confirm(); }
}
function legacyCopy(t){ var ta=document.createElement('textarea'); ta.value=t; ta.style.cssText='position:fixed;opacity:0'; document.body.appendChild(ta); ta.select(); try{document.execCommand('copy');}catch(e){} document.body.removeChild(ta); }
</script>
JS;
    closePage();
}

// ═══════════════════════════════════════════════════════════════════
// MAIN RENDER
// ═══════════════════════════════════════════════════════════════════
if ($step === 0) {
    renderStart($sid);
} elseif ($intr) {
    renderInterstitial($step);
} else {
    switch ($step) {
        case 1: renderTask(1, $sid);        break;
        case 2: renderDistraction(2, $sid); break;
        case 3: renderTask(3, $sid);        break;
        case 4: renderDistraction(4, $sid); break;
        case 5: renderTask(5, $sid);        break;
        case 6: renderSurvey($sid);         break;
        case 7: renderTask(7, $sid);        break;
        case 8: renderEnd($sid);            break;
        default:
            session_destroy();
            header('Location: index.php'); exit;
    }
}
