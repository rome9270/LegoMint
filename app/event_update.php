<?php
// app/event_update.php
session_start();
require __DIR__ . '/db.php';
file_put_contents(__DIR__ . '/last_event_debug.log',
    date('c') . ' ' . json_encode($_POST) . PHP_EOL,
    FILE_APPEND
);

header('Content-Type: application/json');

function bad($code, $msg){ http_response_code($code); echo json_encode(['ok'=>false,'msg'=>$msg]); exit; }

if (empty($_SESSION['user'])) bad(401, 'not authenticated');

$uid    = (int)($_SESSION['user']['id'] ?? 0);
$taskId = (int)($_POST['task_id'] ?? 0);
$event  = $_POST['event'] ?? '';

if ($uid<=0 || $taskId<=0)              bad(400, 'bad ids');
if (!in_array($event, ['sim','download'], true)) bad(400, 'bad event');

// 1) sicherstellen, dass ein Datensatz existiert
$pdo->prepare("
  INSERT INTO student_tasks (user_id, task_id, status, updated_at)
  VALUES (:u, :t, 'nicht_bearbeitet', datetime('now'))
  ON CONFLICT(user_id, task_id) DO NOTHING
")->execute([':u'=>$uid, ':t'=>$taskId]);

// 2) Flag setzen
if ($event === 'sim') {
  $pdo->prepare("
    UPDATE student_tasks
    SET sim_ok=1, sim_at=datetime('now'), updated_at=datetime('now')
    WHERE user_id=:u AND task_id=:t
  ")->execute([':u'=>$uid, ':t'=>$taskId]);
} else {
  $pdo->prepare("
    UPDATE student_tasks
    SET download_ok=1, download_at=datetime('now'), updated_at=datetime('now')
    WHERE user_id=:u AND task_id=:t
  ")->execute([':u'=>$uid, ':t'=>$taskId]);
}

// 3) Status ggf. auf 'korrekt' setzen (außer manuell 'nicht_korrekt')
$row = $pdo->prepare("
  SELECT sim_ok, download_ok, status
  FROM student_tasks
  WHERE user_id=:u AND task_id=:t
");
$row->execute([':u'=>$uid, ':t'=>$taskId]);
$st = $row->fetch();

if ($st && (int)$st['sim_ok'] === 1 && (int)$st['download_ok'] === 1 && $st['status'] !== 'nicht_korrekt') {
  $pdo->prepare("
    UPDATE student_tasks
    SET status='korrekt', updated_at=datetime('now')
    WHERE user_id=:u AND task_id=:t
  ")->execute([':u'=>$uid, ':t'=>$taskId]);
}

// 4) aktuellen Stand zurückgeben (hilft beim Debuggen)
$cur = $pdo->prepare("
  SELECT status, sim_ok, download_ok, sim_at, download_at, updated_at
  FROM student_tasks
  WHERE user_id=:u AND task_id=:t
");
$cur->execute([':u'=>$uid, ':t'=>$taskId]);
echo json_encode(['ok'=>true, 'data'=>$cur->fetch()]);
