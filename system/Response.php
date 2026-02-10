<?php
/**
 * Response Class
 */

declare(strict_types=1);

namespace System;

class Response
{
    /**
     * Send JSON response
     */
    public static function json(mixed $data, int $statusCode = 200, array $headers = []): void
    {
        http_response_code($statusCode);
        
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            
            foreach ($headers as $name => $value) {
                header("$name: $value");
            }
        }
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send success JSON response
     */
    public static function success(mixed $data = null, string $message = 'Success', int $statusCode = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Send error JSON response
     */
    public static function error(string $message, int $statusCode = 400, array $errors = []): void
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        self::json($response, $statusCode);
    }

    /**
     * Send validation error response
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): void
    {
        $errors = array_filter($errors, fn($v) => $v !== null);
        
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], 422);
    }

    /**
     * Send unauthorized response
     */
    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, 401);
    }

    /**
     * Send forbidden response
     */
    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, 403);
    }

    /**
     * Send not found response
     */
    public static function notFound(string $message = 'Not found'): void
    {
        self::error($message, 404);
    }

    /**
     * Send created response
     */
    public static function created(mixed $data = null, string $message = 'Created successfully'): void
    {
        self::success($data, $message, 201);
    }

    /**
     * Send no content response
     */
    public static function noContent(): void
    {
        http_response_code(204);
        exit;
    }

    /**
     * Redirect to URL
     */
    public static function redirect(string $url, int $statusCode = 302): void
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }

    /**
     * Send file download
     */
    public static function download(string $filePath, ?string $filename = null): void
    {
        if (!file_exists($filePath)) {
            self::notFound('File not found');
        }

        $filename = $filename ?? basename($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');
        
        readfile($filePath);
        exit;
    }
}
