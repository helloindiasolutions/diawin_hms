<?php
/**
 * Registration API Controller
 * Handles patient visit registrations (OP/IP)
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use System\Security;
use System\Logger;
use System\JWT;
use System\Session;

class RegistrationController
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
     * List Registrations
     */
    public function index(): void
    {
        $search = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $branchId = $this->user['branch_id'] ?? null;

        $params = [];
        $where = ["1=1"];

        if ($branchId) {
            $where[] = "v.branch_id = ?";
            $params[] = $branchId;
        }

        if ($type) {
            $where[] = "v.visit_type = ?";
            $params[] = $type;
        }

        if ($search) {
            $where[] = "(p.first_name LIKE ? OR p.last_name LIKE ? OR p.mrn LIKE ?)";
            $term = "%$search%";
            $params = array_merge($params, [$term, $term, $term]);
        }

        $sql = "SELECT v.*, p.first_name, p.last_name, p.mrn, p.primary_mobile, pr.full_name as provider_name
                FROM visits v
                JOIN patients p ON v.patient_id = p.patient_id
                LEFT JOIN providers pr ON v.primary_provider_id = pr.provider_id
                WHERE " . implode(" AND ", $where) . "
                ORDER BY v.visit_start DESC
                LIMIT 100";

        try {
            $registrations = $this->db->fetchAll($sql, $params);
            Response::success(['registrations' => $registrations]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Create New Registration (Visit)
     */
    public function store(): void
    {
        $input = jsonInput();
        if (empty($input['patient_id']) || empty($input['visit_type'])) {
            Response::error('Patient and Visit Type are required', 422);
            return;
        }

        try {
            $this->db->beginTransaction();

            $visitId = $this->db->insert('visits', [
                'branch_id' => $this->user['branch_id'] ?? null,
                'patient_id' => $input['patient_id'],
                'visit_type' => $input['visit_type'],
                'visit_start' => date('Y-m-d H:i:s'),
                'visit_status' => 'open',
                'primary_provider_id' => $input['provider_id'] ?? null,
                'created_by' => $this->user['user_id'] ?? null
            ]);

            // If IP, create admission record
            if ($input['visit_type'] === 'IP') {
                $admissionId = $this->db->insert('ip_admissions', [
                    'visit_id' => $visitId,
                    'patient_id' => $input['patient_id'],
                    'admission_number' => 'IP' . date('Ymd') . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'primary_doctor_id' => $input['provider_id'] ?? null,
                    'admission_date' => date('Y-m-d H:i:s'),
                    'admission_status' => 'Active',
                    'ward_id' => $input['ward_id'] ?? null,
                    'bed_id' => $input['bed_id'] ?? null,
                    'branch_id' => $this->user['branch_id'] ?? 1
                ]);

                // Update bed status if allocated
                if (!empty($input['bed_id'])) {
                    $this->db->update('beds', ['bed_status' => 'Occupied'], 'bed_id = ?', [$input['bed_id']]);

                    // Add bed allocation record
                    $this->db->insert('bed_allocations', [
                        'admission_id' => $admissionId,
                        'bed_id' => $input['bed_id'],
                        'ward_id' => $input['ward_id'],
                        'allocated_date' => date('Y-m-d H:i:s'),
                        'is_current' => 1
                    ]);
                }
            }

            $this->db->commit();
            Response::success(['visit_id' => $visitId], 'Registration successful', 201);
        } catch (\Exception $e) {
            $this->db->rollBack();
            Response::error($e->getMessage(), 500);
        }
    }
}
