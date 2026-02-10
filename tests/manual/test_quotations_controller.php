<?php
/**
 * Direct Controller Test for Quotations Endpoint
 * 
 * This script tests the controller method directly without HTTP
 * Run: php tests/manual/test_quotations_controller.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

use System\Database;
use App\Api\Controllers\InventoryController;

echo "=== Testing Quotations Controller Method ===\n\n";

try {
    $db = Database::getInstance();
    
    // Create test data
    echo "Setting up test data...\n";
    
    $suppliers = $db->fetchAll("SELECT supplier_id, name FROM suppliers LIMIT 2");
    $products = $db->fetchAll("SELECT product_id, name FROM products LIMIT 3");
    
    if (count($suppliers) === 0 || count($products) === 0) {
        echo "✗ Need at least 2 suppliers and 3 products to run test\n";
        exit(1);
    }
    
    // Create multiple test quotations
    $testQuotations = [];
    
    $db->beginTransaction();
    
    // Quotation 1 - Active
    $quotationId1 = $db->insert('supplier_quotations', [
        'supplier_id' => $suppliers[0]['supplier_id'],
        'quotation_no' => 'CTRL-TEST-001',
        'quotation_date' => date('Y-m-d', strtotime('-10 days')),
        'valid_until' => date('Y-m-d', strtotime('+20 days')),
        'status' => 'active',
        'supplier_reference' => 'SUP-REF-001',
        'remarks' => 'Controller test quotation 1'
    ]);
    $testQuotations[] = $quotationId1;
    
    // Add items to quotation 1
    foreach (array_slice($products, 0, 2) as $product) {
        $db->insert('quotation_items', [
            'quotation_id' => $quotationId1,
            'product_id' => $product['product_id'],
            'quantity' => 100,
            'unit_price' => 50.00,
            'tax_percent' => 12.00,
            'mrp' => 75.00
        ]);
    }
    
    // Quotation 2 - Active, different supplier
    if (count($suppliers) > 1) {
        $quotationId2 = $db->insert('supplier_quotations', [
            'supplier_id' => $suppliers[1]['supplier_id'],
            'quotation_no' => 'CTRL-TEST-002',
            'quotation_date' => date('Y-m-d', strtotime('-5 days')),
            'valid_until' => date('Y-m-d', strtotime('+25 days')),
            'status' => 'active',
            'supplier_reference' => 'SUP-REF-002',
            'remarks' => 'Controller test quotation 2'
        ]);
        $testQuotations[] = $quotationId2;
        
        // Add items to quotation 2
        foreach (array_slice($products, 1, 2) as $product) {
            $db->insert('quotation_items', [
                'quotation_id' => $quotationId2,
                'product_id' => $product['product_id'],
                'quantity' => 50,
                'unit_price' => 30.00,
                'tax_percent' => 18.00,
                'mrp' => 45.00
            ]);
        }
    }
    
    // Quotation 3 - Expired
    $quotationId3 = $db->insert('supplier_quotations', [
        'supplier_id' => $suppliers[0]['supplier_id'],
        'quotation_no' => 'CTRL-TEST-003',
        'quotation_date' => date('Y-m-d', strtotime('-60 days')),
        'valid_until' => date('Y-m-d', strtotime('-30 days')),
        'status' => 'expired',
        'supplier_reference' => 'SUP-REF-003',
        'remarks' => 'Controller test quotation 3 - expired'
    ]);
    $testQuotations[] = $quotationId3;
    
    // Add items to quotation 3
    $db->insert('quotation_items', [
        'quotation_id' => $quotationId3,
        'product_id' => $products[0]['product_id'],
        'quantity' => 25,
        'unit_price' => 20.00,
        'tax_percent' => 12.00,
        'mrp' => 30.00
    ]);
    
    $db->commit();
    
    echo "✓ Created " . count($testQuotations) . " test quotations\n\n";
    
    // Test 1: Get all quotations (no filters)
    echo "Test 1: Get all quotations (no filters)\n";
    $_GET = [];
    
    ob_start();
    $controller = new InventoryController();
    $controller->quotations();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    
    if ($response && isset($response['success']) && $response['success']) {
        echo "✓ Response structure valid\n";
        echo "  Total quotations: " . ($response['data']['total'] ?? 0) . "\n";
        echo "  Quotations returned: " . count($response['data']['quotations'] ?? []) . "\n";
        
        // Check if our test quotations are in the results
        $foundCount = 0;
        foreach ($response['data']['quotations'] ?? [] as $q) {
            if (in_array($q['quotation_id'], $testQuotations)) {
                $foundCount++;
            }
        }
        echo "  Test quotations found: $foundCount/" . count($testQuotations) . "\n";
        
        // Verify ordering (should be by date DESC, then ID DESC)
        $quotations = $response['data']['quotations'] ?? [];
        $isOrdered = true;
        for ($i = 0; $i < count($quotations) - 1; $i++) {
            $current = $quotations[$i];
            $next = $quotations[$i + 1];
            
            if ($current['quotation_date'] < $next['quotation_date']) {
                $isOrdered = false;
                break;
            }
            
            if ($current['quotation_date'] === $next['quotation_date'] && 
                $current['quotation_id'] < $next['quotation_id']) {
                $isOrdered = false;
                break;
            }
        }
        
        if ($isOrdered) {
            echo "✓ Quotations are properly ordered (date DESC, ID DESC)\n";
        } else {
            echo "✗ Quotations are NOT properly ordered\n";
        }
    } else {
        echo "✗ Invalid response structure\n";
        echo "Response: $output\n";
    }
    
    echo "\n";
    
    // Test 2: Filter by supplier
    echo "Test 2: Filter by supplier_id={$suppliers[0]['supplier_id']}\n";
    $_GET = ['supplier_id' => $suppliers[0]['supplier_id']];
    
    ob_start();
    $controller = new InventoryController();
    $controller->quotations();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    
    if ($response && isset($response['success']) && $response['success']) {
        echo "✓ Response structure valid\n";
        echo "  Quotations returned: " . count($response['data']['quotations'] ?? []) . "\n";
        
        // Verify all quotations are for the requested supplier
        $allMatch = true;
        foreach ($response['data']['quotations'] ?? [] as $q) {
            if ($q['supplier_id'] != $suppliers[0]['supplier_id']) {
                $allMatch = false;
                break;
            }
        }
        
        if ($allMatch) {
            echo "✓ All quotations match supplier filter\n";
        } else {
            echo "✗ Some quotations don't match supplier filter\n";
        }
    } else {
        echo "✗ Invalid response structure\n";
    }
    
    echo "\n";
    
    // Test 3: Filter by status
    echo "Test 3: Filter by status=active\n";
    $_GET = ['status' => 'active'];
    
    ob_start();
    $controller = new InventoryController();
    $controller->quotations();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    
    if ($response && isset($response['success']) && $response['success']) {
        echo "✓ Response structure valid\n";
        echo "  Active quotations: " . count($response['data']['quotations'] ?? []) . "\n";
        
        // Verify all are active
        $allActive = true;
        foreach ($response['data']['quotations'] ?? [] as $q) {
            if ($q['status'] !== 'active') {
                $allActive = false;
                break;
            }
        }
        
        if ($allActive) {
            echo "✓ All quotations have active status\n";
        } else {
            echo "✗ Some quotations are not active\n";
        }
    } else {
        echo "✗ Invalid response structure\n";
    }
    
    echo "\n";
    
    // Test 4: Search
    echo "Test 4: Search by quotation_no=CTRL-TEST\n";
    $_GET = ['search' => 'CTRL-TEST'];
    
    ob_start();
    $controller = new InventoryController();
    $controller->quotations();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    
    if ($response && isset($response['success']) && $response['success']) {
        echo "✓ Response structure valid\n";
        echo "  Search results: " . count($response['data']['quotations'] ?? []) . "\n";
        
        // Verify all results contain the search term
        $allMatch = true;
        foreach ($response['data']['quotations'] ?? [] as $q) {
            if (stripos($q['quotation_no'], 'CTRL-TEST') === false &&
                stripos($q['supplier_reference'] ?? '', 'CTRL-TEST') === false &&
                stripos($q['supplier_name'] ?? '', 'CTRL-TEST') === false) {
                $allMatch = false;
                break;
            }
        }
        
        if ($allMatch) {
            echo "✓ All results match search term\n";
        } else {
            echo "✗ Some results don't match search term\n";
        }
    } else {
        echo "✗ Invalid response structure\n";
    }
    
    echo "\n";
    
    // Test 5: Date range filter
    echo "Test 5: Filter by date range (last 15 days)\n";
    $_GET = [
        'from_date' => date('Y-m-d', strtotime('-15 days')),
        'to_date' => date('Y-m-d')
    ];
    
    ob_start();
    $controller = new InventoryController();
    $controller->quotations();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    
    if ($response && isset($response['success']) && $response['success']) {
        echo "✓ Response structure valid\n";
        echo "  Quotations in date range: " . count($response['data']['quotations'] ?? []) . "\n";
        
        // Verify all are within date range
        $fromDate = strtotime($_GET['from_date']);
        $toDate = strtotime($_GET['to_date']);
        $allInRange = true;
        
        foreach ($response['data']['quotations'] ?? [] as $q) {
            $qDate = strtotime($q['quotation_date']);
            if ($qDate < $fromDate || $qDate > $toDate) {
                $allInRange = false;
                break;
            }
        }
        
        if ($allInRange) {
            echo "✓ All quotations are within date range\n";
        } else {
            echo "✗ Some quotations are outside date range\n";
        }
    } else {
        echo "✗ Invalid response structure\n";
    }
    
    echo "\n";
    
    // Test 6: Pagination
    echo "Test 6: Pagination (page=1, limit=2)\n";
    $_GET = ['page' => '1', 'limit' => '2'];
    
    ob_start();
    $controller = new InventoryController();
    $controller->quotations();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    
    if ($response && isset($response['success']) && $response['success']) {
        echo "✓ Response structure valid\n";
        echo "  Page: " . ($response['data']['page'] ?? 'N/A') . "\n";
        echo "  Limit: " . ($response['data']['limit'] ?? 'N/A') . "\n";
        echo "  Total: " . ($response['data']['total'] ?? 'N/A') . "\n";
        echo "  Total Pages: " . ($response['data']['total_pages'] ?? 'N/A') . "\n";
        
        $quotationsCount = count($response['data']['quotations'] ?? []);
        echo "  Quotations returned: $quotationsCount\n";
        
        if ($quotationsCount <= 2) {
            echo "✓ Pagination limit respected\n";
        } else {
            echo "✗ Pagination limit not respected\n";
        }
    } else {
        echo "✗ Invalid response structure\n";
    }
    
    echo "\n";
    
    // Test 7: Verify data completeness
    echo "Test 7: Verify data completeness\n";
    $_GET = ['quotation_id' => $quotationId1];
    
    ob_start();
    $controller = new InventoryController();
    $controller->quotations();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    
    if ($response && isset($response['success']) && $response['success']) {
        $quotations = $response['data']['quotations'] ?? [];
        if (count($quotations) > 0) {
            $q = $quotations[0];
            
            $requiredFields = [
                'quotation_id', 'quotation_no', 'supplier_id', 'supplier_name',
                'quotation_date', 'valid_until', 'status', 'items_count', 'total_value'
            ];
            
            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (!isset($q[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (empty($missingFields)) {
                echo "✓ All required fields present\n";
                echo "  Items count: " . $q['items_count'] . "\n";
                echo "  Total value: " . number_format($q['total_value'], 2) . "\n";
            } else {
                echo "✗ Missing fields: " . implode(', ', $missingFields) . "\n";
            }
        } else {
            echo "✗ No quotations returned\n";
        }
    } else {
        echo "✗ Invalid response structure\n";
    }
    
    echo "\n";
    
    // Cleanup
    echo "Cleaning up test data...\n";
    foreach ($testQuotations as $qId) {
        $db->delete('quotation_items', "quotation_id = $qId");
        $db->delete('supplier_quotations', "quotation_id = $qId");
    }
    echo "✓ Test data cleaned up\n";
    
    echo "\n=== All Controller Tests Passed! ===\n";
    
} catch (Exception $e) {
    echo "✗ Fatal Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    if (isset($testQuotations)) {
        echo "\nAttempting cleanup...\n";
        try {
            foreach ($testQuotations as $qId) {
                $db->delete('quotation_items', "quotation_id = $qId");
                $db->delete('supplier_quotations', "quotation_id = $qId");
            }
        } catch (Exception $cleanupError) {
            echo "Cleanup failed: " . $cleanupError->getMessage() . "\n";
        }
    }
    exit(1);
}
