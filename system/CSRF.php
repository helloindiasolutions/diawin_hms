<?php
/**
 * CSRF Protection
 */

declare(strict_types=1);

namespace System;

class CSRF
{
    private const TOKEN_NAME = '_csrf_token';

    public static function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::TOKEN_NAME] = $token;
        return $token;
    }

    public static function getToken(): string
    {
        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return self::generateToken();
        }
        return $_SESSION[self::TOKEN_NAME];
    }

    public static function validate(?string $token): bool
    {
        if (empty($token) || !isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }
        return hash_equals($_SESSION[self::TOKEN_NAME], $token);
    }

    public static function verify(): bool
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return true;
        }

        $token = $_POST[self::TOKEN_NAME] 
            ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
            ?? null;

        return self::validate($token);
    }

    public static function field(): string
    {
        return sprintf('<input type="hidden" name="%s" value="%s">', self::TOKEN_NAME, Security::escape(self::getToken()));
    }

    public static function meta(): string
    {
        return sprintf('<meta name="csrf-token" content="%s">', Security::escape(self::getToken()));
    }

    public static function protect(): void
    {
        if (!self::verify()) {
            Logger::warning('CSRF validation failed', ['ip' => Security::getClientIp()]);
            http_response_code(403);
            echo 'CSRF token validation failed';
            exit;
        }
    }
}
