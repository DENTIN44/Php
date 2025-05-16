<?php
require_once 'Models/Database.php';
require_once 'Models/PasswordReset.php';

// Handle link open (GET)
if (isset($_GET['key']) && isset($_GET['email']) && isset($_GET['action']) && $_GET['action'] == 'reset') {
    $key = $_GET['key'];
    $email = $_GET['email'];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<h2>Invalid email address</h2>";
        exit;
    }

    $db = new Database(realpath(__DIR__ . '/../'));
    $passwordReset = new PasswordReset($db, $email, $key);

    if (!$passwordReset->isLinkValid()) {
        echo "<h2>Invalid or Expired Link</h2>";
    } else {
        include 'Views/reset_pasword.php';
    }
    exit; // Ensure GET does not collide with POST
}

// Handle form submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $key = $_POST['key'] ?? '';
    $email = $_POST['email'] ?? '';
    $pass1 = $_POST['pass1'] ?? '';
    $pass2 = $_POST['pass2'] ?? '';
    $error = '';

    if (empty($key) || empty($email)) {
        echo "<p>Invalid request. Missing key or email.</p>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<p>Invalid email address.</p>";
        exit;
    }

    if ($pass1 !== $pass2) {
        $error .= "<p>Password mismatch. Please ensure both passwords match.</p>";
    }

    if (strlen($pass1) < 8) {
        $error .= "<p>Password must be at least 8 characters long.</p>";
    }

    if ($error === '') {
        $db = new Database(realpath(__DIR__ . '/../'));
        $passwordReset = new PasswordReset($db, $email, $key);

        if (!$passwordReset->isLinkValid()) {
            echo "<p>Invalid or expired token.</p>";
            exit;
        }

        if ($passwordReset->updatePassword($pass1)) {
            echo '<p>Password updated successfully! <a href="login.php">Login here</a></p>';
        } else {
            echo "<p>Error updating password. Please try again later.</p>";
        }
    } else {
        echo $error;
    }
}
?>
