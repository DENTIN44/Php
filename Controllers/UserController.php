<?php
require_once 'Models/Database.php';
require_once 'Models/PasswordReset.php';

// Set the path to the .env file (replace with your correct path)
$envPath = __DIR__ . '/.env';
$db = new Database($envPath);  // Initialize the Database class

if (isset($_GET["key"]) && isset($_GET["email"]) && isset($_GET["action"]) && ($_GET["action"] == "reset") && !isset($_POST["action"])) {
    $key = $_GET["key"];
    $email = $_GET["email"];

    // Create PasswordReset instance
    $passwordReset = new PasswordReset($db, $email, $key);

    if (!$passwordReset->isLinkValid()) {
        // If the reset link is invalid or expired
        echo '<h2>Invalid Link</h2>
        <p>The link is invalid or expired. Either you did not copy the correct link
        from the email, or you have already used the key in which case it is 
        deactivated.</p>';
    } else {
        // If the link is valid
        echo '
        <br />
        <form method="post" action="" name="update">
        <input type="hidden" name="action" value="update" />
        <br /><br />
        <label><strong>Enter New Password:</strong></label><br />
        <input type="password" name="pass1" maxlength="15" required />
        <br /><br />
        <label><strong>Re-Enter New Password:</strong></label><br />
        <input type="password" name="pass2" maxlength="15" required/>
        <br /><br />
        <input type="hidden" name="email" value="' . htmlspecialchars($email) . '"/>
        <input type="submit" value="Reset Password" />
        </form>';
    }
}

if (isset($_POST["action"]) && $_POST["action"] == "update") {
    $email = $_POST["email"];
    $pass1 = $_POST["pass1"];
    $pass2 = $_POST["pass2"];

    if ($pass1 !== $pass2) {
        // If passwords don't match
        echo "<p>Password do not match, both password should be same.<br /><br /></p>";
    } else {
        // Create PasswordReset instance and update the password
        $passwordReset = new PasswordReset($db, $email, null);
        if ($passwordReset->updatePassword($pass1)) {
            // Password updated successfully
            echo '<div class="error"><p>Congratulations! Your password has been updated successfully.</p>
            <p><a href="login.php">Click here</a> to Login.</p></div><br />';
        } else {
            // Error updating password
            echo '<p>Error updating password.</p>';
        }
    }
}

$db->close();  // Close the database connection
?>
