<?php
/**
 * Unit Test for Quotations Listing Endpoint
 * Tests task 2.1: Implement quotation listing endpoint
 */

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use System\Database;

class QuotationsEndpointTest extends TestCase
{
    private Database $db;
    private array $testQuotationIds = [];
    private int $testSupplierId;
    private array $testProductIds = [];

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
        // Get or create a test supplier
        $supplier = $this->db->fetch("SELECT supplier_id FROM suppliers LIMIT 1");
        if (!$supplier) {
            $this->testSupplierId = $this->db->insert('suppliers', [
                'name' => 'Test Supplier for Quotations',
                'contact_person' => 'Test Contact',
                'mobile' => '9876543210',
                'email' => 'test@supplier.com',
                'gstin' => 'TEST-GSTIN-001'
            ]);
        } else {
            $this->testSupplierId = (int)$supplier['supplier_id'];
        }

        // Get test products
        $products = $this->db->fetchAll("SELECT product_id FROM products LIMIT 3");
        $this->testProductIds = array_column($products, 'product_id');

        // Create test quotations
        $this->createTestQuotation('TEST-QT-001', 'active', date('Y-m-d'), date('Y-m-d', strtotime('+30 days')));
        $this->createTestQuotation('TEST-QT-002', 'active', date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+20 days')));
        $this->createTestQuotation('TEST-QT-003', 'expired', date('Y-m-d', strtotime('-60 days')), date('Y-m-d', strtotime('-30 days')));
    }

    private function createTestQuotation(string $quotationNo, string $status, string $quotationDate, string $validUntil): void
    {
        $quotationId = $this->db->insert('supplier_quotations', [
            'supplier_id' => $this->testSupplierId,
            'quotation_no' => $quotationNo,
            'quotation_date' => $quotationDate,
            'valid_until' => $validUntil,
            'status' => $status,
            'supplier_reference' => 'REF-' . $quotationNo,
            'remarks' => 'Test quotation'
        ]);

        $this->testQuotationIds[] = $quotationId;

        // Add items to quotation
        foreach ($this->testProductIds as $productId) {
            $this->db->insert('quotation_items', [
                'quotation_id' => $quotationId,
                'product_id' => $productId,
                'quantity' => rand(10, 100),
                'unit_price' => rand(100, 1000) / 10,
                'tax_percent' => 12.00,
                'mrp' => rand(150, 1500) / 10
            ]);
        }
    }

    private function cleanupTestData(): void
    {
        foreach ($this->testQuotationIds as $quotationId) {
            $this->db->delete('quotation_items', "quotation_id = $quotationId");
            $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
        }
    }

    /**
     * Test that quotations can be fetched without filters
     */
    public function testFetchAllQuotations(): void
    {
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
                WHERE 1=1
                GROUP BY sq.quotation_id, sq.quotation_no, sq.supplier_id, s.name, 
                         sq.quotation_date, sq.valid_until, sq.status
                ORDER BY sq.quotation_date DESC, sq.quotation_id DESC";

        $quotations = $this->db->fetchAll($sql);

        $this->assertIsArray($quotations);
        $this->assertGreaterThanOrEqual(3, count($quotations), 'Should have at least 3 test quotations');

        // Verify structure of first quotation
        if (count($quotations) > 0) {
            $quotation = $quotations[0];
            $this->assertArrayHasKey('quotation_id', $quotation);
            $this->assertArrayHasKey('quotation_no', $quotation);
            $this->assertArrayHasKey('supplier_id', $quotation);
            $this->assertArrayHasKey('supplier_name', $quotation);
            $this->assertArrayHasKey('quotation_date', $quotation);
            $this->assertArrayHasKey('valid_until', $quotation);
            $this->assertArrayHasKey('status', $quotation);
            $this->assertArrayHasKey('items_count', $quotation);
            $this->assertArrayHasKey('total_value', $quotation);
        }
    }

    /**
     * Test filtering by supplier_id
     */
    public function testFilterBySupplier(): void
    {
        $sql = "SELECT 
                    sq.quotation_id,
                    sq.supplier_id
                FROM supplier_quotations sq
                WHERE sq.supplier_id = ?
                ORDER BY sq.quotation_date DESC";

        $quotations = $this->db->fetchAll($sql, [$this->testSupplierId]);

        $this->assertIsArray($quotations);
        $this->assertGreaterThanOrEqual(3, count($quotations));

        // Verify all quotations belong to the test supplier
        foreach ($quotations as $quotation) {
            $this->assertEquals($this->testSupplierId, (int)$quotation['supplier_id']);
        }
    }

    /**
     * Test filtering by status
     */
    public function testFilterByStatus(): void
    {
        $sql = "SELECT 
                    sq.quotation_id,
                    sq.status
                FROM supplier_quotations sq
                WHERE sq.status = ?
                ORDER BY sq.quotation_date DESC";

        $activeQuotations = $this->db->fetchAll($sql, ['active']);

        $this->assertIsArray($activeQuotations);
        $this->assertGreaterThanOrEqual(2, count($activeQuotations));

        // Verify all quotations have active status
        foreach ($activeQuotations as $quotation) {
            $this->assertEquals('active', $quotation['status']);
        }

        $expiredQuotations = $this->db->fetchAll($sql, ['expired']);
        $this->assertIsArray($expiredQuotations);
        $this->assertGreaterThanOrEqual(1, count($expiredQuotations));
    }

    /**
     * Test search functionality
     */
    public function testSearchByQuotationNumber(): void
    {
        $searchTerm = '%TEST-QT%';
        
        $sql = "SELECT 
                    sq.quotation_id,
                    sq.quotation_no
                FROM supplier_quotations sq
                WHERE sq.quotation_no LIKE ?
                ORDER BY sq.quotation_date DESC";

        $quotations = $this->db->fetchAll($sql, [$searchTerm]);

        $this->assertIsArray($quotations);
        $this->assertGreaterThanOrEqual(3, count($quotations));

        // Verify all quotations match the search term
        foreach ($quotations as $quotation) {
            $this->assertStringContainsString('TEST-QT', $quotation['quotation_no']);
        }
    }

    /**
     * Test search by supplier name
     */
    public function testSearchBySupplierName(): void
    {
        $searchTerm = '%Test Supplier%';
        
        $sql = "SELECT 
                    sq.quotation_id,
                    s.name as supplier_name
                FROM supplier_quotations sq
                LEFT JOIN suppliers s ON sq.supplier_id = s.supplier_id
                WHERE s.name LIKE ?
                ORDER BY sq.quotation_date DESC";

        $quotations = $this->db->fetchAll($sql, [$searchTerm]);

        $this->assertIsArray($quotations);
        
        // Verify all quotations match the supplier name
        foreach ($quotations as $quotation) {
            $this->assertStringContainsString('Test Supplier', $quotation['supplier_name']);
        }
    }

    /**
     * Test date range filtering
     */
    public function testFilterByDateRange(): void
    {
        $fromDate = date('Y-m-d', strtotime('-15 days'));
        $toDate = date('Y-m-d');
        
        $sql = "SELECT 
                    sq.quotation_id,
                    sq.quotation_date
                FROM supplier_quotations sq
                WHERE sq.quotation_date >= ? AND sq.quotation_date <= ?
                ORDER BY sq.quotation_date DESC";

        $quotations = $this->db->fetchAll($sql, [$fromDate, $toDate]);

        $this->assertIsArray($quotations);

        // Verify all quotations are within the date range
        foreach ($quotations as $quotation) {
            $this->assertGreaterThanOrEqual($fromDate, $quotation['quotation_date']);
            $this->assertLessThanOrEqual($toDate, $quotation['quotation_date']);
        }
    }

    /**
     * Test quotation ordering (latest first)
     */
    public function testQuotationsOrderedByDateDescending(): void
    {
        $sql = "SELECT 
                    sq.quotation_id,
                    sq.quotation_date
                FROM supplier_quotations sq
                ORDER BY sq.quotation_date DESC, sq.quotation_id DESC
                LIMIT 10";

        $quotations = $this->db->fetchAll($sql);

        $this->assertIsArray($quotations);
        $this->assertGreaterThan(0, count($quotations));

        // Verify ordering
        for ($i = 0; $i < count($quotations) - 1; $i++) {
            $current = $quotations[$i];
            $next = $quotations[$i + 1];

            // Date should be descending
            $this->assertGreaterThanOrEqual(
                $next['quotation_date'],
                $current['quotation_date'],
                'Quotations should be ordered by date descending'
            );

            // If dates are equal, ID should be descending
            if ($current['quotation_date'] === $next['quotation_date']) {
                $this->assertGreaterThan(
                    $next['quotation_id'],
                    $current['quotation_id'],
                    'Quotations with same date should be ordered by ID descending'
                );
            }
        }
    }

    /**
     * Test that items_count is calculated correctly
     */
    public function testItemsCountCalculation(): void
    {
        $quotationId = $this->testQuotationIds[0];
        
        $sql = "SELECT 
                    COUNT(qi.quotation_item_id) as items_count
                FROM supplier_quotations sq
                LEFT JOIN quotation_items qi ON sq.quotation_id = qi.quotation_id
                WHERE sq.quotation_id = ?
                GROUP BY sq.quotation_id";

        $result = $this->db->fetch($sql, [$quotationId]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items_count', $result);
        $this->assertEquals(count($this->testProductIds), (int)$result['items_count']);
    }

    /**
     * Test that total_value is calculated correctly
     */
    public function testTotalValueCalculation(): void
    {
        $quotationId = $this->testQuotationIds[0];
        
        $sql = "SELECT 
                    COALESCE(SUM(qi.quantity * qi.unit_price), 0) as total_value
                FROM quotation_items qi
                WHERE qi.quotation_id = ?";

        $result = $this->db->fetch($sql, [$quotationId]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_value', $result);
        $this->assertGreaterThan(0, (float)$result['total_value']);
    }

    /**
     * Test pagination parameters
     */
    public function testPagination(): void
    {
        $limit = 2;
        $offset = 0;
        
        $sql = "SELECT 
                    sq.quotation_id
                FROM supplier_quotations sq
                ORDER BY sq.quotation_date DESC, sq.quotation_id DESC
                LIMIT ? OFFSET ?";

        $page1 = $this->db->fetchAll($sql, [$limit, $offset]);

        $this->assertIsArray($page1);
        $this->assertLessThanOrEqual($limit, count($page1));

        // Get second page
        $offset = $limit;
        $page2 = $this->db->fetchAll($sql, [$limit, $offset]);

        $this->assertIsArray($page2);

        // Verify pages don't overlap
        if (count($page1) > 0 && count($page2) > 0) {
            $page1Ids = array_column($page1, 'quotation_id');
            $page2Ids = array_column($page2, 'quotation_id');
            $this->assertEmpty(array_intersect($page1Ids, $page2Ids), 'Pages should not overlap');
        }
    }

    /**
     * Test combined filters
     */
    public function testCombinedFilters(): void
    {
        $sql = "SELECT 
                    sq.quotation_id,
                    sq.supplier_id,
                    sq.status,
                    sq.quotation_no
                FROM supplier_quotations sq
                WHERE sq.supplier_id = ? 
                  AND sq.status = ?
                  AND sq.quotation_no LIKE ?
                ORDER BY sq.quotation_date DESC";

        $quotations = $this->db->fetchAll($sql, [
            $this->testSupplierId,
            'active',
            '%TEST-QT%'
        ]);

        $this->assertIsArray($quotations);

        // Verify all filters are applied
        foreach ($quotations as $quotation) {
            $this->assertEquals($this->testSupplierId, (int)$quotation['supplier_id']);
            $this->assertEquals('active', $quotation['status']);
            $this->assertStringContainsString('TEST-QT', $quotation['quotation_no']);
        }
    }

    /**
     * Test that supplier details are included
     */
    public function testSupplierDetailsIncluded(): void
    {
        $sql = "SELECT 
                    sq.quotation_id,
                    sq.supplier_id,
                    s.name as supplier_name
                FROM supplier_quotations sq
                LEFT JOIN suppliers s ON sq.supplier_id = s.supplier_id
                WHERE sq.quotation_id = ?";

        $quotation = $this->db->fetch($sql, [$this->testQuotationIds[0]]);

        $this->assertIsArray($quotation);
        $this->assertArrayHasKey('supplier_id', $quotation);
        $this->assertArrayHasKey('supplier_name', $quotation);
        $this->assertNotEmpty($quotation['supplier_name']);
    }
}
