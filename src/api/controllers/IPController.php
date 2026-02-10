<?php
/**
 * In-Patient (IP) API Controller
 * Handles admissions, bed management, nursing notes, and rounds
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use System\Security;
use System\Logger;
use System\JWT;
use System\Session;

class IPController
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
     * Admissions
     */
    public function admissions(): void
    {
        $branchId = $this->user['branch_id'] ?? null;
        $params = [];
        $where = ["1=1"];

        if ($branchId) {
            $where[] = "a.branch_id = ?";
            $params[] = $branchId;
        }

        $sql = "SELECT a.*, p.first_name, p.last_name, p.mrn, pr.full_name as provider_name
                FROM ip_admissions a
                JOIN visits v ON a.visit_id = v.visit_id
                JOIN patients p ON v.patient_id = p.patient_id
                LEFT JOIN providers pr ON a.admitting_provider_id = pr.provider_id
                WHERE " . implode(" AND ", $where) . "
                ORDER BY a.admitted_at DESC";

        try {
            $admissions = $this->db->fetchAll($sql, $params);
            Response::success(['admissions' => $admissions]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Bed Management
     */
    public function beds(): void
    {
        $branchId = $this->user['branch_id'] ?? null;
        $params = [];
        $where = ["1=1"];

        if ($branchId) {
            $where[] = "branch_id = ?";
            $params[] = $branchId;
        }

        try {
            $beds = $this->db->fetchAll("SELECT * FROM beds WHERE " . implode(" AND ", $where) . " ORDER BY ward, room, bed_no", $params);
            Response::success(['beds' => $beds]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function bedStats(): void
    {
        $branchId = $this->user['branch_id'] ?? null;
        $params = [];
        $branchSql = $branchId ? " AND w.branch_id = ?" : "";
        if ($branchId)
            $params[] = $branchId;

        try {
            $total = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM beds b JOIN wards w ON b.ward_id = w.ward_id WHERE 1=1 $branchSql", 
                $params
            );
            $available = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM beds b JOIN wards w ON b.ward_id = w.ward_id WHERE b.bed_status = 'Available' $branchSql", 
                $params
            );
            $wards = $this->db->fetchColumn(
                "SELECT COUNT(DISTINCT b.ward_id) FROM beds b JOIN wards w ON b.ward_id = w.ward_id WHERE 1=1 $branchSql", 
                $params
            );

            Response::success([
                'total_beds' => (int) $total,
                'available_beds' => (int) $available,
                'occupied_beds' => (int) ($total - $available),
                'wards_count' => (int) $wards
            ]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Nursing Notes
     */
    public function nursingNotes(int $admissionId): void
    {
        try {
            $notes = $this->db->fetchAll("SELECT * FROM nursing_notes WHERE admission_id = ? ORDER BY note_time DESC", [$admissionId]);
            Response::success(['notes' => $notes]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Doctor Rounds
     */
    public function rounds(int $admissionId): void
    {
        try {
            $rounds = $this->db->fetchAll("SELECT r.*, pr.full_name as provider_name FROM rounds r LEFT JOIN providers pr ON r.provider_id = pr.provider_id WHERE r.admission_id = ? ORDER BY r.round_time DESC", [$admissionId]);
            Response::success(['rounds' => $rounds]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
