<?php
/**
 * JWT Authentication Service
 * Uses Firebase PHP-JWT library
 */

declare(strict_types=1);

namespace System;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Exception;

class JWT
{
    private static string $algorithm = 'HS256';
    private static int $accessTokenExpiry = 3600;      // 1 hour
    private static int $refreshTokenExpiry = 604800;   // 7 days

    /**
     * Generate access token
     */
    public static function generateAccessToken(array $payload): string
    {
        $issuedAt = time();
        $expire = $issuedAt + self::$accessTokenExpiry;

        $tokenPayload = [
            'iss' => $_ENV['APP_URL'] ?? 'localhost',
            'aud' => $_ENV['APP_URL'] ?? 'localhost',
            'iat' => $issuedAt,
            'nbf' => $issuedAt,
            'exp' => $expire,
            'type' => 'access',
            'jti' => bin2hex(random_bytes(16)),
            'data' => $payload
        ];

        return FirebaseJWT::encode($tokenPayload, self::getSecretKey(), self::$algorithm);
    }

    /**
     * Generate refresh token
     */
    public static function generateRefreshToken(array $payload): string
    {
        $issuedAt = time();
        $expire = $issuedAt + self::$refreshTokenExpiry;

        $tokenPayload = [
            'iss' => $_ENV['APP_URL'] ?? 'localhost',
            'aud' => $_ENV['APP_URL'] ?? 'localhost',
            'iat' => $issuedAt,
            'nbf' => $issuedAt,
            'exp' => $expire,
            'type' => 'refresh',
            'jti' => bin2hex(random_bytes(16)),
            'data' => $payload
        ];

        return FirebaseJWT::encode($tokenPayload, self::getSecretKey(), self::$algorithm);
    }

    /**
     * Generate both tokens
     */
    public static function generateTokenPair(array $payload): array
    {
        return [
            'access_token' => self::generateAccessToken($payload),
            'refresh_token' => self::generateRefreshToken($payload),
            'token_type' => 'Bearer',
            'expires_in' => self::$accessTokenExpiry
        ];
    }

    /**
     * Validate and decode token
     */
    public static function validate(string $token): ?object
    {
        try {
            $decoded = FirebaseJWT::decode($token, new Key(self::getSecretKey(), self::$algorithm));
            return $decoded;
        } catch (ExpiredException $e) {
            return null;
        } catch (SignatureInvalidException $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Validate access token specifically
     */
    public static function validateAccessToken(string $token): ?object
    {
        $decoded = self::validate($token);
        
        if ($decoded && isset($decoded->type) && $decoded->type === 'access') {
            return $decoded;
        }
        
        return null;
    }

    /**
     * Validate refresh token specifically
     */
    public static function validateRefreshToken(string $token): ?object
    {
        $decoded = self::validate($token);
        
        if ($decoded && isset($decoded->type) && $decoded->type === 'refresh') {
            return $decoded;
        }
        
        return null;
    }

    /**
     * Get payload data from token
     */
    public static function getPayload(string $token): ?array
    {
        $decoded = self::validate($token);
        
        if ($decoded && isset($decoded->data)) {
            return (array) $decoded->data;
        }
        
        return null;
    }

    /**
     * Get user ID from token
     */
    public static function getUserId(string $token): ?int
    {
        $payload = self::getPayload($token);
        return $payload['user_id'] ?? null;
    }

    /**
     * Check if token is expired
     */
    public static function isExpired(string $token): bool
    {
        try {
            FirebaseJWT::decode($token, new Key(self::getSecretKey(), self::$algorithm));
            return false;
        } catch (ExpiredException $e) {
            return true;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Get token from Authorization header
     */
    public static function getTokenFromHeader(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get secret key
     */
    private static function getSecretKey(): string
    {
        $key = $_ENV['JWT_SECRET'] ?? $_ENV['APP_KEY'] ?? '';
        
        if (empty($key)) {
            throw new \RuntimeException('JWT_SECRET or APP_KEY not configured');
        }
        
        return $key;
    }

    /**
     * Set token expiry times
     */
    public static function setExpiry(int $accessExpiry, int $refreshExpiry): void
    {
        self::$accessTokenExpiry = $accessExpiry;
        self::$refreshTokenExpiry = $refreshExpiry;
    }

    /**
     * Get remaining time until expiry
     */
    public static function getTimeUntilExpiry(string $token): int
    {
        $decoded = self::validate($token);
        
        if ($decoded && isset($decoded->exp)) {
            return max(0, $decoded->exp - time());
        }
        
        return 0;
    }
}
