<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use PDO;
use Throwable;

final class HealthController
{
    public function __construct(
        private readonly ?PDO $db = null
    ) {}

    public function check(Request $request, Response $response): void
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

        $response->withStatus($status['status'] === 'ok' ? 200 : 500)->json($status);
    }
}
