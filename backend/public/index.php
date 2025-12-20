<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Manual autoloader for App classes - prepend to override stale composer mappings in preview
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $path = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }
}, true, true);

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\RambleController;
use App\Controllers\DatabaseController;
use App\Controllers\KofiWebhookController;
use App\Controllers\HealthController;
use App\Middleware\JwtMiddleware;
use App\Core\Response;
use Dotenv\Dotenv;

// Load Environment
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Throwable $e) {
    // Fail silently or handle as needed
}

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

// Auto-detect base path (e.g., /rambler/api) based on the request
$basePath = $_ENV['API_BASE_PATH'] ?? '';
if (empty($basePath)) {
    if (preg_match('/^(.*\/api)/', $_SERVER['REQUEST_URI'] ?? '', $matches)) {
        $basePath = $matches[1];
    }
}
$router->setBasePath($basePath);

// Routes
$router->post('/login', [AuthController::class, 'login']);
$router->post('/register', [AuthController::class, 'register']);

// Protected Routes
$router->post('/rambles', [RambleController::class, 'capture'], [JwtMiddleware::class]);
$router->get('/rambles', [RambleController::class, 'list'], [JwtMiddleware::class]);
$router->put('/rambles/{id}', [RambleController::class, 'update'], [JwtMiddleware::class]);
$router->delete('/rambles/{id}', [RambleController::class, 'delete'], [JwtMiddleware::class]);
$router->post('/rambles/{id}/process', [RambleController::class, 'process'], [JwtMiddleware::class]);
$router->get('/usage', [RambleController::class, 'getUsage'], [JwtMiddleware::class]);

// Other Routes
$router->post('/webhooks/kofi', [KofiWebhookController::class, 'handle']);
$router->get('/db-init', [DatabaseController::class, 'init']);
$router->get('/db/init', [DatabaseController::class, 'init']);
$router->get('/health', [HealthController::class, 'check']);

// Handle request
try {
    $router->handle();
} catch (\Throwable $e) {
    (new Response())->error('Internal Server Error: ' . $e->getMessage(), 500);
}