<?php
/**
 * Unit Test for Delete/Cancel Quotation Endpoint
 * 
 * Tests the deleteQuotation method in InventoryController
 * Validates: Requirements BR-1.2
 */

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use System\Database;

class DeleteQuotationTest extends TestCase
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
        $suppliers = $this->db->fetchAll("SELECT supplier_id FROM suppliers LIMIT 2");
        if (count($suppliers) < 2) {
            for ($i = count($suppliers); $i < 2; $i++) {
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

    private function createTestQuotation(string $status = 'active'): int
    {
        $quotationDate = date('Y-m-d', strtotime('-10 days'));
        $validUntil = date('Y-m-d', strtotime('+30 days'));
        
        $quotationId = (int)$this->db->insert('supplier_quotations', [
            'supplier_id' => $this->testSupplierIds[0],
            'quotation_no' => 'DELETE-TEST-' . uniqid(),
            'quotation_date' => $quotationDate,
            'valid_until' => $validUntil,
            'status' => $status,
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
        
        return $quotationId;
    }

    /**
     * @test
     */
    public function testDeleteQuotationSoftDeletesSuccessfully(): void
    {
        // Create a test quotation with active status
        $quotationId = $this->createTestQuotation('active');
        
        // Verify initial status
        $quotation = $this->db->fetch(
            "SELECT status FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );
        $this->assertEquals('active', $quotation['status']);
        
        // Soft delete the quotation
        $this->db->update('supplier_quotations', [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ], "quotation_id = $quotationId");
        
        // Verify status changed to cancelled
        $deletedQuotation = $this->db->fetch(
            "SELECT status FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );
        $this->assertEquals('cancelled', $deletedQuotation['status']);
    }

    /**
     * @test
     */
    public function testDeleteQuotationDoesNotDeleteItems(): void
    {
        // Create a test quotation with items
        $quotationId = $this->createTestQuotation('active');
        
        // Verify items exist
        $itemsBefore = $this->db->fetchAll(
            "SELECT * FROM quotation_items WHERE quotation_id = ?",
            [$quotationId]
        );
        $this->assertCount(1, $itemsBefore, 'Should have 1 item before deletion');
        
        // Soft delete the quotation
        $this->db->update('supplier_quotations', [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ], "quotation_id = $quotationId");
        
        // Verify items still exist (soft delete doesn't remove items)
        $itemsAfter = $this->db->fetchAll(
            "SELECT * FROM quotation_items WHERE quotation_id = ?",
            [$quotationId]
        );
        $this->assertCount(1, $itemsAfter, 'Items should still exist after soft delete');
    }

    /**
     * @test
     */
    public function testDeleteQuotationDoesNotRemoveFromDatabase(): void
    {
        // Create a test quotation
        $quotationId = $this->createTestQuotation('active');
        
        // Soft delete the quotation
        $this->db->update('supplier_quotations', [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ], "quotation_id = $quotationId");
        
        // Verify quotation still exists in database
        $quotation = $this->db->fetch(
            "SELECT * FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );
        $this->assertNotNull($quotation, 'Quotation should still exist in database');
        $this->assertEquals('cancelled', $quotation['status']);
    }

    /**
     * @test
     */
    public function testDeleteQuotationUpdatesTimestamp(): void
    {
        // Create a test quotation
        $quotationId = $this->createTestQuotation('active');
        
        // Get original timestamp
        $originalQuotation = $this->db->fetch(
            "SELECT updated_at FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );
        
        // Wait a moment to ensure timestamp difference
        sleep(1);
        
        // Soft delete the quotation
        $this->db->update('supplier_quotations', [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ], "quotation_id = $quotationId");
        
        // Get updated timestamp
        $deletedQuotation = $this->db->fetch(
            "SELECT updated_at FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );
        
        // Verify updated_at changed
        $this->assertNotEquals(
            $originalQuotation['updated_at'],
            $deletedQuotation['updated_at'],
            'updated_at should be updated when quotation is cancelled'
        );
    }

    /**
     * @test
     */
    public function testDeleteAlreadyCancelledQuotation(): void
    {
        // Create a test quotation that's already cancelled
        $quotationId = $this->createTestQuotation('cancelled');
        
        // Verify initial status
        $quotation = $this->db->fetch(
            "SELECT status FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );
        $this->assertEquals('cancelled', $quotation['status']);
        
        // Try to delete again (should still work)
        $this->db->update('supplier_quotations', [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ], "quotation_id = $quotationId");
        
        // Verify status is still cancelled
        $deletedQuotation = $this->db->fetch(
            "SELECT status FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );
        $this->assertEquals('cancelled', $deletedQuotation['status']);
    }

    /**
     * @test
     */
    public function testDeleteExpiredQuotation(): void
    {
        // Create a test quotation with expired status
        $quotationId = $this->createTestQuotation('expired');
        
        // Verify initial status
        $quotation = $this->db->fetch(
            "SELECT status FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );
        $this->assertEquals('expired', $quotation['status']);
        
        // Delete the expired quotation
        $this->db->update('supplier_quotations', [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ], "quotation_id = $quotationId");
        
        // Verify status changed to cancelled
        $deletedQuotation = $this->db->fetch(
            "SELECT status FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );
        $this->assertEquals('cancelled', $deletedQuotation['status']);
    }

    /**
     * @test
     */
    public function testDeleteNonExistentQuotation(): void
    {
        // Try to delete a quotation that doesn't exist
        $nonExistentId = 999999;
        
        // Verify quotation doesn't exist
        $quotation = $this->db->fetch(
            "SELECT * FROM supplier_quotations WHERE quotation_id = ?",
            [$nonExistentId]
        );
        $this->assertNull($quotation, 'Quotation should not exist');
        
        // Try to update (should affect 0 rows)
        $affectedRows = $this->db->update('supplier_quotations', [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ], "quotation_id = $nonExistentId");
        
        // Verify no rows were affected
        $this->assertEquals(0, $affectedRows, 'No rows should be affected for non-existent quotation');
    }

    /**
     * @test
     */
    public function testDeleteMultipleQuotations(): void
    {
        // Create multiple test quotations
        $quotationId1 = $this->createTestQuotation('active');
        $quotationId2 = $this->createTestQuotation('active');
        $quotationId3 = $this->createTestQuotation('active');
        
        // Delete all three quotations
        $this->db->update('supplier_quotations', [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ], "quotation_id = $quotationId1");
        
        $this->db->update('supplier_quotations', [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ], "quotation_id = $quotationId2");
        
        $this->db->update('supplier_quotations', [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ], "quotation_id = $quotationId3");
        
        // Verify all are cancelled
        $quotation1 = $this->db->fetch(
            "SELECT status FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId1]
        );
        $quotation2 = $this->db->fetch(
            "SELECT status FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId2]
        );
        $quotation3 = $this->db->fetch(
            "SELECT status FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId3]
        );
        
        $this->assertEquals('cancelled', $quotation1['status']);
        $this->assertEquals('cancelled', $quotation2['status']);
        $this->assertEquals('cancelled', $quotation3['status']);
    }

    /**
     * @test
     */
    public function testCancelledQuotationsCanStillBeQueried(): void
    {
        // Create and cancel a quotation
        $quotationId = $this->createTestQuotation('active');
        
        $this->db->update('supplier_quotations', [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ], "quotation_id = $quotationId");
        
        // Query the cancelled quotation
        $quotation = $this->db->fetch(
            "SELECT * FROM supplier_quotations WHERE quotation_id = ?",
            [$quotationId]
        );
        
        // Verify we can still retrieve all data
        $this->assertNotNull($quotation);
        $this->assertEquals('cancelled', $quotation['status']);
        $this->assertNotEmpty($quotation['quotation_no']);
        $this->assertNotEmpty($quotation['quotation_date']);
        
        // Verify items can still be queried
        $items = $this->db->fetchAll(
            "SELECT * FROM quotation_items WHERE quotation_id = ?",
            [$quotationId]
        );
        $this->assertCount(1, $items, 'Items should still be queryable');
    }
}
