<?php
// Views/reset_password_form.php
?>
<form method="post" action="">
    <input type="hidden" name="action" value="update" />
    <label for="pass1"><strong>Enter New Password:</strong></label><br />
    <input type="password" name="pass1" maxlength="15" required /><br /><br />
    
    <label for="pass2"><strong>Re-Enter New Password:</strong></label><br />
    <input type="password" name="pass2" maxlength="15" required /><br /><br />
    
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>" />
    <input type="submit" value="Reset Password" />
</form>
