<?php
// Models/User.php
require_once 'Database.php';

class UserRegistration {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Registers a new user.
     * @throws Exception if fields missing or email exists.
     */
    public function registerUser(string $username, string $email, string $password, string $photo = null): void {
        if (!$username || !$email || !$password) {
            throw new Exception('Username, email and password are required.');
        }
        
        // Check for existing email
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            throw new Exception('Email already registered.');
        }
    
        // Hash password
        $hash = password_hash($password, PASSWORD_DEFAULT);
    
        // Insert user (include photo field)
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, email, password, photo, createdAt) VALUES (:username, :email, :password, :photo, NOW())'
        );
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':password' => $hash,
            ':photo'    => $photo,  // Add the photo field here
        ]);
    }
}

class UserAuth {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Authenticates a user by email and password.
     * Returns user data array on success, false on failure.
     */
    public function login(string $email, string $password) {
        if (!$email || !$password) {
            return false;
        }
        $stmt = $this->pdo->prepare(
            'SELECT id, username, email, password FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); // Remove password for security
            // Set user session
            $_SESSION['user'] = $user; // Save user data in session
            return $user;
        }
        return false;
    }
    
    public function logout() {
        // Destroy the session
        session_start();
        session_unset();    // Unset all session variables
        session_destroy();  // Destroy the session
        
        // Redirect to the login page (or homepage)
        header("Location: login.php");
        exit;
    }
}

class UserHandler {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

     // Method to fetch all users
    public function getAllUsers(): array {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY createdAt DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Updates user data. Password optional.
     */
    public function updateUser(int $id, string $username, string $email, ?string $password = null): bool {
        $params = [':id' => $id, ':username' => $username, ':email' => $email];
        $sets = 'username = :username, email = :email';
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sets .= ', password = :password';
            $params[':password'] = $hash;
        }
        $sql = "UPDATE users SET $sets WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /** Deletes a user by id */
    public function deleteUser(int $id): bool {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}

    class PhotoModel {
        private $conn;

        public function __construct($conn) {
            $this->conn = $conn;
        }

        public function getAllPhotos() {
            $sql = "SELECT id, photo FROM your_table";
            $result = $this->conn->query($sql);

            $photos = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $photos[] = $row;
                }
            }
            return $photos;
        }
    }

?>