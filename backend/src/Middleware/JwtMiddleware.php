<?php

declare(strict_types=1);

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

final class JwtMiddleware
{
    /**
     * Authenticates the request and returns the user ID.
     * Throws an exception or exits with a JSON error if unauthorized.
     */
    public function authenticate(): int
    {
        $headers = function_exists('getallheaders') ? getallheaders() : $this->getHeadersManual();
        $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!$authorization || !preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            $this->unauthorized('Missing or invalid token');
            exit(); 
        }

        $token = $matches[1];
        $secret = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? 'rambler_secret_change_me';

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            return (int)$decoded->sub;
        } catch (\Throwable $e) {
            $this->unauthorized('Invalid token: ' . $e->getMessage());
            exit();
        }
    }

    private function unauthorized(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized: ' . $message]);
    }

    private function getHeadersManual(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }
}
