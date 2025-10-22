<?php
// app/event_update.php – erhält Events "sim" (Simulation ok) und "download" (Datei gespeichert)

session_start();
require __DIR__.'/db.php';
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user'])) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'Nicht angemeldet']); exit;
}

$user   = $_SESSION['user'];
$uid    = (int)$user['id'];
$taskId = (int)($_POST['task_id'] ?? 0);
$event  = $_POST['event'] ?? ''; // 'sim' oder 'download'

if ($taskId <= 0 || !in_array($event, ['sim','download'], true)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Ungültige Eingabe']); exit;
}

$pdo->beginTransaction();
try {
  // Sicherstellen, dass ein Datensatz existiert
  $pdo->prepare("
    INSERT INTO student_tasks (user_id, task_id, status, updated_at)
    VALUES (:uid, :tid, 'nicht_bearbeitet', datetime('now'))
    ON CONFLICT(user_id, task_id) DO NOTHING
  ")->execute([':uid'=>$uid, ':tid'=>$taskId]);

  if ($event === 'sim') {
    $pdo->prepare("UPDATE student_tasks
                     SET sim_ok=1, sim_at=datetime('now'), updated_at=datetime('now')
                   WHERE user_id=:uid AND task_id=:tid")
        ->execute([':uid'=>$uid, ':tid'=>$taskId]);
  } else { // download
    $pdo->prepare("UPDATE student_tasks
                     SET download_ok=1, download_at=datetime('now'), updated_at=datetime('now')
                   WHERE user_id=:uid AND task_id=:tid")
        ->execute([':uid'=>$uid, ':tid'=>$TaskId]);
  }

  // Wenn beide erfüllt sind → automatisch 'korrekt' (außer Lehrer hat 'nicht_korrekt' vergeben)
  $st = $pdo->prepare("SELECT sim_ok, download_ok, status
                         FROM student_tasks
                        WHERE user_id=:uid AND task_id=:tid");
  $st->execute([':uid'=>$uid, ':tid'=>$taskId]);
  $row = $st->fetch();

  if ($row && (int)$row['sim_ok'] === 1 && (int)$row['download_ok'] === 1 && $row['status'] !== 'nicht_korrekt') {
    $pdo->prepare("UPDATE student_tasks
                     SET status='korrekt', updated_at=datetime('now')
                   WHERE user_id=:uid AND task_id=:tid")
        ->execute([':uid'=>$uid, ':tid'=>$taskId]);
  }

  $pdo->commit();
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  $pdo->rollBack();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Serverfehler']);
}
