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
    public function registerUser(string $username, string $email, string $password): void {
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
        // Insert user
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, email, password, createdAt) VALUES (:username, :email, :password, NOW())'
        );
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':password' => $hash,
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
            unset($user['password']);
            return $user;
        }
        return false;
    }
}

class UserHandler {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Fetch all users or search by id/email/username
     */
    public function fetchUsers(?int $id = null, ?string $search = null): array {
        if ($id !== null) {
            $stmt = $this->pdo->prepare('SELECT id, username, email, createdAt FROM users WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return $row ? [$row] : [];
        }
        if ($search !== null) {
            $pattern = strlen($search) === 1 ? $search . '%' : '%' . $search . '%';
            $stmt = $this->pdo->prepare(
                'SELECT id, username, email, createdAt FROM users WHERE username LIKE :pat OR email LIKE :pat ORDER BY createdAt DESC'
            );
            $stmt->execute([':pat' => $pattern]);
            return $stmt->fetchAll();
        }
        $stmt = $this->pdo->query('SELECT id, username, email, createdAt FROM users ORDER BY createdAt DESC');
        return $stmt->fetchAll();
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