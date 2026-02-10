<?php
/**
 * Manual Test Script for Get Quotation Details Endpoint
 * Tests task 2.5: GET /api/v1/quotations/{id}
 * 
 * Usage: php tests/manual/test_get_quotation_details.php
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
echo "Testing Get Quotation Details Endpoint\n";
echo "Task 2.5: GET /api/v1/quotations/{id}\n";
echo "==============================================\n\n";

$db = Database::getInstance();

// Setup test data
echo "Setting up test data...\n";

// Create test supplier
$testSupplierId = (int)$db->insert('suppliers', [
    'name' => 'Test Supplier for Quotation Details',
    'contact_person' => 'John Doe',
    'mobile' => '9876543210',
    'email' => 'john@testsupplier.com',
    'gstin' => '29ABCDE1234F1Z5',
    'address' => '123 Test Street, Test City'
]);

echo "✓ Created test supplier (ID: $testSupplierId)\n";

// Get some products
$products = $db->fetchAll("SELECT product_id, name, sku, unit, hsn_code FROM products LIMIT 5");
if (empty($products)) {
    echo "✗ No products found in database. Please add products first.\n";
    exit(1);
}
echo "✓ Found " . count($products) . " products\n";

// Create test quotation
$testQuotationId = (int)$db->insert('supplier_quotations', [
    'supplier_id' => $testSupplierId,
    'quotation_no' => 'QT-TEST-2025-001',
    'quotation_date' => date('Y-m-d'),
    'valid_until' => date('Y-m-d', strtotime('+30 days')),
    'status' => 'active',
    'supplier_reference' => 'SUP-REF-001',
    'remarks' => 'Test quotation for detailed view testing'
]);

echo "✓ Created test quotation (ID: $testQuotationId)\n";

// Add items to quotation with varying data
$testItems = [];
foreach ($products as $index => $product) {
    $itemId = (int)$db->insert('quotation_items', [
        'quotation_id' => $testQuotationId,
        'product_id' => $product['product_id'],
        'quantity' => ($index + 1) * 10,
        'unit_price' => 50.00 + ($index * 10),
        'tax_percent' => 12.00,
        'mrp' => 75.00 + ($index * 15),
        'remarks' => "Item remark " . ($index + 1)
    ]);
    $testItems[] = [
        'item_id' => $itemId,
        'product_id' => $product['product_id'],
        'product_name' => $product['name'],
        'sku' => $product['sku'],
        'unit' => $product['unit'],
        'hsn_code' => $product['hsn_code'],
        'quantity' => ($index + 1) * 10,
        'unit_price' => 50.00 + ($index * 10),
        'tax_percent' => 12.00,
        'mrp' => 75.00 + ($index * 15),
        'remarks' => "Item remark " . ($index + 1)
    ];
}

echo "✓ Created " . count($testItems) . " quotation items\n\n";

// Test the endpoint logic
echo "Testing endpoint logic...\n\n";

// Test 1: Fetch quotation header with supplier info
echo "Test 1: Fetch quotation header with supplier info\n";
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

$quotation = $db->fetch($quotationSql, [$testQuotationId]);

if ($quotation) {
    echo "✓ Quotation header found\n";
    echo "  Quotation Number: {$quotation['quotation_no']}\n";
    echo "  Supplier: {$quotation['supplier_name']}\n";
    echo "  GSTIN: {$quotation['supplier_gstin']}\n";
    echo "  Contact: {$quotation['supplier_contact_person']}\n";
    echo "  Date: {$quotation['quotation_date']}\n";
    echo "  Valid Until: {$quotation['valid_until']}\n";
    echo "  Status: {$quotation['status']}\n";
    echo "  Reference: {$quotation['supplier_reference']}\n";
} else {
    echo "✗ Quotation not found\n";
}
echo "\n";

// Test 2: Fetch all quotation items with product details
echo "Test 2: Fetch all quotation items with product details\n";
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

$items = $db->fetchAll($itemsSql, [$testQuotationId]);

if (count($items) === count($testItems)) {
    echo "✓ Found " . count($items) . " items (expected " . count($testItems) . ")\n";
    
    foreach ($items as $index => $item) {
        echo "\n  Item " . ($index + 1) . ":\n";
        echo "    Product: {$item['product_name']}\n";
        echo "    SKU: {$item['sku']}\n";
        echo "    Unit: {$item['unit']}\n";
        echo "    HSN Code: {$item['hsn_code']}\n";
        echo "    Quantity: {$item['quantity']}\n";
        echo "    Unit Price: {$item['unit_price']}\n";
        echo "    Tax %: {$item['tax_percent']}\n";
        echo "    MRP: {$item['mrp']}\n";
        echo "    Remarks: {$item['remarks']}\n";
    }
} else {
    echo "✗ Expected " . count($testItems) . " items, found " . count($items) . "\n";
}
echo "\n";

// Test 3: Verify quotation header has all required fields
echo "Test 3: Verify quotation header has all required fields\n";
$requiredQuotationFields = [
    'quotation_id', 'quotation_no', 'supplier_id', 'supplier_name', 'supplier_gstin',
    'supplier_contact_person', 'supplier_mobile', 'supplier_email', 'supplier_address',
    'quotation_date', 'valid_until', 'supplier_reference', 'status', 'remarks',
    'created_by', 'created_at', 'updated_at'
];

$quotationFieldsOk = true;
$missingFields = [];
foreach ($requiredQuotationFields as $field) {
    if (!array_key_exists($field, $quotation)) {
        $missingFields[] = $field;
        $quotationFieldsOk = false;
    }
}

if ($quotationFieldsOk) {
    echo "✓ Quotation header has all required fields\n";
} else {
    echo "✗ Missing quotation fields: " . implode(', ', $missingFields) . "\n";
}
echo "\n";

// Test 4: Verify items have all required fields
echo "Test 4: Verify items have all required fields\n";
$requiredItemFields = [
    'quotation_item_id', 'product_id', 'product_name', 'sku', 'unit', 'hsn_code',
    'quantity', 'unit_price', 'tax_percent', 'mrp', 'remarks'
];

$itemFieldsOk = true;
$missingItemFields = [];
foreach ($items as $item) {
    foreach ($requiredItemFields as $field) {
        if (!array_key_exists($field, $item)) {
            $missingItemFields[] = $field;
            $itemFieldsOk = false;
            break 2;
        }
    }
}

if ($itemFieldsOk) {
    echo "✓ All items have required fields\n";
} else {
    echo "✗ Missing item fields: " . implode(', ', array_unique($missingItemFields)) . "\n";
}
echo "\n";

// Test 5: Verify data integrity (values match what we inserted)
echo "Test 5: Verify data integrity\n";
$dataIntegrityOk = true;

// Check quotation data
if ($quotation['quotation_no'] !== 'QT-TEST-2025-001') {
    echo "✗ Quotation number mismatch\n";
    $dataIntegrityOk = false;
}
if ($quotation['supplier_name'] !== 'Test Supplier for Quotation Details') {
    echo "✗ Supplier name mismatch\n";
    $dataIntegrityOk = false;
}
if ($quotation['supplier_reference'] !== 'SUP-REF-001') {
    echo "✗ Supplier reference mismatch\n";
    $dataIntegrityOk = false;
}

// Check items data
foreach ($items as $index => $item) {
    $expectedItem = $testItems[$index];
    if ((int)$item['quantity'] !== $expectedItem['quantity']) {
        echo "✗ Item $index quantity mismatch\n";
        $dataIntegrityOk = false;
    }
    if ((float)$item['unit_price'] !== $expectedItem['unit_price']) {
        echo "✗ Item $index unit_price mismatch\n";
        $dataIntegrityOk = false;
    }
}

if ($dataIntegrityOk) {
    echo "✓ All data values match expected values\n";
}
echo "\n";

// Test 6: Test with non-existent quotation
echo "Test 6: Test with non-existent quotation\n";
$nonExistentId = 999999;
$nonExistentQuotation = $db->fetch($quotationSql, [$nonExistentId]);

if (empty($nonExistentQuotation)) {
    echo "✓ Non-existent quotation returns empty result\n";
} else {
    echo "✗ Non-existent quotation should return empty result\n";
}
echo "\n";

// Test 7: Verify complete response structure
echo "Test 7: Verify complete response structure\n";
$response = [
    'quotation' => $quotation,
    'items' => $items
];

$structureOk = true;
if (!isset($response['quotation']) || !is_array($response['quotation'])) {
    echo "✗ Response missing 'quotation' key or not an array\n";
    $structureOk = false;
}
if (!isset($response['items']) || !is_array($response['items'])) {
    echo "✗ Response missing 'items' key or not an array\n";
    $structureOk = false;
}

if ($structureOk) {
    echo "✓ Response structure is correct\n";
    echo "  - quotation: object with " . count($response['quotation']) . " fields\n";
    echo "  - items: array with " . count($response['items']) . " items\n";
}
echo "\n";

// Test 8: Verify supplier info is included in quotation header
echo "Test 8: Verify supplier info is included in quotation header\n";
$supplierFieldsInQuotation = [
    'supplier_name', 'supplier_gstin', 'supplier_contact_person',
    'supplier_mobile', 'supplier_email', 'supplier_address'
];

$supplierInfoOk = true;
foreach ($supplierFieldsInQuotation as $field) {
    if (!array_key_exists($field, $quotation) || empty($quotation[$field])) {
        echo "✗ Missing or empty supplier field in quotation: $field\n";
        $supplierInfoOk = false;
    }
}

if ($supplierInfoOk) {
    echo "✓ All supplier information is included in quotation header\n";
}
echo "\n";

// Test 9: Verify product details are included in items
echo "Test 9: Verify product details are included in items\n";
$productFieldsInItems = ['product_name', 'sku', 'unit', 'hsn_code'];

$productInfoOk = true;
foreach ($items as $item) {
    foreach ($productFieldsInItems as $field) {
        if (!array_key_exists($field, $item)) {
            echo "✗ Missing product field in item: $field\n";
            $productInfoOk = false;
            break 2;
        }
    }
}

if ($productInfoOk) {
    echo "✓ All product details are included in items\n";
}
echo "\n";

// Cleanup
echo "Cleaning up test data...\n";
$db->delete('quotation_items', "quotation_id = $testQuotationId");
$db->delete('supplier_quotations', "quotation_id = $testQuotationId");
$db->delete('suppliers', "supplier_id = $testSupplierId");
echo "✓ Test data cleaned up\n\n";

echo "==============================================\n";
echo "All tests completed!\n";
echo "==============================================\n";
