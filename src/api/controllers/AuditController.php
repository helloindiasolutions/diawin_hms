<?php
/**
 * Audit Controller
 * Handles audit log viewing and reporting
 */

namespace App\Api\Controllers;

use System\Response;
use Api\Services\AuditService;
use Middleware\PermissionMiddleware;

class AuditController
{
    private AuditService $auditService;
    private PermissionMiddleware $permission;
    
    public function __construct()
    {
        $this->auditService = new AuditService();
        $this->permission = new PermissionMiddleware();
    }
    
    /**
     * Get audit logs
     * GET /api/audit/logs
     */
    public function getLogs(): void
    {
        $this->permission->require('audit', 'view');
        
        $filters = [
            'entity_type' => $_GET['entity_type'] ?? null,
            'entity_id' => $_GET['entity_id'] ?? null,
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
        ];
        
        $limit = (int)($_GET['limit'] ?? 50);
        $offset = (int)($_GET['offset'] ?? 0);
        
        try {
            $logs = $this->auditService->getAuditLogs($filters, $limit, $offset);
            
            Response::success([
                'logs' => $logs,
                'count' => count($logs),
                'limit' => $limit,
                'offset' => $offset
            ]);
            
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * Get entity history
     * GET /api/audit/entity/:type/:id
     */
    public function getEntityHistory(string $type, int $id): void
    {
        $this->permission->require('audit', 'view');
        
        try {
            $history = $this->auditService->getEntityHistory($type, $id);
            
            Response::success([
                'entity_type' => $type,
                'entity_id' => $id,
                'history' => $history,
                'count' => count($history)
            ]);
            
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * Get user activity
     * GET /api/audit/user/:userId
     */
    public function getUserActivity(int $userId): void
    {
        $this->permission->require('audit', 'view');
        
        $limit = (int)($_GET['limit'] ?? 100);
        
        try {
            $activity = $this->auditService->getUserActivity($userId, $limit);
            
            Response::success([
                'user_id' => $userId,
                'activity' => $activity,
                'count' => count($activity)
            ]);
            
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * Get audit statistics
     * GET /api/audit/stats
     */
    public function getStats(): void
    {
        $this->permission->require('audit', 'view');
        
        $filters = [
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
        ];
        
        try {
            $stats = $this->auditService->getAuditStats($filters);
            
            Response::success($stats);
            
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
