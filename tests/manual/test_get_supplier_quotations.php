<?php
/**
 * Manual Test Script for Get Supplier Quotations Endpoint
 * Tests task 2.3: GET /api/v1/quotations/supplier/{id}
 * 
 * Usage: php tests/manual/test_get_supplier_quotations.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment
if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}

use System\Database;

echo "==============================================\n";
echo "Testing Get Supplier Quotations Endpoint\n";
echo "Task 2.3: GET /api/v1/quotations/supplier/{id}\n";
echo "==============================================\n\n";

$db = Database::getInstance();

// Setup test data
echo "Setting up test data...\n";

// Create test supplier
$testSupplierId = (int)$db->insert('suppliers', [
    'name' => 'Manual Test Supplier',
    'contact_person' => 'Test Contact',
    'mobile' => '9876543210',
    'email' => 'manual@test.com',
    'gstin' => 'MANUAL-TEST-GSTIN',
    'address' => '123 Test Street'
]);

echo "✓ Created test supplier (ID: $testSupplierId)\n";

// Get some products
$products = $db->fetchAll("SELECT product_id FROM products LIMIT 3");
if (empty($products)) {
    echo "✗ No products found in database. Please add products first.\n";
    exit(1);
}
$productIds = array_column($products, 'product_id');
echo "✓ Found " . count($productIds) . " products\n";

// Create test quotations
$quotationIds = [];

$quotation1Id = (int)$db->insert('supplier_quotations', [
    'supplier_id' => $testSupplierId,
    'quotation_no' => 'MANUAL-QT-001',
    'quotation_date' => date('Y-m-d'),
    'valid_until' => date('Y-m-d', strtotime('+30 days')),
    'status' => 'active',
    'supplier_reference' => 'REF-MANUAL-001',
    'remarks' => 'Test quotation 1'
]);
$quotationIds[] = $quotation1Id;

// Add items to quotation 1
foreach ($productIds as $productId) {
    $db->insert('quotation_items', [
        'quotation_id' => $quotation1Id,
        'product_id' => $productId,
        'quantity' => 100,
        'unit_price' => 50.00,
        'tax_percent' => 12.00,
        'mrp' => 75.00
    ]);
}

$quotation2Id = (int)$db->insert('supplier_quotations', [
    'supplier_id' => $testSupplierId,
    'quotation_no' => 'MANUAL-QT-002',
    'quotation_date' => date('Y-m-d', strtotime('-10 days')),
    'valid_until' => date('Y-m-d', strtotime('+20 days')),
    'status' => 'active',
    'supplier_reference' => 'REF-MANUAL-002',
    'remarks' => 'Test quotation 2'
]);
$quotationIds[] = $quotation2Id;

// Add items to quotation 2
foreach (array_slice($productIds, 0, 2) as $productId) {
    $db->insert('quotation_items', [
        'quotation_id' => $quotation2Id,
        'product_id' => $productId,
        'quantity' => 50,
        'unit_price' => 45.00,
        'tax_percent' => 12.00,
        'mrp' => 70.00
    ]);
}

echo "✓ Created 2 test quotations\n\n";

// Test the endpoint logic
echo "Testing endpoint logic...\n\n";

// Test 1: Fetch supplier details
echo "Test 1: Fetch supplier details\n";
$supplierSql = "SELECT 
                    supplier_id,
                    name,
                    gstin,
                    contact_person,
                    mobile,
                    email,
                    address
                FROM suppliers
                WHERE supplier_id = ?";

$supplier = $db->fetch($supplierSql, [$testSupplierId]);

if ($supplier) {
    echo "✓ Supplier found\n";
    echo "  Name: {$supplier['name']}\n";
    echo "  GSTIN: {$supplier['gstin']}\n";
    echo "  Contact: {$supplier['contact_person']}\n";
    echo "  Mobile: {$supplier['mobile']}\n";
    echo "  Email: {$supplier['email']}\n";
} else {
    echo "✗ Supplier not found\n";
}
echo "\n";

// Test 2: Fetch quotations for supplier
echo "Test 2: Fetch quotations for supplier\n";
$quotationsSql = "SELECT 
                    sq.quotation_id,
                    sq.quotation_no,
                    sq.quotation_date,
                    sq.valid_until,
                    sq.status,
                    sq.supplier_reference,
                    sq.remarks,
                    sq.created_at,
                    COUNT(qi.quotation_item_id) as items_count
                FROM supplier_quotations sq
                LEFT JOIN quotation_items qi ON sq.quotation_id = qi.quotation_id
                WHERE sq.supplier_id = ?
                GROUP BY sq.quotation_id, sq.quotation_no, sq.quotation_date, 
                         sq.valid_until, sq.status, sq.supplier_reference,
                         sq.remarks, sq.created_at
                ORDER BY sq.quotation_date DESC, sq.quotation_id DESC";

$quotations = $db->fetchAll($quotationsSql, [$testSupplierId]);

if (count($quotations) === 2) {
    echo "✓ Found 2 quotations\n";
    
    foreach ($quotations as $index => $quotation) {
        echo "\n  Quotation " . ($index + 1) . ":\n";
        echo "    ID: {$quotation['quotation_id']}\n";
        echo "    Number: {$quotation['quotation_no']}\n";
        echo "    Date: {$quotation['quotation_date']}\n";
        echo "    Valid Until: {$quotation['valid_until']}\n";
        echo "    Status: {$quotation['status']}\n";
        echo "    Items Count: {$quotation['items_count']}\n";
        echo "    Reference: {$quotation['supplier_reference']}\n";
    }
} else {
    echo "✗ Expected 2 quotations, found " . count($quotations) . "\n";
}
echo "\n";

// Test 3: Verify ordering (latest first)
echo "Test 3: Verify quotations are ordered by date descending\n";
if (count($quotations) >= 2) {
    $isOrdered = true;
    for ($i = 0; $i < count($quotations) - 1; $i++) {
        if ($quotations[$i]['quotation_date'] < $quotations[$i + 1]['quotation_date']) {
            $isOrdered = false;
            break;
        }
    }
    
    if ($isOrdered) {
        echo "✓ Quotations are correctly ordered by date descending\n";
    } else {
        echo "✗ Quotations are not correctly ordered\n";
    }
} else {
    echo "⊘ Not enough quotations to test ordering\n";
}
echo "\n";

// Test 4: Verify items count
echo "Test 4: Verify items count calculation\n";
$expectedCounts = [3, 2]; // Based on how we created the quotations
$actualCounts = array_map(fn($q) => (int)$q['items_count'], $quotations);

if ($actualCounts === $expectedCounts) {
    echo "✓ Items count is correctly calculated\n";
    echo "  Expected: [" . implode(', ', $expectedCounts) . "]\n";
    echo "  Actual: [" . implode(', ', $actualCounts) . "]\n";
} else {
    echo "✗ Items count mismatch\n";
    echo "  Expected: [" . implode(', ', $expectedCounts) . "]\n";
    echo "  Actual: [" . implode(', ', $actualCounts) . "]\n";
}
echo "\n";

// Test 5: Test with non-existent supplier
echo "Test 5: Test with non-existent supplier\n";
$nonExistentId = 999999;
$nonExistentSupplier = $db->fetch($supplierSql, [$nonExistentId]);

if (empty($nonExistentSupplier)) {
    echo "✓ Non-existent supplier returns empty result\n";
} else {
    echo "✗ Non-existent supplier should return empty result\n";
}
echo "\n";

// Test 6: Verify response structure
echo "Test 6: Verify complete response structure\n";
$requiredSupplierFields = ['supplier_id', 'name', 'gstin', 'contact_person', 'mobile', 'email', 'address'];
$requiredQuotationFields = ['quotation_id', 'quotation_no', 'quotation_date', 'valid_until', 
                             'status', 'supplier_reference', 'remarks', 'created_at', 'items_count'];

$supplierFieldsOk = true;
foreach ($requiredSupplierFields as $field) {
    if (!array_key_exists($field, $supplier)) {
        echo "✗ Missing supplier field: $field\n";
        $supplierFieldsOk = false;
    }
}

if ($supplierFieldsOk) {
    echo "✓ Supplier has all required fields\n";
}

$quotationFieldsOk = true;
foreach ($quotations as $quotation) {
    foreach ($requiredQuotationFields as $field) {
        if (!array_key_exists($field, $quotation)) {
            echo "✗ Missing quotation field: $field\n";
            $quotationFieldsOk = false;
            break 2;
        }
    }
}

if ($quotationFieldsOk) {
    echo "✓ All quotations have required fields\n";
}
echo "\n";

// Cleanup
echo "Cleaning up test data...\n";
foreach ($quotationIds as $quotationId) {
    $db->delete('quotation_items', "quotation_id = $quotationId");
    $db->delete('supplier_quotations', "quotation_id = $quotationId");
}
$db->delete('suppliers', "supplier_id = $testSupplierId");
echo "✓ Test data cleaned up\n\n";

echo "==============================================\n";
echo "All tests completed!\n";
echo "==============================================\n";
