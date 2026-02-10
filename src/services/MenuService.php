<?php
/**
 * Menu Service
 * 
 * Handles menu structure retrieval and permission-based filtering for the HMS application.
 */

declare(strict_types=1);

namespace App\Services;

use System\Database;
use System\Logger;
use App\Config\MenuConfig;

class MenuService
{
    private Database $db;
    private int $userId;
    private int $branchId;
    private string $userType;
    private array $userPermissions = [];

    /**
     * Initialize MenuService
     */
    public function __construct(Database $db, int $userId, int $branchId, string $userType = 'user')
    {
        $this->db = $db;
        $this->userId = $userId;
        $this->branchId = $branchId;
        $this->userType = $userType;
        $this->loadUserPermissions();
    }

    /**
     * Load user permissions from database
     */
    private function loadUserPermissions(): void
    {
        try {
            // Handle Patient Type
            if ($this->userType === 'patient') {
                $this->userPermissions = ['Patient', 'patient'];
                return;
            }

            // Fetch roles assigned to this user
            $sql = "SELECT DISTINCT r.name as permission_key 
                    FROM user_roles ur 
                    JOIN roles r ON ur.role_id = r.role_id 
                    WHERE ur.user_id = ?";

            $results = $this->db->fetchAll($sql, [$this->userId]);
            $this->userPermissions = array_column($results, 'permission_key');

            // If user has NO permissions, but is super_admin, we give all
            // (Handled automatically in hasPermission check for 'super_admin')

        } catch (\Exception $e) {
            Logger::error('MenuService: Failed to load user permissions: ' . $e->getMessage());
            $this->userPermissions = [];
        }
    }

    /**
     * Check if user has a specific permission
     */
    private function hasPermission($permission): bool
    {
        // Public items always allowed
        if ($permission === null) {
            return true;
        }

        // Super Admin has full access to everything (code: SUPER_ADMIN, name: super_admin)
        $superAdminRoles = ['SUPER_ADMIN', 'super_admin', 'Super Admin', 'System Administrator'];
        foreach ($superAdminRoles as $superAdminRole) {
            if (in_array($superAdminRole, $this->userPermissions)) {
                return true;
            }
        }

        // System Admin (code: SYSTEM_ADMIN, name: admin) also has full menu access
        $adminRoles = ['SYSTEM_ADMIN', 'admin', 'Admin', 'Administrator', 'Branch Admin', 'branch_admin'];
        foreach ($adminRoles as $adminRole) {
            if (in_array($adminRole, $this->userPermissions)) {
                return true;
            }
        }

        // Handle array of permissions/roles
        if (is_array($permission)) {
            foreach ($permission as $p) {
                if (in_array($p, $this->userPermissions, true)) {
                    return true;
                }
            }
            return false;
        }

        return in_array($permission, $this->userPermissions, true);
    }

    /**
     * Filter menu items recursively
     */
    private function filterMenuItems(array $menuItems): array
    {
        $result = [];

        foreach ($menuItems as $item) {
            // Process children
            if (!empty($item['children'])) {
                $item['children'] = $this->filterMenuItems($item['children']);
            }

            // Check permission
            $hasPermission = $this->hasPermission($item['required_permission'] ?? null);

            if ($hasPermission) {
                // If it's a dropdown/folder, only show if it has visible children
                $isFolder = !empty($item['children']) || (isset($item['route_path']) && $item['route_path'] === '#');

                if ($isFolder) {
                    if (!empty($item['children'])) {
                        $result[] = $item;
                    }
                } else {
                    // It's a leaf node link
                    $result[] = $item;
                }
            }
        }

        return $result;
    }

    /**
     * Get filtered menu items for the current user
     */
    public function getUserMenu(): array
    {
        try {
            $menuStructure = MenuConfig::getMenuStructure();
            return $this->filterMenuItems($menuStructure);
        } catch (\Exception $e) {
            Logger::error('MenuService: getUserMenu Error: ' . $e->getMessage());
            return [];
        }
    }
}
