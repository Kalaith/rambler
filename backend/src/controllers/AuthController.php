<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Actions\LoginAction;
use App\Actions\RegisterAction;
use App\Core\Request;
use App\Core\Response;
use Throwable;

final class AuthController
{
    public function __construct(
        private readonly RegisterAction $registerAction,
        private readonly LoginAction $loginAction
    ) {}

    public function register(Request $request, Response $response): void
    {
        try {
            $user = $this->registerAction->execute(
                (string)$request->get('email', ''),
                (string)$request->get('password', '')
            );

            $response->withStatus(201)->success($user, 'User registered successfully');
        } catch (Throwable $e) {
            $response->error($e->getMessage(), 400);
        }
    }

    public function login(Request $request, Response $response): void
    {
        try {
            $result = $this->loginAction->execute(
                (string)$request->get('email', ''),
                (string)$request->get('password', '')
            );

            $response->success($result);
        } catch (Throwable $e) {
            $response->error($e->getMessage(), 401);
        }
    }
}
