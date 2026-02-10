<?php
/**
 * Rate Limit Middleware
 * Advanced rate limiting with multiple strategies
 */

declare(strict_types=1);

namespace App\Middleware;

use Security;
use Response;
use Logger;

class RateLimitMiddleware
{
    private string $cacheDir;
    private int $maxRequests;
    private int $windowSeconds;
    private string $keyPrefix;

    public function __construct(int $maxRequests = 60, int $windowSeconds = 60, string $keyPrefix = 'rate_limit')
    {
        $this->cacheDir = STORAGE_PATH . '/cache';
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
        $this->keyPrefix = $keyPrefix;
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Handle rate limiting
     */
    public function handle(array $params = []): bool
    {
        $identifier = $this->getIdentifier();
        $key = $this->keyPrefix . '_' . md5($identifier);
        $cacheFile = $this->cacheDir . '/' . $key . '.json';

        $data = $this->getData($cacheFile);
        
        // Check if window has expired
        if (time() > $data['reset']) {
            $data = $this->resetData();
        }

        $data['count']++;
        $this->saveData($cacheFile, $data);

        // Set rate limit headers
        $this->setHeaders($data);

        // Check if limit exceeded
        if ($data['count'] > $this->maxRequests) {
            $this->handleLimitExceeded($data);
            return false;
        }

        return true;
    }

    /**
     * Get unique identifier for rate limiting
     */
    private function getIdentifier(): string
    {
        $ip = Security::getClientIp();
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // For authenticated users, use user ID
        if (isset($_SESSION['user_id'])) {
            return 'user_' . $_SESSION['user_id'] . '_' . $uri;
        }
        
        return 'ip_' . $ip . '_' . $uri;
    }

    /**
     * Get rate limit data from cache
     */
    private function getData(string $cacheFile): array
    {
        if (file_exists($cacheFile)) {
            $content = file_get_contents($cacheFile);
            $data = json_decode($content, true);
            
            if (is_array($data) && isset($data['count'], $data['reset'])) {
                return $data;
            }
        }
        
        return $this->resetData();
    }

    /**
     * Reset rate limit data
     */
    private function resetData(): array
    {
        return [
            'count' => 0,
            'reset' => time() + $this->windowSeconds,
            'first_request' => time()
        ];
    }

    /**
     * Save rate limit data to cache
     */
    private function saveData(string $cacheFile, array $data): void
    {
        file_put_contents($cacheFile, json_encode($data), LOCK_EX);
    }

    /**
     * Set rate limit headers
     */
    private function setHeaders(array $data): void
    {
        $remaining = max(0, $this->maxRequests - $data['count']);
        
        header('X-RateLimit-Limit: ' . $this->maxRequests);
        header('X-RateLimit-Remaining: ' . $remaining);
        header('X-RateLimit-Reset: ' . $data['reset']);
    }

    /**
     * Handle rate limit exceeded
     */
    private function handleLimitExceeded(array $data): void
    {
        $retryAfter = $data['reset'] - time();
        
        Logger::warning('Rate limit exceeded', [
            'ip' => Security::getClientIp(),
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'retry_after' => $retryAfter
        ]);

        http_response_code(429);
        header('Retry-After: ' . $retryAfter);

        if ($this->isAjaxRequest()) {
            Response::json([
                'error' => 'Too many requests',
                'retry_after' => $retryAfter
            ], 429);
        } else {
            echo 'Too Many Requests. Please try again in ' . $retryAfter . ' seconds.';
        }
        
        exit;
    }

    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Clean expired rate limit files
     */
    public static function cleanExpired(): int
    {
        $cacheDir = STORAGE_PATH . '/cache';
        $count = 0;
        
        $files = glob($cacheDir . '/rate_limit_*.json');
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $data = json_decode($content, true);
            
            if (!$data || (isset($data['reset']) && $data['reset'] < time())) {
                unlink($file);
                $count++;
            }
        }
        
        return $count;
    }
}
