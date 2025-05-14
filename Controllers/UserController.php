<?php
// Controller/UserController.php
require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Models/User.php';

// Bootstrapping
$envPath = __DIR__ . '/../.env';
$db = new Database($envPath);
$pdo = $db->getConnection();

// Models
$userReg  = new UserRegistration($pdo);
$userAuth = new UserAuth($pdo);
$userHand = new UserHandler($pdo);

// Determine action
$action = $_REQUEST['action'] ?? 'login';

try {
    switch ($action) {
        case 'register':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Register flow
                $userReg->registerUser  (
                    trim($_POST['username'] ?? ''),
                    trim($_POST['email'] ?? ''),
                    trim($_POST['password'] ?? '')
                );
                header('Location: index.php?action=login');
                exit;
            }
            include __DIR__ . '/../Views/register.php';
            break;

        case 'login':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $user = $userAuth->login(
                    trim($_POST['email'] ?? ''),
                    trim($_POST['password'] ?? '')
                );
                if ($user) {
                    session_start();
                    $_SESSION['user'] = $user;
                    header('Location: index.php?action=dashboard');
                    exit;
                } else {
                    $error = 'Invalid credentials.';
                }
            }
            include __DIR__ . '/../views/login.php';
            break;

        case 'logout':
            session_start();
            session_unset();
            session_destroy();
            header('Location: index.php?action=login');
            exit;

        case 'dashboard':
            session_start();
            if (empty($_SESSION['user'])) {
                header('Location: index.php?action=login');
                exit;
            }
            $users = $userHand->fetchUsers();
            include __DIR__ . '/../Views/dashboard.php';
            break;

        case 'edit':
            session_start();
            if (empty($_SESSION['user'])) throw new Exception('Unauthorized');
            $id = (int)($_GET['id'] ?? $_SESSION['user']['id']);
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $updated = $userHand->updateUser(
                    $id,
                    trim($_POST['username'] ?? ''),
                    trim($_POST['email'] ?? ''),
                    trim($_POST['password'] ?? '') ?: null
                );
                header('Location: index.php?action=dashboard');
                exit;
            }
            $userData = $userHand->fetchUsers($id);
            $userData = $userData[0] ?? null;
            include __DIR__ . '/../Views/edit-user.php';
            break;

        case 'delete':
            session_start();
            if (empty($_SESSION['user'])) throw new Exception('Unauthorized');
            $id = (int)($_GET['id'] ?? 0);
            $userHand->deleteUser($id);
            header('Location: index.php?action=dashboard');
            exit;

        default:
            throw new Exception('Unknown action: ' . htmlspecialchars($action));
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    include __DIR__ . '/../Views/error.php';
}
