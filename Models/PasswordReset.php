<?php
require_once 'Database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$root = realpath(__DIR__ . '/../'); 
if ($root === false) {
    throw new Exception("Could not find the root path for .env");
}

$dotenv = Dotenv::createImmutable($root);
$dotenv->load();

class PasswordReset {
    private PDO $pdo;
    private string $email;
    private ?string $key;  // renamed to match your table column

    public function __construct(Database $db, string $email, ?string $key = null) {
        $this->pdo = $db->getConnection();
        $this->email = $email;
        $this->key = $key;
    }

    // Generate a secure reset key, store it with expiration, and return it
    public function generateResetKey(): string {
        $key = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Remove any existing keys for this email
        $stmt = $this->pdo->prepare("DELETE FROM password_reset_temp WHERE email = ?");
        $stmt->execute([$this->email]);

        // Insert new key record (note backticks around `key` and `expDate`)
        $stmt = $this->pdo->prepare("INSERT INTO password_reset_temp (email, `key`, expDate) VALUES (?, ?, ?)");
        $stmt->execute([$this->email, $key, $expiresAt]);

        return $key;
    }

    // Check if the provided key is valid (exists, matches email, and not expired)
    public function isValidToken(): bool {
        if (empty($this->key)) {
            return false;
        }

        $stmt = $this->pdo->prepare("SELECT * FROM password_reset_temp WHERE email = ? AND `key` = ? AND expDate > NOW()");
        $stmt->execute([$this->email, $this->key]);

        return $stmt->fetch() !== false;
    }

    // Delete the key after successful password reset or expiration
    public function clearToken(): void {
        if (!empty($this->key)) {
            $stmt = $this->pdo->prepare("DELETE FROM password_reset_temp WHERE email = ? AND `key` = ?");
            $stmt->execute([$this->email, $this->key]);
        }
    }

    // Alias to isValidToken (can add extra checks here if needed)
    public function isLinkValid(): bool {
        return $this->isValidToken();
    }

    // Check if email exists in users table
    public function isEmailValid(): bool {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$this->email]);
        return $stmt->fetch() !== false;
    }

    // Update the user's password and remove the reset key in a transaction
    public function updatePassword(string $password): bool {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        $this->pdo->beginTransaction();

        // Check if the key is still valid before updating
        $stmt = $this->pdo->prepare("SELECT * FROM password_reset_temp WHERE email = ? AND `key` = ?");
        $stmt->execute([$this->email, $this->key]);
        if ($stmt->rowCount() === 0) {
            $this->pdo->rollBack();
            return false;
        }

        // Update password
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $this->email]);
        if ($stmt->rowCount() === 0) {
            $this->pdo->rollBack();
            return false;
        }

        // Delete the reset request
        $stmt = $this->pdo->prepare("DELETE FROM password_reset_temp WHERE email = ?");
        $stmt->execute([$this->email]);

        $this->pdo->commit();

        return true;
    } catch (PDOException $e) {
        $this->pdo->rollBack();
        error_log("Password update error: " . $e->getMessage());
        return false;
    }
}

    // Send reset password email
    public function sendResetEmail(string $token): bool {
    // Load env…
    $mailHost        = $_ENV['MAIL_HOST'];
    $mailPort        = $_ENV['MAIL_PORT'];
    $mailUsername    = $_ENV['MAIL_USERNAME'];
    $mailPassword    = $_ENV['MAIL_PASSWORD'];
    $mailFromAddress = $_ENV['MAIL_FROM_ADDRESS'];
    $mailFromName    = $_ENV['MAIL_FROM_NAME'];

    $this->token = $token; // store token in the object for use

    // Build the reset link once
    $resetLink = $_ENV['APP_URL'] . "/reset-password.php?key={$this->token}&email=" . urlencode($this->email);

    $mail = new PHPMailer(true);
    try {
        // ——— SMTP Setup ———
        $mail->isSMTP();
        $mail->Host       = $mailHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailUsername;
        $mail->Password   = $mailPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $mailPort;

        // ——— Headers & Recipients ———
        $mail->setFrom($mailFromAddress, $mailFromName);
        $mail->addReplyTo($mailFromAddress, $mailFromName);
        $mail->addAddress($this->email);

        // Small headers that help spam scores
        $mail->addCustomHeader('X-Mailer', 'PHP/' . phpversion());
        $mail->addCustomHeader('MIME-Version', '1.0');
        $mail->addEmbeddedImage('/home/felix/Php/Assets/images/minharola.jpeg', 'logoimg');


        // ——— Content ———
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request for YourAppName';

        // HTML body
        $mail->Body = "
            <p><img src='cid:logoimg' style='max-width: 200px;'></p>
            <p>Hello,</p>
            <p>We received a request to reset your password. Click the button below to proceed:</p>
            <p><a href='{$resetLink}' style='display:inline-block;padding:10px 20px;
                background:#007bff;color:#fff;text-decoration:none;
                border-radius:4px;'>Reset Password</a></p>
            <p>If that button doesn’t work, copy and paste this URL into your browser:</p>
            <p><small>{$resetLink}</small></p>
            <p>If you didn’t ask for a password reset, just ignore this email.</p>
            <p>— YourAppName Team</p>
        ";

        // Plain-text fallback
        $mail->AltBody = "Hello,\n\n"
            . "We received a request to reset your password. Visit the link below:\n"
            . "{$resetLink}\n\n"
            . "If you didn’t request this, you can safely ignore this message.\n\n"
            . "— YourAppName Team";

        // ——— Send! ———
        return $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error ({$this->email}): " . $mail->ErrorInfo);
        return false;
    }
}

}
?>
