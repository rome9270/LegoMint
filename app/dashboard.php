<?php
session_start();
require __DIR__.'/db.php';
if (empty($_SESSION['user'])) { header('Location: login.html'); exit; }
$user = $_SESSION['user'];

$labels = [
  'nicht_bearbeitet' => 'nicht bearbeitet',
  'korrekt'          => 'bearbeitet korrekt',
  'nicht_korrekt'    => 'bearbeitet nicht korrekt'
];

if ($user['role'] === 'student') {
  $stmt = $pdo->prepare("
    SELECT t.id, t.title, t.html_file, COALESCE(st.status,'nicht_bearbeitet') AS status, st.updated_at
    FROM tasks t
    LEFT JOIN student_tasks st
      ON st.task_id = t.id AND st.user_id = :uid
    ORDER BY t.id
  ");
  $stmt->execute([':uid'=>$user['id']]);
  $rows = $stmt->fetchAll();
  ?>
  <!doctype html><html lang="de"><head>
    <meta charset="utf-8"><title>Meine Aufgaben</title>
    <link rel="stylesheet" href="../CSS/01_main.css">
    <link rel="stylesheet" href="../CSS/addons_login.css">
  </head><body><div class="container">
    <div class="hero">
      <h1>Meine Aufgaben</h1><hr><p>Status pro Aufgabe wählen</p>
    </div>

    <table class="table">
      <thead><tr><th>Aufgabe</th><th>Status</th><th>Aktionen</th></tr></thead>
      <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['title']) ?></td>
          <td><span class="badge <?= htmlspecialchars($r['status']) ?>"><?= htmlspecialchars($labels[$r['status']]) ?></span></td>
          <td style="display:flex; gap:8px;">
            <?php if (!empty($r['html_file'])): ?>
              <a class="btn ghost" href="../html/<?= htmlspecialchars($r['html_file']) ?>" target="_blank">Aufgabe öffnen</a>
            <?php endif; ?>
            <form action="update_status.php" method="post" class="form" style="box-shadow:none;padding:0;background:transparent;margin:0;">
              <input type="hidden" name="task_id" value="<?= (int)$r['id'] ?>">
              <select name="status" required>
                <?php foreach (['nicht_bearbeitet','korrekt','nicht_korrekt'] as $s): ?>
                  <option value="<?= $s ?>" <?= $s===$r['status']?'selected':'' ?>><?= $labels[$s] ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn primary" style="margin-left:8px;">Speichern</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <div class="toolbar"><a class="btn ghost" href="01_main.php">Zurück</a></div>
  </div></body></html>
  <?php exit; }

// Teacher view
$filter = trim($_GET['student_number'] ?? '');
$params = [];
$where = "WHERE u.role = 'student'";
if ($filter !== '') { $where .= " AND u.student_number = :sn"; $params[':sn']=$filter; }

$sql = "
  SELECT u.id AS uid, u.student_number, u.name,
         t.id AS task_id, t.title,
         COALESCE(st.status,'nicht_bearbeitet') AS status
  FROM users u
  JOIN tasks t
  LEFT JOIN student_tasks st ON st.user_id = u.id AND st.task_id = t.id
  $where
  ORDER BY u.student_number, t.id
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$byStudent = [];
foreach($rows as $r){
  $sid = $r['uid'];
  if(!isset($byStudent[$sid])){
    $byStudent[$sid] = [
      'student_number'=>$r['student_number'],
      'name'=>$r['name'],
      'cnt'=>['nicht_bearbeitet'=>0,'korrekt'=>0,'nicht_korrekt'=>0],
      'total'=>0
    ];
  }
  $byStudent[$sid]['cnt'][$r['status']]++;
  $byStudent[$sid]['total']++;
}
?>
<!doctype html><html lang="de"><head>
  <meta charset="utf-8"><title>Lehrer-Übersicht</title>
  <link rel="stylesheet" href="../CSS/01_main.css">
  <link rel="stylesheet" href="../CSS/addons_login.css">
</head><body><div class="container">
  <div class="hero">
    <h1>Fortschritt Übersicht</h1><hr><p>Zusammenfassung pro Schüler</p>
  </div>

  <form class="form" method="get" style="max-width:560px;">
    <div class="form-field">
      <label>Schülernummer filtern</label>
      <input name="student_number" value="<?= htmlspecialchars($filter) ?>">
    </div>
    <button class="btn primary">Filtern</button>
    <a class="btn ghost" href="dashboard.php">Zurücksetzen</a>
  </form>

  <table class="table">
    <thead><tr><th>Schüler-Nr.</th><th>Name</th><th>Gesamt</th><th>nicht bearbeitet</th><th>korrekt</th><th>nicht korrekt</th></tr></thead>
    <tbody>
    <?php foreach($byStudent as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['student_number']) ?></td>
        <td><?= htmlspecialchars($s['name']) ?></td>
        <td><?= (int)$s['total'] ?></td>
        <td><span class="badge nicht_bearbeitet"><?= (int)$s['cnt']['nicht_bearbeitet'] ?></span></td>
        <td><span class="badge korrekt"><?= (int)$s['cnt']['korrekt'] ?></span></td>
        <td><span class="badge nicht_korrekt"><?= (int)$s['cnt']['nicht_korrekt'] ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <div class="toolbar"><a class="btn ghost" href="01_main.php">Zurück</a></div>
</div></body></html>
