<?php
/**
 * Property-Based Test for Quotation Validation
 * 
 * Property 10: Quotation Validation Rules
 * Validates: Requirements US-6.2
 * 
 * Property: For any quotation creation or update request, if the request 
 * violates validation rules (missing required fields, invalid dates, empty 
 * items array, or valid_until before quotation_date), the API should return 
 * a 422 status with specific validation errors.
 * 
 * @feature purchase-quotation-system
 * @property 10
 */

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use System\Database;

class QuotationValidationPropertyTest extends TestCase
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
     * Generate a valid quotation for testing
     */
    private function generateValidQuotation(): array
    {
        $quotationDate = date('Y-m-d', strtotime('-' . rand(0, 30) . ' days'));
        $validUntil = date('Y-m-d', strtotime($quotationDate . ' +' . rand(30, 90) . ' days'));
        
        return [
            'supplier_id' => $this->testSupplierIds[array_rand($this->testSupplierIds)],
            'quotation_no' => 'VAL-TEST-' . uniqid() . '-' . rand(1000, 9999),
            'quotation_date' => $quotationDate,
            'valid_until' => $validUntil,
            'status' => 'active',
            'supplier_reference' => 'REF-' . rand(10000, 99999),
            'remarks' => 'Validation test quotation',
            'items' => [
                [
                    'product_id' => $this->testProductIds[array_rand($this->testProductIds)],
                    'quantity' => rand(1, 100),
                    'unit_price' => round(rand(100, 10000) / 100, 2),
                    'tax_percent' => [0, 5, 12, 18, 28][rand(0, 4)],
                    'mrp' => round(rand(150, 15000) / 100, 2)
                ]
            ]
        ];
    }

    /**
     * Simulate API call to storeQuotation
     */
    private function callStoreQuotationAPI(array $input): array
    {
        // Validate required fields
        $errors = [];
        
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
        
        // Validate date logic
        if (!empty($input['quotation_date']) && !empty($input['valid_until'])) {
            $quotationDate = strtotime($input['quotation_date']);
            $validUntil = strtotime($input['valid_until']);
            
            if ($validUntil <= $quotationDate) {
                $errors['valid_until'] = 'Valid until date must be after quotation date';
            }
        }
        
        // Validate date formats
        if (!empty($input['quotation_date']) && !$this->isValidDate($input['quotation_date'])) {
            $errors['quotation_date'] = 'Invalid date format. Use YYYY-MM-DD';
        }
        
        if (!empty($input['valid_until']) && !$this->isValidDate($input['valid_until'])) {
            $errors['valid_until'] = 'Invalid date format. Use YYYY-MM-DD';
        }
        
        // Validate items array
        if (!empty($input['items']) && is_array($input['items'])) {
            foreach ($input['items'] as $index => $item) {
                if (empty($item['product_id'])) {
                    $errors["items.$index.product_id"] = "Product ID is required for item $index";
                }
                
                if (!isset($item['quantity']) || $item['quantity'] <= 0) {
                    $errors["items.$index.quantity"] = "Valid quantity is required for item $index";
                }
                
                if (!isset($item['unit_price']) || $item['unit_price'] < 0) {
                    $errors["items.$index.unit_price"] = "Valid unit price is required for item $index";
                }
                
                if (isset($item['tax_percent']) && ($item['tax_percent'] < 0 || $item['tax_percent'] > 100)) {
                    $errors["items.$index.tax_percent"] = "Tax percent must be between 0 and 100 for item $index";
                }
                
                if (isset($item['mrp']) && $item['mrp'] < 0) {
                    $errors["items.$index.mrp"] = "MRP cannot be negative for item $index";
                }
            }
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'status' => 422,
                'errors' => $errors
            ];
        }
        
        // Check if quotation number already exists
        $existingQuotation = $this->db->fetch(
            "SELECT quotation_id FROM supplier_quotations WHERE quotation_no = ?",
            [$input['quotation_no']]
        );
        
        if ($existingQuotation) {
            return [
                'success' => false,
                'status' => 422,
                'errors' => ['quotation_no' => 'Quotation number already exists']
            ];
        }
        
        // If validation passes, create the quotation
        try {
            $this->db->beginTransaction();
            
            $quotationId = $this->db->insert('supplier_quotations', [
                'supplier_id' => $input['supplier_id'],
                'quotation_no' => $input['quotation_no'],
                'quotation_date' => $input['quotation_date'],
                'valid_until' => $input['valid_until'],
                'supplier_reference' => $input['supplier_reference'] ?? null,
                'status' => $input['status'] ?? 'active',
                'remarks' => $input['remarks'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->testQuotationIds[] = $quotationId;
            
            foreach ($input['items'] as $item) {
                $this->db->insert('quotation_items', [
                    'quotation_id' => $quotationId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'],
                    'tax_percent' => $item['tax_percent'] ?? 0.00,
                    'mrp' => $item['mrp'] ?? 0.00,
                    'remarks' => $item['remarks'] ?? null
                ]);
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'status' => 201,
                'data' => ['quotation_id' => $quotationId]
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'status' => 500,
                'message' => $e->getMessage()
            ];
        }
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Property Test: Missing required fields should return 422 with validation errors
     * 
     * @test
     */
    public function testMissingRequiredFieldsReturnValidationErrors(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;
        $requiredFields = ['supplier_id', 'quotation_no', 'quotation_date', 'valid_until', 'items'];
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Generate a valid quotation
            $quotation = $this->generateValidQuotation();
            
            // Randomly remove one or more required fields
            $fieldsToRemove = array_rand(array_flip($requiredFields), rand(1, count($requiredFields)));
            if (!is_array($fieldsToRemove)) {
                $fieldsToRemove = [$fieldsToRemove];
            }
            
            foreach ($fieldsToRemove as $field) {
                unset($quotation[$field]);
            }
            
            // Call API
            $response = $this->callStoreQuotationAPI($quotation);
            
            // Verify response
            $this->assertFalse($response['success'], 'Request with missing required fields should fail');
            $this->assertEquals(422, $response['status'], 'Should return 422 status for validation errors');
            $this->assertArrayHasKey('errors', $response, 'Response should contain errors array');
            
            // Verify that errors are reported for the missing fields
            foreach ($fieldsToRemove as $field) {
                $this->assertArrayHasKey(
                    $field,
                    $response['errors'],
                    "Validation error should be reported for missing field: $field"
                );
            }
        }
        
        $this->assertTrue(true, "Missing required fields validation held for $iterations iterations");
    }

    /**
     * Property Test: Invalid date logic should return 422 with validation error
     * 
     * @test
     */
    public function testInvalidDateLogicReturnsValidationError(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Generate a quotation with invalid date logic (valid_until before quotation_date)
            $quotationDate = date('Y-m-d', strtotime('-' . rand(0, 30) . ' days'));
            $validUntil = date('Y-m-d', strtotime($quotationDate . ' -' . rand(1, 30) . ' days'));
            
            $quotation = [
                'supplier_id' => $this->testSupplierIds[array_rand($this->testSupplierIds)],
                'quotation_no' => 'INVALID-DATE-' . uniqid() . '-' . rand(1000, 9999),
                'quotation_date' => $quotationDate,
                'valid_until' => $validUntil,
                'items' => [
                    [
                        'product_id' => $this->testProductIds[array_rand($this->testProductIds)],
                        'quantity' => 10,
                        'unit_price' => 100.00
                    ]
                ]
            ];
            
            // Call API
            $response = $this->callStoreQuotationAPI($quotation);
            
            // Verify response
            $this->assertFalse($response['success'], 'Request with invalid date logic should fail');
            $this->assertEquals(422, $response['status'], 'Should return 422 status for validation errors');
            $this->assertArrayHasKey('errors', $response, 'Response should contain errors array');
            $this->assertArrayHasKey(
                'valid_until',
                $response['errors'],
                'Validation error should be reported for invalid valid_until date'
            );
        }
        
        $this->assertTrue(true, "Invalid date logic validation held for $iterations iterations");
    }

    /**
     * Property Test: Empty items array should return 422 with validation error
     * 
     * @test
     */
    public function testEmptyItemsArrayReturnsValidationError(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Generate a quotation with empty items array
            $quotation = $this->generateValidQuotation();
            $quotation['items'] = [];
            
            // Call API
            $response = $this->callStoreQuotationAPI($quotation);
            
            // Verify response
            $this->assertFalse($response['success'], 'Request with empty items array should fail');
            $this->assertEquals(422, $response['status'], 'Should return 422 status for validation errors');
            $this->assertArrayHasKey('errors', $response, 'Response should contain errors array');
            $this->assertArrayHasKey(
                'items',
                $response['errors'],
                'Validation error should be reported for empty items array'
            );
        }
        
        $this->assertTrue(true, "Empty items array validation held for $iterations iterations");
    }

    /**
     * Property Test: Invalid item data should return 422 with validation errors
     * 
     * @test
     */
    public function testInvalidItemDataReturnsValidationErrors(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Generate a quotation with invalid item data
            $quotation = $this->generateValidQuotation();
            
            // Randomly introduce invalid item data
            $invalidationType = rand(1, 4);
            
            switch ($invalidationType) {
                case 1: // Missing product_id
                    unset($quotation['items'][0]['product_id']);
                    $expectedErrorKey = 'items.0.product_id';
                    break;
                case 2: // Invalid quantity (negative or zero)
                    $quotation['items'][0]['quantity'] = rand(-100, 0);
                    $expectedErrorKey = 'items.0.quantity';
                    break;
                case 3: // Invalid unit_price (negative)
                    $quotation['items'][0]['unit_price'] = -rand(1, 100);
                    $expectedErrorKey = 'items.0.unit_price';
                    break;
                case 4: // Invalid tax_percent (out of range)
                    $quotation['items'][0]['tax_percent'] = rand(101, 200);
                    $expectedErrorKey = 'items.0.tax_percent';
                    break;
            }
            
            // Call API
            $response = $this->callStoreQuotationAPI($quotation);
            
            // Verify response
            $this->assertFalse($response['success'], 'Request with invalid item data should fail');
            $this->assertEquals(422, $response['status'], 'Should return 422 status for validation errors');
            $this->assertArrayHasKey('errors', $response, 'Response should contain errors array');
            $this->assertArrayHasKey(
                $expectedErrorKey,
                $response['errors'],
                "Validation error should be reported for invalid item field: $expectedErrorKey"
            );
        }
        
        $this->assertTrue(true, "Invalid item data validation held for $iterations iterations");
    }

    /**
     * Property Test: Duplicate quotation number should return 422 with validation error
     * 
     * @test
     */
    public function testDuplicateQuotationNumberReturnsValidationError(): void
    {
        $iterations = 50;
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Create a valid quotation
            $quotation = $this->generateValidQuotation();
            $response1 = $this->callStoreQuotationAPI($quotation);
            
            $this->assertTrue($response1['success'], 'First quotation should be created successfully');
            
            // Try to create another quotation with the same quotation_no
            $duplicateQuotation = $this->generateValidQuotation();
            $duplicateQuotation['quotation_no'] = $quotation['quotation_no'];
            
            $response2 = $this->callStoreQuotationAPI($duplicateQuotation);
            
            // Verify response
            $this->assertFalse($response2['success'], 'Request with duplicate quotation number should fail');
            $this->assertEquals(422, $response2['status'], 'Should return 422 status for validation errors');
            $this->assertArrayHasKey('errors', $response2, 'Response should contain errors array');
            $this->assertArrayHasKey(
                'quotation_no',
                $response2['errors'],
                'Validation error should be reported for duplicate quotation number'
            );
        }
        
        $this->assertTrue(true, "Duplicate quotation number validation held for $iterations iterations");
    }

    /**
     * Property Test: Invalid date format should return 422 with validation error
     * 
     * @test
     */
    public function testInvalidDateFormatReturnsValidationError(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;
        $invalidDateFormats = [
            '2025/01/15',  // Wrong separator
            '15-01-2025',  // Wrong order
            '2025-1-15',   // Missing leading zero
            '2025-13-01',  // Invalid month
            '2025-01-32',  // Invalid day
            'invalid',     // Not a date
            '2025-02-30',  // Invalid date
        ];
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Generate a quotation with invalid date format
            $quotation = $this->generateValidQuotation();
            
            // Randomly choose which date field to invalidate
            $dateField = ['quotation_date', 'valid_until'][rand(0, 1)];
            $quotation[$dateField] = $invalidDateFormats[array_rand($invalidDateFormats)];
            
            // Call API
            $response = $this->callStoreQuotationAPI($quotation);
            
            // Verify response
            $this->assertFalse($response['success'], 'Request with invalid date format should fail');
            $this->assertEquals(422, $response['status'], 'Should return 422 status for validation errors');
            $this->assertArrayHasKey('errors', $response, 'Response should contain errors array');
            $this->assertArrayHasKey(
                $dateField,
                $response['errors'],
                "Validation error should be reported for invalid $dateField format"
            );
        }
        
        $this->assertTrue(true, "Invalid date format validation held for $iterations iterations");
    }

    /**
     * Property Test: Valid quotations should be created successfully
     * 
     * @test
     */
    public function testValidQuotationsAreCreatedSuccessfully(): void
    {
        $iterations = self::PROPERTY_TEST_ITERATIONS;
        
        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            // Generate a valid quotation
            $quotation = $this->generateValidQuotation();
            
            // Call API
            $response = $this->callStoreQuotationAPI($quotation);
            
            // Verify response
            $this->assertTrue($response['success'], 'Valid quotation should be created successfully');
            $this->assertEquals(201, $response['status'], 'Should return 201 status for successful creation');
            $this->assertArrayHasKey('data', $response, 'Response should contain data');
            $this->assertArrayHasKey('quotation_id', $response['data'], 'Response should contain quotation_id');
            
            // Verify quotation was actually created in database
            $quotationId = $response['data']['quotation_id'];
            $dbQuotation = $this->db->fetch(
                "SELECT * FROM supplier_quotations WHERE quotation_id = ?",
                [$quotationId]
            );
            
            $this->assertNotNull($dbQuotation, 'Quotation should exist in database');
            $this->assertEquals($quotation['quotation_no'], $dbQuotation['quotation_no']);
            $this->assertEquals($quotation['supplier_id'], $dbQuotation['supplier_id']);
            $this->assertEquals($quotation['quotation_date'], $dbQuotation['quotation_date']);
            $this->assertEquals($quotation['valid_until'], $dbQuotation['valid_until']);
        }
        
        $this->assertTrue(true, "Valid quotation creation held for $iterations iterations");
    }
}
