<?php
session_name('shoplab_admin');
session_start();

define('DATA_DIR',    __DIR__ . '/data');
define('EVENTS_FILE', DATA_DIR . '/events.csv');
require __DIR__ . '/' . (file_exists(__DIR__ . '/config.php') ? 'config.php' : 'config.example.php');

// ── Authentication ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (ADMIN_PASS !== '' && hash_equals(ADMIN_PASS, (string)$_POST['password'])) {
        $_SESSION['admin_ok'] = true;
    }
    header('Location: admin.php'); exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php'); exit;
}

if (empty($_SESSION['admin_ok'])) {
    ?><!DOCTYPE html>
    <html lang="en"><head><meta charset="UTF-8"><title>ShopLab Admin — Login</title>
    <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center}
    .card{background:#fff;border-radius:12px;padding:2.5rem 2rem;max-width:360px;width:100%;box-shadow:0 10px 25px rgba(0,0,0,.12);text-align:center}
    h1{font-size:1.35rem;font-weight:700;margin-bottom:.4rem}
    p{font-size:.875rem;color:#6b7280;margin-bottom:1.5rem}
    input[type=password]{width:100%;padding:.7rem 1rem;border:1px solid #e5e7eb;border-radius:8px;font-size:.95rem;margin-bottom:.875rem;outline:none}
    input:focus{border-color:#2563eb}
    button{width:100%;padding:.75rem;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:.95rem;font-weight:700;cursor:pointer}
    button:hover{background:#1d4ed8}
    </style></head><body>
    <div class="card">
        <div style="font-size:2rem;margin-bottom:.5rem">🔒</div>
        <h1>ShopLab Admin</h1>
        <p>Enter the admin password to continue.</p>
        <form method="POST">
            <input type="password" name="password" placeholder="Password" autofocus>
            <button type="submit">Sign in</button>
        </form>
    </div>
    </body></html>
    <?php
    exit;
}

// ── Download raw CSV ───────────────────────────────────────────────
if (isset($_GET['download'])) {
    if (file_exists(EVENTS_FILE)) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="shoplab_events_' . date('Ymd_His') . '.csv"');
        readfile(EVENTS_FILE);
    } else {
        header('Location: admin.php?msg=nodata');
    }
    exit;
}

// ── Read and parse CSV ─────────────────────────────────────────────
function parseCsv(string $file): array {
    if (!file_exists($file) || filesize($file) === 0) return [];
    $rows = [];
    $fp   = fopen($file, 'r');
    if (!$fp) return [];
    $header = fgetcsv($fp, 0, ',', '"', '\\');
    while (($line = fgetcsv($fp, 0, ',', '"', '\\')) !== false) {
        if (count($line) === count($header)) {
            $rows[] = array_combine($header, $line);
        }
    }
    fclose($fp);
    return $rows;
}

function summariseSessions(array $rows): array {
    $sessions = [];

    // Validation question IDs and the conditions for passing
    $val_ids = ['Q9', 'D1', 'D2'];

    foreach ($rows as $r) {
        $sid = $r['session_id'] ?? '';
        if (!$sid) continue;
        if (!isset($sessions[$sid])) {
            $sessions[$sid] = [
                'session_id'         => $sid,
                'completed'          => false,
                'tasks'              => [],
                'val_failures'       => 0,
                'val_checked'        => [],
                'first_ts'           => $r['timestamp_iso'],
            ];
        }
        $s = &$sessions[$sid];

        $et   = $r['event_type']   ?? '';
        $step = (int)($r['step']   ?? 0);
        $cond = $r['condition']    ?? '';

        if ($et === 'session_complete') $s['completed'] = true;

        // Collect task metrics from task_complete events
        if ($et === 'task_complete') {
            $label = '';
            switch ($step) {
                case 1: $label = 'standard_a'; break;
                case 3: $label = 'standard_b'; break;
                case 5: $label = 'modified_a'; break;
                case 7: $label = 'modified_b'; break;
            }
            if ($label) {
                $s['tasks'][$label] = [
                    'time_ms'     => (int)($r['time_to_first_click_ms']    ?? 0),
                    'error'       => (int)($r['is_unintended_interaction'] ?? 0),
                    'condition'   => $cond,
                ];
            }
        }

        // Collect validation question results
        if ($et === 'survey_response' && ($r['is_validation_question'] ?? '') === '1') {
            $qid = $r['question_id'] ?? '';
            if (in_array($qid, $val_ids) && !isset($s['val_checked'][$qid])) {
                $passed = $r['validation_passed'] ?? '';
                $s['val_checked'][$qid] = $passed;
                if ($passed === '0') $s['val_failures']++;
            }
        }
    }

    return $sessions;
}

$rows     = parseCsv(EVENTS_FILE);
$sessions = summariseSessions($rows);

// ── HTML ───────────────────────────────────────────────────────────
$total      = count($sessions);
$n_complete = count(array_filter($sessions, fn($s) => $s['completed']));
$n_exclude  = count(array_filter($sessions, fn($s) => $s['val_failures'] >= 2));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ShopLab Admin Dashboard</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f1f5f9;color:#111827;font-size:.875rem}
.topbar{background:#1e293b;color:#e2e8f0;padding:.875rem 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem}
.topbar h1{font-size:1.05rem;font-weight:700}
.topbar-links{display:flex;gap:1rem;align-items:center}
.topbar a{color:#94a3b8;text-decoration:none;font-size:.8rem}
.topbar a:hover{color:#fff}
.dl-btn{background:#2563eb;color:#fff;border:none;padding:.45rem .875rem;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;text-decoration:none}
.stats{display:flex;gap:1rem;padding:1.25rem 1.5rem;flex-wrap:wrap}
.stat-card{background:#fff;border-radius:10px;padding:1rem 1.25rem;min-width:140px;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.stat-card .val{font-size:1.75rem;font-weight:800;line-height:1.1}
.stat-card .lbl{font-size:.75rem;color:#6b7280;margin-top:.2rem}
.wrap{padding:0 1.5rem 2rem}
.tbl-wrap{overflow-x:auto;background:#fff;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.08)}
table{width:100%;border-collapse:collapse;white-space:nowrap}
th{background:#f8fafc;padding:.6rem .875rem;text-align:left;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:1px solid #e2e8f0;position:sticky;top:0}
td{padding:.55rem .875rem;border-bottom:1px solid #f1f5f9;font-size:.82rem}
tr:last-child td{border-bottom:none}
tr:hover td{background:#f8fafc}
.badge{display:inline-block;padding:.15rem .5rem;border-radius:4px;font-size:.72rem;font-weight:700}
.badge-green{background:#dcfce7;color:#16a34a}
.badge-red{background:#fee2e2;color:#dc2626}
.badge-blue{background:#dbeafe;color:#1d4ed8}
.badge-gray{background:#f1f5f9;color:#64748b}
.no-data{text-align:center;padding:3rem;color:#94a3b8}
</style>
</head>
<body>
<div class="topbar">
    <h1>🧪 ShopLab Admin Dashboard</h1>
    <div class="topbar-links">
        <a href="admin.php?download=1" class="dl-btn">⬇ Download events.csv</a>
        <a href="admin.php?logout=1">Sign out</a>
    </div>
</div>

<div class="stats">
    <div class="stat-card"><div class="val"><?= $total ?></div><div class="lbl">Total sessions</div></div>
    <div class="stat-card"><div class="val"><?= $n_complete ?></div><div class="lbl">Completed</div></div>
    <div class="stat-card"><div class="val"><?= $total - $n_complete ?></div><div class="lbl">Incomplete</div></div>
    <div class="stat-card"><div class="val" style="color:#dc2626"><?= $n_exclude ?></div><div class="lbl">Flagged for exclusion</div></div>
    <div class="stat-card"><div class="val"><?= count($rows) ?></div><div class="lbl">Total log rows</div></div>
</div>

<div class="wrap">
<?php if (empty($sessions)): ?>
    <div class="tbl-wrap"><div class="no-data">No data yet. Events will appear here once participants start.</div></div>
<?php else: ?>
<div class="tbl-wrap">
<table>
<thead>
<tr>
    <th>Session ID</th>
    <th>Completed</th>
    <th>Std-A time (ms)</th>
    <th>Std-A error</th>
    <th>Std-B time (ms)</th>
    <th>Std-B error</th>
    <th>Mod-A time (ms)</th>
    <th>Mod-A error</th>
    <th>Mod-B time (ms)</th>
    <th>Mod-B error</th>
    <th>Total errors (0–2)</th>
    <th>Val. failures</th>
    <th>Exclude</th>
</tr>
</thead>
<tbody>
<?php
$task_keys = [
    'standard_a' => ['time_ms', 'error'],
    'standard_b' => ['time_ms', 'error'],
    'modified_a' => ['time_ms', 'error'],
    'modified_b' => ['time_ms', 'error'],
];

foreach ($sessions as $s):
    $completed = $s['completed'];
    $tasks     = $s['tasks'];

    $std_a_time  = isset($tasks['standard_a']) ? $tasks['standard_a']['time_ms']   : '';
    $std_a_err   = isset($tasks['standard_a']) ? $tasks['standard_a']['error']      : '';
    $std_b_time  = isset($tasks['standard_b']) ? $tasks['standard_b']['time_ms']   : '';
    $std_b_err   = isset($tasks['standard_b']) ? $tasks['standard_b']['error']      : '';
    $mod_a_time  = isset($tasks['modified_a']) ? $tasks['modified_a']['time_ms']   : '';
    $mod_a_err   = isset($tasks['modified_a']) ? $tasks['modified_a']['error']      : '';
    $mod_b_time  = isset($tasks['modified_b']) ? $tasks['modified_b']['time_ms']   : '';
    $mod_b_err   = isset($tasks['modified_b']) ? $tasks['modified_b']['error']      : '';

    // Total unintended interactions on modified tasks only
    $mod_errors = ($mod_a_err !== '' ? (int)$mod_a_err : 0) + ($mod_b_err !== '' ? (int)$mod_b_err : 0);

    $val_fail = $s['val_failures'];
    $exclude  = $val_fail >= 2;
?>
<tr>
    <td><code><?= htmlspecialchars($s['session_id']) ?></code></td>
    <td>
        <?php if ($completed): ?>
            <span class="badge badge-green">Yes</span>
        <?php else: ?>
            <span class="badge badge-gray">No</span>
        <?php endif; ?>
    </td>
    <td><?= $std_a_time !== '' ? number_format((int)$std_a_time) : '<span style="color:#94a3b8">—</span>' ?></td>
    <td>
        <?php if ($std_a_err !== ''):
            echo $std_a_err ? '<span class="badge badge-red">1</span>' : '<span class="badge badge-green">0</span>';
        else: ?><span style="color:#94a3b8">—</span><?php endif; ?>
    </td>
    <td><?= $std_b_time !== '' ? number_format((int)$std_b_time) : '<span style="color:#94a3b8">—</span>' ?></td>
    <td>
        <?php if ($std_b_err !== ''):
            echo $std_b_err ? '<span class="badge badge-red">1</span>' : '<span class="badge badge-green">0</span>';
        else: ?><span style="color:#94a3b8">—</span><?php endif; ?>
    </td>
    <td><?= $mod_a_time !== '' ? number_format((int)$mod_a_time) : '<span style="color:#94a3b8">—</span>' ?></td>
    <td>
        <?php if ($mod_a_err !== ''):
            echo $mod_a_err ? '<span class="badge badge-red">1</span>' : '<span class="badge badge-green">0</span>';
        else: ?><span style="color:#94a3b8">—</span><?php endif; ?>
    </td>
    <td><?= $mod_b_time !== '' ? number_format((int)$mod_b_time) : '<span style="color:#94a3b8">—</span>' ?></td>
    <td>
        <?php if ($mod_b_err !== ''):
            echo $mod_b_err ? '<span class="badge badge-red">1</span>' : '<span class="badge badge-green">0</span>';
        else: ?><span style="color:#94a3b8">—</span><?php endif; ?>
    </td>
    <td><strong><?= $mod_errors ?></strong></td>
    <td><?= $val_fail ?></td>
    <td>
        <?php if ($exclude): ?>
            <span class="badge badge-red">Yes — exclude</span>
        <?php else: ?>
            <span class="badge badge-green">No</span>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
</body>
</html>
