<?php
/**
 * API Entry Point
 * Handles all API requests with JWT authentication
 */

declare(strict_types=1);

use System\Response;
use System\Router;
use System\Middleware;
use System\Logger;

// Set JSON content type
header('Content-Type: application/json');

// Apply CORS
Middleware::cors([]);

// Get request info
$method = $_SERVER['REQUEST_METHOD'];

// Handle OPTIONS preflight immediately
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Track request timing
$startTime = microtime(true);

// Create API router
$apiRouter = new Router();

// Load API routes
require_once SRC_PATH . '/routes/api.php';

// Get URI for logging
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = $_ENV['BASE_PATH'] ?? '';
if (!empty($basePath) && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Dispatch request
try {
    $apiRouter->dispatch();
} catch (Throwable $e) {
    Logger::error('API Error', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);

    $response = ['success' => false, 'message' => 'Internal Server Error'];

    if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
        $response['debug'] = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }

    Response::json($response, 500);
}

// Log API request
$duration = microtime(true) - $startTime;
Logger::api($method, $uri, http_response_code(), $duration);
