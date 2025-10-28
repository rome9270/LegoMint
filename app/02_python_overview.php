<?php
session_start();
require __DIR__ . '/../app/db.php';

/*
  Python-Übersicht
  ---------------------------
  Zeigt alle Python-Aufgaben aus tasks (category = 'python'),
  getrennt in basic / advanced per Spalte "level".
*/

$allTasks = $pdo->query("
    SELECT id, title, html_file, level
    FROM tasks
    WHERE category = 'python'
    ORDER BY id
")->fetchAll(PDO::FETCH_ASSOC);

$basicTasks = array_filter($allTasks, fn($t) => $t['level'] === 'basic');
$advancedTasks = array_filter($allTasks, fn($t) => $t['level'] === 'advanced');

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
<title>Python – Aufgabenübersicht</title>
<style>
body.page-width {
  max-width: 1100px;
  margin: 20px auto 60px auto;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
.arrow { font-size:1rem; font-weight:600; color:#000; }
.task-row {
  display:grid;
  grid-template-columns: auto 1fr auto;
  align-items:center;
  column-gap:1rem;
  padding:1rem 1.25rem;
  border-bottom:1px solid #d1d5db;
}
.task-num {
  min-width:2ch;
  font-family: ui-monospace, Menlo, Consolas, monospace;
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
</style>
<script>
function toggleBox(idHead, idBody){
  const body  = document.getElementById(idBody);
  const arrow = document.querySelector('#'+idHead+' .arrow');
  const open  = body.getAttribute('data-open') === '1';
  if(open){
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

  <div class="topic-row">
    <div class="topic-btn">Python Basic Course (Elementary school)</div>
    <div class="topic-btn">Python Advanced Course (Middle school)</div>
  </div>

  <!-- BASIC -->
  <section class="accordion-box">
    <div class="accordion-head" id="basic-head"
         onclick="toggleBox('basic-head','basic-body')">
      <span>Basic Course (Grundkurs)</span>
      <span class="arrow">▲</span>
    </div>

    <div id="basic-body" data-open="1">
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

  <!-- ADVANCED -->
  <section class="accordion-box">
    <div class="accordion-head" id="adv-head"
         onclick="toggleBox('adv-head','adv-body')">
      <span>Advanced Course (Oberstufe)</span>
      <span class="arrow">▼</span>
    </div>

    <div id="adv-body" data-open="0" style="display:none;">
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
