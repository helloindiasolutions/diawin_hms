<?php
/**
 * API Integration Test for Quotations Endpoint
 * 
 * This script tests the actual HTTP API endpoint
 * Run: php tests/manual/test_quotations_api.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

use System\Database;

echo "=== Testing Quotations API Endpoint ===\n\n";

// Get base URL from environment
$baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
$apiUrl = $baseUrl . '/api/v1/quotations';

echo "API URL: $apiUrl\n\n";

try {
    $db = Database::getInstance();
    
    // Create a test quotation first
    echo "Setting up test data...\n";
    
    $suppliers = $db->fetchAll("SELECT supplier_id, name FROM suppliers LIMIT 1");
    $products = $db->fetchAll("SELECT product_id, name FROM products LIMIT 2");
    
    if (count($suppliers) === 0 || count($products) === 0) {
        echo "✗ Need at least 1 supplier and 2 products to run test\n";
        exit(1);
    }
    
    $supplierId = $suppliers[0]['supplier_id'];
    $quotationNo = 'API-TEST-' . date('Ymd-His');
    
    $db->beginTransaction();
    
    $quotationId = $db->insert('supplier_quotations', [
        'supplier_id' => $supplierId,
        'quotation_no' => $quotationNo,
        'quotation_date' => date('Y-m-d'),
        'valid_until' => date('Y-m-d', strtotime('+30 days')),
        'status' => 'active',
        'remarks' => 'API test quotation'
    ]);
    
    foreach ($products as $product) {
        $db->insert('quotation_items', [
            'quotation_id' => $quotationId,
            'product_id' => $product['product_id'],
            'quantity' => 50,
            'unit_price' => 25.50,
            'tax_percent' => 12.00,
            'mrp' => 35.00
        ]);
    }
    
    $db->commit();
    
    echo "✓ Created test quotation ID: $quotationId\n\n";
    
    // Test 1: Get all quotations
    echo "Test 1: GET /api/v1/quotations\n";
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "✓ Status: 200 OK\n";
            echo "✓ Response structure valid\n";
            echo "  Total quotations: " . ($data['data']['total'] ?? 0) . "\n";
            echo "  Quotations in response: " . count($data['data']['quotations'] ?? []) . "\n";
        } else {
            echo "✗ Invalid response structure\n";
            echo "Response: $response\n";
        }
    } else {
        echo "✗ HTTP Status: $httpCode\n";
        echo "Response: $response\n";
    }
    
    echo "\n";
    
    // Test 2: Filter by supplier
    echo "Test 2: GET /api/v1/quotations?supplier_id=$supplierId\n";
    $ch = curl_init($apiUrl . "?supplier_id=$supplierId");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "✓ Status: 200 OK\n";
            echo "  Quotations for supplier: " . count($data['data']['quotations'] ?? []) . "\n";
            
            // Verify all quotations are for the requested supplier
            $allMatch = true;
            foreach ($data['data']['quotations'] ?? [] as $q) {
                if ($q['supplier_id'] != $supplierId) {
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
    } else {
        echo "✗ HTTP Status: $httpCode\n";
    }
    
    echo "\n";
    
    // Test 3: Search
    echo "Test 3: GET /api/v1/quotations?search=API-TEST\n";
    $ch = curl_init($apiUrl . "?search=API-TEST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "✓ Status: 200 OK\n";
            echo "  Search results: " . count($data['data']['quotations'] ?? []) . "\n";
            
            // Verify search results contain the search term
            $found = false;
            foreach ($data['data']['quotations'] ?? [] as $q) {
                if (stripos($q['quotation_no'], 'API-TEST') !== false) {
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                echo "✓ Search results contain matching quotation\n";
            } else {
                echo "✗ Search results don't contain expected quotation\n";
            }
        } else {
            echo "✗ Invalid response structure\n";
        }
    } else {
        echo "✗ HTTP Status: $httpCode\n";
    }
    
    echo "\n";
    
    // Test 4: Filter by status
    echo "Test 4: GET /api/v1/quotations?status=active\n";
    $ch = curl_init($apiUrl . "?status=active");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "✓ Status: 200 OK\n";
            echo "  Active quotations: " . count($data['data']['quotations'] ?? []) . "\n";
            
            // Verify all are active
            $allActive = true;
            foreach ($data['data']['quotations'] ?? [] as $q) {
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
    } else {
        echo "✗ HTTP Status: $httpCode\n";
    }
    
    echo "\n";
    
    // Test 5: Pagination
    echo "Test 5: GET /api/v1/quotations?page=1&limit=5\n";
    $ch = curl_init($apiUrl . "?page=1&limit=5");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "✓ Status: 200 OK\n";
            echo "  Page: " . ($data['data']['page'] ?? 'N/A') . "\n";
            echo "  Limit: " . ($data['data']['limit'] ?? 'N/A') . "\n";
            echo "  Total: " . ($data['data']['total'] ?? 'N/A') . "\n";
            echo "  Total Pages: " . ($data['data']['total_pages'] ?? 'N/A') . "\n";
            
            $quotationsCount = count($data['data']['quotations'] ?? []);
            if ($quotationsCount <= 5) {
                echo "✓ Pagination limit respected (returned $quotationsCount items)\n";
            } else {
                echo "✗ Pagination limit not respected (returned $quotationsCount items)\n";
            }
        } else {
            echo "✗ Invalid response structure\n";
        }
    } else {
        echo "✗ HTTP Status: $httpCode\n";
    }
    
    echo "\n";
    
    // Cleanup
    echo "Cleaning up test data...\n";
    $db->delete('quotation_items', "quotation_id = $quotationId");
    $db->delete('supplier_quotations', "quotation_id = $quotationId");
    echo "✓ Test data cleaned up\n";
    
    echo "\n=== All API Tests Completed! ===\n";
    
} catch (Exception $e) {
    echo "✗ Fatal Error: " . $e->getMessage() . "\n";
    if (isset($quotationId)) {
        echo "Attempting cleanup...\n";
        try {
            $db->delete('quotation_items', "quotation_id = $quotationId");
            $db->delete('supplier_quotations', "quotation_id = $quotationId");
        } catch (Exception $cleanupError) {
            echo "Cleanup failed: " . $cleanupError->getMessage() . "\n";
        }
    }
    exit(1);
}
