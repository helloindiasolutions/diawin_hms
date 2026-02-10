<?php
/**
 * Secure Session Management
 */

declare(strict_types=1);

namespace System;

class Session
{
    private static bool $started = false;

    /**
     * Start secure session
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        session_name($_ENV['SESSION_NAME'] ?? 'SECURE_SESSION');

        session_set_cookie_params([
            'lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 7200),
            'path' => '/',
            'domain' => $_ENV['SESSION_DOMAIN'] ?? '',
            'secure' => Security::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        session_start();
        self::$started = true;

        self::regenerateIfNeeded();
    }

    /**
     * Regenerate session ID periodically
     */
    private static function regenerateIfNeeded(): void
    {
        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
        } elseif (time() - $_SESSION['_created'] > 300) {
            session_regenerate_id(true);
            $_SESSION['_created'] = time();
        }
    }

    /**
     * Get session value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session value
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Delete session key
     */
    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Flash message
     */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Get flash message
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Check if logged in
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get user ID
     */
    public static function userId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get user ID (alias)
     */
    public static function getUserId(): ?int
    {
        return self::userId();
    }

    /**
     * Get user data
     */
    public static function user(?string $key = null): mixed
    {
        $userData = $_SESSION['user_data'] ?? [];
        return $key === null ? $userData : ($userData[$key] ?? null);
    }

    /**
     * Get user data as array (for API compatibility)
     */
    public static function getUser(): ?array
    {
        if (!self::isLoggedIn()) {
            return null;
        }
        $userData = $_SESSION['user_data'] ?? [];
        $userData['user_id'] = $_SESSION['user_id'];
        return $userData;
    }

    /**
     * Login user
     */
    public static function login(int $userId, array $userData = []): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_data'] = $userData;
        $_SESSION['logged_in_at'] = time();
    }

    /**
     * Logout user
     */
    public static function logout(): void
    {
        // Clear all session data
        $_SESSION = [];

        // Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy the session
        session_destroy();
        self::$started = false;

        // Start a new session for flash messages
        self::start();
    }

    /**
     * Destroy session
     */
    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        self::$started = false;
    }
}
