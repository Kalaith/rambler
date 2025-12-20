<?php

declare(strict_types=1);

namespace App\External;

use PDO;

final class ResultRepository
{
    public function __construct(
        private readonly PDO $db
    ) {}

    public function create(int $rambleId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO processed_results (ramble_id, summary, topics, questions, ideas) 
             VALUES (:ramble_id, :summary, :topics, :questions, :ideas)'
        );
        
        $stmt->execute([
            'ramble_id' => $rambleId,
            'summary' => $data['summary'] ?? null,
            'topics' => json_encode($data['topics'] ?? []),
            'questions' => json_encode($data['questions'] ?? []),
            'ideas' => json_encode($data['ideas'] ?? [])
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function findByRambleId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM processed_results WHERE ramble_id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        
        if ($result) {
            $result['topics'] = json_decode((string)$result['topics'], true) ?: [];
            $result['questions'] = json_decode((string)$result['questions'], true) ?: [];
            $result['ideas'] = json_decode((string)$result['ideas'], true) ?: [];
        }

        return $result ?: null;
    }

    public function countRecentByUserId(int $userId, string $interval = '1 DAY'): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(pr.id) as count 
             FROM processed_results pr
             JOIN rambles r ON pr.ramble_id = r.id
             WHERE r.user_id = :user_id 
             AND pr.created_at >= DATE_SUB(NOW(), INTERVAL $interval)"
        );
        
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return (int)($row['count'] ?? 0);
    }
}
