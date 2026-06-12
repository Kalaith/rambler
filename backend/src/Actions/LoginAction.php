<?php

declare(strict_types=1);

namespace App\Actions;

use App\Core\Env;
use App\External\UserRepository;
use Firebase\JWT\JWT;
use InvalidArgumentException;

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

        $secret = Env::required('JWT_SECRET');

        $payload = [
            'sub' => $user['id'],
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => 'user',
            'auth_type' => 'frontpage',
            'is_guest' => false,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];

        return [
            'token' => JWT::encode($payload, $secret, 'HS256'),
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'is_guest' => false,
                'auth_type' => 'frontpage',
            ]
        ];
    }
}
