<?php
/**
 * Visit & Clinical API Controller
 * Handles all clinical-related API endpoints (Visits, Vitals, Notes, Prescriptions)
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

class VisitController
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
     * List all clinical visits
     */
    public function index(): void
    {
        $search = Security::sanitizeInput($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';
        $type = $_GET['type'] ?? '';
        $dateRange = $_GET['date_range'] ?? '';

        $params = [];
        $where = ["1=1"];

        if (!empty($this->user['branch_id'])) {
            $where[] = "v.branch_id = ?";
            $params[] = $this->user['branch_id'];
        }

        if (!empty($status)) {
            $where[] = "v.visit_status = ?";
            $params[] = $status;
        }

        if (!empty($type)) {
            $where[] = "v.visit_type = ?";
            $params[] = $type;
        }

        if (!empty($search)) {
            $where[] = "(p.mrn LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR pr.full_name LIKE ?)";
            $term = "%$search%";
            $params = array_merge($params, [$term, $term, $term, $term]);
        }

        if (!empty($dateRange)) {
            $dates = explode(' to ', $dateRange);
            if (count($dates) === 2) {
                $where[] = "DATE(v.visit_start) BETWEEN ? AND ?";
                $params[] = $dates[0];
                $params[] = $dates[1];
            } else {
                $where[] = "DATE(v.visit_start) = ?";
                $params[] = $dates[0];
            }
        }

        $whereSql = implode(" AND ", $where);
        $sql = "SELECT v.*, p.mrn, p.first_name, p.last_name, p.gender, p.dob,
                       pr.full_name as provider_name, pr.specialization
                FROM visits v
                JOIN patients p ON v.patient_id = p.patient_id
                LEFT JOIN providers pr ON v.primary_provider_id = pr.provider_id
                WHERE {$whereSql}
                ORDER BY v.visit_start DESC
                LIMIT 100";

        try {
            $visits = $this->db->fetchAll($sql, $params);
            Response::success(['visits' => $visits]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
    /**
     * Get single visit full details
     */
    public function show(int $id): void
    {
        try {
            $visit = $this->db->fetch(
                "SELECT v.*, p.mrn, p.first_name, p.last_name, p.gender, p.dob,
                        pr.full_name as provider_name, pr.specialization, b.name as branch_name
                 FROM visits v
                 JOIN patients p ON v.patient_id = p.patient_id
                 LEFT JOIN providers pr ON v.primary_provider_id = pr.provider_id
                 LEFT JOIN branches b ON v.branch_id = b.branch_id
                 WHERE v.visit_id = ?",
                [$id]
            );

            if (!$visit) {
                Response::notFound('Visit not found');
                return;
            }

            // Get vitals
            $vitals = $this->db->fetch("SELECT * FROM vitals WHERE visit_id = ?", [$id]);

            // Get prescriptions
            $prescriptions = $this->db->fetchAll(
                "SELECT p.*, pi.dosage, pi.frequency, pi.duration_days, pi.quantity, prod.name as product_name
                 FROM prescriptions p
                 LEFT JOIN prescription_items pi ON p.prescription_id = pi.prescription_id
                 LEFT JOIN products prod ON pi.product_id = prod.product_id
                 WHERE p.visit_id = ?",
                [$id]
            );

            // Get clinical notes
            $clinicalNotes = $this->db->fetchAll("SELECT * FROM clinical_notes WHERE visit_id = ? ORDER BY created_at DESC", [$id]);

            // Get siddha notes
            $siddhaNotes = $this->db->fetch("SELECT * FROM siddha_notes WHERE visit_id = ?", [$id]);

            Response::success([
                'visit' => $visit,
                'vitals' => $vitals,
                'prescriptions' => $prescriptions,
                'clinical_notes' => $clinicalNotes,
                'siddha_notes' => $siddhaNotes
            ]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Get Clinical Stats
     */
    public function stats(): void
    {
        $branchId = $this->user['branch_id'] ?? null;
        $params = [];
        $branchSql = $branchId ? " AND branch_id = ?" : "";
        if ($branchId)
            $params[] = $branchId;

        try {
            $total = $this->db->fetchColumn("SELECT COUNT(*) FROM visits WHERE 1=1 $branchSql", $params);
            $active = $this->db->fetchColumn("SELECT COUNT(*) FROM visits WHERE visit_status = 'open' $branchSql", $params);
            $today = $this->db->fetchColumn("SELECT COUNT(*) FROM visits WHERE DATE(visit_start) = CURDATE() $branchSql", $params);

            Response::success([
                'total_visits' => $total,
                'active_encounters' => $active,
                'today_visits' => $today,
                'emergency' => 0 // Placeholder
            ]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Vitals Management
     */
    public function getVitals(int $visitId): void
    {
        try {
            $vitals = $this->db->fetchAll("SELECT * FROM vitals WHERE visit_id = ? ORDER BY captured_at DESC", [$visitId]);
            Response::success(['vitals' => $vitals]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function storeVitals(): void
    {
        $input = jsonInput();
        if (empty($input['visit_id'])) {
            Response::error('Visit ID required', 422);
            return;
        }

        try {
            $data = [
                'visit_id' => $input['visit_id'],
                'captured_by' => $this->user['user_id'] ?? null,
                'captured_at' => date('Y-m-d H:i:s'),
                'height_cm' => !empty($input['height']) ? $input['height'] : null,
                'weight_kg' => !empty($input['weight']) ? $input['weight'] : null,
                'temperature_c' => !empty($input['temp']) ? $input['temp'] : null,
                'pulse_per_min' => !empty($input['pulse']) ? $input['pulse'] : null,
                'respiratory_rate' => !empty($input['rr']) ? $input['rr'] : null,
                'bp_systolic' => !empty($input['sys']) ? $input['sys'] : null,
                'bp_diastolic' => !empty($input['dia']) ? $input['dia'] : null,
                'spo2' => !empty($input['spo2']) ? $input['spo2'] : null,
                'notes' => !empty($input['notes']) ? $input['notes'] : null
            ];

            // Check if vitals already exist for this visit to update or insert
            $existing = $this->db->fetch("SELECT vitals_id FROM vitals WHERE visit_id = ?", [$input['visit_id']]);
            if ($existing) {
                unset($data['visit_id'], $data['captured_at'], $data['captured_by']); // Keep original capture info? or update? Let's just update values
                $this->db->update('vitals', $data, 'vitals_id = ?', [$existing['vitals_id']]);
                Response::success(['vitals_id' => $existing['vitals_id']], 'Vitals updated successfully');
            } else {
                $id = $this->db->insert('vitals', $data);
                Response::success(['vitals_id' => $id], 'Vitals saved successfully');
            }

        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Siddha Notes Management
     */
    public function getSiddhaNotes(int $visitId): void
    {
        try {
            $notes = $this->db->fetch("SELECT * FROM siddha_notes WHERE visit_id = ?", [$visitId]);
            Response::success(['notes' => $notes]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function storeSiddhaNotes(): void
    {
        $input = jsonInput();
        if (empty($input['visit_id'])) {
            Response::error('Visit ID required', 422);
            return;
        }

        try {
            $exists = $this->db->fetch("SELECT siddha_id FROM siddha_notes WHERE visit_id = ?", [$input['visit_id']]);

            // Fix: Logged in user is a 'user' (users table), NOT a 'provider' (providers table).
            // The FK points to providers table.
            // If the current user is NOT linked to a provider record, this will fail if we use user_id directly as practitioner_id.
            // Ideally, we should find the provider_id linked to this user_id.
            // For now, given the schema change (FK dropped), we can store user_id or null.

            $data = [
                'practitioner_id' => $this->user['user_id'] ?? null,
                'pulse_diagnosis' => !empty($input['pulse_diagnosis']) ? $input['pulse_diagnosis'] : null,
                'tongue' => !empty($input['tongue']) ? $input['tongue'] : null,
                'prakriti' => !empty($input['prakriti']) ? $input['prakriti'] : null,
                'anupanam' => !empty($input['anupanam']) ? $input['anupanam'] : null,
                'note_text' => !empty($input['note_text']) ? $input['note_text'] : null,
            ];

            if ($exists) {
                $this->db->update('siddha_notes', $data, 'visit_id = ?', [$exists['visit_id']]); // Use visit_id from fetch or input

                // Update Follow-up date in visits table
                if (!empty($input['follow_up_date'])) {
                    $this->db->update('visits', ['follow_up_date' => $input['follow_up_date']], 'visit_id = ?', [$input['visit_id']]);
                }

                Response::success(null, 'Siddha notes updated');
            } else {
                $data['visit_id'] = $input['visit_id'];
                $this->db->insert('siddha_notes', $data);

                // Update Follow-up date in visits table
                if (!empty($input['follow_up_date'])) {
                    $this->db->update('visits', ['follow_up_date' => $input['follow_up_date']], 'visit_id = ?', [$input['visit_id']]);
                }

                Response::success(null, 'Siddha notes saved');
            }
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Clinical Notes Management
     */
    public function getClinicalNotes(int $visitId): void
    {
        try {
            $notes = $this->db->fetchAll("SELECT * FROM clinical_notes WHERE visit_id = ? ORDER BY created_at DESC", [$visitId]);
            $siddha = $this->db->fetch("SELECT * FROM siddha_notes WHERE visit_id = ?", [$visitId]);
            Response::success([
                'notes' => $notes,
                'siddha' => $siddha
            ]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function storeClinicalNote(): void
    {
        $input = jsonInput();
        if (empty($input['visit_id']) || empty($input['note_text'])) {
            Response::error('Visit ID and note text are required', 422);
            return;
        }

        try {
            $id = $this->db->insert('clinical_notes', [
                'visit_id' => $input['visit_id'],
                'provider_id' => $this->user['user_id'] ?? null,
                'note_type' => $input['note_type'] ?? 'general',
                'note_text' => $input['note_text'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            Response::success(['note_id' => $id], 'Clinical note added');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Close a visit
     * POST /api/v1/visits/{id}/close
     */
    public function closeVisit(int $visitId): void
    {
        try {
            // Check if visit exists
            $visit = $this->db->fetch("SELECT * FROM visits WHERE visit_id = ?", [$visitId]);

            if (!$visit) {
                Response::error('Visit not found', 404);
                return;
            }

            if ($visit['visit_status'] === 'closed') {
                Response::error('Visit is already closed', 400);
                return;
            }

            // Close the visit
            $this->db->update('visits', [
                'visit_status' => 'closed',
                'visit_end' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'visit_id = ?', [$visitId]);

            Logger::info('Visit closed', [
                'visit_id' => $visitId,
                'closed_by' => $this->user['user_id'] ?? null
            ]);

            Response::success(null, 'Visit closed successfully');
        } catch (\Exception $e) {
            Logger::error('Failed to close visit', [
                'visit_id' => $visitId,
                'error' => $e->getMessage()
            ]);
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Prescriptions Management
     */
    /**
     * Store Prescription
     */
    public function storePrescription(): void
    {
        $input = jsonInput();
        if (empty($input['visit_id']) || empty($input['items'])) {
            Response::error('Visit ID and items are required', 422);
            return;
        }

        try {
            $this->db->beginTransaction();

            // 1. Create Prescription Header
            // Check if one already exists for this visit? (Optional: for now, we just create a new one or update existing)
            // Strategy: Delete old prescription for this visit and create new one to handle updates easily
            // OR: Just create a new one. Let's stick to one active prescription per visit for now.

            $existing = $this->db->fetch("SELECT prescription_id FROM prescriptions WHERE visit_id = ?", [$input['visit_id']]);
            if ($existing) {
                // Delete existing items to replace with new set
                $this->db->query("DELETE FROM prescription_items WHERE prescription_id = ?", [$existing['prescription_id']]);
                $prescriptionId = $existing['prescription_id'];

                // Update notes if provided
                if (isset($input['notes'])) {
                    $this->db->update('prescriptions', ['notes' => $input['notes']], 'prescription_id = ?', [$prescriptionId]);
                }
            } else {
                $prescriptionId = $this->db->insert('prescriptions', [
                    'visit_id' => $input['visit_id'],
                    'prescribed_by' => $this->user['user_id'] ?? null,
                    'prescribed_at' => date('Y-m-d H:i:s'),
                    'notes' => $input['notes'] ?? null
                ]);
            }

            // 2. Insert Items
            // 2. Insert Items
            foreach ($input['items'] as $item) {
                $productId = $item['product_id'] ?? null;
                $productName = trim($item['product_name'] ?? '');

                // A. Try lookup by name if ID missing
                if (empty($productId) && !empty($productName)) {
                    $prod = $this->db->fetch("SELECT product_id FROM products WHERE name = ? LIMIT 1", [$productName]);
                    if ($prod) {
                        $productId = $prod['product_id'];
                    }
                }

                // B. If still empty, AUTO-CREATE PRODUCT (To satisfy FK and allow custom meds)
                if (empty($productId) && !empty($productName)) {
                    try {
                        $newSku = 'RX-' . strtoupper(substr(md5($productName . time()), 0, 8));
                        $productId = $this->db->insert('products', [
                            'name' => $productName,
                            'sku' => $newSku,
                            'is_active' => 1,
                            'created_at' => date('Y-m-d H:i:s'),
                            'description' => 'Auto-created from Prescription'
                        ]);
                        Logger::info("Auto-created product for prescription", ['name' => $productName, 'id' => $productId]);
                    } catch (\Exception $ex) {
                        Logger::error("Failed to auto-create product", ['name' => $productName, 'error' => $ex->getMessage()]);
                    }
                }

                if (empty($productId)) {
                    Logger::warning("Skipping prescription item: Missing Product ID and Name", ['item' => $item]);
                    continue;
                }

                $this->db->insert('prescription_items', [
                    'prescription_id' => $prescriptionId,
                    'product_id' => $productId,
                    'dosage' => $item['dosage'] ?? null,
                    'frequency' => $item['frequency'] ?? null,
                    'duration_days' => $item['duration_days'] ?? null,
                    'quantity' => $item['quantity'] ?? 1,
                    'notes' => $item['notes'] ?? null
                ]);
            }

            $this->db->commit();
            Response::success(['prescription_id' => $prescriptionId], 'Prescription saved successfully');

        } catch (\Exception $e) {
            $this->db->rollBack();
            Response::error($e->getMessage(), 500);
        }
    }

    public function getPrescriptions(int $visitId): void
    {
        try {
            $sql = "SELECT p.*, pi.item_id, pi.product_id, pi.dosage, pi.frequency, pi.duration_days, pi.quantity, pi.notes as item_notes,
                           prod.name as product_name, prod.unit
                    FROM prescriptions p
                    LEFT JOIN prescription_items pi ON p.prescription_id = pi.prescription_id
                    LEFT JOIN products prod ON pi.product_id = prod.product_id
                    WHERE p.visit_id = ?
                    ORDER BY p.prescribed_at DESC";
            $data = $this->db->fetchAll($sql, [$visitId]);

            // Format result: group items by prescription
            $prescriptions = [];
            foreach ($data as $row) {
                $id = $row['prescription_id'];
                if (!isset($prescriptions[$id])) {
                    $prescriptions[$id] = [
                        'prescription_id' => $id,
                        'prescribed_at' => $row['prescribed_at'],
                        'notes' => $row['notes'],
                        'items' => []
                    ];
                }
                if ($row['item_id']) {
                    $prescriptions[$id]['items'][] = [
                        'item_id' => $row['item_id'],
                        'product_id' => $row['product_id'],
                        'product_name' => $row['product_name'],
                        'dosage' => $row['dosage'],
                        'frequency' => $row['frequency'],
                        'duration_days' => $row['duration_days'],
                        'quantity' => $row['quantity'],
                        'unit' => $row['unit'],
                        'notes' => $row['item_notes']
                    ];
                }
            }

            Response::success(['prescriptions' => array_values($prescriptions)]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Share Prescription via WhatsApp
     */
    public function sharePrescription(): void
    {
        $input = jsonInput();
        $visitId = $input['visit_id'] ?? null;

        if (!$visitId) {
            Response::error('Visit ID required', 400);
            return;
        }

        // Fetch patient
        $visit = $this->db->fetch(
            "SELECT p.primary_mobile, p.first_name, p.last_name, v.visit_id, v.follow_up_date, b.name as branch_name 
             FROM visits v 
             JOIN patients p ON v.patient_id = p.patient_id 
             LEFT JOIN branches b ON v.branch_id = b.branch_id
             WHERE v.visit_id = ?",
            [$visitId]
        );

        if (!$visit || empty($visit['primary_mobile'])) {
            Response::error('Patient mobile not found', 404);
            return;
        }

        try {
            $wa = new WhatsAppService();
            $ptName = trim(($visit['first_name'] ?? '') . ' ' . ($visit['last_name'] ?? ''));
            $branchName = $visit['branch_name'] ?? 'DIAWIN';

            $followUp = !empty($visit['follow_up_date']) ? "\n\n📅 *Next Sitting Date:* " . date('d-M-Y', strtotime($visit['follow_up_date'])) : "";
            $msg = "Hello $ptName,\n\nHere is your digital prescription from $branchName.$followUp\n\nTake care & Get well soon! 🌿";

            // NEW: Generate PDF locally and send as binary attachment
            $pc = new \App\Controllers\PrintController();
            $pdfContent = $pc->getCaseSheetPdfContent((int) $visitId);

            if ($pdfContent) {
                $tempFile = sys_get_temp_dir() . '/rx_' . $visitId . '_' . time() . '.pdf';
                file_put_contents($tempFile, $pdfContent);

                $wa->sendFile($visit['primary_mobile'], $tempFile, "Prescription-$visitId.pdf", $msg);

                // Cleanup
                if (file_exists($tempFile))
                    unlink($tempFile);
            } else {
                // Fallback link
                $encryptedId = \System\Security::encrypt($visitId);
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/opd/print_prescription?visit_id=" . $encryptedId;
                $wa->sendDocument($visit['primary_mobile'], $url, "Prescription-$visitId.pdf", $msg);
            }

            Response::success(['message' => 'Prescription shared successfully']);
        } catch (\Exception $e) {
            \System\Logger::error("Share Prescription Failed: " . $e->getMessage());
            Response::error('Failed to share: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Print Prescription by Prescription ID
     */
    public function printPrescription(): void
    {
        // Get ID from URL path (e.g., /api/v1/prescriptions/25/print)
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $parts = explode('/', trim($uri, '/'));

        // Find prescription ID (should be before 'print')
        $prescriptionId = 0;
        foreach ($parts as $i => $part) {
            if ($part === 'prescriptions' && isset($parts[$i + 1]) && is_numeric($parts[$i + 1])) {
                $prescriptionId = (int) $parts[$i + 1];
                break;
            }
        }

        if (!$prescriptionId) {
            Response::error('Prescription ID is required', 400);
            return;
        }

        // Get visit_id from prescription
        $prescription = $this->db->fetch(
            "SELECT visit_id FROM prescriptions WHERE prescription_id = ?",
            [$prescriptionId]
        );

        if (!$prescription) {
            Response::error('Prescription not found', 404);
            return;
        }

        $visitId = (int) $prescription['visit_id'];

        // Generate PDF using PrintController
        $pc = new \App\Controllers\PrintController();
        $pdfContent = $pc->getCaseSheetPdfContent($visitId);

        if (!$pdfContent) {
            Response::error('Failed to generate prescription PDF', 500);
            return;
        }

        // Output PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="Prescription-' . $prescriptionId . '.pdf"');
        header('Content-Length: ' . strlen($pdfContent));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo $pdfContent;
        exit;
    }
}
