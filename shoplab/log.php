<?php
session_start();

define('DATA_DIR',    __DIR__ . '/data');
define('EVENTS_FILE', DATA_DIR . '/events.csv');

header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

// Parse JSON body
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_json']);
    exit;
}

// Validate session_id matches active session
$session_sid = $_SESSION['session_id'] ?? '';
$request_sid = $data['session_id'] ?? '';

// Accept the event if session IDs match, or if it's a page_exit/beacon on end page where
// session may have already ended.  Always require a non-empty matching session_id.
if (!$session_sid || $request_sid !== $session_sid) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'session_mismatch']);
    exit;
}

// ── Compute server-side fields ─────────────────────────────────────
$timestamp = (function(): string {
    $dt = new DateTime('now', new DateTimeZone('UTC'));
    return $dt->format('Y-m-d\TH:i:s') . '.'
           . str_pad((string)(int)(microtime(true) * 1000 % 1000), 3, '0', STR_PAD_LEFT) . 'Z';
})();

$ms_elapsed = 0;
if (!empty($_SESSION['start_microtime'])) {
    $ms_elapsed = (int)round((microtime(true) - $_SESSION['start_microtime']) * 1000);
}

// ── Build CSV row ──────────────────────────────────────────────────
$cols = [
    'session_id', 'timestamp_iso', 'ms_elapsed', 'event_type', 'step', 'condition',
    'target_product_id', 'clicked_element', 'click_x', 'click_y',
    'time_to_first_click_ms', 'is_unintended_interaction',
    'question_id', 'question_text', 'answer_value',
    'is_validation_question', 'validation_passed', 'completed',
];

$row = [];
foreach ($cols as $c) {
    switch ($c) {
        case 'session_id':    $row[] = $session_sid;                          break;
        case 'timestamp_iso': $row[] = $timestamp;                            break;
        case 'ms_elapsed':    $row[] = $ms_elapsed;                           break;
        default:              $row[] = isset($data[$c]) ? $data[$c] : '';     break;
    }
}

// ── Append to CSV ──────────────────────────────────────────────────
$new = !file_exists(EVENTS_FILE) || filesize(EVENTS_FILE) === 0;
$fp  = fopen(EVENTS_FILE, 'a');
if (!$fp) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'cannot_open_file']);
    exit;
}
if (flock($fp, LOCK_EX)) {
    if ($new) fputcsv($fp, $cols);
    fputcsv($fp, $row);
    flock($fp, LOCK_UN);
}
fclose($fp);

echo json_encode(['ok' => true]);
