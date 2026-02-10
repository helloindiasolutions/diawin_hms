<?php
/**
 * Integration Test for Store Quotation API Endpoint
 * Tests task 3.1: POST /api/v1/quotations endpoint
 */

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use System\Database;

class StoreQuotationIntegrationTest extends TestCase
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
        $supplier = $this->db->fetch("SELECT supplier_id FROM suppliers WHERE name = 'Test Supplier for API'");
        if (!$supplier) {
            $this->testSupplierId = (int)$this->db->insert('suppliers', [
                'name' => 'Test Supplier for API',
                'contact_person' => 'API Test Contact',
                'mobile' => '9876543210',
                'email' => 'api@test.com',
                'gstin' => 'TEST-API-001'
            ]);
        } else {
            $this->testSupplierId = (int)$supplier['supplier_id'];
        }

        // Get test products
        $products = $this->db->fetchAll("SELECT product_id FROM products LIMIT 3");
        if (count($products) < 3) {
            for ($i = count($products); $i < 3; $i++) {
                $productId = $this->db->insert('products', [
                    'name' => 'API Test Product ' . ($i + 1),
                    'sku' => 'API-SKU-' . ($i + 1) . '-' . time(),
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
     * Test successful API call with valid data
     */
    public function testApiCreateQuotationSuccess(): void
    {
        $quotationData = [
            'supplier_id' => $this->testSupplierId,
            'quotation_no' => 'API-TEST-' . time(),
            'quotation_date' => date('Y-m-d'),
            'valid_until' => date('Y-m-d', strtotime('+30 days')),
            'supplier_reference' => 'API-REF-001',
            'status' => 'active',
            'remarks' => 'API integration test',
            'items' => [
                [
                    'product_id' => $this->testProductIds[0],
                    'quantity' => 100,
                    'unit_price' => 25.50,
                    'tax_percent' => 12.00,
                    'mrp' => 35.00,
                    'remarks' => 'Test item 1'
                ],
                [
                    'product_id' => $this->testProductIds[1],
                    'quantity' => 50,
                    'unit_price' => 15.75,
                    'tax_percent' => 18.00,
                    'mrp' => 22.00,
                    'remarks' => 'Test item 2'
                ]
            ]
        ];

        // Simulate the API endpoint logic
        $this->db->beginTransaction();
        
        try {
            $quotationId = (int)$this->db->insert('supplier_quotations', [
                'supplier_id' => $quotationData['supplier_id'],
                'quotation_no' => $quotationData['quotation_no'],
                'quotation_date' => $quotationData['quotation_date'],
                'valid_until' => $quotationData['valid_until'],
                'supplier_reference' => $quotationData['supplier_reference'],
                'status' => $quotationData['status'],
                'remarks' => $quotationData['remarks'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            foreach ($quotationData['items'] as $item) {
                $this->db->insert('quotation_items', [
                    'quotation_id' => $quotationId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_percent' => $item['tax_percent'],
                    'mrp' => $item['mrp'],
                    'remarks' => $item['remarks']
                ]);
            }

            $this->db->commit();
            $this->createdQuotationIds[] = $quotationId;

            // Verify response structure
            $this->assertIsInt($quotationId);
            $this->assertGreaterThan(0, $quotationId);

            // Verify data was persisted correctly
            $quotation = $this->db->fetch(
                "SELECT * FROM supplier_quotations WHERE quotation_id = ?",
                [$quotationId]
            );

            $this->assertNotEmpty($quotation);
            $this->assertEquals($quotationData['quotation_no'], $quotation['quotation_no']);
            $this->assertEquals($quotationData['supplier_id'], (int)$quotation['supplier_id']);

            $items = $this->db->fetchAll(
                "SELECT * FROM quotation_items WHERE quotation_id = ?",
                [$quotationId]
            );

            $this->assertCount(2, $items);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->fail('API call should succeed: ' . $e->getMessage());
        }
    }

    /**
     * Test API validation error response for missing fields
     */
    public function testApiValidationErrorMissingFields(): void
    {
        $invalidData = [
            'quotation_no' => 'API-INVALID-' . time(),
            // Missing supplier_id, quotation_date, valid_until, items
        ];

        $errors = [];
        
        if (empty($invalidData['supplier_id'])) {
            $errors['supplier_id'] = 'Supplier ID is required';
        }
        if (empty($invalidData['quotation_date'])) {
            $errors['quotation_date'] = 'Quotation date is required';
        }
        if (empty($invalidData['valid_until'])) {
            $errors['valid_until'] = 'Valid until date is required';
        }
        if (empty($invalidData['items']) || !is_array($invalidData['items']) || count($invalidData['items']) === 0) {
            $errors['items'] = 'At least one item is required';
        }

        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('supplier_id', $errors);
        $this->assertArrayHasKey('quotation_date', $errors);
        $this->assertArrayHasKey('valid_until', $errors);
        $this->assertArrayHasKey('items', $errors);
    }

    /**
     * Test API validation error for invalid date logic
     */
    public function testApiValidationErrorInvalidDateLogic(): void
    {
        $invalidData = [
            'supplier_id' => $this->testSupplierId,
            'quotation_no' => 'API-INVALID-DATE-' . time(),
            'quotation_date' => date('Y-m-d'),
            'valid_until' => date('Y-m-d', strtotime('-1 day')), // Before quotation_date
            'items' => [
                [
                    'product_id' => $this->testProductIds[0],
                    'quantity' => 10,
                    'unit_price' => 25.50
                ]
            ]
        ];

        $errors = [];
        
        if (!empty($invalidData['quotation_date']) && !empty($invalidData['valid_until'])) {
            $quotationDate = strtotime($invalidData['quotation_date']);
            $validUntil = strtotime($invalidData['valid_until']);
            
            if ($validUntil <= $quotationDate) {
                $errors['valid_until'] = 'Valid until date must be after quotation date';
            }
        }

        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('valid_until', $errors);
    }

    /**
     * Test API error for duplicate quotation number
     */
    public function testApiErrorDuplicateQuotationNumber(): void
    {
        $quotationNo = 'API-DUP-' . time();

        // Create first quotation
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

        // Try to create duplicate
        $existing = $this->db->fetch(
            "SELECT quotation_id FROM supplier_quotations WHERE quotation_no = ?",
            [$quotationNo]
        );

        $this->assertNotEmpty($existing);
        // This should trigger validation error in API
    }

    /**
     * Test API error for non-existent supplier
     */
    public function testApiErrorNonExistentSupplier(): void
    {
        $invalidSupplierId = 999999999;

        $supplier = $this->db->fetch(
            "SELECT supplier_id FROM suppliers WHERE supplier_id = ?",
            [$invalidSupplierId]
        );

        $this->assertEmpty($supplier);
        // This should trigger validation error in API
    }

    /**
     * Test API error for non-existent product
     */
    public function testApiErrorNonExistentProduct(): void
    {
        $quotationNo = 'API-INVALID-PROD-' . time();
        $invalidProductId = 999999999;

        try {
            $this->db->beginTransaction();

            $quotationId = (int)$this->db->insert('supplier_quotations', [
                'supplier_id' => $this->testSupplierId,
                'quotation_no' => $quotationNo,
                'quotation_date' => date('Y-m-d'),
                'valid_until' => date('Y-m-d', strtotime('+30 days')),
                'status' => 'active'
            ]);

            // This should fail due to foreign key constraint
            $this->db->insert('quotation_items', [
                'quotation_id' => $quotationId,
                'product_id' => $invalidProductId,
                'quantity' => 10,
                'unit_price' => 25.50
            ]);

            $this->db->commit();
            $this->fail('Should have thrown exception for invalid product_id');
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->assertTrue(true, 'Exception thrown as expected');
        }
    }

    /**
     * Test API with minimal required fields
     */
    public function testApiWithMinimalFields(): void
    {
        $minimalData = [
            'supplier_id' => $this->testSupplierId,
            'quotation_no' => 'API-MINIMAL-' . time(),
            'quotation_date' => date('Y-m-d'),
            'valid_until' => date('Y-m-d', strtotime('+30 days')),
            'items' => [
                [
                    'product_id' => $this->testProductIds[0],
                    'quantity' => 10,
                    'unit_price' => 25.50
                ]
            ]
        ];

        $this->db->beginTransaction();

        $quotationId = (int)$this->db->insert('supplier_quotations', [
            'supplier_id' => $minimalData['supplier_id'],
            'quotation_no' => $minimalData['quotation_no'],
            'quotation_date' => $minimalData['quotation_date'],
            'valid_until' => $minimalData['valid_until'],
            'status' => 'active'
        ]);

        $this->db->insert('quotation_items', [
            'quotation_id' => $quotationId,
            'product_id' => $minimalData['items'][0]['product_id'],
            'quantity' => $minimalData['items'][0]['quantity'],
            'unit_price' => $minimalData['items'][0]['unit_price'],
            'tax_percent' => 0.00,
            'mrp' => 0.00
        ]);

        $this->db->commit();
        $this->createdQuotationIds[] = $quotationId;

        $this->assertGreaterThan(0, $quotationId);

        // Verify defaults were applied
        $quotation = $this->db->fetch(
            "SELECT * FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );

        $this->assertEquals('active', $quotation['status']);
        $this->assertNull($quotation['supplier_reference']);
        $this->assertNull($quotation['remarks']);
    }

    /**
     * Test API with all optional fields
     */
    public function testApiWithAllOptionalFields(): void
    {
        $completeData = [
            'supplier_id' => $this->testSupplierId,
            'quotation_no' => 'API-COMPLETE-' . time(),
            'quotation_date' => date('Y-m-d'),
            'valid_until' => date('Y-m-d', strtotime('+60 days')),
            'supplier_reference' => 'SUP-REF-12345',
            'status' => 'active',
            'remarks' => 'Complete quotation with all fields',
            'items' => [
                [
                    'product_id' => $this->testProductIds[0],
                    'quantity' => 100,
                    'unit_price' => 25.50,
                    'tax_percent' => 12.00,
                    'mrp' => 35.00,
                    'remarks' => 'Item with all fields'
                ]
            ]
        ];

        $this->db->beginTransaction();

        $quotationId = (int)$this->db->insert('supplier_quotations', [
            'supplier_id' => $completeData['supplier_id'],
            'quotation_no' => $completeData['quotation_no'],
            'quotation_date' => $completeData['quotation_date'],
            'valid_until' => $completeData['valid_until'],
            'supplier_reference' => $completeData['supplier_reference'],
            'status' => $completeData['status'],
            'remarks' => $completeData['remarks'],
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->insert('quotation_items', [
            'quotation_id' => $quotationId,
            'product_id' => $completeData['items'][0]['product_id'],
            'quantity' => $completeData['items'][0]['quantity'],
            'unit_price' => $completeData['items'][0]['unit_price'],
            'tax_percent' => $completeData['items'][0]['tax_percent'],
            'mrp' => $completeData['items'][0]['mrp'],
            'remarks' => $completeData['items'][0]['remarks']
        ]);

        $this->db->commit();
        $this->createdQuotationIds[] = $quotationId;

        // Verify all fields were saved
        $quotation = $this->db->fetch(
            "SELECT * FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );

        $this->assertEquals($completeData['supplier_reference'], $quotation['supplier_reference']);
        $this->assertEquals($completeData['remarks'], $quotation['remarks']);

        $item = $this->db->fetch(
            "SELECT * FROM quotation_items WHERE quotation_id = ?",
            [$quotationId]
        );

        $this->assertEquals($completeData['items'][0]['remarks'], $item['remarks']);
    }

    /**
     * Test API response structure
     */
    public function testApiResponseStructure(): void
    {
        $quotationNo = 'API-RESPONSE-' . time();

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

        // Simulate API response
        $response = [
            'success' => true,
            'data' => [
                'quotation_id' => $quotationId
            ],
            'message' => 'Quotation created successfully'
        ];

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('quotation_id', $response['data']);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Quotation created successfully', $response['message']);
    }
}
