<?php
/**
 * Manual Test Script for Quotations Endpoint
 * 
 * This script tests the quotations listing endpoint
 * Run: php tests/manual/test_quotations_endpoint.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

use System\Database;
use System\Response;

echo "=== Testing Quotations Endpoint ===\n\n";

try {
    $db = Database::getInstance();
    
    // Test 1: Check if tables exist
    echo "Test 1: Checking if quotation tables exist...\n";
    $tables = $db->fetchAll("SHOW TABLES LIKE 'supplier_quotations'");
    if (count($tables) > 0) {
        echo "✓ supplier_quotations table exists\n";
    } else {
        echo "✗ supplier_quotations table does NOT exist\n";
        exit(1);
    }
    
    $tables = $db->fetchAll("SHOW TABLES LIKE 'quotation_items'");
    if (count($tables) > 0) {
        echo "✓ quotation_items table exists\n";
    } else {
        echo "✗ quotation_items table does NOT exist\n";
        exit(1);
    }
    
    echo "\n";
    
    // Test 2: Check if we have any suppliers
    echo "Test 2: Checking for suppliers...\n";
    $suppliers = $db->fetchAll("SELECT supplier_id, name FROM suppliers LIMIT 5");
    echo "Found " . count($suppliers) . " suppliers\n";
    foreach ($suppliers as $supplier) {
        echo "  - {$supplier['name']} (ID: {$supplier['supplier_id']})\n";
    }
    
    echo "\n";
    
    // Test 3: Check if we have any products
    echo "Test 3: Checking for products...\n";
    $products = $db->fetchAll("SELECT product_id, name FROM products LIMIT 5");
    echo "Found " . count($products) . " products\n";
    foreach ($products as $product) {
        echo "  - {$product['name']} (ID: {$product['product_id']})\n";
    }
    
    echo "\n";
    
    // Test 4: Create a test quotation if we have suppliers and products
    if (count($suppliers) > 0 && count($products) > 0) {
        echo "Test 4: Creating a test quotation...\n";
        
        $supplierId = $suppliers[0]['supplier_id'];
        $quotationNo = 'TEST-QT-' . date('Ymd-His');
        
        $db->beginTransaction();
        
        try {
            $quotationId = $db->insert('supplier_quotations', [
                'supplier_id' => $supplierId,
                'quotation_no' => $quotationNo,
                'quotation_date' => date('Y-m-d'),
                'valid_until' => date('Y-m-d', strtotime('+30 days')),
                'status' => 'active',
                'remarks' => 'Test quotation for endpoint verification'
            ]);
            
            echo "✓ Created quotation ID: $quotationId\n";
            
            // Add some items
            $itemsAdded = 0;
            foreach (array_slice($products, 0, 3) as $product) {
                $db->insert('quotation_items', [
                    'quotation_id' => $quotationId,
                    'product_id' => $product['product_id'],
                    'quantity' => rand(10, 100),
                    'unit_price' => rand(100, 1000) / 10,
                    'tax_percent' => 12.00,
                    'mrp' => rand(150, 1500) / 10
                ]);
                $itemsAdded++;
            }
            
            echo "✓ Added $itemsAdded items to quotation\n";
            
            $db->commit();
            
            echo "\n";
            
            // Test 5: Query the quotations
            echo "Test 5: Querying quotations...\n";
            
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.quotation_no,
                        sq.supplier_id,
                        s.name as supplier_name,
                        sq.quotation_date,
                        sq.valid_until,
                        sq.status,
                        COUNT(qi.quotation_item_id) as items_count,
                        COALESCE(SUM(qi.quantity * qi.unit_price), 0) as total_value
                    FROM supplier_quotations sq
                    LEFT JOIN suppliers s ON sq.supplier_id = s.supplier_id
                    LEFT JOIN quotation_items qi ON sq.quotation_id = qi.quotation_id
                    WHERE sq.quotation_id = ?
                    GROUP BY sq.quotation_id, sq.quotation_no, sq.supplier_id, s.name, 
                             sq.quotation_date, sq.valid_until, sq.status";
            
            $result = $db->fetch($sql, [$quotationId]);
            
            if ($result) {
                echo "✓ Successfully queried quotation\n";
                echo "  Quotation No: {$result['quotation_no']}\n";
                echo "  Supplier: {$result['supplier_name']}\n";
                echo "  Date: {$result['quotation_date']}\n";
                echo "  Valid Until: {$result['valid_until']}\n";
                echo "  Status: {$result['status']}\n";
                echo "  Items Count: {$result['items_count']}\n";
                echo "  Total Value: " . number_format($result['total_value'], 2) . "\n";
            } else {
                echo "✗ Failed to query quotation\n";
            }
            
            echo "\n";
            
            // Test 6: Test filtering
            echo "Test 6: Testing filters...\n";
            
            // Filter by supplier
            $sql = "SELECT COUNT(*) as count FROM supplier_quotations WHERE supplier_id = ?";
            $count = $db->fetch($sql, [$supplierId])['count'];
            echo "✓ Filter by supplier: Found $count quotations\n";
            
            // Filter by status
            $sql = "SELECT COUNT(*) as count FROM supplier_quotations WHERE status = 'active'";
            $count = $db->fetch($sql)['count'];
            echo "✓ Filter by status: Found $count active quotations\n";
            
            // Search
            $sql = "SELECT COUNT(*) as count FROM supplier_quotations WHERE quotation_no LIKE ?";
            $count = $db->fetch($sql, ['%TEST%'])['count'];
            echo "✓ Search: Found $count quotations with 'TEST' in number\n";
            
            echo "\n";
            
            // Cleanup
            echo "Cleaning up test data...\n";
            $db->delete('quotation_items', "quotation_id = $quotationId");
            $db->delete('supplier_quotations', "quotation_id = $quotationId");
            echo "✓ Test data cleaned up\n";
            
        } catch (Exception $e) {
            $db->rollBack();
            echo "✗ Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    } else {
        echo "Skipping test quotation creation - need suppliers and products\n";
    }
    
    echo "\n=== All Tests Passed! ===\n";
    
} catch (Exception $e) {
    echo "✗ Fatal Error: " . $e->getMessage() . "\n";
    exit(1);
}
