<?php
// my_status.php
session_start();
require __DIR__ . '/db.php';

// --- Helper: Sicherstellen, dass User eingeloggt ist ---
if (empty($_SESSION['user'])) {
    header('Location: login.html');
    exit;
}

$user = $_SESSION['user'];

// Wir brauchen eine gültige user_id für student_tasks.
// login.php MUSS 'id' setzen: $_SESSION['user']['id'] = $row['id'];
$uid = isset($user['id']) ? $user['id'] : null;

// Fallback: Wenn es keinen user-id gibt (z.B. Demo Benutzer), wir machen -1.
// Das lässt den LEFT JOIN weiter funktionieren, nur gibt's dann halt keinen Eintrag in student_tasks.
if ($uid === null) {
    $uid = -1;
}

// Hilfsfunktionen für Anzeige
function fmtStatus($s) {
    if (!$s) return 'nicht_bearbeitet';
    return $s;
}
function fmtTime($t) {
    if (!$t) return '-';
    // nice formatting
    return date('d.m.Y, H:i', strtotime($t)) . ' Uhr';
}

// Lädt ALLE Tasks einer Kategorie (z.B. 'ev3' oder 'python')
function loadTasksForCategory(PDO $pdo, $uid, $category) {
    $stmt = $pdo->prepare("
        SELECT
            t.id,
            t.title,
            t.category,
            t.level,
            st.status,
            st.updated_at
        FROM tasks t
        LEFT JOIN student_tasks st
          ON st.task_id = t.id
         AND st.user_id = :uid
        WHERE t.category = :cat
        ORDER BY t.id ASC
    ");
    $stmt->execute([
        ':uid' => $uid,
        ':cat' => $category,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// echte Daten holen
$ev3Rows     = loadTasksForCategory($pdo, $uid, 'ev3');
$pythonRows  = loadTasksForCategory($pdo, $uid, 'python');
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Mein Status</title>
  <link rel="stylesheet" href="../CSS/01_main.css">
  <style>
    body { max-width: 1200px; margin: 20px auto; font-family: system-ui, sans-serif; line-height:1.4; }
    h1 { font-size:2.2rem; margin-bottom:0.5rem; }
    nav.top-nav { display:flex; gap:12px; align-items:center; margin-bottom:24px; }
    nav.top-nav .btn-nav { padding:6px 10px; border:1px solid #ccc; border-radius:8px; text-decoration:none; color:#111; background:#f3f4f6; font-size:0.95rem; }
    .section-head { font-size:1.5rem; font-weight:600; margin:32px 0 12px 0; }
    table.status-table { width:100%; border-collapse:collapse; background:#fff; box-shadow:0 2px 4px rgba(0,0,0,.04); border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; }
    table.status-table th { text-align:left; background:#f9fafb; font-weight:600; padding:12px 16px; border-bottom:1px solid #e5e7eb; font-size:1.1rem; }
    table.status-table td { padding:14px 16px; border-bottom:1px solid #e5e7eb; font-size:1.1rem; }
    .note { color:#6b7280; font-size:0.95rem; font-style:italic; padding:8px 0 16px; }
  </style>
</head>
<body>

<nav class="top-nav">
    <a class="btn-nav" href="01_main.php">⬅ Main</a>
    <a class="btn-nav" href="logout.php">🚪 Logout</a>
    <span style="margin-left:auto">
        Angemeldet als:
        <b><?= htmlspecialchars($user['name'] ?? '???') ?></b>
        (<?= htmlspecialchars($user['role'] ?? '???') ?>)
    </span>
</nav>

<h1>Mein Status</h1>

<!-- EV3 -->
<div class="section-head">EV3</div>

<?php if (count($ev3Rows) === 0): ?>
    <div class="note">Keine EV3-Aufgaben gefunden.</div>
<?php else: ?>
<table class="status-table">
    <tr>
        <th style="width:50%">Aufgabe</th>
        <th style="width:25%">Status</th>
        <th style="width:25%">Zuletzt geändert</th>
    </tr>
    <?php foreach ($ev3Rows as $row): ?>
    <tr>
        <td><?= htmlspecialchars($row['title']) ?></td>
        <td><?= htmlspecialchars(fmtStatus($row['status'])) ?></td>
        <td><?= htmlspecialchars(fmtTime($row['updated_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>


<!-- PYTHON -->
<div class="section-head">Python</div>

<?php if (count($pythonRows) === 0): ?>
    <div class="note">Keine Python-Aufgaben gefunden.</div>
<?php else: ?>
<table class="status-table">
    <tr>
        <th style="width:50%">Aufgabe</th>
        <th style="width:25%">Status</th>
        <th style="width:25%">Zuletzt geändert</th>
    </tr>
    <?php foreach ($pythonRows as $row): ?>
    <tr>
        <td><?= htmlspecialchars($row['title']) ?></td>
        <td><?= htmlspecialchars(fmtStatus($row['status'])) ?></td>
        <td><?= htmlspecialchars(fmtTime($row['updated_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>
