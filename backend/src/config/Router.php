<?php

declare(strict_types=1);

namespace App\Config;

use Exception;

final class Router
{
    private array $routes = [];

    public function post(string $path, array|callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function get(string $path, array|callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function options(string $path, array|callable $handler): void
    {
        $this->addRoute('OPTIONS', $path, $handler);
    }

    public function delete(string $path, array|callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, array|callable $handler): void
    {
        // Convert /rambles/{id} to regex
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = "#^" . $pattern . "$#";
        
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    public function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = explode('?', $_SERVER['REQUEST_URI'] ?? '/')[0];

        // CORS is handled in public/index.php

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $handler = $route['handler'];

                if (is_callable($handler)) {
                    $handler($params);
                    return;
                }

                if (is_array($handler)) {
                    $controllerClass = $handler[0];
                    $methodName = $handler[1];

                    $factory = new ServiceFactory();
                    $controller = $factory->create($controllerClass);
                    
                    $controller->$methodName($params);
                    return;
                }
            }
        }

        http_response_code(404);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: ' . ($_ENV['CORS_ORIGIN'] ?? '*'));
        echo json_encode(['error' => 'Route not found: ' . $path]);
    }
}
