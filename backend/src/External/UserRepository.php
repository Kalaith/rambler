<?php

declare(strict_types=1);

namespace App\External;

use PDO;

final class UserRepository
{
    public function __construct(
        private readonly PDO $db
    ) {}

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        
        return $user ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (email, password_hash) VALUES (:email, :password_hash)'
        );
        $stmt->execute([
            'email' => $data['email'],
            'password_hash' => $data['password_hash']
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        
        return $user ?: null;
    }
}
