<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'Models/Database.php';
require_once 'Models/PasswordReset.php';

// Initialize variables based on request method
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $email = $_GET['email'] ?? '';
    $key = $_GET['key'] ?? '';
} else { // POST
    $email = $_POST['email'] ?? '';
    $key = $_POST['key'] ?? '';
}

$error = '';
$success = '';

var_dump($email, $key);

// 1. Check link validity only if GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($email === '' || $key === '') {
        $error = "<h2>Invalid or Expired Link</h2>";
    } else {
        $db = new Database(realpath(__DIR__));
        $passwordReset = new PasswordReset($db, $email, $key);

        if (!$passwordReset->isLinkValid()) {
            $error = "<h2>Invalid or Expired Link</h2>";
        }
    }
}

// 2. Handle form submission (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $pass1 = $_POST['pass1'] ?? '';
    $pass2 = $_POST['pass2'] ?? '';

    // Validate passwords
    if ($pass1 !== $pass2) {
        $error .= "<p>Passwords don't match!</p>";
    } elseif (strlen($pass1) < 8) {
        $error .= "<p>Password must be at least 8 characters long.</p>";
    }

    if (empty($error)) {
        $db = new Database(realpath(__DIR__));
        $passwordReset = new PasswordReset($db, $email, $key);

        if ($passwordReset->updatePassword($pass1)) {
            $success = '<p>Password updated! <a href="login.php">Login here</a></p>';
        } else {
            $error = "<p>Update failed. Please try again later.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <?php if (!empty($error)) { ?>
        <div class="error"><?php echo $error; ?></div>
    <?php } elseif (!empty($success)) { ?>
        <div class="success"><?php echo $success; ?></div>
    <?php } ?>

    <?php if (empty($success) && empty($error) || !empty($error)) { ?>
        <form method="post" action="reset-password.php?email=<?php echo urlencode($email); ?>&key=<?php echo urlencode($key); ?>">
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>" />
    <input type="hidden" name="key" value="<?php echo htmlspecialchars($key); ?>" />
    
    <label><strong>New Password:</strong></label><br>
    <input type="password" name="pass1" required minlength="8"><br><br>
    
    <label><strong>Confirm Password:</strong></label><br>
    <input type="password" name="pass2" required minlength="8"><br><br>
    
    <input type="submit" value="Reset Password">
</form>
    <?php } ?>
</body>
</html>
