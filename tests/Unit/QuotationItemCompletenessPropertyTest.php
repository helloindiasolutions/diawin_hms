<?php
/**
 * Property-Based Test for Quotation Item Data Completeness
 * 
 * Property 8: Quotation Item Data Completeness
 * Validates: Requirements US-4.4
 * 
 * Property: For any quotation item in a quotation details response, the item 
 * should include all required fields: product_name, sku, unit, unit_price, 
 * tax_percent, mrp, and remarks (nullable).
 * 
 * @feature purchase-quotation-system
 * @property 8
 */

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use System\Database;

class QuotationItemCompletenessPropertyTest extends TestCase
{
    private Database $db;
    private array $testQuotationIds = [];
    private array $testSupplierIds = [];
    private array $testProductIds = [];
    private const PROPERTY_TEST_ITERATIONS = 100;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Load environment
        if (file_exists(__DIR__ . '/../../.env')) {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();
        }
        
        $this->db = Database::getInstance();
        $this->setupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
        parent::tearDown();
    }

    private function setupTestData(): void
    {
        // Get or create test suppliers
        $suppliers = $this->db->fetchAll("SELECT supplier_id FROM suppliers LIMIT 5");
        if (count($suppliers) < 5) {
            // Create test suppliers if needed
            for ($i = count($suppliers); $i < 5; $i++) {
                $supplierId = $this->db->insert('suppliers', [
                    'name' => 'Test Supplier ' . ($i + 1),
                    'contact_person' => 'Contact ' . ($i + 1),
                    'mobile' => '98' . str_pad((string)rand(10000000, 99999999), 10, '0', STR_PAD_LEFT),
                    'email' => 'supplier' . ($i + 1) . '@test.com',
                    'gstin' => 'TEST-GSTIN-' . str_pad((string)($i + 1), 3, '0', STR_PAD_LEFT)
                ]);
                $this->testSupplierIds[] = $supplierId;
            }
        } else {
            $this->testSupplierIds = array_column($suppliers, 'supplier_id');
        }

        // Get test products
        $products = $this->db->fetchAll("SELECT product_id FROM products LIMIT 10");
        $this->testProductIds = array_column($products, 'product_id');
    }

    private function cleanupTestData(): void
    {
        // Clean up quotations and their items
        foreach ($this->testQuotationIds as $quotationId) {
            $this->db->delete('quotation_items', "quotation_id = $quotationId");
            $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
        }
    }

    /**
     * Generate a random quotation
     */
    private function generateRandomQuotation(): array
    {
        // Generate random date within the last 365 days
        $daysAgo = rand(0, 365);
        $quotationDate = date('Y-m-d', strtotime("-$daysAgo days"));
        $validUntil = date('Y-m-d', strtotime($quotationDate . ' +' . rand(30, 90) . ' days'));
        
        return [
            'supplier_id' => $this->testSupplierIds[array_rand($this->testSupplierIds)],
            'quotation_no' => 'PROP-ITEM-' . uniqid() . '-' . rand(1000, 9999),
            'quotation_date' => $quotationDate,
            'valid_until' => $validUntil,
            'status' => ['active', 'expired', 'cancelled'][rand(0, 2)],
            'supplier_reference' => 'REF-' . rand(10000, 99999),
            'remarks' => 'Property test for item completeness'
        ];
    }

    /**
     * Generate random quotation items
     */
    private function generateRandomItems(int $count): array
    {
        $items = [];
        for ($i = 0; $i < $count; $i++) {
            if (!empty($this->testProductIds)) {
                $items[] = [
                    'product_id' => $this->testProductIds[array_rand($this->testProductIds)],
                    'quantity' => rand(1, 1000),
                    'unit_price' => round(rand(10, 10000) / 100, 2),
                    'tax_percent' => [0, 5, 12, 18, 28][rand(0, 4)],
                    'mrp' => round(rand(20, 20000) / 100, 2),
                    // remarks is nullable - randomly include or exclude
                    'remarks' => rand(0, 1) ? 'Test remark ' . rand(1, 100) : null
                ];
            }
        }
        return $items;
    }

    /**
     * Create a quotation in the database with items
     */
    private function createQuotationWithItems(array $quotationData, array $items): int
    {
        $quotationId = (int)$this->db->insert('supplier_quotations', $quotationData);
        $this->testQuotationIds[] = $quotationId;

        // Add items to the quotation
        foreach ($items as $item) {
            $this->db->insert('quotation_items', array_merge($item, [
                'quotation_id' => $quotationId
            ]));
        }

        return $quotationId;
    }

    /**
     * Verify that a quotation item has all required fields with valid values
     */
    private function verifyQuotationItemCompleteness(array $item): void
    {
        // Required field: product_name
        $this->assertArrayHasKey(
            'product_name',
            $item,
            'Quotation item must have product_name field'
        );
        $this->assertNotEmpty(
            $item['product_name'],
            'product_name must not be empty'
        );
        $this->assertIsString(
            $item['product_name'],
            'product_name must be a string'
        );

        // Required field: sku
        $this->assertArrayHasKey(
            'sku',
            $item,
            'Quotation item must have sku field'
        );
        // SKU can be empty but field must exist
        $this->assertTrue(
            isset($item['sku']),
            'sku field must be set (can be null or empty)'
        );

        // Required field: unit
        $this->assertArrayHasKey(
            'unit',
            $item,
            'Quotation item must have unit field'
        );
        // Unit can be empty but field must exist
        $this->assertTrue(
            isset($item['unit']),
            'unit field must be set (can be null or empty)'
        );

        // Required field: unit_price
        $this->assertArrayHasKey(
            'unit_price',
            $item,
            'Quotation item must have unit_price field'
        );
        $this->assertIsNumeric(
            $item['unit_price'],
            'unit_price must be numeric'
        );
        $this->assertGreaterThanOrEqual(
            0,
            (float)$item['unit_price'],
            'unit_price must be non-negative'
        );

        // Required field: tax_percent
        $this->assertArrayHasKey(
            'tax_percent',
            $item,
            'Quotation item must have tax_percent field'
        );
        $this->assertIsNumeric(
            $item['tax_percent'],
            'tax_percent must be numeric'
        );
        $this->assertGreaterThanOrEqual(
            0,
            (float)$item['tax_percent'],
            'tax_percent must be non-negative'
        );
        $this->assertLessThanOrEqual(
            100,
            (float)$item['tax_percent'],
            'tax_percent must not exceed 100'
        );

        // Required field: mrp
        $this->assertArrayHasKey(
            'mrp',
            $item,
            'Quotation item must have mrp field'
        );
        $this->assertIsNumeric(
            $item['mrp'],
            'mrp must be numeric'
        );
        $this->assertGreaterThanOrEqual(
            0,
            (float)$item['mrp'],
            'mrp must be non-negative'
        );

        // Required field: remarks (nullable)
        $this->assertArrayHasKey(
            'remarks',
            $item,
            'Quotation item must have remarks field (can be null)'
        );
        // remarks can be null or string
        if ($item['remarks'] !== null) {
            $this->assertIsString(
                $item['remarks'],
                'remarks must be a string when not null'
            );
        }
    }

    /**
     * Property Test: All quotation items have complete data
     * 
     * This test generates random quotations with items and verifies that all
     * returned quotation items include all required fields with valid values.
     * 
     * **Validates: Requirements US-4.4**
     * 
     * @test
     */
    public function testQuotationItemCompletenessPropertyWithRandomData(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Generate random quotation with random number of items
            $quotationData = $this->generateRandomQuotation();
            $itemCount = rand(1, 15);
            $items = $this->generateRandomItems($itemCount);
            
            $quotationId = $this->createQuotationWithItems($quotationData, $items);

            // Fetch quotation items using the same query as the API endpoint
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

            $fetchedItems = $this->db->fetchAll($itemsSql, [$quotationId]);

            // Verify we got the expected number of items
            $this->assertCount(
                $itemCount,
                $fetchedItems,
                "Should fetch exactly $itemCount items"
            );

            // Verify completeness for each item
            foreach ($fetchedItems as $item) {
                $this->verifyQuotationItemCompleteness($item);
            }

            // Clean up this iteration's quotation
            $this->db->delete('quotation_items', "quotation_id = $quotationId");
            $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
            
            // Remove from cleanup list
            $key = array_search($quotationId, $this->testQuotationIds);
            if ($key !== false) {
                unset($this->testQuotationIds[$key]);
            }
        }

        // If we got here, the property held for all iterations
        $this->assertTrue(true, "Quotation item completeness property held for $iterations iterations");
    }

    /**
     * Property Test: Completeness holds for items with null remarks
     * 
     * This test specifically verifies that items with null remarks still
     * have complete data.
     * 
     * **Validates: Requirements US-4.4**
     * 
     * @test
     */
    public function testQuotationItemCompletenessWithNullRemarks(): void
    {
        $iterations = 30;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Generate quotation with items that have null remarks
            $quotationData = $this->generateRandomQuotation();
            $itemCount = rand(3, 10);
            $items = [];
            
            for ($i = 0; $i < $itemCount; $i++) {
                if (!empty($this->testProductIds)) {
                    $items[] = [
                        'product_id' => $this->testProductIds[array_rand($this->testProductIds)],
                        'quantity' => rand(1, 100),
                        'unit_price' => round(rand(10, 10000) / 100, 2),
                        'tax_percent' => [0, 5, 12, 18, 28][rand(0, 4)],
                        'mrp' => round(rand(20, 20000) / 100, 2),
                        'remarks' => null  // Explicitly null
                    ];
                }
            }
            
            $quotationId = $this->createQuotationWithItems($quotationData, $items);

            // Fetch items
            $itemsSql = "SELECT 
                            qi.quotation_item_id,
                            qi.product_id,
                            p.name as product_name,
                            p.sku,
                            p.unit,
                            qi.quantity,
                            qi.unit_price,
                            qi.tax_percent,
                            qi.mrp,
                            qi.remarks
                        FROM quotation_items qi
                        INNER JOIN products p ON qi.product_id = p.product_id
                        WHERE qi.quotation_id = ?";

            $fetchedItems = $this->db->fetchAll($itemsSql, [$quotationId]);

            // Verify completeness for each item
            foreach ($fetchedItems as $item) {
                $this->verifyQuotationItemCompleteness($item);
                
                // Verify remarks is null
                $this->assertNull(
                    $item['remarks'],
                    'remarks should be null for this test'
                );
            }

            // Clean up
            $this->db->delete('quotation_items', "quotation_id = $quotationId");
            $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
            
            $key = array_search($quotationId, $this->testQuotationIds);
            if ($key !== false) {
                unset($this->testQuotationIds[$key]);
            }
        }

        $this->assertTrue(true, "Item completeness with null remarks held for $iterations iterations");
    }

    /**
     * Property Test: Completeness holds for items with non-null remarks
     * 
     * This test specifically verifies that items with non-null remarks still
     * have complete data.
     * 
     * **Validates: Requirements US-4.4**
     * 
     * @test
     */
    public function testQuotationItemCompletenessWithNonNullRemarks(): void
    {
        $iterations = 30;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Generate quotation with items that have non-null remarks
            $quotationData = $this->generateRandomQuotation();
            $itemCount = rand(3, 10);
            $items = [];
            
            for ($i = 0; $i < $itemCount; $i++) {
                if (!empty($this->testProductIds)) {
                    $items[] = [
                        'product_id' => $this->testProductIds[array_rand($this->testProductIds)],
                        'quantity' => rand(1, 100),
                        'unit_price' => round(rand(10, 10000) / 100, 2),
                        'tax_percent' => [0, 5, 12, 18, 28][rand(0, 4)],
                        'mrp' => round(rand(20, 20000) / 100, 2),
                        'remarks' => 'Test remark ' . uniqid()  // Non-null
                    ];
                }
            }
            
            $quotationId = $this->createQuotationWithItems($quotationData, $items);

            // Fetch items
            $itemsSql = "SELECT 
                            qi.quotation_item_id,
                            qi.product_id,
                            p.name as product_name,
                            p.sku,
                            p.unit,
                            qi.quantity,
                            qi.unit_price,
                            qi.tax_percent,
                            qi.mrp,
                            qi.remarks
                        FROM quotation_items qi
                        INNER JOIN products p ON qi.product_id = p.product_id
                        WHERE qi.quotation_id = ?";

            $fetchedItems = $this->db->fetchAll($itemsSql, [$quotationId]);

            // Verify completeness for each item
            foreach ($fetchedItems as $item) {
                $this->verifyQuotationItemCompleteness($item);
                
                // Verify remarks is not null and is a string
                $this->assertNotNull(
                    $item['remarks'],
                    'remarks should not be null for this test'
                );
                $this->assertIsString(
                    $item['remarks'],
                    'remarks should be a string'
                );
            }

            // Clean up
            $this->db->delete('quotation_items', "quotation_id = $quotationId");
            $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
            
            $key = array_search($quotationId, $this->testQuotationIds);
            if ($key !== false) {
                unset($this->testQuotationIds[$key]);
            }
        }

        $this->assertTrue(true, "Item completeness with non-null remarks held for $iterations iterations");
    }

    /**
     * Property Test: Completeness holds for items with edge case prices
     * 
     * This test verifies that items with edge case prices (very low, very high,
     * zero) still have complete data.
     * 
     * **Validates: Requirements US-4.4**
     * 
     * @test
     */
    public function testQuotationItemCompletenessWithEdgeCasePrices(): void
    {
        $iterations = 30;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Generate quotation with items that have edge case prices
            $quotationData = $this->generateRandomQuotation();
            $items = [];
            
            if (!empty($this->testProductIds)) {
                // Zero price
                $items[] = [
                    'product_id' => $this->testProductIds[array_rand($this->testProductIds)],
                    'quantity' => 1,
                    'unit_price' => 0.00,
                    'tax_percent' => 0,
                    'mrp' => 0.00,
                    'remarks' => 'Zero price test'
                ];
                
                // Very low price
                $items[] = [
                    'product_id' => $this->testProductIds[array_rand($this->testProductIds)],
                    'quantity' => 1,
                    'unit_price' => 0.01,
                    'tax_percent' => 5,
                    'mrp' => 0.02,
                    'remarks' => 'Very low price test'
                ];
                
                // Very high price
                $items[] = [
                    'product_id' => $this->testProductIds[array_rand($this->testProductIds)],
                    'quantity' => 1,
                    'unit_price' => 99999.99,
                    'tax_percent' => 28,
                    'mrp' => 150000.00,
                    'remarks' => 'Very high price test'
                ];
                
                // Maximum tax percent
                $items[] = [
                    'product_id' => $this->testProductIds[array_rand($this->testProductIds)],
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'tax_percent' => 100,
                    'mrp' => 200.00,
                    'remarks' => 'Maximum tax test'
                ];
            }
            
            $quotationId = $this->createQuotationWithItems($quotationData, $items);

            // Fetch items
            $itemsSql = "SELECT 
                            qi.quotation_item_id,
                            qi.product_id,
                            p.name as product_name,
                            p.sku,
                            p.unit,
                            qi.quantity,
                            qi.unit_price,
                            qi.tax_percent,
                            qi.mrp,
                            qi.remarks
                        FROM quotation_items qi
                        INNER JOIN products p ON qi.product_id = p.product_id
                        WHERE qi.quotation_id = ?";

            $fetchedItems = $this->db->fetchAll($itemsSql, [$quotationId]);

            // Verify completeness for each item
            $this->assertCount(count($items), $fetchedItems, 'Should fetch all items');
            
            foreach ($fetchedItems as $item) {
                $this->verifyQuotationItemCompleteness($item);
            }

            // Clean up
            $this->db->delete('quotation_items', "quotation_id = $quotationId");
            $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
            
            $key = array_search($quotationId, $this->testQuotationIds);
            if ($key !== false) {
                unset($this->testQuotationIds[$key]);
            }
        }

        $this->assertTrue(true, "Item completeness with edge case prices held for $iterations iterations");
    }

    /**
     * Property Test: Completeness holds for quotations with many items
     * 
     * This test verifies that completeness holds even when a quotation has
     * a large number of items.
     * 
     * **Validates: Requirements US-4.4**
     * 
     * @test
     */
    public function testQuotationItemCompletenessWithManyItems(): void
    {
        $iterations = 20;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Generate quotation with many items
            $quotationData = $this->generateRandomQuotation();
            $itemCount = rand(30, 50);
            $items = $this->generateRandomItems($itemCount);
            
            $quotationId = $this->createQuotationWithItems($quotationData, $items);

            // Fetch items
            $itemsSql = "SELECT 
                            qi.quotation_item_id,
                            qi.product_id,
                            p.name as product_name,
                            p.sku,
                            p.unit,
                            qi.quantity,
                            qi.unit_price,
                            qi.tax_percent,
                            qi.mrp,
                            qi.remarks
                        FROM quotation_items qi
                        INNER JOIN products p ON qi.product_id = p.product_id
                        WHERE qi.quotation_id = ?";

            $fetchedItems = $this->db->fetchAll($itemsSql, [$quotationId]);

            // Verify we got all items
            $this->assertCount(
                $itemCount,
                $fetchedItems,
                "Should fetch all $itemCount items"
            );

            // Verify completeness for each item
            foreach ($fetchedItems as $item) {
                $this->verifyQuotationItemCompleteness($item);
            }

            // Clean up
            $this->db->delete('quotation_items', "quotation_id = $quotationId");
            $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
            
            $key = array_search($quotationId, $this->testQuotationIds);
            if ($key !== false) {
                unset($this->testQuotationIds[$key]);
            }
        }

        $this->assertTrue(true, "Item completeness with many items held for $iterations iterations");
    }

    /**
     * Property Test: Completeness holds across different tax rates
     * 
     * This test verifies that item completeness holds regardless of the
     * tax rate applied.
     * 
     * **Validates: Requirements US-4.4**
     * 
     * @test
     */
    public function testQuotationItemCompletenessAcrossTaxRates(): void
    {
        $iterations = 30;
        $taxRates = [0, 5, 12, 18, 28];

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Generate quotation with items at each tax rate
            $quotationData = $this->generateRandomQuotation();
            $items = [];
            
            foreach ($taxRates as $taxRate) {
                if (!empty($this->testProductIds)) {
                    $items[] = [
                        'product_id' => $this->testProductIds[array_rand($this->testProductIds)],
                        'quantity' => rand(1, 100),
                        'unit_price' => round(rand(100, 10000) / 100, 2),
                        'tax_percent' => $taxRate,
                        'mrp' => round(rand(150, 15000) / 100, 2),
                        'remarks' => "Tax rate: $taxRate%"
                    ];
                }
            }
            
            $quotationId = $this->createQuotationWithItems($quotationData, $items);

            // Fetch items
            $itemsSql = "SELECT 
                            qi.quotation_item_id,
                            qi.product_id,
                            p.name as product_name,
                            p.sku,
                            p.unit,
                            qi.quantity,
                            qi.unit_price,
                            qi.tax_percent,
                            qi.mrp,
                            qi.remarks
                        FROM quotation_items qi
                        INNER JOIN products p ON qi.product_id = p.product_id
                        WHERE qi.quotation_id = ?";

            $fetchedItems = $this->db->fetchAll($itemsSql, [$quotationId]);

            // Verify completeness for each tax rate
            $this->assertCount(count($taxRates), $fetchedItems, 'Should fetch all items');
            
            foreach ($fetchedItems as $item) {
                $this->verifyQuotationItemCompleteness($item);
                $this->assertContains(
                    (int)(float)$item['tax_percent'],
                    $taxRates,
                    'Tax rate should be one of the valid rates'
                );
            }

            // Clean up
            $this->db->delete('quotation_items', "quotation_id = $quotationId");
            $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
            
            $key = array_search($quotationId, $this->testQuotationIds);
            if ($key !== false) {
                unset($this->testQuotationIds[$key]);
            }
        }

        $this->assertTrue(true, "Item completeness across tax rates held for $iterations iterations");
    }
}
