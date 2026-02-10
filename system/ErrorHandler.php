<?php
/**
 * Error Handler
 */

declare(strict_types=1);

namespace System;

class ErrorHandler
{
    public static function register(): void
    {
        $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
        
        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        ini_set('log_errors', '1');
        ini_set('error_log', STORAGE_PATH . '/logs/php_errors.log');

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (!(error_reporting() & $errno)) return false;

        Logger::error("PHP Error", [
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ]);

        return true;
    }

    public static function handleException(\Throwable $e): void
    {
        Logger::error('Uncaught Exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        http_response_code(500);
        $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

        if (self::isApiRequest()) {
            $response = ['success' => false, 'message' => 'Internal Server Error'];
            if ($debug) {
                $response['debug'] = [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ];
            }
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            if ($debug) {
                echo '<h1>Error</h1>';
                echo '<p>' . Security::escape($e->getMessage()) . '</p>';
                echo '<p>File: ' . Security::escape($e->getFile()) . ':' . $e->getLine() . '</p>';
                echo '<pre>' . Security::escape($e->getTraceAsString()) . '</pre>';
            } else {
                echo '<h1>500 - Server Error</h1><p>Something went wrong.</p>';
            }
        }
        exit(1);
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            Logger::error('Fatal Error', $error);
        }
    }

    private static function isApiRequest(): bool
    {
        return strpos($_SERVER['REQUEST_URI'] ?? '', '/api') !== false
            || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
}
