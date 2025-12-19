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
            'SELECT id, content, word_count, created_at FROM rambles WHERE user_id = :user_id ORDER BY created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM rambles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $ramble = $stmt->fetch();
        
        return $ramble ?: null;
    }
}
