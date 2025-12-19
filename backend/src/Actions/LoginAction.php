<?php

declare(strict_types=1);

namespace App\Actions;

use App\External\UserRepository;
use Firebase\JWT\JWT;
use InvalidArgumentException;

final class LoginAction
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function execute(string $email, string $password): string
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new InvalidArgumentException('Invalid credentials');
        }

        $secret = $_ENV['JWT_SECRET'] ?? 'rambler_secret_change_me';
        $payload = [
            'sub' => $user['id'],
            'email' => $user['email'],
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }
}
