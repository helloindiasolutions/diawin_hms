<?php
/**
 * Audit Service
 * Handles comprehensive audit logging for compliance and monitoring
 */

namespace Api\Services;

use System\Database;
use System\Logger;

class AuditService
{
    private Database $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Log an action to audit trail
     * 
     * @param int $userId User ID who performed the action
     * @param string $action Action performed (create, update, delete, cancel, refund, etc.)
     * @param string $entityType Entity type (invoice, payment, user, etc.)
     * @param int|null $entityId Entity ID
     * @param array|null $changes Changes made (before/after state)
     * @param string|null $reason Reason for action
     * @return int Audit log ID
     */
    public function logAction(
        int $userId,
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $changes = null,
        ?string $reason = null
    ): int {
        try {
            $auditId = $this->db->insert('audit_logs', [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'action' => $action,
                'performed_by' => $userId,
                'performed_at' => date('Y-m-d H:i:s'),
                'changes' => $changes ? json_encode($changes) : null
            ]);
            
            Logger::info('Audit log created', [
                'audit_id' => $auditId,
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId
            ]);
            
            return $auditId;
            
        } catch (\Exception $e) {
            Logger::error('Failed to create audit log', [
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $entityType,
                'error' => $e->getMessage()
            ]);
            // Don't throw - audit logging should not break main flow
            return 0;
        }
    }
    
    /**
     * Log login attempt
     * 
     * @param int|null $userId User ID (null if failed)
     * @param string $username Username attempted
     * @param string $ipAddress IP address
     * @param bool $success Success status
     * @return int Audit log ID
     */
    public function logLogin(?int $userId, string $username, string $ipAddress, bool $success): int
    {
        return $this->logAction(
            $userId ?? 0,
            $success ? 'login_success' : 'login_failed',
            'authentication',
            $userId,
            [
                'username' => $username,
                'ip_address' => $ipAddress,
                'success' => $success,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        );
    }
    
    /**
     * Log permission denied
     * 
     * @param int $userId User ID
     * @param string $resource Resource attempted
     * @param string $action Action attempted
     * @return int Audit log ID
     */
    public function logPermissionDenied(int $userId, string $resource, string $action): int
    {
        return $this->logAction(
            $userId,
            'permission_denied',
            'authorization',
            null,
            [
                'resource' => $resource,
                'action' => $action,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        );
    }
    
    /**
     * Log invoice creation
     * 
     * @param int $userId User ID
     * @param int $invoiceId Invoice ID
     * @param array $invoiceData Invoice data
     * @return int Audit log ID
     */
    public function logInvoiceCreated(int $userId, int $invoiceId, array $invoiceData): int
    {
        return $this->logAction(
            $userId,
            'create',
            'invoice',
            $invoiceId,
            [
                'invoice_no' => $invoiceData['invoice_no'] ?? '',
                'patient_id' => $invoiceData['patient_id'] ?? null,
                'invoice_type' => $invoiceData['invoice_type'] ?? '',
                'total' => $invoiceData['total'] ?? 0,
                'status' => $invoiceData['status'] ?? ''
            ]
        );
    }
    
    /**
     * Log invoice cancellation
     * 
     * @param int $userId User ID
     * @param int $invoiceId Invoice ID
     * @param string $reason Cancellation reason
     * @param array $beforeState State before cancellation
     * @return int Audit log ID
     */
    public function logInvoiceCancelled(int $userId, int $invoiceId, string $reason, array $beforeState): int
    {
        return $this->logAction(
            $userId,
            'cancel',
            'invoice',
            $invoiceId,
            [
                'before' => $beforeState,
                'reason' => $reason,
                'cancelled_at' => date('Y-m-d H:i:s')
            ],
            $reason
        );
    }
    
    /**
     * Log payment transaction
     * 
     * @param int $userId User ID
     * @param int $paymentId Payment ID
     * @param array $paymentData Payment data
     * @return int Audit log ID
     */
    public function logPayment(int $userId, int $paymentId, array $paymentData): int
    {
        return $this->logAction(
            $userId,
            'create',
            'payment',
            $paymentId,
            [
                'invoice_id' => $paymentData['invoice_id'] ?? null,
                'amount' => $paymentData['amount'] ?? 0,
                'payment_mode' => $paymentData['payment_mode'] ?? '',
                'reference' => $paymentData['reference'] ?? ''
            ]
        );
    }
    
    /**
     * Log refund transaction
     * 
     * @param int $userId User ID
     * @param int $invoiceId Invoice ID
     * @param float $amount Refund amount
     * @param string $reason Refund reason
     * @return int Audit log ID
     */
    public function logRefund(int $userId, int $invoiceId, float $amount, string $reason): int
    {
        return $this->logAction(
            $userId,
            'refund',
            'invoice',
            $invoiceId,
            [
                'refund_amount' => $amount,
                'reason' => $reason,
                'refunded_at' => date('Y-m-d H:i:s')
            ],
            $reason
        );
    }
    
    /**
     * Log user creation/update
     * 
     * @param int $performedBy User ID who performed action
     * @param string $action Action (create/update/delete)
     * @param int $targetUserId Target user ID
     * @param array $changes Changes made
     * @return int Audit log ID
     */
    public function logUserAction(int $performedBy, string $action, int $targetUserId, array $changes): int
    {
        return $this->logAction(
            $performedBy,
            $action,
            'user',
            $targetUserId,
            $changes
        );
    }
    
    /**
     * Log inventory adjustment
     * 
     * @param int $userId User ID
     * @param int $batchId Batch ID
     * @param int $quantityChange Quantity change (positive or negative)
     * @param string $reason Reason for adjustment
     * @return int Audit log ID
     */
    public function logInventoryAdjustment(int $userId, int $batchId, int $quantityChange, string $reason): int
    {
        return $this->logAction(
            $userId,
            'adjust',
            'inventory_batch',
            $batchId,
            [
                'quantity_change' => $quantityChange,
                'reason' => $reason,
                'adjusted_at' => date('Y-m-d H:i:s')
            ],
            $reason
        );
    }
    
    /**
     * Log prescription dispensing
     * 
     * @param int $userId User ID
     * @param int $prescriptionId Prescription ID
     * @param array $items Dispensed items
     * @return int Audit log ID
     */
    public function logPrescriptionDispensed(int $userId, int $prescriptionId, array $items): int
    {
        return $this->logAction(
            $userId,
            'dispense',
            'prescription',
            $prescriptionId,
            [
                'items_count' => count($items),
                'items' => $items,
                'dispensed_at' => date('Y-m-d H:i:s')
            ]
        );
    }
    
    /**
     * Get audit logs with filters
     * 
     * @param array $filters Filters (entity_type, entity_id, user_id, action, date_from, date_to)
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Audit logs
     */
    public function getAuditLogs(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['entity_type'])) {
            $where .= " AND al.entity_type = ?";
            $params[] = $filters['entity_type'];
        }
        
        if (!empty($filters['entity_id'])) {
            $where .= " AND al.entity_id = ?";
            $params[] = $filters['entity_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $where .= " AND al.performed_by = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $where .= " AND al.action = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['date_from'])) {
            $where .= " AND DATE(al.performed_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where .= " AND DATE(al.performed_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $logs = $this->db->fetchAll(
            "SELECT al.*, u.full_name as performed_by_name, u.username
             FROM audit_logs al
             LEFT JOIN users u ON al.performed_by = u.user_id
             WHERE {$where}
             ORDER BY al.performed_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        );
        
        // Decode JSON changes
        foreach ($logs as &$log) {
            if ($log['changes']) {
                $log['changes'] = json_decode($log['changes'], true);
            }
        }
        
        return $logs;
    }
    
    /**
     * Get entity history
     * 
     * @param string $entityType Entity type
     * @param int $entityId Entity ID
     * @return array History logs
     */
    public function getEntityHistory(string $entityType, int $entityId): array
    {
        $logs = $this->db->fetchAll(
            "SELECT al.*, u.full_name as performed_by_name, u.username
             FROM audit_logs al
             LEFT JOIN users u ON al.performed_by = u.user_id
             WHERE al.entity_type = ? AND al.entity_id = ?
             ORDER BY al.performed_at DESC",
            [$entityType, $entityId]
        );
        
        // Decode JSON changes
        foreach ($logs as &$log) {
            if ($log['changes']) {
                $log['changes'] = json_decode($log['changes'], true);
            }
        }
        
        return $logs;
    }
    
    /**
     * Get user activity
     * 
     * @param int $userId User ID
     * @param int $limit Limit
     * @return array Activity logs
     */
    public function getUserActivity(int $userId, int $limit = 100): array
    {
        $logs = $this->db->fetchAll(
            "SELECT al.*
             FROM audit_logs al
             WHERE al.performed_by = ?
             ORDER BY al.performed_at DESC
             LIMIT ?",
            [$userId, $limit]
        );
        
        // Decode JSON changes
        foreach ($logs as &$log) {
            if ($log['changes']) {
                $log['changes'] = json_decode($log['changes'], true);
            }
        }
        
        return $logs;
    }
    
    /**
     * Get audit statistics
     * 
     * @param array $filters Filters
     * @return array Statistics
     */
    public function getAuditStats(array $filters = []): array
    {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $where .= " AND DATE(performed_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where .= " AND DATE(performed_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $stats = $this->db->fetch(
            "SELECT 
                COUNT(*) as total_actions,
                COUNT(DISTINCT performed_by) as unique_users,
                COUNT(DISTINCT entity_type) as entity_types,
                COUNT(CASE WHEN action LIKE '%failed%' OR action LIKE '%denied%' THEN 1 END) as failed_actions
             FROM audit_logs
             WHERE {$where}",
            $params
        );
        
        // Get top actions
        $topActions = $this->db->fetchAll(
            "SELECT action, COUNT(*) as count
             FROM audit_logs
             WHERE {$where}
             GROUP BY action
             ORDER BY count DESC
             LIMIT 10",
            $params
        );
        
        return [
            'summary' => $stats,
            'top_actions' => $topActions
        ];
    }
}
