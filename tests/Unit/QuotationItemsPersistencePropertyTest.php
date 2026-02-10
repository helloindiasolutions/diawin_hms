<?php
/**
 * Property-Based Test for Quotation Items Persistence
 * 
 * Property 11: Quotation Items Persistence
 * Validates: Requirements US-6.3
 * 
 * Property: For any quotation created with N items, querying the quotation 
 * details should return exactly N items with matching product_ids, quantities, 
 * and prices.
 * 
 * @feature purchase-quotation-system
 * @property 11
 */

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use System\Database;

class QuotationItemsPersistencePropertyTest extends TestCase
{
    private Database $db;
    private array $testSupplierIds = [];
    private array $testProductIds = [];
    private array $testQuotationIds = [];
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
        $suppliers = $this->db->fetchAll("SELECT supplier_id FROM suppliers LIMIT 3");
        if (count($suppliers) < 3) {
            for ($i = count($suppliers); $i < 3; $i++) {
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
        $products = $this->db->fetchAll("SELECT product_id FROM products LIMIT 20");
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
     * Generate a random quotation with N items
     */
    private function generateQuotationWithItems(int $itemCount): array
    {
        $quotationDate = date('Y-m-d', strtotime('-' . rand(0, 30) . ' days'));
        $validUntil = date('Y-m-d', strtotime($quotationDate . ' +' . rand(30, 90) . ' days'));
        
        $items = [];
        for ($i = 0; $i < $itemCount; $i++) {
            $items[] = [
                'product_id' => $this->testProductIds[array_rand($this->testProductIds)],
                'quantity' => rand(1, 100),
                'unit_price' => round(rand(100, 10000) / 100, 2),
                'tax_percent' => [0, 5, 12, 18, 28][rand(0, 4)],
                'mrp' => round(rand(150, 15000) / 100, 2),
                'remarks' => 'Test item ' . ($i + 1)
            ];
        }
        
        return [
            'supplier_id' => $this->testSupplierIds[array_rand($this->testSupplierIds)],
            'quotation_no' => 'PERSIST-TEST-' . uniqid() . '-' . rand(1000, 9999),
            'quotation_date' => $quotationDate,
            'valid_until' => $validUntil,
            'status' => 'active',
            'supplier_reference' => 'REF-' . rand(10000, 99999),
            'remarks' => 'Persistence test quotation',
            'items' => $items
        ];
    }

    /**
     * Create a quotation with items in the database
     */
    private function createQuotation(array $quotationData): int
    {
        $this->db->beginTransaction();
        
        try {
            $quotationId = (int)$this->db->insert('supplier_quotations', [
                'supplier_id' => $quotationData['supplier_id'],
                'quotation_no' => $quotationData['quotation_no'],
                'quotation_date' => $quotationData['quotation_date'],
                'valid_until' => $quotationData['valid_until'],
                'supplier_reference' => $quotationData['supplier_reference'] ?? null,
                'status' => $quotationData['status'] ?? 'active',
                'remarks' => $quotationData['remarks'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->testQuotationIds[] = $quotationId;
            
            foreach ($quotationData['items'] as $item) {
                $this->db->insert('quotation_items', [
                    'quotation_id' => $quotationId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_percent' => $item['tax_percent'] ?? 0.00,
                    'mrp' => $item['mrp'] ?? 0.00,
                    'remarks' => $item['remarks'] ?? null
                ]);
            }
            
            $this->db->commit();
            return $quotationId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Fetch quotation details with items (simulating API call)
     */
    private function fetchQuotationDetails(int $quotationId): array
    {
        // Fetch quotation header
        $quotationSql = "SELECT 
                            sq.quotation_id,
                            sq.quotation_no,
                            sq.supplier_id,
                            sq.quotation_date,
                            sq.valid_until,
                            sq.status,
                            sq.supplier_reference,
                            sq.remarks
                        FROM supplier_quotations sq
                        WHERE sq.quotation_id = ?";
        
        $quotation = $this->db->fetch($quotationSql, [$quotationId]);
        
        if (!$quotation) {
            return [];
        }

        // Fetch all quotation items
        $itemsSql = "SELECT 
                        qi.quotation_item_id,
                        qi.product_id,
                        qi.quantity,
                        qi.unit_price,
                        qi.tax_percent,
                        qi.mrp,
                        qi.remarks
                    FROM quotation_items qi
                    WHERE qi.quotation_id = ?
                    ORDER BY qi.quotation_item_id ASC";
        
        $items = $this->db->fetchAll($itemsSql, [$quotationId]);

        return [
            'quotation' => $quotation,
            'items' => $items
        ];
    }

    /**
     * Property Test: Quotation with N items should return exactly N items
     * 
     * @test
     */
    public function testQuotationReturnsExactNumberOfItems(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Generate random number of items (1 to 20)
            $itemCount = rand(1, 20);
            
            // Create quotation with N items
            $quotationData = $this->generateQuotationWithItems($itemCount);
            $quotationId = $this->createQuotation($quotationData);
            
            // Fetch quotation details
            $result = $this->fetchQuotationDetails($quotationId);
            
            // Verify exactly N items are returned
            $this->assertArrayHasKey('items', $result, 'Result should contain items array');
            $this->assertCount(
                $itemCount,
                $result['items'],
                "Quotation created with $itemCount items should return exactly $itemCount items"
            );
            
            // Clean up
            $this->db->delete('quotation_items', "quotation_id = $quotationId");
            $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
            
            $key = array_search($quotationId, $this->testQuotationIds);
            if ($key !== false) {
                unset($this->testQuotationIds[$key]);
            }
        }
        
        $this->assertTrue(true, "Item count persistence held for $iterations iterations");
    }

    /**
     * Property Test: All item product_ids should match the created items
     * 
     * @test
     */
    public function testItemProductIdsArePersisted(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Create quotation with random items
            $itemCount = rand(1, 15);
            $quotationData = $this->generateQuotationWithItems($itemCount);
            $quotationId = $this->createQuotation($quotationData);
            
            // Fetch quotation details
            $result = $this->fetchQuotationDetails($quotationId);
            
            // Extract product_ids from created and fetched items
            $createdProductIds = array_column($quotationData['items'], 'product_id');
            $fetchedProductIds = array_column($result['items'], 'product_id');
            
            // Sort both arrays for comparison (order might differ)
            sort($createdProductIds);
            sort($fetchedProductIds);
            
            // Verify all product_ids match
            $this->assertEquals(
                $createdProductIds,
                $fetchedProductIds,
                'Fetched product_ids should match created product_ids'
            );
            
            // Clean up
            $this->db->delete('quotation_items', "quotation_id = $quotationId");
            $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
            
            $key = array_search($quotationId, $this->testQuotationIds);
            if ($key !== false) {
                unset($this->testQuotationIds[$key]);
            }
        }
        
        $this->assertTrue(true, "Product ID persistence held for $iterations iterations");
    }

    /**
     * Property Test: All item quantities should match the created items
     * 
     * @test
     */
    public function testItemQuantitiesArePersisted(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Create quotation with random items
            $itemCount = rand(1, 15);
            $quotationData = $this->generateQuotationWithItems($itemCount);
            $quotationId = $this->createQuotation($quotationData);
            
            // Fetch quotation details
            $result = $this->fetchQuotationDetails($quotationId);
            
            // Verify each item's quantity matches
            foreach ($quotationData['items'] as $index => $createdItem) {
                $fetchedItem = $result['items'][$index];
                
                $this->assertEquals(
                    $createdItem['quantity'],
                    $fetchedItem['quantity'],
                    "Item $index quantity should match"
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
        
        $this->assertTrue(true, "Quantity persistence held for $iterations iterations");
    }

    /**
     * Property Test: All item unit_prices should match the created items
     * 
     * @test
     */
    public function testItemUnitPricesArePersisted(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Create quotation with random items
            $itemCount = rand(1, 15);
            $quotationData = $this->generateQuotationWithItems($itemCount);
            $quotationId = $this->createQuotation($quotationData);
            
            // Fetch quotation details
            $result = $this->fetchQuotationDetails($quotationId);
            
            // Verify each item's unit_price matches
            foreach ($quotationData['items'] as $index => $createdItem) {
                $fetchedItem = $result['items'][$index];
                
                $this->assertEquals(
                    $createdItem['unit_price'],
                    (float)$fetchedItem['unit_price'],
                    "Item $index unit_price should match"
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
        
        $this->assertTrue(true, "Unit price persistence held for $iterations iterations");
    }

    /**
     * Property Test: All item tax_percent values should match the created items
     * 
     * @test
     */
    public function testItemTaxPercentArePersisted(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Create quotation with random items
            $itemCount = rand(1, 15);
            $quotationData = $this->generateQuotationWithItems($itemCount);
            $quotationId = $this->createQuotation($quotationData);
            
            // Fetch quotation details
            $result = $this->fetchQuotationDetails($quotationId);
            
            // Verify each item's tax_percent matches
            foreach ($quotationData['items'] as $index => $createdItem) {
                $fetchedItem = $result['items'][$index];
                
                $this->assertEquals(
                    $createdItem['tax_percent'],
                    (float)$fetchedItem['tax_percent'],
                    "Item $index tax_percent should match"
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
        
        $this->assertTrue(true, "Tax percent persistence held for $iterations iterations");
    }

    /**
     * Property Test: All item MRP values should match the created items
     * 
     * @test
     */
    public function testItemMRPsArePersisted(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Create quotation with random items
            $itemCount = rand(1, 15);
            $quotationData = $this->generateQuotationWithItems($itemCount);
            $quotationId = $this->createQuotation($quotationData);
            
            // Fetch quotation details
            $result = $this->fetchQuotationDetails($quotationId);
            
            // Verify each item's MRP matches
            foreach ($quotationData['items'] as $index => $createdItem) {
                $fetchedItem = $result['items'][$index];
                
                $this->assertEquals(
                    $createdItem['mrp'],
                    (float)$fetchedItem['mrp'],
                    "Item $index MRP should match"
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
        
        $this->assertTrue(true, "MRP persistence held for $iterations iterations");
    }

    /**
     * Property Test: Complete item data integrity (all fields together)
     * 
     * @test
     */
    public function testCompleteItemDataIntegrity(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Create quotation with random items
            $itemCount = rand(1, 15);
            $quotationData = $this->generateQuotationWithItems($itemCount);
            $quotationId = $this->createQuotation($quotationData);
            
            // Fetch quotation details
            $result = $this->fetchQuotationDetails($quotationId);
            
            // Verify complete data integrity for each item
            $this->assertCount($itemCount, $result['items'], 'Should have correct number of items');
            
            foreach ($quotationData['items'] as $index => $createdItem) {
                $fetchedItem = $result['items'][$index];
                
                // Verify all fields match
                $this->assertEquals(
                    $createdItem['product_id'],
                    $fetchedItem['product_id'],
                    "Item $index product_id should match"
                );
                
                $this->assertEquals(
                    $createdItem['quantity'],
                    $fetchedItem['quantity'],
                    "Item $index quantity should match"
                );
                
                $this->assertEquals(
                    $createdItem['unit_price'],
                    (float)$fetchedItem['unit_price'],
                    "Item $index unit_price should match"
                );
                
                $this->assertEquals(
                    $createdItem['tax_percent'],
                    (float)$fetchedItem['tax_percent'],
                    "Item $index tax_percent should match"
                );
                
                $this->assertEquals(
                    $createdItem['mrp'],
                    (float)$fetchedItem['mrp'],
                    "Item $index MRP should match"
                );
                
                // Remarks can be null, so handle that
                $expectedRemarks = $createdItem['remarks'] ?? null;
                $actualRemarks = $fetchedItem['remarks'] ?? null;
                $this->assertEquals(
                    $expectedRemarks,
                    $actualRemarks,
                    "Item $index remarks should match"
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
        
        $this->assertTrue(true, "Complete item data integrity held for $iterations iterations");
    }

    /**
     * Property Test: Items persist correctly with edge case values
     * 
     * @test
     */
    public function testItemsPersistWithEdgeCaseValues(): void
    {
        $iterations = 50;
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Create quotation with edge case values
            $quotationDate = date('Y-m-d');
            $validUntil = date('Y-m-d', strtotime('+30 days'));
            
            $quotationData = [
                'supplier_id' => $this->testSupplierIds[array_rand($this->testSupplierIds)],
                'quotation_no' => 'EDGE-TEST-' . uniqid() . '-' . rand(1000, 9999),
                'quotation_date' => $quotationDate,
                'valid_until' => $validUntil,
                'status' => 'active',
                'items' => [
                    // Edge case: minimum values
                    [
                        'product_id' => $this->testProductIds[0],
                        'quantity' => 1,
                        'unit_price' => 0.01,
                        'tax_percent' => 0,
                        'mrp' => 0.01
                    ],
                    // Edge case: maximum typical values
                    [
                        'product_id' => $this->testProductIds[1],
                        'quantity' => 999999,
                        'unit_price' => 99999.99,
                        'tax_percent' => 100,
                        'mrp' => 99999.99
                    ],
                    // Edge case: zero tax
                    [
                        'product_id' => $this->testProductIds[2],
                        'quantity' => 50,
                        'unit_price' => 100.00,
                        'tax_percent' => 0,
                        'mrp' => 150.00
                    ]
                ]
            ];
            
            $quotationId = $this->createQuotation($quotationData);
            
            // Fetch quotation details
            $result = $this->fetchQuotationDetails($quotationId);
            
            // Verify all edge case items are persisted correctly
            $this->assertCount(3, $result['items'], 'Should have 3 items');
            
            foreach ($quotationData['items'] as $index => $createdItem) {
                $fetchedItem = $result['items'][$index];
                
                $this->assertEquals($createdItem['product_id'], $fetchedItem['product_id']);
                $this->assertEquals($createdItem['quantity'], $fetchedItem['quantity']);
                $this->assertEquals($createdItem['unit_price'], (float)$fetchedItem['unit_price']);
                $this->assertEquals($createdItem['tax_percent'], (float)$fetchedItem['tax_percent']);
                $this->assertEquals($createdItem['mrp'], (float)$fetchedItem['mrp']);
            }
            
            // Clean up
            $this->db->delete('quotation_items', "quotation_id = $quotationId");
            $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
            
            $key = array_search($quotationId, $this->testQuotationIds);
            if ($key !== false) {
                unset($this->testQuotationIds[$key]);
            }
        }
        
        $this->assertTrue(true, "Edge case value persistence held for $iterations iterations");
    }
}
