<?php

use Dotenv\Dotenv;

// Autoload and include model
require_once dirname(__DIR__) . '/Models/User.php';
require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

$host = 'localhost';
$dbname = $_ENV['DB_DATABASE'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $loginService = new UserAuth($pdo);
    $user = $loginService->login($email, $password);

    if ($user) {
        $_SESSION['user'] = $user;
        header('Location: /index.php'); // Redirect after successful login
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}

// Check if we're on the login page
if (basename($_SERVER['PHP_SELF']) === 'login.php'):

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2rem;
        }
        .container {
            max-width: 500px;
            margin: auto;
        }
        h2 {
            margin-bottom: 1rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.5rem;
            margin-top: 0.25rem;
        }
        button {
            padding: 0.5rem 1rem;
        }
        .alert {
            padding: 0.75rem;
            margin-bottom: 1rem;
        }
        .alert-danger {
            background-color: #f8d7da;
        }
        a {
            text-decoration: none;
            color: blue;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>

            <div class="form-group">
                <button type="submit">Log In</button>
            </div>
        </form>

        <p>No account? <a href="register.php">Sign up</a></p>
    </div>
</body>
</html>

<?php endif; ?>
