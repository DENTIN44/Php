<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Controllers/UserController.php';

try {
    $database = new Database(__DIR__ . '/../');
    $pdo = $database->getConnection();
    $database->createTables();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

// CSRF protection
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

// Reset old input and errors on each request
$_SESSION['errors'] = [];
$_SESSION['old'] = [];
