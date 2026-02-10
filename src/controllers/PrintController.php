<?php
declare(strict_types=1);

namespace App\Controllers;

use System\Database;
use System\Response;
use System\Session;
use Mpdf\Mpdf;

class PrintController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function registrationSlip(): void
    {
        $patientId = (int) ($_GET['patient_id'] ?? 0);
        if (!$patientId) {
            die('Patient ID is required');
        }

        // Fetch patient details
        $patient = $this->db->fetch(
            "SELECT p.*, b.name as branch_name, b.address as branch_address, b.phone as branch_phone, b.email as branch_email 
             FROM patients p
             LEFT JOIN branches b ON p.branch_id = b.branch_id
             WHERE p.patient_id = ?",
            [$patientId]
        );

        if (!$patient) {
            die('Patient not found');
        }

        $hospitalName = $_ENV['APP_NAME'] ?? 'Melina HMS';
        $mrn = $patient['mrn'];
        $name = trim(($patient['title'] ? $patient['title'] . ' ' : '') . $patient['first_name'] . ' ' . ($patient['last_name'] ?? ''));
        $age = $patient['age'] ?? $this->calculateAge($patient['dob']);
        $gender = ucfirst($patient['gender'] ?? 'Unknown');
        $mobile = $patient['primary_mobile'] ?? 'N/A';
        $registrationDate = date('d-m-Y H:i', strtotime($patient['created_at']));
        $bloodGroup = $patient['blood_group'] ?? 'N/A';
        $address = trim(($patient['address'] ?? '') . ', ' . ($patient['city'] ?? '') . ', ' . ($patient['state'] ?? ''));

        // QR Code Data: MRN|Name|Age|Gender|Mobile
        $qrData = "MRN: {$mrn}\nName: {$name}\nAge: {$age}\nGender: {$gender}\nMobile: {$mobile}\nReg Date: {$registrationDate}";

        try {
            // Setup mPDF
            // Custom page size for ID card (Approx 85mm x 55mm or customized for slip)
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [86, 54], // ID Card Size in mm
                'margin_left' => 5,
                'margin_right' => 5,
                'margin_top' => 5,
                'margin_bottom' => 5,
                'margin_header' => 0,
                'margin_footer' => 0,
            ]);

            $html = "
            <html>
            <head>
                <style>
                    body { font-family: 'Montserrat', sans-serif; font-size: 10pt; color: #333; margin: 0; padding: 0; }
                    .card-wrapper { border: 1px solid #eee; border-radius: 8px; overflow: hidden; background: #fff; height: 100%; position: relative; }
                    .header { background: #007a18; color: #fff; padding: 6px 10px; display: flex; align-items: center; justify-content: space-between; }
                    .hospital-name { font-size: 11pt; font-weight: bold; margin: 0; }
                    .card-body { padding: 8px 10px; }
                    .patient-details { margin-top: 5px; }
                    .row { margin-bottom: 2px; }
                    .label { color: #666; font-size: 7pt; font-weight: bold; display: block; margin-bottom: 0; }
                    .value { font-size: 8pt; font-weight: 600; color: #000; }
                    .qr-container { position: absolute; bottom: 8px; right: 10px; width: 45px; height: 45px; }
                    .barcode-container { margin-top: 5px; text-align: center; }
                    .footer-info { font-size: 6pt; color: #888; margin-top: 2px; }
                    .id-label { background: #eef2f7; color: #007a18; font-size: 7pt; font-weight: bold; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-bottom: 5px; }
                    .branch-name { font-size: 6pt; color: #fff; opacity: 0.9; display: block; }
                </style>
            </head>
            <body>
                <div class='card-wrapper'>
                    <div class='header'>
                        <div class='hospital-name'>{$hospitalName}</div>
                        <div class='branch-name'>" . ($patient['branch_name'] ?? 'Diawin HMS') . "</div>
                    </div>
                    <div class='card-body'>
                        <div class='id-label'>PATIENT ID CARD</div>
                        
                        <div class='patient-details'>
                            <table width='100%' cellpadding='0' cellspacing='0'>
                                <tr>
                                    <td width='70%' valign='top'>
                                        <div class='row'>
                                            <span class='label'>MRN</span>
                                            <span class='value' style='font-size: 10pt; color: #007a18;'>{$mrn}</span>
                                        </div>
                                        <div class='row' style='margin-top: 3px;'>
                                            <span class='label'>Patient Name</span>
                                            <span class='value' style='font-size: 9pt;'>{$name}</span>
                                        </div>
                                        <div class='row' style='margin-top: 3px;'>
                                            <table width='100%'>
                                                <tr>
                                                    <td><span class='label'>Age/Sex</span><span class='value'>{$age} / {$gender}</span></td>
                                                    <td><span class='label'>Group</span><span class='value'>{$bloodGroup}</span></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class='row' style='margin-top: 3px;'>
                                            <span class='label'>Contact</span>
                                            <span class='value'>{$mobile}</span>
                                        </div>
                                    </td>
                                    <td width='30%' valign='bottom' align='right'>
                                        <div class='qr-container'>
                                            <barcode code='{$qrData}' type='QR' size='0.5' error='M' disableborder='1' />
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class='footer-info'>
                            Reg Date: {$registrationDate}
                        </div>
                    </div>
                </div>
            </body>
            </html>
            ";

            $mpdf->WriteHTML($html);
            $mpdf->Output("Registration_Slip_{$mrn}.pdf", 'I');

        } catch (\Exception $e) {
            die('Error generating PDF: ' . $e->getMessage());
        }
    }


    public function caseSheet(): void
    {
        $visitId = (int) ($_GET['visit_id'] ?? 0);
        if (!$visitId) {
            die('Visit ID is required');
        }

        $pdfContent = $this->getCaseSheetPdfContent($visitId);
        if ($pdfContent) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="CaseSheet_' . $visitId . '.pdf"');
            echo $pdfContent;
            exit;
        }

        die('Unable to generate case sheet or visit not found');
    }

    private function calculateAge(?string $dob): string
    {
        if (!$dob)
            return 'N/A';
        try {
            $birthDate = new \DateTime($dob);
            $today = new \DateTime();
            $age = $today->diff($birthDate)->y;
            return (string) $age;
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
    public function getCaseSheetPdfContent(int $visitId): ?string
    {
        if (!$visitId) {
            return null;
        }

        // Fetch Visit & Patient details
        $visit = $this->db->fetch(
            "SELECT v.*, p.first_name, p.last_name, p.mrn, p.dob, p.gender, p.blood_group,
                    pr.full_name as provider_name, pr.specialization, 
                    COALESCE(pr.license_no, '') as reg_no,
                    b.name as branch_name, b.address as branch_address, b.phone as branch_phone, b.email as branch_email
             FROM visits v
             JOIN patients p ON v.patient_id = p.patient_id
             LEFT JOIN providers pr ON v.primary_provider_id = pr.provider_id
             LEFT JOIN branches b ON v.branch_id = b.branch_id
             WHERE v.visit_id = ?",
            [$visitId]
        );

        if (!$visit) {
            return null;
        }

        // Fetch Vitals
        $vitals = $this->db->fetch(
            "SELECT * FROM vitals WHERE visit_id = ? ORDER BY captured_at DESC LIMIT 1",
            [$visitId]
        );

        // Fetch Clinical Notes (Generic)
        $notes = $this->db->fetchAll(
            "SELECT * FROM clinical_notes WHERE visit_id = ? ORDER BY created_at ASC",
            [$visitId]
        );

        // Fetch Siddha Notes (Specific)
        $siddha = $this->db->fetch(
            "SELECT * FROM siddha_notes WHERE visit_id = ?",
            [$visitId]
        );

        // Fetch Prescriptions (Header for Diagnosis)
        $rxHeader = $this->db->fetch("SELECT notes FROM prescriptions WHERE visit_id = ? LIMIT 1", [$visitId]);
        $diagnosis = $rxHeader['notes'] ?? 'General Consultation';

        // Fetch Prescriptions Items
        $prescriptions = $this->db->fetchAll(
            "SELECT pi.*, prod.name as product_name, prod.unit
             FROM prescription_items pi
             JOIN prescriptions p ON pi.prescription_id = p.prescription_id
             LEFT JOIN products prod ON pi.product_id = prod.product_id
             WHERE p.visit_id = ?",
            [$visitId]
        );

        $hospitalName = str_replace([' HMS', ' hms'], '', $_ENV['APP_NAME'] ?? 'Melina');
        $age = $this->calculateAge($visit['dob']);
        $gender = ucfirst($visit['gender'] ?? 'Unknown');
        $visitDate = date('d-M-Y', strtotime($visit['visit_start']));
        $visitTime = date('h:i A', strtotime($visit['visit_start']));
        $followUp = !empty($visit['follow_up_date']) ? date('d-M-Y', strtotime($visit['follow_up_date'])) : 'As advised';

        try {
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 12,
                'margin_right' => 12,
                'margin_top' => 50,
                'margin_bottom' => 20,
                'margin_header' => 8,
                'margin_footer' => 8,
            ]);

            $mpdf->SetTitle("Prescription - " . $visit['first_name']);

            // Header Content ( Apollo style)
            $headerHtml = "
            <table width='100%' style='border-bottom: 1px solid #eee; padding-bottom: 20px;'>
                <tr>
                    <td width='20%' valign='top'>
                        <div style='font-size: 24pt; font-weight: bold; color: #007a18; font-family: sans-serif;'>Diawin</div>
                        <div style='font-size: 8pt; color: #666;'>Siddha Healthcare</div>
                    </td>
                    <td width='40%' valign='top' style='padding-left: 20px; border-left: 1px solid #f0f0f0;'>
                        <span style='font-size: 13pt; font-weight: bold; color: #2c3e50;'>Dr. {$visit['provider_name']}</span><br>
                        <span style='font-size: 9pt; color: #555;'>{$visit['specialization']}</span><br>
                        <span style='font-size: 8pt; color: #7f8c8d;'>Reg.No: " . ($visit['reg_no'] ?: '---') . "</span>
                    </td>
                    <td width='40%' align='right' valign='top' style='font-size: 8pt; color: #7f8c8d; line-height: 1.4;'>
                        {$visit['branch_name']}<br>
                        {$visit['branch_address']}<br>
                        Ph: {$visit['branch_phone']} | Email: {$visit['branch_email']}
                    </td>
                </tr>
            </table>";

            $mpdf->SetHTMLHeader($headerHtml);

            // Footer
            $footerHtml = "
            <table width='100%' style='border-top: 1px solid #eee; font-size: 8pt; color: #95a5a6; padding-top: 10px;'>
                <tr>
                    <td>Printed: " . date('d-m-Y H:i') . "</td>
                    <td align='center'>Page {PAGENO} of {nbpg}</td>
                    <td align='right'>Diawin HMS - Digital Prescription</td>
                </tr>
            </table>";
            $mpdf->SetHTMLFooter($footerHtml);

            $html = "
            <html>
            <head>
                <style>
                    body { font-family: 'Inter', 'Helvetica', sans-serif; font-size: 10pt; color: #2c3e50; line-height: 1.5; }
                    .section-header { font-size: 11pt; font-weight: bold; color: #2980b9; margin: 25px 0 10px 0; border-bottom: 1px solid #ecf0f1; padding-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
                    .patient-info { background: #fdfdfd; padding: 15px; border: 1px solid #f1f1f1; border-radius: 4px; margin-bottom: 20px; }
                    .label { color: #7f8c8d; font-size: 9pt; font-weight: normal; }
                    .value { font-weight: 600; color: #2c3e50; }
                    .vitals-row { background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #eee; font-size: 9pt; }
                    .rx-table { border-collapse: collapse; width: 100%; margin-top: 10px; border: 1px solid #f1f1f1; }
                    .rx-table th { background: #f8f9fa; text-align: left; padding: 12px 10px; border-bottom: 1px solid #ddd; color: #34495e; font-size: 9pt; }
                    .rx-table td { padding: 12px 10px; border-bottom: 1px solid #f9f9f9; vertical-align: top; font-size: 9.5pt; }
                    .follow-up-box { background: #fff5f5; border: 1px solid #fed7d7; padding: 12px; margin-top: 30px; border-radius: 4px; }
                </style>
            </head>
            <body>
                <!-- Patient Info Row -->
                <div class='patient-info'>
                    <table width='100%'>
                        <tr>
                            <td width='65%'>
                                <span class='label'>Patient:</span> <span class='value' style='font-size: 11pt;'>{$visit['first_name']} {$visit['last_name']}</span><br>
                                <span class='label'>Age/Sex:</span> <span class='value'>{$age} / {$gender}</span> | 
                                <span class='label'>UHID:</span> <span class='value'>{$visit['mrn']}</span>
                            </td>
                            <td width='35%' align='right'>
                                <span class='label'>Date:</span> <span class='value'>{$visitDate}</span><br>
                                <span class='label'>Time:</span> <span class='value'>{$visitTime}</span><br>
                                <span class='label'>Visit ID:</span> <span class='value'>#{$visitId}</span>
                            </td>
                        </tr>
                    </table>
                <!-- Chief Complaints -->
                <div class='section-header'>Chief Complaints</div>
                <div style='padding-left: 5px; color: #34495e;'>
                    " . ($siddha['note_text'] ?: 'Not recorded') . "
                </div>

                <!-- Vitals -->
                " . ($vitals ? "
                <div class='section-header'>Vitals</div>
                <div class='vitals-row'>
                    <span class='label'>BP:</span> <span class='value'>{$vitals['bp_systolic']}/{$vitals['bp_diastolic']} mmHg</span> &nbsp;&bull;&nbsp;
                    <span class='label'>Pulse:</span> <span class='value'>{$vitals['pulse_per_min']} bpm</span> &nbsp;&bull;&nbsp;
                    <span class='label'>Temp:</span> <span class='value'>{$vitals['temperature_c']} °C</span> &nbsp;&bull;&nbsp;
                    <span class='label'>SPO2:</span> <span class='value'>{$vitals['spo2']} %</span>
                </div>
                " : "") . "

                <!-- Diagnosis -->
                <div class='section-header'>Diagnosis / Provisional Diagnosis</div>
                <div style='padding-left: 5px; font-weight: bold; color: #c0392b;'>
                    " . ($diagnosis ?: 'Under investigation') . "
                </div>

                <!-- Medication -->
                <div class='section-header'>Medication Prescribed</div>
                " . (count($prescriptions) > 0 ? "
                <table class='rx-table'>
                    <thead>
                        <tr>
                            <th width='40%'>Medicine Name</th>
                            <th width='20%'>Dosage</th>
                            <th width='25%'>Frequency & Timing</th>
                            <th width='15%'>Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        " . array_reduce($prescriptions, function ($carry, $item) {
                return $carry . "<tr>
                                <td>
                                    <span style='font-weight: bold; color: #2c3e50;'>" . strtoupper(htmlspecialchars((string) $item['product_name'])) . "</span><br>
                                    <span style='font-size: 8pt; color: #7f8c8d;'>" . htmlspecialchars((string) ($item['notes'] ?? '')) . "</span>
                                </td>
                                <td>" . htmlspecialchars((string) ($item['dosage'] ?: '-')) . "</td>
                                <td>
                                    <span style='font-weight: bold;'>" . htmlspecialchars((string) ($item['frequency'] ?? '')) . "</span><br>
                                    <span style='font-size: 8pt; color: #16a085;'>Take Oral | " . htmlspecialchars((string) ($item['notes'] ?? '')) . "</span>
                                </td>
                                <td>" . htmlspecialchars((string) ($item['duration_days'] ?? '')) . " Days</td>
                            </tr>";
            }, "") . "
                    </tbody>
                </table>
                " : "<p style='color: #888;'>No medications prescribed.</p>") . "

                <!-- Follow up -->
                <div class='follow-up-box'>
                    <table width='100%'>
                        <tr>
                            <td>
                                <span style='font-weight: bold; color: #e74c3c; font-size: 11pt;'>Next Sitting Date: {$followUp}</span><br>
                                <span style='font-size: 8.5pt; color: #7f8c8d;'>Please bring this prescription for your next visit.</span>
                            </td>
                            <td align='right'>
                                <img src='https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode("MRN:{$visit['mrn']}|Visit:{$visitId}") . "' width='60' height='60' />
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Signature -->
                <div style='margin-top: 40px; text-align: right;'>
                    <div style='border-top: 1px solid #eee; display: inline-block; width: 200px; padding-top: 10px;'>
                        <span style='font-weight: bold;'>Dr. {$visit['provider_name']}</span><br>
                        <span style='font-size: 8.5pt; color: #7f8c8d;'>{$visit['specialization']}</span>
                    </div>
                </div>

            </body>
            </html>
            ";

            $mpdf->WriteHTML($html);
            $output = $mpdf->Output('', 'S');
            \System\Logger::info("Prescription PDF Generated successfully for Visit #{$visitId}. Size: " . strlen($output) . " bytes.");
            return $output;

        } catch (\Exception $e) {
            \System\Logger::error('Error generating Case Sheet PDF: ' . $e->getMessage());
            return null;
        }
    }

    public function getInvoicePdfContent(int $invoiceId): ?string
    {
        if (!$invoiceId)
            return null;

        try {
            // RELEASE SESSION LOCK if any (to avoid deadlock if view attempts session write)
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            // Fetch invoice data directly instead of requiring the view
            $invoice = $this->db->fetch(
                "SELECT i.*,
                        p.first_name, p.last_name, p.mrn, p.primary_mobile, p.address as p_address, p.age, p.gender,
                        d.full_name as doctor_name,
                        b.name as branch_name, b.address as b_address, b.phone as b_phone
                 FROM invoices i
                 LEFT JOIN patients p ON i.patient_id = p.patient_id
                 LEFT JOIN users d ON i.doctor_id = d.user_id
                 LEFT JOIN branches b ON i.branch_id = b.branch_id
                 WHERE i.invoice_id = ?",
                [$invoiceId]
            );

            if (!$invoice) {
                throw new \Exception("Invoice #$invoiceId not found");
            }

            // Fetch items
            $items = $this->db->fetchAll(
                "SELECT ii.*, p.name as product_name, p.hsn_code, ib.batch_no, ib.expiry_date
                 FROM invoice_items ii
                 LEFT JOIN products p ON ii.product_id = p.product_id
                 LEFT JOIN inventory_batches ib ON ii.batch_id = ib.batch_id
                 WHERE ii.invoice_id = ?",
                [$invoiceId]
            );

            $customerName = trim(($invoice['first_name'] ?? '') . ' ' . ($invoice['last_name'] ?? ''));
            $brandName = 'DIAWIN HEALTHCARE';
            $gstin = $invoice['gstin'] ?? '33AAAAA0000A1Z5';
            $dlno = $invoice['dl_no'] ?? '1234/20/21, 1236/20/21';
            $payMode = strtoupper($invoice['payment_mode'] ?? 'CASH');

            // Build QR data (no external API call - mPDF will generate it)
            $medicineList = [];
            foreach ($items as $item) {
                $medicineList[] = $item['product_name'] . " x " . $item['qty'];
            }
            $medsString = implode(", ", $medicineList);
            $qrData = "Bill: " . $invoice['invoice_no'] . " | Date: " . date('d/m/Y H:i', strtotime($invoice['created_at'])) . " | Pt: " . $customerName . " | Dr: " . ($invoice['doctor_name'] ?? 'Direct') . " | Pay: " . $payMode . " | Items: " . $medsString . " | Net: ₹" . number_format((float) $invoice['total_amount'], 2);

            // Generate HTML directly (Apollo-style pharmacy bill)
            $html = $this->generateInvoiceHTML($invoice, $items, $customerName, $brandName, $gstin, $dlno, $payMode, $qrData);

            // Configure mPDF
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'tempDir' => sys_get_temp_dir() . '/mpdf'
            ]);

            $mpdf->showImageErrors = false; // Don't show image errors in PDF

            $mpdf->SetTitle("Invoice #" . $invoice['invoice_no']);
            $mpdf->WriteHTML($html);

            $output = $mpdf->Output('', 'S');
            \System\Logger::info("Invoice PDF Generated for #$invoiceId. Size: " . strlen($output) . " bytes.");
            return $output;

        } catch (\Exception $e) {
            \System\Logger::error("Failed to generate Invoice PDF: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate Invoice HTML (Apollo-style pharmacy bill)
     */
    private function generateInvoiceHTML($invoice, $items, $customerName, $brandName, $gstin, $dlno, $payMode, $qrData): string
    {
        // Cast numeric values to float to avoid type errors
        $totalAmount = (float) ($invoice['total_amount'] ?? 0);
        $taxTotal = (float) ($invoice['tax_total'] ?? 0);
        $discountTotal = (float) ($invoice['discount_total'] ?? 0);

        // Number to words helper
        $amountInWords = $this->numberToWords($totalAmount);

        $itemsHtml = '';
        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $taxPercent = (float) ($item['tax_percent'] ?? 0);
            $amount = (float) ($item['amount'] ?? 0);

            $itemsHtml .= "<tr>
                <td class='text-center'>" . number_format($qty, 0) . "</td>
                <td class='fw-bold'>" . strtoupper(htmlspecialchars($item['description'])) . "</td>
                <td>Non</td>
                <td>" . ($item['hsn_code'] ?? '---') . "</td>
                <td>UNIC</td>
                <td>" . ($item['batch_no'] ?? 'N/A') . "</td>
                <td>" . date('d M y', strtotime($item['expiry_date'] ?? 'now')) . "</td>
                <td class='text-right'>" . number_format($unitPrice * (1 + $taxPercent / 100), 2) . "</td>
                <td class='text-right'>" . number_format($taxPercent, 1) . "%</td>
                <td class='text-right'>" . number_format($amount, 2) . "</td>
            </tr>";
        }

        // Add blank rows
        for ($i = count($items); $i < 4; $i++) {
            $itemsHtml .= "<tr><td colspan='10'>&nbsp;</td></tr>";
        }

        // Use absolute path for logo
        $logoPath = dirname(dirname(__DIR__)) . '/assets/images/brand-logos/toggle-logo.png';
        $logoExists = file_exists($logoPath);

        // If logo doesn't exist, try alternative path
        if (!$logoExists && defined('ROOT_PATH')) {
            $logoPath = ROOT_PATH . '/assets/images/brand-logos/toggle-logo.png';
            $logoExists = file_exists($logoPath);
        }

        \System\Logger::info("Logo path: $logoPath, exists: " . ($logoExists ? 'yes' : 'no'));

        // Convert logo to base64 for embedding in PDF
        $logoSrc = '';
        if ($logoExists) {
            $imageData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $imageData;
        }

        return "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #111; font-size: 11px; line-height: 1.3; margin: 0; padding: 0; }
        .ph-container { width: 100%; padding: 5mm; }
        .ph-header-table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
        .ph-header-table td { vertical-align: top; }
        .ph-logo { height: 60px; margin-bottom: 5px; }
        .ph-brand-name { font-size: 16px; font-weight: bold; color: #006837; }
        .ph-title { text-align: center; font-size: 20px; font-weight: bold; text-decoration: underline; margin: 5mm 0; letter-spacing: 2px; }
        .ph-details-table { width: 100%; border-top: 1.5px solid #333; border-bottom: 1.5px solid #333; margin-bottom: 3mm; padding: 1mm 0; border-collapse: collapse; }
        .ph-details-table td { padding: 1mm 2mm; font-size: 11px; vertical-align: top; }
        .ph-details-label { font-weight: bold; margin-right: 5px; }
        .ph-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .ph-table th { border-top: 1px solid #333; border-bottom: 1px solid #333; padding: 2mm 1mm; font-size: 11px; text-align: left; font-weight: bold; }
        .ph-table td { padding: 1.5mm 1mm; font-size: 11px; border-bottom: 0.5px solid #eee; }
        .ph-summary-table { width: 100%; border-top: 1.5px solid #333; margin-top: 2mm; border-collapse: collapse; }
        .ph-summary-table td { border-bottom: 1px solid #eee; padding: 1mm 0; font-weight: bold; text-align: center; }
        .ph-footer-table { width: 100%; margin-top: 5mm; border-collapse: collapse; }
        .ph-footer-table td { vertical-align: top; }
        .ph-terms { width: 60%; font-size: 10px; line-height: 1.4; }
        .ph-sign-box { width: 35%; text-align: right; }
        .bg-grey { background: #f9f9f9; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class='ph-container'>
        <!-- HEADER -->
        <table class='ph-header-table'>
            <tr>
                <td style='width: 25%;'>
                    " . ($logoExists ? "<img src='" . $logoSrc . "' class='ph-logo'>" : "") . "
                    <div class='ph-brand-box'>
                        <div class='ph-brand-name'>DIAWIN PHARMACY</div>
                        <div style='font-size: 10px; font-weight: bold;'>" . strtoupper($invoice['branch_name']) . "</div>
                        <div style='font-size: 9px;'>" . $invoice['b_address'] . "</div>
                        <div style='font-size: 9px;'>Phone: " . $invoice['b_phone'] . "</div>
                    </div>
                </td>
                <td style='width: 45%; padding-left: 20px;'>
                    <div style='font-size: 11px; line-height: 1.5;'>
                        <b>FSSAI No:</b> 13621011000118<br>
                        <b>D.L.No:</b> $dlno<br>
                        <b>GST No:</b> $gstin<br>
                        <b>CIN:</b> U52500TN2016PLC111328
                    </div>
                </td>
                <td style='width: 30%; font-size: 10px; line-height: 1.3;'>
                    <b>Registered Office:</b> No.19 Bishop Garden, Raja Annamalaipuram, Chennai-600028<br>
                    <b>Admin Office:</b> For All Correspondence, Ali Towers, 3rd Floor, No 55, Greams Road, Chennai - 600006, Tamil Nadu
                </td>
            </tr>
        </table>

        <div class='ph-title'>INVOICE</div>

        <!-- DETAILS TABLE -->
        <table class='ph-details-table'>
            <tr>
                <td style='width: 35%;'>
                    <span class='ph-details-label'>Name:</span>
                    <span class='ph-details-value'>" . strtoupper($customerName) . "</span>
                </td>
                <td style='width: 25%;'>
                    <span class='ph-details-label'>Mobile:</span>
                    <span class='ph-details-value'>" . $invoice['primary_mobile'] . "</span>
                </td>
                <td style='width: 20%;'>
                    <span class='ph-details-label'>Bill No:</span>
                    <span class='ph-details-value'>" . $invoice['invoice_no'] . "</span>
                </td>
                <td style='width: 20%;'>
                    <span class='ph-details-label'>Doctor:</span>
                    <span class='ph-details-value'>" . strtoupper($invoice['doctor_name'] ?? 'SELF') . "</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class='ph-details-label'>CORP:</span>
                    <span class='ph-details-value'>" . strtoupper($invoice['branch_name']) . "</span>
                </td>
                <td>
                    <span class='ph-details-label'>TID:</span>
                    <span class='ph-details-value'>002</span>
                </td>
                <td>
                    <span class='ph-details-label'>Bill Date:</span>
                    <span class='ph-details-value'>" . date('M j Y g:iA', strtotime($invoice['created_at'])) . "</span>
                </td>
                <td>
                    <span class='ph-details-label'>CIN:</span>
                    <span class='ph-details-value' style='font-size: 8px;'>U52500TN2016PLC111328</span>
                </td>
            </tr>
        </table>

        <!-- TABLE -->
        <table class='ph-table'>
            <thead>
                <tr>
                    <th style='width: 40px;'>Qty</th>
                    <th>Product Name</th>
                    <th style='width: 50px;'>SCH</th>
                    <th style='width: 80px;'>HSN Code</th>
                    <th style='width: 60px;'>Mfg</th>
                    <th style='width: 100px;'>Batch</th>
                    <th style='width: 80px;'>Expiry</th>
                    <th style='width: 70px;' class='text-right'>MRP</th>
                    <th style='width: 60px;' class='text-right'>GST%</th>
                    <th style='width: 70px;' class='text-right'>Amount</th>
                </tr>
            </thead>
            <tbody>
                $itemsHtml
                <tr>
                    <td colspan='10' class='text-center fw-bold' style='border-top: 1.5px solid #333; padding: 2mm 0;'>
                        Packing And Handling Charge: 0.00, SAC: 998549, GST: 18.00%
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- SUMMARY TABLE -->
        <table class='ph-summary-table'>
            <tr>
                <td>Taxable: ₹" . number_format($totalAmount - $taxTotal, 2) . "</td>
                <td>CGST: ₹" . number_format($taxTotal / 2, 2) . "</td>
                <td>SGST: ₹" . number_format($taxTotal / 2, 2) . "</td>
                <td>Gross: ₹" . number_format($totalAmount, 2) . "</td>
            </tr>
            <tr>
                <td colspan='2'>Discount: ₹" . number_format($discountTotal, 2) . "</td>
                <td colspan='2'>Savings: ₹" . number_format($discountTotal, 2) . "</td>
            </tr>
            <tr style='background: #eee;'>
                <td style='text-align: left; padding-left: 5px;'>You Saved ₹" . number_format($discountTotal, 2) . "</td>
                <td colspan='2' style='font-size: 10px;'>" . strtoupper($amountInWords) . "</td>
                <td style='text-align: right; padding-right: 5px; font-size: 16px;'>Paid: ₹" . number_format($totalAmount, 2) . "</td>
            </tr>
        </table>

        <!-- FOOTER TABLE -->
        <table class='ph-footer-table'>
            <tr>
                <td style='width: 65%;' class='ph-terms'>
                    Wishing You Speedy Recovery<br><br>
                    QR Code was digitally displayed to the<br>
                    Customer at the time of the transaction<br><br>
                    *1 HC equal to 1 Rupee
                </td>
                <td style='width: 35%; text-align: right;' class='ph-sign-box'>
                    <br><br><br>
                    for DIAWIN PHARMACY<br>
                    Registered Pharmacist
                </td>
            </tr>
        </table>

        <div style='text-align: center; font-size: 9px; margin-top: 10mm; border-top: 1px solid #eee; padding-top: 2mm;'>
            Goods Once Sold Cannot Be Taken Back or Exchanged INSULINS AND VACCINES WILL NOT BE TAKEN BACK EMERGENCY CALL: 1066 | HELPLINE: 18605000101
        </div>
    </div>
</body>
</html>";
    }

    /**
     * Convert number to words (Indian Style)
     */
    private function numberToWords($number): string
    {
        $no = (int) floor($number);
        $point = (int) round(($number - $no) * 100);
        $hundred = null;
        $digits_1 = strlen((string) $no);
        $i = 0;
        $str = array();
        $words = array(
            '0' => '',
            '1' => 'one',
            '2' => 'two',
            '3' => 'three',
            '4' => 'four',
            '5' => 'five',
            '6' => 'six',
            '7' => 'seven',
            '8' => 'eight',
            '9' => 'nine',
            '10' => 'ten',
            '11' => 'eleven',
            '12' => 'twelve',
            '13' => 'thirteen',
            '14' => 'fourteen',
            '15' => 'fifteen',
            '16' => 'sixteen',
            '17' => 'seventeen',
            '18' => 'eighteen',
            '19' => 'nineteen',
            '20' => 'twenty',
            '30' => 'thirty',
            '40' => 'forty',
            '50' => 'fifty',
            '60' => 'sixty',
            '70' => 'seventy',
            '80' => 'eighty',
            '90' => 'ninety'
        );
        $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');

        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str[] = ($number < 21) ? $words[$number] . " " . $digits[$counter] . $plural . " " . $hundred
                    : $words[floor($number / 10) * 10] . " " . $words[$number % 10] . " " . $digits[$counter] . $plural . " " . $hundred;
            } else {
                $str[] = null;
            }
        }
        $str = array_reverse($str);
        $result = implode('', $str);
        $points = ($point) ? "." . $words[floor($point / 10) * 10] . " " . $words[$point % 10] : '';
        return $result . "rupees " . $points . " only";
    }
}

