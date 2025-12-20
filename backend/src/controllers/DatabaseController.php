<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use PDOException;
use Exception;
use Throwable;

final class DatabaseController
{
    public function init(): void
    {
        try {
            $host = $_SERVER['DB_HOST'] ?? $_ENV['DB_HOST'] ?? '127.0.0.1';
            $db   = $_SERVER['DB_NAME'] ?? $_ENV['DB_NAME'] ?? 'rambler';
            $user = $_SERVER['DB_USERNAME'] ?? $_ENV['DB_USERNAME'] ?? 'root';
            $pass = $_SERVER['DB_PASSWORD'] ?? $_ENV['DB_PASSWORD'] ?? '';
            $port = $_SERVER['DB_PORT'] ?? $_ENV['DB_PORT'] ?? '3306';
            $charset = 'utf8mb4';

            // 1. Connect without database first to create it if it doesn't exist
            $dsn = "mysql:host=$host;port=$port;charset=$charset";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // 2. Reconnect to the database
            $pdo->exec("USE `$db`");

            // 3. Check if tables already exist (idempotency check)
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($stmt->fetch()) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Database already initialized'
                ]);
                return;
            }

            // 4. Read and execute schema.sql
            $schemaPath = __DIR__ . '/../../database/schema.sql';
            if (!file_exists($schemaPath)) {
                throw new Exception("Schema file not found at $schemaPath");
            }

            $sql = file_get_contents($schemaPath);
            
            // Split by semicolon and execute
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Database initialized successfully'
            ]);

        } catch (PDOException $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Database Error: ' . $e->getMessage()
            ], 500);
        } catch (Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
