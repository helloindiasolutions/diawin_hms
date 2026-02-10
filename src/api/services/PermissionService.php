<?php
/**
 * Permission Service
 * Handles role-based access control and permissions
 */

namespace Api\Services;

use System\Database;
use System\Logger;

class PermissionService
{
    private Database $db;
    private static ?array $permissionCache = null;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Check if user has permission for resource and action
     * 
     * @param int $userId User ID
     * @param string $resource Resource name (e.g., 'patients', 'invoices')
     * @param string $action Action name (e.g., 'create', 'read', 'update', 'delete')
     * @param int|null $branchId Optional branch ID for branch-level check
     * @return bool True if user has permission
     */
    public function checkPermission(int $userId, string $resource, string $action, ?int $branchId = null): bool
    {
        try {
            // Get user roles
            $roles = $this->getUserRoles($userId, $branchId);
            
            if (empty($roles)) {
                return false;
            }
            
            // Super Admin has all permissions
            if (in_array('Super Admin', array_column($roles, 'role_name'))) {
                return true;
            }
            
            // Load permissions configuration
            $permissions = $this->loadPermissions();
            
            // Check if resource exists in permissions
            if (!isset($permissions[$resource])) {
                Logger::warning('Unknown resource in permission check', [
                    'resource' => $resource,
                    'action' => $action,
                    'user_id' => $userId
                ]);
                return false;
            }
            
            // Check if action is valid for resource
            if (!in_array($action, $permissions[$resource])) {
                Logger::warning('Unknown action for resource', [
                    'resource' => $resource,
                    'action' => $action,
                    'user_id' => $userId
                ]);
                return false;
            }
            
            // Check role permissions
            foreach ($roles as $role) {
                $rolePermissions = $this->getRolePermissions($role['role_id']);
                $permissionKey = "{$resource}:{$action}";
                
                if (in_array($permissionKey, $rolePermissions)) {
                    return true;
                }
                
                // Check wildcard permissions
                if (in_array("{$resource}:*", $rolePermissions) || in_array("*:*", $rolePermissions)) {
                    return true;
                }
            }
            
            return false;
            
        } catch (\Exception $e) {
            Logger::error('Permission check failed', [
                'user_id' => $userId,
                'resource' => $resource,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get user roles
     * 
     * @param int $userId User ID
     * @param int|null $branchId Optional branch ID filter
     * @return array Array of roles
     */
    public function getUserRoles(int $userId, ?int $branchId = null): array
    {
        $sql = "SELECT r.role_id, r.name as role_name, r.description, ur.branch_id 
                FROM user_roles ur 
                JOIN roles r ON ur.role_id = r.role_id 
                WHERE ur.user_id = ?";
        
        $params = [$userId];
        
        if ($branchId !== null) {
            $sql .= " AND (ur.branch_id = ? OR ur.branch_id IS NULL)";
            $params[] = $branchId;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get user branches
     * 
     * @param int $userId User ID
     * @return array Array of branch IDs
     */
    public function getUserBranches(int $userId): array
    {
        $branches = $this->db->fetchAll(
            "SELECT DISTINCT ur.branch_id, b.name as branch_name, b.code as branch_code
             FROM user_roles ur 
             LEFT JOIN branches b ON ur.branch_id = b.branch_id
             WHERE ur.user_id = ? AND ur.branch_id IS NOT NULL",
            [$userId]
        );
        
        return $branches;
    }
    
    /**
     * Check if user has specific role
     * 
     * @param int $userId User ID
     * @param string $roleName Role name
     * @param int|null $branchId Optional branch ID
     * @return bool True if user has role
     */
    public function hasRole(int $userId, string $roleName, ?int $branchId = null): bool
    {
        $roles = $this->getUserRoles($userId, $branchId);
        return in_array($roleName, array_column($roles, 'role_name'));
    }
    
    /**
     * Get all permissions for user
     * 
     * @param int $userId User ID
     * @param int|null $branchId Optional branch ID
     * @return array Array of permissions
     */
    public function getUserPermissions(int $userId, ?int $branchId = null): array
    {
        $roles = $this->getUserRoles($userId, $branchId);
        
        if (empty($roles)) {
            return [];
        }
        
        $allPermissions = [];
        
        foreach ($roles as $role) {
            $rolePermissions = $this->getRolePermissions($role['role_id']);
            $allPermissions = array_merge($allPermissions, $rolePermissions);
        }
        
        return array_unique($allPermissions);
    }
    
    /**
     * Get permissions for a role
     * 
     * @param int $roleId Role ID
     * @return array Array of permission strings (resource:action)
     */
    private function getRolePermissions(int $roleId): array
    {
        // Get role details
        $role = $this->db->fetch("SELECT name FROM roles WHERE role_id = ?", [$roleId]);
        
        if (!$role) {
            return [];
        }
        
        // Map roles to permissions (hardcoded for now, can be moved to database)
        $rolePermissionsMap = $this->getRolePermissionsMap();
        
        return $rolePermissionsMap[$role['name']] ?? [];
    }
    
    /**
     * Load permissions configuration
     * 
     * @return array Permissions array
     */
    private function loadPermissions(): array
    {
        if (self::$permissionCache !== null) {
            return self::$permissionCache;
        }
        
        // Define all available permissions
        self::$permissionCache = [
            'patients' => ['create', 'read', 'update', 'delete', 'export'],
            'appointments' => ['create', 'read', 'update', 'delete', 'cancel'],
            'visits' => ['create', 'read', 'update', 'close'],
            'prescriptions' => ['create', 'read', 'update', 'delete'],
            'invoices' => ['create', 'read', 'update', 'cancel', 'approve'],
            'estimates' => ['create', 'read', 'update', 'convert', 'approve'],
            'payments' => ['create', 'read', 'refund'],
            'inventory' => ['create', 'read', 'update', 'delete', 'adjust'],
            'pharmacy' => ['dispense', 'read', 'return'],
            'therapy' => ['create', 'read', 'update', 'complete', 'cancel'],
            'users' => ['create', 'read', 'update', 'delete', 'activate', 'deactivate'],
            'roles' => ['create', 'read', 'update', 'delete'],
            'branches' => ['create', 'read', 'update', 'delete'],
            'reports' => ['view', 'export'],
            'dashboard' => ['view'],
            'audit' => ['view'],
            'settings' => ['read', 'update'],
            'dcr' => ['create', 'read', 'reconcile'],
            'queue' => ['manage', 'view'],
        ];
        
        return self::$permissionCache;
    }
    
    /**
     * Get role to permissions mapping
     * 
     * @return array Role permissions map
     */
    private function getRolePermissionsMap(): array
    {
        return [
            'Super Admin' => ['*:*'], // All permissions
            
            'Clinic Admin' => [
                'patients:*', 'appointments:*', 'visits:*', 'prescriptions:*',
                'invoices:*', 'estimates:*', 'payments:*',
                'inventory:*', 'pharmacy:*', 'therapy:*',
                'users:read', 'users:update',
                'reports:*', 'dashboard:*', 'audit:*',
                'dcr:*', 'queue:*', 'settings:*'
            ],
            
            'Doctor' => [
                'patients:read', 'patients:update',
                'appointments:read', 'appointments:update',
                'visits:*', 'prescriptions:*',
                'estimates:create', 'estimates:read',
                'therapy:create', 'therapy:read', 'therapy:update',
                'dashboard:view', 'queue:view'
            ],
            
            'Nurse' => [
                'patients:read',
                'appointments:read',
                'visits:read', 'visits:update',
                'prescriptions:read',
                'therapy:read', 'therapy:update', 'therapy:complete',
                'queue:view'
            ],
            
            'Pharmacist' => [
                'patients:read',
                'prescriptions:read',
                'invoices:create', 'invoices:read',
                'payments:create', 'payments:read',
                'inventory:*', 'pharmacy:*',
                'dashboard:view'
            ],
            
            'Receptionist' => [
                'patients:*',
                'appointments:*',
                'visits:create', 'visits:read',
                'invoices:read',
                'payments:create', 'payments:read',
                'queue:*',
                'dashboard:view'
            ],
            
            'Accountant' => [
                'patients:read',
                'invoices:*', 'estimates:*',
                'payments:*',
                'reports:*', 'dashboard:*',
                'dcr:*', 'audit:view'
            ],
            
            'Therapist' => [
                'patients:read',
                'therapy:*',
                'queue:view',
                'dashboard:view'
            ]
        ];
    }
    
    /**
     * Assign role to user
     * 
     * @param int $userId User ID
     * @param int $roleId Role ID
     * @param int|null $branchId Branch ID
     * @return bool Success status
     */
    public function assignRole(int $userId, int $roleId, ?int $branchId = null): bool
    {
        try {
            // Check if already assigned
            $existing = $this->db->fetch(
                "SELECT user_role_id FROM user_roles 
                 WHERE user_id = ? AND role_id = ? AND (branch_id = ? OR (branch_id IS NULL AND ? IS NULL))",
                [$userId, $roleId, $branchId, $branchId]
            );
            
            if ($existing) {
                return true; // Already assigned
            }
            
            $this->db->insert('user_roles', [
                'user_id' => $userId,
                'role_id' => $roleId,
                'branch_id' => $branchId
            ]);
            
            Logger::info('Role assigned to user', [
                'user_id' => $userId,
                'role_id' => $roleId,
                'branch_id' => $branchId
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Logger::error('Failed to assign role', [
                'user_id' => $userId,
                'role_id' => $roleId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Remove role from user
     * 
     * @param int $userId User ID
     * @param int $roleId Role ID
     * @param int|null $branchId Branch ID
     * @return bool Success status
     */
    public function removeRole(int $userId, int $roleId, ?int $branchId = null): bool
    {
        try {
            $where = "user_id = ? AND role_id = ?";
            $params = [$userId, $roleId];
            
            if ($branchId !== null) {
                $where .= " AND branch_id = ?";
                $params[] = $branchId;
            } else {
                $where .= " AND branch_id IS NULL";
            }
            
            $this->db->delete('user_roles', $where, $params);
            
            Logger::info('Role removed from user', [
                'user_id' => $userId,
                'role_id' => $roleId,
                'branch_id' => $branchId
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Logger::error('Failed to remove role', [
                'user_id' => $userId,
                'role_id' => $roleId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
