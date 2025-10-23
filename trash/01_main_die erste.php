<?php
session_start();
if (empty($_SESSION['user'])) { header("Location: login.html"); exit; }
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content="Digital applications for modern lesson development" />
  <title>Digital Education</title>
  <link rel="stylesheet" href="../CSS/01_main.css">
  <link rel="stylesheet" href="../CSS/addons_login.css">
</head>

<body>
  <h1>Digital Education – Discover. Develop. Understand.</h1>
  <p>Digital applications for modern lesson development in all subjects</p>
  <div class="toolbar">
    <span>Angemeldet als: <strong><?= htmlspecialchars($user['name']) ?></strong> (<?= htmlspecialchars($user['role']) ?>)</span>
    <a class="btn ghost" href="dashboard.php">Dashboard</a>
    <a class="btn ghost" href="logout.php">Logout</a>
  </div>
  <hr>

  <h2>Choose your topic</h2>
  <div class="link-grid">
    <!-- Link to your existing lesson pages in /html -->
    <a href="../html/02_python_overview.html" class="level-btn">Python</a>
    <a href="../html/02_LegoEV3.html" class="level-btn">Lego EV3</a>
  </div>
</body>
</html>
