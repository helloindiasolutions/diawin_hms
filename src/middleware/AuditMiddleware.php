<?php
/**
 * Audit Middleware
 * Automatically logs sensitive operations
 */

namespace Middleware;

use System\Logger;
use Api\Services\AuditService;

class AuditMiddleware
{
    private AuditService $auditService;
    private static ?array $beforeState = null;
    
    public function __construct()
    {
        $this->auditService = new AuditService();
    }
    
    /**
     * Capture state before operation
     * 
     * @param string $entityType Entity type
     * @param int $entityId Entity ID
     * @return void
     */
    public function captureBeforeState(string $entityType, int $entityId): void
    {
        // Store before state for comparison
        self::$beforeState = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'captured_at' => microtime(true)
        ];
    }
    
    /**
     * Log operation with before/after comparison
     * 
     * @param int $userId User ID
     * @param string $action Action
     * @param string $entityType Entity type
     * @param int $entityId Entity ID
     * @param array|null $afterState After state
     * @param string|null $reason Reason
     * @return void
     */
    public function logOperation(
        int $userId,
        string $action,
        string $entityType,
        int $entityId,
        ?array $afterState = null,
        ?string $reason = null
    ): void {
        $changes = null;
        
        if (self::$beforeState && $afterState) {
            $changes = [
                'before' => self::$beforeState,
                'after' => $afterState
            ];
        } elseif ($afterState) {
            $changes = $afterState;
        }
        
        $this->auditService->logAction(
            $userId,
            $action,
            $entityType,
            $entityId,
            $changes,
            $reason
        );
        
        // Clear before state
        self::$beforeState = null;
    }
    
    /**
     * Get client IP address
     * 
     * @return string IP address
     */
    public static function getClientIP(): string
    {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                $ip = $_SERVER[$key];
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Get user agent
     * 
     * @return string User agent
     */
    public static function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }
}
