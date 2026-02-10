<?php
/**
 * Global Helper Functions
 * Loaded via Composer autoload
 */

declare(strict_types=1);

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $status = 302): void
    {
        \System\Router::redirect($url, $status);
    }
}

if (!function_exists('baseUrl')) {
    function baseUrl(string $path = ''): string
    {
        $basePath = $_ENV['BASE_PATH'] ?? '';
        $path = ltrim($path, '/');
        return $basePath . ($path ? '/' . $path : '');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $basePath = $_ENV['BASE_PATH'] ?? '';
        return $basePath . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('csrfField')) {
    function csrfField(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . e(csrfToken()) . '">';
    }
}

if (!function_exists('csrfMeta')) {
    function csrfMeta(): string
    {
        return '<meta name="csrf-token" content="' . e(csrfToken()) . '">';
    }
}

if (!function_exists('csrfToken')) {
    function csrfToken(): string
    {
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('session')) {
    function session(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}

if (!function_exists('auth')) {
    function auth(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('user')) {
    function user(?string $key = null): mixed
    {
        $userData = $_SESSION['user_data'] ?? [];
        return $key === null ? $userData : ($userData[$key] ?? null);
    }
}

if (!function_exists('userId')) {
    function userId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }
}

if (!function_exists('view')) {
    function view(string $name, array $data = []): void
    {
        extract($data);
        $viewPath = SRC_PATH . '/views/' . str_replace('.', '/', $name) . '.php';
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            throw new RuntimeException("View not found: {$name}");
        }
    }
}

if (!function_exists('input')) {
    function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}

if (!function_exists('jsonInput')) {
    function jsonInput(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_flash']['old_input'][$key] ?? $default;
    }
}

if (!function_exists('storeOldInput')) {
    function storeOldInput(): void
    {
        $input = $_POST;
        unset($input['password'], $input['password_confirmation'], $input['_csrf_token']);
        $_SESSION['_flash']['old_input'] = $input;
    }
}

if (!function_exists('formatDate')) {
    function formatDate(string|int|null $date, string $format = 'd M Y'): string
    {
        if (empty($date)) return '';
        if (is_string($date)) $date = strtotime($date);
        return date($format, $date);
    }
}

if (!function_exists('formatDateTime')) {
    function formatDateTime(string|int|null $date, string $format = 'd M Y H:i'): string
    {
        return formatDate($date, $format);
    }
}

if (!function_exists('abort')) {
    function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        $errorPage = SRC_PATH . "/views/errors/{$code}.php";
        
        if (file_exists($errorPage)) {
            require_once $errorPage;
        } else {
            echo $message ?: "Error {$code}";
        }
        exit;
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): void
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        exit;
    }
}

if (!function_exists('dump')) {
    function dump(mixed ...$vars): void
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
    }
}
