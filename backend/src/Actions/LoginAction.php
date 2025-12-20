<?php

declare(strict_types=1);

namespace App\Actions;

use App\External\UserRepository;
use Firebase\JWT\JWT;
use InvalidArgumentException;
use RuntimeException;

final class LoginAction
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function execute(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new InvalidArgumentException('Invalid credentials');
        }

        $secret = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: '';
        
        // Debug logging
        $maskedSecret = substr($secret, 0, 3) . '...' . substr($secret, -3);
        $source = isset($_ENV['JWT_SECRET']) ? '$_ENV' : (isset($_SERVER['JWT_SECRET']) ? '$_SERVER' : (getenv('JWT_SECRET') ? 'getenv' : 'none'));
        error_log("JWT Sign - Source: $source, Secret: $maskedSecret");

        if (empty($secret)) {
            throw new RuntimeException('JWT security not configured');
        }

        $payload = [
            'sub' => $user['id'],
            'email' => $user['email'],
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];

        return [
            'token' => JWT::encode($payload, $secret, 'HS256'),
            'user' => [
                'id' => $user['id'],
                'email' => $user['email']
            ]
        ];
    }
}
