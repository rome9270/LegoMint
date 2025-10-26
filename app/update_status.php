<?php
session_start();
require __DIR__.'/db.php';
if (empty($_SESSION['user'])) { http_response_code(403); exit('Nicht angemeldet'); }
$user = $_SESSION['user'];
if ($user['role'] !== 'student') { http_response_code(403); exit('Nur Schüler ändern ihren Status.'); }

$task_id = (int)($_POST['task_id'] ?? 0);
$status  = $_POST['status'] ?? '';
$allowed = ['nicht_bearbeitet','korrekt','nicht_korrekt'];

if ($task_id<=0 || !in_array($status,$allowed,true)) {
  http_response_code(400); exit('Ungültige Eingabe');
}

$pdo->beginTransaction();
try{
  $pdo->prepare("
    INSERT INTO student_tasks (user_id, task_id, status, updated_at)
    VALUES (:uid, :tid, :st, datetime('now'))
    ON CONFLICT(user_id, task_id) DO UPDATE SET status=excluded.status, updated_at=datetime('now')
  ")->execute([':uid'=>$user['id'], ':tid'=>$task_id, ':st'=>$status]);

  $pdo->commit();
} catch (Exception $e){
  $pdo->rollBack();
  http_response_code(500); exit('Fehler beim Speichern');
}

header('Location: dashboard.php');
exit;