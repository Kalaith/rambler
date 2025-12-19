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

    public function findByRambleId(int $rambleId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM processed_results WHERE ramble_id = :ramble_id LIMIT 1');
        $stmt->execute(['ramble_id' => $rambleId]);
        $result = $stmt->fetch();
        
        if ($result) {
            $result['topics'] = json_decode($result['topics'], true);
            $result['questions'] = json_decode($result['questions'], true);
            $result['ideas'] = json_decode($result['ideas'], true);
        }

        return $result ?: null;
    }
}
