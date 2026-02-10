<?php
/**
 * Doctor API Controller
 * Handles all provider/doctor-related API endpoints for Doctor Master
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use System\Security;
use System\Logger;
use System\JWT;
use System\Session;

class DoctorController
{
    private Database $db;
    private ?array $user;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->user = $this->getAuthUser();
    }

    /**
     * Get authenticated user from JWT token or session
     */
    private function getAuthUser(): ?array
    {
        $token = JWT::getTokenFromHeader();
        if ($token) {
            $payload = JWT::getPayload($token);
            if ($payload)
                return $payload;
        }

        if (Session::isLoggedIn()) {
            return Session::getUser();
        }

        return null;
    }

    /**
     * List doctors with filters and search
     */
    public function index(): void
    {
        $search = Security::sanitizeInput($_GET['search'] ?? '');
        $specialty = $_GET['specialty'] ?? '';
        $status = $_GET['status'] ?? '';

        $where = ['1=1'];
        $params = [];

        if (!empty($this->user['branch_id'])) {
            $where[] = '(branch_id = ? OR branch_id IS NULL)';
            $params[] = $this->user['branch_id'];
        }

        if (!empty($search)) {
            $where[] = '(full_name LIKE ? OR specialization LIKE ? OR department LIKE ? OR phone LIKE ?)';
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term]);
        }

        if (!empty($specialty)) {
            $where[] = 'specialization = ?';
            $params[] = $specialty;
        }

        if ($status !== '') {
            $where[] = 'is_active = ?';
            $params[] = $status === 'active' ? 1 : 0;
        }

        $sql = "SELECT * FROM providers WHERE " . implode(' AND ', $where) . " ORDER BY full_name ASC";

        try {
            $doctors = $this->db->fetchAll($sql, $params);
            Response::success(['doctors' => $doctors]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Get Doctor Stats
     */
    public function stats(): void
    {
        try {
            $totalSql = "SELECT COUNT(*) FROM providers";
            $activeSql = "SELECT COUNT(*) FROM providers WHERE is_active = 1";
            $specialtiesSql = "SELECT COUNT(DISTINCT specialization) FROM providers WHERE specialization IS NOT NULL AND specialization != ''";

            $total = $this->db->fetchColumn($totalSql);
            $active = $this->db->fetchColumn($activeSql);
            $specs = $this->db->fetchColumn($specialtiesSql);

            Response::success([
                'total' => $total,
                'active' => $active,
                'specialties_count' => $specs,
                'inactive' => $total - $active
            ]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Get unique specialties
     */
    public function specialties(): void
    {
        try {
            $sql = "SELECT DISTINCT specialization FROM providers WHERE specialization IS NOT NULL AND specialization != '' ORDER BY specialization ASC";
            $specialties = $this->db->fetchAll($sql);
            Response::success(['specialties' => array_column($specialties, 'specialization')]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Create new doctor
     */
    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Basic validation
        if (empty($data['full_name'])) {
            Response::error('Full name is required', 400);
            return;
        }

        if (!empty($data['phone']) && !Security::isValidPhone($data['phone'])) {
            Response::error('Please enter a valid 10-digit mobile number', 422);
            return;
        }

        if (!empty($data['email']) && !Security::isValidEmail($data['email'])) {
            Response::error('Please enter a valid email address', 422);
            return;
        }

        // Ensure necessary columns exist (fallback if DDL failed)
        $this->ensureColumnsExist();

        $insertData = [
            'full_name' => Security::sanitizeInput($data['full_name']),
            'specialization' => Security::sanitizeInput($data['specialization'] ?? ''),
            'department' => Security::sanitizeInput($data['department'] ?? ''),
            'phone' => Security::sanitizeInput($data['phone'] ?? ''),
            'email' => Security::sanitizeInput($data['email'] ?? ''),
            'license_no' => Security::sanitizeInput($data['license_no'] ?? ''),
            'experience' => (int) ($data['experience'] ?? 0),
            'consultation_fee' => (float) ($data['consultation_fee'] ?? 0.00),
            'is_active' => (int) ($data['is_active'] ?? 1),
            'branch_id' => $this->user['branch_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $id = $this->db->insert('providers', $insertData);
            Response::success(['provider_id' => $id], 'Doctor added successfully', 201);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Update doctor details
     */
    public function update(string $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['full_name'])) {
            Response::error('Full name is required', 400);
            return;
        }

        if (!empty($data['phone']) && !Security::isValidPhone($data['phone'])) {
            Response::error('Please enter a valid 10-digit mobile number', 422);
            return;
        }

        if (!empty($data['email']) && !Security::isValidEmail($data['email'])) {
            Response::error('Please enter a valid email address', 422);
            return;
        }

        $this->ensureColumnsExist();

        $updateData = [
            'full_name' => Security::sanitizeInput($data['full_name']),
            'specialization' => Security::sanitizeInput($data['specialization'] ?? ''),
            'department' => Security::sanitizeInput($data['department'] ?? ''),
            'phone' => Security::sanitizeInput($data['phone'] ?? ''),
            'email' => Security::sanitizeInput($data['email'] ?? ''),
            'license_no' => Security::sanitizeInput($data['license_no'] ?? ''),
            'experience' => (int) ($data['experience'] ?? 0),
            'consultation_fee' => (float) ($data['consultation_fee'] ?? 0.00),
            'is_active' => (int) ($data['is_active'] ?? 1),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $this->db->update('providers', $updateData, 'provider_id = ?', [$id]);
            Response::success(null, 'Doctor details updated');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Delete doctor
     */
    public function destroy(string $id): void
    {
        try {
            // Check if doctor has appointments (optional check for deleting)
            $count = $this->db->fetchColumn("SELECT COUNT(*) FROM appointments WHERE provider_id = ?", [$id]);
            if ($count > 0) {
                // Deactivate instead of delete if appointments exist
                $this->db->update('providers', ['is_active' => 0], 'provider_id = ?', [$id]);
                Response::success(null, 'Doctor deactivated because appointments exist');
            } else {
                $this->db->delete('providers', 'provider_id = ?', [$id]);
                Response::success(null, 'Doctor removed successfully');
            }
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Internal helper to make sure commercial columns exist
     */
    private function ensureColumnsExist(): void
    {
        try {
            $columns = $this->db->fetchAll("DESCRIBE providers");
            $names = array_column($columns, 'Field');

            $toAdd = [
                'phone' => "ALTER TABLE providers ADD COLUMN phone VARCHAR(20)",
                'email' => "ALTER TABLE providers ADD COLUMN email VARCHAR(100)",
                'department' => "ALTER TABLE providers ADD COLUMN department VARCHAR(100)",
                'license_no' => "ALTER TABLE providers ADD COLUMN license_no VARCHAR(50)",
                'experience' => "ALTER TABLE providers ADD COLUMN experience INT",
                'consultation_fee' => "ALTER TABLE providers ADD COLUMN consultation_fee DECIMAL(10,2)",
                'created_at' => "ALTER TABLE providers ADD COLUMN created_at DATETIME",
                'updated_at' => "ALTER TABLE providers ADD COLUMN updated_at DATETIME"
            ];

            foreach ($toAdd as $col => $sql) {
                if (!in_array($col, $names)) {
                    $this->db->getPdo()->exec($sql);
                }
            }
        } catch (\Exception $e) {
            // Silently fail if DDL errors (usually already exists or permission issue)
        }
    }

    /**
     * Get unique departments from providers table
     */
    public function getDepartments(): void
    {
        try {
            $sql = "SELECT DISTINCT department 
                    FROM providers 
                    WHERE department IS NOT NULL 
                    AND department != '' 
                    AND is_active = 1 
                    ORDER BY department ASC";
            
            $result = $this->db->fetchAll($sql);
            $departments = array_column($result, 'department');
            
            Response::success(['departments' => $departments]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}

