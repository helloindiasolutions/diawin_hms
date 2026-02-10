<?php
/**
 * Simple Query Cache System
 * Caches query results to reduce database load
 */

declare(strict_types=1);

namespace System;

class QueryCache
{
    private static array $cache = [];
    private static int $ttl = 60; // Cache TTL in seconds
    private static bool $enabled = true;

    /**
     * Get cached query result
     */
    public static function get(string $key): mixed
    {
        if (!self::$enabled) {
            return null;
        }

        if (!isset(self::$cache[$key])) {
            return null;
        }

        $item = self::$cache[$key];
        
        // Check if expired
        if (time() > $item['expires']) {
            unset(self::$cache[$key]);
            return null;
        }

        return $item['data'];
    }

    /**
     * Set cache item
     */
    public static function set(string $key, mixed $data, ?int $ttl = null): void
    {
        if (!self::$enabled) {
            return;
        }

        self::$cache[$key] = [
            'data' => $data,
            'expires' => time() + ($ttl ?? self::$ttl)
        ];
    }

    /**
     * Clear cache by key or pattern
     */
    public static function clear(?string $pattern = null): void
    {
        if ($pattern === null) {
            self::$cache = [];
            return;
        }

        foreach (array_keys(self::$cache) as $key) {
            if (str_contains($key, $pattern)) {
                unset(self::$cache[$key]);
            }
        }
    }

    /**
     * Enable/disable cache
     */
    public static function setEnabled(bool $enabled): void
    {
        self::$enabled = $enabled;
    }

    /**
     * Set default TTL
     */
    public static function setTTL(int $seconds): void
    {
        self::$ttl = $seconds;
    }

    /**
     * Get cache statistics
     */
    public static function getStats(): array
    {
        $total = count(self::$cache);
        $expired = 0;
        $now = time();

        foreach (self::$cache as $item) {
            if ($now > $item['expires']) {
                $expired++;
            }
        }

        return [
            'total' => $total,
            'active' => $total - $expired,
            'expired' => $expired,
            'enabled' => self::$enabled
        ];
    }
}
