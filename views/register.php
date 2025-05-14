<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Controllers/UserController.php';

// Initialize Database
try {
    $database = new Database(__DIR__ . '/../');
    $pdo = $database->getConnection();
    $database->createTables(); // Ensure user table exists
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

$registrationService = new UserRegistration($pdo);

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Old input and error helpers
function old($key) {
    return $_SESSION['old'][$key] ?? '';
}

function error($key) {
    return $_SESSION['errors'][$key] ?? '';
}

// Reset error and old input values
$_SESSION['errors'] = [];
$_SESSION['old'] = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $photo = 'default.jpg';

    $_SESSION['old'] = compact('username', 'email');

    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_extensions = ['png', 'jpg', 'jpeg'];
        $upload_dir = './uploads/';
        $max_file_size = 5 * 1024 * 1024;

        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = basename($_FILES['photo']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_size = $_FILES['photo']['size'];

        if (!in_array($file_ext, $allowed_extensions)) {
            die("Invalid file type. Only PNG, JPG, and JPEG are allowed.");
        }

        if ($file_size > $max_file_size) {
            die("File is too large. Maximum size is 5MB.");
        }

        $new_name = uniqid('', true) . '.' . $file_ext;
        $file_path = $upload_dir . $new_name;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $file_path)) {
            $photo = $new_name;
        } else {
            die("Failed to move the uploaded file.");
        }
    }

    try {
        $registrationService->registerUser($username, $email, $password, $photo);
        unset($_SESSION['old']);
        echo "<div class='alert alert-success'>User registered successfully!</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register User</title>
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
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"] {
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
        .alert-success {
            background-color: #d4edda;
        }
        .alert-danger {
            background-color: #f8d7da;
        }
        span {
            color: red;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register User</h2>
        <form method="post" action="register.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars(old('username')) ?>" required>
                <span><?= error('username') ?></span>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars(old('email')) ?>" required>
                <span><?= error('email') ?></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
                <span><?= error('password') ?></span>
            </div>

            <div class="form-group">
                <label for="photo">Profile Photo</label>
                <input type="file" name="photo" id="photo" accept=".png,.jpg,.jpeg">
            </div>

            <div class="form-group">
                <button type="submit">Register</button>
            </div>
        </form>
    </div>
</body>
</html>
