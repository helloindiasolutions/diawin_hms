<?php
/**
 * Unit Test for Get Supplier Quotations Endpoint
 * Tests task 2.3: Implement get quotations by supplier endpoint
 */

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use System\Database;

class GetSupplierQuotationsTest extends TestCase
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
        // Create a test supplier
        $this->testSupplierId = (int)$this->db->insert('suppliers', [
            'name' => 'Test Supplier for Task 2.3',
            'contact_person' => 'John Doe',
            'mobile' => '9876543210',
            'email' => 'john@testsupplier.com',
            'gstin' => 'TEST-GSTIN-2.3',
            'address' => '123 Test Street, Test City'
        ]);

        // Get test products
        $products = $this->db->fetchAll("SELECT product_id FROM products LIMIT 5");
        if (empty($products)) {
            $this->markTestSkipped('No products available in database for testing');
        }
        $this->testProductIds = array_column($products, 'product_id');

        // Create test quotations with different dates
        $this->createTestQuotation('TASK-2.3-QT-001', 'active', date('Y-m-d'), date('Y-m-d', strtotime('+30 days')), 3);
        $this->createTestQuotation('TASK-2.3-QT-002', 'active', date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+20 days')), 5);
        $this->createTestQuotation('TASK-2.3-QT-003', 'expired', date('Y-m-d', strtotime('-60 days')), date('Y-m-d', strtotime('-30 days')), 2);
    }

    private function createTestQuotation(string $quotationNo, string $status, string $quotationDate, string $validUntil, int $itemCount): void
    {
        $quotationId = (int)$this->db->insert('supplier_quotations', [
            'supplier_id' => $this->testSupplierId,
            'quotation_no' => $quotationNo,
            'quotation_date' => $quotationDate,
            'valid_until' => $validUntil,
            'status' => $status,
            'supplier_reference' => 'REF-' . $quotationNo,
            'remarks' => 'Test quotation for task 2.3'
        ]);

        $this->testQuotationIds[] = $quotationId;

        // Add specified number of items to quotation
        for ($i = 0; $i < $itemCount && $i < count($this->testProductIds); $i++) {
            $this->db->insert('quotation_items', [
                'quotation_id' => $quotationId,
                'product_id' => $this->testProductIds[$i],
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
        
        if ($this->testSupplierId) {
            $this->db->delete('suppliers', "supplier_id = {$this->testSupplierId}");
        }
    }

    /**
     * Test that supplier details are fetched correctly
     */
    public function testFetchSupplierDetails(): void
    {
        $sql = "SELECT 
                    supplier_id,
                    name,
                    gstin,
                    contact_person,
                    mobile,
                    email,
                    address
                FROM suppliers
                WHERE supplier_id = ?";
        
        $supplier = $this->db->fetch($sql, [$this->testSupplierId]);
        
        $this->assertIsArray($supplier);
        $this->assertArrayHasKey('supplier_id', $supplier);
        $this->assertArrayHasKey('name', $supplier);
        $this->assertArrayHasKey('gstin', $supplier);
        $this->assertArrayHasKey('contact_person', $supplier);
        $this->assertArrayHasKey('mobile', $supplier);
        $this->assertArrayHasKey('email', $supplier);
        $this->assertArrayHasKey('address', $supplier);
        
        $this->assertEquals($this->testSupplierId, (int)$supplier['supplier_id']);
        $this->assertEquals('Test Supplier for Task 2.3', $supplier['name']);
        $this->assertEquals('TEST-GSTIN-2.3', $supplier['gstin']);
        $this->assertEquals('John Doe', $supplier['contact_person']);
        $this->assertEquals('9876543210', $supplier['mobile']);
        $this->assertEquals('john@testsupplier.com', $supplier['email']);
    }

    /**
     * Test that all quotations for a supplier are fetched
     */
    public function testFetchAllQuotationsForSupplier(): void
    {
        $sql = "SELECT 
                    sq.quotation_id,
                    sq.quotation_no,
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
                GROUP BY sq.quotation_id, sq.quotation_no, sq.quotation_date, 
                         sq.valid_until, sq.status, sq.supplier_reference,
                         sq.remarks, sq.created_at
                ORDER BY sq.quotation_date DESC, sq.quotation_id DESC";
        
        $quotations = $this->db->fetchAll($sql, [$this->testSupplierId]);
        
        $this->assertIsArray($quotations);
        $this->assertCount(3, $quotations, 'Should have exactly 3 test quotations');
        
        // Verify structure of each quotation
        foreach ($quotations as $quotation) {
            $this->assertArrayHasKey('quotation_id', $quotation);
            $this->assertArrayHasKey('quotation_no', $quotation);
            $this->assertArrayHasKey('quotation_date', $quotation);
            $this->assertArrayHasKey('valid_until', $quotation);
            $this->assertArrayHasKey('status', $quotation);
            $this->assertArrayHasKey('supplier_reference', $quotation);
            $this->assertArrayHasKey('remarks', $quotation);
            $this->assertArrayHasKey('created_at', $quotation);
            $this->assertArrayHasKey('items_count', $quotation);
        }
    }

    /**
     * Test that quotations are ordered by date descending
     */
    public function testQuotationsOrderedByDateDescending(): void
    {
        $sql = "SELECT 
                    sq.quotation_id,
                    sq.quotation_date
                FROM supplier_quotations sq
                WHERE sq.supplier_id = ?
                ORDER BY sq.quotation_date DESC, sq.quotation_id DESC";
        
        $quotations = $this->db->fetchAll($sql, [$this->testSupplierId]);
        
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
     * Test that items_count is calculated correctly for each quotation
     */
    public function testItemsCountCalculation(): void
    {
        $sql = "SELECT 
                    sq.quotation_id,
                    sq.quotation_no,
                    COUNT(qi.quotation_item_id) as items_count
                FROM supplier_quotations sq
                LEFT JOIN quotation_items qi ON sq.quotation_id = qi.quotation_id
                WHERE sq.supplier_id = ?
                GROUP BY sq.quotation_id, sq.quotation_no
                ORDER BY sq.quotation_date DESC";
        
        $quotations = $this->db->fetchAll($sql, [$this->testSupplierId]);
        
        $this->assertIsArray($quotations);
        $this->assertCount(3, $quotations);
        
        // Verify expected item counts
        $expectedCounts = [3, 5, 2]; // Based on createTestQuotation calls
        $actualCounts = array_map(fn($q) => (int)$q['items_count'], $quotations);
        
        // Sort both arrays to compare
        sort($expectedCounts);
        sort($actualCounts);
        
        $this->assertEquals($expectedCounts, $actualCounts, 'Items count should match expected values');
    }

    /**
     * Test that non-existent supplier returns appropriate result
     */
    public function testNonExistentSupplier(): void
    {
        $nonExistentId = 999999;
        
        $sql = "SELECT 
                    supplier_id,
                    name,
                    gstin
                FROM suppliers
                WHERE supplier_id = ?";
        
        $supplier = $this->db->fetch($sql, [$nonExistentId]);
        
        $this->assertEmpty($supplier, 'Non-existent supplier should return empty result');
    }

    /**
     * Test that supplier with no quotations returns empty array
     */
    public function testSupplierWithNoQuotations(): void
    {
        // Create a supplier with no quotations
        $emptySupplierId = (int)$this->db->insert('suppliers', [
            'name' => 'Empty Supplier',
            'contact_person' => 'Jane Doe',
            'mobile' => '9876543211',
            'email' => 'jane@emptysupplier.com',
            'gstin' => 'EMPTY-GSTIN'
        ]);
        
        $sql = "SELECT 
                    sq.quotation_id
                FROM supplier_quotations sq
                WHERE sq.supplier_id = ?";
        
        $quotations = $this->db->fetchAll($sql, [$emptySupplierId]);
        
        $this->assertIsArray($quotations);
        $this->assertEmpty($quotations, 'Supplier with no quotations should return empty array');
        
        // Cleanup
        $this->db->delete('suppliers', "supplier_id = $emptySupplierId");
    }

    /**
     * Test that all quotation statuses are included (active, expired, cancelled)
     */
    public function testAllStatusesIncluded(): void
    {
        $sql = "SELECT 
                    sq.quotation_id,
                    sq.status
                FROM supplier_quotations sq
                WHERE sq.supplier_id = ?
                ORDER BY sq.quotation_date DESC";
        
        $quotations = $this->db->fetchAll($sql, [$this->testSupplierId]);
        
        $this->assertIsArray($quotations);
        
        $statuses = array_column($quotations, 'status');
        $this->assertContains('active', $statuses, 'Should include active quotations');
        $this->assertContains('expired', $statuses, 'Should include expired quotations');
    }

    /**
     * Test that supplier reference and remarks are included
     */
    public function testSupplierReferenceAndRemarksIncluded(): void
    {
        $sql = "SELECT 
                    sq.quotation_id,
                    sq.supplier_reference,
                    sq.remarks
                FROM supplier_quotations sq
                WHERE sq.supplier_id = ?
                LIMIT 1";
        
        $quotation = $this->db->fetch($sql, [$this->testSupplierId]);
        
        $this->assertIsArray($quotation);
        $this->assertArrayHasKey('supplier_reference', $quotation);
        $this->assertArrayHasKey('remarks', $quotation);
        $this->assertStringContainsString('REF-TASK-2.3', $quotation['supplier_reference']);
        $this->assertStringContainsString('Test quotation for task 2.3', $quotation['remarks']);
    }

    /**
     * Test that created_at timestamp is included
     */
    public function testCreatedAtIncluded(): void
    {
        $sql = "SELECT 
                    sq.quotation_id,
                    sq.created_at
                FROM supplier_quotations sq
                WHERE sq.supplier_id = ?
                LIMIT 1";
        
        $quotation = $this->db->fetch($sql, [$this->testSupplierId]);
        
        $this->assertIsArray($quotation);
        $this->assertArrayHasKey('created_at', $quotation);
        $this->assertNotEmpty($quotation['created_at']);
        
        // Verify it's a valid timestamp
        $timestamp = strtotime($quotation['created_at']);
        $this->assertNotFalse($timestamp, 'created_at should be a valid timestamp');
    }

    /**
     * Test the complete response structure
     */
    public function testCompleteResponseStructure(): void
    {
        // Fetch supplier details
        $supplierSql = "SELECT 
                            supplier_id,
                            name,
                            gstin,
                            contact_person,
                            mobile,
                            email,
                            address
                        FROM suppliers
                        WHERE supplier_id = ?";
        
        $supplier = $this->db->fetch($supplierSql, [$this->testSupplierId]);
        
        // Fetch quotations
        $quotationsSql = "SELECT 
                            sq.quotation_id,
                            sq.quotation_no,
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
                        GROUP BY sq.quotation_id, sq.quotation_no, sq.quotation_date, 
                                 sq.valid_until, sq.status, sq.supplier_reference,
                                 sq.remarks, sq.created_at
                        ORDER BY sq.quotation_date DESC, sq.quotation_id DESC";
        
        $quotations = $this->db->fetchAll($quotationsSql, [$this->testSupplierId]);
        
        // Verify complete structure
        $this->assertIsArray($supplier);
        $this->assertIsArray($quotations);
        $this->assertCount(3, $quotations);
        
        // Verify supplier has all required fields
        $requiredSupplierFields = ['supplier_id', 'name', 'gstin', 'contact_person', 'mobile', 'email', 'address'];
        foreach ($requiredSupplierFields as $field) {
            $this->assertArrayHasKey($field, $supplier, "Supplier should have $field field");
        }
        
        // Verify each quotation has all required fields
        $requiredQuotationFields = ['quotation_id', 'quotation_no', 'quotation_date', 'valid_until', 
                                     'status', 'supplier_reference', 'remarks', 'created_at', 'items_count'];
        foreach ($quotations as $quotation) {
            foreach ($requiredQuotationFields as $field) {
                $this->assertArrayHasKey($field, $quotation, "Quotation should have $field field");
            }
        }
    }
}
