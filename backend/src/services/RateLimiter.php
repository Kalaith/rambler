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
        $limitInfo = $this->getLimitInfo($userId);
        return $limitInfo['count'] < $limitInfo['limit'];
    }

    public function getLimitInfo(int $userId): array
    {
        // Get user tier
        $stmt = $this->db->prepare('SELECT subscription_tier, subscription_expires_at FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        $tier = 'free';
        if ($user && $user['subscription_tier'] === 'pro') {
            // Check if expired
            $expires = $user['subscription_expires_at'] ? strtotime($user['subscription_expires_at']) : 0;
            if ($expires > time()) {
                $tier = 'pro';
            }
        }

        $limit = ($tier === 'pro') ? 200 : 20;

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
            'limit' => $limit,
            'remaining' => max(0, $limit - $count),
            'tier' => $tier
        ];
    }
}
