<?php
/**
 * Security System
 */

declare(strict_types=1);

namespace System;

class Security
{
    /**
     * Initialize security measures
     */
    public static function init(): void
    {
        self::setSecurityHeaders();
        self::sanitizeGlobals();
    }

    /**
     * Set security headers
     */
    private static function setSecurityHeaders(): void
    {
        if (headers_sent())
            return;

        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header_remove('X-Powered-By');

        if (self::isHttps() && ($_ENV['APP_ENV'] ?? '') === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    /**
     * Check HTTPS
     */
    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? 80) == 443
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    /**
     * Sanitize global arrays
     */
    private static function sanitizeGlobals(): void
    {
        $_GET = self::sanitizeArray($_GET);
        $_POST = self::sanitizeArray($_POST);
        $_COOKIE = self::sanitizeArray($_COOKIE);
    }

    /**
     * Sanitize array recursively
     */
    public static function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $key = preg_replace('/[^a-zA-Z0-9_\-\[\]]/', '', (string) $key);
            $sanitized[$key] = is_array($value) ? self::sanitizeArray($value) : self::sanitizeInput($value);
        }
        return $sanitized;
    }

    /**
     * Sanitize input
     */
    public static function sanitizeInput(mixed $value): mixed
    {
        if (!is_string($value))
            return $value;
        $value = str_replace(chr(0), '', $value);
        return trim($value);
    }

    /**
     * Get client IP
     */
    public static function getClientIp(): string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Escape HTML
     */
    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Detect SQL injection
     */
    public static function detectSqlInjection(string $input): bool
    {
        $patterns = [
            '/(\%27)|(\')|(\-\-)|(\%23)|(#)/i',
            '/UNION(\s+)SELECT/i',
            '/INSERT(\s+)INTO/i',
            '/DELETE(\s+)FROM/i',
            '/DROP(\s+)TABLE/i',
            '/UPDATE(\s+)\w+(\s+)SET/i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input))
                return true;
        }
        return false;
    }

    /**
     * Detect XSS
     */
    public static function detectXss(string $input): bool
    {
        $patterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<\s*iframe/i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input))
                return true;
        }
        return false;
    }

    /**
     * Hash password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verify password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate token
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Sanitize filename
     */
    public static function sanitizeFilename(string $filename): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-\.]/', '', basename($filename));
    }

    /**
     * Validate email address
     */
    public static function isValidEmail(string $email): bool
    {
        if (empty($email))
            return false;
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate phone number (10 digits)
     */
    public static function isValidPhone(string $phone): bool
    {
        if (empty($phone))
            return false;
        // Strip everything except numbers to check the length
        $numeric = preg_replace('/[^0-9]/', '', $phone);
        return strlen($numeric) === 10;
    }

    /**
     * Obfuscate/Encrypt a numeric ID or string for URLs
     */
    public static function encrypt(mixed $data): string
    {
        $key = $_ENV['APP_KEY'] ?? 'melina-hms-secret-key';
        $iv = substr(hash('sha256', $key), 0, 16);
        $encrypted = openssl_encrypt((string) $data, 'AES-256-CBC', $key, 0, $iv);
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($encrypted));
    }

    /**
     * De-obfuscate/Decrypt a string from URLs
     */
    public static function decrypt(string $data): ?string
    {
        $key = $_ENV['APP_KEY'] ?? 'melina-hms-secret-key';
        $iv = substr(hash('sha256', $key), 0, 16);
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        $decoded = base64_decode($data);
        if (!$decoded)
            return null;
        $decrypted = openssl_decrypt($decoded, 'AES-256-CBC', $key, 0, $iv);
        return $decrypted ?: null;
    }
}
