<?php
/**
 * API Integration Test for Get Quotation Details Endpoint
 * Tests task 2.5: GET /api/v1/quotations/{id}
 * 
 * Usage: php tests/manual/test_get_quotation_api.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment
if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}

use System\Database;
use App\Api\Controllers\InventoryController;

echo "==============================================\n";
echo "API Integration Test: Get Quotation Details\n";
echo "Task 2.5: GET /api/v1/quotations/{id}\n";
echo "==============================================\n\n";

$db = Database::getInstance();

// Setup test data
echo "Setting up test data...\n";

// Create test supplier
$testSupplierId = (int)$db->insert('suppliers', [
    'name' => 'API Test Supplier',
    'contact_person' => 'Jane Smith',
    'mobile' => '9876543210',
    'email' => 'jane@apisupplier.com',
    'gstin' => '29APITEST1234F1Z5',
    'address' => '456 API Test Street'
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

// Create test quotation
$uniqueId = time() . rand(100, 999);
$testQuotationId = (int)$db->insert('supplier_quotations', [
    'supplier_id' => $testSupplierId,
    'quotation_no' => "API-QT-{$uniqueId}",
    'quotation_date' => date('Y-m-d'),
    'valid_until' => date('Y-m-d', strtotime('+30 days')),
    'status' => 'active',
    'supplier_reference' => "API-REF-{$uniqueId}",
    'remarks' => 'API test quotation'
]);

echo "✓ Created test quotation (ID: $testQuotationId)\n";

// Add items to quotation
foreach ($productIds as $index => $productId) {
    $db->insert('quotation_items', [
        'quotation_id' => $testQuotationId,
        'product_id' => $productId,
        'quantity' => ($index + 1) * 10,
        'unit_price' => 50.00 + ($index * 10),
        'tax_percent' => 12.00,
        'mrp' => 75.00 + ($index * 15)
    ]);
}

echo "✓ Created " . count($productIds) . " quotation items\n\n";

// Test the API endpoint
echo "Testing API endpoint...\n\n";

// Test 1: Call the controller method directly
echo "Test 1: Call controller method directly\n";
ob_start();
try {
    $controller = new InventoryController();
    $controller->getQuotation($testQuotationId);
} catch (\Exception $e) {
    // Catch any exceptions
}
$output = ob_get_clean();

// Remove any JSON output from the buffer for cleaner display
$jsonStart = strpos($output, '{"success"');
if ($jsonStart !== false) {
    $jsonOutput = substr($output, $jsonStart);
    $response = json_decode($jsonOutput, true);
} else {
    $response = json_decode($output, true);
}

if ($response && isset($response['success']) && $response['success']) {
    echo "✓ API call successful\n";
    echo "  Response has 'success' = true\n";
    
    if (isset($response['data']['quotation'])) {
        echo "✓ Response contains 'quotation' data\n";
        $quotation = $response['data']['quotation'];
        echo "  Quotation Number: {$quotation['quotation_no']}\n";
        echo "  Supplier: {$quotation['supplier_name']}\n";
        echo "  Status: {$quotation['status']}\n";
    } else {
        echo "✗ Response missing 'quotation' data\n";
    }
    
    if (isset($response['data']['items'])) {
        echo "✓ Response contains 'items' data\n";
        echo "  Items count: " . count($response['data']['items']) . "\n";
    } else {
        echo "✗ Response missing 'items' data\n";
    }
} else {
    echo "✗ API call failed\n";
    echo "  Response: " . print_r($response, true) . "\n";
}
echo "\n";

// Test 2: Verify response structure matches design spec
echo "Test 2: Verify response structure matches design spec\n";
if ($response && $response['success']) {
    $quotation = $response['data']['quotation'] ?? null;
    $items = $response['data']['items'] ?? null;
    
    $structureOk = true;
    
    // Check quotation fields
    $requiredQuotationFields = [
        'quotation_id', 'quotation_no', 'supplier_id', 'supplier_name',
        'quotation_date', 'valid_until', 'status'
    ];
    
    foreach ($requiredQuotationFields as $field) {
        if (!isset($quotation[$field])) {
            echo "✗ Missing quotation field: $field\n";
            $structureOk = false;
        }
    }
    
    // Check items structure
    if (!is_array($items)) {
        echo "✗ Items is not an array\n";
        $structureOk = false;
    } else {
        $requiredItemFields = [
            'quotation_item_id', 'product_id', 'product_name', 'sku',
            'quantity', 'unit_price', 'tax_percent', 'mrp'
        ];
        
        foreach ($items as $item) {
            foreach ($requiredItemFields as $field) {
                if (!isset($item[$field])) {
                    echo "✗ Missing item field: $field\n";
                    $structureOk = false;
                    break 2;
                }
            }
        }
    }
    
    if ($structureOk) {
        echo "✓ Response structure matches design specification\n";
    }
} else {
    echo "⊘ Cannot verify structure - API call failed\n";
}
echo "\n";

// Test 3: Test with non-existent quotation ID
echo "Test 3: Test with non-existent quotation ID\n";
ob_start();
$controller = new InventoryController();
$controller->getQuotation(999999);
$output = ob_get_clean();

$response = json_decode($output, true);

if ($response && isset($response['success']) && !$response['success']) {
    echo "✓ Non-existent quotation returns error response\n";
    echo "  Error message: {$response['message']}\n";
} else {
    echo "✗ Non-existent quotation should return error\n";
}
echo "\n";

// Test 4: Verify data completeness (US-4.2, US-4.3, US-4.4)
echo "Test 4: Verify data completeness (Requirements US-4.2, US-4.3, US-4.4)\n";
ob_start();
$controller = new InventoryController();
$controller->getQuotation($testQuotationId);
$output = ob_get_clean();

$response = json_decode($output, true);

if ($response && $response['success']) {
    $quotation = $response['data']['quotation'];
    $items = $response['data']['items'];
    
    $completenessOk = true;
    
    // US-4.2: Quotation record completeness
    if (empty($quotation['quotation_no']) || empty($quotation['quotation_date']) || 
        empty($quotation['valid_until']) || empty($quotation['status'])) {
        echo "✗ Quotation missing required fields (US-4.2)\n";
        $completenessOk = false;
    }
    
    // US-4.3: Supplier info included
    if (empty($quotation['supplier_name']) || empty($quotation['supplier_id'])) {
        echo "✗ Supplier info missing (US-4.3)\n";
        $completenessOk = false;
    }
    
    // US-4.4: Item data completeness
    foreach ($items as $item) {
        if (empty($item['product_name']) || !isset($item['unit_price']) || 
            !isset($item['tax_percent']) || !isset($item['mrp'])) {
            echo "✗ Item missing required fields (US-4.4)\n";
            $completenessOk = false;
            break;
        }
    }
    
    if ($completenessOk) {
        echo "✓ All requirements satisfied (US-4.2, US-4.3, US-4.4)\n";
        echo "  - Quotation has all required fields\n";
        echo "  - Supplier information is complete\n";
        echo "  - All items have complete data\n";
    }
} else {
    echo "⊘ Cannot verify completeness - API call failed\n";
}
echo "\n";

// Test 5: Verify items are ordered correctly
echo "Test 5: Verify items are ordered by quotation_item_id ASC\n";
if ($response && $response['success']) {
    $items = $response['data']['items'];
    $isOrdered = true;
    
    for ($i = 0; $i < count($items) - 1; $i++) {
        if ($items[$i]['quotation_item_id'] > $items[$i + 1]['quotation_item_id']) {
            $isOrdered = false;
            break;
        }
    }
    
    if ($isOrdered) {
        echo "✓ Items are correctly ordered by quotation_item_id ASC\n";
    } else {
        echo "✗ Items are not correctly ordered\n";
    }
} else {
    echo "⊘ Cannot verify ordering - API call failed\n";
}
echo "\n";

// Cleanup
echo "Cleaning up test data...\n";
$db->delete('quotation_items', "quotation_id = $testQuotationId");
$db->delete('supplier_quotations', "quotation_id = $testQuotationId");
$db->delete('suppliers', "supplier_id = $testSupplierId");
echo "✓ Test data cleaned up\n\n";

echo "==============================================\n";
echo "All API integration tests completed!\n";
echo "==============================================\n";
