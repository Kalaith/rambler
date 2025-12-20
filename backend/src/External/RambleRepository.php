<?php

declare(strict_types=1);

namespace App\External;

use PDO;

final class RambleRepository
{
    public function __construct(
        private readonly PDO $db
    ) {}

    public function create(int $userId, string $content, int $wordCount): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO rambles (user_id, content, word_count) VALUES (:user_id, :content, :word_count)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'content' => $content,
            'word_count' => $wordCount
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, pr.summary, pr.topics, pr.questions, pr.ideas 
             FROM rambles r 
             LEFT JOIN processed_results pr ON r.id = pr.ramble_id 
             WHERE r.user_id = :user_id 
             AND r.deleted_at IS NULL
             ORDER BY r.created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM rambles WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $id]);
        $ramble = $stmt->fetch();
        
        return $ramble ?: null;
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE rambles SET deleted_at = CURRENT_TIMESTAMP 
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId
        ]);
        return $stmt->rowCount() > 0;
    }

    public function update(int $id, int $userId, string $content, int $wordCount): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE rambles SET content = :content, word_count = :word_count, updated_at = CURRENT_TIMESTAMP 
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'content' => $content,
            'word_count' => $wordCount
        ]);
        return $stmt->rowCount() > 0;
    }
}
