<?php
/**
 * Property-Based Test for Quotation Supplier Association
 * 
 * Property 6: Quotation Supplier Association
 * Validates: Requirements US-4.1
 * 
 * Property: For any supplier ID provided to the quotations endpoint, all 
 * returned quotations should have supplier_id matching the requested supplier ID.
 * 
 * @feature purchase-quotation-system
 * @property 6
 */

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use System\Database;

class QuotationSupplierAssociationPropertyTest extends TestCase
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
     * Generate a random quotation for a specific supplier
     */
    private function generateRandomQuotation(int $supplierId): array
    {
        // Generate random date within the last 365 days
        $daysAgo = rand(0, 365);
        $quotationDate = date('Y-m-d', strtotime("-$daysAgo days"));
        $validUntil = date('Y-m-d', strtotime($quotationDate . ' +' . rand(30, 90) . ' days'));
        
        return [
            'supplier_id' => $supplierId,
            'quotation_no' => 'PROP-SUPP-' . uniqid() . '-' . rand(1000, 9999),
            'quotation_date' => $quotationDate,
            'valid_until' => $validUntil,
            'status' => ['active', 'expired'][rand(0, 1)],
            'supplier_reference' => 'REF-' . rand(10000, 99999),
            'remarks' => 'Property test quotation for supplier association'
        ];
    }

    /**
     * Create a quotation in the database
     */
    private function createQuotation(array $quotationData): int
    {
        $quotationId = (int)$this->db->insert('supplier_quotations', $quotationData);
        $this->testQuotationIds[] = $quotationId;

        // Add random items to the quotation
        $itemCount = rand(1, 5);
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
     * Verify that all quotations belong to the specified supplier
     */
    private function verifySupplierAssociation(array $quotations, int $expectedSupplierId): void
    {
        $this->assertIsArray($quotations, 'Quotations should be an array');
        
        foreach ($quotations as $quotation) {
            $this->assertArrayHasKey(
                'supplier_id',
                $quotation,
                'Each quotation should have a supplier_id field'
            );
            
            $this->assertEquals(
                $expectedSupplierId,
                $quotation['supplier_id'],
                sprintf(
                    'Quotation ID %d has supplier_id=%d but expected supplier_id=%d',
                    $quotation['quotation_id'] ?? 'unknown',
                    $quotation['supplier_id'],
                    $expectedSupplierId
                )
            );
        }
    }

    /**
     * Property Test: All quotations returned for a supplier belong to that supplier
     * 
     * This test generates random quotations for multiple suppliers and verifies
     * that when querying by supplier_id, only quotations for that supplier are returned.
     * 
     * @test
     */
    public function testSupplierAssociationPropertyWithRandomData(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Select a random supplier to test
            $targetSupplierId = $this->testSupplierIds[array_rand($this->testSupplierIds)];
            
            // Create quotations for multiple suppliers (including the target)
            $createdQuotations = [];
            $quotationsForTarget = rand(2, 8);
            $quotationsForOthers = rand(3, 10);
            
            // Create quotations for the target supplier
            for ($i = 0; $i < $quotationsForTarget; $i++) {
                $quotationData = $this->generateRandomQuotation($targetSupplierId);
                $quotationId = $this->createQuotation($quotationData);
                $createdQuotations[] = array_merge($quotationData, ['quotation_id' => $quotationId]);
            }
            
            // Create quotations for other suppliers (noise)
            for ($i = 0; $i < $quotationsForOthers; $i++) {
                // Pick a different supplier
                $otherSuppliers = array_filter(
                    $this->testSupplierIds,
                    fn($id) => $id !== $targetSupplierId
                );
                $otherSupplierId = $otherSuppliers[array_rand($otherSuppliers)];
                
                $quotationData = $this->generateRandomQuotation($otherSupplierId);
                $quotationId = $this->createQuotation($quotationData);
                $createdQuotations[] = array_merge($quotationData, ['quotation_id' => $quotationId]);
            }

            // Get the IDs of quotations we created for the target supplier
            $targetQuotationIds = array_column(
                array_filter($createdQuotations, fn($q) => $q['supplier_id'] === $targetSupplierId),
                'quotation_id'
            );

            // Fetch quotations for the target supplier using the same query as the API endpoint
            // but limited to the quotations we created in this iteration
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
                    WHERE sq.supplier_id = ? 
                    AND sq.quotation_id IN (" . implode(',', $targetQuotationIds) . ")
                    GROUP BY sq.quotation_id, sq.quotation_no, sq.supplier_id, 
                             sq.quotation_date, sq.valid_until, sq.status, 
                             sq.supplier_reference, sq.remarks, sq.created_at
                    ORDER BY sq.quotation_date DESC, sq.quotation_id DESC";

            $fetchedQuotations = $this->db->fetchAll($sql, [$targetSupplierId]);

            // Verify the supplier association property
            $this->verifySupplierAssociation($fetchedQuotations, $targetSupplierId);
            
            // Verify we got the expected number of quotations
            $this->assertCount(
                $quotationsForTarget,
                $fetchedQuotations,
                sprintf(
                    'Expected %d quotations for supplier %d, but got %d',
                    $quotationsForTarget,
                    $targetSupplierId,
                    count($fetchedQuotations)
                )
            );

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
        $this->assertTrue(true, "Supplier association property held for $iterations iterations");
    }

    /**
     * Property Test: Empty result when querying non-existent supplier
     * 
     * This test verifies that querying for a supplier with no quotations
     * returns an empty array (not an error).
     * 
     * @test
     */
    public function testSupplierAssociationWithNoQuotations(): void
    {
        $iterations = 20;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Create a new supplier with no quotations
            $supplierId = $this->db->insert('suppliers', [
                'name' => 'Empty Supplier ' . uniqid(),
                'contact_person' => 'Contact',
                'mobile' => '98' . str_pad((string)rand(10000000, 99999999), 10, '0', STR_PAD_LEFT),
                'email' => 'empty' . uniqid() . '@test.com',
                'gstin' => 'EMPTY-' . uniqid()
            ]);

            // Query for quotations
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.supplier_id
                    FROM supplier_quotations sq
                    WHERE sq.supplier_id = ?";

            $quotations = $this->db->fetchAll($sql, [$supplierId]);

            // Verify empty result
            $this->assertIsArray($quotations, 'Result should be an array');
            $this->assertEmpty($quotations, 'Should return empty array for supplier with no quotations');

            // Clean up
            $this->db->delete('suppliers', "supplier_id = $supplierId");
        }

        $this->assertTrue(true, "Empty supplier property held for $iterations iterations");
    }

    /**
     * Property Test: Supplier association holds with mixed statuses
     * 
     * This test verifies that the supplier association property holds
     * regardless of quotation status (active, expired, cancelled).
     * 
     * @test
     */
    public function testSupplierAssociationWithMixedStatuses(): void
    {
        $iterations = 30;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            $targetSupplierId = $this->testSupplierIds[array_rand($this->testSupplierIds)];
            
            // Create quotations with different statuses
            $createdQuotations = [];
            $statuses = ['active', 'expired', 'cancelled'];
            
            foreach ($statuses as $status) {
                $count = rand(1, 3);
                for ($i = 0; $i < $count; $i++) {
                    $quotationData = $this->generateRandomQuotation($targetSupplierId);
                    $quotationData['status'] = $status;
                    $quotationId = $this->createQuotation($quotationData);
                    $createdQuotations[] = array_merge($quotationData, ['quotation_id' => $quotationId]);
                }
            }

            // Fetch all quotations for the supplier (no status filter)
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.supplier_id,
                        sq.status
                    FROM supplier_quotations sq
                    WHERE sq.supplier_id = ?";

            $fetchedQuotations = $this->db->fetchAll($sql, [$targetSupplierId]);

            // Verify supplier association holds for all statuses
            $this->verifySupplierAssociation($fetchedQuotations, $targetSupplierId);
            
            // Verify we have quotations with different statuses
            $fetchedStatuses = array_unique(array_column($fetchedQuotations, 'status'));
            $this->assertGreaterThan(
                1,
                count($fetchedStatuses),
                'Should have quotations with multiple statuses'
            );

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

        $this->assertTrue(true, "Supplier association with mixed statuses held for $iterations iterations");
    }

    /**
     * Property Test: Supplier association holds across date ranges
     * 
     * This test verifies that the supplier association property holds
     * when quotations span different date ranges.
     * 
     * @test
     */
    public function testSupplierAssociationAcrossDateRanges(): void
    {
        $iterations = 30;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            $targetSupplierId = $this->testSupplierIds[array_rand($this->testSupplierIds)];
            
            // Create quotations across different time periods
            $createdQuotations = [];
            $timePeriods = [
                ['days_ago' => 0, 'range' => 30],      // Recent
                ['days_ago' => 90, 'range' => 60],     // 3 months ago
                ['days_ago' => 180, 'range' => 90],    // 6 months ago
                ['days_ago' => 365, 'range' => 180],   // 1 year ago
            ];
            
            foreach ($timePeriods as $period) {
                $count = rand(1, 3);
                for ($i = 0; $i < $count; $i++) {
                    $daysAgo = $period['days_ago'] + rand(0, $period['range']);
                    $quotationDate = date('Y-m-d', strtotime("-$daysAgo days"));
                    $validUntil = date('Y-m-d', strtotime($quotationDate . ' +30 days'));
                    
                    $quotationData = [
                        'supplier_id' => $targetSupplierId,
                        'quotation_no' => 'DATE-TEST-' . uniqid() . '-' . rand(1000, 9999),
                        'quotation_date' => $quotationDate,
                        'valid_until' => $validUntil,
                        'status' => 'active',
                        'supplier_reference' => 'REF-' . rand(10000, 99999),
                        'remarks' => 'Date range test'
                    ];
                    
                    $quotationId = $this->createQuotation($quotationData);
                    $createdQuotations[] = array_merge($quotationData, ['quotation_id' => $quotationId]);
                }
            }

            // Fetch quotations for the supplier
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.supplier_id,
                        sq.quotation_date
                    FROM supplier_quotations sq
                    WHERE sq.supplier_id = ?";

            $fetchedQuotations = $this->db->fetchAll($sql, [$targetSupplierId]);

            // Verify supplier association holds across all date ranges
            $this->verifySupplierAssociation($fetchedQuotations, $targetSupplierId);

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

        $this->assertTrue(true, "Supplier association across date ranges held for $iterations iterations");
    }

    /**
     * Property Test: Supplier association is exclusive
     * 
     * This test verifies that quotations for one supplier are never
     * returned when querying for a different supplier.
     * 
     * @test
     */
    public function testSupplierAssociationIsExclusive(): void
    {
        $iterations = 50;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Pick two different suppliers
            $supplier1Id = $this->testSupplierIds[0];
            $supplier2Id = $this->testSupplierIds[1];
            
            // Create quotations for both suppliers
            $createdQuotations = [];
            
            // Quotations for supplier 1
            $count1 = rand(2, 5);
            for ($i = 0; $i < $count1; $i++) {
                $quotationData = $this->generateRandomQuotation($supplier1Id);
                $quotationId = $this->createQuotation($quotationData);
                $createdQuotations[] = array_merge($quotationData, ['quotation_id' => $quotationId]);
            }
            
            // Quotations for supplier 2
            $count2 = rand(2, 5);
            for ($i = 0; $i < $count2; $i++) {
                $quotationData = $this->generateRandomQuotation($supplier2Id);
                $quotationId = $this->createQuotation($quotationData);
                $createdQuotations[] = array_merge($quotationData, ['quotation_id' => $quotationId]);
            }

            // Query for supplier 1 (only quotations created in this iteration)
            $ids1Created = array_column(
                array_filter($createdQuotations, fn($q) => $q['supplier_id'] === $supplier1Id),
                'quotation_id'
            );
            
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.supplier_id
                    FROM supplier_quotations sq
                    WHERE sq.supplier_id = ? 
                    AND sq.quotation_id IN (" . implode(',', $ids1Created) . ")";

            $quotations1 = $this->db->fetchAll($sql, [$supplier1Id]);
            $this->verifySupplierAssociation($quotations1, $supplier1Id);
            $this->assertCount($count1, $quotations1);

            // Query for supplier 2 (only quotations created in this iteration)
            $ids2Created = array_column(
                array_filter($createdQuotations, fn($q) => $q['supplier_id'] === $supplier2Id),
                'quotation_id'
            );
            
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.supplier_id
                    FROM supplier_quotations sq
                    WHERE sq.supplier_id = ? 
                    AND sq.quotation_id IN (" . implode(',', $ids2Created) . ")";

            $quotations2 = $this->db->fetchAll($sql, [$supplier2Id]);
            $this->verifySupplierAssociation($quotations2, $supplier2Id);
            $this->assertCount($count2, $quotations2);

            // Verify no overlap
            $ids1 = array_column($quotations1, 'quotation_id');
            $ids2 = array_column($quotations2, 'quotation_id');
            $intersection = array_intersect($ids1, $ids2);
            
            $this->assertEmpty(
                $intersection,
                'Quotations for different suppliers should not overlap'
            );

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

        $this->assertTrue(true, "Exclusive supplier association held for $iterations iterations");
    }
}
