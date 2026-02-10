<?php

declare(strict_types=1);

namespace App\Middleware;

use System\Database;
use System\Session;
use System\Response;
use System\Router;

/**
 * Branch Middleware
 * Ensures users can only access data from their assigned branch
 */
class BranchMiddleware
{
    /**
     * Get user's roles from session
     */
    private static function getUserRoles(): array
    {
        return $_SESSION['user_data']['roles'] ?? [];
    }

    /**
     * Get user's primary role from session
     */
    private static function getUserRole(): ?string
    {
        return $_SESSION['user_data']['role'] ?? null;
    }

    /**
     * Get user's assigned branch ID from session
     */
    private static function getSessionBranchId(): ?int
    {
        $branchId = $_SESSION['user_data']['branch_id'] ?? null;
        return $branchId !== null ? (int) $branchId : null;
    }

    /**
     * Check if user has access to specific branch
     */
    public static function checkBranchAccess(array $params = []): void
    {
        if (!Session::isLoggedIn()) {
            Response::error('Unauthorized', 401);
            exit;
        }

        $userId = Session::get('user_id');
        $userBranchId = self::getSessionBranchId();
        $userRoles = self::getUserRoles();

        // Super Admin can access all branches
        if (self::isSuperAdminByRoles($userRoles)) {
            return;
        }

        // Get requested branch ID from params or query
        $requestedBranchId = $params['branch_id'] ?? $_GET['branch_id'] ?? $_POST['branch_id'] ?? null;

        // If no specific branch requested, ensure user has a branch assigned
        if ($requestedBranchId === null) {
            if ($userBranchId === null) {
                Response::error('No branch assigned to user', 403);
                exit;
            }
            return;
        }

        // Check if user is trying to access a different branch
        if ((int) $requestedBranchId !== (int) $userBranchId) {
            Response::error('Access denied: You can only access your assigned branch', 403);
            exit;
        }
    }

    /**
     * Get user's branch ID or null for super admin viewing all branches
     * 
     * For Super Admin:
     *   - If they selected a specific branch, return that branch_id
     *   - If they selected "all" (branch_id = 0 or null in session), return null (all branches)
     * 
     * For all other roles (Admin, Doctor, Nurse, Pharmacist, Receptionist):
     *   - Always return their assigned branch_id
     */
    public static function getUserBranchId(): ?int
    {
        $userRoles = self::getUserRoles();

        // Super Admin can switch between branches or see all
        if (self::isSuperAdminByRoles($userRoles)) {
            // Check if super admin has selected a specific branch in session
            $selectedBranchId = $_SESSION['user_data']['branch_id'] ?? null;

            // If 0 or null, they want to see ALL branches
            if (empty($selectedBranchId) || $selectedBranchId === 0 || $selectedBranchId === '0') {
                return null;
            }

            // They selected a specific branch
            return (int) $selectedBranchId;
        }

        // All other roles (including Admin) are restricted to their assigned branch
        return self::getSessionBranchId();
    }

    /**
     * Check if user is super admin by roles array
     */
    public static function isSuperAdminByRoles(array $roles): bool
    {
        $superAdminRoles = ['SUPER_ADMIN', 'super_admin', 'Super Admin', 'System Administrator'];
        return !empty(array_intersect($roles, $superAdminRoles));
    }

    /**
     * Check if user is super admin (legacy - checks single role string)
     * Database: code=SUPER_ADMIN, name=super_admin
     */
    public static function isSuperAdmin($role): bool
    {
        // If passed as array, use the optimized version
        if (is_array($role)) {
            return self::isSuperAdminByRoles($role);
        }
        return in_array($role, ['SUPER_ADMIN', 'super_admin', 'Super Admin', 'System Administrator']);
    }

    /**
     * Check if user is branch admin
     */
    public static function isBranchAdmin($role): bool
    {
        if (is_array($role)) {
            return !empty(array_intersect($role, ['Branch Admin', 'branch_admin', 'BRANCH_ADMIN']));
        }
        return in_array($role, ['Branch Admin', 'branch_admin', 'BRANCH_ADMIN']);
    }

    /**
     * Filter query with branch restriction
     */
    public static function addBranchFilter(string $sql, string $tableAlias = ''): string
    {
        $branchId = self::getUserBranchId();

        // Super admin sees all
        if ($branchId === null) {
            return $sql;
        }

        $prefix = $tableAlias ? $tableAlias . '.' : '';

        // Add branch filter to WHERE clause
        if (stripos($sql, 'WHERE') !== false) {
            $sql .= " AND {$prefix}branch_id = {$branchId}";
        } else {
            $sql .= " WHERE {$prefix}branch_id = {$branchId}";
        }

        return $sql;
    }
}

