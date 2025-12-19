<?php

declare(strict_types=1);

namespace App\Actions;

use App\External\UserRepository;
use InvalidArgumentException;

final class RegisterAction
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function execute(string $email, string $password): array
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Valid email is required');
        }

        if (strlen($password) < 6) {
            throw new InvalidArgumentException('Password must be at least 6 characters');
        }

        if ($this->userRepository->findByEmail($email)) {
            throw new InvalidArgumentException('Email already in use');
        }

        $userId = $this->userRepository->create([
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT)
        ]);

        return [
            'id' => $userId,
            'email' => $email
        ];
    }
}
