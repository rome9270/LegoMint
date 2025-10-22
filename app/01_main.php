<?php
// app/01_main.php
session_start();
require __DIR__.'/db.php';

// Nur eingeloggte Nutzer dürfen hier rein
if (empty($_SESSION['user'])) {
  header('Location: login.html');
  exit;
}

$user = $_SESSION['user']; // ['name','role',...]
?>
<!doctype html>
<meta charset="utf-8">
<link rel="stylesheet" href="../CSS/01_main.css">
<link rel="stylesheet" href="../CSS/addons_login.css"><!-- falls vorhanden -->

<body>
  <h1>Digital Education – Discover. Develop. Understand.</h1>
  <p>Digital applications for modern lesson development in all subjects</p>

  <!-- ===== Toolbar / Dashboard ===== -->
  <div class="toolbar" style="display:flex;gap:12px;align-items:center;justify-content:center;margin:18px 0;">
    <span>Angemeldet als: <b><?= htmlspecialchars($user['name']) ?></b> (<?= htmlspecialchars($user['role']) ?>)</span>
    <a class="btn" href="my_status.php">Dashboard</a>   <!-- oder dashboard.php, falls du die große Variante hast -->
    <a class="btn" href="logout.php">Logout</a>
  </div>

  <hr>

  <h2>Choose your topic</h2>
  <div class="link-grid">
    <a class="level-btn" href="/LegoMint/html/02_python_overview.html">Python</a>
    <a class="level-btn" href="/LegoMint/html/03_01LegoEV3.html">Lego EV3</a>
  </div>
</body>
