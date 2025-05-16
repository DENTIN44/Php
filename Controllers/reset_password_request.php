<?php
require_once 'Models/Database.php';
require_once 'Models/PasswordReset.php';

if (isset($_POST["email"]) && !empty($_POST["email"])) {
    // Sanitize and validate email
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "<p>Invalid email address, please enter a valid email address!</p>";
    } else {
        // Create Database instance
        $db = new Database(realpath(__DIR__ . '/../'));  // path to your .env folder

        // Create PasswordReset instance
        $passwordReset = new PasswordReset($db, $email);

        // Check if email exists in your users table
        if (!$passwordReset->isEmailValid()) {
            $error = "<p>No user is registered with this email address!</p>";
        } else {
            // Generate and store token with expiration
            $token = $passwordReset->generateResetKey();

            // Send the reset email with the token
            $message = $passwordReset->sendResetEmail($token);
        }
    }

    if (isset($error)) {
        echo "<div class='error'>$error</div><br /><a href='javascript:history.go(-1)'>Go Back</a>";
    } else {
        echo "<div class='success'>$message</div><br /><br />";
    }
} else {
    // Show reset form
    ?>
    <form method="post" action="" name="reset">
        <br /><br />
        <label><strong>Enter Your Email Address:</strong></label><br /><br />
        <input type="email" name="email" placeholder="username@email.com" required />
        <br /><br />
        <input type="submit" value="Reset Password"/>
    </form>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <?php
}
?>
