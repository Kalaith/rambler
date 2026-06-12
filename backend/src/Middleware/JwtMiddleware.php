<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

final class JwtMiddleware
{
    public function handle(Request $request, Response $response): bool
    {
        $authorization = $request->getHeader('Authorization') ?? '';

        if (!$authorization || !preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            $response->error('Missing or invalid token', 401);
            return false;
        }

        $token = $matches[1];
        try {
            $secret = Env::required('JWT_SECRET');
        } catch (RuntimeException) {
            $response->error('Internal server error: JWT security not configured', 500);
            return false;
        }

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            $isGuest = (bool) ($decoded->is_guest ?? false) || (($decoded->auth_type ?? 'frontpage') === 'guest');
            $request->setAttribute('user_id', (int)$decoded->sub);
            $request->setAttribute('is_guest', $isGuest);
            $request->setAttribute('auth_type', $isGuest ? 'guest' : 'frontpage');
            $request->setAttribute('user_role', $isGuest ? 'guest' : (string) ($decoded->role ?? 'user'));
            return true;
        } catch (\Throwable $e) {
            $response->error('Invalid token: ' . $e->getMessage(), 401);
            return false;
        }
    }
}
