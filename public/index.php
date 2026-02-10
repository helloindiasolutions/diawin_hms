<?php
/**
 * Main Entry Point
 * All requests are routed through this file
 */

declare(strict_types=1);

// Define base paths
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('SYSTEM_PATH', ROOT_PATH . '/system');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Prevent PHP warnings from breaking the HTML layout
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Serve static files from assets folder (for PHP built-in server)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Handle assets - they are in ROOT/assets, not PUBLIC/assets
if (preg_match('/^\/assets\//', $requestUri)) {
    $filePath = ROOT_PATH . $requestUri;

    // Debug: uncomment to see path issues
    // error_log("Asset request: $requestUri -> $filePath (exists: " . (file_exists($filePath) ? 'yes' : 'no') . ")");

    if (file_exists($filePath) && is_file($filePath)) {
        // Get MIME type
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'pdf' => 'application/pdf',
        ];
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = $mimeTypes[$ext] ?? mime_content_type($filePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=31536000');
        readfile($filePath);
        exit;
    }
}

// Load Composer autoloader
$autoloader = ROOT_PATH . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;

    // Also load fallback autoloader for additional support
    require_once SYSTEM_PATH . '/autoload.php';

    // Determine which environment file to load
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $envFileName = '.env';

    if ($host === 'diawinhms.mindmagik.in') {
        $envFileName = '.env.prod';
    } elseif (in_array($host, ['localhost', '127.0.0.1']) || strpos($host, 'localhost:') === 0 || strpos($host, '127.0.0.1:') === 0) {
        $envFileName = '.env.local';
    }

    // Load .env using vlucas/phpdotenv
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH, $envFileName);
        $dotenv->safeLoad();

        // If the specific file wasn't found and we aren't using .env, fallback to .env just in case
        if ($envFileName !== '.env' && !file_exists(ROOT_PATH . '/' . $envFileName)) {
            $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
            $dotenv->safeLoad();
        }
    } else {
        // Fallback if Dotenv not available
        require_once SYSTEM_PATH . '/env.php';
        \Env::load();
    }
} else {
    // Fallback to manual loading if Composer not installed
    require_once SYSTEM_PATH . '/autoload.php';
    require_once SYSTEM_PATH . '/env.php';
    \Env::load();
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Kolkata');

// Load Response first (needed by other classes)
require_once SYSTEM_PATH . '/Response.php';
require_once SYSTEM_PATH . '/Logger.php';
require_once SYSTEM_PATH . '/Security.php';
require_once SYSTEM_PATH . '/Session.php';
require_once SYSTEM_PATH . '/CSRF.php';
require_once SYSTEM_PATH . '/JWT.php';
require_once SYSTEM_PATH . '/Database.php';
require_once SYSTEM_PATH . '/Router.php';
require_once SYSTEM_PATH . '/Middleware.php';
require_once SYSTEM_PATH . '/ErrorHandler.php';

use System\ErrorHandler;
use System\Security;
use System\Session;
use System\Router;

// Initialize error handler
ErrorHandler::register();

// Initialize security headers
Security::init();

// Start secure session
Session::start();

// Get request URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = $_ENV['BASE_PATH'] ?? '';

// Remove base path from URI
if (!empty($basePath) && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath)) ?: '/';
}

// Route API requests
if (strpos($requestUri, '/api') === 0) {
    require_once SRC_PATH . '/api/index.php';
    exit;
}

// Load and dispatch web routes
$router = new Router();
require_once SRC_PATH . '/routes/web.php';
$router->dispatch();
