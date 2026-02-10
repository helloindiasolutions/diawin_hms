<?php
/**
 * Permission Middleware
 * Checks user permissions before allowing access to resources
 */

namespace Middleware;

use System\Response;
use System\Logger;
use Api\Services\PermissionService;

class PermissionMiddleware
{
    private PermissionService $permissionService;
    
    public function __construct()
    {
        $this->permissionService = new PermissionService();
    }
    
    /**
     * Check if user has permission
     * 
     * @param string $resource Resource name
     * @param string $action Action name
     * @return bool True if has permission
     */
    public function check(string $resource, string $action): bool
    {
        // Get authenticated user from JWT
        $jwtUser = $GLOBALS['jwt_user'] ?? null;
        
        if (!$jwtUser) {
            Logger::warning('Permission check without authenticated user', [
                'resource' => $resource,
                'action' => $action
            ]);
            Response::unauthorized('Authentication required');
            return false;
        }
        
        $userId = $jwtUser['user_id'];
        $branchId = $jwtUser['branch_id'] ?? null;
        
        // Check permission
        $hasPermission = $this->permissionService->checkPermission(
            $userId,
            $resource,
            $action,
            $branchId
        );
        
        if (!$hasPermission) {
            Logger::warning('Permission denied', [
                'user_id' => $userId,
                'resource' => $resource,
                'action' => $action,
                'branch_id' => $branchId
            ]);
            
            Response::forbidden('You do not have permission to perform this action');
            return false;
        }
        
        return true;
    }
    
    /**
     * Require specific permission (throws response if denied)
     * 
     * @param string $resource Resource name
     * @param string $action Action name
     * @return void
     */
    public function require(string $resource, string $action): void
    {
        if (!$this->check($resource, $action)) {
            exit; // Response already sent
        }
    }
    
    /**
     * Check if user has any of the specified roles
     * 
     * @param array $roles Array of role names
     * @return bool True if user has any of the roles
     */
    public function hasAnyRole(array $roles): bool
    {
        $jwtUser = $GLOBALS['jwt_user'] ?? null;
        
        if (!$jwtUser) {
            Response::unauthorized('Authentication required');
            return false;
        }
        
        $userId = $jwtUser['user_id'];
        $branchId = $jwtUser['branch_id'] ?? null;
        
        foreach ($roles as $roleName) {
            if ($this->permissionService->hasRole($userId, $roleName, $branchId)) {
                return true;
            }
        }
        
        Logger::warning('Role check failed', [
            'user_id' => $userId,
            'required_roles' => $roles
        ]);
        
        Response::forbidden('Insufficient privileges');
        return false;
    }
    
    /**
     * Require user to have any of the specified roles
     * 
     * @param array $roles Array of role names
     * @return void
     */
    public function requireAnyRole(array $roles): void
    {
        if (!$this->hasAnyRole($roles)) {
            exit; // Response already sent
        }
    }
    
    /**
     * Check if user has access to specific branch
     * 
     * @param int $branchId Branch ID
     * @return bool True if has access
     */
    public function hasBranchAccess(int $branchId): bool
    {
        $jwtUser = $GLOBALS['jwt_user'] ?? null;
        
        if (!$jwtUser) {
            Response::unauthorized('Authentication required');
            return false;
        }
        
        $userId = $jwtUser['user_id'];
        
        // Super Admin has access to all branches
        if ($this->permissionService->hasRole($userId, 'Super Admin')) {
            return true;
        }
        
        // Check if user has role in this branch
        $branches = $this->permissionService->getUserBranches($userId);
        $branchIds = array_column($branches, 'branch_id');
        
        if (!in_array($branchId, $branchIds)) {
            Logger::warning('Branch access denied', [
                'user_id' => $userId,
                'requested_branch_id' => $branchId,
                'user_branches' => $branchIds
            ]);
            
            Response::forbidden('You do not have access to this branch');
            return false;
        }
        
        return true;
    }
    
    /**
     * Require branch access
     * 
     * @param int $branchId Branch ID
     * @return void
     */
    public function requireBranchAccess(int $branchId): void
    {
        if (!$this->hasBranchAccess($branchId)) {
            exit; // Response already sent
        }
    }
}
