<?php
/**
 * Inventory Allocation Service
 * Handles FIFO-based batch allocation for inventory management
 */

namespace Api\Services;

use System\Database;
use System\Logger;

class InventoryAllocationService
{
    private Database $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Allocate batches using FIFO (First-In-First-Out) method
     * 
     * @param int $productId Product ID
     * @param int $quantity Quantity to allocate
     * @param int|null $branchId Branch ID
     * @return array Array of batch allocations
     * @throws \Exception
     */
    public function allocateBatches(int $productId, int $quantity, ?int $branchId = null): array
    {
        if ($quantity <= 0) {
            throw new \Exception('Quantity must be greater than zero');
        }
        
        // Get available batches ordered by expiry date (FIFO)
        $where = "product_id = ? AND qty_available > 0 AND (expiry_date IS NULL OR expiry_date > CURDATE())";
        $params = [$productId];
        
        if ($branchId !== null) {
            $where .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        $batches = $this->db->fetchAll(
            "SELECT batch_id, batch_no, qty_available, cost_price, mrp, expiry_date 
             FROM inventory_batches 
             WHERE {$where}
             ORDER BY 
                CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END,
                expiry_date ASC,
                batch_id ASC",
            $params
        );
        
        if (empty($batches)) {
            throw new \Exception('No available stock for this product');
        }
        
        // Calculate total available quantity
        $totalAvailable = array_sum(array_column($batches, 'qty_available'));
        
        if ($totalAvailable < $quantity) {
            throw new \Exception("Insufficient stock. Required: {$quantity}, Available: {$totalAvailable}");
        }
        
        // Allocate from batches using FIFO
        $allocations = [];
        $remainingQty = $quantity;
        
        foreach ($batches as $batch) {
            if ($remainingQty <= 0) {
                break;
            }
            
            $allocateQty = min($remainingQty, $batch['qty_available']);
            
            $allocations[] = [
                'batch_id' => $batch['batch_id'],
                'batch_no' => $batch['batch_no'],
                'quantity' => $allocateQty,
                'cost_price' => $batch['cost_price'],
                'mrp' => $batch['mrp'],
                'expiry_date' => $batch['expiry_date'],
                'amount' => round($allocateQty * $batch['cost_price'], 2)
            ];
            
            $remainingQty -= $allocateQty;
        }
        
        Logger::info('Batches allocated (FIFO)', [
            'product_id' => $productId,
            'quantity' => $quantity,
            'allocations' => count($allocations),
            'branch_id' => $branchId
        ]);
        
        return $allocations;
    }
    
    /**
     * Reduce batch stock quantity
     * 
     * @param int $batchId Batch ID
     * @param int $quantity Quantity to reduce
     * @return bool Success status
     * @throws \Exception
     */
    public function reduceBatchStock(int $batchId, int $quantity): bool
    {
        if ($quantity <= 0) {
            throw new \Exception('Quantity must be greater than zero');
        }
        
        // Get current batch
        $batch = $this->db->fetch(
            "SELECT * FROM inventory_batches WHERE batch_id = ?",
            [$batchId]
        );
        
        if (!$batch) {
            throw new \Exception('Batch not found');
        }
        
        if ($batch['qty_available'] < $quantity) {
            throw new \Exception("Insufficient quantity in batch. Available: {$batch['qty_available']}, Requested: {$quantity}");
        }
        
        // Update batch quantity
        $newQty = $batch['qty_available'] - $quantity;
        
        $this->db->update(
            'inventory_batches',
            ['qty_available' => $newQty],
            'batch_id = ?',
            [$batchId]
        );
        
        Logger::info('Batch stock reduced', [
            'batch_id' => $batchId,
            'batch_no' => $batch['batch_no'],
            'quantity_reduced' => $quantity,
            'new_quantity' => $newQty
        ]);
        
        return true;
    }
    
    /**
     * Get available batches for a product
     * 
     * @param int $productId Product ID
     * @param int|null $branchId Branch ID
     * @return array Array of batches
     */
    public function getAvailableBatches(int $productId, ?int $branchId = null): array
    {
        $where = "product_id = ? AND qty_available > 0";
        $params = [$productId];
        
        if ($branchId !== null) {
            $where .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        $batches = $this->db->fetchAll(
            "SELECT ib.*, p.name as product_name, p.unit,
                    DATEDIFF(ib.expiry_date, CURDATE()) as days_to_expiry,
                    CASE 
                        WHEN ib.expiry_date IS NULL THEN 'No Expiry'
                        WHEN ib.expiry_date < CURDATE() THEN 'Expired'
                        WHEN DATEDIFF(ib.expiry_date, CURDATE()) <= 30 THEN 'Expiring Soon'
                        WHEN DATEDIFF(ib.expiry_date, CURDATE()) <= 90 THEN 'Near Expiry'
                        ELSE 'Good'
                    END as expiry_status
             FROM inventory_batches ib
             JOIN products p ON ib.product_id = p.product_id
             WHERE {$where}
             ORDER BY 
                CASE WHEN ib.expiry_date IS NULL THEN 1 ELSE 0 END,
                ib.expiry_date ASC",
            $params
        );
        
        return $batches;
    }
    
    /**
     * Get expiring batches
     * 
     * @param int|null $branchId Branch ID
     * @param int $daysThreshold Days threshold (default: 90)
     * @return array Array of expiring batches
     */
    public function getExpiringBatches(?int $branchId = null, int $daysThreshold = 90): array
    {
        $where = "qty_available > 0 AND expiry_date IS NOT NULL 
                  AND expiry_date > CURDATE() 
                  AND DATEDIFF(expiry_date, CURDATE()) <= ?";
        $params = [$daysThreshold];
        
        if ($branchId !== null) {
            $where .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        $batches = $this->db->fetchAll(
            "SELECT ib.*, p.name as product_name, p.unit,
                    DATEDIFF(ib.expiry_date, CURDATE()) as days_to_expiry,
                    (ib.qty_available * ib.cost_price) as value_at_risk
             FROM inventory_batches ib
             JOIN products p ON ib.product_id = p.product_id
             WHERE {$where}
             ORDER BY ib.expiry_date ASC",
            $params
        );
        
        return $batches;
    }
    
    /**
     * Get low stock products
     * 
     * @param int|null $branchId Branch ID
     * @return array Array of low stock products
     */
    public function getLowStockProducts(?int $branchId = null): array
    {
        $where = "1=1";
        $params = [];
        
        if ($branchId !== null) {
            $where .= " AND ib.branch_id = ?";
            $params[] = $branchId;
        }
        
        $products = $this->db->fetchAll(
            "SELECT p.product_id, p.name as product_name, p.unit, p.sku,
                    SUM(ib.qty_available) as total_available,
                    0 as reorder_level,
                    CASE 
                        WHEN SUM(ib.qty_available) = 0 THEN 'Out of Stock'
                        WHEN SUM(ib.qty_available) <= 10 THEN 'Critical'
                        WHEN SUM(ib.qty_available) <= 50 THEN 'Low'
                        ELSE 'Adequate'
                    END as stock_status
             FROM products p
             LEFT JOIN inventory_batches ib ON p.product_id = ib.product_id
             WHERE {$where}
             GROUP BY p.product_id
             HAVING total_available <= 50
             ORDER BY total_available ASC",
            $params
        );
        
        return $products;
    }
    
    /**
     * Validate batch availability before allocation
     * 
     * @param int $batchId Batch ID
     * @param int $quantity Quantity to check
     * @return bool True if available
     */
    public function validateBatchAvailability(int $batchId, int $quantity): bool
    {
        $batch = $this->db->fetch(
            "SELECT qty_available, expiry_date FROM inventory_batches WHERE batch_id = ?",
            [$batchId]
        );
        
        if (!$batch) {
            return false;
        }
        
        // Check quantity
        if ($batch['qty_available'] < $quantity) {
            return false;
        }
        
        // Check expiry
        if ($batch['expiry_date'] && $batch['expiry_date'] < date('Y-m-d')) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get stock summary for a product
     * 
     * @param int $productId Product ID
     * @param int|null $branchId Branch ID
     * @return array Stock summary
     */
    public function getStockSummary(int $productId, ?int $branchId = null): array
    {
        $where = "product_id = ?";
        $params = [$productId];
        
        if ($branchId !== null) {
            $where .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        $summary = $this->db->fetch(
            "SELECT 
                COUNT(*) as total_batches,
                SUM(qty_available) as total_quantity,
                SUM(qty_available * cost_price) as total_value,
                MIN(expiry_date) as nearest_expiry,
                AVG(cost_price) as avg_cost_price
             FROM inventory_batches
             WHERE {$where} AND qty_available > 0",
            $params
        );
        
        // Get product details
        $product = $this->db->fetch(
            "SELECT name, unit, sku FROM products WHERE product_id = ?",
            [$productId]
        );
        
        return [
            'product_id' => $productId,
            'product_name' => $product['name'] ?? '',
            'unit' => $product['unit'] ?? '',
            'sku' => $product['sku'] ?? '',
            'total_batches' => (int)($summary['total_batches'] ?? 0),
            'total_quantity' => (int)($summary['total_quantity'] ?? 0),
            'total_value' => round($summary['total_value'] ?? 0, 2),
            'nearest_expiry' => $summary['nearest_expiry'],
            'avg_cost_price' => round($summary['avg_cost_price'] ?? 0, 2)
        ];
    }
    
    /**
     * Process batch allocation and reduce stock (transaction)
     * 
     * @param array $allocations Array of allocations from allocateBatches()
     * @return bool Success status
     * @throws \Exception
     */
    public function processAllocations(array $allocations): bool
    {
        try {
            $this->db->beginTransaction();
            
            foreach ($allocations as $allocation) {
                $this->reduceBatchStock($allocation['batch_id'], $allocation['quantity']);
            }
            
            $this->db->commit();
            
            Logger::info('Batch allocations processed', [
                'allocations_count' => count($allocations)
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            Logger::error('Failed to process allocations', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
