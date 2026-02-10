<?php
use System\Database;
use System\Security;

$db = Database::getInstance();
$idInput = $_GET['id'] ?? '';

// Handle Encrypted ID (for secure WhatsApp links)
if (!is_numeric($idInput) && !empty($idInput)) {
    $invoiceId = (int) Security::decrypt($idInput);
} else {
    $invoiceId = (int) $idInput;
}

$type = $_GET['type'] ?? 'pharmacy'; // 'pharmacy' or 'thermal'

if (!$invoiceId) {
    die("Invalid Invoice ID");
}

/* =========================
   FETCH INVOICE
   ========================= */
$invoice = $db->fetch(
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
    die("Invoice not found");
}

/* =========================
   FETCH ITEMS
   ========================= */
$items = $db->fetchAll(
    "SELECT ii.*, p.name as product_name, p.hsn_code, ib.batch_no, ib.expiry_date
     FROM invoice_items ii
     LEFT JOIN products p ON ii.product_id = p.product_id
     LEFT JOIN inventory_batches ib ON ii.batch_id = ib.batch_id
     WHERE ii.invoice_id = ?",
    [$invoiceId]
);

$customerName = trim(($invoice['first_name'] ?? '') . ' ' . ($invoice['last_name'] ?? ''));

/* =========================
   BRAND / LICENSE FALLBACKS
   ========================= */
$brandName = 'DIAWIN HEALTHCARE';
$gstin = $invoice['gstin'] ?? '33AAAAA0000A1Z5';
$dlno = $invoice['dl_no'] ?? '1234/20/21, 1236/20/21';

// QR Information
$medicineList = [];
foreach ($items as $item) {
    $medicineList[] = $item['product_name'] . " x " . $item['qty'];
}
$medsString = implode(", ", $medicineList);

$payMode = strtoupper($invoice['payment_mode'] ?? 'CASH');
$qrData = "Bill: " . $invoice['invoice_no'] . " | Date: " . date('d/m/Y H:i', strtotime($invoice['created_at'])) . " | Pt: " . $customerName . " | Dr: " . ($invoice['doctor_name'] ?? 'Direct') . " | Pay: " . $payMode . " | Items: " . $medsString . " | Net: ₹" . number_format($invoice['total_amount'], 2);
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" . urlencode($qrData);

// WhatsApp logic removed - handled by BillingController service

/**
 * Helper to convert number to words (Indian Style)
 */
if (!function_exists('numberToWords')) {
    function numberToWords($number)
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
                $str[] = ($number < 21) ? $words[$number] .
                    " " . $digits[$counter] . $plural . " " . $hundred
                    :
                    $words[floor($number / 10) * 10]
                    . " " . $words[$number % 10] . " "
                    . $digits[$counter] . $plural . " " . $hundred;
            } else
                $str[] = null;
        }
        $str = array_reverse($str);
        $result = implode('', $str);
        $points = ($point) ?
            "." . $words[floor($point / 10) * 10] . " " .
            $words[$point % 10] : '';
        return $result . "rupees  " . $points . " only";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $invoice['invoice_no'] ?> - <?= $brandName ?></title>

    <!-- WhatsApp / Open Graph Preview Tags -->
    <meta property="og:title" content="Invoice #<?= $invoice['invoice_no'] ?> | <?= $brandName ?>">
    <meta property="og:description"
        content="Secure Digital Invoice for <?= $customerName ?>. Total: ₹<?= number_format($invoice['total_amount'], 2) ?>">
    <meta property="og:image" content="<?= asset('images/brand-logos/toggle-logo.png') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url"
        content="<?= (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>">

    <!-- Favicon or icon can be added here -->
    <style>
        @media screen {
            body {
                background: #eef2f5 !important;
            }

            .thermal-container {
                background: white;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                margin: 20px auto !important;
            }

            .ph-container {
                background: white;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
                margin: 20px auto !important;
                max-width: 280mm;
            }
        }

        /* DYNAMIC PAGE SIZING */
        <?php if ($type === 'thermal'): ?>
            @page {
                size: 80mm auto;
                margin: 0;
                orientation: portrait;
            }

            body {
                width: 80mm;
                background: #fff;
                padding: 0;
                margin: 0;
                overflow-x: hidden;
            }

        <?php else: ?>
            @page {
                size: A4 landscape;
                margin: 5mm;
            }

            body {
                width: 280mm;
                background: #fff;
                padding: 0;
                margin: 0;
            }

        <?php endif; ?>

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #111;
            font-size: 11px;
        }

        /* THERMAL SPECIFIC STYLES */
        .thermal-container {
            width: 76mm;
            margin: 0 auto;
            padding: 5mm 2mm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.2;
        }

        .thermal-header {
            text-align: center;
            margin-bottom: 4mm;
        }

        .thermal-brand {
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .thermal-dashed-line {
            border-top: 1px dashed #000;
            margin: 2mm 0;
        }

        .thermal-table {
            width: 100%;
            border-collapse: collapse;
        }

        .thermal-table th {
            border-bottom: 1px solid #000;
            text-align: left;
            padding: 1mm 0;
            font-size: 10px;
        }

        .thermal-table td {
            padding: 1.5mm 0;
            vertical-align: top;
        }

        .thermal-net-total {
            font-size: 22px;
            font-weight: 900;
            margin-top: 2mm;
            text-align: right;
        }

        /* APOLLO STYLE PHARMACY BILL */
        .ph-container {
            width: 100%;
            padding: 5mm;
        }

        line-height: 1.3;
        }

        .ph-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5mm;
        }

        .ph-header-table td {
            vertical-align: top;
        }

        .ph-logo {
            height: 60px;
            margin-bottom: 5px;
        }

        .ph-brand-box {
            text-align: left;
        }

        .ph-brand-name {
            font-size: 16px;
            font-weight: bold;
            color: #006837;
        }

        .ph-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            text-decoration: underline;
            margin: 5mm 0;
            letter-spacing: 2px;
        }

        .ph-details-table {
            width: 100%;
            border-top: 1.5px solid #333;
            border-bottom: 1.5px solid #333;
            margin-bottom: 3mm;
            padding: 1mm 0;
            border-collapse: collapse;
        }

        .ph-details-table td {
            padding: 1mm 2mm;
            font-size: 11px;
            vertical-align: top;
        }

        .ph-details-label {
            font-weight: bold;
            margin-right: 5px;
        }

        .ph-details-value {
            font-weight: normal;
        }

        .ph-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .ph-table th {
            border-top: 1px solid #333;
            border-bottom: 1px solid #333;
            padding: 2mm 1mm;
            font-size: 11px;
            text-align: left;
            font-weight: bold;
        }

        .ph-table td {
            padding: 1.5mm 1mm;
            font-size: 11px;
            border-bottom: 0.5px solid #eee;
        }

        .ph-table-header {
            background: #fff;
        }

        .ph-summary-table {
            width: 100%;
            border-top: 1.5px solid #333;
            margin-top: 2mm;
            border-collapse: collapse;
        }

        .ph-summary-table td {
            border-bottom: 1px solid #eee;
            padding: 1mm 0;
            font-weight: bold;
            text-align: center;
        }

        .ph-footer-table {
            width: 100%;
            margin-top: 5mm;
            border-collapse: collapse;
        }

        .ph-footer-table td {
            vertical-align: top;
        }

        .ph-terms {
            width: 60%;
            font-size: 10px;
            line-height: 1.4;
        }

        .ph-sign-box {
            width: 35%;
            text-align: right;
        }

        .bg-grey {
            background: #f9f9f9;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .fw-bold {
            font-weight: bold;
        }
    </style>
</head>

<body onload="window.print()">


    <?php if ($type === 'thermal'): ?>
        <div class="thermal-container">
            <!-- HEADER -->
            <div class="thermal-header">
                <div class="thermal-brand">DIAWIN HEALTHCARE</div>
                <div style="font-weight: bold; font-size: 13px;">
                    <?= strtoupper(htmlspecialchars($invoice['branch_name'])) ?>
                </div>
                <div>ID: B-<?= $invoice['branch_id'] ?> | <?= htmlspecialchars($invoice['b_address']) ?></div>
                <div>PHONE: <?= htmlspecialchars($invoice['b_phone']) ?></div>
            </div>
            <div class="thermal-dashed-line"></div>
            <div style="display: flex; justify-content: space-between;">
                <span>BILL: <?= $invoice['invoice_no'] ?></span>
                <span>DATE: <?= date('d/m/Y', strtotime($invoice['created_at'])) ?></span>
            </div>
            <div style="margin-top: 1mm;">
                <b>PATIENT :</b> <?= strtoupper(htmlspecialchars($customerName)) ?><br>
                <b>DOCTOR :</b> <?= strtoupper(htmlspecialchars($invoice['doctor_name'] ?? 'DIRECT')) ?><br>
                <b>PAY MODE:</b> <?= $payMode ?>
            </div>
            <div class="thermal-dashed-line"></div>
            <table class="thermal-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">ITEM</th>
                        <th style="text-align: center;">QTY</th>
                        <th style="text-align: right;">PRICE</th>
                        <th style="text-align: right;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?= strtoupper(htmlspecialchars($item['description'])) ?><br>
                                <span style="font-size: 8px;">Tax: <?= $item['tax_percent'] ?>%</span>
                            </td>
                            <td style="text-align: center;"><?= number_format($item['qty'], 2) ?></td>
                            <td style="text-align: right;"><?= number_format($item['unit_price'], 2) ?></td>
                            <td style="text-align: right; font-weight: bold;"><?= number_format($item['amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="border-top: 1px solid #000; padding-top: 2mm;">
                <div class="thermal-net-total">
                    <span style="font-size: 13px; font-weight: normal; vertical-align: middle;">NET PAYABLE</span>
                    ₹<?= number_format($invoice['total_amount'], 2) ?>
                </div>
            </div>
            <div style="text-align: center; margin-top: 5mm;">
                <img src="<?= $qrUrl ?>" style="width: 100px; height: 100px;">
                <div style="font-size: 9px; margin-top: 1mm;">Scan for patient/invoice details</div>
                <div style="font-weight: bold; margin-top: 4mm; font-size: 12px;">*** THANK YOU ! SEE YOU AGAIN ***</div>
            </div>
        </div>

    <?php else: ?>
        <!-- APOLLO STYLE PHARMACY BILL -->
        <div class="ph-container">
            <!-- HEADER -->
            <table class="ph-header-table">
                <tr>
                    <td style="width: 25%;">
                        <img src="/assets/images/brand-logos/toggle-logo.png" class="ph-logo">
                        <div class="ph-brand-box">
                            <div class="ph-brand-name">DIAWIN PHARMACY</div>
                            <div style="font-size: 10px; font-weight: bold;"><?= strtoupper($invoice['branch_name']) ?>
                            </div>
                            <div style="font-size: 9px;"><?= $invoice['b_address'] ?></div>
                            <div style="font-size: 9px;">Phone: <?= $invoice['b_phone'] ?></div>
                        </div>
                    </td>
                    <td style="width: 45%; padding-left: 20px;">
                        <div style="font-size: 11px; line-height: 1.5;">
                            <b>FSSAI No:</b> 13621011000118<br>
                            <b>D.L.No:</b> <?= $dlno ?><br>
                            <b>GST No:</b> <?= $gstin ?><br>
                            <b>CIN:</b> U52500TN2016PLC111328
                        </div>
                    </td>
                    <td style="width: 30%; font-size: 10px; line-height: 1.3;">
                        <b>Registered Office:</b> No.19 Bishop Garden, Raja Annamalaipuram, Chennai-600028<br>
                        <b>Admin Office:</b> For All Correspondence, Ali Towers, 3rd Floor, No 55, Greams Road, Chennai -
                        600006, Tamil Nadu
                    </td>
                </tr>
            </table>

            <div class="ph-title">INVOICE</div>

            <!-- DETAILS TABLE -->
            <table class="ph-details-table">
                <tr>
                    <td style="width: 35%;">
                        <span class="ph-details-label">Name:</span>
                        <span class="ph-details-value"><?= strtoupper($customerName) ?></span>
                    </td>
                    <td style="width: 25%;">
                        <span class="ph-details-label">Mobile:</span>
                        <span class="ph-details-value"><?= $invoice['primary_mobile'] ?></span>
                    </td>
                    <td style="width: 20%;">
                        <span class="ph-details-label">Bill No:</span>
                        <span class="ph-details-value"><?= $invoice['invoice_no'] ?></span>
                    </td>
                    <td style="width: 20%;">
                        <span class="ph-details-label">Doctor:</span>
                        <span class="ph-details-value"><?= strtoupper($invoice['doctor_name'] ?? 'SELF') ?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="ph-details-label">CORP:</span>
                        <span class="ph-details-value"><?= strtoupper($invoice['branch_name']) ?></span>
                    </td>
                    <td>
                        <span class="ph-details-label">TID:</span>
                        <span class="ph-details-value">002</span>
                    </td>
                    <td>
                        <span class="ph-details-label">Bill Date:</span>
                        <span class="ph-details-value"><?= date('M j Y g:iA', strtotime($invoice['created_at'])) ?></span>
                    </td>
                    <td>
                        <span class="ph-details-label">CIN:</span>
                        <span class="ph-details-value" style="font-size: 8px;">U52500TN2016PLC111328</span>
                    </td>
                </tr>
            </table>

            <!-- TABLE -->
            <table class="ph-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">Qty</th>
                        <th>Product Name</th>
                        <th style="width: 50px;">SCH</th>
                        <th style="width: 80px;">HSN Code</th>
                        <th style="width: 60px;">Mfg</th>
                        <th style="width: 100px;">Batch</th>
                        <th style="width: 80px;">Expiry</th>
                        <th style="width: 70px;" class="text-right">MRP</th>
                        <th style="width: 60px;" class="text-right">GST%</th>
                        <th style="width: 70px;" class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="text-center"><?= number_format($item['qty'], 0) ?></td>
                            <td class="fw-bold"><?= strtoupper($item['description']) ?></td>
                            <td>Non</td>
                            <td><?= $item['hsn_code'] ?? '---' ?></td>
                            <td>UNIC</td>
                            <td><?= $item['batch_no'] ?></td>
                            <td><?= date('d M y', strtotime($item['expiry_date'] ?? 'now')) ?></td>
                            <td class="text-right">
                                <?= number_format($item['unit_price'] * (1 + $item['tax_percent'] / 100), 2) ?>
                            </td>
                            <td class="text-right"><?= number_format($item['tax_percent'], 1) ?>%</td>
                            <td class="text-right"><?= number_format($item['amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Blank rows to keep structure -->
                    <?php for ($i = count($items); $i < 4; $i++): ?>
                        <tr>
                            <td colspan="10">&nbsp;</td>
                        </tr>
                    <?php endfor; ?>

                    <tr>
                        <td colspan="10" class="text-center fw-bold" style="border-top: 1.5px solid #333; padding: 2mm 0;">
                            Packing And Handling Charge: 0.00, SAC: 998549, GST: 18.00%
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- SUMMARY TABLE -->
            <table class="ph-summary-table">
                <tr>
                    <td>Taxable: ₹<?= number_format($invoice['total_amount'] - $invoice['tax_total'], 2) ?></td>
                    <td>CGST: ₹<?= number_format($invoice['tax_total'] / 2, 2) ?></td>
                    <td>SGST: ₹<?= number_format($invoice['tax_total'] / 2, 2) ?></td>
                    <td>Gross: ₹<?= number_format($invoice['total_amount'], 2) ?></td>
                </tr>
                <tr>
                    <td colspan="2">Discount: ₹<?= number_format($invoice['discount_total'] ?? 0, 2) ?></td>
                    <td colspan="2">Savings: ₹<?= number_format($invoice['discount_total'] ?? 0, 2) ?></td>
                </tr>
                <tr style="background: #eee;">
                    <td style="text-align: left; padding-left: 5px;">You Saved
                        ₹<?= number_format($invoice['discount_total'] ?? 0, 2) ?></td>
                    <td colspan="2" style="font-size: 10px;"><?= strtoupper(numberToWords($invoice['total_amount'])) ?></td>
                    <td style="text-align: right; padding-right: 5px; font-size: 16px;">Paid:
                        ₹<?= number_format($invoice['total_amount'], 2) ?></td>
                </tr>
            </table>

            <!-- FOOTER TABLE -->
            <table class="ph-footer-table">
                <tr>
                    <td style="width: 65%;" class="ph-terms">
                        Wishing You Speedy Recovery<br><br>
                        QR Code was digitally displayed to the<br>
                        Customer at the time of the transaction<br><br>
                        *1 HC equal to 1 Rupee<br>
                        <img src="<?= $qrUrl ?>"
                            style="width: 70px; height: 70px; border: 1px solid #ccc; padding: 2px; margin-top: 10px;">
                    </td>
                    <td style="width: 35%; text-align: right;" class="ph-sign-box">
                        <br><br><br>
                        for DIAWIN PHARMACY<br>
                        Registered Pharmacist
                    </td>
                </tr>
            </table>

            <div
                style="text-align: center; font-size: 9px; margin-top: 10mm; border-top: 1px solid #eee; padding-top: 2mm;">
                Goods Once Sold Cannot Be Taken Back or Exchanged INSULINS AND VACCINES WILL NOT BE TAKEN BACK EMERGENCY
                CALL: 1066 | HELPLINE: 18605000101
            </div>
        </div>
    <?php endif; ?>

</body>

</html>