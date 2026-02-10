<?php
/**
 * Middleware System with JWT Support
 */

declare(strict_types=1);

namespace System;

class Middleware
{
    /**
     * JWT Authentication middleware
     */
    public static function jwtAuth(array $params = []): bool
    {
        $token = JWT::getTokenFromHeader();

        if (!$token) {
            Response::json([
                'success' => false,
                'message' => 'No authentication token provided'
            ], 401);
            exit;
        }

        $decoded = JWT::validateAccessToken($token);

        if (!$decoded) {
            Response::json([
                'success' => false,
                'message' => 'Invalid or expired token'
            ], 401);
            exit;
        }

        // Store user data for use in controllers
        $GLOBALS['jwt_user'] = (array) $decoded->data;
        $GLOBALS['jwt_token'] = $decoded;

        return true;
    }

    /**
     * Web session authentication
     */
    public static function auth(array $params = []): bool
    {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            // Check if it's an AJAX/Fetch request or an API request
            if (self::isAjaxRequest() || strpos($_SERVER['REQUEST_URI'], '/api') !== false) {
                Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
                exit;
            }

            // Only store intended URL for non-API web requests
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . ($_ENV['BASE_PATH'] ?? '') . '/login');
            exit;
        }

        return true;
    }

    /**
     * Guest middleware (not logged in)
     */
    public static function guest(array $params = []): bool
    {
        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
            header('Location: ' . ($_ENV['BASE_PATH'] ?? '') . '/dashboard');
            exit;
        }

        return true;
    }

    /**
     * CSRF protection
     */
    public static function csrf(array $params = []): bool
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return true;
        }

        $token = $_POST['_csrf_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? null;

        if (!$token || !isset($_SESSION['_csrf_token']) || !hash_equals($_SESSION['_csrf_token'], $token)) {
            if (self::isAjaxRequest()) {
                Response::json(['success' => false, 'message' => 'CSRF token mismatch'], 403);
            } else {
                http_response_code(403);
                echo 'CSRF token validation failed';
            }
            exit;
        }

        return true;
    }

    /**
     * Rate limiting
     */
    public static function rateLimit(array $params = [], int $maxRequests = 60, int $windowSeconds = 60): bool
    {
        $ip = Security::getClientIp();
        $key = 'rate_' . md5($ip . $_SERVER['REQUEST_URI']);
        $cacheFile = STORAGE_PATH . '/cache/' . $key . '.json';

        // Ensure cache directory exists
        if (!is_dir(STORAGE_PATH . '/cache')) {
            mkdir(STORAGE_PATH . '/cache', 0755, true);
        }

        $data = ['count' => 0, 'reset' => time() + $windowSeconds];

        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true) ?? $data;
            if (time() > $data['reset']) {
                $data = ['count' => 0, 'reset' => time() + $windowSeconds];
            }
        }

        $data['count']++;
        file_put_contents($cacheFile, json_encode($data));

        header('X-RateLimit-Limit: ' . $maxRequests);
        header('X-RateLimit-Remaining: ' . max(0, $maxRequests - $data['count']));
        header('X-RateLimit-Reset: ' . $data['reset']);

        if ($data['count'] > $maxRequests) {
            Response::json([
                'success' => false,
                'message' => 'Too many requests',
                'retry_after' => $data['reset'] - time()
            ], 429);
            exit;
        }

        return true;
    }

    /**
     * CORS middleware
     */
    public static function cors(array $params = []): bool
    {
        $allowedOrigins = explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? '*');
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');

        return true;
    }

    /**
     * Role-based access
     */
    public static function role(array $params = [], string|array $roles = []): bool
    {
        $user = $GLOBALS['jwt_user'] ?? $_SESSION['user_data'] ?? null;

        if (!$user) {
            Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
            exit;
        }

        $userRole = $user['role'] ?? '';
        $allowedRoles = is_array($roles) ? $roles : [$roles];

        if (!in_array($userRole, $allowedRoles)) {
            Response::json(['success' => false, 'message' => 'Forbidden'], 403);
            exit;
        }

        return true;
    }

    /**
     * Check if AJAX request
     */
    private static function isAjaxRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
}
