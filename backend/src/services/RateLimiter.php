<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final class RateLimiter
{
    private int $maxDaily;

    public function __construct(
        private readonly PDO $db
    ) {
        $this->maxDaily = (int)($_ENV['MAX_DAILY_PROCESS'] ?? 20);
    }

    public function canProcess(int $userId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(pr.id) as count 
             FROM processed_results pr
             JOIN rambles r ON pr.ramble_id = r.id
             WHERE r.user_id = :user_id 
             AND pr.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)'
        );
        
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        
        return (int)($row['count'] ?? 0) < $this->maxDaily;
    }

    public function getLimitInfo(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(pr.id) as count 
             FROM processed_results pr
             JOIN rambles r ON pr.ramble_id = r.id
             WHERE r.user_id = :user_id 
             AND pr.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)'
        );
        
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        $count = (int)($row['count'] ?? 0);

        return [
            'count' => $count,
            'limit' => $this->maxDaily,
            'remaining' => max(0, $this->maxDaily - $count)
        ];
    }
}
