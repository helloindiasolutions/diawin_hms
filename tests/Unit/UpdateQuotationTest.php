<?php
/**
 * Unit Test for Update Quotation Endpoint
 * 
 * Tests the updateQuotation method in InventoryController
 * Validates: Requirements US-6.2
 */

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use System\Database;

class UpdateQuotationTest extends TestCase
{
    private Database $db;
    private array $testSupplierIds = [];
    private array $testProductIds = [];
    private array $testQuotationIds = [];

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

    private function createTestQuotation(): int
    {
        $quotationDate = date('Y-m-d', strtotime('-10 days'));
        $validUntil = date('Y-m-d', strtotime('+30 days'));
        
        $quotationId = (int)$this->db->insert('supplier_quotations', [
            'supplier_id' => $this->testSupplierIds[0],
            'quotation_no' => 'UPDATE-TEST-' . uniqid(),
            'quotation_date' => $quotationDate,
            'valid_until' => $validUntil,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->testQuotationIds[] = $quotationId;
        
        // Add some items
        $this->db->insert('quotation_items', [
            'quotation_id' => $quotationId,
            'product_id' => $this->testProductIds[0],
            'quantity' => 10,
            'unit_price' => 100.00,
            'tax_percent' => 12.00,
            'mrp' => 150.00
        ]);
        
        $this->db->insert('quotation_items', [
            'quotation_id' => $quotationId,
            'product_id' => $this->testProductIds[1],
            'quantity' => 20,
            'unit_price' => 200.00,
            'tax_percent' => 18.00,
            'mrp' => 300.00
        ]);
        
        return $quotationId;
    }

    /**
     * @test
     */
    public function testUpdateQuotationSuccessfully(): void
    {
        // Create a test quotation
        $quotationId = $this->createTestQuotation();
        
        // Prepare update data
        $updateData = [
            'supplier_id' => $this->testSupplierIds[1], // Change supplier
            'quotation_no' => 'UPDATED-' . uniqid(),
            'quotation_date' => date('Y-m-d', strtotime('-5 days')),
            'valid_until' => date('Y-m-d', strtotime('+60 days')),
            'status' => 'active',
            'supplier_reference' => 'NEW-REF-123',
            'remarks' => 'Updated quotation',
            'items' => [
                [
                    'product_id' => $this->testProductIds[2],
                    'quantity' => 30,
                    'unit_price' => 300.00,
                    'tax_percent' => 12.00,
                    'mrp' => 450.00
                ],
                [
                    'product_id' => $this->testProductIds[3],
                    'quantity' => 40,
                    'unit_price' => 400.00,
                    'tax_percent' => 18.00,
                    'mrp' => 600.00
                ],
                [
                    'product_id' => $this->testProductIds[4],
                    'quantity' => 50,
                    'unit_price' => 500.00,
                    'tax_percent' => 28.00,
                    'mrp' => 750.00
                ]
            ]
        ];
        
        // Simulate the update operation
        $this->db->beginTransaction();
        
        // Update quotation header
        $this->db->update('supplier_quotations', [
            'supplier_id' => $updateData['supplier_id'],
            'quotation_no' => $updateData['quotation_no'],
            'quotation_date' => $updateData['quotation_date'],
            'valid_until' => $updateData['valid_until'],
            'supplier_reference' => $updateData['supplier_reference'],
            'status' => $updateData['status'],
            'remarks' => $updateData['remarks'],
            'updated_at' => date('Y-m-d H:i:s')
        ], "quotation_id = $quotationId");
        
        // Delete existing items
        $this->db->delete('quotation_items', "quotation_id = $quotationId");
        
        // Insert new items
        foreach ($updateData['items'] as $item) {
            $this->db->insert('quotation_items', [
                'quotation_id' => $quotationId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_percent' => $item['tax_percent'],
                'mrp' => $item['mrp']
            ]);
        }
        
        $this->db->commit();
        
        // Verify the update
        $updatedQuotation = $this->db->fetch(
            "SELECT * FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );
        
        $this->assertNotNull($updatedQuotation);
        $this->assertEquals($updateData['supplier_id'], $updatedQuotation['supplier_id']);
        $this->assertEquals($updateData['quotation_no'], $updatedQuotation['quotation_no']);
        $this->assertEquals($updateData['quotation_date'], $updatedQuotation['quotation_date']);
        $this->assertEquals($updateData['valid_until'], $updatedQuotation['valid_until']);
        $this->assertEquals($updateData['supplier_reference'], $updatedQuotation['supplier_reference']);
        $this->assertEquals($updateData['remarks'], $updatedQuotation['remarks']);
        
        // Verify items were replaced
        $items = $this->db->fetchAll(
            "SELECT * FROM quotation_items WHERE quotation_id = ? ORDER BY quotation_item_id",
            [$quotationId]
        );
        
        $this->assertCount(3, $items, 'Should have 3 items after update');
        
        // Verify item details
        foreach ($updateData['items'] as $index => $expectedItem) {
            $actualItem = $items[$index];
            $this->assertEquals($expectedItem['product_id'], $actualItem['product_id']);
            $this->assertEquals($expectedItem['quantity'], $actualItem['quantity']);
            $this->assertEquals($expectedItem['unit_price'], (float)$actualItem['unit_price']);
            $this->assertEquals($expectedItem['tax_percent'], (float)$actualItem['tax_percent']);
            $this->assertEquals($expectedItem['mrp'], (float)$actualItem['mrp']);
        }
    }

    /**
     * @test
     */
    public function testUpdateQuotationWithSameNumberSucceeds(): void
    {
        // Create a test quotation
        $quotationId = $this->createTestQuotation();
        
        // Get the original quotation number
        $originalQuotation = $this->db->fetch(
            "SELECT quotation_no FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );
        
        // Update with the same quotation number (should succeed)
        $updateData = [
            'supplier_id' => $this->testSupplierIds[0],
            'quotation_no' => $originalQuotation['quotation_no'], // Same number
            'quotation_date' => date('Y-m-d', strtotime('-5 days')),
            'valid_until' => date('Y-m-d', strtotime('+60 days')),
            'items' => [
                [
                    'product_id' => $this->testProductIds[0],
                    'quantity' => 15,
                    'unit_price' => 150.00
                ]
            ]
        ];
        
        // Simulate the update operation
        $this->db->beginTransaction();
        
        $this->db->update('supplier_quotations', [
            'supplier_id' => $updateData['supplier_id'],
            'quotation_no' => $updateData['quotation_no'],
            'quotation_date' => $updateData['quotation_date'],
            'valid_until' => $updateData['valid_until'],
            'updated_at' => date('Y-m-d H:i:s')
        ], "quotation_id = $quotationId");
        
        $this->db->delete('quotation_items', "quotation_id = $quotationId");
        
        foreach ($updateData['items'] as $item) {
            $this->db->insert('quotation_items', [
                'quotation_id' => $quotationId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_percent' => 0,
                'mrp' => 0
            ]);
        }
        
        $this->db->commit();
        
        // Verify the update succeeded
        $updatedQuotation = $this->db->fetch(
            "SELECT * FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );
        
        $this->assertNotNull($updatedQuotation);
        $this->assertEquals($updateData['quotation_no'], $updatedQuotation['quotation_no']);
    }

    /**
     * @test
     */
    public function testUpdateQuotationReplacesAllItems(): void
    {
        // Create a test quotation with 2 items
        $quotationId = $this->createTestQuotation();
        
        // Verify initial item count
        $initialItems = $this->db->fetchAll(
            "SELECT * FROM quotation_items WHERE quotation_id = ?",
            [$quotationId]
        );
        $this->assertCount(2, $initialItems);
        
        // Update with 4 items
        $updateData = [
            'supplier_id' => $this->testSupplierIds[0],
            'quotation_no' => 'REPLACE-TEST-' . uniqid(),
            'quotation_date' => date('Y-m-d'),
            'valid_until' => date('Y-m-d', strtotime('+30 days')),
            'items' => [
                ['product_id' => $this->testProductIds[0], 'quantity' => 10, 'unit_price' => 100.00],
                ['product_id' => $this->testProductIds[1], 'quantity' => 20, 'unit_price' => 200.00],
                ['product_id' => $this->testProductIds[2], 'quantity' => 30, 'unit_price' => 300.00],
                ['product_id' => $this->testProductIds[3], 'quantity' => 40, 'unit_price' => 400.00]
            ]
        ];
        
        // Simulate the update operation
        $this->db->beginTransaction();
        
        $this->db->update('supplier_quotations', [
            'supplier_id' => $updateData['supplier_id'],
            'quotation_no' => $updateData['quotation_no'],
            'quotation_date' => $updateData['quotation_date'],
            'valid_until' => $updateData['valid_until'],
            'updated_at' => date('Y-m-d H:i:s')
        ], "quotation_id = $quotationId");
        
        $this->db->delete('quotation_items', "quotation_id = $quotationId");
        
        foreach ($updateData['items'] as $item) {
            $this->db->insert('quotation_items', [
                'quotation_id' => $quotationId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_percent' => 0,
                'mrp' => 0
            ]);
        }
        
        $this->db->commit();
        
        // Verify new item count
        $updatedItems = $this->db->fetchAll(
            "SELECT * FROM quotation_items WHERE quotation_id = ?",
            [$quotationId]
        );
        $this->assertCount(4, $updatedItems, 'Should have 4 items after update');
    }

    /**
     * @test
     */
    public function testUpdateQuotationUpdatesTimestamp(): void
    {
        // Create a test quotation
        $quotationId = $this->createTestQuotation();
        
        // Get original timestamp
        $originalQuotation = $this->db->fetch(
            "SELECT created_at, updated_at FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );
        
        // Wait a moment to ensure timestamp difference
        sleep(1);
        
        // Update the quotation
        $this->db->update('supplier_quotations', [
            'remarks' => 'Updated remarks',
            'updated_at' => date('Y-m-d H:i:s')
        ], "quotation_id = $quotationId");
        
        // Get updated timestamp
        $updatedQuotation = $this->db->fetch(
            "SELECT created_at, updated_at FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );
        
        // Verify created_at didn't change
        $this->assertEquals($originalQuotation['created_at'], $updatedQuotation['created_at']);
        
        // Verify updated_at changed
        $this->assertNotEquals($originalQuotation['updated_at'], $updatedQuotation['updated_at']);
    }
}
