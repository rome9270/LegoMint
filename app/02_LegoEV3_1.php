<?php
// html/02_LegoEV3_I.php

session_start();

// Versuche DB zu laden, aber fang Fehler ab
$tasks = [];
$dbError = null;
try {
    require __DIR__ . '/../app/db.php'; // erwartet $pdo
    if (!isset($pdo)) {
        throw new Exception("DB connection (\$pdo) not available");
    }

    $stmt = $pdo->query("SELECT id, title, html_file FROM tasks ORDER BY id");
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

// Hilfsfunktion für ID-Anzeige
function format_id($n) {
    $n = (int)$n;
    if ($n < 10) return '0' . $n;
    return (string)$n;
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Aufgabenübersicht</title>

  <!-- Styling -->
  <link rel="stylesheet" href="./python/03_python.css" />
  <style>
    .task-list-wrapper {
      max-width: 900px;
      margin: 20px auto 60px auto;
    }
    .task-grid {
      display: grid;
      gap: 12px;
    }
    .task-row {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: 12px 14px;
      box-shadow: 0 8px 24px rgb(0 0 0 / 0.03);
    }
    .task-left {
      font-size: .95rem;
      color: #111827;
      line-height: 1.4;
      display: flex;
      flex-wrap: wrap;
      align-items: baseline;
      gap: 8px;
      font-weight: 500;
    }
    .task-id {
      display: inline-block;
      min-width: 2ch;
      font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
      background: #111827;
      color: #fff;
      font-size: .8rem;
      line-height: 1.2;
      padding: 2px 6px;
      border-radius: 6px;
      font-weight: 600;
    }
    .open-btn {
      text-decoration: none;
      font-size: .8rem;
      font-weight: 500;
      background: #e0e7ff;
      color: #1e1e5a;
      border: 1px solid #c7d2fe;
      border-radius: 8px;
      padding: 8px 12px;
      display: inline-flex;
      align-items: center;
      gap: .5em;
    }
    nav.link-grid {
      max-width: 900px;
      margin: 20px auto;
      justify-content: space-between;
    }
    header.page-head {
      max-width: 900px;
      margin: 20px auto;
    }
    header.page-head h1 {
      margin: 0 0 .5rem 0;
      font-size: 1.4rem;
      font-weight: 600;
      color: #111827;
      line-height: 1.2;
    }
    header.page-head p {
      margin: 0;
      color: #374151;
      font-size: .9rem;
      line-height: 1.4;
    }
    footer {
      max-width:900px;
      margin:40px auto 60px auto;
      text-align:center;
      font-size:.8rem;
      color:#6b7280;
    }
  </style>
</head>
<body class="page-width">

  <nav class="link-grid" aria-label="Topic selection">
    <a class="btn" href="01_main.php">⬅️ Main</a>
    <a class="btn" href="logout.php">🚪 Logout</a>
    <a class="btn primary" href="./python/02_python_overview.html">Python Overview</a>
  </nav>

  <header class="page-head">
    <h1>Aufgabenübersicht</h1>
    <p>Wähle eine Aufgabe. Dein Fortschritt wird automatisch gespeichert.</p>

    <?php if ($dbError): ?>
      <p style="color:#dc2626; font-size:.9rem; font-weight:600;">
        Achtung: Konnte DB nicht laden: <?= htmlspecialchars($dbError) ?>
      </p>
    <?php endif; ?>
  </header>

  <section class="task-list-wrapper">
    <div class="task-grid">
      <?php if (!$dbError && $tasks): ?>
        <?php foreach ($tasks as $t): ?>
          <?php
            $shownId = format_id($t['id']);
            // wir sind in /html/ → also "./" + dateiname aus der DB
            $href = './' . $t['html_file'];
          ?>
          <div class="task-row">
            <div class="task-left">
              <span class="task-id"><?= htmlspecialchars($shownId) ?></span>
              <div><?= htmlspecialchars($t['title']) ?></div>
            </div>
            <a class="open-btn" href="<?= htmlspecialchars($href) ?>">🔓 Open</a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="task-row">
          <div class="task-left">
            <span class="task-id">!!</span>
            <div>Keine Aufgaben gefunden.</div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <footer>
    <div>Digital Education Environment</div>
  </footer>

</body>
</html>
