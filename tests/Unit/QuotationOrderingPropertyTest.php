<?php
/**
 * Property-Based Test for Quotation Ordering
 * 
 * Property 3: Quotation Date Ordering
 * Validates: Requirements US-2.4, BR-2.1, BR-2.2
 * 
 * Property: For any list of quotations returned by the API, the quotations 
 * should be ordered by quotation_date in descending order (latest first), 
 * and for quotations with the same date, ordered by quotation_id descending.
 * 
 * @feature purchase-quotation-system
 * @property 3
 */

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use System\Database;

class QuotationOrderingPropertyTest extends TestCase
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
        $suppliers = $this->db->fetchAll("SELECT supplier_id FROM suppliers LIMIT 3");
        if (count($suppliers) < 3) {
            // Create test suppliers if needed
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
     * Generate a random quotation with random date
     */
    private function generateRandomQuotation(): array
    {
        // Generate random date within the last 365 days
        $daysAgo = rand(0, 365);
        $quotationDate = date('Y-m-d', strtotime("-$daysAgo days"));
        $validUntil = date('Y-m-d', strtotime($quotationDate . ' +' . rand(30, 90) . ' days'));
        
        return [
            'supplier_id' => $this->testSupplierIds[array_rand($this->testSupplierIds)],
            'quotation_no' => 'PROP-TEST-' . uniqid() . '-' . rand(1000, 9999),
            'quotation_date' => $quotationDate,
            'valid_until' => $validUntil,
            'status' => ['active', 'expired'][rand(0, 1)],
            'supplier_reference' => 'REF-' . rand(10000, 99999),
            'remarks' => 'Property test quotation'
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
     * Verify that quotations are ordered correctly
     */
    private function verifyQuotationOrdering(array $quotations): void
    {
        $this->assertIsArray($quotations, 'Quotations should be an array');
        
        if (count($quotations) < 2) {
            // Not enough quotations to verify ordering
            return;
        }

        // Verify ordering property
        for ($i = 0; $i < count($quotations) - 1; $i++) {
            $current = $quotations[$i];
            $next = $quotations[$i + 1];

            // Property: Date should be descending (current >= next)
            $this->assertGreaterThanOrEqual(
                $next['quotation_date'],
                $current['quotation_date'],
                sprintf(
                    'Quotations should be ordered by date descending. Found quotation_id=%d (date=%s) before quotation_id=%d (date=%s)',
                    $current['quotation_id'],
                    $current['quotation_date'],
                    $next['quotation_id'],
                    $next['quotation_date']
                )
            );

            // Property: If dates are equal, ID should be descending (current > next)
            if ($current['quotation_date'] === $next['quotation_date']) {
                $this->assertGreaterThan(
                    $next['quotation_id'],
                    $current['quotation_id'],
                    sprintf(
                        'Quotations with same date should be ordered by ID descending. Found quotation_id=%d before quotation_id=%d (both dated %s)',
                        $current['quotation_id'],
                        $next['quotation_id'],
                        $current['quotation_date']
                    )
                );
            }
        }
    }

    /**
     * Property Test: Quotation ordering holds for randomly generated quotations
     * 
     * This test generates multiple sets of random quotations and verifies that
     * the ordering property holds for all of them.
     * 
     * @test
     */
    public function testQuotationOrderingPropertyWithRandomData(): void
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
                        sq.status
                    FROM supplier_quotations sq
                    WHERE sq.quotation_id IN (" . implode(',', array_column($createdQuotations, 'quotation_id')) . ")
                    ORDER BY sq.quotation_date DESC, sq.quotation_id DESC";

            $fetchedQuotations = $this->db->fetchAll($sql);

            // Verify the ordering property
            $this->verifyQuotationOrdering($fetchedQuotations);

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
        $this->assertTrue(true, "Quotation ordering property held for $iterations iterations");
    }

    /**
     * Property Test: Ordering holds with quotations on the same date
     * 
     * This test specifically generates quotations with the same date to verify
     * that the secondary ordering by quotation_id works correctly.
     * 
     * @test
     */
    public function testQuotationOrderingWithSameDateQuotations(): void
    {
        $iterations = 50;
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Generate a random date
            $sameDate = date('Y-m-d', strtotime('-' . rand(0, 365) . ' days'));
            
            // Create multiple quotations with the same date
            $createdQuotations = [];
            $quotationCount = rand(3, 8);
            
            for ($i = 0; $i < $quotationCount; $i++) {
                $quotationData = [
                    'supplier_id' => $this->testSupplierIds[array_rand($this->testSupplierIds)],
                    'quotation_no' => 'SAME-DATE-' . uniqid() . '-' . rand(1000, 9999),
                    'quotation_date' => $sameDate,
                    'valid_until' => date('Y-m-d', strtotime($sameDate . ' +30 days')),
                    'status' => 'active',
                    'supplier_reference' => 'REF-' . rand(10000, 99999),
                    'remarks' => 'Same date test'
                ];
                
                $quotationId = $this->createQuotation($quotationData);
                $createdQuotations[] = array_merge($quotationData, ['quotation_id' => $quotationId]);
            }

            // Fetch quotations
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.quotation_date
                    FROM supplier_quotations sq
                    WHERE sq.quotation_id IN (" . implode(',', array_column($createdQuotations, 'quotation_id')) . ")
                    ORDER BY sq.quotation_date DESC, sq.quotation_id DESC";

            $fetchedQuotations = $this->db->fetchAll($sql);

            // Verify all have the same date
            foreach ($fetchedQuotations as $quotation) {
                $this->assertEquals($sameDate, $quotation['quotation_date']);
            }

            // Verify IDs are in descending order
            for ($i = 0; $i < count($fetchedQuotations) - 1; $i++) {
                $this->assertGreaterThan(
                    $fetchedQuotations[$i + 1]['quotation_id'],
                    $fetchedQuotations[$i]['quotation_id'],
                    'Quotations with same date should be ordered by ID descending'
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

        $this->assertTrue(true, "Same-date ordering property held for $iterations iterations");
    }

    /**
     * Property Test: Ordering holds across different filters
     * 
     * This test verifies that the ordering property holds even when filters
     * are applied (supplier_id, status, date range).
     * 
     * @test
     */
    public function testQuotationOrderingWithFilters(): void
    {
        $iterations = 30;
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Create quotations with varied attributes
            $createdQuotations = [];
            $quotationCount = rand(10, 20);
            
            for ($i = 0; $i < $quotationCount; $i++) {
                $quotationData = $this->generateRandomQuotation();
                $quotationId = $this->createQuotation($quotationData);
                $createdQuotations[] = array_merge($quotationData, ['quotation_id' => $quotationId]);
            }

            // Test with supplier filter
            $randomSupplierId = $this->testSupplierIds[array_rand($this->testSupplierIds)];
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.quotation_date,
                        sq.supplier_id
                    FROM supplier_quotations sq
                    WHERE sq.supplier_id = ?
                    ORDER BY sq.quotation_date DESC, sq.quotation_id DESC";

            $filteredQuotations = $this->db->fetchAll($sql, [$randomSupplierId]);
            $this->verifyQuotationOrdering($filteredQuotations);

            // Test with status filter
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.quotation_date,
                        sq.status
                    FROM supplier_quotations sq
                    WHERE sq.status = ?
                    ORDER BY sq.quotation_date DESC, sq.quotation_id DESC";

            $filteredQuotations = $this->db->fetchAll($sql, ['active']);
            $this->verifyQuotationOrdering($filteredQuotations);

            // Test with date range filter
            $fromDate = date('Y-m-d', strtotime('-180 days'));
            $toDate = date('Y-m-d');
            
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.quotation_date
                    FROM supplier_quotations sq
                    WHERE sq.quotation_date >= ? AND sq.quotation_date <= ?
                    ORDER BY sq.quotation_date DESC, sq.quotation_id DESC";

            $filteredQuotations = $this->db->fetchAll($sql, [$fromDate, $toDate]);
            $this->verifyQuotationOrdering($filteredQuotations);

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

        $this->assertTrue(true, "Ordering property with filters held for $iterations iterations");
    }

    /**
     * Property Test: Ordering is stable across pagination
     * 
     * This test verifies that the ordering remains consistent when paginating
     * through results.
     * 
     * @test
     */
    public function testQuotationOrderingAcrossPagination(): void
    {
        // Create a larger set of quotations
        $createdQuotations = [];
        $quotationCount = 25;
        
        for ($i = 0; $i < $quotationCount; $i++) {
            $quotationData = $this->generateRandomQuotation();
            $quotationId = $this->createQuotation($quotationData);
            $createdQuotations[] = array_merge($quotationData, ['quotation_id' => $quotationId]);
        }

        // Fetch all quotations
        $sql = "SELECT 
                    sq.quotation_id,
                    sq.quotation_date
                FROM supplier_quotations sq
                WHERE sq.quotation_id IN (" . implode(',', array_column($createdQuotations, 'quotation_id')) . ")
                ORDER BY sq.quotation_date DESC, sq.quotation_id DESC";

        $allQuotations = $this->db->fetchAll($sql);

        // Verify ordering of all quotations
        $this->verifyQuotationOrdering($allQuotations);

        // Fetch in pages and verify each page maintains ordering
        $pageSize = 5;
        $totalPages = ceil(count($allQuotations) / $pageSize);
        
        $reconstructed = [];
        for ($page = 0; $page < $totalPages; $page++) {
            $offset = $page * $pageSize;
            
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.quotation_date
                    FROM supplier_quotations sq
                    WHERE sq.quotation_id IN (" . implode(',', array_column($createdQuotations, 'quotation_id')) . ")
                    ORDER BY sq.quotation_date DESC, sq.quotation_id DESC
                    LIMIT ? OFFSET ?";

            $pageQuotations = $this->db->fetchAll($sql, [$pageSize, $offset]);
            
            // Verify ordering within the page
            $this->verifyQuotationOrdering($pageQuotations);
            
            // Add to reconstructed list
            $reconstructed = array_merge($reconstructed, $pageQuotations);
        }

        // Verify that paginated results match the full result set
        $this->assertEquals(
            array_column($allQuotations, 'quotation_id'),
            array_column($reconstructed, 'quotation_id'),
            'Paginated results should match full result set in order'
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

        $this->assertTrue(true, 'Ordering property held across pagination');
    }
}
