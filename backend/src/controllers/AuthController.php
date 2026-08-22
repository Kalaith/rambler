<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\External\UserRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
use Throwable;

final class AuthController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PDO $db,
    ) {}

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
            'email' => (string) $user['email'],
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
            $guestUserId = $this->userRepository->create([
                'email' => $email,
                'password_hash' => password_hash(bin2hex(random_bytes(24)), PASSWORD_BCRYPT),
            ]);
            $payload = [
                'sub' => $guestUserId,
                'user_id' => $guestUserId,
                'email' => $email,
                'role' => 'guest',
                'roles' => ['guest'],
                'auth_type' => 'guest',
                'is_guest' => true,
                'iat' => time(),
                'exp' => time() + (24 * 60 * 60 * 365),
            ];

            $response->withStatus(201)->success([
                'token' => JWT::encode($payload, Env::required('JWT_SECRET'), 'HS256'),
                'user' => ['id' => $guestUserId, 'email' => $email, 'is_guest' => true, 'auth_type' => 'guest', 'role' => 'guest'],
            ]);
        } catch (Throwable $exception) {
            $response->error($exception->getMessage(), 500);
        }
    }

    public function linkGuest(Request $request, Response $response): void
    {
        try {
            $userId = (int) $request->getAttribute('user_id', 0);
            $role = (string) $request->getAttribute('user_role', 'user');
            if ($userId <= 0 || $role === 'admin' || (bool) $request->getAttribute('is_guest', false)) {
                $response->error('Guest destination is not allowed', 403);
                return;
            }

            $guestUserId = (int) $request->get('guest_user_id', 0);
            $guestToken = (string) $request->get('guest_token', '');
            $strategy = (string) ($request->get('merge_strategy', $request->get('strategy', 'merge')));
            if ($guestUserId <= 0 || $guestUserId === $userId || !$this->guestTokenMatches($guestToken, $guestUserId)) {
                $response->error('Guest token proof is invalid', 400);
                return;
            }
            if (!in_array($strategy, ['keep_account', 'guest_wins', 'merge'], true)) {
                $response->error('Invalid guest merge strategy', 400);
                return;
            }

            $query = $this->db->prepare($strategy === 'keep_account'
                ? 'DELETE FROM rambles WHERE user_id = :guest_user_id'
                : 'UPDATE rambles SET user_id = :user_id WHERE user_id = :guest_user_id');
            $query->execute(['user_id' => $userId, 'guest_user_id' => $guestUserId]);
            $count = $query->rowCount();
            $response->success([
                'guest_user_id' => $guestUserId,
                'linked_to_user_id' => $userId,
                'strategy' => $strategy,
                'moved_rows_by_table' => ['rambles' => $count],
                'total_moved_rows' => $count,
            ]);
        } catch (Throwable $exception) {
            $response->error($exception->getMessage(), 500);
        }
    }

    public function previewGuestLink(Request $request, Response $response): void
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $guestUserId = (int) $request->get('guest_user_id', 0);
        $guestToken = (string) $request->get('guest_token', '');
        if ($userId <= 0 || $guestUserId <= 0 || !$this->guestTokenMatches($guestToken, $guestUserId)) {
            $response->error('Guest token proof is invalid', 400);
            return;
        }

        $count = function (int $ownerId): int {
            $query = $this->db->prepare('SELECT COUNT(*) FROM rambles WHERE user_id = ?');
            $query->execute([$ownerId]);
            return (int) $query->fetchColumn();
        };
        $response->success([
            'has_guest_data' => $count($guestUserId) > 0,
            'has_account_data' => $count($userId) > 0,
            'guest_summary' => ['rambles' => $count($guestUserId)],
            'account_summary' => ['rambles' => $count($userId)],
            'allowed_strategies' => ['keep_account', 'guest_wins', 'merge'],
        ]);
    }

    private function guestTokenMatches(string $token, int $guestUserId): bool
    {
        try {
            $claims = (array) JWT::decode($token, new Key(Env::required('JWT_SECRET'), 'HS256'));
            return ((bool) ($claims['is_guest'] ?? false) || (($claims['auth_type'] ?? null) === 'guest'))
                && (string) ($claims['user_id'] ?? $claims['sub'] ?? '') === (string) $guestUserId;
        } catch (Throwable) {
            return false;
        }
    }
}
