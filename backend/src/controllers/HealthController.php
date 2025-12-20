<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use Throwable;

final class HealthController
{
    public function __construct(
        private readonly ?PDO $db = null
    ) {}

    public function check(): void
    {
        $status = [
            'status' => 'ok',
            'timestamp' => date('c'),
            'php_version' => PHP_VERSION,
            'database' => [
                'connection' => 'unknown',
                'initialized' => false
            ]
        ];

        if ($this->db) {
            try {
                $this->db->query("SELECT 1");
                $status['database']['connection'] = 'connected';

                $stmt = $this->db->query("SHOW TABLES LIKE 'users'");
                $status['database']['initialized'] = (bool)$stmt->fetch();
            } catch (Throwable $e) {
                $status['database']['connection'] = 'error: ' . $e->getMessage();
                $status['status'] = 'degraded';
            }
        } else {
            $status['database']['connection'] = 'not configured';
            $status['status'] = 'degraded';
        }

        $this->jsonResponse($status, $status['status'] === 'ok' ? 200 : 500);
    }

    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
