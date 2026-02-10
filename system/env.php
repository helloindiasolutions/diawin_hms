<?php
/**
 * Environment Loader (Fallback when Composer not installed)
 */

declare(strict_types=1);

class Env
{
    private static bool $loaded = false;

    /**
     * Load environment variables
     */
    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = self::detectEnvFile();

        if ($envFile && file_exists($envFile)) {
            self::parseEnvFile($envFile);
        }

        self::$loaded = true;
    }

    /**
     * Detect which .env file to use
     */
    private static function detectEnvFile(): ?string
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';

        $basePath = ROOT_PATH;

        // Production environment
        if ($host === 'diawinhms.mindmagik.in') {
            $envFile = $basePath . '/.env.prod';
            if (file_exists($envFile)) {
                return $envFile;
            }
        }

        // Local environment
        $isLocalhost = in_array($host, ['localhost', '127.0.0.1'])
            || strpos($host, 'localhost:') === 0
            || strpos($host, '127.0.0.1:') === 0;

        if ($isLocalhost) {
            $envFile = $basePath . '/.env.local';
            if (file_exists($envFile)) {
                return $envFile;
            }
        }

        // Fallback to .env if it exists (for compatibility during transition)
        $defaultEnv = $basePath . '/.env';
        return file_exists($defaultEnv) ? $defaultEnv : null;
    }

    /**
     * Parse .env file
     */
    private static function parseEnvFile(string $filePath): void
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, '"\'');

            // Handle special values
            $lower = strtolower($value);
            $value = match ($lower) {
                'true', '(true)' => true,
                'false', '(false)' => false,
                'null', '(null)' => null,
                'empty', '(empty)' => '',
                default => $value
            };

            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }

    /**
     * Get environment variable
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}
