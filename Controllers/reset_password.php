<?php
require_once 'Models/Database.php';
require_once 'Models/PasswordReset.php';

// Start by checking the URL parameters
if (isset($_GET['key']) && isset($_GET['email']) && isset($_GET['action']) && $_GET['action'] == 'reset') {
    $key = $_GET['key'];
    $email = $_GET['email'];
    
    // Make sure the email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<h2>Invalid email address</h2>";
        exit;
    }

    // Connect to the database
    $db = new Database('localhost', 'username', 'password', 'dbname'); // Use environment variables or a config file here
    $passwordReset = new PasswordReset($db, $email, $key);
    
    if (!$passwordReset->isLinkValid()) {
        echo "<h2>Invalid or Expired Link</h2>";
    } else {
        // Render the password reset form
        include 'Views/reset_password_form.php';
    }
}

// Handle form submission for password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $email = $_POST['email'];
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];
    $error = '';

    // Ensure passwords match
    if ($pass1 !== $pass2) {
        $error .= "<p>Password mismatch. Please ensure both passwords match.</p>";
    }

    // Password validation (optional, add as needed)
    if (strlen($pass1) < 8) {
        $error .= "<p>Password must be at least 8 characters long.</p>";
    }

    if ($error === '') {
        $db = new Database('localhost', 'username', 'password', 'dbname');
        $passwordReset = new PasswordReset($db, $email, null); // key is not required now
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
