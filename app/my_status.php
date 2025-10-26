<?php
session_start();
require __DIR__.'/db.php';

if (empty($_SESSION['user'])) {
    header('Location: login.html');
    exit;
}

$uid = (int)$_SESSION['user']['id'];

$stmt = $pdo->prepare("
  SELECT t.title,
         COALESCE(st.status,'nicht_bearbeitet') AS status,
         st.updated_at
    FROM tasks t
    LEFT JOIN student_tasks st 
      ON st.task_id = t.id
     AND st.user_id = :u
   ORDER BY t.id
");
$stmt->execute([':u' => $uid]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function deTime($utc){
    if (!$utc) return '-';
    $dt = new DateTime($utc, new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Europe/Berlin'));
    return $dt->format('d.m.Y, H:i') . ' Uhr';
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="../CSS/01_main.css">
  <link rel="stylesheet" href="../CSS/addons_login.css">
  <title>Mein Status</title>
</head>
<body>

<div class="container">
  <div class="hero">
    <h1>Mein Status</h1>
    <hr>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>Aufgabe</th>
        <th>Status</th>
        <th>Zuletzt geändert</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['title']) ?></td>
        <td><?= htmlspecialchars($r['status']) ?></td>
        <td><?= htmlspecialchars(deTime($r['updated_at'] ?? '')) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="toolbar">
    <a class="btn" href="/LegoMint/app/01_main.php">⬅️ Zur Hauptseite</a>
  </div>
</div>

</body>
</html>
