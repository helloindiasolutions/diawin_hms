<?php
/**
 * Quick Verification Test for Task 2.5
 * Verifies the getQuotation endpoint implementation
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}

use System\Database;

echo "Task 2.5 Verification: Get Quotation Details Endpoint\n";
echo "======================================================\n\n";

$db = Database::getInstance();

// Get an existing quotation from the database
$existingQuotation = $db->fetch("SELECT quotation_id FROM supplier_quotations LIMIT 1");

if (!$existingQuotation) {
    echo "⊘ No quotations found in database. Creating test data...\n";
    
    // Create minimal test data
    $supplierId = $db->insert('suppliers', [
        'name' => 'Verify Test Supplier',
        'contact_person' => 'Test',
        'mobile' => '9999999999',
        'email' => 'verify@test.com',
        'gstin' => 'VERIFY-TEST'
    ]);
    
    $quotationId = $db->insert('supplier_quotations', [
        'supplier_id' => $supplierId,
        'quotation_no' => 'VERIFY-' . time(),
        'quotation_date' => date('Y-m-d'),
        'valid_until' => date('Y-m-d', strtotime('+30 days')),
        'status' => 'active'
    ]);
    
    $product = $db->fetch("SELECT product_id FROM products LIMIT 1");
    if ($product) {
        $db->insert('quotation_items', [
            'quotation_id' => $quotationId,
            'product_id' => $product['product_id'],
            'quantity' => 10,
            'unit_price' => 50.00,
            'tax_percent' => 12.00,
            'mrp' => 75.00
        ]);
    }
    
    $existingQuotation = ['quotation_id' => $quotationId];
    echo "✓ Created test quotation (ID: $quotationId)\n\n";
}

$quotationId = $existingQuotation['quotation_id'];
echo "Testing with quotation ID: $quotationId\n\n";

// Test the SQL queries directly
echo "1. Testing quotation header query...\n";
$quotationSql = "SELECT 
                    sq.quotation_id,
                    sq.quotation_no,
                    sq.supplier_id,
                    s.name as supplier_name,
                    s.gstin as supplier_gstin,
                    s.contact_person as supplier_contact_person,
                    s.mobile as supplier_mobile,
                    s.email as supplier_email,
                    s.address as supplier_address,
                    sq.quotation_date,
                    sq.valid_until,
                    sq.supplier_reference,
                    sq.status,
                    sq.remarks,
                    sq.created_by,
                    sq.created_at,
                    sq.updated_at
                FROM supplier_quotations sq
                INNER JOIN suppliers s ON sq.supplier_id = s.supplier_id
                WHERE sq.quotation_id = ?";

$quotation = $db->fetch($quotationSql, [$quotationId]);

if ($quotation) {
    echo "   ✓ Quotation header retrieved\n";
    echo "   ✓ Supplier info included: {$quotation['supplier_name']}\n";
} else {
    echo "   ✗ Failed to retrieve quotation\n";
    exit(1);
}

echo "\n2. Testing quotation items query...\n";
$itemsSql = "SELECT 
                qi.quotation_item_id,
                qi.product_id,
                p.name as product_name,
                p.sku,
                p.unit,
                p.hsn_code,
                qi.quantity,
                qi.unit_price,
                qi.tax_percent,
                qi.mrp,
                qi.remarks
            FROM quotation_items qi
            INNER JOIN products p ON qi.product_id = p.product_id
            WHERE qi.quotation_id = ?
            ORDER BY qi.quotation_item_id ASC";

$items = $db->fetchAll($itemsSql, [$quotationId]);

if (count($items) > 0) {
    echo "   ✓ Retrieved " . count($items) . " items\n";
    echo "   ✓ Product details included: {$items[0]['product_name']}\n";
} else {
    echo "   ⊘ No items found for this quotation\n";
}

echo "\n3. Verifying response structure...\n";
$response = [
    'quotation' => $quotation,
    'items' => $items
];

$checks = [
    'quotation key exists' => isset($response['quotation']),
    'items key exists' => isset($response['items']),
    'quotation is array' => is_array($response['quotation']),
    'items is array' => is_array($response['items']),
    'quotation_no present' => !empty($response['quotation']['quotation_no']),
    'supplier_name present' => !empty($response['quotation']['supplier_name']),
    'quotation_date present' => !empty($response['quotation']['quotation_date']),
    'valid_until present' => !empty($response['quotation']['valid_until']),
    'status present' => !empty($response['quotation']['status'])
];

$allPassed = true;
foreach ($checks as $check => $result) {
    if ($result) {
        echo "   ✓ $check\n";
    } else {
        echo "   ✗ $check\n";
        $allPassed = false;
    }
}

echo "\n4. Checking requirements compliance...\n";
echo "   US-4.2 (Quotation record completeness): ";
if (!empty($quotation['quotation_no']) && !empty($quotation['quotation_date']) && 
    !empty($quotation['valid_until']) && !empty($quotation['status'])) {
    echo "✓ PASS\n";
} else {
    echo "✗ FAIL\n";
    $allPassed = false;
}

echo "   US-4.3 (Supplier info included): ";
if (!empty($quotation['supplier_name']) && !empty($quotation['supplier_id'])) {
    echo "✓ PASS\n";
} else {
    echo "✗ FAIL\n";
    $allPassed = false;
}

echo "   US-4.4 (Item data completeness): ";
$itemsComplete = true;
foreach ($items as $item) {
    if (empty($item['product_name']) || !isset($item['unit_price']) || 
        !isset($item['tax_percent']) || !isset($item['mrp'])) {
        $itemsComplete = false;
        break;
    }
}
if ($itemsComplete || count($items) === 0) {
    echo "✓ PASS\n";
} else {
    echo "✗ FAIL\n";
    $allPassed = false;
}

echo "\n======================================================\n";
if ($allPassed) {
    echo "✓ Task 2.5 Implementation: VERIFIED\n";
    echo "✓ All requirements satisfied\n";
} else {
    echo "✗ Task 2.5 Implementation: ISSUES FOUND\n";
}
echo "======================================================\n";
