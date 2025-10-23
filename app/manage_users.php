<?php
require __DIR__.'/db.php';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['del'])) {
  $pdo->prepare("DELETE FROM users WHERE id=:id")->execute([':id'=>(int)$_POST['del']]);
}

$rows = $pdo->query("SELECT id, student_number, name, role FROM users ORDER BY role, student_number")->fetchAll();
?>
<!doctype html><meta charset="utf-8">
<h1>Benutzerverwaltung</h1>
<table border="1" cellpadding="6">
<tr><th>ID</th><th>Nummer</th><th>Name</th><th>Rolle</th><th>Aktion</th></tr>
<?php foreach($rows as $r): ?>
<tr>
  <td><?= $r['id'] ?></td>
  <td><?= htmlspecialchars($r['student_number']) ?></td>
  <td><?= htmlspecialchars($r['name']) ?></td>
  <td><?= htmlspecialchars($r['role']) ?></td>
  <td>
    <form method="post" style="display:inline">
      <button name="del" value="<?= $r['id'] ?>" onclick="return confirm('Löschen wirklich?')">🗑️</button>
    </form>
  </td>
</tr>
<?php endforeach ?>
</table>
