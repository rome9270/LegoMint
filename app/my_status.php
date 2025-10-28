<?php
session_start();
require __DIR__ . '/db.php';

if (empty($_SESSION['user'])) {
    header('Location: login.html');
    exit;
}

$user = $_SESSION['user'];

// Nutzer-ID aus Session holen (robust)
$uid = null;
if (isset($user['id'])) {
    $uid = $user['id'];
} elseif (isset($user['user_id'])) {
    $uid = $user['user_id'];
}

// Falls immer noch leer: Wir können trotzdem Status anzeigen,
// nur ohne student_tasks-Infos. Wir setzen dann $uid = -1,
// dann matched das LEFT JOIN eh nicht und wir sehen status = null.
if ($uid === null) {
    $uid = -1;
}

// Helper: DB-Lader nach Kategorie
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
          ON st.task_id = t.id AND st.user_id = :uid
        WHERE t.category = :cat
        ORDER BY t.id
    ");
    $stmt->execute([
        ':uid' => $uid,
        ':cat' => $category
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$ev3Rows    = loadTasksForCategory($pdo, $uid, 'ev3');
$pythonRows = loadTasksForCategory($pdo, $uid, 'python');

// Formatter
function fmtStatus($s) {
    return $s !== null ? $s : 'nicht_bearbeitet';
}
function fmtTime($ts) {
    if (!$ts) return '-';
    $dt = new DateTime($ts, new DateTimeZone('Europe/Berlin'));
    return $dt->format('d.m.Y, H:i') . ' Uhr';
}
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mein Status</title>

<style>
body {
    font-family: system-ui, sans-serif;
    background:#fff;
    color:#111;
    max-width:1200px;
    margin:20px auto 80px auto;
    padding:0 16px;
    line-height:1.4;
}
.top-nav {
    display:flex;
    gap:1rem;
    align-items:baseline;
    font-size:0.95rem;
    margin-bottom:1.5rem;
}
.top-nav a.btn-nav {
    background:#34a853;
    color:#fff;
    font-weight:600;
    padding:.5rem .75rem;
    border-radius:.5rem;
    text-decoration:none;
}
h1 {
    font-size:2rem;
    font-weight:600;
    margin:0 0 .75rem 0;
}
.section-head {
    font-size:1.25rem;
    font-weight:600;
    margin:2rem 0 .5rem 0;
}
.status-table {
    width:100%;
    border-collapse:collapse;
    font-size:1rem;
    margin-bottom:2rem;
}
.status-table th {
    text-align:left;
    background:#f9fafb;
    border-bottom:1px solid #d1d5db;
    font-weight:600;
    padding:.75rem;
}
.status-table td {
    border-bottom:1px solid #e5e7eb;
    padding:.75rem;
    vertical-align:top;
    font-size:1rem;
}
.note {
    font-size:.9rem;
    color:#666;
    margin-bottom:1rem;
    font-style:italic;
}
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
    <div class="note">Keine EV3-Aufgaben gefunden. (Evtl. nicht eingeloggt mit ID?)</div>
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

<!-- Python -->
<div class="section-head">Python</div>
<?php if (count($pythonRows) === 0): ?>
    <div class="note">Keine Python-Aufgaben gefunden. (Evtl. nicht eingeloggt mit ID?)</div>
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
