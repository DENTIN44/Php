<?php
// Models/Database.php
require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

class Database {
    private ?PDO $pdo = null;

    public function __construct(string $envPath) {
        $this->loadEnvironment($envPath);
        $this->connect();
    }

    private function loadEnvironment(string $path): void {
        $root = realpath(__DIR__ . '/../');
        $dotenv = Dotenv::createImmutable($root);
        $dotenv->load();
    }

    private function connect(): void {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $_ENV['DB_HOST'],
            $_ENV['DB_DATABASE']
        );
        $user = $_ENV['DB_USERNAME'];
        $pass = $_ENV['DB_PASSWORD'];

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    public function getConnection(): PDO {
        if ($this->pdo === null) {
            throw new RuntimeException('Database connection not established.');
        }
        return $this->pdo;
    }

    /**
     * Optional: create tables if they do not exist
     */
    public function createTables(): void {
        $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                photo VARCHAR(255) DEFAULT NULL,
                createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $this->getConnection()->exec($sql);
    }
}