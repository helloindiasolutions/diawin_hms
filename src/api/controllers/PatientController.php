<?php
/**
 * Patient API Controller
 * Handles all patient-related API endpoints
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use System\Security;
use System\Logger;
use System\JWT;
use System\Session;

class PatientController
{
    private Database $db;
    private ?array $user;

    public function __construct()
    {
        $this->db = Database::getInstance();
        // Support both JWT and session auth
        $this->user = $this->getAuthUser();
    }

    /**
     * Get authenticated user from JWT token or session
     */
    private function getAuthUser(): ?array
    {
        // First try JWT from Authorization header
        $token = JWT::getTokenFromHeader();
        if ($token) {
            $payload = JWT::getPayload($token);
            if ($payload) {
                return $payload;
            }
        }

        // Fallback to session auth (for web requests)
        if (Session::isLoggedIn()) {
            return Session::getUser();
        }

        return null;
    }

    /**
     * List patients with pagination, search, and filters
     */
    public function index(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = min(100, max(10, (int) ($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $search = Security::sanitizeInput($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';
        $gender = $_GET['gender'] ?? '';
        $bloodGroup = $_GET['blood_group'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'created_at';
        $sortOrder = strtoupper($_GET['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        // Build query
        $where = ['1=1'];
        $params = [];

        // Branch filter (if user has branch)
        if (!empty($this->user['branch_id'])) {
            $where[] = '(p.branch_id = ? OR p.branch_id IS NULL)';
            $params[] = $this->user['branch_id'];
        }

        // Search
        if (!empty($search)) {
            $where[] = '(p.mrn LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR p.primary_mobile LIKE ? OR p.primary_email LIKE ?)';
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        // Status filter
        if ($status !== '') {
            $where[] = 'p.is_active = ?';
            $params[] = $status === 'active' ? 1 : 0;
        }

        // Gender filter
        if (!empty($gender)) {
            $where[] = 'p.gender = ?';
            $params[] = $gender;
        }

        // Blood group filter
        if (!empty($bloodGroup)) {
            $where[] = 'p.blood_group = ?';
            $params[] = $bloodGroup;
        }

        $whereClause = implode(' AND ', $where);

        // Allowed sort columns
        $allowedSorts = ['mrn', 'first_name', 'last_name', 'created_at', 'primary_mobile', 'gender', 'dob'];
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM patients p WHERE {$whereClause}";
        $total = (int) $this->db->fetch($countSql, $params)['total'];

        // Get patients with last visit info
        $sql = "SELECT p.patient_id, p.mrn, p.title, p.first_name, p.last_name, p.gender, 
                       p.dob, p.age, p.blood_group, p.primary_mobile, p.primary_email,
                       p.city, p.state, p.is_active, p.created_at,
                       (SELECT MAX(v.visit_start) FROM visits v WHERE v.patient_id = p.patient_id) as last_visit,
                       (SELECT COUNT(*) FROM visits v WHERE v.patient_id = p.patient_id) as total_visits
                FROM patients p
                WHERE {$whereClause}
                ORDER BY p.{$sortBy} {$sortOrder}
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $patients = $this->db->fetchAll($sql, $params);

        // Format response
        $formattedPatients = array_map(function ($p) {
            return [
                'patient_id' => (int) $p['patient_id'],
                'mrn' => $p['mrn'],
                'full_name' => trim(($p['title'] ? $p['title'] . ' ' : '') . $p['first_name'] . ' ' . ($p['last_name'] ?? '')),
                'first_name' => $p['first_name'],
                'last_name' => $p['last_name'],
                'title' => $p['title'],
                'gender' => $p['gender'],
                'dob' => $p['dob'],
                'age' => $this->calculateAge($p['dob'], $p['age']),
                'blood_group' => $p['blood_group'],
                'mobile' => $p['primary_mobile'],
                'email' => $p['primary_email'],
                'city' => $p['city'],
                'state' => $p['state'],
                'is_active' => (bool) $p['is_active'],
                'last_visit' => $p['last_visit'],
                'total_visits' => (int) $p['total_visits'],
                'created_at' => $p['created_at']
            ];
        }, $patients);

        Response::success([
            'patients' => $formattedPatients,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
    }

    /**
     * Get single patient details
     */
    public function show(int $id): void
    {
        $patient = $this->db->fetch(
            "SELECT p.*, 
                    (SELECT COUNT(*) FROM visits v WHERE v.patient_id = p.patient_id) as total_visits,
                    (SELECT MAX(v.visit_start) FROM visits v WHERE v.patient_id = p.patient_id) as last_visit,
                    pc.name as emergency_contact_name,
                    pc.relation as emergency_contact_relation,
                    pc.mobile as emergency_contact_mobile
             FROM patients p
             LEFT JOIN patient_contacts pc ON p.patient_id = pc.patient_id AND pc.is_primary = 1
             WHERE p.patient_id = ?",
            [$id]
        );

        if (!$patient) {
            Response::notFound('Patient not found');
            return;
        }

        // Get contacts
        $contacts = $this->db->fetchAll(
            "SELECT * FROM patient_contacts WHERE patient_id = ? ORDER BY is_primary DESC",
            [$id]
        );

        // Get identifiers
        $identifiers = $this->db->fetchAll(
            "SELECT * FROM patient_identifiers WHERE patient_id = ?",
            [$id]
        );

        // Get recent visits
        $recentVisits = $this->db->fetchAll(
            "SELECT v.visit_id, v.visit_start, v.visit_type, v.visit_status, v.notes,
                    pr.full_name as provider_name
             FROM visits v
             LEFT JOIN providers pr ON v.primary_provider_id = pr.provider_id
             WHERE v.patient_id = ?
             ORDER BY v.visit_start DESC
             LIMIT 5",
            [$id]
        );

        Response::success([
            'patient' => [
                'patient_id' => (int) $patient['patient_id'],
                'mrn' => $patient['mrn'],
                'title' => $patient['title'],
                'first_name' => $patient['first_name'],
                'last_name' => $patient['last_name'],
                'full_name' => trim(($patient['title'] ? $patient['title'] . ' ' : '') . $patient['first_name'] . ' ' . ($patient['last_name'] ?? '')),
                'gender' => $patient['gender'],
                'dob' => $patient['dob'],
                'age' => $this->calculateAge($patient['dob'], $patient['age']),
                'blood_group' => $patient['blood_group'],
                'mobile' => $patient['primary_mobile'],
                'email' => $patient['primary_email'],
                'address' => $patient['address'],
                'city' => $patient['city'],
                'state' => $patient['state'],
                'country' => $patient['country'],
                'pincode' => $patient['pincode'],
                'is_active' => (bool) $patient['is_active'],
                'total_visits' => (int) $patient['total_visits'],
                'last_visit' => $patient['last_visit'],
                'emergency_contact_name' => $patient['emergency_contact_name'],
                'emergency_contact_relation' => $patient['emergency_contact_relation'],
                'emergency_contact_mobile' => $patient['emergency_contact_mobile'],
                'created_at' => $patient['created_at'],
                'updated_at' => $patient['updated_at']
            ],
            'contacts' => $contacts,
            'identifiers' => $identifiers,
            'recent_visits' => $recentVisits
        ]);
    }

    /**
     * Create new patient
     */
    public function store(): void
    {
        $input = jsonInput();

        // Validate required fields
        $errors = $this->validatePatient($input);
        if (!empty($errors)) {
            Response::error('Validation failed', 422, $errors);
            return;
        }

        // Generate MRN
        $mrn = $this->generateMRN();

        try {
            // Determine family relationship
            $relation = $input['relation'] ?? 'self';
            // Convert empty string to null for integer column
            $relatedToPatientId = !empty($input['related_to_patient_id']) ? (int) $input['related_to_patient_id'] : null;
            $relationToMember = !empty($input['relation_to_member']) ? $input['relation_to_member'] : null;

            // If adding to existing family and relation indicates new patient is parent of current head
            // We need to update the family hierarchy
            $shouldBecomeHead = false;
            if ($relatedToPatientId && $relationToMember) {
                $parentRelations = ['Father', 'Mother', 'Grandfather', 'Grandmother'];
                if (in_array($relationToMember, $parentRelations)) {
                    // Check if related patient is current family head
                    $relatedPatient = $this->db->fetch(
                        "SELECT patient_id, relation FROM patients WHERE patient_id = ?",
                        [$relatedToPatientId]
                    );
                    if ($relatedPatient && ($relatedPatient['relation'] === 'self' || $relatedPatient['relation'] === 'head')) {
                        $shouldBecomeHead = true;
                        $relation = 'self'; // New patient becomes head
                    }
                }
            }

            $patientId = $this->db->insert('patients', [
                'branch_id' => $this->user['branch_id'] ?? null,
                'mrn' => $mrn,
                'title' => $input['title'] ?? null,
                'first_name' => Security::sanitizeInput($input['first_name']),
                'last_name' => Security::sanitizeInput($input['last_name'] ?? ''),
                'gender' => $input['gender'] ?? 'unknown',
                'dob' => !empty($input['dob']) ? $input['dob'] : null,
                'age' => !empty($input['age']) ? $input['age'] : null,
                'blood_group' => $input['blood_group'] ?? null,
                'primary_mobile' => $input['mobile'] ?? null,
                'primary_email' => $input['email'] ?? null,
                'address' => $input['address'] ?? null,
                'city' => $input['city'] ?? null,
                'state' => $input['state'] ?? null,
                'country' => $input['country'] ?? 'India',
                'pincode' => $input['pincode'] ?? null,
                'relation' => $relation,
                'related_to_patient_id' => $relatedToPatientId,
                'relation_to_member' => $relationToMember,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // If new patient should become family head, update the old head
            if ($shouldBecomeHead && $relatedToPatientId) {
                // Determine what the old head's new relation should be
                $newRelationForOldHead = $this->getInverseRelation($relationToMember);
                $this->db->update('patients', [
                    'relation' => $newRelationForOldHead,
                    'related_to_patient_id' => $patientId,
                    'relation_to_member' => $relationToMember
                ], 'patient_id = ?', [$relatedToPatientId]);

                Logger::info('Family head updated', [
                    'new_head_id' => $patientId,
                    'old_head_id' => $relatedToPatientId,
                    'relation' => $relationToMember
                ]);
            }

            // Add emergency contact if provided
            if (!empty($input['emergency_contact_name'])) {
                $this->db->insert('patient_contacts', [
                    'patient_id' => $patientId,
                    'name' => Security::sanitizeInput($input['emergency_contact_name']),
                    'relation' => $input['emergency_contact_relation'] ?? null,
                    'mobile' => $input['emergency_contact_mobile'] ?? null,
                    'is_primary' => 1
                ]);
            }

            // Add identifiers if provided
            if (!empty($input['identifiers']) && is_array($input['identifiers'])) {
                foreach ($input['identifiers'] as $identifier) {
                    if (!empty($identifier['type']) && !empty($identifier['value'])) {
                        $this->db->insert('patient_identifiers', [
                            'patient_id' => $patientId,
                            'identifier_type' => $identifier['type'],
                            'identifier_value' => $identifier['value']
                        ]);
                    }
                }
            }

            Logger::info('Patient created', ['patient_id' => $patientId, 'mrn' => $mrn, 'user_id' => $this->user['user_id'] ?? null]);

            Response::success([
                'patient_id' => $patientId,
                'mrn' => $mrn
            ], 'Patient created successfully', 201);

        } catch (\Exception $e) {
            Logger::error('Failed to create patient', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Response::error('Failed to create patient: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get inverse relation (e.g., Father -> Son/Daughter)
     */
    private function getInverseRelation(string $relation): string
    {
        $inverseMap = [
            'Father' => 'son',
            'Mother' => 'son',
            'Grandfather' => 'grandson',
            'Grandmother' => 'grandson',
            'Son' => 'father',
            'Daughter' => 'father',
            'Grandson' => 'grandfather',
            'Granddaughter' => 'grandfather',
            'Brother' => 'brother',
            'Sister' => 'sister',
            'Spouse' => 'spouse',
            'Husband' => 'wife',
            'Wife' => 'husband',
            'Uncle' => 'nephew',
            'Aunt' => 'nephew',
            'Nephew' => 'uncle',
            'Niece' => 'uncle',
            'Cousin' => 'cousin',
        ];

        return $inverseMap[$relation] ?? 'other';
    }

    /**
     * Update patient
     */
    public function update(int $id): void
    {
        $patient = $this->db->fetch("SELECT patient_id FROM patients WHERE patient_id = ?", [$id]);

        if (!$patient) {
            Response::notFound('Patient not found');
            return;
        }

        $input = jsonInput();

        // Check if this is just an emergency contact update
        $isContactOnlyUpdate = isset($input['emergency_contact_name']) && !isset($input['first_name']);

        if (!$isContactOnlyUpdate) {
            // Validate full patient update
            $errors = $this->validatePatient($input, true);
            if (!empty($errors)) {
                Response::error('Validation failed', 422, $errors);
                return;
            }
        }

        try {
            if (!$isContactOnlyUpdate) {
                $updateData = [
                    'title' => $input['title'] ?? null,
                    'first_name' => Security::sanitizeInput($input['first_name']),
                    'last_name' => Security::sanitizeInput($input['last_name'] ?? ''),
                    'gender' => $input['gender'] ?? 'unknown',
                    'dob' => !empty($input['dob']) ? $input['dob'] : null,
                    'age' => !empty($input['age']) ? $input['age'] : null,
                    'blood_group' => $input['blood_group'] ?? null,
                    'primary_mobile' => $input['mobile'] ?? null,
                    'primary_email' => $input['email'] ?? null,
                    'address' => $input['address'] ?? null,
                    'city' => $input['city'] ?? null,
                    'state' => $input['state'] ?? null,
                    'country' => $input['country'] ?? 'India',
                    'pincode' => $input['pincode'] ?? null,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->db->update('patients', $updateData, 'patient_id = ?', [$id]);
            }

            // Update emergency contact if provided
            if (isset($input['emergency_contact_name'])) {
                // Check if contact exists
                $existingContact = $this->db->fetch(
                    "SELECT contact_id FROM patient_contacts WHERE patient_id = ? AND is_primary = 1",
                    [$id]
                );

                $contactData = [
                    'name' => Security::sanitizeInput($input['emergency_contact_name']),
                    'relation' => $input['emergency_contact_relation'] ?? null,
                    'mobile' => $input['emergency_contact_mobile'] ?? null
                ];

                if ($existingContact) {
                    $this->db->update('patient_contacts', $contactData, 'contact_id = ?', [$existingContact['contact_id']]);
                } else {
                    $contactData['patient_id'] = $id;
                    $contactData['is_primary'] = 1;
                    $this->db->insert('patient_contacts', $contactData);
                }
            }

            Logger::info('Patient updated', ['patient_id' => $id, 'user_id' => $this->user['user_id'] ?? null]);

            Response::success(null, 'Patient updated successfully');

        } catch (\Exception $e) {
            Logger::error('Failed to update patient', ['error' => $e->getMessage()]);
            Response::error('Failed to update patient', 500);
        }
    }

    /**
     * Toggle patient status
     */
    public function toggleStatus(int $id): void
    {
        $patient = $this->db->fetch("SELECT patient_id, is_active FROM patients WHERE patient_id = ?", [$id]);

        if (!$patient) {
            Response::notFound('Patient not found');
            return;
        }

        $newStatus = $patient['is_active'] ? 0 : 1;
        $this->db->update('patients', ['is_active' => $newStatus], 'patient_id = ?', [$id]);

        Logger::info('Patient status toggled', ['patient_id' => $id, 'new_status' => $newStatus]);

        Response::success(['is_active' => (bool) $newStatus], 'Patient status updated');
    }

    /**
     * Delete patient (soft delete by deactivating)
     */
    public function destroy(int $id): void
    {
        $patient = $this->db->fetch("SELECT patient_id FROM patients WHERE patient_id = ?", [$id]);

        if (!$patient) {
            Response::notFound('Patient not found');
            return;
        }

        // Check for related records
        $visits = $this->db->fetch("SELECT COUNT(*) as cnt FROM visits WHERE patient_id = ?", [$id]);
        if ($visits['cnt'] > 0) {
            Response::error('Cannot delete patient with existing visits. Deactivate instead.', 400);
            return;
        }

        $this->db->update('patients', ['is_active' => 0], 'patient_id = ?', [$id]);

        Logger::info('Patient deactivated', ['patient_id' => $id]);

        Response::success(null, 'Patient deactivated successfully');
    }

    /**
     * Get patient statistics
     */
    public function stats(): void
    {
        $branchFilter = '';
        $params = [];

        if (!empty($this->user['branch_id'])) {
            $branchFilter = 'WHERE (branch_id = ? OR branch_id IS NULL)';
            $params[] = $this->user['branch_id'];
        }

        $total = $this->db->fetch("SELECT COUNT(*) as cnt FROM patients {$branchFilter}", $params)['cnt'];
        $active = $this->db->fetch("SELECT COUNT(*) as cnt FROM patients {$branchFilter}" . ($branchFilter ? ' AND' : ' WHERE') . " is_active = 1", $params)['cnt'];

        // New this month
        $monthStart = date('Y-m-01');
        $newThisMonth = $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM patients {$branchFilter}" . ($branchFilter ? ' AND' : ' WHERE') . " created_at >= ?",
            array_merge($params, [$monthStart])
        )['cnt'];

        // Gender distribution
        $genderDist = $this->db->fetchAll(
            "SELECT gender, COUNT(*) as count FROM patients {$branchFilter} GROUP BY gender",
            $params
        );

        Response::success([
            'total_patients' => (int) $total,
            'active_patients' => (int) $active,
            'inactive_patients' => (int) $total - (int) $active,
            'new_this_month' => (int) $newThisMonth,
            'gender_distribution' => $genderDist
        ]);
    }

    /**
     * Search patients (quick search for autocomplete)
     */
    public function search(): void
    {
        // PROFESSIONAL FIX: Release session lock immediately so multiple rapid search requests don't queue up
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $query = Security::sanitizeInput($_GET['q'] ?? '');

        if (strlen($query) < 2) {
            Response::success(['patients' => []]);
            return;
        }

        // Optimize: Use prefix search (%) for instant results with indexes
        $searchPattern = "{$query}%";

        if (is_numeric($query) && strlen($query) >= 3) {
            $sql = "SELECT patient_id, mrn, title, first_name, last_name, primary_mobile, gender, dob
                    FROM patients 
                    WHERE is_active = 1 
                    AND (primary_mobile LIKE ? OR mrn LIKE ?)
                    ORDER BY first_name LIMIT 15";
            $patients = $this->db->fetchAll($sql, [$searchPattern, $searchPattern]);
        } else {
            $sql = "SELECT patient_id, mrn, title, first_name, last_name, primary_mobile, gender, dob
                    FROM patients 
                    WHERE is_active = 1 
                    AND (first_name LIKE ? OR last_name LIKE ? OR mrn LIKE ? OR primary_mobile LIKE ?)
                    ORDER BY first_name LIMIT 15";
            $patients = $this->db->fetchAll($sql, [$searchPattern, $searchPattern, $searchPattern, $searchPattern]);
        }

        $formatted = array_map(function ($p) {
            return [
                'patient_id' => (int) $p['patient_id'],
                'mrn' => $p['mrn'],
                'full_name' => trim(($p['title'] ? $p['title'] . ' ' : '') . $p['first_name'] . ' ' . ($p['last_name'] ?? '')),
                'mobile' => $p['primary_mobile'],
                'gender' => $p['gender'],
                'age' => $this->calculateAge($p['dob'], null)
            ];
        }, $patients);

        Response::success(['patients' => $formatted]);
    }

    /**
     * Family lookup by mobile number
     * Returns existing patients/families with this mobile
     */
    public function familyLookup(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        $mobile = Security::sanitizeInput($_GET['mobile'] ?? '');

        // Clean mobile - remove spaces, dashes, country code
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        if (strlen($mobile) > 10) {
            $mobile = substr($mobile, -10); // Take last 10 digits
        }

        if (strlen($mobile) !== 10) {
            Response::error('Invalid mobile number', 400);
            return;
        }

        // Find all patients with this mobile number
        $patients = $this->db->fetchAll(
            "SELECT patient_id, mrn, title, first_name, last_name, gender, dob, age, 
                    primary_mobile, city, created_at, relation, related_to_patient_id, relation_to_member
             FROM patients 
             WHERE primary_mobile LIKE ? AND is_active = 1
             ORDER BY 
                CASE WHEN relation = 'self' OR relation = 'head' THEN 0 ELSE 1 END,
                COALESCE(TIMESTAMPDIFF(YEAR, dob, CURDATE()), age) DESC,
                created_at ASC",
            ['%' . $mobile]
        );

        if (empty($patients)) {
            // No existing patients - will create new family
            Response::success([
                'families' => [],
                'mobile' => $mobile,
                'message' => 'No existing patients found'
            ]);
            return;
        }

        // Group patients by family (using mobile as family identifier)
        $families = [];
        $familyId = 'FAM-' . substr(md5($mobile), 0, 8);

        // Build family tree structure
        $members = [];
        $headPatient = null;

        foreach ($patients as $p) {
            $age = $this->calculateAge($p['dob'], $p['age']);
            $isHead = ($p['relation'] === 'self' || $p['relation'] === 'head' || $p['patient_id'] === $patients[0]['patient_id']);

            if ($isHead && !$headPatient) {
                $headPatient = $p;
            }

            $members[] = [
                'patient_id' => (int) $p['patient_id'],
                'mrn' => $p['mrn'],
                'full_name' => trim(($p['title'] ? $p['title'] . ' ' : '') . $p['first_name'] . ' ' . ($p['last_name'] ?? '')),
                'gender' => $p['gender'],
                'age' => $age,
                'dob' => $p['dob'],
                'relation' => $isHead ? 'Head' : ucfirst($p['relation'] ?? 'Member'),
                'related_to_patient_id' => $p['related_to_patient_id'],
                'relation_to_member' => $p['relation_to_member'],
                'mobile' => $p['primary_mobile']
            ];
        }

        // Sort members: Head first, then by age (older first), siblings grouped together
        usort($members, function ($a, $b) {
            // Head always first
            if ($a['relation'] === 'Head')
                return -1;
            if ($b['relation'] === 'Head')
                return 1;

            // Then sort by age (older first)
            $ageA = $a['age'] ?? 0;
            $ageB = $b['age'] ?? 0;
            return $ageB - $ageA;
        });

        $families[] = [
            'family_id' => $familyId,
            'mobile' => $mobile,
            'members' => $members,
            'head_patient_id' => $headPatient ? (int) $headPatient['patient_id'] : null
        ];

        Response::success([
            'families' => $families,
            'mobile' => $mobile,
            'total_patients' => count($patients)
        ]);
    }

    /**
     * Search patient by emergency contact mobile
     * Used for auto-filling emergency contact when registering new patient
     */
    public function searchByEmergencyMobile(): void
    {
        $mobile = Security::sanitizeInput($_GET['mobile'] ?? '');

        // Clean mobile
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        if (strlen($mobile) > 10) {
            $mobile = substr($mobile, -10);
        }

        if (strlen($mobile) < 10) {
            Response::success(['patient' => null]);
            return;
        }

        // Search for patient with this mobile as primary mobile
        $patient = $this->db->fetch(
            "SELECT patient_id, mrn, title, first_name, last_name, gender, dob, age, 
                    primary_mobile, blood_group
             FROM patients 
             WHERE primary_mobile LIKE ? AND is_active = 1
             ORDER BY created_at DESC
             LIMIT 1",
            ['%' . $mobile]
        );

        if (!$patient) {
            Response::success(['patient' => null]);
            return;
        }

        Response::success([
            'patient' => [
                'patient_id' => (int) $patient['patient_id'],
                'mrn' => $patient['mrn'],
                'full_name' => trim(($patient['title'] ? $patient['title'] . ' ' : '') . $patient['first_name'] . ' ' . ($patient['last_name'] ?? '')),
                'first_name' => $patient['first_name'],
                'last_name' => $patient['last_name'],
                'gender' => $patient['gender'],
                'dob' => $patient['dob'],
                'age' => $this->calculateAge($patient['dob'], $patient['age']),
                'blood_group' => $patient['blood_group'],
                'mobile' => $patient['primary_mobile']
            ]
        ]);
    }

    /**
     * Validate patient data
     */
    private function validatePatient(array $input, bool $isUpdate = false): array
    {
        $errors = [];

        if (empty($input['first_name'])) {
            $errors['first_name'] = 'First name is required';
        } elseif (strlen($input['first_name']) < 2) {
            $errors['first_name'] = 'First name must be at least 2 characters';
        }

        if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (!empty($input['mobile']) && !preg_match('/^[0-9+\-\s]{10,15}$/', $input['mobile'])) {
            $errors['mobile'] = 'Invalid mobile number format';
        }

        if (!empty($input['dob'])) {
            $dob = strtotime($input['dob']);
            if ($dob === false || $dob > time()) {
                $errors['dob'] = 'Invalid date of birth';
            }
        }

        if (!empty($input['gender']) && !in_array($input['gender'], ['male', 'female', 'other', 'unknown'])) {
            $errors['gender'] = 'Invalid gender value';
        }

        return $errors;
    }

    /**
     * Generate unique MRN
     */
    private function generateMRN(): string
    {
        $prefix = 'MRN';
        $year = date('y');

        // Get last MRN for this year
        $last = $this->db->fetch(
            "SELECT mrn FROM patients WHERE mrn LIKE ? ORDER BY patient_id DESC LIMIT 1",
            [$prefix . $year . '%']
        );

        if ($last) {
            $lastNum = (int) substr($last['mrn'], strlen($prefix) + 2);
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        return $prefix . $year . str_pad((string) $newNum, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate age from DOB
     */
    private function calculateAge(?string $dob, ?int $storedAge): ?int
    {
        if ($dob) {
            $birthDate = new \DateTime($dob);
            $today = new \DateTime();
            return $birthDate->diff($today)->y;
        }
        return $storedAge;
    }

    /**
     * Get patient contacts (emergency contacts list)
     */
    public function contacts(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = min(100, max(10, (int) ($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $search = Security::sanitizeInput($_GET['search'] ?? '');
        $relation = $_GET['relation'] ?? '';

        // Build query - get patients with emergency contacts
        $where = ['1=1'];
        $params = [];

        // Branch filter
        if (!empty($this->user['branch_id'])) {
            $where[] = '(p.branch_id = ? OR p.branch_id IS NULL)';
            $params[] = $this->user['branch_id'];
        }

        // Only active patients
        $where[] = 'p.is_active = 1';

        // Search
        if (!empty($search)) {
            $where[] = '(p.mrn LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR pc.name LIKE ? OR pc.mobile LIKE ?)';
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        // Relation filter
        if (!empty($relation)) {
            $where[] = 'pc.relation = ?';
            $params[] = $relation;
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countSql = "SELECT COUNT(DISTINCT p.patient_id) as total 
                     FROM patients p 
                     LEFT JOIN patient_contacts pc ON p.patient_id = pc.patient_id AND pc.is_primary = 1
                     WHERE {$whereClause}";
        $total = (int) $this->db->fetch($countSql, $params)['total'];

        // Get contacts
        $sql = "SELECT p.patient_id, p.mrn, p.title, p.first_name, p.last_name, p.gender, p.city,
                       pc.name as emergency_contact_name, pc.mobile as emergency_contact_mobile, 
                       pc.relation as emergency_contact_relation
                FROM patients p
                LEFT JOIN patient_contacts pc ON p.patient_id = pc.patient_id AND pc.is_primary = 1
                WHERE {$whereClause}
                ORDER BY p.first_name ASC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $contacts = $this->db->fetchAll($sql, $params);

        // Format response
        $formattedContacts = array_map(function ($c) {
            return [
                'patient_id' => (int) $c['patient_id'],
                'mrn' => $c['mrn'],
                'patient_name' => trim(($c['title'] ? $c['title'] . ' ' : '') . $c['first_name'] . ' ' . ($c['last_name'] ?? '')),
                'gender' => $c['gender'],
                'city' => $c['city'],
                'emergency_contact_name' => $c['emergency_contact_name'],
                'emergency_contact_mobile' => $c['emergency_contact_mobile'],
                'emergency_contact_relation' => $c['emergency_contact_relation']
            ];
        }, $contacts);

        Response::success([
            'contacts' => $formattedContacts,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
    }

    /**
     * Get full patient details with all related data
     */
    public function fullDetails(int $id): void
    {
        $patient = $this->db->fetch(
            "SELECT p.*, 
                    pc.name as emergency_contact_name,
                    pc.relation as emergency_contact_relation,
                    pc.mobile as emergency_contact_mobile,
                    b.name as branch_name
             FROM patients p
             LEFT JOIN patient_contacts pc ON p.patient_id = pc.patient_id AND pc.is_primary = 1
             LEFT JOIN branches b ON p.branch_id = b.branch_id
             WHERE p.patient_id = ?",
            [$id]
        );

        if (!$patient) {
            Response::notFound('Patient not found');
            return;
        }

        // Get stats
        $stats = $this->getPatientStats($id);

        // Get recent visits with provider and branch info
        $recentVisits = $this->db->fetchAll(
            "SELECT v.visit_id, v.visit_type, v.visit_start, v.visit_end, v.visit_status, v.notes,
                    pr.full_name as provider_name, b.name as branch_name,
                    aq.token_no
             FROM visits v
             LEFT JOIN providers pr ON v.primary_provider_id = pr.provider_id
             LEFT JOIN branches b ON v.branch_id = b.branch_id
             LEFT JOIN appointment_queue aq ON aq.appointment_id = v.appointment_id
             WHERE v.patient_id = ?
             ORDER BY v.visit_start DESC
             LIMIT 10",
            [$id]
        );

        // Get branch visit summary
        $branchVisits = $this->db->fetchAll(
            "SELECT b.name as branch_name, b.branch_id,
                    COUNT(v.visit_id) as visit_count,
                    MAX(v.visit_start) as last_visit,
                    COALESCE(SUM(i.total_amount), 0) as total_billed
             FROM visits v
             LEFT JOIN branches b ON v.branch_id = b.branch_id
             LEFT JOIN invoices i ON i.visit_id = v.visit_id
             WHERE v.patient_id = ?
             GROUP BY b.branch_id, b.name
             ORDER BY visit_count DESC",
            [$id]
        );

        Response::success([
            'patient' => [
                'patient_id' => (int) $patient['patient_id'],
                'mrn' => $patient['mrn'],
                'title' => $patient['title'],
                'first_name' => $patient['first_name'],
                'last_name' => $patient['last_name'],
                'full_name' => trim(($patient['title'] ? $patient['title'] . ' ' : '') . $patient['first_name'] . ' ' . ($patient['last_name'] ?? '')),
                'gender' => $patient['gender'],
                'dob' => $patient['dob'],
                'age' => $this->calculateAge($patient['dob'], $patient['age']),
                'blood_group' => $patient['blood_group'],
                'mobile' => $patient['primary_mobile'],
                'email' => $patient['primary_email'],
                'address' => $patient['address'],
                'city' => $patient['city'],
                'state' => $patient['state'],
                'country' => $patient['country'],
                'pincode' => $patient['pincode'],
                'is_active' => (bool) $patient['is_active'],
                'branch_name' => $patient['branch_name'],
                'emergency_contact_name' => $patient['emergency_contact_name'],
                'emergency_contact_relation' => $patient['emergency_contact_relation'],
                'emergency_contact_mobile' => $patient['emergency_contact_mobile'],
                'created_at' => $patient['created_at']
            ],
            'stats' => $stats,
            'recent_visits' => $recentVisits,
            'branch_visits' => $branchVisits
        ]);
    }

    /**
     * Get brief medical summary for quick appointment
     */
    public function medicalSummary(int $id): void
    {
        // 1. Last provider visited
        $lastVisit = $this->db->fetch(
            "SELECT v.primary_provider_id as provider_id, p.full_name as provider_name, 
                    p.specialization, v.visit_start as last_date
             FROM visits v
             JOIN providers p ON v.primary_provider_id = p.provider_id
             WHERE v.patient_id = ? AND v.visit_status = 'completed'
             ORDER BY v.visit_start DESC LIMIT 1",
            [$id]
        );

        // 2. Medical history (latest vitals) - use captured_at instead of created_at
        $latestVitals = $this->db->fetch(
            "SELECT weight_kg as weight, bp_systolic, bp_diastolic, pulse_per_min as heart_rate, temperature_c as temp, captured_at as created_at
             FROM vitals 
             WHERE visit_id IN (SELECT visit_id FROM visits WHERE patient_id = ?)
             ORDER BY captured_at DESC LIMIT 1",
            [$id]
        );

        // Fetch small selection of history items from rounds if available
        $diagnoses = $this->db->fetchAll(
            "SELECT diagnosis as diagnosis_name, created_at as date
             FROM rounds 
             WHERE patient_id = ? AND diagnosis IS NOT NULL AND diagnosis != ''
             ORDER BY created_at DESC LIMIT 3",
            [$id]
        );

        // Fetch recent prescriptions as well
        $prescriptions = $this->db->fetchAll(
            "SELECT p.prescribed_at as date, GROUP_CONCAT(prod.name SEPARATOR ', ') as meds
             FROM prescriptions p
             JOIN prescription_items pi ON p.prescription_id = pi.prescription_id
             JOIN products prod ON pi.product_id = prod.product_id
             WHERE p.visit_id IN (SELECT visit_id FROM visits WHERE patient_id = ?)
             GROUP BY p.prescription_id
             ORDER BY p.prescribed_at DESC LIMIT 2",
            [$id]
        );

        Response::success([
            'last_provider' => $lastVisit,
            'summary' => [
                'vitals' => $latestVitals,
                'diagnoses' => $diagnoses,
                'prescriptions' => $prescriptions,
                'allergies' => [] // No dedicated allergies table found yet
            ]
        ]);
    }

    private function getPatientStats(int $patientId): array
    {
        // Total visits
        $visitStats = $this->db->fetch(
            "SELECT COUNT(*) as total_visits, MAX(visit_start) as last_visit
             FROM visits WHERE patient_id = ?",
            [$patientId]
        );

        // Billing stats for this patient
        $billingStats = $this->db->fetch(
            "SELECT COALESCE(SUM(total_amount), 0) as total_billed,
                    COALESCE(SUM(paid_amount), 0) as total_paid
             FROM invoices WHERE patient_id = ? AND status != 'cancelled'",
            [$patientId]
        );

        // Get family members by mobile
        $patient = $this->db->fetch("SELECT primary_mobile FROM patients WHERE patient_id = ?", [$patientId]);
        $familyBilling = ['total_billed' => 0, 'total_paid' => 0];

        if ($patient && $patient['primary_mobile']) {
            $familyBilling = $this->db->fetch(
                "SELECT COALESCE(SUM(i.total_amount), 0) as total_billed,
                        COALESCE(SUM(i.paid_amount), 0) as total_paid
                 FROM invoices i
                 JOIN patients p ON i.patient_id = p.patient_id
                 WHERE p.primary_mobile LIKE ? AND i.status != 'cancelled'",
                ['%' . substr($patient['primary_mobile'], -10)]
            ) ?: ['total_billed' => 0, 'total_paid' => 0];
        }

        return [
            'total_visits' => (int) ($visitStats['total_visits'] ?? 0),
            'last_visit' => $visitStats['last_visit'] ?? null,
            'total_billed' => (float) ($billingStats['total_billed'] ?? 0),
            'total_paid' => (float) ($billingStats['total_paid'] ?? 0),
            'balance_due' => (float) (($billingStats['total_billed'] ?? 0) - ($billingStats['total_paid'] ?? 0)),
            'family_total_billed' => (float) ($familyBilling['total_billed'] ?? 0),
            'family_total_paid' => (float) ($familyBilling['total_paid'] ?? 0)
        ];
    }

    /**
     * Get patient visits
     */
    public function visits(int $id): void
    {
        try {
            // Try full query with all joins
            $visits = $this->db->fetchAll(
                "SELECT v.visit_id, v.branch_id, v.patient_id, v.visit_type, v.visit_start, v.visit_end, 
                        v.visit_status, v.primary_provider_id, v.created_at,
                        pr.full_name as provider_name, b.name as branch_name,
                        aq.token_no
                 FROM visits v
                 LEFT JOIN providers pr ON v.primary_provider_id = pr.provider_id
                 LEFT JOIN branches b ON v.branch_id = b.branch_id
                 LEFT JOIN appointment_queue aq ON v.appointment_id = aq.appointment_id
                 WHERE v.patient_id = ?
                 ORDER BY v.visit_start DESC",
                [$id]
            );
        } catch (\Exception $e) {
            // Fallback to simplest possible query
            try {
                $visits = $this->db->fetchAll(
                    "SELECT * FROM visits WHERE patient_id = ? ORDER BY visit_start DESC",
                    [$id]
                );
            } catch (\Exception $e2) {
                Logger::error('Failed to fetch visits', ['error' => $e2->getMessage()]);
                Response::success(['visits' => []]);
                return;
            }
        }

        $formatted = array_map(function ($v) {
            return [
                'visit_id' => (int) $v['visit_id'],
                'visit_type' => $v['visit_type'] ?? 'OP',
                'visit_start' => $v['visit_start'] ?? null,
                'visit_end' => $v['visit_end'] ?? null,
                'visit_status' => $v['visit_status'] ?? 'open',
                'notes' => $v['notes'] ?? null,
                'provider_name' => $v['provider_name'] ?? null,
                'branch_name' => $v['branch_name'] ?? null,
                'token_no' => $v['token_no'] ?? null
            ];
        }, $visits);

        Response::success(['visits' => $formatted]);
    }

    /**
     * Get patient prescriptions
     */
    public function prescriptions(int $id): void
    {
        $prescriptions = $this->db->fetchAll(
            "SELECT p.*, 
                    COALESCE(pr.full_name, u.full_name) as provider_name
             FROM prescriptions p
             LEFT JOIN providers pr ON p.prescribed_by = pr.provider_id
             LEFT JOIN users u ON p.prescribed_by = u.user_id
             WHERE p.visit_id IN (SELECT visit_id FROM visits WHERE patient_id = ?)
             ORDER BY p.prescribed_at DESC",
            [$id]
        );

        foreach ($prescriptions as &$presc) {
            $presc['items'] = $this->db->fetchAll(
                "SELECT pi.*, prod.name as product_name
                 FROM prescription_items pi
                 LEFT JOIN products prod ON pi.product_id = prod.product_id
                 WHERE pi.prescription_id = ?",
                [$presc['prescription_id']]
            );
        }

        Response::success(['prescriptions' => $prescriptions]);
    }

    /**
     * Get patient billing
     */
    public function billing(int $id): void
    {
        $invoices = $this->db->fetchAll(
            "SELECT i.*, b.name as branch_name
             FROM invoices i
             LEFT JOIN branches b ON i.branch_id = b.branch_id
             WHERE i.patient_id = ?
             ORDER BY i.created_at DESC",
            [$id]
        );

        $summary = $this->db->fetch(
            "SELECT COALESCE(SUM(total_amount), 0) as total_invoiced,
                    COALESCE(SUM(paid_amount), 0) as total_paid
             FROM invoices WHERE patient_id = ? AND status != 'cancelled'",
            [$id]
        );

        Response::success([
            'invoices' => $invoices,
            'summary' => [
                'total_invoiced' => (float) ($summary['total_invoiced'] ?? 0),
                'total_paid' => (float) ($summary['total_paid'] ?? 0),
                'balance_due' => (float) (($summary['total_invoiced'] ?? 0) - ($summary['total_paid'] ?? 0))
            ]
        ]);
    }

    /**
     * Get patient family members
     */
    public function family(int $id): void
    {
        $patient = $this->db->fetch("SELECT primary_mobile FROM patients WHERE patient_id = ?", [$id]);

        if (!$patient || !$patient['primary_mobile']) {
            Response::success(['family_members' => []]);
            return;
        }

        $mobile = substr($patient['primary_mobile'], -10);

        $members = $this->db->fetchAll(
            "SELECT p.patient_id, p.mrn, p.title, p.first_name, p.last_name, p.gender, p.dob, p.age,
                    (SELECT COUNT(*) FROM visits v WHERE v.patient_id = p.patient_id) as total_visits,
                    (SELECT COALESCE(SUM(total_amount), 0) FROM invoices i WHERE i.patient_id = p.patient_id AND i.status != 'cancelled') as total_billed
             FROM patients p
             WHERE p.primary_mobile LIKE ? AND p.is_active = 1
             ORDER BY p.created_at ASC",
            ['%' . $mobile]
        );

        $formatted = array_map(function ($m) use ($id) {
            return [
                'patient_id' => (int) $m['patient_id'],
                'mrn' => $m['mrn'],
                'full_name' => trim(($m['title'] ? $m['title'] . ' ' : '') . $m['first_name'] . ' ' . ($m['last_name'] ?? '')),
                'gender' => $m['gender'],
                'age' => $this->calculateAge($m['dob'], $m['age']),
                'relation' => $m['patient_id'] == $id ? 'Self' : 'Family Member',
                'total_visits' => (int) $m['total_visits'],
                'total_billed' => (float) $m['total_billed']
            ];
        }, $members);

        Response::success(['family_members' => $formatted]);
    }

    /**
     * Get patient timeline
     */
    public function timeline(int $id): void
    {
        $events = [];

        // Visits
        $visits = $this->db->fetchAll(
            "SELECT 'visit' as type, visit_start as date, 
                    CONCAT(visit_type, ' Visit') as title,
                    notes as description
             FROM visits WHERE patient_id = ?",
            [$id]
        );
        $events = array_merge($events, $visits);

        // Payments
        $payments = $this->db->fetchAll(
            "SELECT 'payment' as type, pay.payment_date as date,
                    CONCAT('Payment - ', pay.payment_mode) as title,
                    CONCAT('₹', pay.amount, ' received') as description
             FROM payments pay
             JOIN invoices i ON pay.invoice_id = i.invoice_id
             WHERE i.patient_id = ?",
            [$id]
        );
        $events = array_merge($events, $payments);

        // Prescriptions
        $prescriptions = $this->db->fetchAll(
            "SELECT 'prescription' as type, p.prescribed_at as date,
                    'Prescription' as title,
                    CONCAT('Prescribed by ', pr.full_name) as description
             FROM prescriptions p
             LEFT JOIN providers pr ON p.prescribed_by = pr.provider_id
             WHERE p.visit_id IN (SELECT visit_id FROM visits WHERE patient_id = ?)",
            [$id]
        );
        $events = array_merge($events, $prescriptions);

        // Sort by date descending
        usort($events, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        Response::success(['events' => array_slice($events, 0, 50)]);
    }
}
