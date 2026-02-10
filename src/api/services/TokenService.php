<?php
/**
 * Token Service
 * Handles API token generation and validation
 */

declare(strict_types=1);

namespace App\Api\Services;

use Security;
use Env;

class TokenService
{
    private const TOKEN_EXPIRY = 3600; // 1 hour
    private const REFRESH_TOKEN_EXPIRY = 604800; // 7 days

    /**
     * Generate access token
     */
    public function generateAccessToken(int $userId): string
    {
        $payload = [
            'user_id' => $userId,
            'type' => 'access',
            'exp' => time() + self::TOKEN_EXPIRY,
            'iat' => time(),
            'jti' => bin2hex(random_bytes(16))
        ];

        return $this->encodeToken($payload);
    }

    /**
     * Generate refresh token
     */
    public function generateRefreshToken(int $userId): string
    {
        $payload = [
            'user_id' => $userId,
            'type' => 'refresh',
            'exp' => time() + self::REFRESH_TOKEN_EXPIRY,
            'iat' => time(),
            'jti' => bin2hex(random_bytes(16))
        ];

        return $this->encodeToken($payload);
    }

    /**
     * Validate and decode token
     */
    public function validateToken(string $token): ?array
    {
        $payload = $this->decodeToken($token);

        if (!$payload) {
            return null;
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * Encode token payload
     */
    private function encodeToken(array $payload): string
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode($payload));
        $signature = $this->sign("$header.$payload");

        return "$header.$payload.$signature";
    }

    /**
     * Decode token
     */
    private function decodeToken(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        // Verify signature
        $expectedSignature = $this->sign("$header.$payload");
        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $decoded = json_decode(base64_decode($payload), true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Sign token data
     */
    private function sign(string $data): string
    {
        $key = Env::get('APP_KEY', '');
        return base64_encode(hash_hmac('sha256', $data, $key, true));
    }

    /**
     * Get token expiry time
     */
    public function getAccessTokenExpiry(): int
    {
        return self::TOKEN_EXPIRY;
    }

    /**
     * Get refresh token expiry time
     */
    public function getRefreshTokenExpiry(): int
    {
        return self::REFRESH_TOKEN_EXPIRY;
    }

    /**
     * Revoke token (store in blacklist)
     */
    public function revokeToken(string $tokenId): bool
    {
        // TODO: Store revoked token ID in database or cache
        // db()->insert('revoked_tokens', [
        //     'token_id' => $tokenId,
        //     'revoked_at' => date('Y-m-d H:i:s')
        // ]);

        return true;
    }

    /**
     * Check if token is revoked
     */
    public function isTokenRevoked(string $tokenId): bool
    {
        // TODO: Check if token ID exists in revoked tokens
        // $revoked = db()->fetch("SELECT * FROM revoked_tokens WHERE token_id = ?", [$tokenId]);
        // return $revoked !== null;

        return false;
    }
}
