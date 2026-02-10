<?php
/**
 * Estimate Management Service
 * Handles estimate creation, conversion to invoices, and approval workflows
 */

namespace Api\Services;

use System\Database;
use System\Logger;

class EstimateService
{
    private Database $db;
    private float $approvalThreshold;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->approvalThreshold = (float) ($_ENV['ESTIMATE_APPROVAL_THRESHOLD'] ?? 10000.00);
    }

    /**
     * Create new estimate
     * 
     * @param int $patientId Patient ID
     * @param array $items Array of estimate items
     * @param int $createdBy User ID who created
     * @param int|null $branchId Branch ID
     * @return array Estimate details
     * @throws \Exception
     */
    public function createEstimate(int $patientId, array $items, int $createdBy, ?int $branchId = null): array
    {
        if (empty($items)) {
            throw new \Exception('Estimate must have at least one item');
        }

        try {
            $this->db->beginTransaction();

            // Generate estimate number
            $estimateNo = $this->generateEstimateNumber($branchId);

            // Calculate totals
            $totals = $this->calculateTotals($items);

            // Determine if approval is required
            $requiresApproval = $totals['total'] >= $this->approvalThreshold;
            $status = $requiresApproval ? 'draft' : 'posted';

            // Create estimate (invoice with type 'estimate')
            $estimateId = $this->db->insert('invoices', [
                'branch_id' => $branchId,
                'invoice_no' => $estimateNo,
                'patient_id' => $patientId,
                'invoice_type' => 'estimate',
                'total_amount' => $totals['total'],
                'tax_total' => $totals['tax_total'],
                'discount_total' => $totals['discount_total'],
                'paid_amount' => 0.00,
                'status' => $status,
                'created_by' => $createdBy,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Insert estimate items
            foreach ($items as $item) {
                $this->db->insert('invoice_items', [
                    'invoice_id' => $estimateId,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'] ?? '',
                    'qty' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0.00,
                    'tax_percent' => $item['tax_percent'] ?? 0.00
                ]);
            }

            $this->db->commit();

            Logger::info('Estimate created', [
                'estimate_id' => $estimateId,
                'estimate_no' => $estimateNo,
                'patient_id' => $patientId,
                'total' => $totals['total'],
                'requires_approval' => $requiresApproval,
                'created_by' => $createdBy
            ]);

            return [
                'estimate_id' => $estimateId,
                'estimate_no' => $estimateNo,
                'patient_id' => $patientId,
                'total' => $totals['total'],
                'tax_total' => $totals['tax_total'],
                'discount_total' => $totals['discount_total'],
                'status' => $status,
                'requires_approval' => $requiresApproval,
                'created_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            Logger::error('Estimate creation failed', [
                'patient_id' => $patientId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get estimate details
     * 
     * @param int $estimateId Estimate ID
     * @return array|null Estimate details
     */
    public function getEstimate(int $estimateId): ?array
    {
        $estimate = $this->db->fetch(
            "SELECT i.*, p.first_name, p.last_name, p.mrn, p.primary_mobile,
                    b.name as branch_name, u.full_name as created_by_name
             FROM invoices i
             LEFT JOIN patients p ON i.patient_id = p.patient_id
             LEFT JOIN branches b ON i.branch_id = b.branch_id
             LEFT JOIN users u ON i.created_by = u.user_id
             WHERE i.invoice_id = ? AND i.invoice_type = 'estimate'",
            [$estimateId]
        );

        if (!$estimate) {
            return null;
        }

        // Get estimate items
        $items = $this->db->fetchAll(
            "SELECT ii.*, pr.name as product_name, pr.unit
             FROM invoice_items ii
             LEFT JOIN products pr ON ii.product_id = pr.product_id
             WHERE ii.invoice_id = ?",
            [$estimateId]
        );

        $estimate['items'] = $items;
        $estimate['patient_name'] = trim($estimate['first_name'] . ' ' . $estimate['last_name']);

        return $estimate;
    }

    /**
     * Update estimate
     * 
     * @param int $estimateId Estimate ID
     * @param array $items New items array
     * @return bool Success status
     * @throws \Exception
     */
    public function updateEstimate(int $estimateId, array $items): bool
    {
        // Check if estimate exists and is editable
        $estimate = $this->db->fetch(
            "SELECT * FROM invoices WHERE invoice_id = ? AND invoice_type = 'estimate'",
            [$estimateId]
        );

        if (!$estimate) {
            throw new \Exception('Estimate not found');
        }

        if ($estimate['status'] === 'posted' && $estimate['paid_amount'] > 0) {
            throw new \Exception('Cannot update estimate that has been converted to invoice');
        }

        try {
            $this->db->beginTransaction();

            // Delete existing items
            $this->db->delete('invoice_items', 'invoice_id = ?', [$estimateId]);

            // Calculate new totals
            $totals = $this->calculateTotals($items);

            // Update estimate
            $this->db->update('invoices', [
                'total_amount' => $totals['total'],
                'tax_total' => $totals['tax_total'],
                'discount_total' => $totals['discount_total'],
                'updated_at' => date('Y-m-d H:i:s')
            ], 'invoice_id = ?', [$estimateId]);

            // Insert new items
            foreach ($items as $item) {
                $this->db->insert('invoice_items', [
                    'invoice_id' => $estimateId,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'] ?? '',
                    'qty' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0.00,
                    'tax_percent' => $item['tax_percent'] ?? 0.00
                ]);
            }

            $this->db->commit();

            Logger::info('Estimate updated', [
                'estimate_id' => $estimateId,
                'new_total' => $totals['total']
            ]);

            return true;

        } catch (\Exception $e) {
            $this->db->rollback();
            Logger::error('Estimate update failed', [
                'estimate_id' => $estimateId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Approve estimate
     * 
     * @param int $estimateId Estimate ID
     * @param int $approvedBy User ID who approved
     * @return bool Success status
     * @throws \Exception
     */
    public function approveEstimate(int $estimateId, int $approvedBy): bool
    {
        $estimate = $this->db->fetch(
            "SELECT * FROM invoices WHERE invoice_id = ? AND invoice_type = 'estimate'",
            [$estimateId]
        );

        if (!$estimate) {
            throw new \Exception('Estimate not found');
        }

        if ($estimate['status'] !== 'draft') {
            throw new \Exception('Only draft estimates can be approved');
        }

        $this->db->update('invoices', [
            'status' => 'posted',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ], 'invoice_id = ?', [$estimateId]);

        Logger::info('Estimate approved', [
            'estimate_id' => $estimateId,
            'approved_by' => $approvedBy
        ]);

        return true;
    }

    /**
     * Convert estimate to invoice
     * 
     * @param int $estimateId Estimate ID
     * @param string $invoiceType New invoice type (pharmacy, service, package, room)
     * @param int $convertedBy User ID who converted
     * @return array New invoice details
     * @throws \Exception
     */
    public function convertToInvoice(int $estimateId, string $invoiceType, int $convertedBy): array
    {
        $estimate = $this->getEstimate($estimateId);

        if (!$estimate) {
            throw new \Exception('Estimate not found');
        }

        if ($estimate['status'] === 'cancelled') {
            throw new \Exception('Cannot convert cancelled estimate');
        }

        if (!in_array($invoiceType, ['pharmacy', 'service', 'package', 'room'])) {
            throw new \Exception('Invalid invoice type');
        }

        try {
            $this->db->beginTransaction();

            // Generate invoice number
            $invoiceNo = $this->generateInvoiceNumber($estimate['branch_id'], $invoiceType);

            // Create new invoice
            $invoiceId = $this->db->insert('invoices', [
                'branch_id' => $estimate['branch_id'],
                'invoice_no' => $invoiceNo,
                'patient_id' => $estimate['patient_id'],
                'visit_id' => null, // Can be linked later
                'invoice_type' => $invoiceType,
                'total_amount' => $estimate['total_amount'],
                'tax_total' => $estimate['tax_total'],
                'discount_total' => $estimate['discount_total'],
                'paid_amount' => 0.00,
                'status' => 'posted',
                'created_by' => $convertedBy,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Copy items from estimate to invoice
            foreach ($estimate['items'] as $item) {
                $this->db->insert('invoice_items', [
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'tax_percent' => $item['tax_percent']
                ]);
            }

            // Mark estimate as converted (cancelled status)
            $this->db->update('invoices', [
                'status' => 'cancelled',
                'cancellation_reason' => "Converted to invoice #{$invoiceNo}",
                'cancelled_by' => $convertedBy,
                'cancelled_at' => date('Y-m-d H:i:s')
            ], 'invoice_id = ?', [$estimateId]);

            $this->db->commit();

            Logger::info('Estimate converted to invoice', [
                'estimate_id' => $estimateId,
                'invoice_id' => $invoiceId,
                'invoice_no' => $invoiceNo,
                'invoice_type' => $invoiceType,
                'converted_by' => $convertedBy
            ]);

            return [
                'invoice_id' => $invoiceId,
                'invoice_no' => $invoiceNo,
                'invoice_type' => $invoiceType,
                'total' => $estimate['total_amount'],
                'status' => 'posted'
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            Logger::error('Estimate conversion failed', [
                'estimate_id' => $estimateId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get estimate metrics
     * 
     * @param int|null $branchId Branch ID filter
     * @param array|null $dateRange Date range [start, end]
     * @return array Metrics
     */
    public function getEstimateMetrics(?int $branchId = null, ?array $dateRange = null): array
    {
        $where = "invoice_type = 'estimate'";
        $params = [];

        if ($branchId) {
            $where .= " AND branch_id = ?";
            $params[] = $branchId;
        }

        if ($dateRange && count($dateRange) === 2) {
            $where .= " AND DATE(created_at) BETWEEN ? AND ?";
            $params[] = $dateRange[0];
            $params[] = $dateRange[1];
        }

        $metrics = $this->db->fetch(
            "SELECT 
                COUNT(*) as total_estimates,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as pending_approval,
                SUM(CASE WHEN status = 'posted' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as converted,
                SUM(total_amount) as total_value,
                AVG(total_amount) as average_value,
                MAX(total_amount) as highest_value
             FROM invoices
             WHERE {$where}",
            $params
        );

        return $metrics ?: [];
    }

    /**
     * Calculate totals from items
     * 
     * @param array $items Items array
     * @return array Totals
     */
    private function calculateTotals(array $items): array
    {
        $subtotal = 0.00;
        $taxTotal = 0.00;
        $discountTotal = 0.00;

        foreach ($items as $item) {
            $qty = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? 0.00;
            $taxPercent = $item['tax_percent'] ?? 0.00;
            $discount = $item['discount'] ?? 0.00;

            $itemTotal = $qty * $unitPrice;
            $itemTax = $itemTotal * ($taxPercent / 100);

            $subtotal += $itemTotal;
            $taxTotal += $itemTax;
            $discountTotal += $discount;
        }

        $total = $subtotal + $taxTotal - $discountTotal;

        return [
            'subtotal' => round($subtotal, 2),
            'tax_total' => round($taxTotal, 2),
            'discount_total' => round($discountTotal, 2),
            'total' => round($total, 2)
        ];
    }

    /**
     * Generate estimate number
     * 
     * @param int|null $branchId Branch ID
     * @return string Estimate number
     */
    private function generateEstimateNumber(?int $branchId): string
    {
        $prefix = 'EST';
        $date = date('Ymd');

        if ($branchId) {
            $branch = $this->db->fetch("SELECT code FROM branches WHERE branch_id = ?", [$branchId]);
            if ($branch) {
                $prefix = $branch['code'] . '-EST';
            }
        }

        // Get last estimate number for today
        $lastNo = $this->db->fetchColumn(
            "SELECT invoice_no FROM invoices 
             WHERE invoice_type = 'estimate' 
             AND invoice_no LIKE ? 
             ORDER BY invoice_id DESC LIMIT 1",
            ["{$prefix}-{$date}%"]
        );

        $sequence = 1;
        if ($lastNo) {
            $parts = explode('-', $lastNo);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf("%s-%s-%04d", $prefix, $date, $sequence);
    }

    /**
     * Generate invoice number
     * 
     * @param int|null $branchId Branch ID
     * @param string $type Invoice type
     * @return string Invoice number
     */
    private function generateInvoiceNumber(?int $branchId, string $type): string
    {
        $prefix = strtoupper(substr($type, 0, 3));
        $date = date('Ymd');

        if ($branchId) {
            $branch = $this->db->fetch("SELECT code FROM branches WHERE branch_id = ?", [$branchId]);
            if ($branch) {
                $prefix = $branch['code'] . '-' . $prefix;
            }
        }

        $lastNo = $this->db->fetchColumn(
            "SELECT invoice_no FROM invoices 
             WHERE invoice_type = ? 
             AND invoice_no LIKE ? 
             ORDER BY invoice_id DESC LIMIT 1",
            [$type, "{$prefix}-{$date}%"]
        );

        $sequence = 1;
        if ($lastNo) {
            $parts = explode('-', $lastNo);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf("%s-%s-%04d", $prefix, $date, $sequence);
    }
}
