<?php
/**
 * Property-Based Test for Quotation Data Completeness
 * 
 * Property 7: Quotation Record Completeness
 * Validates: Requirements US-4.2
 * 
 * Property: For any quotation record returned by the API, it should include 
 * all required fields: quotation_no, quotation_date, valid_until, items_count, 
 * and status with valid values.
 * 
 * @feature purchase-quotation-system
 * @property 7
 */

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use System\Database;

class QuotationDataCompletenessPropertyTest extends TestCase
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
        $products = $this->db->fetchAll("SELECT product_id FROM products LIMIT 5");
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
            'quotation_no' => 'PROP-COMPLETE-' . uniqid() . '-' . rand(1000, 9999),
            'quotation_date' => $quotationDate,
            'valid_until' => $validUntil,
            'status' => ['active', 'expired', 'cancelled'][rand(0, 2)],
            'supplier_reference' => 'REF-' . rand(10000, 99999),
            'remarks' => 'Property test for data completeness'
        ];
    }

    /**
     * Create a quotation in the database with random items
     */
    private function createQuotation(array $quotationData): int
    {
        $quotationId = (int)$this->db->insert('supplier_quotations', $quotationData);
        $this->testQuotationIds[] = $quotationId;

        // Add random items to the quotation
        $itemCount = rand(1, 10);
        for ($i = 0; $i < $itemCount; $i++) {
            if (!empty($this->testProductIds)) {
                $this->db->insert('quotation_items', [
                    'quotation_id' => $quotationId,
                    'product_id' => $this->testProductIds[array_rand($this->testProductIds)],
                    'quantity' => rand(10, 100),
                    'unit_price' => round(rand(100, 10000) / 100, 2),
                    'tax_percent' => [0, 5, 12, 18, 28][rand(0, 4)],
                    'mrp' => round(rand(150, 15000) / 100, 2)
                ]);
            }
        }

        return $quotationId;
    }

    /**
     * Verify that a quotation record has all required fields with valid values
     */
    private function verifyQuotationCompleteness(array $quotation): void
    {
        // Required field: quotation_no
        $this->assertArrayHasKey(
            'quotation_no',
            $quotation,
            'Quotation record must have quotation_no field'
        );
        $this->assertNotEmpty(
            $quotation['quotation_no'],
            'quotation_no must not be empty'
        );
        $this->assertIsString(
            $quotation['quotation_no'],
            'quotation_no must be a string'
        );

        // Required field: quotation_date
        $this->assertArrayHasKey(
            'quotation_date',
            $quotation,
            'Quotation record must have quotation_date field'
        );
        $this->assertNotEmpty(
            $quotation['quotation_date'],
            'quotation_date must not be empty'
        );
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}$/',
            $quotation['quotation_date'],
            'quotation_date must be in YYYY-MM-DD format'
        );
        // Verify it's a valid date
        $dateTime = \DateTime::createFromFormat('Y-m-d', $quotation['quotation_date']);
        $this->assertNotFalse(
            $dateTime,
            'quotation_date must be a valid date'
        );

        // Required field: valid_until
        $this->assertArrayHasKey(
            'valid_until',
            $quotation,
            'Quotation record must have valid_until field'
        );
        $this->assertNotEmpty(
            $quotation['valid_until'],
            'valid_until must not be empty'
        );
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}$/',
            $quotation['valid_until'],
            'valid_until must be in YYYY-MM-DD format'
        );
        // Verify it's a valid date
        $validUntilDateTime = \DateTime::createFromFormat('Y-m-d', $quotation['valid_until']);
        $this->assertNotFalse(
            $validUntilDateTime,
            'valid_until must be a valid date'
        );

        // Required field: items_count
        $this->assertArrayHasKey(
            'items_count',
            $quotation,
            'Quotation record must have items_count field'
        );
        $this->assertIsNumeric(
            $quotation['items_count'],
            'items_count must be numeric'
        );
        $this->assertGreaterThanOrEqual(
            0,
            (int)$quotation['items_count'],
            'items_count must be non-negative'
        );

        // Required field: status
        $this->assertArrayHasKey(
            'status',
            $quotation,
            'Quotation record must have status field'
        );
        $this->assertNotEmpty(
            $quotation['status'],
            'status must not be empty'
        );
        $this->assertContains(
            $quotation['status'],
            ['active', 'expired', 'cancelled'],
            'status must be one of: active, expired, cancelled'
        );
    }

    /**
     * Property Test: All quotation records have complete data
     * 
     * This test generates random quotations and verifies that all returned
     * quotation records include all required fields with valid values.
     * 
     * @test
     */
    public function testQuotationDataCompletenessPropertyWithRandomData(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;
        $quotationsPerIteration = 10;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Generate and create random quotations
            $createdQuotations = [];
            for ($i = 0; $i < $quotationsPerIteration; $i++) {
                $quotationData = $this->generateRandomQuotation();
                $quotationId = $this->createQuotation($quotationData);
                $createdQuotations[] = array_merge($quotationData, ['quotation_id' => $quotationId]);
            }

            // Fetch quotations using the same query as the API endpoint
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.quotation_no,
                        sq.supplier_id,
                        sq.quotation_date,
                        sq.valid_until,
                        sq.status,
                        sq.supplier_reference,
                        sq.remarks,
                        sq.created_at,
                        COUNT(qi.quotation_item_id) as items_count
                    FROM supplier_quotations sq
                    LEFT JOIN quotation_items qi ON sq.quotation_id = qi.quotation_id
                    WHERE sq.quotation_id IN (" . implode(',', array_column($createdQuotations, 'quotation_id')) . ")
                    GROUP BY sq.quotation_id, sq.quotation_no, sq.supplier_id, 
                             sq.quotation_date, sq.valid_until, sq.status, 
                             sq.supplier_reference, sq.remarks, sq.created_at
                    ORDER BY sq.quotation_date DESC, sq.quotation_id DESC";

            $fetchedQuotations = $this->db->fetchAll($sql);

            // Verify completeness for each quotation
            $this->assertNotEmpty($fetchedQuotations, 'Should fetch quotations');
            foreach ($fetchedQuotations as $quotation) {
                $this->verifyQuotationCompleteness($quotation);
            }

            // Clean up this iteration's quotations
            foreach ($createdQuotations as $quotation) {
                $quotationId = $quotation['quotation_id'];
                $this->db->delete('quotation_items', "quotation_id = $quotationId");
                $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
                
                // Remove from cleanup list
                $key = array_search($quotationId, $this->testQuotationIds);
                if ($key !== false) {
                    unset($this->testQuotationIds[$key]);
                }
            }
        }

        // If we got here, the property held for all iterations
        $this->assertTrue(true, "Quotation data completeness property held for $iterations iterations");
    }

    /**
     * Property Test: Completeness holds for quotations with zero items
     * 
     * This test verifies that quotations with no items still have complete
     * data with items_count = 0.
     * 
     * @test
     */
    public function testQuotationCompletenessWithZeroItems(): void
    {
        $iterations = 30;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Create quotation without items
            $quotationData = $this->generateRandomQuotation();
            $quotationId = (int)$this->db->insert('supplier_quotations', $quotationData);
            $this->testQuotationIds[] = $quotationId;

            // Fetch the quotation
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.quotation_no,
                        sq.quotation_date,
                        sq.valid_until,
                        sq.status,
                        COUNT(qi.quotation_item_id) as items_count
                    FROM supplier_quotations sq
                    LEFT JOIN quotation_items qi ON sq.quotation_id = qi.quotation_id
                    WHERE sq.quotation_id = ?
                    GROUP BY sq.quotation_id, sq.quotation_no, sq.quotation_date, 
                             sq.valid_until, sq.status";

            $quotations = $this->db->fetchAll($sql, [$quotationId]);
            
            $this->assertCount(1, $quotations, 'Should fetch exactly one quotation');
            $quotation = $quotations[0];

            // Verify completeness
            $this->verifyQuotationCompleteness($quotation);
            
            // Verify items_count is 0
            $this->assertEquals(
                0,
                (int)$quotation['items_count'],
                'Quotation with no items should have items_count = 0'
            );

            // Clean up
            $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
            $key = array_search($quotationId, $this->testQuotationIds);
            if ($key !== false) {
                unset($this->testQuotationIds[$key]);
            }
        }

        $this->assertTrue(true, "Completeness with zero items held for $iterations iterations");
    }

    /**
     * Property Test: Completeness holds for quotations with many items
     * 
     * This test verifies that quotations with many items still have complete
     * data with accurate items_count.
     * 
     * @test
     */
    public function testQuotationCompletenessWithManyItems(): void
    {
        $iterations = 30;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Create quotation with many items
            $quotationData = $this->generateRandomQuotation();
            $quotationId = (int)$this->db->insert('supplier_quotations', $quotationData);
            $this->testQuotationIds[] = $quotationId;

            // Add many items
            $itemCount = rand(20, 50);
            for ($i = 0; $i < $itemCount; $i++) {
                if (!empty($this->testProductIds)) {
                    $this->db->insert('quotation_items', [
                        'quotation_id' => $quotationId,
                        'product_id' => $this->testProductIds[array_rand($this->testProductIds)],
                        'quantity' => rand(10, 100),
                        'unit_price' => round(rand(100, 10000) / 100, 2),
                        'tax_percent' => [0, 5, 12, 18, 28][rand(0, 4)],
                        'mrp' => round(rand(150, 15000) / 100, 2)
                    ]);
                }
            }

            // Fetch the quotation
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.quotation_no,
                        sq.quotation_date,
                        sq.valid_until,
                        sq.status,
                        COUNT(qi.quotation_item_id) as items_count
                    FROM supplier_quotations sq
                    LEFT JOIN quotation_items qi ON sq.quotation_id = qi.quotation_id
                    WHERE sq.quotation_id = ?
                    GROUP BY sq.quotation_id, sq.quotation_no, sq.quotation_date, 
                             sq.valid_until, sq.status";

            $quotations = $this->db->fetchAll($sql, [$quotationId]);
            
            $this->assertCount(1, $quotations, 'Should fetch exactly one quotation');
            $quotation = $quotations[0];

            // Verify completeness
            $this->verifyQuotationCompleteness($quotation);
            
            // Verify items_count matches actual count
            $this->assertEquals(
                $itemCount,
                (int)$quotation['items_count'],
                sprintf(
                    'Quotation should have items_count = %d, got %d',
                    $itemCount,
                    (int)$quotation['items_count']
                )
            );

            // Clean up
            $this->db->delete('quotation_items', "quotation_id = $quotationId");
            $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
            $key = array_search($quotationId, $this->testQuotationIds);
            if ($key !== false) {
                unset($this->testQuotationIds[$key]);
            }
        }

        $this->assertTrue(true, "Completeness with many items held for $iterations iterations");
    }

    /**
     * Property Test: Completeness holds across different statuses
     * 
     * This test verifies that quotation data completeness holds regardless
     * of the quotation status.
     * 
     * @test
     */
    public function testQuotationCompletenessAcrossStatuses(): void
    {
        $iterations = 30;
        $statuses = ['active', 'expired', 'cancelled'];

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            $createdQuotations = [];
            
            // Create quotations with each status
            foreach ($statuses as $status) {
                $quotationData = $this->generateRandomQuotation();
                $quotationData['status'] = $status;
                $quotationId = $this->createQuotation($quotationData);
                $createdQuotations[] = array_merge($quotationData, ['quotation_id' => $quotationId]);
            }

            // Fetch all quotations
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.quotation_no,
                        sq.quotation_date,
                        sq.valid_until,
                        sq.status,
                        COUNT(qi.quotation_item_id) as items_count
                    FROM supplier_quotations sq
                    LEFT JOIN quotation_items qi ON sq.quotation_id = qi.quotation_id
                    WHERE sq.quotation_id IN (" . implode(',', array_column($createdQuotations, 'quotation_id')) . ")
                    GROUP BY sq.quotation_id, sq.quotation_no, sq.quotation_date, 
                             sq.valid_until, sq.status";

            $fetchedQuotations = $this->db->fetchAll($sql);

            // Verify completeness for each status
            $this->assertCount(count($statuses), $fetchedQuotations, 'Should fetch all quotations');
            
            foreach ($fetchedQuotations as $quotation) {
                $this->verifyQuotationCompleteness($quotation);
                $this->assertContains(
                    $quotation['status'],
                    $statuses,
                    'Status should be one of the valid statuses'
                );
            }

            // Clean up
            foreach ($createdQuotations as $quotation) {
                $quotationId = $quotation['quotation_id'];
                $this->db->delete('quotation_items', "quotation_id = $quotationId");
                $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
                
                $key = array_search($quotationId, $this->testQuotationIds);
                if ($key !== false) {
                    unset($this->testQuotationIds[$key]);
                }
            }
        }

        $this->assertTrue(true, "Completeness across statuses held for $iterations iterations");
    }

    /**
     * Property Test: Completeness holds for quotations with edge case dates
     * 
     * This test verifies that quotation data completeness holds for quotations
     * with various date scenarios (old dates, recent dates, future valid_until).
     * 
     * @test
     */
    public function testQuotationCompletenessWithEdgeCaseDates(): void
    {
        $iterations = 30;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            $createdQuotations = [];
            
            // Create quotations with different date scenarios
            $dateScenarios = [
                // Very old quotation
                ['days_ago' => 730, 'valid_days' => 30],
                // Recent quotation
                ['days_ago' => 1, 'valid_days' => 60],
                // Today's quotation
                ['days_ago' => 0, 'valid_days' => 90],
                // Quotation with long validity
                ['days_ago' => 30, 'valid_days' => 365],
            ];
            
            foreach ($dateScenarios as $scenario) {
                $quotationDate = date('Y-m-d', strtotime("-{$scenario['days_ago']} days"));
                $validUntil = date('Y-m-d', strtotime($quotationDate . " +{$scenario['valid_days']} days"));
                
                $quotationData = [
                    'supplier_id' => $this->testSupplierIds[array_rand($this->testSupplierIds)],
                    'quotation_no' => 'DATE-EDGE-' . uniqid() . '-' . rand(1000, 9999),
                    'quotation_date' => $quotationDate,
                    'valid_until' => $validUntil,
                    'status' => 'active',
                    'supplier_reference' => 'REF-' . rand(10000, 99999),
                    'remarks' => 'Edge case date test'
                ];
                
                $quotationId = $this->createQuotation($quotationData);
                $createdQuotations[] = array_merge($quotationData, ['quotation_id' => $quotationId]);
            }

            // Fetch all quotations
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.quotation_no,
                        sq.quotation_date,
                        sq.valid_until,
                        sq.status,
                        COUNT(qi.quotation_item_id) as items_count
                    FROM supplier_quotations sq
                    LEFT JOIN quotation_items qi ON sq.quotation_id = qi.quotation_id
                    WHERE sq.quotation_id IN (" . implode(',', array_column($createdQuotations, 'quotation_id')) . ")
                    GROUP BY sq.quotation_id, sq.quotation_no, sq.quotation_date, 
                             sq.valid_until, sq.status";

            $fetchedQuotations = $this->db->fetchAll($sql);

            // Verify completeness for all date scenarios
            $this->assertCount(count($dateScenarios), $fetchedQuotations, 'Should fetch all quotations');
            
            foreach ($fetchedQuotations as $quotation) {
                $this->verifyQuotationCompleteness($quotation);
                
                // Verify valid_until is after quotation_date
                $quotationDate = new \DateTime($quotation['quotation_date']);
                $validUntil = new \DateTime($quotation['valid_until']);
                $this->assertGreaterThanOrEqual(
                    $quotationDate->getTimestamp(),
                    $validUntil->getTimestamp(),
                    'valid_until should be on or after quotation_date'
                );
            }

            // Clean up
            foreach ($createdQuotations as $quotation) {
                $quotationId = $quotation['quotation_id'];
                $this->db->delete('quotation_items', "quotation_id = $quotationId");
                $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
                
                $key = array_search($quotationId, $this->testQuotationIds);
                if ($key !== false) {
                    unset($this->testQuotationIds[$key]);
                }
            }
        }

        $this->assertTrue(true, "Completeness with edge case dates held for $iterations iterations");
    }
}
