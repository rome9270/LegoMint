<?php
// app/02_LegoEV3_I.php
// Übersicht LEGO EV3 Aufgaben im alten Layout mit deinem gewünschten Verhalten

session_start();
require __DIR__ . '/db.php';

if (empty($_SESSION['user'])) {
    header('Location: login.html');
    exit;
}

// Aufgaben aus DB holen
$sql = "SELECT id, title, html_file, level
        FROM tasks
        WHERE category = 'ev3'
        ORDER BY id";
$allTasks = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Aufteilung
$basicTasks     = array_filter($allTasks, fn($t) => $t['level'] === 'basic');
$advancedTasks  = array_filter($allTasks, fn($t) => $t['level'] === 'advanced');

// Helfer für Nummern
function fmt_id($n){ return str_pad($n, 2, '0', STR_PAD_LEFT); }

$user = $_SESSION['user'];
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EV3 Aufgaben</title>

<style>
body {
    font-family: system-ui, sans-serif;
    background: #fff;
    color: #111;
    max-width: 1200px;
    margin: 20px auto 80px auto;
    padding: 0 16px;
    line-height: 1.4;
}

/* Navigation */
.top-nav {
    display: flex;
    gap: 1rem;
    align-items: baseline;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
}
.top-nav a.btn-nav {
    background: #34a853;
    color: #fff;
    font-weight: 600;
    padding: .5rem .75rem;
    border-radius: .5rem;
    text-decoration: none;
}

/* Grüne Kursbuttons */
.course-row {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
    justify-content: center;
    margin-bottom: 2rem;
}
.course-pill {
    background: #34a853;
    color: #fff;
    font-size: 1.2rem;
    line-height: 1.3;
    font-weight: 600;
    padding: 1rem 2rem;
    border-radius: 2rem;
    text-align: center;
    min-width: 320px;
    text-decoration: none;
    display: inline-block;
}

/* Accordion */
.accordion-box {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #f9fafb;
    margin-bottom: 1.5rem;
}
.accordion-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1rem;
    font-size: 1.25rem;
    font-weight: 500;
    color: #111;
    cursor: pointer;
    user-select: none;
}
.accordion-head .arrow {
    font-size: 1rem;
    font-weight: 600;
    color: #000;
}
.accordion-body {
    background: #fff;
    border-top: 1px solid #d1d5db;
}

/* Tasks */
.task-row {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    column-gap: 1rem;
    padding: 1rem 1rem;
    border-bottom: 1px solid #e5e7eb;
}
.task-num {
    min-width: 2ch;
    font-family: ui-monospace, monospace;
    font-size: 1.1rem;
    font-weight: 600;
    color: #111;
}
.task-title {
    font-size: 1.1rem;
    font-weight: 500;
    color: #111;
    line-height: 1.4;
}
.open-btn {
    background: #e0e7ff;
    color: #1e1e5a;
    border: 1px solid #c7d2fe;
    border-radius: 8px;
    padding: .6rem .9rem;
    font-size: 1rem;
    font-weight: 500;
    text-decoration: none;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: .5em;
    justify-content: center;
}
</style>

<script>
function toggleBox(headId, bodyId) {
    const body = document.getElementById(bodyId);
    const arrow = document.querySelector('#' + headId + ' .arrow');
    const open = body.getAttribute('data-open') === '1';

    if (open) {
        body.style.display = 'none';
        body.setAttribute('data-open', '0');
        arrow.textContent = '▼';
    } else {
        body.style.display = 'block';
        body.setAttribute('data-open', '1');
        arrow.textContent = '▲';
    }
}
</script>
</head>

<body>
<nav class="top-nav">
    <a class="btn-nav" href="01_main.php">⬅ Main</a>
    <a class="btn-nav" href="logout.php">🚪 Logout</a>
    <span style="margin-left:auto">
        Angemeldet als: <b><?= htmlspecialchars($user['name']) ?></b>
        (<?= htmlspecialchars($user['role']) ?>)
    </span>
</nav>

<!-- Grüne Buttons oben -->
<div class="course-row">
    <!-- Button 1: direkt zur ersten HTML (Basic) -->
    <a class="course-pill" href="../html/03_01LegoEV3_I.html">EV3 Basic Course (Grundkurs)</a>
    <!-- Button 2: direkt zur HTML 11 (Advanced) -->
    <a class="course-pill" href="../html/04_01LegoEV3_I.html">EV3 Advanced Course (Oberstufe)</a>
</div>

<!-- BASIC COURSE -->
<section class="accordion-box">
    <div class="accordion-head" id="basic-head"
         onclick="toggleBox('basic-head','basic-body')">
        <span>Basic Course (Grundkurs)</span>
        <span class="arrow">▼</span>
    </div>

    <!-- standardmäßig eingeklappt -->
    <div class="accordion-body" id="basic-body" data-open="0" style="display:none;">
        <?php foreach ($basicTasks as $t): ?>
            <?php
                $num   = fmt_id($t['id']);
                $title = $t['title'];
                $href  = '../html/' . $t['html_file'];
            ?>
            <div class="task-row">
                <div class="task-num"><?= htmlspecialchars($num) ?></div>
                <div class="task-title"><?= htmlspecialchars($title) ?></div>
                <a class="open-btn" href="<?= htmlspecialchars($href) ?>">Open task →</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ADVANCED COURSE -->
<section class="accordion-box">
    <div class="accordion-head" id="adv-head"
         onclick="toggleBox('adv-head','adv-body')">
        <span>Advanced Course (Oberstufe)</span>
        <span class="arrow">▼</span>
    </div>

    <!-- standardmäßig eingeklappt -->
    <div class="accordion-body" id="adv-body" data-open="0" style="display:none;">
        <?php foreach ($advancedTasks as $t): ?>
            <?php
                $num   = fmt_id($t['id']);
                $title = $t['title'];
                $href  = '../html/' . $t['html_file'];
            ?>
            <div class="task-row">
                <div class="task-num"><?= htmlspecialchars($num) ?></div>
                <div class="task-title"><?= htmlspecialchars($title) ?></div>
                <a class="open-btn" href="<?= htmlspecialchars($href) ?>">Open task →</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

</body>
</html>
