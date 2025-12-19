<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Router;
use App\Controllers\AuthController;
use App\Controllers\RambleController;
use Dotenv\Dotenv;

// Custom autoloader for App classes
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $path = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($path)) {
            require $path;
        }
    }
}, true, true);

// Load Environment
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Get CORS origin
$allowedOrigin = $_ENV['CORS_ORIGIN'] ?? '*';

// Handle CORS preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: ' . $allowedOrigin);
    header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
    header('Access-Control-Max-Age: 86400');
    exit(0);
}

// Global CORS header for all other requests
header('Access-Control-Allow-Origin: ' . $allowedOrigin);

$router = new Router();

// Routes
$router->post('/login', [AuthController::class, 'login']);
$router->post('/register', [AuthController::class, 'register']);

$router->post('/rambles', [RambleController::class, 'capture']);
$router->get('/rambles', [RambleController::class, 'list']);
$router->post('/rambles/{id}/process', [RambleController::class, 'process']);

$router->get('/health', function() {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
});

// Handle request
try {
    $router->handle();
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Internal Server Error',
        'error' => $e->getMessage()
    ]);
}