<?php
require_once 'Database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Assuming you're using Composer for PHPMailer

class PasswordReset {
    private $db;
    private $email;
    private $key;

    public function __construct($db, $email, $key) {
        $this->db = $db;
        $this->email = $email;
        $this->key = $key;
    }

    // Check if the reset link is valid
    public function isLinkValid() {
        $curDate = date("Y-m-d H:i:s");
        $stmt = $this->db->prepare("SELECT * FROM `password_reset_temp` WHERE `key`=? AND `email`=?");
        $stmt->bind_param("ss", $this->key, $this->email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            return false;
        }

        $row = $result->fetch_assoc();
        return $row['expDate'] >= $curDate;
    }

    // Update the password in the database
    public function updatePassword($password) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE `users` SET `password`=?, `trn_date`=NOW() WHERE `email`=?");
        $stmt->bind_param("ss", $hashedPassword, $this->email);

        if ($stmt->execute()) {
            // Remove the reset key from the temporary table
            $stmt = $this->db->prepare("DELETE FROM `password_reset_temp` WHERE `email`=?");
            $stmt->bind_param("s", $this->email);
            $stmt->execute();
            return true;
        }
        return false;
    }

    // Send reset password email
    public function sendResetEmail() {
        // Load environment variables for mail configuration
        $mailHost = getenv('MAIL_HOST');
        $mailPort = getenv('MAIL_PORT');
        $mailUsername = getenv('MAIL_USERNAME');
        $mailPassword = getenv('MAIL_PASSWORD');
        $mailFromAddress = getenv('MAIL_FROM_ADDRESS');
        $mailFromName = getenv('MAIL_FROM_NAME');

        echo "Mail From Address: " . $mailFromAddress . "<br>";
        echo "Mail From Name: " . $mailFromName . "<br>";

        // Validate the 'From' address
        if (empty($mailFromAddress) || !filter_var($mailFromAddress, FILTER_VALIDATE_EMAIL)) {
            echo "Invalid 'From' address: " . $mailFromAddress;
            return false;
        }

        if (empty($mailFromName)) {
            $mailFromName = 'Your Application Name';  // Default value if missing
        }

        // Set up PHPMailer
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('MAIL_USERNAME');
            $mail->Password   = getenv('MAIL_PASSWORD'); // This should be your Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            //Recipients
            $mail->setFrom($mailFromAddress, $mailFromName);
            $mail->addAddress($this->email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Password Reset Request";
            $mail->Body    = "To reset your password, click the following link: <br> <a href='http://yourwebsite.com/reset_password.php?key={$this->key}&email=" . urlencode($this->email) . "'>Reset Password</a>";

            // Send the email
            if ($mail->send()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            // Log error message
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }
}
?>
