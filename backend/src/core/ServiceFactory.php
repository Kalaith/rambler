<?php

declare(strict_types=1);

namespace App\Core;

use App\Actions\CaptureRambleAction;
use App\Actions\ProcessRambleAction;
use App\Controllers\AuthController;
use App\Controllers\RambleController;
use App\External\RambleRepository;
use App\External\ResultRepository;
use App\External\UserRepository;
use App\Services\GeminiService;
use App\Services\RateLimiter;
use App\Controllers\KofiWebhookController;
use App\Controllers\HealthController;
use PDO;
use RuntimeException;

final class ServiceFactory
{
    private static ?PDO $db = null;

    public function create(string $class): object
    {
        return match ($class) {
            AuthController::class => new AuthController(
                new UserRepository($this->getDb()),
                $this->getDb()
            ),
            RambleController::class => new RambleController(
                new CaptureRambleAction(new RambleRepository($this->getDb())),
                new ProcessRambleAction(
                    new RambleRepository($this->getDb()),
                    new ResultRepository($this->getDb()),
                    new GeminiService()
                ),
                new \App\Actions\UpdateRambleAction(new RambleRepository($this->getDb())),
                new \App\Actions\ListRamblesAction(new RambleRepository($this->getDb())),
                new \App\Actions\DeleteRambleAction(new RambleRepository($this->getDb())),
                new RateLimiter(new UserRepository($this->getDb()), new ResultRepository($this->getDb()))
            ),
            KofiWebhookController::class => new KofiWebhookController(
                new UserRepository($this->getDb()),
                $this->getDb()
            ),
            HealthController::class => new HealthController($this->tryGetDb()),
            default => throw new RuntimeException("Unknown class: $class")
        };
    }

    private function tryGetDb(): ?PDO
    {
        try {
            return $this->getDb();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getDb(): PDO
    {
        if (self::$db !== null) {
            return self::$db;
        }

        $host = Env::required('DB_HOST');
        $db   = Env::required('DB_NAME');
        $user = Env::required('DB_USER');
        $pass = Env::configured('DB_PASSWORD');
        $port = Env::required('DB_PORT');
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
