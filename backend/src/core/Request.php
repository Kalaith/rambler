<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private array $params;
    private array $queryParams;
    private array $body;
    private array $headers;
    private array $attributes = [];

    public function __construct(array $params = [])
    {
        $this->params = $params;
        $this->queryParams = $_GET;
        $this->body = $this->parseBody();
        $this->headers = $this->getHeaders();
    }

    public function getParam(string $name, mixed $default = null): mixed
    {
        return $this->params[$name] ?? $this->queryParams[$name] ?? $default;
    }

    public function getQueryParam(string $name, mixed $default = null): mixed
    {
        return $this->queryParams[$name] ?? $default;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function getHeader(string $name): ?string
    {
        $name = strtolower($name);
        return $this->headers[$name] ?? null;
    }

    public function setAttribute(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    private function parseBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (str_contains($contentType, 'application/json')) {
            $input = file_get_contents('php://input');
            return json_decode($input, true) ?? [];
        }

        return $_POST;
    }

    private function getHeaders(): array
    {
        $headers = [];

        // Try standard PHP function first
        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                $headers[strtolower($name)] = $value;
            }
        }

        // Fallback to $_SERVER and common Apache variants
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = $value;
            } elseif ($key === 'CONTENT_TYPE') {
                $headers['content-type'] = $value;
            } elseif ($key === 'CONTENT_LENGTH') {
                $headers['content-length'] = $value;
            } elseif ($key === 'REDIRECT_HTTP_AUTHORIZATION') {
                // Common Apache/CGI fix
                $headers['authorization'] = $value;
            }
        }

        // If authorization is still missing, check for a common Apache issue
        if (!isset($headers['authorization']) && isset($_SERVER['PHP_AUTH_USER'])) {
            // Basic auth fallback (though we use Bearer, some setups might still use this)
            $headers['authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . ($_SERVER['PHP_AUTH_PW'] ?? ''));
        }

        return $headers;
    }
}
