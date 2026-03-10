<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Actions\LoginAction;
use App\Actions\RegisterAction;
use App\Core\Request;
use App\Core\Response;
use App\External\UserRepository;
use Firebase\JWT\JWT;
use PDO;
use Throwable;

final class AuthController
{
    public function __construct(
        private readonly RegisterAction $registerAction,
        private readonly LoginAction $loginAction,
        private readonly UserRepository $userRepository,
        private readonly PDO $db
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

    public function currentUser(Request $request, Response $response): void
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            $response->error('User not found', 404);
            return;
        }

        $response->success([
            'id' => (int) $user['id'],
            'email' => $user['email'],
            'subscription_tier' => $user['subscription_tier'] ?? 'free',
            'subscription_expires_at' => $user['subscription_expires_at'] ?? null,
            'is_guest' => (bool) $request->getAttribute('is_guest', false),
            'auth_type' => (string) $request->getAttribute('auth_type', 'frontpage'),
            'role' => (string) $request->getAttribute('user_role', 'user'),
        ]);
    }

    public function createGuestSession(Request $request, Response $response): void
    {
        try {
            $guestTag = bin2hex(random_bytes(8));
            $email = "guest_{$guestTag}@guest.webhatchery.local";
            $passwordHash = password_hash(bin2hex(random_bytes(24)), PASSWORD_BCRYPT);
            $guestUserId = $this->userRepository->create([
                'email' => $email,
                'password_hash' => $passwordHash,
            ]);

            $secret = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: '';
            if (empty($secret)) {
                throw new \RuntimeException('JWT security not configured');
            }

            $payload = [
                'sub' => $guestUserId,
                'user_id' => $guestUserId,
                'email' => $email,
                'role' => 'guest',
                'auth_type' => 'guest',
                'is_guest' => true,
                'iat' => time(),
                'exp' => time() + (24 * 60 * 60 * 365),
            ];

            $response->withStatus(201)->success([
                'token' => JWT::encode($payload, $secret, 'HS256'),
                'user' => [
                    'id' => $guestUserId,
                    'email' => $email,
                    'subscription_tier' => 'free',
                    'subscription_expires_at' => null,
                    'is_guest' => true,
                    'auth_type' => 'guest',
                ],
            ]);
        } catch (Throwable $e) {
            $response->error($e->getMessage(), 500);
        }
    }

    public function linkGuest(Request $request, Response $response): void
    {
        try {
            $userId = (int) $request->getAttribute('user_id', 0);
            $isGuest = (bool) $request->getAttribute('is_guest', false);
            $role = (string) $request->getAttribute('user_role', 'user');

            if ($userId <= 0) {
                $response->error('Authentication required', 401);
                return;
            }

            if ($isGuest) {
                $response->error('Guest destination is not allowed', 400);
                return;
            }

            if ($role === 'admin') {
                $response->error('Guest and admin accounts cannot be linked', 403);
                return;
            }

            $guestUserId = (int) $request->get('guest_user_id', 0);
            if ($guestUserId <= 0 || $guestUserId === $userId) {
                $response->error('Invalid guest_user_id', 400);
                return;
            }

            $guestUser = $this->userRepository->findById($guestUserId);
            if (!$guestUser || !str_starts_with((string) $guestUser['email'], 'guest_')) {
                $response->error('Invalid guest_user_id', 400);
                return;
            }

            $stmt = $this->db->prepare('UPDATE rambles SET user_id = :user_id WHERE user_id = :guest_user_id');
            $stmt->execute([
                'user_id' => $userId,
                'guest_user_id' => $guestUserId,
            ]);

            $response->success([
                'guest_user_id' => $guestUserId,
                'linked_to_user_id' => $userId,
                'moved_rows_by_table' => ['rambles' => $stmt->rowCount()],
                'total_moved_rows' => $stmt->rowCount(),
            ]);
        } catch (Throwable $e) {
            $response->error($e->getMessage(), 500);
        }
    }
}
