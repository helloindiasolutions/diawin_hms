<?php
/**
 * Security Middleware
 * Additional security checks for requests
 */

declare(strict_types=1);

namespace App\Middleware;

use Security;
use Logger;
use Response;

class SecurityMiddleware
{
    /**
     * Handle security checks
     */
    public function handle(array $params = []): bool
    {
        // Check for blocked IPs
        if ($this->isIpBlocked()) {
            $this->handleBlocked('IP address is blocked');
            return false;
        }

        // Check for suspicious patterns in request
        if ($this->detectSuspiciousRequest()) {
            return false;
        }

        // Validate request headers
        if (!$this->validateHeaders()) {
            return false;
        }

        return true;
    }

    /**
     * Check if IP is blocked
     */
    private function isIpBlocked(): bool
    {
        return Security::isIpBlocked();
    }

    /**
     * Detect suspicious request patterns
     */
    private function detectSuspiciousRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Check for SQL injection in URL
        if (Security::detectSqlInjection($uri) || Security::detectSqlInjection($queryString)) {
            Logger::security('SQL injection attempt detected in URL', [
                'uri' => $uri,
                'query' => $queryString
            ]);
            $this->handleBlocked('Malicious request detected');
            return true;
        }

        // Check for XSS in URL
        if (Security::detectXss($uri) || Security::detectXss($queryString)) {
            Logger::security('XSS attempt detected in URL', [
                'uri' => $uri,
                'query' => $queryString
            ]);
            $this->handleBlocked('Malicious request detected');
            return true;
        }

        // Check for path traversal
        if ($this->detectPathTraversal($uri)) {
            Logger::security('Path traversal attempt detected', ['uri' => $uri]);
            $this->handleBlocked('Malicious request detected');
            return true;
        }

        // Check for suspicious user agents
        if ($this->isSuspiciousUserAgent($userAgent)) {
            Logger::security('Suspicious user agent detected', ['user_agent' => $userAgent]);
            // Don't block, just log
        }

        return false;
    }

    /**
     * Detect path traversal attempts
     */
    private function detectPathTraversal(string $uri): bool
    {
        $patterns = [
            '/\.\.\//',
            '/\.\.\\\\/',
            '/%2e%2e%2f/i',
            '/%2e%2e\//i',
            '/\.%2e\//i',
            '/%2e\.\//i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $uri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for suspicious user agents
     */
    private function isSuspiciousUserAgent(string $userAgent): bool
    {
        $suspiciousPatterns = [
            '/sqlmap/i',
            '/nikto/i',
            '/nmap/i',
            '/masscan/i',
            '/dirbuster/i',
            '/gobuster/i',
            '/wpscan/i',
            '/acunetix/i',
            '/nessus/i',
            '/burpsuite/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate request headers
     */
    private function validateHeaders(): bool
    {
        // Check for oversized headers
        $headers = getallheaders();
        
        foreach ($headers as $name => $value) {
            if (strlen($value) > 8192) {
                Logger::security('Oversized header detected', [
                    'header' => $name,
                    'size' => strlen($value)
                ]);
                $this->handleBlocked('Invalid request');
                return false;
            }
        }

        // Check for required headers in API requests
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/api') !== false) {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            
            if (in_array($method, ['POST', 'PUT', 'PATCH']) && 
                !empty(file_get_contents('php://input')) &&
                strpos($contentType, 'application/json') === false &&
                strpos($contentType, 'multipart/form-data') === false &&
                strpos($contentType, 'application/x-www-form-urlencoded') === false) {
                // Log but don't block - some clients may not set content-type
                Logger::warning('Missing or invalid Content-Type header', [
                    'content_type' => $contentType,
                    'method' => $method
                ]);
            }
        }

        return true;
    }

    /**
     * Handle blocked request
     */
    private function handleBlocked(string $message): void
    {
        http_response_code(403);
        
        if ($this->isAjaxRequest()) {
            Response::json(['error' => $message], 403);
        } else {
            echo $message;
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
}
