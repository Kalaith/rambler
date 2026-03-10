<?php

declare(strict_types=1);

namespace App\Middleware;

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
        $secret = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: '';

        // Debug logging
        $maskedSecret = substr($secret, 0, 3) . '...' . substr($secret, -3);
        $source = isset($_ENV['JWT_SECRET']) ? '$_ENV' : (isset($_SERVER['JWT_SECRET']) ? '$_SERVER' : (getenv('JWT_SECRET') ? 'getenv' : 'none'));
        error_log("JWT Verify - Source: $source, Secret: $maskedSecret, Token Prefix: " . substr($token, 0, 10));

        if (empty($secret)) {
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
