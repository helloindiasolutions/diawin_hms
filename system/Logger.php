<?php
/**
 * Logger System
 */

declare(strict_types=1);

namespace System;

class Logger
{
    private static ?string $logPath = null;

    private static function init(): void
    {
        if (self::$logPath === null) {
            self::$logPath = STORAGE_PATH . '/logs';
            if (!is_dir(self::$logPath)) {
                mkdir(self::$logPath, 0755, true);
            }
        }
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        self::init();
        
        $timestamp = date('Y-m-d H:i:s');
        $levelUpper = strtoupper($level);
        $logEntry = "[{$timestamp}] [{$levelUpper}] {$message}";
        
        if (!empty($context)) {
            $logEntry .= ' ' . json_encode($context, JSON_UNESCAPED_SLASHES);
        }
        
        $logEntry .= PHP_EOL;
        
        $logFile = self::$logPath . '/app-' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    public static function emergency(string $message, array $context = []): void { self::log('emergency', $message, $context); }
    public static function alert(string $message, array $context = []): void { self::log('alert', $message, $context); }
    public static function critical(string $message, array $context = []): void { self::log('critical', $message, $context); }
    public static function error(string $message, array $context = []): void { self::log('error', $message, $context); }
    public static function warning(string $message, array $context = []): void { self::log('warning', $message, $context); }
    public static function notice(string $message, array $context = []): void { self::log('notice', $message, $context); }
    public static function info(string $message, array $context = []): void { self::log('info', $message, $context); }
    public static function debug(string $message, array $context = []): void { self::log('debug', $message, $context); }

    public static function security(string $message, array $context = []): void
    {
        self::init();
        $context['ip'] = Security::getClientIp();
        $context['uri'] = $_SERVER['REQUEST_URI'] ?? '';
        
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [SECURITY] {$message} " . json_encode($context) . PHP_EOL;
        
        file_put_contents(self::$logPath . '/security.log', $logEntry, FILE_APPEND | LOCK_EX);
    }

    public static function api(string $method, string $endpoint, int $statusCode, float $duration): void
    {
        self::init();
        
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = sprintf("[%s] %s %s %d %.3fms\n", $timestamp, $method, $endpoint, $statusCode, $duration * 1000);
        
        file_put_contents(self::$logPath . '/api.log', $logEntry, FILE_APPEND | LOCK_EX);
    }
}
