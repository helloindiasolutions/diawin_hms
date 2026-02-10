<?php
/**
 * Appointment API Controller
 * Handles all appointment-related API endpoints
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use System\Security;
use System\Logger;
use System\JWT;
use System\Session;
use App\Api\Services\WhatsAppService;
use App\Controllers\PrintController;

class AppointmentController
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
     * List appointments with pagination and filters
     */
    public function index(): void
    {
        $startDate = $_GET['start'] ?? $_GET['date'] ?? date('Y-m-d');
        $endDate = $_GET['end'] ?? '';
        $providerId = $_GET['provider_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $search = Security::sanitizeInput($_GET['search'] ?? '');

        $params = [];
        $where = [];

        if (strpos($startDate, ' to ') !== false) {
            $dates = explode(' to ', $startDate);
            $where[] = "DATE(a.scheduled_at) BETWEEN ? AND ?";
            $params[] = $dates[0];
            $params[] = $dates[1];
        } elseif (!empty($endDate)) {
            // Calendar range (usually End is exclusive, but we'll use < for End to be safe)
            $where[] = "a.scheduled_at >= ? AND a.scheduled_at < ?";
            $params[] = $startDate;
            $params[] = $endDate;
        } else {
            $where[] = "DATE(a.scheduled_at) = ?";
            $params[] = $startDate;
        }

        if (!empty($this->user['branch_id'])) {
            $where[] = "a.branch_id = ?";
            $params[] = $this->user['branch_id'];
        }

        if (!empty($providerId)) {
            $where[] = "a.provider_id = ?";
            $params[] = $providerId;
        }

        if (!empty($status)) {
            $where[] = "a.status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $where[] = "(p.first_name LIKE ? OR p.last_name LIKE ? OR p.mrn LIKE ? OR p.primary_mobile LIKE ?)";
            $term = "%$search%";
            $params = array_merge($params, [$term, $term, $term, $term]);
        }

        $whereSql = implode(" AND ", $where);

        $sql = "SELECT a.*, p.first_name, p.last_name, p.mrn, p.primary_mobile, p.gender, p.dob,
                       pr.full_name as provider_name, pr.specialization,
                       q.token_no, q.status as queue_status
                FROM appointments a
                JOIN patients p ON a.patient_id = p.patient_id
                LEFT JOIN providers pr ON a.provider_id = pr.provider_id
                LEFT JOIN appointment_queue q ON a.appointment_id = q.appointment_id
                WHERE {$whereSql}
                ORDER BY a.scheduled_at DESC";

        $appointments = $this->db->fetchAll($sql, $params);

        $formatted = array_map(function ($a) {
            return [
                'appointment_id' => (int) $a['appointment_id'],
                'appointment_no' => $a['appointment_no'],
                'scheduled_at' => $a['scheduled_at'],
                'duration' => (int) $a['duration_minutes'],
                'status' => $a['status'],
                'source' => $a['source'],
                'patient' => [
                    'patient_id' => (int) $a['patient_id'],
                    'mrn' => $a['mrn'],
                    'full_name' => trim($a['first_name'] . ' ' . ($a['last_name'] ?? '')),
                    'mobile' => $a['primary_mobile'],
                    'gender' => $a['gender'],
                    'age' => $this->calculateAge($a['dob'])
                ],
                'provider' => [
                    'provider_id' => (int) $a['provider_id'],
                    'name' => $a['provider_name'],
                    'specialization' => $a['specialization']
                ],
                'queue' => [
                    'token_no' => $a['token_no'],
                    'status' => $a['queue_status']
                ]
            ];
        }, $appointments);

        Response::success(['appointments' => $formatted]);
    }

    /**
     * Get statistics for a specific date
     */
    public function stats(): void
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        $branchId = $this->user['branch_id'] ?? null;

        $params = [];
        $whereDate = "";

        if (strpos($date, ' to ') !== false) {
            $dates = explode(' to ', $date);
            $whereDate = "DATE(scheduled_at) BETWEEN ? AND ?";
            $params[] = $dates[0];
            $params[] = $dates[1];
        } else {
            $whereDate = "DATE(scheduled_at) = ?";
            $params[] = $date;
        }

        $branchSql = "";
        if ($branchId) {
            $branchSql = " AND branch_id = ?";
            $params[] = $branchId;
        }

        $allStats = $this->db->fetchAll(
            "SELECT status, COUNT(*) as cnt FROM appointments WHERE $whereDate $branchSql GROUP BY status",
            $params
        );

        $stats = [
            'total' => 0,
            'scheduled' => 0,
            'checked_in' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'no_show' => 0
        ];

        foreach ($allStats as $s) {
            $key = str_replace('-', '_', $s['status']);
            if (isset($stats[$key])) {
                $stats[$key] = (int) $s['cnt'];
            }
            $stats['total'] += (int) $s['cnt'];
        }

        Response::success($stats);
    }

    /**
     * Get active providers
     */
    public function providers(): void
    {
        $branchId = $this->user['branch_id'] ?? null;
        $sql = "SELECT provider_id, full_name, specialization FROM providers WHERE is_active = 1";
        $params = [];

        if ($branchId) {
            $sql .= " AND (branch_id = ? OR branch_id IS NULL)";
            $params[] = $branchId;
        }

        $providers = $this->db->fetchAll($sql, $params);
        Response::success(['providers' => $providers]);
    }

    /**
     * Create appointment
     */
    public function store(): void
    {
        $input = jsonInput();

        if (empty($input['patient_id']) || empty($input['scheduled_at'])) {
            Response::error('Patient and Schedule time are required', 422);
            return;
        }

        try {
            $this->db->beginTransaction();

            $aptId = $this->db->insert('appointments', [
                'branch_id' => $this->user['branch_id'] ?? null,
                'patient_id' => $input['patient_id'],
                'provider_id' => $input['provider_id'] ?? null,
                'appointment_no' => 'APT-' . date('Ymd') . '-' . rand(1000, 9999),
                'scheduled_at' => $input['scheduled_at'],
                'duration_minutes' => $input['duration'] ?? 15,
                'status' => 'scheduled',
                'source' => $input['source'] ?? 'in-person',
                'created_by' => $this->user['user_id'] ?? null
            ]);

            // For walk-in visits, automatically create a clinical visit record so it shows in OPD
            if (($input['source'] ?? 'in-person') === 'in-person') {
                $this->db->insert('visits', [
                    'branch_id' => $this->user['branch_id'] ?? null,
                    'patient_id' => $input['patient_id'],
                    'appointment_id' => $aptId,
                    'visit_type' => 'OP',
                    'visit_start' => $input['scheduled_at'],
                    'visit_status' => 'open',
                    'primary_provider_id' => $input['provider_id'] ?? null,
                    'created_by' => $this->user['user_id'] ?? null
                ]);
            }

            $this->db->commit();

            // Send WhatsApp Confirmation
            try {
                $patient = $this->db->fetch("SELECT primary_mobile, first_name, last_name, title FROM patients WHERE patient_id = ?", [$input['patient_id']]);
                if ($patient && !empty($patient['primary_mobile'])) {
                    $wa = new WhatsAppService();
                    $scheduledTime = date('d-M-Y h:i A', strtotime($input['scheduled_at']));
                    $ptName = trim(($patient['title'] ? $patient['title'] . ' ' : '') . $patient['first_name'] . ' ' . $patient['last_name']);

                    // Professional Template
                    $msg = "👋 Greetings from Diawin HMS\n\nDear $ptName,\n\nYour appointment has been successfully booked.\n\n📅 Date: $scheduledTime\n🎫 Appt ID: #$aptId\n\nPlease arrive 10 minutes early. Thank you for choosing us.";

                    $wa->send($patient['primary_mobile'], $msg);
                }
            } catch (\Exception $e) {
                error_log("WA Error: " . $e->getMessage());
            }

            Response::success(['appointment_id' => $aptId], 'Appointment booked successfully', 201);
        } catch (\Exception $e) {
            if ($this->db->inTransaction())
                $this->db->rollback();
            Response::error('Failed to book appointment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update appointment status
     */
    public function updateStatus(int $id): void
    {
        $input = jsonInput();
        $status = $input['status'] ?? '';

        // ... validation ...
        if (!in_array($status, ['scheduled', 'checked-in', 'completed', 'cancelled', 'no-show'])) {
            Response::error('Invalid status', 422);
            return;
        }

        try {
            $this->db->beginTransaction();

            $this->db->update('appointments', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')], 'appointment_id = ?', [$id]);

            // If checked-in, add to queue if not exists
            if ($status === 'checked-in') {
                $exists = $this->db->fetch("SELECT queue_id, token_no FROM appointment_queue WHERE appointment_id = ?", [$id]);
                $tokenNo = null;

                if (!$exists) {
                    $apt = $this->db->fetch("SELECT * FROM appointments WHERE appointment_id = ?", [$id]);
                    // Generate Token No
                    $lastToken = $this->db->fetch("SELECT token_no FROM appointment_queue WHERE branch_id = ? AND DATE(created_at) = CURDATE() ORDER BY queue_id DESC LIMIT 1", [$apt['branch_id']]);
                    $newToken = $lastToken ? ((int) $lastToken['token_no'] + 1) : 1;
                    $tokenNo = str_pad((string) $newToken, 3, '0', STR_PAD_LEFT);

                    $this->db->insert('appointment_queue', [
                        'appointment_id' => $id,
                        'branch_id' => $apt['branch_id'],
                        'patient_id' => $apt['patient_id'],
                        'token_no' => $tokenNo,
                        'status' => 'waiting'
                    ]);

                    // Also create a sequence in the visits table so it shows in OPD list
                    // Check if visit already exists for this appointment
                    $visitExists = $this->db->fetch("SELECT visit_id FROM visits WHERE appointment_id = ?", [$id]);
                    if (!$visitExists) {
                        $this->db->insert('visits', [
                            'branch_id' => $apt['branch_id'],
                            'patient_id' => $apt['patient_id'],
                            'appointment_id' => $id,
                            'visit_type' => 'OP',
                            'visit_start' => date('Y-m-d H:i:s'),
                            'visit_status' => 'open',
                            'primary_provider_id' => $apt['provider_id'] ?? null,
                            'created_by' => $this->user['user_id'] ?? null
                        ]);
                    }
                } else {
                    $tokenNo = $exists['token_no'];
                }

                // Send Token Notification via WhatsApp
                try {
                    $apt = $this->db->fetch("SELECT patient_id, provider_id FROM appointments WHERE appointment_id = ?", [$id]);
                    $patient = $this->db->fetch("SELECT primary_mobile, first_name, last_name, title FROM patients WHERE patient_id = ?", [$apt['patient_id']]);
                    $doctor = $this->db->fetch("SELECT full_name FROM providers WHERE provider_id = ?", [$apt['provider_id']]);

                    // Fetch Current Token being served by this doctor
                    $currentToken = $this->db->fetchColumn(
                        "SELECT q.token_no FROM appointment_queue q
                         JOIN appointments a ON q.appointment_id = a.appointment_id
                         WHERE a.provider_id = ? AND DATE(q.created_at) = CURDATE() AND q.status IN ('called', 'in-progress')
                         ORDER BY q.created_at DESC LIMIT 1",
                        [$apt['provider_id']]
                    ) ?: 'N/A';

                    if ($patient && !empty($patient['primary_mobile'])) {
                        $wa = new WhatsAppService();
                        $ptName = trim(($patient['title'] ? $patient['title'] . ' ' : '') . $patient['first_name']);
                        $docName = $doctor['full_name'] ?? 'Doctor';

                        $msg = "👋 *Welcome to Diawin HMS*\n\nDear $ptName,\n\nYour arrival is confirmed.\n\n🔢 *Your Token: $tokenNo*\n👨‍⚕️ Consulting: $docName\n📢 *Currently Serving: $currentToken*\n\nPlease wait in the reception. We will call you shortly.";

                        $wa->send($patient['primary_mobile'], $msg);
                    }
                } catch (\Exception $ex) {
                    error_log("WA Token Error: " . $ex->getMessage());
                }

            } elseif (in_array($status, ['cancelled', 'completed'])) {
                // ... rest of logic ...
                // If cancelled or completed, mark queue entry likewise
                $this->db->update('appointment_queue', [
                    'status' => $status
                ], 'appointment_id = ? AND status NOT IN ("completed", "cancelled")', [$id]);
            }

            $this->db->commit();
            Response::success(null, 'Status updated successfully');
        } catch (\Exception $e) {
            if ($this->db->inTransaction())
                $this->db->rollback();
            Response::error('Update failed: ' . $e->getMessage(), 500);
        }
    }

    // ... callPatient ...
    /**
     * Call patient to cabin
     */
    public function callPatient(): void
    {
        $input = jsonInput();
        $id = $input['queue_id'] ?? null;

        if (!$id) {
            Response::error('Queue ID required', 422);
            return;
        }

        try {
            $this->db->update('appointment_queue', [
                'status' => 'called',
                'called_at' => date('Y-m-d H:i:s')
            ], 'queue_id = ?', [$id]);

            Response::success(null, 'Patient called');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    // ... startSession ...
    public function startSession(): void
    {
        $input = jsonInput();
        $id = $input['queue_id'] ?? null;

        if (!$id) {
            Response::error('Queue ID required', 422);
            return;
        }

        try {
            $this->db->update('appointment_queue', [
                'status' => 'in-progress'
            ], 'queue_id = ?', [$id]);

            Response::success(null, 'Session started');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Complete patient visit
     */
    public function completePatient(): void
    {
        $input = jsonInput();
        $id = $input['queue_id'] ?? null;

        if (!$id) {
            Response::error('Queue ID required', 422);
            return;
        }

        try {
            $q = $this->db->fetch("SELECT appointment_id FROM appointment_queue WHERE queue_id = ?", [$id]);

            $this->db->beginTransaction();

            $this->db->update('appointment_queue', [
                'status' => 'completed'
            ], 'queue_id = ?', [$id]);

            if ($q) {
                $this->db->update('appointments', ['status' => 'completed'], 'appointment_id = ?', [$q['appointment_id']]);

                // Also close the clinical visit
                $this->db->update('visits', [
                    'visit_status' => 'closed',
                    'visit_end' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'appointment_id = ? AND visit_status = "open"', [$q['appointment_id']]);
            }

            $this->db->commit();

            // Send Prescription via WhatsApp
            try {
                error_log("=== Starting Prescription WhatsApp Send ===");

                // Get Appointment and Visit details
                $apt = $this->db->fetch("SELECT patient_id, appointment_id FROM appointments WHERE appointment_id = (SELECT appointment_id FROM appointment_queue WHERE queue_id = ?)", [$id]);
                error_log("Appointment data: " . json_encode($apt));

                if ($apt) {
                    $visitData = $this->db->fetch("SELECT visit_id, follow_up_date FROM visits WHERE appointment_id = ? ORDER BY visit_id ASC LIMIT 1", [$apt['appointment_id']]);
                    error_log("Visit data: " . json_encode($visitData));

                    $patient = $this->db->fetch("SELECT primary_mobile, first_name, last_name, title FROM patients WHERE patient_id = ?", [$apt['patient_id']]);
                    error_log("Patient data: " . json_encode($patient));

                    if ($visitData && $patient && !empty($patient['primary_mobile'])) {
                        $wa = new WhatsAppService();
                        $ptName = trim(($patient['title'] ? $patient['title'] . ' ' : '') . $patient['first_name']);

                        $followUp = !empty($visitData['follow_up_date']) ? "\n\n📅 *Next Sitting Date:* " . date('d-M-Y', strtotime($visitData['follow_up_date'])) : "";
                        $msg = "👋 Hello $ptName,\n\nThank you for visiting Diawin HMS. Please find your digital prescription attached.$followUp\n\nTake care & Get well soon! 🌿";

                        // NEW: Generate PDF locally and send as binary attachment
                        error_log("Generating PDF for visit_id: " . $visitData['visit_id']);
                        $pc = new \App\Controllers\PrintController();
                        $pdfContent = $pc->getCaseSheetPdfContent((int) $visitData['visit_id']);
                        error_log("PDF Content generated: " . ($pdfContent ? "YES (" . strlen($pdfContent) . " bytes)" : "NO"));

                        if ($pdfContent) {
                            $tempFile = sys_get_temp_dir() . '/rx_' . $visitData['visit_id'] . '_' . time() . '.pdf';
                            file_put_contents($tempFile, $pdfContent);
                            error_log("Temp file created: " . $tempFile . " (exists: " . (file_exists($tempFile) ? 'YES' : 'NO') . ")");

                            $result = $wa->sendFile($patient['primary_mobile'], $tempFile, "Prescription-" . $visitData['visit_id'] . ".pdf", $msg);
                            error_log("WhatsApp sendFile result: " . json_encode($result));

                            // Cleanup
                            if (file_exists($tempFile))
                                unlink($tempFile);
                        } else {
                            error_log("PDF generation failed, using link fallback");
                            // Link fallback if PDF fails
                            $encryptedId = \System\Security::encrypt($visitData['visit_id']);
                            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                            $url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/opd/print_prescription?visit_id=" . $encryptedId;
                            $result = $wa->sendDocument($patient['primary_mobile'], $url, "Prescription-" . $visitData['visit_id'] . ".pdf", $msg);
                            error_log("WhatsApp sendDocument result: " . json_encode($result));
                        }
                    } else {
                        error_log("Missing data - visitData: " . ($visitData ? 'YES' : 'NO') . ", patient: " . ($patient ? 'YES' : 'NO') . ", mobile: " . ($patient['primary_mobile'] ?? 'EMPTY'));
                    }
                } else {
                    error_log("No appointment found for queue_id: " . $id);
                }
            } catch (\Exception $e) {
                error_log("WA Prescription Error: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
            }

            Response::success(null, 'Visit completed');
        } catch (\Exception $e) {
            if ($this->db->inTransaction())
                $this->db->rollback();
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Queue Management - Active Queue
     */
    public function activeQueue(): void
    {
        $branchId = $this->user['branch_id'] ?? null;
        $params = [];
        $sql = "SELECT q.*, 
                   CONCAT(p.first_name, ' ', COALESCE(p.last_name, '')) as patient_name,
                   p.mrn as mr_no, p.age, p.gender,
                   pr.full_name as provider_name,
                   v.visit_id
            FROM appointment_queue q
            JOIN patients p ON q.patient_id = p.patient_id
            JOIN appointments a ON q.appointment_id = a.appointment_id
            LEFT JOIN providers pr ON a.provider_id = pr.provider_id
            LEFT JOIN visits v ON a.appointment_id = v.appointment_id AND v.visit_status = 'open'
            WHERE DATE(q.created_at) = CURDATE() AND q.status IN ('waiting', 'called', 'in-progress')";

        if ($branchId) {
            $sql .= " AND q.branch_id = ?";
            $params[] = $branchId;
        }

        $sql .= " ORDER BY q.status = 'called' DESC, q.status = 'in-progress' DESC, q.token_no ASC";

        $queue = $this->db->fetchAll($sql, $params);
        Response::success(['queue' => $queue]);
    }

    private function calculateAge(?string $dob): int
    {
        if (!$dob)
            return 0;
        $birthDate = new \DateTime($dob);
        $today = new \DateTime();
        return $today->diff($birthDate)->y;
    }
}
