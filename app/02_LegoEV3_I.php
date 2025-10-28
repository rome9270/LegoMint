<?php
session_start();
require __DIR__ . '/../app/db.php';

/*
  Filterung EV3-Aufgaben

*/
$stmt = $pdo->query("
    SELECT id, title, html_file
    FROM tasks
    WHERE html_file NOT LIKE 'python/%'
    ORDER BY id
");
$allTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Aufteilung in Basic/Advanced anhand der ID's */
$mid = intdiv(count($allTasks), 2);
$basicTasks = array_filter($allTasks, fn($t) => $t['id'] <= 10);
$advancedTasks = array_filter($allTasks, fn($t) => $t['id'] > 10);


function fmt_id($n){
    $n = (int)$n;
    return $n < 10 ? '0'.$n : (string)$n;
}
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>EV3 · Übersicht</title>
<link rel="stylesheet" href="./python/03_python.css" />

<style>
  body.page-width {
    max-width: 1100px;
    margin: 20px auto 60px auto;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  }

  .top-nav {
    font-size: 1rem;
    margin-bottom: 1.5rem;
    display:flex;
    flex-wrap:wrap;
    gap:1rem;
    align-items:center;
  }
  .top-nav a {
    text-decoration:none;
    color:#1e40af;
    font-weight:500;
    display:inline-flex;
    align-items:center;
    gap:.4em;
  }

  h1.page-title {
    text-align:center;
    font-size:2rem;
    font-weight:600;
    color:#111827;
    line-height:1.2;
    margin:0 0 1rem 0;
  }
  .page-subline {
    max-width:900px;
    margin:0 auto 2rem auto;
    text-align:center;
    color:#111827;
    font-size:1rem;
    line-height:1.4;
    border-bottom:1px solid #d1d5db;
    padding-bottom:1.5rem;
  }

  .topic-row {
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:2rem;
    margin:2rem auto 2rem auto;
  }
  .topic-btn {
    background:#34a853;
    color:#fff;
    font-size:1.25rem;
    line-height:1.3;
    padding:1rem 2rem;
    border-radius:2rem;
    font-weight:600;
    text-decoration:none;
    text-align:center;
    min-width:320px;
    box-shadow:0 12px 24px rgb(0 0 0 / .08);
  }
  .topic-btn:hover {
    filter:brightness(1.05);
  }

  .accordion-box {
    border:1px solid #d1d5db;
    border-radius:8px;
    background:#fff;
    box-shadow:0 24px 48px rgb(0 0 0 / .05);
    overflow:hidden;
    max-width:1600px;
    margin:0 auto 2rem auto;
  }

  .accordion-head {
    background:#f9fafb;
    font-size:1.5rem;
    font-weight:500;
    color:#111827;
    line-height:1.4;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:1rem 1.25rem;
    cursor:pointer;
    user-select:none;
    border-bottom:1px solid #d1d5db;
  }
  .accordion-head .arrow {
    font-size:1rem;
    font-weight:600;
    color:#000;
  }

  .task-row {
    display:grid;
    grid-template-columns: auto 1fr auto;
    align-items:center;
    column-gap:1rem;
    row-gap:0;
    padding:1rem 1.25rem;
    border-bottom:1px solid #d1d5db;
  }

  .task-num {
    min-width:2ch;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size:1.1rem;
    font-weight:600;
    color:#111827;
  }

  .task-title {
    font-size:1.25rem;
    line-height:1.4;
    color:#111827;
    font-weight:500;
  }

  .open-btn {
    background:#e0e7ff;
    color:#1e1e5a;
    border:1px solid #c7d2fe;
    border-radius:8px;
    padding:.75rem 1rem;
    font-size:1rem;
    font-weight:500;
    text-decoration:none;
    white-space:nowrap;
    display:inline-flex;
    align-items:center;
    gap:.5em;
    justify-content:center;
    min-width:9rem;
  }

  @media (max-width:700px){
    h1.page-title { font-size:1.5rem; }
    .accordion-head { font-size:1.2rem; }
    .task-title { font-size:1rem; }
    .topic-btn {
      min-width:240px;
      font-size:1rem;
      padding:.75rem 1rem;
    }
  }
</style>
<script>
function toggleBox(idHead, idBody){
  const body = document.getElementById(idBody);
  const head = document.getElementById(idHead);
  const arrow = head.querySelector('.arrow');
  const isOpen = body.getAttribute('data-open') === '1';
  if(isOpen){
    body.style.display = 'none';
    body.setAttribute('data-open','0');
    arrow.textContent = '▼';
  } else {
    body.style.display = 'block';
    body.setAttribute('data-open','1');
    arrow.textContent = '▲';
  }
}
</script>
</head>
<body class="page-width">

  <div class="top-nav">
    <a href="01_main.php">⬅️ Main</a>
    <a href="logout.php">Logout</a>
    <a href="02_python_overview.php">Python Übersicht</a>
  </div>

  <h1 class="page-title">Choose your topic</h1>
  <div class="page-subline"></div>

  <div class="topic-row">
    <a class="topic-btn" href="../html/03_01LegoEV3_I.html">
      EV3 Basic Course (Grundkurs)
    </a>
    <a class="topic-btn" href="../html/04_01LegoEV3_I.html">
      EV3 Advanced Course (Oberstufe)
    </a>
  </div>

  <!-- BASIC COURSE BOX -->
  <section class="accordion-box">

    <div class="accordion-head"
         id="basic-head"
         onclick="toggleBox('basic-head','basic-body')">
      <span>Basic Course (Grundkurs)</span>
      <span class="arrow">▲</span>
    </div>

    <div id="basic-body" data-open="1">
      <?php foreach ($basicTasks as $t): ?>
        <?php
          $num   = fmt_id($t['id']);
          $title = $t['title'];
          $href  = './' . $t['html_file']; // z.B. ./03_01LegoEV3_I.html
        ?>
        <div class="task-row">
          <div class="task-num"><?= htmlspecialchars($num) ?></div>
          <div class="task-title"><?= htmlspecialchars($title) ?></div>
          <a class="open-btn" href="<?= htmlspecialchars($href) ?>">Open task →</a>
        </div>
      <?php endforeach; ?>
    </div>

  </section>

  <!-- ADVANCED COURSE BOX -->
  <section class="accordion-box">

    <div class="accordion-head"
         id="adv-head"
         onclick="toggleBox('adv-head','adv-body')">
      <span>Advanced Course (Oberstufe)</span>
      <span class="arrow">▼</span>
    </div>

    <div id="adv-body" data-open="0" style="display:none;">
      <?php foreach ($advancedTasks as $t): ?>
        <?php
          $num   = fmt_id($t['id']);
          $title = $t['title'];
          $href  = './' . $t['html_file'];
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
