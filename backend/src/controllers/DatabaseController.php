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
        // Simple security check for production/shared environments
        $secret = $_ENV['DB_INIT_SECRET'] ?? $_SERVER['DB_INIT_SECRET'] ?? null;
        if ($secret && $request->getParam('key') !== $secret) {
            $response->error('Unauthorized: Invalid migration key', 403);
            return;
        }

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

            // 3. Read and execute schema.sql (contains IF NOT EXISTS)
            $schemaPath = __DIR__ . '/../../database/schema.sql';
            if (!file_exists($schemaPath)) {
                throw new Exception("Schema file not found at $schemaPath");
            }

            $sql = file_get_contents($schemaPath);
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }

            // 4. Handle specific migrations (Add missing columns)
            
            // Add subscription columns to users if missing
            $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'subscription_tier'");
            if (!$stmt->fetch()) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `subscription_tier` ENUM('free', 'pro') DEFAULT 'free' AFTER `updated_at` ");
            }
            $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'subscription_expires_at'");
            if (!$stmt->fetch()) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `subscription_expires_at` TIMESTAMP NULL DEFAULT NULL AFTER `subscription_tier` ");
            }

            // Add deleted_at to rambles if not exists
            $stmt = $pdo->query("SHOW COLUMNS FROM `rambles` LIKE 'deleted_at'");
            if (!$stmt->fetch()) {
                $pdo->exec("ALTER TABLE `rambles` ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at` ");
            }

            $response->success(null, 'Database initialized and migrated successfully');

        } catch (PDOException $e) {
            $response->error('Database Error: ' . $e->getMessage(), 500);
        } catch (Throwable $e) {
            $response->error('Error: ' . $e->getMessage(), 500);
        }
    }
}
