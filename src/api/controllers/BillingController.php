<?php
/**
 * Billing & Finance API Controller
 * Handles invoicing, payments, and daily cash reports
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

class BillingController
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
     * Get initial data for POS (Next Bill No, etc)
     */
    public function init(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        try {
            // Get last invoice number to increment
            $last = $this->db->fetch("SELECT invoice_no FROM invoices ORDER BY invoice_id DESC LIMIT 1");
            $lastNum = 0;
            if ($last && preg_match('/BN-(\d+)/', $last['invoice_no'], $matches)) {
                $lastNum = (int) $matches[1];
            }

            $nextBillNo = 'BN-' . str_pad((string) ($lastNum + 1), 4, '0', STR_PAD_LEFT);
            $nextRefNo = 'REF-' . rand(100, 999);

            Response::success([
                'bill_no' => $nextBillNo,
                'ref_no' => $nextRefNo,
                'date' => date('Y-m-d')
            ]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * List invoices
     */
    public function invoices(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        $search = $_GET['search'] ?? '';
        $branchId = $this->user['branch_id'] ?? null;
        $patientId = $_GET['patient_id'] ?? null;

        $params = [];
        $where = ["1=1"];

        if ($branchId) {
            $where[] = "i.branch_id = ?";
            $params[] = $branchId;
        }

        if ($patientId) {
            $where[] = "i.patient_id = ?";
            $params[] = (int) $patientId;
        }

        if ($search) {
            $where[] = "(i.invoice_no LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ?)";
            $term = "%$search%";
            $params = array_merge($params, [$term, $term, $term]);
        }

        $sql = "SELECT i.*, p.first_name, p.last_name, p.mrn 
                FROM invoices i 
                LEFT JOIN patients p ON i.patient_id = p.patient_id 
                WHERE " . implode(" AND ", $where) . " 
                ORDER BY i.created_at DESC";

        try {
            $invoices = $this->db->fetchAll($sql, $params);
            Response::success(['invoices' => $invoices]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Get single invoice details
     */
    public function show($id): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        try {
            $invoice = $this->db->fetch("
                SELECT i.*, p.first_name, p.last_name, p.mrn, p.primary_mobile as mobile, p.patient_id
                FROM invoices i 
                LEFT JOIN patients p ON i.patient_id = p.patient_id 
                WHERE i.invoice_id = ?", [(int) $id]);

            if (!$invoice) {
                Response::error('Invoice not found', 404);
                return;
            }

            $invoice['full_name'] = trim(($invoice['first_name'] ?? '') . ' ' . ($invoice['last_name'] ?? ''));

            // Fetch items with product details and calculated totals
            $items = $this->db->fetchAll("
                SELECT 
                    ii.*, 
                    p.name as product_name, 
                    p.sku as barcode, 
                    p.hsn_code, 
                    p.unit, 
                    ii.tax_percent as gst_percent, 
                    ii.unit_price as rate,
                    ii.discount_pct as discount_percent,
                    (ii.qty * ii.unit_price) * (1 - ii.discount_pct/100) * (1 + ii.tax_percent/100) as line_total
                FROM invoice_items ii 
                LEFT JOIN products p ON ii.product_id = p.product_id
                WHERE ii.invoice_id = ?", [(int) $id]);

            // Map calculated line total to 'total' for easier JS consumption
            foreach ($items as &$item) {
                $item['total'] = round((float) ($item['line_total'] ?? 0), 2);
            }

            $invoice['items'] = $items;

            Response::success($invoice);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * List payments
     */
    public function payments(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        $branchId = $this->user['branch_id'] ?? null;
        $params = [];
        $where = ["1=1"];

        if ($branchId) {
            $where[] = "branch_id = ?";
            $params[] = $branchId;
        }

        try {
            $payments = $this->db->fetchAll("SELECT * FROM payments WHERE " . implode(" AND ", $where) . " ORDER BY payment_date DESC", $params);
            Response::success(['payments' => $payments]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * List Packages
     */
    public function packages(): void
    {
        try {
            $packages = $this->db->fetchAll("SELECT * FROM packages ORDER BY name");
            Response::success(['packages' => $packages]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * DCR entries
     */
    public function dcr(): void
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        try {
            $dcr = $this->db->fetch("SELECT * FROM dcr_entries WHERE date = ?", [$date]);
            Response::success(['dcr' => $dcr]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Search billable items from remote Melina database
     */
    public function items(): void
    {
        // PROFESSIONAL FIX: Release session lock immediately so multiple rapid search requests don't queue up
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $search = $_GET['search'] ?? '';

        try {
            $searchPattern = "%{$search}%";

            $sql = "SELECT 
                        p.product_id as id, 
                        p.name, 
                        p.sku, 
                        p.unit,
                        p.tax_percent as tax_rate,
                        p.hsn_code,
                        COALESCE(ib.mrp, 0) as price,
                        COALESCE(ib.batch_no, 'N/A') as batch_no,
                        COALESCE(ib.expiry_date, 'N/A') as expiry,
                        SUM(COALESCE(ib.qty_available, 0)) as stock
                    FROM products p
                    LEFT JOIN inventory_batches ib ON p.product_id = ib.product_id
                    WHERE p.is_active = 1 
                    AND (p.name LIKE ? OR p.sku LIKE ?)
                    GROUP BY p.product_id, p.name, p.sku, ib.batch_no
                    ORDER BY p.name ASC LIMIT 30";

            $items = $this->db->fetchAll($sql, [$searchPattern, $searchPattern]);
            Response::success(['items' => $items]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch billing items: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new invoice
     */
    public function store(): void
    {
        $input = \jsonInput();

        if (empty($input['patient_id']) || empty($input['items'])) {
            Response::error('Patient and items are required', 422);
            return;
        }

        try {
            $this->db->beginTransaction();

            // 1. Create Invoice Header
            $netTotal = (float) ($input['net_total'] ?? $input['total_amount'] ?? 0);
            $invoiceId = $this->db->insert('invoices', [
                'branch_id' => $this->user['branch_id'] ?? null,
                'invoice_no' => $input['invoice_no'] ?? ('INV-' . date('Ymd') . '-' . rand(1000, 9999)),
                'patient_id' => $input['patient_id'],
                'doctor_id' => $input['doctor_id'] ?? null,
                'visit_id' => $input['visit_id'] ?? null,
                'invoice_type' => $input['invoice_type'] ?? 'service',
                'invoice_date' => $input['invoice_date'] ?? date('Y-m-d'),
                'total_amount' => $netTotal,
                'tax_total' => $input['tax_total'] ?? $input['gst_total'] ?? 0,
                'discount_total' => $input['discount_total'] ?? $input['trade_discount_amount'] ?? 0,
                'paid_amount' => $input['received'] ?? $input['paid_amount'] ?? $netTotal,
                'status' => 'paid',
                'payment_mode' => $input['payment_mode'] ?? 'cash',
                'remarks' => $input['remarks'] ?? $input['notes'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // 2. Create Invoice Items
            foreach ($input['items'] as $item) {
                $this->db->insert('invoice_items', [
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'] ?? $item['product_name'] ?? 'Item',
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'] ?? $item['rate'] ?? 0,
                    'discount_pct' => $item['discount_pct'] ?? $item['discount_percent'] ?? 0,
                    'tax_percent' => $item['tax_percent'] ?? $item['gst_percent'] ?? 0
                ]);
            }

            // 3. Record Payment if any
            if (!empty($input['paid_amount']) && $input['paid_amount'] > 0) {
                $this->db->insert('payments', [
                    'branch_id' => $this->user['branch_id'] ?? null,
                    'invoice_id' => $invoiceId,
                    'patient_id' => $input['patient_id'],
                    'amount' => $input['paid_amount'],
                    'payment_mode' => $input['payment_mode'] ?? 'cash',
                    'payment_date' => date('Y-m-d H:i:s'),
                    'transaction_ref' => $input['transaction_ref'] ?? null,
                    'created_by' => $this->user['user_id'] ?? null
                ]);
            }

            $this->db->commit();

            // Fetch the generated invoice no to return it
            $invNo = $this->db->fetchColumn("SELECT invoice_no FROM invoices WHERE invoice_id = ?", [$invoiceId]);

            // 4. Generate PDF (must happen before response)
            $pdfGenerated = false;
            try {
                $pc = new \App\Controllers\PrintController();
                $pdfContent = $pc->getInvoicePdfContent((int) $invoiceId);

                if ($pdfContent) {
                    $dir = PUBLIC_PATH . '/storage/invoices';
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }

                    $cleanNo = str_replace(['/', '\\'], '_', $invNo);
                    $invoiceFile = $dir . '/Inv_' . $cleanNo . '.pdf';
                    file_put_contents($invoiceFile, $pdfContent);

                    Logger::info("Invoice #$invoiceId PDF saved to: $invoiceFile");
                    $pdfGenerated = true;
                } else {
                    Logger::error("Failed to generate PDF for invoice #$invoiceId");
                }
            } catch (\Exception $pdfEx) {
                Logger::error("PDF generation failed: " . $pdfEx->getMessage());
            }

            // 5. Send WhatsApp (must happen before response since Response::success() calls exit)
            if ($pdfGenerated) {
                try {
                    $patient = $this->db->fetch("SELECT first_name, last_name, primary_mobile FROM patients WHERE patient_id = ?", [(int) $input['patient_id']]);
                    if ($patient && !empty($patient['primary_mobile'])) {
                        $wa = new WhatsAppService();
                        $custName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''));
                        $branch = $this->db->fetch("SELECT name FROM branches WHERE branch_id = ?", [$this->user['branch_id'] ?? 1]);
                        $branchName = $branch['name'] ?? 'DIAWIN';

                        $msg = "👋 *Greetings from " . $branchName . "*\n\nDear " . $custName . ",\n\nThank you for choosing us for your healthcare needs. Please find your secure digital invoice attached.\n\n🧾 *Bill No:* " . $invNo . "\n💰 *Total Amount:* ₹" . number_format($netTotal, 2) . "\n\nWe wish you a speedy recovery! 🌿";

                        $dir = PUBLIC_PATH . '/storage/invoices';
                        $cleanNo = str_replace(['/', '\\'], '_', $invNo);
                        $invoiceFile = $dir . '/Inv_' . $cleanNo . '.pdf';

                        if (file_exists($invoiceFile)) {
                            $result = $wa->sendFile($patient['primary_mobile'], $invoiceFile, $invNo . ".pdf", $msg);
                            Logger::info("Invoice #$invoiceId WhatsApp sent. Result: " . json_encode($result));
                        } else {
                            Logger::error("Invoice PDF file not found: $invoiceFile");
                        }
                    }
                } catch (\Exception $waEx) {
                    Logger::error("WhatsApp sending failed: " . $waEx->getMessage());
                }
            }

            // 6. Send response to user (this calls exit, so nothing after this runs)
            Response::success(['invoice_id' => $invoiceId, 'invoice_no' => $invNo, 'bill_no' => $invNo], 'Invoice created successfully', 201);

        } catch (\Exception $e) {
            $this->db->rollBack();
            Response::error('Failed to create invoice: ' . $e->getMessage(), 500);
        }
    }
    public function printInvoice($id): void
    {
        // Get ID from URL path (e.g., /api/v1/invoices/82/print) or argument
        if (!$id) {
            // Fallback parsing if needed, though Router should pass it
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if (preg_match('/\/invoices\/(\d+)\/print/', $uri, $matches)) {
                $id = (int) $matches[1];
            }
        }

        if (!$id) {
            Response::error('Invoice ID required', 400);
            return;
        }

        try {
            $pc = new \App\Controllers\PrintController();
            $pdfContent = $pc->getInvoicePdfContent((int) $id);

            if (!$pdfContent) {
                Response::error('Failed to generate invoice PDF', 500);
                return;
            }

            // Output PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="Invoice-' . $id . '.pdf"');
            header('Content-Length: ' . strlen($pdfContent));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            echo $pdfContent;
            exit;

        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}

