<?php
// app/02_LegoEV3_I.php

session_start();
require __DIR__ . '/../app/db.php';

// Alle Aufgaben aus DB holen
// tasks: id | title | html_file
$stmt = $pdo->query("SELECT id, title, html_file FROM tasks ORDER BY id");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// kleine Hilfsfunktion: zweistellige ID fürs Frontend, z.B. 1 -> "01"
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

  <!-- Styles laden -->
  <!-- Wir sind hier unter html/, und du hast 03_python.css unter html/python/ -->
  <!-- Du hast 2 Optionen:
       1) Du kopierst 03_python.css zusätzlich nach html/ und bindest "./03_python.css" ein
       2) Du bindest die Version aus dem python-Ordner ein: "./python/03_python.css"
     Ich nehme hier Variante 2. -->
  <link rel="stylesheet" href="./python/03_python.css" />

  <style>
    /* kleine Zusatzkosmetik speziell für diese Seite */
    .task-list-wrapper {
      max-width: 900px;
      margin: 20px auto 60px auto;
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

    .task-grid {
      display: grid;
      gap: 12px;
    }

    header.page-head {
      max-width: 900px;
      margin: 20px auto;
    }

    header.page-head h1 {
      margin: 0 0 0.5rem 0;
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

    nav.link-grid {
      max-width: 900px;
      margin: 20px auto;
      justify-content: space-between;
    }
  </style>
</head>
<body class="page-width">

  <!-- Top-Navigation wie bei den anderen Seiten -->
  <nav class="link-grid" aria-label="Topic selection">
    <!-- wir sind in /html/, also zwei Ebenen hoch zu app/ -->
    <a class="btn" href="../app/01_main.php" aria-label="Back to main">⬅️ Main</a>
    <a class="btn" href="../app/logout.php">🚪 Logout</a>
    <a class="btn primary" href="./python/02_python_overview.html">Python Overview</a>
  </nav>

  <header class="page-head">
    <h1>Aufgabenübersicht</h1>
    <p>Wähle eine Aufgabe. Dein Fortschritt wird automatisch gespeichert, wenn du die Aufgabe ausführst oder speicherst.</p>
  </header>

  <section class="task-list-wrapper">
    <div class="task-grid">
      <?php foreach ($tasks as $task): ?>
        <?php
          // HTML-Datei aus DB, z.B. "03_01LegoEV3_I.html" oder "python/03_1python.html"
          // Wir bauen einen sauberen relativen Link daraus.
          // Wir sind aktuell in /html/02_LegoEV3_I.php
          // -> also relative Links zu /html/... sind einfach "./" + dateiname
          $href = './' . $task['html_file'];

          // ID hübsch formatiert
          $shownId = format_id($task['id']);

          // Titel
          $title = $task['title'];
        ?>

        <div class="task-row">
          <div class="task-left">
            <span class="task-id"><?= htmlspecialchars($shownId) ?></span>
            <div><?= htmlspecialchars($title) ?></div>
          </div>

          <a class="open-btn" href="<?= htmlspecialchars($href) ?>">
            🔓 Open
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <footer style="max-width:900px;margin:40px auto 60px auto;text-align:center;font-size:.8rem;color:#6b7280;">
    <div>Digital Education Environment</div>
  </footer>

</body>
</html>
