<?php

declare(strict_types=1);

$autoloadCandidates = [
    __DIR__ . '/../../../../vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
];
$autoloader = null;
foreach ($autoloadCandidates as $candidate) {
    if (file_exists($candidate)) {
        $autoloader = $candidate;
        break;
    }
}
if (!$autoloader) {
    throw new RuntimeException("Composer autoload.php not found for rambler db-init.");
}
require_once $autoloader;

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
try {
    $config = $dotenv->load();
} catch (\Throwable $e) {
    $config = [];
}

$host = $config['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? $_ENV['DB_HOST'] ?? '127.0.0.1';
$db   = $config['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? $_ENV['DB_NAME'] ?? 'rambler';
$user = $config['DB_USERNAME'] ?? $_SERVER['DB_USERNAME'] ?? $_ENV['DB_USERNAME'] ?? 'root';
$pass = $config['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? $_ENV['DB_PASSWORD'] ?? '';
$port = $config['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? $_ENV['DB_PORT'] ?? '3306';
$charset = 'utf8mb4';

echo "🚀 Initializing Database: $db on $host:$port...\n";

try {
    // Connect without database first to create it if it doesn't exist
    $dsn = "mysql:host=$host;port=$port;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database `$db` ensured.\n";

    // Reconnect to the database
    $pdo->exec("USE `$db`");

    // Read and execute schema.sql
    $schemaPath = __DIR__ . '/../database/schema.sql';
    if (!file_exists($schemaPath)) {
        throw new Exception("Schema file not found at $schemaPath");
    }

    $sql = file_get_contents($schemaPath);
    
    // Split by semicolon to execute one by one (basic approach)
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }

    echo "✅ Schema imported successfully.\n";
    echo "🎉 Database initialization complete!\n";

} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
