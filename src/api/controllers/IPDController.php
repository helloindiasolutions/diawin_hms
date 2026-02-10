<?php
/**
 * IPD (In-Patient Department) API Controller
 * Handles all IPD-related API endpoints
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use System\Security;
use System\Logger;
use System\JWT;
use System\Session;

class IPDController
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

    // ==================== WARDS MANAGEMENT ====================

    public function getWards(): void
    {
        try {
            $branchId = $_GET['branch_id'] ?? $this->user['branch_id'] ?? 0;
            $branchSql = (!empty($branchId) && $branchId != 0) ? " AND w.branch_id = " . (int) $branchId : "";

            $sql = "SELECT w.*, 
                    (SELECT COUNT(*) FROM beds WHERE ward_id = w.ward_id AND is_active = 1) as total_beds,
                    (SELECT COUNT(*) FROM beds WHERE ward_id = w.ward_id AND bed_status = 'Available' AND is_active = 1) as available_beds
                    FROM wards w 
                    WHERE w.is_active = 1 $branchSql
                    ORDER BY w.ward_name";
            $wards = $this->db->fetchAll($sql);
            Response::success(['wards' => $wards]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function storeWard(): void
    {
        $input = jsonInput();

        if (empty($input['ward_name']) || empty($input['ward_code'])) {
            Response::error('Ward name and code are required', 422);
            return;
        }

        try {
            $id = $this->db->insert('wards', [
                'ward_name' => $input['ward_name'],
                'ward_code' => $input['ward_code'],
                'ward_type' => $input['ward_type'] ?? 'General',
                'floor_number' => $input['floor_number'] ?? null,
                'department' => $input['department'] ?? null,
                'branch_id' => $this->user['branch_id'] ?? 1
            ]);
            Response::success(['ward_id' => $id], 'Ward created successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function updateWard(int $wardId): void
    {
        $input = jsonInput();

        try {
            $updateData = [];
            if (isset($input['ward_name']))
                $updateData['ward_name'] = $input['ward_name'];
            if (isset($input['ward_code']))
                $updateData['ward_code'] = $input['ward_code'];
            if (isset($input['ward_type']))
                $updateData['ward_type'] = $input['ward_type'];
            if (isset($input['floor_number']))
                $updateData['floor_number'] = $input['floor_number'];
            if (isset($input['department']))
                $updateData['department'] = $input['department'];

            if (empty($updateData)) {
                Response::error('No data to update', 422);
                return;
            }

            $this->db->update('wards', $updateData, 'ward_id = ?', [$wardId]);
            Response::success(null, 'Ward updated successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function updateWardStatus(int $wardId): void
    {
        $input = jsonInput();

        if (!isset($input['is_active'])) {
            Response::error('Status is required', 422);
            return;
        }

        try {
            $this->db->update('wards', [
                'is_active' => $input['is_active'] ? 1 : 0
            ], 'ward_id = ?', [$wardId]);
            Response::success(null, 'Ward status updated successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function deleteWard(int $wardId): void
    {
        try {
            // Check if ward has beds
            $bedCount = $this->db->fetchColumn("SELECT COUNT(*) FROM beds WHERE ward_id = ? AND is_active = 1", [$wardId]);

            if ($bedCount > 0) {
                Response::error('Cannot delete ward with active beds. Please remove or deactivate all beds first.', 422);
                return;
            }

            $this->db->delete('wards', 'ward_id = ?', [$wardId]);
            Response::success(null, 'Ward deleted successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    // ==================== BEDS MANAGEMENT ====================

    public function getBeds(): void
    {
        $wardId = $_GET['ward_id'] ?? null;
        $branchId = $_GET['branch_id'] ?? $this->user['branch_id'] ?? 0;

        try {
            $where = "b.is_active = 1";
            $params = [];

            if ($wardId) {
                $where .= " AND b.ward_id = ?";
                $params[] = $wardId;
            }

            if (!empty($branchId) && $branchId != 0) {
                $where .= " AND w.branch_id = ?";
                $params[] = $branchId;
            }

            if (isset($_GET['status'])) {
                $where .= " AND b.bed_status = ?";
                $params[] = $_GET['status'];
            }

            $sql = "SELECT b.*, w.ward_name, w.ward_code, w.ward_type, w.branch_id
                    FROM beds b
                    JOIN wards w ON b.ward_id = w.ward_id
                    WHERE {$where}
                    ORDER BY w.ward_name, b.bed_number";
            $beds = $this->db->fetchAll($sql, $params);
            Response::success(['beds' => $beds]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function storeBed(): void
    {
        $input = jsonInput();

        try {
            $id = $this->db->insert('beds', [
                'bed_number' => $input['bed_number'],
                'ward_id' => $input['ward_id'],
                'bed_type' => $input['bed_type'] ?? 'Standard',
                'daily_rate' => $input['daily_rate'] ?? 0,
                'features' => $input['features'] ?? null
            ]);
            Response::success(['bed_id' => $id], 'Bed created successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function updateBedStatus(): void
    {
        $input = jsonInput();
        $bedId = $input['bed_id'] ?? null;
        $status = $input['status'] ?? null;

        if (!$bedId || !$status) {
            Response::error('Bed ID and status are required', 422);
            return;
        }

        try {
            $this->db->update('beds', [
                'bed_status' => $status
            ], 'bed_id = ?', [$bedId]);
            Response::success(null, 'Bed status updated');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function transferBed(): void
    {
        $input = jsonInput();
        $admissionId = $input['admission_id'] ?? null;
        $newBedId = $input['new_bed_id'] ?? null;
        $transferDate = $input['transfer_date'] ?? date('Y-m-d H:i:s');

        if (!$admissionId || !$newBedId) {
            Response::error('Admission ID and New Bed ID are required', 422);
            return;
        }

        try {
            $this->db->beginTransaction();

            // 1. Get current admission and bed
            $admission = $this->db->fetch("SELECT bed_id, ward_id FROM ip_admissions WHERE admission_id = ?", [$admissionId]);
            if (!$admission) {
                Response::error('Admission not found', 404);
                $this->db->rollBack();
                return;
            }

            $oldBedId = $admission['bed_id'];

            // 2. Check if new bed is available
            $newBed = $this->db->fetch("SELECT bed_status, ward_id, daily_rate FROM beds WHERE bed_id = ?", [$newBedId]);
            if (!$newBed || $newBed['bed_status'] !== 'Available') {
                Response::error('New bed is not available', 422);
                $this->db->rollBack();
                return;
            }

            // 3. Mark old bed as Available
            if ($oldBedId) {
                $this->db->update('beds', ['bed_status' => 'Available'], 'bed_id = ?', [$oldBedId]);

                // End previous allocation
                $this->db->update('bed_allocations', [
                    'released_date' => $transferDate,
                    'is_current' => 0
                ], 'admission_id = ? AND bed_id = ? AND is_current = 1', [$admissionId, $oldBedId]);
            }

            // 4. Mark new bed as Occupied
            $this->db->update('beds', ['bed_status' => 'Occupied'], 'bed_id = ?', [$newBedId]);

            // 5. Update admission record
            $this->db->update('ip_admissions', [
                'bed_id' => $newBedId,
                'ward_id' => $newBed['ward_id']
            ], 'admission_id = ?', [$admissionId]);

            // 6. Create new allocation entry
            $this->db->insert('bed_allocations', [
                'admission_id' => $admissionId,
                'bed_id' => $newBedId,
                'ward_id' => $newBed['ward_id'],
                'allocated_date' => $transferDate,
                'allocated_by' => $this->user['user_id'] ?? null,
                'daily_rate' => $newBed['daily_rate'] ?? 0,
                'is_current' => 1
            ]);

            $this->db->commit();
            Response::success(null, 'Bed transferred successfully');
        } catch (\Exception $e) {
            $this->db->rollBack();
            Response::error($e->getMessage(), 500);
        }
    }

    public function deallocateBed(): void
    {
        $input = jsonInput();
        $admissionId = $input['admission_id'] ?? null;
        $releaseDate = $input['release_date'] ?? date('Y-m-d H:i:s');

        if (!$admissionId) {
            Response::error('Admission ID is required', 422);
            return;
        }

        try {
            $this->db->beginTransaction();

            $admission = $this->db->fetch("SELECT bed_id FROM ip_admissions WHERE admission_id = ?", [$admissionId]);
            if (!$admission || !$admission['bed_id']) {
                Response::error('No active bed found for this admission', 404);
                $this->db->rollBack();
                return;
            }

            $bedId = $admission['bed_id'];

            // 1. Mark bed as Available
            $this->db->update('beds', ['bed_status' => 'Available'], 'bed_id = ?', [$bedId]);

            // 2. End allocation record
            $this->db->update('bed_allocations', [
                'released_date' => $releaseDate,
                'is_current' => 0
            ], 'admission_id = ? AND bed_id = ? AND is_current = 1', [$admissionId, $bedId]);

            // 3. Update admission
            $this->db->update('ip_admissions', [
                'bed_id' => null
            ], 'admission_id = ?', [$admissionId]);

            $this->db->commit();
            Response::success(null, 'Bed deallocated successfully');
        } catch (\Exception $e) {
            $this->db->rollBack();
            Response::error($e->getMessage(), 500);
        }
    }

    // ==================== ADMISSIONS ====================

    public function getAdmissions(): void
    {
        $status = $_GET['status'] ?? 'Active';
        $search = Security::sanitizeInput($_GET['search'] ?? '');
        $bedId = $_GET['bed_id'] ?? null;
        $branchId = $_GET['branch_id'] ?? $this->user['branch_id'] ?? 0;

        try {
            $where = ["a.admission_status = ?"];
            $params = [$status];

            // Filter by bed_id if provided
            if (!empty($bedId)) {
                $where[] = "a.bed_id = ?";
                $params[] = $bedId;
            }

            // Filter by branch_id if provided (must join ward for branch_id)
            if (!empty($branchId) && $branchId != 0) {
                $where[] = "w.branch_id = ?";
                $params[] = $branchId;
            }

            if (!empty($search)) {
                $where[] = "(p.mrn LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR a.admission_number LIKE ?)";
                $term = "%$search%";
                $params = array_merge($params, [$term, $term, $term, $term]);
            }

            $whereSql = implode(" AND ", $where);

            $sql = "SELECT a.*, 
                    p.mrn, p.first_name, p.last_name, p.gender, p.dob, p.primary_mobile, p.age, p.blood_group,
                    w.ward_name, w.ward_code, w.branch_id,
                    b.bed_number, b.daily_rate,
                    doc.full_name as doctor_name
                    FROM ip_admissions a
                    JOIN patients p ON a.patient_id = p.patient_id
                    LEFT JOIN wards w ON a.ward_id = w.ward_id
                    LEFT JOIN beds b ON a.bed_id = b.bed_id
                    LEFT JOIN providers doc ON a.primary_doctor_id = doc.provider_id
                    WHERE {$whereSql}
                    ORDER BY a.admission_date DESC
                    LIMIT 100";

            $admissions = $this->db->fetchAll($sql, $params);
            Response::success(['admissions' => $admissions]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function storeAdmission(): void
    {
        $input = jsonInput();

        if (empty($input['patient_id']) || empty($input['admission_date'])) {
            Response::error('Patient ID and admission date are required', 422);
            return;
        }

        try {
            // Generate admission number
            $admissionNumber = 'IP' . date('Ymd') . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Check if bed is available
            if (!empty($input['bed_id'])) {
                $bed = $this->db->fetch("SELECT bed_status FROM beds WHERE bed_id = ?", [$input['bed_id']]);
                if ($bed && $bed['bed_status'] !== 'Available') {
                    Response::error('Selected bed is not available', 422);
                    return;
                }
            }

            $this->db->beginTransaction();

            // Create admission
            $admissionId = $this->db->insert('ip_admissions', [
                'admission_number' => $admissionNumber,
                'patient_id' => $input['patient_id'],
                'visit_id' => $input['visit_id'] ?? null,
                'admission_date' => $input['admission_date'],
                'admission_type' => $input['admission_type'] ?? 'Planned',
                'admitting_doctor_id' => $input['admitting_doctor_id'] ?? null,
                'primary_doctor_id' => $input['primary_doctor_id'] ?? null,
                'department' => $input['department'] ?? null,
                'ward_id' => $input['ward_id'] ?? null,
                'bed_id' => $input['bed_id'] ?? null,
                'admission_reason' => $input['admission_reason'] ?? null,
                'provisional_diagnosis' => $input['provisional_diagnosis'] ?? null,
                'medical_history' => $input['medical_history'] ?? null,
                'allergies' => $input['allergies'] ?? null,
                'current_medications' => $input['current_medications'] ?? null,
                'estimated_discharge_date' => $input['estimated_discharge_date'] ?? null,
                'branch_id' => $this->user['branch_id'] ?? 1,
                'created_by' => $this->user['user_id'] ?? null
            ]);

            // Allocate bed if provided
            if (!empty($input['bed_id'])) {
                $bedRate = $this->db->fetchColumn("SELECT daily_rate FROM beds WHERE bed_id = ?", [$input['bed_id']]);

                $this->db->insert('bed_allocations', [
                    'admission_id' => $admissionId,
                    'bed_id' => $input['bed_id'],
                    'ward_id' => $input['ward_id'],
                    'allocated_date' => $input['admission_date'],
                    'allocated_by' => $this->user['user_id'] ?? null,
                    'daily_rate' => $bedRate ?? 0,
                    'is_current' => 1
                ]);

                // Update bed status
                $this->db->update('beds', ['bed_status' => 'Occupied'], 'bed_id = ?', [$input['bed_id']]);
            }

            $this->db->commit();

            Logger::info('IP Admission created', ['admission_id' => $admissionId, 'admission_number' => $admissionNumber]);
            Response::success(['admission_id' => $admissionId, 'admission_number' => $admissionNumber], 'Patient admitted successfully');
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to create admission', ['error' => $e->getMessage()]);
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Store Quick Emergency Admission (Registration + Admission)
     */
    public function storeQuickAdmission(): void
    {
        $input = jsonInput();

        if (empty($input['full_name']) || empty($input['gender']) || empty($input['age'])) {
            Response::error('Patient name, gender, and age are required for quick registration', 422);
            return;
        }

        try {
            $this->db->beginTransaction();

            // 1. Generate MRN
            $prefix = 'MRN';
            $year = date('y');
            $last = $this->db->fetch(
                "SELECT mrn FROM patients WHERE mrn LIKE ? ORDER BY patient_id DESC LIMIT 1",
                [$prefix . $year . '%']
            );
            $newNum = $last ? ((int) substr($last['mrn'], strlen($prefix) + 2)) + 1 : 1;
            $mrn = $prefix . $year . str_pad((string) $newNum, 5, '0', STR_PAD_LEFT);

            // 2. Create Patient
            $patientId = $this->db->insert('patients', [
                'branch_id' => $this->user['branch_id'] ?? 1,
                'mrn' => $mrn,
                'first_name' => Security::sanitizeInput($input['full_name']),
                'gender' => $input['gender'],
                'age' => (int) $input['age'],
                'primary_mobile' => Security::sanitizeInput($input['mobile'] ?? ''),
                'relation' => 'self',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // 3. Create Admission
            $admissionNumber = 'IP' . date('Ymd') . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $admissionDate = date('Y-m-d H:i:s');

            $admissionId = $this->db->insert('ip_admissions', [
                'admission_number' => $admissionNumber,
                'patient_id' => $patientId,
                'admission_date' => $admissionDate,
                'admission_type' => 'Emergency',
                'primary_doctor_id' => $input['primary_doctor_id'] ?? null,
                'ward_id' => $input['ward_id'] ?? null,
                'admission_reason' => $input['admission_reason'] ?? 'Emergency Admission',
                'branch_id' => $this->user['branch_id'] ?? 1,
                'created_by' => $this->user['user_id'] ?? null
            ]);

            $this->db->commit();

            Logger::info('Quick Emergency Admission created', ['admission_id' => $admissionId, 'mrn' => $mrn]);
            Response::success([
                'admission_id' => $admissionId,
                'admission_number' => $admissionNumber,
                'patient_id' => $patientId,
                'mrn' => $mrn
            ], 'Emergency admission created successfully');

        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to create quick admission', ['error' => $e->getMessage()]);
            Response::error($e->getMessage(), 500);
        }
    }

    public function getAdmissionDetails(int $admissionId): void
    {
        try {
            $sql = "SELECT a.*, 
                    p.mrn, p.first_name, p.last_name, CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                    p.gender, p.dob, p.primary_mobile, p.primary_email, p.address, p.age, p.blood_group,
                    w.ward_name, w.ward_code, w.ward_type,
                    b.bed_number, b.bed_type,
                    doc.full_name as doctor_name, doc.specialization
                    FROM ip_admissions a
                    JOIN patients p ON a.patient_id = p.patient_id
                    LEFT JOIN wards w ON a.ward_id = w.ward_id
                    LEFT JOIN beds b ON a.bed_id = b.bed_id
                    LEFT JOIN providers doc ON a.primary_doctor_id = doc.provider_id
                    WHERE a.admission_id = ?";

            $admission = $this->db->fetch($sql, [$admissionId]);

            if (!$admission) {
                Response::error('Admission not found', 404);
                return;
            }

            Response::success(['admission' => $admission]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    // ==================== NURSING NOTES ====================

    public function getNursingNotes(int $admissionId): void
    {
        try {
            $sql = "SELECT n.*, u.full_name as nurse_name
                    FROM nursing_notes n
                    LEFT JOIN users u ON n.nurse_id = u.user_id
                    WHERE n.admission_id = ?
                    ORDER BY n.note_date DESC";
            $notes = $this->db->fetchAll($sql, [$admissionId]);
            Response::success(['notes' => $notes]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function storeNursingNote(): void
    {
        $input = jsonInput();

        if (empty($input['admission_id'])) {
            Response::error('Admission ID is required', 422);
            return;
        }

        try {
            // Get patient_id from admission if not provided
            if (empty($input['patient_id'])) {
                $admission = $this->db->fetch("SELECT patient_id FROM ip_admissions WHERE admission_id = ?", [$input['admission_id']]);
                if (!$admission) {
                    Response::error('Admission not found', 404);
                    return;
                }
                $input['patient_id'] = $admission['patient_id'];
            }

            $id = $this->db->insert('nursing_notes', [
                'admission_id' => $input['admission_id'],
                'patient_id' => $input['patient_id'],
                'note_date' => $input['note_date'] ?? date('Y-m-d H:i:s'),
                'shift' => $input['shift'] ?? 'Morning',
                'nurse_id' => $this->user['user_id'] ?? null,
                'vital_signs' => $input['vital_signs'] ?? null,
                'intake_output' => $input['intake_output'] ?? null,
                'general_condition' => $input['general_condition'] ?? null,
                'consciousness_level' => $input['consciousness_level'] ?? 'Alert',
                'pain_score' => $input['pain_score'] ?? null,
                'medications_given' => $input['medications_given'] ?? null,
                'procedures_done' => $input['procedures_done'] ?? null,
                'observations' => $input['observations'] ?? null,
                'care_plan' => $input['care_plan'] ?? null
            ]);
            Response::success(['note_id' => $id], 'Nursing note added successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    // ==================== DOCTOR ROUNDS ====================

    public function getDoctorRounds(int $admissionId): void
    {
        try {
            $sql = "SELECT r.*, doc.full_name as doctor_name, doc.specialization
                    FROM doctor_rounds r
                    LEFT JOIN providers doc ON r.doctor_id = doc.provider_id
                    WHERE r.admission_id = ?
                    ORDER BY r.round_date DESC";
            $rounds = $this->db->fetchAll($sql, [$admissionId]);
            Response::success(['rounds' => $rounds]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function storeDoctorRound(): void
    {
        $input = jsonInput();

        if (empty($input['admission_id']) || empty($input['patient_id']) || empty($input['doctor_id'])) {
            Response::error('Admission ID, Patient ID, and Doctor ID are required', 422);
            return;
        }

        try {
            $id = $this->db->insert('doctor_rounds', [
                'admission_id' => $input['admission_id'],
                'patient_id' => $input['patient_id'],
                'round_date' => $input['round_date'] ?? date('Y-m-d H:i:s'),
                'doctor_id' => $input['doctor_id'],
                'round_type' => $input['round_type'] ?? 'Morning',
                'chief_complaint' => $input['chief_complaint'] ?? null,
                'examination_findings' => $input['examination_findings'] ?? null,
                'diagnosis' => $input['diagnosis'] ?? null,
                'treatment_plan' => $input['treatment_plan'] ?? null,
                'medications_prescribed' => $input['medications_prescribed'] ?? null,
                'investigations_ordered' => $input['investigations_ordered'] ?? null,
                'special_instructions' => $input['special_instructions'] ?? null,
                'next_review_date' => $input['next_review_date'] ?? null,
                'patient_condition' => $input['patient_condition'] ?? 'Stable'
            ]);
            Response::success(['round_id' => $id], 'Doctor round recorded successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    // ==================== DISCHARGE SUMMARY ====================

    public function getDischargeSummary(int $admissionId): void
    {
        try {
            $sql = "SELECT ds.*, doc.full_name as doctor_name
                    FROM discharge_summaries ds
                    LEFT JOIN providers doc ON ds.doctor_id = doc.provider_id
                    WHERE ds.admission_id = ?";
            $summary = $this->db->fetch($sql, [$admissionId]);
            Response::success(['summary' => $summary]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function storeDischargeSummary(): void
    {
        $input = jsonInput();

        if (empty($input['admission_id']) || empty($input['patient_id'])) {
            Response::error('Admission ID and Patient ID are required', 422);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Create discharge summary
            $id = $this->db->insert('discharge_summaries', [
                'admission_id' => $input['admission_id'],
                'patient_id' => $input['patient_id'],
                'discharge_date' => $input['discharge_date'] ?? date('Y-m-d H:i:s'),
                'discharge_type' => $input['discharge_type'] ?? 'Normal',
                'admitting_diagnosis' => $input['admitting_diagnosis'] ?? null,
                'final_diagnosis' => $input['final_diagnosis'] ?? null,
                'clinical_summary' => $input['clinical_summary'] ?? null,
                'investigations_summary' => $input['investigations_summary'] ?? null,
                'treatment_given' => $input['treatment_given'] ?? null,
                'surgical_procedures' => $input['surgical_procedures'] ?? null,
                'condition_on_discharge' => $input['condition_on_discharge'] ?? null,
                'discharge_medications' => $input['discharge_medications'] ?? null,
                'follow_up_instructions' => $input['follow_up_instructions'] ?? null,
                'follow_up_date' => $input['follow_up_date'] ?? null,
                'diet_advice' => $input['diet_advice'] ?? null,
                'activity_restrictions' => $input['activity_restrictions'] ?? null,
                'doctor_id' => $input['doctor_id'] ?? null,
                'prepared_by' => $this->user['user_id'] ?? null
            ]);

            // Update admission status
            $admissionDate = $this->db->fetchColumn("SELECT admission_date FROM ip_admissions WHERE admission_id = ?", [$input['admission_id']]);
            $dischargeDate = $input['discharge_date'] ?? date('Y-m-d H:i:s');
            $totalDays = ceil((strtotime($dischargeDate) - strtotime($admissionDate)) / 86400);

            $this->db->update('ip_admissions', [
                'admission_status' => 'Discharged',
                'discharge_date' => $dischargeDate,
                'discharge_type' => $input['discharge_type'] ?? 'Normal',
                'total_days' => $totalDays
            ], 'admission_id = ?', [$input['admission_id']]);

            // Release bed
            $bedId = $this->db->fetchColumn("SELECT bed_id FROM ip_admissions WHERE admission_id = ?", [$input['admission_id']]);
            if ($bedId) {
                $this->db->update('beds', ['bed_status' => 'Available'], 'bed_id = ?', [$bedId]);
                $this->db->update('bed_allocations', [
                    'released_date' => $dischargeDate,
                    'is_current' => 0,
                    'total_days' => $totalDays
                ], 'admission_id = ? AND is_current = 1', [$input['admission_id']]);
            }

            $this->db->commit();
            Response::success(['summary_id' => $id], 'Discharge summary created successfully');
        } catch (\Exception $e) {
            $this->db->rollBack();
            Response::error($e->getMessage(), 500);
        }
    }

    // ==================== IP BILLING ====================

    public function getBillingItems(int $admissionId): void
    {
        try {
            $sql = "SELECT * FROM ip_billing_items 
                    WHERE admission_id = ? 
                    ORDER BY item_date DESC, created_at DESC";
            $items = $this->db->fetchAll($sql, [$admissionId]);
            Response::success(['items' => $items]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function storeBillingItem(): void
    {
        $input = jsonInput();

        if (empty($input['admission_id']) || empty($input['item_name']) || empty($input['unit_price'])) {
            Response::error('Admission ID, item name, and unit price are required', 422);
            return;
        }

        try {
            $quantity = $input['quantity'] ?? 1;
            $unitPrice = $input['unit_price'];
            $discountPercent = $input['discount_percent'] ?? 0;
            $taxPercent = $input['tax_percent'] ?? 0;

            $subtotal = $quantity * $unitPrice;
            $discountAmount = ($subtotal * $discountPercent) / 100;
            $taxableAmount = $subtotal - $discountAmount;
            $taxAmount = ($taxableAmount * $taxPercent) / 100;
            $totalAmount = $taxableAmount + $taxAmount;

            $id = $this->db->insert('ip_billing_items', [
                'admission_id' => $input['admission_id'],
                'item_date' => $input['item_date'] ?? date('Y-m-d'),
                'item_type' => $input['item_type'],
                'item_name' => $input['item_name'],
                'item_code' => $input['item_code'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'department' => $input['department'] ?? null,
                'doctor_id' => $input['doctor_id'] ?? null,
                'notes' => $input['notes'] ?? null,
                'created_by' => $this->user['user_id'] ?? null
            ]);
            Response::success(['billing_item_id' => $id], 'Billing item added successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getIPStats(): void
    {
        try {
            $branchId = $_GET['branch_id'] ?? $this->user['branch_id'] ?? 0;
            $branchSql = (!empty($branchId) && $branchId != 0) ? " AND w.branch_id = " . (int) $branchId : "";

            // For general counts, join with wards to check branch if needed
            $activeAdmissionsSql = "SELECT COUNT(*) FROM ip_admissions a LEFT JOIN wards w ON a.ward_id = w.ward_id WHERE a.admission_status = 'Active'";
            if ($branchId != 0) {
                $activeAdmissionsSql .= " AND w.branch_id = " . (int) $branchId;
            }
            $currentlyAdmitted = $this->db->fetchColumn($activeAdmissionsSql);

            $pendingDischargesSql = "SELECT COUNT(*) FROM ip_admissions a LEFT JOIN wards w ON a.ward_id = w.ward_id WHERE a.admission_status = 'Active' AND a.estimated_discharge_date <= CURDATE()";
            if ($branchId != 0) {
                $pendingDischargesSql .= " AND w.branch_id = " . (int) $branchId;
            }
            $pendingDischarges = $this->db->fetchColumn($pendingDischargesSql);

            $totalBedsSql = "SELECT COUNT(*) FROM beds b JOIN wards w ON b.ward_id = w.ward_id WHERE b.is_active = 1";
            if ($branchId != 0) {
                $totalBedsSql .= " AND w.branch_id = " . (int) $branchId;
            }
            $totalBeds = $this->db->fetchColumn($totalBedsSql);

            $availableBedsSql = "SELECT COUNT(*) FROM beds b JOIN wards w ON b.ward_id = w.ward_id WHERE b.bed_status = 'Available' AND b.is_active = 1";
            if ($branchId != 0) {
                $availableBedsSql .= " AND w.branch_id = " . (int) $branchId;
            }
            $availableBeds = $this->db->fetchColumn($availableBedsSql);

            Response::success([
                'currently_admitted' => $currentlyAdmitted,
                'pending_discharges' => $pendingDischarges,
                'total_beds' => $totalBeds,
                'available_beds' => $availableBeds,
                'occupancy_rate' => $totalBeds > 0 ? round((($totalBeds - $availableBeds) / $totalBeds) * 100, 2) : 0
            ]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    // ==================== BRANCH INFO ====================

    public function getBranchInfo(): void
    {
        try {
            $branchId = $this->user['branch_id'] ?? 1;

            $sql = "SELECT code, name, is_main FROM branches WHERE branch_id = ? AND is_active = 1";
            $branch = $this->db->fetch($sql, [$branchId]);

            if ($branch) {
                // Extract branch code - if main branch and code is like "MB01", use "M"
                // Otherwise use the code as-is or first letter
                $branchCode = $branch['code'];

                // If it's main branch, simplify to "M"
                if ($branch['is_main'] == 1) {
                    $branchCode = 'M';
                } else {
                    // For non-main branches, use the code as-is or extract meaningful part
                    // Example: "TEST-479" -> "TEST" or just use first few chars
                    if (strlen($branchCode) > 4) {
                        $branchCode = substr($branchCode, 0, 4);
                    }
                }

                Response::success([
                    'branch_id' => $branchId,
                    'branch_code' => $branchCode,
                    'branch_name' => $branch['name'],
                    'is_main' => $branch['is_main']
                ]);
            } else {
                Response::error('Branch not found', 404);
            }
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}

