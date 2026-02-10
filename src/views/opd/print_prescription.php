<?php
use System\Database;
use System\Security;

// Autoload (if not already handled by route inclusion)
require_once __DIR__ . '/../../../system/autoload.php';

$db = Database::getInstance();
$idInput = $_GET['visit_id'] ?? '';

// Handle Encrypted ID
if (!is_numeric($idInput) && !empty($idInput)) {
    $visitId = (int) Security::decrypt($idInput);
} else {
    $visitId = (int) $idInput;
}

if (!$visitId) {
    die("Invalid Visit ID");
}

/* =========================
   FETCH VISIT DETAILS
   ========================= */
$visit = $db->fetch(
    "SELECT v.*, 
            p.first_name, p.last_name, p.mrn, p.primary_mobile, p.age, p.gender,
            doc.full_name as doctor_name, doc.specialization as doctor_qual, doc.license_no as doctor_reg,
            b.name as branch_name, b.address as branch_address, b.phone as branch_phone
     FROM visits v
     LEFT JOIN patients p ON v.patient_id = p.patient_id
     LEFT JOIN providers doc ON v.primary_provider_id = doc.provider_id
     LEFT JOIN branches b ON v.branch_id = b.branch_id
     WHERE v.visit_id = ?",
    [$visitId]
);

if (!$visit) {
    die("Visit not found");
}

/* =========================
   FETCH PRESCRIPTION
   ========================= */
// Fetch latest prescription for this visit
$prescription = $db->fetch(
    "SELECT * FROM prescriptions WHERE visit_id = ? ORDER BY prescribed_at DESC LIMIT 1",
    [$visitId]
);

$items = [];
if ($prescription) {
    $items = $db->fetchAll(
        "SELECT pi.*, prod.name as product_name
         FROM prescription_items pi
         LEFT JOIN products prod ON pi.product_id = prod.product_id
         WHERE pi.prescription_id = ?",
        [$prescription['prescription_id']]
    );
}

/* =========================
   FETCH CLINICAL NOTES
   ========================= */
$notes = $db->fetch("SELECT * FROM siddha_notes WHERE visit_id = ?", [$visitId]);

// Meta for WhatsApp Link Preview
$docName = $visit['doctor_name'] ?? 'Doctor';
$ptName = trim(($visit['first_name'] ?? '') . ' ' . ($visit['last_name'] ?? ''));
$brandName = $visit['branch_name'] ?? 'DIAWIN';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription #<?= $visitId ?> | <?= $brandName ?></title>

    <!-- Open Graph Tags -->
    <meta property="og:title" content="Prescription for <?= $ptName ?> | <?= $brandName ?>">
    <meta property="og:description"
        content="Prescribed by <?= $docName ?> on <?= date('d M Y', strtotime($visit['visit_start'])) ?>">
    <meta property="og:image" content="<?= asset('images/brand-logos/toggle-logo.png') ?>">
    <meta property="og:type" content="website">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #525659;
            margin: 0;
            padding: 20px;
            color: #1e293b;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .page-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 0;
            min-height: 297mm;
            position: relative;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .page-container {
                box-shadow: none;
                margin: 0;
                width: 100%;
                height: 100%;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Header */
        .rx-header {
            padding: 25px 40px;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .doc-info h1 {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 4px 0;
            color: #0f172a;
        }

        .doc-info p {
            margin: 0;
            font-size: 11px;
            color: #64748b;
            line-height: 1.4;
        }

        .clinic-info {
            text-align: right;
        }

        .clinic-info h2 {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 4px 0;
            color: #3b82f6;
        }

        .clinic-info p {
            margin: 0;
            font-size: 11px;
            color: #64748b;
            line-height: 1.4;
        }

        /* Patient Info Bar */
        .pt-info-bar {
            background: #f8fafc;
            padding: 12px 40px;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #e2e8f0;
        }

        .pt-detail {
            font-size: 12px;
        }

        .pt-detail span {
            display: block;
        }

        .pt-label {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .pt-value {
            font-weight: 600;
            color: #334155;
        }

        /* Content Area */
        .rx-content {
            padding: 30px 40px;
        }

        /* Diagnosis Section */
        .diagnosis-box {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #e2e8f0;
        }

        .section-title {
            font-size: 11px;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 8px;
            display: block;
        }

        .diagnosis-text {
            font-size: 14px;
            font-weight: 500;
            color: #0f172a;
        }

        /* Medicine Table */
        .rx-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .rx-table th {
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
        }

        .rx-table td {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            font-size: 12px;
            color: #334155;
        }

        .rx-table tr:last-child td {
            border-bottom: none;
        }

        .med-name {
            font-weight: 600;
            color: #0f172a;
            font-size: 13px;
            margin-bottom: 2px;
            display: block;
        }

        .med-inst {
            font-size: 11px;
            color: #64748b;
            font-style: italic;
        }

        .dose-badge {
            display: inline-block;
            background: #fff;
            border: 1px solid #cbd5e1;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            margin-right: 5px;
        }

        /* Footer */
        .rx-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px 40px 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .signature-box {
            text-align: right;
        }

        .sign-line {
            width: 150px;
            border-top: 1px solid #334155;
            margin-bottom: 5px;
            margin-left: auto;
        }

        .doc-sign-name {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            display: block;
        }

        .disclaimer {
            font-size: 9px;
            color: #94a3b8;
            margin-top: 5px;
            max-width: 200px;
            line-height: 1.3;
        }

        .qr-box {
            border: 1px solid #e2e8f0;
            padding: 4px;
            width: 60px;
            height: 60px;
            display: inline-block;
        }

        .qr-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Vitals Grid */
        .vitals-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
            background: #fdfdfd;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #f1f5f9;
        }

        .vital-item {
            text-align: center;
        }

        .vital-label {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }

        .vital-val {
            font-size: 12px;
            font-weight: 600;
            color: #334155;
        }

        /* Print Controls */
        .print-controls {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            padding: 10px 20px;
            border-radius: 30px;
            display: flex;
            gap: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        .btn-ctrl {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 13px;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 20px;
            transition: 0.2s;
        }

        .btn-ctrl:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .btn-ctrl svg {
            margin-right: 6px;
            width: 16px;
            height: 16px;
        }
    </style>
</head>

<body>

    <div class="page-container">
        <!-- Header -->
        <div class="rx-header">
            <div class="doc-info">
                <h1>Dr. <?= $visit['doctor_name'] ?></h1>
                <p><?= $visit['doctor_qual'] ?><br>Reg No: <?= $visit['doctor_reg'] ?></p>
            </div>
            <div class="clinic-info">
                <h2><?= $visit['branch_name'] ?></h2>
                <p><?= $visit['branch_address'] ?><br>Ph: <?= $visit['branch_phone'] ?></p>
            </div>
        </div>

        <!-- Patient Info -->
        <div class="pt-info-bar">
            <div class="pt-detail">
                <span class="pt-label">Patient Name</span>
                <span class="pt-value"><?= $ptName ?></span>
            </div>
            <div class="pt-detail">
                <span class="pt-label">Age / Gender</span>
                <span class="pt-value"><?= $visit['age'] ?> Yrs / <?= ucfirst($visit['gender']) ?></span>
            </div>
            <div class="pt-detail">
                <span class="pt-label">Visit ID</span>
                <span class="pt-value">#<?= $visit['visit_id'] ?></span>
            </div>
            <div class="pt-detail" style="text-align: right;">
                <span class="pt-label">Date</span>
                <span class="pt-value"><?= date('d M Y, h:i A', strtotime($visit['visit_start'])) ?></span>
            </div>
        </div>

        <!-- Content -->
        <div class="rx-content">

            <!-- Diagnosis / Vitals -->
            <?php if (!empty($notes['anupanam']) || !empty($notes['note_text'])): ?>
                <div class="diagnosis-box">
                    <?php if (!empty($notes['anupanam'])): ?>
                        <div style="margin-bottom: 10px;">
                            <span class="section-title">Diagnosis / Provisional</span>
                            <div class="diagnosis-text"><?= $notes['anupanam'] ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($notes['note_text'])): ?>
                        <div>
                            <span class="section-title">Chief Complaints / Symptoms</span>
                            <div style="font-size: 12px; color: #475569;"><?= $notes['note_text'] ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Rx Symbol -->
            <div style="font-size: 24px; font-weight: 700; font-family: serif; color: #0f172a; margin-bottom: 10px;">Rx
            </div>

            <!-- Medicines Table -->
            <?php if (empty($items)): ?>
                <div style="text-align: center; padding: 40px; color: #94a3b8; font-size: 13px; font-style: italic;">
                    No medicines prescribed in this visit.
                </div>
            <?php else: ?>
                <table class="rx-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Medicine Name</th>
                            <th style="width: 20%;">Dosage</th>
                            <th style="width: 15%;">Days</th>
                            <th style="width: 15%; text-align: center;">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $idx => $item): ?>
                            <tr>
                                <td>
                                    <span class="med-name"><?= $idx + 1 ?>. <?= $item['product_name'] ?></span>
                                    <span class="med-inst"><?= $item['notes'] ? $item['notes'] : 'As directed' ?></span>
                                </td>
                                <td>
                                    <span class="dose-badge"><?= $item['frequency'] ?></span>
                                </td>
                                <td>
                                    <?= $item['duration_days'] ? $item['duration_days'] . ' Days' : '-' ?>
                                </td>
                                <td style="text-align: center; font-weight: 600;">
                                    <?= $item['quantity'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        </div>

        <!-- Footer -->
        <div class="rx-footer">
            <div style="display:flex; gap:15px; align-items:flex-end;">
                <div class="qr-box">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode("Rx ID: " . $visitId) ?>"
                        alt="QR">
                </div>
                <div>
                    <p class="disclaimer">Use this prescription within 30 days of issue. Substitutes allowed if
                        prescribed brand unavailable.</p>
                </div>
            </div>

            <div class="signature-box">
                <div class="sign-line"></div>
                <span class="doc-sign-name">Dr. <?= $visit['doctor_name'] ?></span>
                <span style="font-size: 9px; color: #94a3b8;">(Electronically Signed)</span>
            </div>
        </div>
    </div>

    <!-- Controls -->
    <div class="print-controls no-print">
        <button class="btn-ctrl" onclick="window.print()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                </path>
            </svg>
            Print Rx
        </button>
    </div>

</body>

</html>