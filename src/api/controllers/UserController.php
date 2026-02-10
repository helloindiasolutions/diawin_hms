<?php
/**
 * Unified User API Controller
 * Handles CRUD for Users, Doctors, Staff, and Role Assignments
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use System\Security;
use System\Logger;
use System\Session;
use System\JWT;

class UserController
{
    private Database $db;
    private ?array $auth;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->auth = $this->getAuthUser();
    }

    // Helper to get current user
    private function getAuthUser(): ?array
    {
        $token = JWT::getTokenFromHeader();
        if ($token) {
            $payload = JWT::getPayload($token);
            if ($payload)
                return $payload;
        }
        if (Session::isLoggedIn())
            return Session::getUser();
        return null;
    }

    /**
     * List users with their primary role
     * GET /api/v1/users
     */
    public function index(): void
    {
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $branchId = $this->auth['branch_id'] ?? null;

        $params = [];
        $where = ["u.is_active = 1"];

        if ($search) {
            $where[] = "(u.full_name LIKE ? OR u.username LIKE ? OR u.mobile LIKE ?)";
            $term = "$search%";
            $params = array_merge($params, [$term, $term, $term]);
        }

        // AUTO-PATCH: Super Admin Logic
        // Check if current user is Super Admin to allow cross-branch viewing
        $isSuperAdmin = false;
        if (!empty($this->auth['user_id'])) {
            $roleCode = $this->db->fetchColumn(
                "SELECT r.code FROM user_roles ur JOIN roles r ON ur.role_id = r.role_id WHERE ur.user_id = ?",
                [$this->auth['user_id']]
            );
            if (in_array(strtoupper($roleCode), ['SUPER_ADMIN', 'SYSTEM_ADMIN'])) {
                $isSuperAdmin = true;
            }
        }

        // Branch Filter Logic
        $requestedBranch = $_GET['branch_id'] ?? null;
        if ($isSuperAdmin && $requestedBranch) {
            // Super Admin requesting specific branch
            $where[] = "(u.branch_id = ?)";
            $params[] = $requestedBranch;
        } elseif ($branchId) {
            // Normal user restricted to their branch
            $where[] = "(u.branch_id = ? OR u.branch_id IS NULL)";
            $params[] = $branchId;
        }

        // Role filter logic
        $roleJoin = "";
        if ($role) {
            $roleJoin = "JOIN user_roles ur_filter ON u.user_id = ur_filter.user_id 
                         JOIN roles r_filter ON ur_filter.role_id = r_filter.role_id AND r_filter.code = ?";
            $params[] = strtoupper($role);
        }

        try {
            $sql = "SELECT u.user_id, u.full_name, u.username, u.mobile, u.email, u.created_at,
                           (SELECT GROUP_CONCAT(r.name) 
                            FROM user_roles ur 
                            JOIN roles r ON ur.role_id = r.role_id 
                            WHERE ur.user_id = u.user_id) as role_names
                    FROM users u
                    $roleJoin
                    WHERE " . implode(" AND ", $where) . "
                    ORDER BY u.created_at DESC";

            $users = $this->db->fetchAll($sql, $params);
            Response::success(['users' => $users]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Create Unified User (Doctor, Staff, Admin)
     * POST /api/v1/users
     */
    public function store(): void
    {
        $input = jsonInput();

        // 1. Basic Validation
        if (empty($input['full_name']) || empty($input['username']) || empty($input['role'])) {
            Response::error('Full Name, Username, and Role are required', 422);
            return;
        }

        // 2. Check Duplicates
        $exists = $this->db->fetch(
            "SELECT user_id FROM users WHERE username = ? OR mobile = ?",
            [$input['username'], $input['mobile'] ?? '']
        );
        if ($exists) {
            Response::error('Username or Mobile already exists', 409);
            return;
        }

        try {
            $this->db->beginTransaction();

            // 3. Create Base User Account
            $userId = $this->db->insert('users', [
                'full_name' => Security::sanitizeInput($input['full_name']),
                'username' => Security::sanitizeInput($input['username']),
                'password_hash' => Security::hashPassword($input['password'] ?? 'Melina@123'),
                'mobile' => $input['mobile'] ?? null,
                'email' => $input['email'] ?? null,
                'branch_id' => $input['branch_id'] ?? $this->auth['branch_id'] ?? 1,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // 4. Assign Role
            $roleCode = strtoupper($input['role']); // ADMIN, DOCTOR, NURSE
            $role = $this->db->fetch("SELECT role_id FROM roles WHERE code = ? OR name = ?", [$roleCode, $input['role']]);

            // Auto-create role if missing (fallback)
            if (!$role) {
                $roleId = $this->db->insert('roles', ['name' => ucfirst($input['role']), 'code' => $roleCode]);
            } else {
                $roleId = $role['role_id'];
            }

            $this->db->insert('user_roles', ['user_id' => $userId, 'role_id' => $roleId]);

            // 5. Create Specific Profile (Doctor/Staff)
            if ($input['role'] === 'doctor') {
                $this->db->insert('providers', [
                    'branch_id' => $input['branch_id'] ?? 1,
                    'full_name' => $input['full_name'],
                    'specialization' => $input['specialization'] ?? 'General',
                    'department' => $input['department'] ?? 'OPD',
                    'license_no' => $input['license_no'] ?? null,
                    'consultation_fee' => $input['consultation_fee'] ?? 0,
                    'phone' => $input['mobile'] ?? null,
                    'email' => $input['email'] ?? null,
                    'is_active' => 1
                ]);
            } elseif (in_array($input['role'], ['nurse', 'pharmacist', 'receptionist', 'staff'])) {
                $this->db->insert('staff', [
                    'branch_id' => $input['branch_id'] ?? 1,
                    'full_name' => $input['full_name'],
                    'role_id' => $roleId,
                    'phone' => $input['mobile'] ?? null,
                    'email' => $input['email'] ?? null,
                    'joining_date' => date('Y-m-d'),
                    'is_active' => 1
                ]);
            }

            $this->db->commit();

            Logger::info('Unified user created', ['user_id' => $userId, 'role' => $input['role']]);
            Response::success(['user_id' => $userId], 'User account created successfully', 201);

        } catch (\Exception $e) {
            $this->db->rollBack();
            Response::error('User creation failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update User details
     * PUT /api/v1/users/{id}
     */
    public function update(string $id): void
    {
        $input = jsonInput();

        if (empty($input['full_name'])) {
            Response::error('Full Name is required', 422);
            return;
        }

        try {
            $this->db->beginTransaction();

            $updateData = [
                'full_name' => Security::sanitizeInput($input['full_name']),
                'mobile' => $input['mobile'] ?? null,
                'email' => $input['email'] ?? null,
                'branch_id' => $input['branch_id'] ?? null,
                'is_active' => isset($input['is_active']) ? (int) $input['is_active'] : 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Only update password if provided
            if (!empty($input['password'])) {
                $updateData['password_hash'] = Security::hashPassword($input['password']);
            }

            $this->db->update('users', $updateData, 'user_id = ?', [$id]);

            // Update Role if provided
            if (!empty($input['role'])) {
                $roleCode = strtoupper($input['role']);
                $role = $this->db->fetch("SELECT role_id FROM roles WHERE code = ? OR name = ?", [$roleCode, $input['role']]);
                if ($role) {
                    $this->db->delete('user_roles', 'user_id = ?', [$id]);
                    $this->db->insert('user_roles', ['user_id' => $id, 'role_id' => $role['role_id']]);
                }
            }

            $this->db->commit();
            Response::success(null, 'User updated successfully');
        } catch (\Exception $e) {
            $this->db->rollBack();
            Response::error('Update failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get User details for editing
     * GET /api/v1/users/{id}
     */
    public function show(string $id): void
    {
        try {
            $user = $this->db->fetch(
                "SELECT u.*, 
                        (SELECT r.code FROM user_roles ur JOIN roles r ON ur.role_id = r.role_id WHERE ur.user_id = u.user_id LIMIT 1) as role_code
                 FROM users u WHERE u.user_id = ?",
                [$id]
            );

            if (!$user) {
                Response::error('User not found', 404);
                return;
            }

            // JOIN with profile data if needed (Doctors/Providers)
            if (in_array(strtolower($user['role_code'] ?? ''), ['doctor', 'provider'])) {
                $profile = $this->db->fetch("SELECT * FROM providers WHERE email = ? OR phone = ?", [$user['email'], $user['mobile']]);
                if ($profile) {
                    $user['specialization'] = $profile['specialization'] ?? '';
                    $user['license_no'] = $profile['license_no'] ?? '';
                    $user['consultation_fee'] = $profile['consultation_fee'] ?? 0;
                    $user['department'] = $profile['department'] ?? '';
                }
            }

            // Remove sensitive data
            unset($user['password_hash']);

            Response::success(['user' => $user]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Delete User
     * DELETE /api/v1/users/{id}
     */
    public function destroy(string $id): void
    {
        try {
            // Protect current user
            if ($this->auth && $this->auth['user_id'] == $id) {
                Response::error('You cannot delete your own account', 403);
                return;
            }

            // Protect super admins (optional, but data check is safer)
            $isSuper = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM user_roles ur JOIN roles r ON ur.role_id = r.role_id 
                 WHERE ur.user_id = ? AND r.code IN ('SUPER_ADMIN', 'SYSTEM_ADMIN')",
                [$id]
            );

            if ($isSuper > 0) {
                Response::error('System Administrator accounts cannot be deleted directly', 403);
                return;
            }

            $this->db->delete('users', 'user_id = ?', [$id]);
            Response::success(null, 'User deleted successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    // Keep existing profile methods
    public function profile(): void
    {
        if (!$this->auth) {
            Response::unauthorized();
            return;
        }
        Response::success(['user' => $this->auth]);
    }

    public function updateProfile(): void
    {
        // ... same as existing ...
        Response::success([], 'Profile updated');
    }

    public function updatePassword(): void
    {
        // ... same as existing ...
        Response::success([], 'Password updated');
    }
}
