<?php
// Erhält POST: task_id, event ('sim' | 'download')
// Setzt Flags + Zeitstempel; wenn beides = 1, Status='korrekt'
session_start();
require __DIR__.'/db.php';

header('Content-Type: application/json');
if (empty($_SESSION['user'])) { http_response_code(401); echo json_encode(['ok'=>false]); exit; }

$uid = (int)$_SESSION['user']['id'];
$taskId = (int)($_POST['task_id'] ?? 0);
$event  = $_POST['event'] ?? '';

if ($taskId<=0 || !in_array($event, ['sim','download'], true)) {
  http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'bad input']); exit;
}

// sicherstellen, dass Datensatz existiert
$pdo->prepare("
  INSERT INTO student_tasks (user_id,task_id,status,updated_at)
  VALUES (:u,:t,'nicht_bearbeitet',datetime('now'))
  ON CONFLICT(user_id,task_id) DO NOTHING
")->execute([':u'=>$uid, ':t'=>$taskId]);

if ($event==='sim') {
  $pdo->prepare("UPDATE student_tasks SET sim_ok=1, sim_at=datetime('now'), updated_at=datetime('now')
                 WHERE user_id=:u AND task_id=:t")->execute([':u'=>$uid, ':t'=>$taskId]);
} else {
  $pdo->prepare("UPDATE student_tasks SET download_ok=1, download_at=datetime('now'), updated_at=datetime('now')
                 WHERE user_id=:u AND task_id=:t")->execute([':u'=>$uid, ':t'=>$taskId]);
}

// wenn beide erfüllt -> korrekt (außer Lehrer hat 'nicht_korrekt' gesetzt)
$row = $pdo->prepare("SELECT sim_ok,download_ok,status FROM student_tasks WHERE user_id=:u AND task_id=:t");
$row->execute([':u'=>$uid, ':t'=>$taskId]);
$st = $row->fetch();

if ($st && (int)$st['sim_ok']===1 && (int)$st['download_ok']===1 && $st['status']!=='nicht_korrekt') {
  $pdo->prepare("UPDATE student_tasks SET status='korrekt', updated_at=datetime('now')
                 WHERE user_id=:u AND task_id=:t")->execute([':u'=>$uid, ':t'=>$taskId]);
}

echo json_encode(['ok'=>true]);
