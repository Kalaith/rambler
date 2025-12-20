<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final class RateLimiter
{
    private int $maxDaily;

    public function __construct(
        private readonly \App\External\UserRepository $userRepository,
        private readonly \App\External\ResultRepository $resultRepository
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
        $user = $this->userRepository->findByIdWithSubscription($userId);

        $tier = 'free';
        if ($user && ($user['subscription_tier'] ?? 'free') === 'pro') {
            // Check if expired
            $expires = $user['subscription_expires_at'] ? strtotime($user['subscription_expires_at']) : 0;
            if ($expires > time()) {
                $tier = 'pro';
            }
        }

        $limit = ($tier === 'pro') ? 200 : 20;

        $count = $this->resultRepository->countRecentByUserId($userId);

        return [
            'count' => $count,
            'limit' => $limit,
            'remaining' => max(0, $limit - $count),
            'tier' => $tier
        ];
    }
}
