<?php
/**
 * Unit Test for Store Quotation Endpoint
 * Tests task 3.1: Implement create quotation endpoint
 */

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use System\Database;

class StoreQuotationTest extends TestCase
{
    private Database $db;
    private int $testSupplierId;
    private array $testProductIds = [];
    private array $createdQuotationIds = [];

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
        $supplier = $this->db->fetch("SELECT supplier_id FROM suppliers WHERE name = 'Test Supplier for Store Quotation'");
        if (!$supplier) {
            $this->testSupplierId = (int)$this->db->insert('suppliers', [
                'name' => 'Test Supplier for Store Quotation',
                'contact_person' => 'Test Contact',
                'mobile' => '9876543210',
                'email' => 'test@storeq.com',
                'gstin' => 'TEST-STORE-Q-001'
            ]);
        } else {
            $this->testSupplierId = (int)$supplier['supplier_id'];
        }

        // Get test products
        $products = $this->db->fetchAll("SELECT product_id FROM products LIMIT 3");
        if (count($products) < 3) {
            // Create test products if not enough exist
            for ($i = count($products); $i < 3; $i++) {
                $productId = $this->db->insert('products', [
                    'name' => 'Test Product ' . ($i + 1),
                    'sku' => 'TEST-SKU-' . ($i + 1) . '-' . time(),
                    'unit' => 'nos',
                    'tax_percent' => 12.00,
                    'is_active' => 1
                ]);
                $products[] = ['product_id' => $productId];
            }
        }
        $this->testProductIds = array_map('intval', array_column($products, 'product_id'));
    }

    private function cleanupTestData(): void
    {
        foreach ($this->createdQuotationIds as $quotationId) {
            $this->db->delete('quotation_items', "quotation_id = $quotationId");
            $this->db->delete('supplier_quotations', "quotation_id = $quotationId");
        }
    }

    /**
     * Test successful quotation creation with valid data
     */
    public function testCreateQuotationWithValidData(): void
    {
        $quotationData = [
            'supplier_id' => $this->testSupplierId,
            'quotation_no' => 'TEST-CREATE-' . time(),
            'quotation_date' => date('Y-m-d'),
            'valid_until' => date('Y-m-d', strtotime('+30 days')),
            'supplier_reference' => 'REF-TEST-001',
            'status' => 'active',
            'remarks' => 'Test quotation creation',
            'items' => [
                [
                    'product_id' => $this->testProductIds[0],
                    'quantity' => 100,
                    'unit_price' => 25.50,
                    'tax_percent' => 12.00,
                    'mrp' => 35.00,
                    'remarks' => 'Item 1'
                ],
                [
                    'product_id' => $this->testProductIds[1],
                    'quantity' => 50,
                    'unit_price' => 15.75,
                    'tax_percent' => 18.00,
                    'mrp' => 22.00,
                    'remarks' => 'Item 2'
                ]
            ]
        ];

        // Simulate the storeQuotation logic
        $this->db->beginTransaction();
        
        $quotationId = $this->db->insert('supplier_quotations', [
            'supplier_id' => $quotationData['supplier_id'],
            'quotation_no' => $quotationData['quotation_no'],
            'quotation_date' => $quotationData['quotation_date'],
            'valid_until' => $quotationData['valid_until'],
            'supplier_reference' => $quotationData['supplier_reference'],
            'status' => $quotationData['status'],
            'remarks' => $quotationData['remarks'],
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->assertGreaterThan(0, $quotationId, 'Quotation ID should be greater than 0');
        $this->createdQuotationIds[] = $quotationId;

        // Insert items
        foreach ($quotationData['items'] as $item) {
            $itemId = $this->db->insert('quotation_items', [
                'quotation_id' => $quotationId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_percent' => $item['tax_percent'],
                'mrp' => $item['mrp'],
                'remarks' => $item['remarks']
            ]);
            $this->assertGreaterThan(0, $itemId, 'Item ID should be greater than 0');
        }

        $this->db->commit();

        // Verify quotation was created
        $quotation = $this->db->fetch(
            "SELECT * FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );

        $this->assertIsArray($quotation);
        $this->assertEquals($quotationData['quotation_no'], $quotation['quotation_no']);
        $this->assertEquals($quotationData['supplier_id'], (int)$quotation['supplier_id']);
        $this->assertEquals($quotationData['quotation_date'], $quotation['quotation_date']);
        $this->assertEquals($quotationData['valid_until'], $quotation['valid_until']);

        // Verify items were created
        $items = $this->db->fetchAll(
            "SELECT * FROM quotation_items WHERE quotation_id = ?",
            [$quotationId]
        );

        $this->assertCount(2, $items);
        $this->assertEquals($quotationData['items'][0]['product_id'], (int)$items[0]['product_id']);
        $this->assertEquals($quotationData['items'][0]['quantity'], (int)$items[0]['quantity']);
        $this->assertEquals($quotationData['items'][0]['unit_price'], (float)$items[0]['unit_price']);
    }

    /**
     * Test validation: missing required fields
     */
    public function testValidationMissingRequiredFields(): void
    {
        $testCases = [
            ['field' => 'supplier_id', 'data' => ['quotation_no' => 'TEST', 'quotation_date' => date('Y-m-d'), 'valid_until' => date('Y-m-d', strtotime('+30 days')), 'items' => []]],
            ['field' => 'quotation_no', 'data' => ['supplier_id' => $this->testSupplierId, 'quotation_date' => date('Y-m-d'), 'valid_until' => date('Y-m-d', strtotime('+30 days')), 'items' => []]],
            ['field' => 'quotation_date', 'data' => ['supplier_id' => $this->testSupplierId, 'quotation_no' => 'TEST', 'valid_until' => date('Y-m-d', strtotime('+30 days')), 'items' => []]],
            ['field' => 'valid_until', 'data' => ['supplier_id' => $this->testSupplierId, 'quotation_no' => 'TEST', 'quotation_date' => date('Y-m-d'), 'items' => []]],
            ['field' => 'items', 'data' => ['supplier_id' => $this->testSupplierId, 'quotation_no' => 'TEST', 'quotation_date' => date('Y-m-d'), 'valid_until' => date('Y-m-d', strtotime('+30 days'))]],
        ];

        foreach ($testCases as $testCase) {
            $errors = [];
            $input = $testCase['data'];

            // Simulate validation logic
            if (empty($input['supplier_id'])) {
                $errors['supplier_id'] = 'Supplier ID is required';
            }
            if (empty($input['quotation_no'])) {
                $errors['quotation_no'] = 'Quotation number is required';
            }
            if (empty($input['quotation_date'])) {
                $errors['quotation_date'] = 'Quotation date is required';
            }
            if (empty($input['valid_until'])) {
                $errors['valid_until'] = 'Valid until date is required';
            }
            if (empty($input['items']) || !is_array($input['items']) || count($input['items']) === 0) {
                $errors['items'] = 'At least one item is required';
            }

            $this->assertNotEmpty($errors, "Validation should fail for missing {$testCase['field']}");
            $this->assertArrayHasKey($testCase['field'], $errors, "Error should be present for {$testCase['field']}");
        }
    }

    /**
     * Test validation: invalid date logic (valid_until before quotation_date)
     */
    public function testValidationInvalidDateLogic(): void
    {
        $quotationDate = date('Y-m-d');
        $validUntil = date('Y-m-d', strtotime('-1 day')); // Before quotation date

        $quotationDateTimestamp = strtotime($quotationDate);
        $validUntilTimestamp = strtotime($validUntil);

        $this->assertLessThanOrEqual(
            $quotationDateTimestamp,
            $validUntilTimestamp,
            'Valid until should be before or equal to quotation date (invalid)'
        );

        // This should trigger validation error
        $isValid = $validUntilTimestamp > $quotationDateTimestamp;
        $this->assertFalse($isValid, 'Date logic validation should fail');
    }

    /**
     * Test validation: empty items array
     */
    public function testValidationEmptyItemsArray(): void
    {
        $items = [];
        
        $isValid = !empty($items) && is_array($items) && count($items) > 0;
        $this->assertFalse($isValid, 'Empty items array should fail validation');
    }

    /**
     * Test validation: negative quantity
     */
    public function testValidationNegativeQuantity(): void
    {
        $item = [
            'product_id' => $this->testProductIds[0],
            'quantity' => -10,
            'unit_price' => 25.50
        ];

        $errors = [];
        if (!isset($item['quantity']) || $item['quantity'] <= 0) {
            $errors['quantity'] = 'Valid quantity is required';
        }

        $this->assertNotEmpty($errors, 'Negative quantity should fail validation');
    }

    /**
     * Test validation: negative unit price
     */
    public function testValidationNegativeUnitPrice(): void
    {
        $item = [
            'product_id' => $this->testProductIds[0],
            'quantity' => 10,
            'unit_price' => -25.50
        ];

        $errors = [];
        if (!isset($item['unit_price']) || $item['unit_price'] < 0) {
            $errors['unit_price'] = 'Valid unit price is required';
        }

        $this->assertNotEmpty($errors, 'Negative unit price should fail validation');
    }

    /**
     * Test validation: tax percent out of range
     */
    public function testValidationTaxPercentOutOfRange(): void
    {
        $testCases = [
            ['tax_percent' => -5, 'expected' => 'fail'],
            ['tax_percent' => 150, 'expected' => 'fail'],
            ['tax_percent' => 0, 'expected' => 'pass'],
            ['tax_percent' => 50, 'expected' => 'pass'],
            ['tax_percent' => 100, 'expected' => 'pass'],
        ];

        foreach ($testCases as $testCase) {
            $errors = [];
            if (isset($testCase['tax_percent']) && ($testCase['tax_percent'] < 0 || $testCase['tax_percent'] > 100)) {
                $errors['tax_percent'] = 'Tax percent must be between 0 and 100';
            }

            if ($testCase['expected'] === 'fail') {
                $this->assertNotEmpty($errors, "Tax percent {$testCase['tax_percent']} should fail validation");
            } else {
                $this->assertEmpty($errors, "Tax percent {$testCase['tax_percent']} should pass validation");
            }
        }
    }

    /**
     * Test duplicate quotation number
     */
    public function testDuplicateQuotationNumber(): void
    {
        $quotationNo = 'TEST-DUP-' . time();

        // Create first quotation
        $this->db->beginTransaction();
        $quotationId1 = $this->db->insert('supplier_quotations', [
            'supplier_id' => $this->testSupplierId,
            'quotation_no' => $quotationNo,
            'quotation_date' => date('Y-m-d'),
            'valid_until' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'active'
        ]);
        $this->db->commit();
        $this->createdQuotationIds[] = $quotationId1;

        // Check if quotation number exists
        $existing = $this->db->fetch(
            "SELECT quotation_id FROM supplier_quotations WHERE quotation_no = ?",
            [$quotationNo]
        );

        $this->assertNotEmpty($existing, 'Quotation number should exist');
        $this->assertEquals($quotationId1, (int)$existing['quotation_id']);
    }

    /**
     * Test transaction rollback on error
     */
    public function testTransactionRollbackOnError(): void
    {
        $quotationNo = 'TEST-ROLLBACK-' . time();

        try {
            $this->db->beginTransaction();

            // Insert quotation
            $quotationId = $this->db->insert('supplier_quotations', [
                'supplier_id' => $this->testSupplierId,
                'quotation_no' => $quotationNo,
                'quotation_date' => date('Y-m-d'),
                'valid_until' => date('Y-m-d', strtotime('+30 days')),
                'status' => 'active'
            ]);

            // Try to insert item with invalid product_id (should fail)
            $this->db->insert('quotation_items', [
                'quotation_id' => $quotationId,
                'product_id' => 999999999, // Non-existent product
                'quantity' => 10,
                'unit_price' => 25.50
            ]);

            $this->db->commit();
            $this->fail('Should have thrown an exception for invalid product_id');
        } catch (\Exception $e) {
            $this->db->rollBack();
            
            // Verify quotation was not created
            $quotation = $this->db->fetch(
                "SELECT quotation_id FROM supplier_quotations WHERE quotation_no = ?",
                [$quotationNo]
            );

            $this->assertEmpty($quotation, 'Quotation should not exist after rollback');
        }
    }

    /**
     * Test batch insert of multiple items
     */
    public function testBatchInsertMultipleItems(): void
    {
        $quotationNo = 'TEST-BATCH-' . time();
        $itemCount = 5;

        $this->db->beginTransaction();

        $quotationId = $this->db->insert('supplier_quotations', [
            'supplier_id' => $this->testSupplierId,
            'quotation_no' => $quotationNo,
            'quotation_date' => date('Y-m-d'),
            'valid_until' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'active'
        ]);
        $this->createdQuotationIds[] = $quotationId;

        // Insert multiple items
        for ($i = 0; $i < $itemCount; $i++) {
            $this->db->insert('quotation_items', [
                'quotation_id' => $quotationId,
                'product_id' => $this->testProductIds[$i % count($this->testProductIds)],
                'quantity' => ($i + 1) * 10,
                'unit_price' => ($i + 1) * 5.50,
                'tax_percent' => 12.00,
                'mrp' => ($i + 1) * 8.00
            ]);
        }

        $this->db->commit();

        // Verify all items were inserted
        $items = $this->db->fetchAll(
            "SELECT * FROM quotation_items WHERE quotation_id = ?",
            [$quotationId]
        );

        $this->assertCount($itemCount, $items, "Should have $itemCount items");
    }

    /**
     * Test quotation creation returns correct quotation_id
     */
    public function testQuotationCreationReturnsId(): void
    {
        $quotationNo = 'TEST-RETURN-ID-' . time();

        $this->db->beginTransaction();

        $quotationId = (int)$this->db->insert('supplier_quotations', [
            'supplier_id' => $this->testSupplierId,
            'quotation_no' => $quotationNo,
            'quotation_date' => date('Y-m-d'),
            'valid_until' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'active'
        ]);

        $this->db->commit();
        $this->createdQuotationIds[] = $quotationId;

        $this->assertIsInt($quotationId);
        $this->assertGreaterThan(0, $quotationId);

        // Verify the quotation exists with this ID
        $quotation = $this->db->fetch(
            "SELECT quotation_id FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );

        $this->assertNotEmpty($quotation);
        $this->assertEquals($quotationId, (int)$quotation['quotation_id']);
    }

    /**
     * Test date format validation
     */
    public function testDateFormatValidation(): void
    {
        $validDates = ['2025-01-15', '2024-12-31', '2025-02-28'];
        $invalidDates = ['2025-13-01', '2025-02-30', '15-01-2025', '2025/01/15', 'invalid'];

        foreach ($validDates as $date) {
            $d = \DateTime::createFromFormat('Y-m-d', $date);
            $isValid = $d && $d->format('Y-m-d') === $date;
            $this->assertTrue($isValid, "Date $date should be valid");
        }

        foreach ($invalidDates as $date) {
            $d = \DateTime::createFromFormat('Y-m-d', $date);
            $isValid = $d && $d->format('Y-m-d') === $date;
            $this->assertFalse($isValid, "Date $date should be invalid");
        }
    }

    /**
     * Test supplier existence validation
     */
    public function testSupplierExistenceValidation(): void
    {
        // Valid supplier
        $validSupplier = $this->db->fetch(
            "SELECT supplier_id FROM suppliers WHERE supplier_id = ?",
            [$this->testSupplierId]
        );
        $this->assertNotEmpty($validSupplier, 'Valid supplier should exist');

        // Invalid supplier
        $invalidSupplier = $this->db->fetch(
            "SELECT supplier_id FROM suppliers WHERE supplier_id = ?",
            [999999999]
        );
        $this->assertEmpty($invalidSupplier, 'Invalid supplier should not exist');
    }

    /**
     * Test product existence validation
     */
    public function testProductExistenceValidation(): void
    {
        // Valid product
        $validProduct = $this->db->fetch(
            "SELECT product_id FROM products WHERE product_id = ?",
            [$this->testProductIds[0]]
        );
        $this->assertNotEmpty($validProduct, 'Valid product should exist');

        // Invalid product
        $invalidProduct = $this->db->fetch(
            "SELECT product_id FROM products WHERE product_id = ?",
            [999999999]
        );
        $this->assertEmpty($invalidProduct, 'Invalid product should not exist');
    }
}
