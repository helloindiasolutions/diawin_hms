<?php
/**
 * Role API Controller
 * Handles permission and role management
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use System\Security;
use System\Session;
use System\JWT;

class RoleController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * List all roles with stats
     * GET /api/v1/roles
     */
    public function index(): void
    {
        try {
            // Check if current user is super_admin (code: SUPER_ADMIN, name: super_admin)
            $currentUserRoles = Session::get('roles') ?? [];
            $isSuperAdmin = false;
            $superAdminRoles = ['SUPER_ADMIN', 'super_admin', 'Super Admin', 'System Administrator'];
            foreach ($superAdminRoles as $saRole) {
                if (in_array($saRole, $currentUserRoles)) {
                    $isSuperAdmin = true;
                    break;
                }
            }

            // Get all roles
            $roles = $this->db->fetchAll("SELECT * FROM roles ORDER BY name ASC");

            // Filter and enrich roles
            $filteredRoles = [];
            foreach ($roles as &$role) {
                $roleName = strtolower($role['name']);
                $roleCode = strtolower($role['code'] ?? '');

                // Hide super_admin from non-super_admin users
                if (
                    !$isSuperAdmin && (
                        $roleName === 'super_admin' ||
                        $roleName === 'super admin' ||
                        $roleCode === 'super_admin' ||
                        $roleCode === 'super-admin'
                    )
                ) {
                    continue; // Skip super_admin role for non-super_admin users
                }

                $count = $this->db->fetchColumn(
                    "SELECT COUNT(*) FROM user_roles WHERE role_id = ?",
                    [$role['role_id']]
                );

                $role['users_count'] = $count;
                // Add dummy scope/modified if not in DB, to support UI
                $role['scope'] = $role['scope'] ?? 'Global';
                $role['is_system'] = in_array($roleName, ['admin', 'super admin', 'system admin', 'doctor', 'patient']);
                $role['modified_at'] = $role['updated_at'] ?? $role['created_at'] ?? date('Y-m-d');

                $filteredRoles[] = $role;
            }

            Response::success(['roles' => $filteredRoles]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Create new role
     * POST /api/v1/roles
     */
    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['name'])) {
            Response::error('Role name is required', 400);
            return;
        }

        try {
            // Check duplicate
            $exists = $this->db->fetchColumn("SELECT COUNT(*) FROM roles WHERE name = ?", [$data['name']]);
            if ($exists > 0) {
                Response::error('Role name already exists', 400);
                return;
            }

            $id = $this->db->insert('roles', [
                'name' => Security::sanitizeInput($data['name']),
                'code' => strtoupper(str_slug($data['name'])),
                'description' => Security::sanitizeInput($data['description'] ?? ''),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            Response::success(['role_id' => $id], 'Role created successfully', 201);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Update role
     * PUT /api/v1/roles/{id}
     */
    public function update(string $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['name'])) {
            Response::error('Role name is required', 400);
            return;
        }

        try {
            $this->db->update('roles', [
                'name' => Security::sanitizeInput($data['name']),
                'description' => Security::sanitizeInput($data['description'] ?? ''),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'role_id = ?', [$id]);

            Response::success(null, 'Role updated successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Delete role
     * DELETE /api/v1/roles/{id}
     */
    public function destroy(string $id): void
    {
        try {
            // Prevent deleting system roles
            $role = $this->db->fetch("SELECT name FROM roles WHERE role_id = ?", [$id]);
            if (!$role) {
                Response::error('Role not found', 404);
                return;
            }

            $systemRoles = ['admin', 'super_admin', 'system_admin', 'doctor', 'patient', 'receptionist', 'pharmacist'];
            if (in_array(strtolower($role['name']), $systemRoles)) {
                Response::error('Cannot delete system protected role', 403);
                return;
            }

            // Check if users assigned
            $count = $this->db->fetchColumn("SELECT COUNT(*) FROM user_roles WHERE role_id = ?", [$id]);
            if ($count > 0) {
                Response::error("Cannot delete role: Assigned to $count users", 400);
                return;
            }

            $this->db->delete('roles', 'role_id = ?', [$id]);
            Response::success(null, 'Role deleted successfully');

        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}

/**
 * Helper for slug if not exists globally
 */
if (!function_exists('str_slug')) {
    function str_slug($str)
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $str)));
    }
}
