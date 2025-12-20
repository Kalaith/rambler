<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use PDO;
use PDOException;
use Exception;
use Throwable;

final class DatabaseController
{
    public function init(Request $request, Response $response): void
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
                $response->success(null, 'Database already initialized');
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

            $response->success(null, 'Database initialized successfully');

        } catch (PDOException $e) {
            $response->error('Database Error: ' . $e->getMessage(), 500);
        } catch (Throwable $e) {
            $response->error('Error: ' . $e->getMessage(), 500);
        }
    }
}
