<?php
require_once 'Models/Database.php';
require_once 'Models/PasswordResetRequest.php';

if (isset($_POST["email"]) && !empty($_POST["email"])) {
    $email = $_POST["email"];
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        $error = "<p>Invalid email address, please enter a valid email address!</p>";
    } else {
        // Create Database instance
        $db = new Database(__DIR__ . '/.env');
        $passwordReset = new PasswordResetRequest($db, $email);

        if (!$passwordReset->isEmailValid()) {
            $error = "<p>No user is registered with this email address!</p>";
        } else {
            $key = $passwordReset->generateResetKey();
            $expFormat = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y"));
            $expDate = date("Y-m-d H:i:s", $expFormat);

            // Store the key in the database
            $passwordReset->storeResetKey($key, $expDate);

            // Send the email
            $message = $passwordReset->sendResetEmail($key);
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
