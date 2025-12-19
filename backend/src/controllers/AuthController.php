<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Actions\LoginAction;
use App\Actions\RegisterAction;
use Throwable;

final class AuthController
{
    public function __construct(
        private readonly RegisterAction $registerAction,
        private readonly LoginAction $loginAction
    ) {}

    public function register(): void
    {
        try {
            $data = $this->getParsedBody();
            $user = $this->registerAction->execute(
                (string)($data['email'] ?? ''),
                (string)($data['password'] ?? '')
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => $user
            ], 201);
        } catch (Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function login(): void
    {
        try {
            $data = $this->getParsedBody();
            $token = $this->loginAction->execute(
                (string)($data['email'] ?? ''),
                (string)($data['password'] ?? '')
            );

            $this->jsonResponse([
                'success' => true,
                'token' => $token
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    private function getParsedBody(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? $_POST;
    }

    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
