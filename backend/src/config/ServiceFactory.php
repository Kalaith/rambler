<?php

declare(strict_types=1);

namespace App\Config;

use App\Actions\CaptureRambleAction;
use App\Actions\LoginAction;
use App\Actions\ProcessRambleAction;
use App\Actions\RegisterAction;
use App\Controllers\AuthController;
use App\Controllers\RambleController;
use App\External\RambleRepository;
use App\External\ResultRepository;
use App\External\UserRepository;
use App\Services\GeminiService;
use App\Services\RateLimiter;
use PDO;
use RuntimeException;

final class ServiceFactory
{
    private static ?PDO $db = null;

    public function create(string $class): object
    {
        $db = $this->getDb();

        return match ($class) {
            AuthController::class => new AuthController(
                new RegisterAction(new UserRepository($db)),
                new LoginAction(new UserRepository($db))
            ),
            RambleController::class => new RambleController(
                new CaptureRambleAction(new RambleRepository($db)),
                new ProcessRambleAction(
                    new RambleRepository($db),
                    new ResultRepository($db),
                    new GeminiService()
                ),
                new RambleRepository($db),
                new RateLimiter($db)
            ),
            default => throw new RuntimeException("Unknown class: $class")
        };
    }

    private function getDb(): PDO
    {
        if (self::$db !== null) {
            return self::$db;
        }

        $host = $_SERVER['DB_HOST'] ?? $_ENV['DB_HOST'] ?? '127.0.0.1';
        $db   = $_SERVER['DB_NAME'] ?? $_ENV['DB_NAME'] ?? 'rambler';
        $user = $_SERVER['DB_USERNAME'] ?? $_ENV['DB_USERNAME'] ?? 'root';
        $pass = $_SERVER['DB_PASSWORD'] ?? $_ENV['DB_PASSWORD'] ?? '';
        $port = $_SERVER['DB_PORT'] ?? $_ENV['DB_PORT'] ?? '3306';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        self::$db = new PDO($dsn, $user, $pass, $options);
        return self::$db;
    }
}
