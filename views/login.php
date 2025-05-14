<?php if(!empty($error)): ?><p style="color:red"><?=htmlspecialchars($error)?></p><?php endif; ?>
<form method="post" action="login.php">
  <label>Email:    <input name="email"    type="email"></label><br>
  <label>Passwordd: <input name="password" type="password"></label><br>
  <button type="submit">Log In</button>
</form>
<p>No account? <a href="register.php">Sign up</a></p>
