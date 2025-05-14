<h1>Welcome, <?=htmlspecialchars($_SESSION['user']['username'])?></h1>
<p><a href="logout.php">Log out</a></p>
<h2>All users</h2>
<ul>
  <?php foreach($users as $u): ?>
    <li><?=htmlspecialchars($u['username'])?>(<?=htmlspecialchars($u['email'])?>)</li>
  <?php endforeach; ?>
</ul>
