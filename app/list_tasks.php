<?php
require __DIR__.'/db.php';

// Aufgaben abrufen
$tasks = $pdo->query("SELECT id, title, html_file FROM tasks ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Aufgabenliste</title>
  <link rel="stylesheet" href="../CSS/01_main.css">
</head>
<body>
  <h1>🧩 Aufgabenliste</h1>
  <p>Alle Aufgaben aus der Tabelle <code>tasks</code>:</p>

  <table border="1" cellspacing="0" cellpadding="6">
    <tr>
      <th>ID</th>
      <th>Titel</th>
      <th>Datei (HTML)</th>
    </tr>

    <?php foreach ($tasks as $task): ?>
      <tr>
        <td><?= htmlspecialchars($task['id']) ?></td>
        <td><?= htmlspecialchars($task['title']) ?></td>
        <td>
          <a href="../html/<?= htmlspecialchars($task['html_file']) ?>" target="_blank">
            <?= htmlspecialchars($task['html_file']) ?>
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p><a href="01_main.php">← Zurück zur Hauptseite</a></p>
</body>
</html>
