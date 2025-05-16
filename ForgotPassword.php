<?php
require_once 'Models/Database.php';
require_once 'Models/PasswordReset.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<div class="alert alert-danger">Invalid email address provided.</div>';
        exit;
    }

    // Create database connection (make sure credentials are correct)
    $db = new Database(realpath(__DIR__));
    $pdo = $db->getConnection();

    // Generate secure random key
    
    // Now create PasswordReset object with the key generated
    $passwordReset = new PasswordReset($db, $email);
    
    $resetKey = $passwordReset->generateResetKey();
    
    // Send email with the key
    if ($passwordReset->sendResetEmail($resetKey)) {
        echo '<div class="alert alert-success">An email has been sent to your address with instructions to reset your password.</div>';
    } else {
        echo '<div class="alert alert-danger">There was an error sending the reset email. Please try again later.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Forgot Password</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Enter Your Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Reset Link</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
