<?php
/**
 * Staff API Controller
 * Handles staff management, roles, and profiles
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use System\Security;
use System\Logger;
use System\JWT;
use System\Session;

class StaffController
{
    private Database $db;
    private ?array $user;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->user = $this->getAuthUser();
    }

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
     * List all staff
     */
    public function index(): void
    {
        $search = $_GET['search'] ?? '';
        $branchId = $_GET['branch_id'] ?? null;

        // If no specific branch requested, fall back to user's branch
        if ($branchId === null || $branchId === '') {
            $branchId = $this->user['branch_id'] ?? null;
        }

        $params = [];
        $where = ["1=1"];

        if ($branchId && $branchId !== 'all') {
            $where[] = "s.branch_id = ?";
            $params[] = $branchId;
        }

        if ($search) {
            $where[] = "(s.full_name LIKE ? OR s.code LIKE ? OR s.email LIKE ?)";
            $term = "%$search%";
            $params = array_merge($params, [$term, $term, $term]);
        }

        $sql = "SELECT s.*, r.name as role_name 
                FROM staff s 
                LEFT JOIN roles r ON s.role_id = r.role_id 
                WHERE " . implode(" AND ", $where) . " 
                ORDER BY s.full_name ASC";

        try {
            $staff = $this->db->fetchAll($sql, $params);
            Response::success(['staff' => $staff]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Get staff roles
     */
    public function roles(): void
    {
        try {
            $roles = $this->db->fetchAll("SELECT * FROM roles ORDER BY name");
            Response::success(['roles' => $roles]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Save/Update staff
     */
    public function store(): void
    {
        $input = jsonInput();
        if (empty($input['full_name'])) {
            Response::error('Full name is required', 422);
            return;
        }

        if (!empty($input['phone']) && !Security::isValidPhone($input['phone'])) {
            Response::error('Please enter a valid 10-digit mobile number', 422);
            return;
        }

        if (!empty($input['email']) && !Security::isValidEmail($input['email'])) {
            Response::error('Please enter a valid email address', 422);
            return;
        }

        $data = [
            'full_name' => $input['full_name'],
            'branch_id' => $this->user['branch_id'] ?? null,
            'code' => $input['code'] ?? null,
            'role_id' => $input['role_id'] ?? null,
            'joining_date' => $input['joining_date'] ?? null,
            'phone' => $input['phone'] ?? null,
            'email' => $input['email'] ?? null,
            'address' => $input['address'] ?? null,
            'is_active' => $input['is_active'] ?? 1
        ];

        try {
            if (!empty($input['staff_id'])) {
                $this->db->update('staff', $data, 'staff_id = ?', [$input['staff_id']]);
                Response::success(null, 'Staff updated successfully');
            } else {
                $id = $this->db->insert('staff', $data);
                Response::success(['staff_id' => $id], 'Staff created successfully', 201);
            }
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Delete staff
     */
    public function destroy(string $id): void
    {
        try {
            $this->db->delete('staff', 'staff_id = ?', [$id]);
            Response::success(null, 'Staff removed successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
