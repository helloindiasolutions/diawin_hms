<?php
/**
 * Therapy Management API Controller
 * Handles sessions, protocols, and therapy rules
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use System\Security;
use System\Logger;
use System\JWT;
use System\Session;

class TherapyController
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
     * Therapy Sessions
     */
    public function sessions(): void
    {
        $branchId = $this->user['branch_id'] ?? null;
        $params = [];
        $where = ["1=1"];

        if ($branchId) {
            $where[] = "s.branch_id = ?";
            $params[] = $branchId;
        }

        $sql = "SELECT s.*, p.first_name, p.last_name, p.mrn, pr.name as protocol_name, staff.full_name as practitioner_name
                FROM therapy_sessions s
                JOIN patients p ON s.patient_id = p.patient_id
                JOIN therapy_protocols pr ON s.protocol_id = pr.protocol_id
                LEFT JOIN staff ON s.practitioner_id = staff.staff_id
                WHERE " . implode(" AND ", $where) . "
                ORDER BY s.scheduled_on DESC, s.scheduled_time DESC";

        try {
            $sessions = $this->db->fetchAll($sql, $params);
            Response::success(['sessions' => $sessions]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Therapy Protocols
     */
    public function protocols(): void
    {
        $branchId = $this->user['branch_id'] ?? null;
        $params = [];
        $where = ["1=1"];

        if ($branchId) {
            $where[] = "branch_id = ?";
            $params[] = $branchId;
        }

        try {
            $protocols = $this->db->fetchAll("SELECT * FROM therapy_protocols WHERE " . implode(" AND ", $where) . " ORDER BY name", $params);
            Response::success(['protocols' => $protocols]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Therapy Rules
     */
    public function rules(): void
    {
        $branchId = $this->user['branch_id'] ?? null;
        $params = [];
        $where = ["1=1"];

        if ($branchId) {
            $where[] = "branch_id = ?";
            $params[] = $branchId;
        }

        try {
            $rules = $this->db->fetchAll("SELECT * FROM therapy_rules WHERE " . implode(" AND ", $where) . " ORDER BY name", $params);
            Response::success(['rules' => $rules]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Store Therapy Session
     */
    public function storeSession(): void
    {
        $data = Security::sanitizeInput($_POST);

        $required = ['patient_id', 'protocol_id', 'scheduled_on'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::error("Field '$field' is required", 400);
                return;
            }
        }

        $branchId = $this->user['branch_id'] ?? null;

        $sql = "INSERT INTO therapy_sessions (protocol_id, branch_id, patient_id, scheduled_on, scheduled_time, practitioner_id, notes, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'scheduled')";

        try {
            $this->db->query($sql, [
                $data['protocol_id'],
                $branchId,
                $data['patient_id'],
                $data['scheduled_on'],
                $data['scheduled_time'] ?? null,
                $data['practitioner_id'] ?? null,
                $data['notes'] ?? null
            ]);
            Response::success(['message' => 'Session booked successfully']);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Store Therapy Protocol
     */
    public function storeProtocol(): void
    {
        $data = Security::sanitizeInput($_POST);
        if (empty($data['name'])) {
            Response::error("Protocol name is required", 400);
            return;
        }

        $branchId = $this->user['branch_id'] ?? null;

        try {
            $this->db->query("INSERT INTO therapy_protocols (branch_id, code, name, description, duration_days, type) VALUES (?, ?, ?, ?, ?, ?)", [
                $branchId,
                $data['code'] ?? null,
                $data['name'],
                $data['description'] ?? null,
                (int) ($data['duration_days'] ?? 0),
                $data['type'] ?? 'others'
            ]);
            Response::success(['message' => 'Protocol created successfully']);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Log Consumable Usage
     */
    public function logConsumable(): void
    {
        $data = Security::sanitizeInput($_POST);
        if (empty($data['session_id']) || empty($data['consumable_id'])) {
            Response::error("Session ID and Consumable ID are required", 400);
            return;
        }

        try {
            $this->db->query("INSERT INTO therapy_consumables (session_id, consumable_id, qty_used) VALUES (?, ?, ?)", [
                $data['session_id'],
                $data['consumable_id'],
                (float) ($data['qty_used'] ?? 0)
            ]);
            Response::success(['message' => 'Consumable usage logged successfully']);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Update Session Status
     */
    public function updateStatus(): void
    {
        $data = Security::sanitizeInput($_POST);
        if (empty($data['session_id']) || empty($data['status'])) {
            Response::error("Session ID and Status are required", 400);
            return;
        }

        try {
            $sql = "UPDATE therapy_sessions SET status = ?, completed_at = ? WHERE session_id = ?";
            $completedAt = ($data['status'] === 'completed') ? date('Y-m-d H:i:s') : null;
            $this->db->query($sql, [$data['status'], $completedAt, $data['session_id']]);
            Response::success(['message' => 'Session updated successfully']);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
