<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use System\Session;
use App\Middleware\BranchMiddleware;

class BranchController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all branches (Super Admin) or user's branch (Branch Admin)
     */
    public function index(): void
    {
        try {
            $userRole = Session::get('role');
            $branchId = BranchMiddleware::getUserBranchId();

            $sql = "SELECT 
                        b.branch_id,
                        b.code,
                        b.name as branch_name,
                        b.address,
                        b.city,
                        b.state,
                        b.country,
                        b.pincode,
                        b.phone,
                        b.email,
                        b.is_main,
                        b.is_active,
                        b.timezone,
                        b.created_at,
                        COUNT(DISTINCT u.user_id) as total_users,
                        COUNT(DISTINCT p.patient_id) as total_patients,
                        COUNT(DISTINCT s.staff_id) as staff_count
                    FROM branches b
                    LEFT JOIN users u ON b.branch_id = u.branch_id AND u.is_active = 1
                    LEFT JOIN patients p ON b.branch_id = p.branch_id
                    LEFT JOIN staff s ON b.branch_id = s.branch_id
                    ";

            // Branch admin can only see their branch
            if ($branchId !== null) {
                $sql .= " WHERE b.branch_id = ?";
                $params = [$branchId];
            } else {
                $params = [];
            }

            $sql .= " GROUP BY b.branch_id ORDER BY b.is_main DESC, b.name ASC";

            $branches = $this->db->fetchAll($sql, $params);
            Response::success(['branches' => $branches]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch branches: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single branch details
     */
    public function show(int $branchId): void
    {
        try {
            // Check access
            $userBranchId = BranchMiddleware::getUserBranchId();
            if ($userBranchId !== null && $userBranchId !== $branchId) {
                Response::error('Access denied', 403);
                return;
            }

            $sql = "SELECT * FROM branches WHERE branch_id = ?";
            $branch = $this->db->fetch($sql, [$branchId]);

            if (!$branch) {
                Response::error('Branch not found', 404);
                return;
            }

            Response::success(['branch' => $branch]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch branch: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new branch (Super Admin only)
     */
    public function store(): void
    {
        try {
            // Only super admin can create branches
            if (!BranchMiddleware::isSuperAdmin(Session::get('role'))) {
                Response::error('Only Super Admin can create branches', 403);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            if (empty($data['code']) || empty($data['name'])) {
                Response::error('Branch code and name are required', 400);
                return;
            }

            // Check if code already exists
            $existing = $this->db->fetch("SELECT branch_id FROM branches WHERE code = ?", [$data['code']]);
            if ($existing) {
                Response::error('Branch code already exists', 400);
                return;
            }

            $sql = "INSERT INTO branches (
                        code, name, address, city, state, country, pincode, 
                        phone, email, timezone, is_main, is_active
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $params = [
                $data['code'],
                $data['name'],
                $data['address'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['country'] ?? 'India',
                $data['pincode'] ?? null,
                $data['phone'] ?? null,
                $data['email'] ?? null,
                $data['timezone'] ?? 'Asia/Kolkata',
                $data['is_main'] ?? 0,
                $data['is_active'] ?? 1
            ];

            $this->db->execute($sql, $params);
            $branchId = $this->db->lastInsertId();

            Response::success([
                'message' => 'Branch created successfully',
                'branch_id' => $branchId
            ], 201);
        } catch (\Exception $e) {
            Response::error('Failed to create branch: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update branch
     */
    public function update(int $branchId): void
    {
        try {
            // Check access
            $userBranchId = BranchMiddleware::getUserBranchId();
            $userRole = Session::get('role');

            // Super admin can update any branch, branch admin can update their own
            if ($userBranchId !== null && $userBranchId !== $branchId) {
                Response::error('Access denied', 403);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            // Branch admin cannot change certain fields
            if (BranchMiddleware::isBranchAdmin($userRole)) {
                unset($data['code'], $data['is_main']);
            }

            $updates = [];
            $params = [];

            $allowedFields = ['name', 'address', 'city', 'state', 'country', 'pincode', 'phone', 'email', 'timezone', 'is_active'];

            // Super admin can also update these
            if (BranchMiddleware::isSuperAdmin($userRole)) {
                $allowedFields[] = 'code';
                $allowedFields[] = 'is_main';
            }

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }

            if (empty($updates)) {
                Response::error('No valid fields to update', 400);
                return;
            }

            $params[] = $branchId;
            $sql = "UPDATE branches SET " . implode(', ', $updates) . " WHERE branch_id = ?";

            $this->db->execute($sql, $params);

            Response::success(['message' => 'Branch updated successfully']);
        } catch (\Exception $e) {
            Response::error('Failed to update branch: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete branch (Super Admin only)
     */
    public function destroy(int $branchId): void
    {
        try {
            // Only super admin can delete branches
            if (!BranchMiddleware::isSuperAdmin(Session::get('role'))) {
                Response::error('Only Super Admin can delete branches', 403);
                return;
            }

            // Check if branch has users
            $userCount = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE branch_id = ?", [$branchId]);
            if ($userCount['count'] > 0) {
                Response::error('Cannot delete branch with active users. Please reassign users first.', 400);
                return;
            }

            // Soft delete by setting is_active = 0
            $this->db->execute("UPDATE branches SET is_active = 0 WHERE branch_id = ?", [$branchId]);

            Response::success(['message' => 'Branch deleted successfully']);
        } catch (\Exception $e) {
            Response::error('Failed to delete branch: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get branch users
     */
    public function getBranchUsers(int $branchId): void
    {
        try {
            // Check access
            $userBranchId = BranchMiddleware::getUserBranchId();
            if ($userBranchId !== null && $userBranchId !== $branchId) {
                Response::error('Access denied', 403);
                return;
            }

            $sql = "SELECT 
                        u.user_id,
                        u.username,
                        u.full_name,
                        u.email,
                        u.mobile,
                        u.is_active,
                        u.last_login,
                        u.created_at,
                        GROUP_CONCAT(r.name) as roles
                    FROM users u
                    LEFT JOIN user_roles ur ON u.user_id = ur.user_id
                    LEFT JOIN roles r ON ur.role_id = r.role_id
                    WHERE u.branch_id = ?
                    GROUP BY u.user_id
                    ORDER BY u.full_name ASC";

            $users = $this->db->fetchAll($sql, [$branchId]);
            Response::success(['users' => $users]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch branch users: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Assign user to branch
     */
    public function assignUser(): void
    {
        try {
            // Only super admin can assign users to branches
            if (!BranchMiddleware::isSuperAdmin(Session::get('role'))) {
                Response::error('Only Super Admin can assign users to branches', 403);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['user_id']) || empty($data['branch_id'])) {
                Response::error('User ID and Branch ID are required', 400);
                return;
            }

            $sql = "UPDATE users SET branch_id = ? WHERE user_id = ?";
            $this->db->execute($sql, [$data['branch_id'], $data['user_id']]);

            Response::success(['message' => 'User assigned to branch successfully']);
        } catch (\Exception $e) {
            Response::error('Failed to assign user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create branch admin user
     */
    public function createBranchAdmin(): void
    {
        try {
            // Only super admin can create branch admins
            if (!BranchMiddleware::isSuperAdmin(Session::get('role'))) {
                Response::error('Only Super Admin can create branch admins', 403);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            if (empty($data['branch_id']) || empty($data['username']) || empty($data['password']) || empty($data['full_name'])) {
                Response::error('Branch ID, username, password, and full name are required', 400);
                return;
            }

            // Check if username exists
            $existing = $this->db->fetch("SELECT user_id FROM users WHERE username = ?", [$data['username']]);
            if ($existing) {
                Response::error('Username already exists', 400);
                return;
            }

            // Get or create Branch Admin role
            $role = $this->db->fetch("SELECT role_id FROM roles WHERE code = 'BRANCH_ADMIN'");
            if (!$role) {
                // Create Branch Admin role
                $this->db->execute(
                    "INSERT INTO roles (code, name, description) VALUES ('BRANCH_ADMIN', 'Branch Admin', 'Branch Administrator')"
                );
                $roleId = $this->db->lastInsertId();
            } else {
                $roleId = $role['role_id'];
            }

            // Create user
            $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

            $sql = "INSERT INTO users (branch_id, username, password_hash, full_name, email, mobile, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, 1)";

            $params = [
                $data['branch_id'],
                $data['username'],
                $passwordHash,
                $data['full_name'],
                $data['email'] ?? null,
                $data['mobile'] ?? null
            ];

            $this->db->execute($sql, $params);
            $userId = $this->db->lastInsertId();

            // Assign Branch Admin role
            $this->db->execute(
                "INSERT INTO user_roles (user_id, role_id, branch_id) VALUES (?, ?, ?)",
                [$userId, $roleId, $data['branch_id']]
            );

            Response::success([
                'message' => 'Branch admin created successfully',
                'user_id' => $userId
            ], 201);
        } catch (\Exception $e) {
            Response::error('Failed to create branch admin: ' . $e->getMessage(), 500);
        }
    }
}
