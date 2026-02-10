<?php
/**
 * Inventory & Pharmacy API Controller
 * Handles products, suppliers, and stock management
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use System\Security;
use System\Logger;
use System\JWT;
use System\Session;

class InventoryController
{
    private Database $db;
    private ?array $user;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->user = $this->getAuthUser();
    }

    private function getAuthUser(): ?array
    {
        $token = JWT::getTokenFromHeader();
        if ($token) {
            $payload = JWT::getPayload($token);
            if ($payload)
                return $payload;
        }
        if (Session::isLoggedIn())
            return Session::getUser();
        return null;
    }

    /**
     * List products (Pharmacy) - Fetching from local products table
     */
    public function products(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';

        $params = [];
        $where = ["1=1"];

        if ($search) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)";
            $term = "%$search%";
            $params = array_merge($params, [$term, $term, $term]);
        }

        if ($category) {
            $where[] = "p.category_id = ?";
            $params[] = $category;
        }

        if ($status) {
            $where[] = "p.is_active = ?";
            $params[] = ($status === 'active') ? 1 : 0;
        }

        try {
            $sql = "SELECT 
                        p.product_id,
                        p.name,
                        p.sku,
                        p.description,
                        p.unit,
                        p.hsn_code,
                        p.tax_percent,
                        p.is_active,
                        p.created_at,
                        c.name as category_name,
                        c.id as category_id,
                        COALESCE((SELECT SUM(qty_available) FROM inventory_batches WHERE product_id = p.product_id), 0) as stock
                    FROM products p
                    LEFT JOIN product_categories c ON p.category_id = c.id
                    WHERE " . implode(" AND ", $where) . " 
                    ORDER BY p.name ASC";

            $products = $this->db->fetchAll($sql, $params);
            Response::success(['products' => $products]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch products: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a single product by ID
     */
    public function getProduct(int $id): void
    {
        try {
            $sql = "SELECT 
                        p.product_id,
                        p.name,
                        p.sku,
                        p.description,
                        p.unit,
                        p.hsn_code,
                        p.tax_percent,
                        p.is_active,
                        p.created_at,
                        c.name as category_name,
                        c.id as category_id
                    FROM products p
                    LEFT JOIN product_categories c ON p.category_id = c.id
                    WHERE p.product_id = ?";

            $product = $this->db->fetch($sql, [$id]);

            if ($product) {
                Response::success($product);
            } else {
                Response::error('Product not found', 404);
            }
        } catch (\Exception $e) {
            Response::error('Failed to fetch product: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a new product
     */
    public function storeProduct(): void
    {
        $input = jsonInput();

        // Validate required fields
        $errors = [];
        if (empty($input['product_name'])) {
            $errors['product_name'] = 'Product name is required';
        }
        if (empty($input['sku'])) {
            $errors['sku'] = 'SKU/Barcode is required';
        }

        if (!empty($errors)) {
            Response::validationError($errors);
            return;
        }

        try {
            $productId = $this->db->insert('products', [
                'branch_id' => $this->user['branch_id'] ?? null,
                'name' => $input['product_name'],
                'sku' => $input['sku'],
                'category_id' => $input['category'] ?? null,
                'hsn_code' => $input['hsn_code'] ?? null,
                'unit' => $input['unit'] ?? 'nos',
                'tax_percent' => $input['tax_rate'] ?? 0,
                'description' => $input['description'] ?? null,
                'is_active' => isset($input['is_active']) ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            Response::success(['product_id' => $productId], 'Product created successfully', 201);
        } catch (\Exception $e) {
            Response::error('Failed to create product: ' . $e->getMessage(), 500);
        }
    }

    /**
     * List suppliers
     */
    public function suppliers(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        try {
            // Ensure Melina is in the list as primary
            $melina = $this->db->fetch("SELECT supplier_id FROM suppliers WHERE name LIKE 'Melina%' OR gstin = 'MELINA-PRIM'");
            if (!$melina) {
                $this->db->insert('suppliers', [
                    'name' => 'Melina Manufacturing',
                    'contact_person' => 'Central Manufacturing Unit',
                    'mobile' => '9884012345',
                    'email' => 'factory@melina.com',
                    'gstin' => 'MELINA-PRIM',
                    'address' => 'Primary Distribution Hub'
                ]);
            }

            $suppliers = $this->db->fetchAll("SELECT * FROM suppliers ORDER BY name ASC");
            Response::success(['suppliers' => $suppliers]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Consumables stock
     */
    public function consumables(): void
    {
        try {
            $consumables = $this->db->fetchAll("SELECT c.*, s.qty FROM consumables c LEFT JOIN consumable_stock s ON c.consumable_id = s.consumable_id ORDER BY c.name ASC");
            Response::success(['consumables' => $consumables]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Purchase orders
     */
    public function purchaseOrders(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        try {
            $pos = $this->db->fetchAll("
                SELECT 
                    po.*, 
                    s.name as supplier_name,
                    CASE WHEN g.grn_id IS NOT NULL THEN 1 ELSE 0 END as grn_posted
                FROM purchase_orders po 
                LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
                LEFT JOIN grns g ON po.po_id = g.po_id AND g.status = 'posted'
                ORDER BY po.created_at DESC
            ");
            Response::success(['purchase_orders' => $pos]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Get single Purchase Order with items
     */
    public function getPurchaseOrder($id): void
    {
        $id = (int) $id; // Cast manually to be safe
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        try {
            // Fetch PO Details
            $po = $this->db->fetch("
                SELECT 
                    po.*, 
                    s.name as supplier_name,
                    s.gstin as supplier_gstin,
                    s.address as supplier_address,
                    u.full_name as created_by_name,
                    CASE WHEN g.grn_id IS NOT NULL THEN 1 ELSE 0 END as grn_posted
                FROM purchase_orders po 
                LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
                LEFT JOIN users u ON po.ordered_by = u.user_id
                LEFT JOIN grns g ON po.po_id = g.po_id AND g.status = 'posted'
                WHERE po.po_id = ?
            ", [$id]);

            if (!$po) {
                Response::error('Purchase Order not found', 404);
                return;
            }

            // Fetch PO Items
            $items = $this->db->fetchAll("
                SELECT 
                    poi.*,
                    p.name as product_name,
                    p.sku,
                    p.unit,
                    p.hsn_code,
                    p.tax_percent
                FROM purchase_order_items poi
                LEFT JOIN products p ON poi.product_id = p.product_id
                WHERE poi.po_id = ?
            ", [$id]);

            // If local products join failed (e.g. for remote items), try to get names from remote DB or fallback
            // For now, we assume if product_name is null, it might be a remote item or we just return what we have
            foreach ($items as &$item) {
                if (empty($item['product_name'])) {
                    // Try to fetch from remote DB or just use ID as name if not found for now
                    // In a real scenario, we might query the remote DB here if p.name is specific to local products
                    // But typically we should have synced basic info. 
                    // Let's check if we can get it from 'items' table if it exists locally or just leave it.
                    $item['product_name'] = 'Product #' . $item['product_id'];
                }
            }

            Response::success([
                'purchase_order' => $po,
                'items' => $items
            ]);

        } catch (\Exception $e) {
            Response::error('Failed to fetch PO: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Fetch all items (RM & FG) from remote database for procurement/GRN
     */
    public function remoteItems(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        $search = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? ''; // RM or FG
        $remoteDb = Database::getRemoteInstance();

        $params = [];
        $where = ["status = 'active'"];

        if ($type) {
            $where[] = "item_code LIKE ?";
            $params[] = $type . '-%';
        }

        if ($search) {
            $where[] = "(item_name LIKE ? OR item_code LIKE ?)";
            $term = "%$search%";
            $params = array_merge($params, [$term, $term]);
        }

        try {
            $sql = "SELECT id, item_name, item_code, mrp, cost_price, selling_price, gst_rate 
                    FROM items 
                    WHERE " . implode(" AND ", $where) . " 
                    ORDER BY item_name ASC";

            $items = $remoteDb->fetchAll($sql, $params);
            Response::success(['items' => $items]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch remote items: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create Purchase Order
     */
    public function storePurchaseOrder(): void
    {
        $input = \jsonInput();
        if (empty($input['supplier_id']) || empty($input['items'])) {
            Response::error('Supplier and items are required', 422);
            return;
        }

        try {
            $this->db->beginTransaction();

            $poId = $this->db->insert('purchase_orders', [
                'branch_id' => $this->user['branch_id'] ?? null,
                'po_no' => 'PO-' . date('Ymd') . '-' . rand(100, 999),
                'supplier_id' => $input['supplier_id'],
                'status' => ($input['status'] ?? 'draft') === 'sent' ? 'ordered' : (in_array($input['status'] ?? 'draft', ['draft', 'ordered', 'received']) ? ($input['status'] ?? 'draft') : 'draft'),
                'total_amount' => $input['total_amount'] ?? 0,
                'ordered_by' => $this->user['user_id'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            foreach ($input['items'] as $item) {
                // Ensure unit_price is never NULL - use cost, unit_price, or default to 0
                $unitPrice = $item['cost'] ?? $item['unit_price'] ?? 0;

                $this->db->insert('purchase_order_items', [
                    'po_id' => $poId,
                    'product_id' => $item['product_id'], // Remote Item ID
                    'qty_ordered' => $item['qty'] ?? 0,
                    'unit_price' => $unitPrice
                ]);
            }

            $this->db->commit();

            Logger::info('Purchase Order created', [
                'po_id' => $poId,
                'items_count' => count($input['items']),
                'total_amount' => $input['total_amount'] ?? 0
            ]);

            Response::success(['po_id' => $poId], 'Purchase Order created successfully', 201);
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to create PO', [
                'error' => $e->getMessage(),
                'input' => $input
            ]);
            Response::error('Failed to create PO: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create GRN (Goods Receipt)
     */
    public function storeGRN(): void
    {
        $input = \jsonInput();

        // Validate required fields
        if (empty($input['items'])) {
            Response::error('Items are required', 422);
            return;
        }

        if (empty($input['po_id'])) {
            Response::error('Purchase Order is required', 422);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Use provided GRN number or generate one
            $grnNo = $input['grn_no'] ?? 'GRN-' . date('Ymd') . '-' . rand(100, 999);

            // Fetch PO details to get unit prices
            $poItems = $this->db->fetchAll(
                "SELECT product_id, unit_price FROM purchase_order_items WHERE po_id = ?",
                [$input['po_id']]
            );

            // Create a map of product_id => unit_price for quick lookup
            $priceMap = [];
            foreach ($poItems as $poItem) {
                $priceMap[$poItem['product_id']] = $poItem['unit_price'];
            }

            // Create GRN record
            $grnId = $this->db->insert('grns', [
                'branch_id' => $this->user['branch_id'] ?? null,
                'grn_no' => $grnNo,
                'po_id' => $input['po_id'],
                'received_by' => $this->user['user_id'] ?? null,
                'received_at' => date('Y-m-d H:i:s'),
                'total_amount' => 0
            ]);

            $totalAmount = 0;

            foreach ($input['items'] as $item) {
                // Validate item has required fields
                if (empty($item['product_id'])) {
                    $this->db->rollBack();
                    Response::error('Product ID is required for all items', 422);
                    return;
                }

                // Get unit cost from PO or use provided value
                $unitCost = $priceMap[$item['product_id']] ?? ($item['unit_cost'] ?? 0);
                $qtyReceived = $item['qty_received'] ?? 0;
                $totalAmount += ($unitCost * $qtyReceived);

                // Calculate MRP (typically cost + margin, here using 30% margin as default)
                // You can adjust this or fetch from product master
                $mrp = $item['mrp'] ?? ($unitCost * 1.3); // 30% margin

                // Insert GRN item
                $this->db->insert('grn_items', [
                    'grn_id' => $grnId,
                    'product_id' => $item['product_id'],
                    'batch_no' => $item['batch_no'] ?? null,
                    'qty_received' => $qtyReceived,
                    'unit_cost' => $unitCost
                ]);

                // Create inventory batch record
                $this->db->insert('inventory_batches', [
                    'product_id' => $item['product_id'],
                    'branch_id' => $this->user['branch_id'] ?? null,
                    'batch_no' => $item['batch_no'] ?? null,
                    'manufacture_date' => $item['mfg_date'] ?? null,
                    'expiry_date' => $item['exp_date'] ?? null,
                    'cost_price' => $unitCost,
                    'mrp' => $mrp, // Use calculated or provided MRP
                    'qty_received' => $qtyReceived,
                    'qty_available' => $qtyReceived, // Initially all received qty is available
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Update GRN total amount
            $this->db->update('grns', [
                'total_amount' => $totalAmount
            ], "grn_id = $grnId");

            // Update PO status to 'received'
            $this->db->update('purchase_orders', [
                'status' => 'received'
            ], "po_id = {$input['po_id']}");

            $this->db->commit();

            Logger::info('GRN created successfully with inventory batches', [
                'grn_id' => $grnId,
                'grn_no' => $grnNo,
                'po_id' => $input['po_id'],
                'items_count' => count($input['items']),
                'total_amount' => $totalAmount,
                'batches_created' => count($input['items']),
                'po_status_updated' => 'received',
                'created_by' => $this->user['user_id'] ?? null
            ]);

            Response::success([
                'grn_id' => $grnId,
                'grn_no' => $grnNo,
                'total_amount' => $totalAmount
            ], 'GRN posted successfully! Stock updated.', 201);
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to create GRN', [
                'error' => $e->getMessage(),
                'po_id' => $input['po_id'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            Response::error('Failed to create GRN: ' . $e->getMessage(), 500);
        }
    }

    /**
     * List all GRNs
     */
    public function getGRNs(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        try {
            $grns = $this->db->fetchAll("
                SELECT 
                    g.*, 
                    po.po_no,
                    s.name as supplier_name,
                    u.full_name as received_by_name
                FROM grns g
                LEFT JOIN purchase_orders po ON g.po_id = po.po_id
                LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
                LEFT JOIN users u ON g.received_by = u.user_id
                ORDER BY g.received_at DESC
            ");
            Response::success(['grns' => $grns]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Get single GRN details
     */
    public function getGRN($id): void
    {
        $id = (int) $id;
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        try {
            $grn = $this->db->fetch("
                SELECT 
                    g.*, 
                    po.po_no,
                    s.name as supplier_name,
                    u.full_name as received_by_name
                FROM grns g
                LEFT JOIN purchase_orders po ON g.po_id = po.po_id
                LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
                LEFT JOIN users u ON g.received_by = u.user_id
                WHERE g.grn_id = ?
            ", [$id]);

            if (!$grn) {
                Response::error('GRN not found', 404);
                return;
            }

            $items = $this->db->fetchAll("
                SELECT 
                    gi.*,
                    p.name as product_name,
                    p.sku,
                    ib.expiry_date,
                    ib.manufacture_date,
                    poi.qty_ordered
                FROM grn_items gi
                LEFT JOIN products p ON gi.product_id = p.product_id
                LEFT JOIN inventory_batches ib ON gi.batch_no = ib.batch_no AND gi.product_id = ib.product_id
                LEFT JOIN grns g ON gi.grn_id = g.grn_id
                LEFT JOIN purchase_order_items poi ON g.po_id = poi.po_id AND gi.product_id = poi.product_id
                WHERE gi.grn_id = ?
            ", [$id]);

            Response::success([
                'grn' => $grn,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }


    /**
     * Allocate batches using FIFO
     * POST /api/inventory/allocate
     */
    public function allocateBatches(): void
    {
        $input = jsonInput();

        $errors = [];
        if (empty($input['product_id'])) {
            $errors['product_id'] = 'Product ID is required';
        }
        if (empty($input['quantity']) || $input['quantity'] <= 0) {
            $errors['quantity'] = 'Valid quantity is required';
        }

        if (!empty($errors)) {
            Response::validationError($errors);
            return;
        }

        try {
            $allocationService = new \Api\Services\InventoryAllocationService();

            $allocations = $allocationService->allocateBatches(
                (int) $input['product_id'],
                (int) $input['quantity'],
                $this->user['branch_id'] ?? null
            );

            Response::success([
                'allocations' => $allocations,
                'total_quantity' => array_sum(array_column($allocations, 'quantity')),
                'total_amount' => array_sum(array_column($allocations, 'amount'))
            ], 'Batches allocated successfully');

        } catch (\Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Get stock summary grouped by products (for stock page)
     * GET /api/v1/inventory/stock-products
     */
    public function getStockProducts(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        $search = $_GET['search'] ?? '';
        $supplier = $_GET['supplier'] ?? '';
        $status = $_GET['status'] ?? '';

        try {
            $params = [];
            $where = ["1=1"];

            if ($search) {
                $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
                $term = "%$search%";
                $params = array_merge($params, [$term, $term]);
            }

            if ($supplier) {
                $where[] = "s.supplier_id = ?";
                $params[] = $supplier;
            }

            // Get products with aggregated batch data
            $sql = "SELECT 
                        p.product_id,
                        p.name as product_name,
                        p.sku,
                        COALESCE(SUM(ib.qty_available), 0) as total_qty,
                        COUNT(DISTINCT ib.batch_id) as batch_count,
                        AVG(ib.cost_price) as avg_cost,
                        MAX(ib.mrp) as mrp
                    FROM products p
                    LEFT JOIN inventory_batches ib ON p.product_id = ib.product_id AND ib.qty_available > 0
                    LEFT JOIN grn_items gi ON ib.batch_no = gi.batch_no AND ib.product_id = gi.product_id
                    LEFT JOIN grns g ON gi.grn_id = g.grn_id
                    LEFT JOIN purchase_orders po ON g.po_id = po.po_id
                    LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
                    WHERE " . implode(" AND ", $where) . "
                    GROUP BY p.product_id, p.name, p.sku
                    HAVING total_qty > 0";

            if ($status === 'in_stock') {
                $sql .= " AND total_qty > 10";
            } elseif ($status === 'low_stock') {
                $sql .= " AND total_qty > 0 AND total_qty <= 10";
            } elseif ($status === 'out_of_stock') {
                $sql .= " AND total_qty = 0";
            }

            $sql .= " ORDER BY p.name ASC";

            $products = $this->db->fetchAll($sql, $params);

            // Get summary statistics
            $summarySQL = "SELECT 
                            COUNT(DISTINCT p.product_id) as total_products,
                            COUNT(DISTINCT ib.batch_id) as total_batches,
                            SUM(CASE WHEN ib.qty_available > 0 AND ib.qty_available <= 10 THEN 1 ELSE 0 END) as low_stock,
                            SUM(CASE WHEN ib.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY) THEN 1 ELSE 0 END) as near_expiry
                        FROM products p
                        LEFT JOIN inventory_batches ib ON p.product_id = ib.product_id AND ib.qty_available > 0";

            $summary = $this->db->fetch($summarySQL);

            Response::success([
                'products' => $products,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            Logger::error('Failed to fetch stock products', [
                'error' => $e->getMessage()
            ]);
            Response::error('Failed to fetch stock products: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all inventory batches
     * GET /api/v1/inventory/batches
     */
    public function getAllBatches(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        $search = $_GET['search'] ?? '';
        $productId = $_GET['product_id'] ?? '';
        $expiryStatus = $_GET['expiry_status'] ?? '';

        try {
            $params = [];
            $where = ["ib.qty_available > 0"]; // Only show batches with available stock

            if ($search) {
                $where[] = "(p.name LIKE ? OR p.sku LIKE ? OR ib.batch_no LIKE ?)";
                $term = "%$search%";
                $params = array_merge($params, [$term, $term, $term]);
            }

            if ($productId) {
                $where[] = "ib.product_id = ?";
                $params[] = $productId;
            }

            if ($expiryStatus === 'expired') {
                $where[] = "ib.expiry_date < CURDATE()";
            } elseif ($expiryStatus === 'near_expiry') {
                $where[] = "ib.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)";
            } elseif ($expiryStatus === 'active') {
                $where[] = "(ib.expiry_date IS NULL OR ib.expiry_date > DATE_ADD(CURDATE(), INTERVAL 90 DAY))";
            }

            $sql = "SELECT 
                        ib.batch_id,
                        ib.batch_no as batch_number,
                        ib.product_id,
                        p.name as product_name,
                        p.sku,
                        ib.manufacture_date as mfg_date,
                        ib.expiry_date as exp_date,
                        ib.cost_price as purchase_price,
                        ib.mrp,
                        ib.qty_received,
                        ib.qty_available as quantity,
                        ib.created_at as receipt_date,
                        g.grn_no as grn_number,
                        po.po_no as po_number,
                        s.name as supplier_name,
                        'approved' as qc_status,
                        'Not Set' as rack_location
                    FROM inventory_batches ib
                    INNER JOIN products p ON ib.product_id = p.product_id
                    LEFT JOIN grn_items gi ON ib.batch_no = gi.batch_no AND ib.product_id = gi.product_id
                    LEFT JOIN grns g ON gi.grn_id = g.grn_id
                    LEFT JOIN purchase_orders po ON g.po_id = po.po_id
                    LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
                    WHERE " . implode(" AND ", $where) . "
                    ORDER BY ib.created_at DESC, ib.expiry_date ASC";

            $batches = $this->db->fetchAll($sql, $params);

            Response::success(['batches' => $batches]);
        } catch (\Exception $e) {
            Logger::error('Failed to fetch inventory batches', [
                'error' => $e->getMessage()
            ]);
            Response::error('Failed to fetch inventory batches: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get available batches for a product
     * GET /api/inventory/batches/:productId
     */
    public function getBatches(int $productId): void
    {
        try {
            $allocationService = new \Api\Services\InventoryAllocationService();

            $batches = $allocationService->getAvailableBatches(
                $productId,
                $this->user['branch_id'] ?? null
            );

            Response::success(['batches' => $batches]);

        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Get expiring batches
     * GET /api/inventory/expiring
     */
    public function getExpiringBatches(): void
    {
        $daysThreshold = (int) ($_GET['days'] ?? 90);

        try {
            $allocationService = new \Api\Services\InventoryAllocationService();

            $batches = $allocationService->getExpiringBatches(
                $this->user['branch_id'] ?? null,
                $daysThreshold
            );

            Response::success([
                'batches' => $batches,
                'count' => count($batches),
                'days_threshold' => $daysThreshold
            ]);

        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Get low stock products
     * GET /api/inventory/low-stock
     */
    public function getLowStock(): void
    {
        try {
            $allocationService = new \Api\Services\InventoryAllocationService();

            $products = $allocationService->getLowStockProducts(
                $this->user['branch_id'] ?? null
            );

            Response::success([
                'products' => $products,
                'count' => count($products)
            ]);

        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Get stock summary for a product
     * GET /api/inventory/stock-summary/:productId
     */
    public function getStockSummary(int $productId): void
    {
        try {
            $allocationService = new \Api\Services\InventoryAllocationService();

            $summary = $allocationService->getStockSummary(
                $productId,
                $this->user['branch_id'] ?? null
            );

            Response::success($summary);

        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * List Pharmacy Categories
     */
    public function categories(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';

        try {
            $params = [];
            $sql = "SELECT *, (SELECT COUNT(*) FROM products WHERE category_id = product_categories.id) as products_count FROM product_categories WHERE 1=1";

            if ($search) {
                $sql .= " AND (name LIKE ? OR description LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            if ($status) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY name ASC";

            $categories = $this->db->fetchAll($sql, $params);
            Response::success(['categories' => $categories]);
        } catch (\Exception $e) {
            Response::error('DB Error: ' . $e->getMessage(), 500);
        }
    }

    public function storeCategory(): void
    {
        $input = jsonInput();
        if (empty($input['name'])) {
            Response::error('Category name is required', 422);
            return;
        }

        try {
            $id = $this->db->insert('product_categories', [
                'name' => $input['name'],
                'description' => $input['description'] ?? '',
                'status' => $input['status'] ?? 'active',
                'created_by' => $this->user['user_id'] ?? null
            ]);
            Response::success(['id' => $id], 'Category created successfully', 201);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function updateCategory(int $id): void
    {
        $input = jsonInput();
        try {
            $this->db->update('product_categories', [
                'name' => $input['name'],
                'description' => $input['description'] ?? '',
                'status' => $input['status'] ?? 'active'
            ], "id = $id");
            Response::success(null, 'Category updated successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function deleteCategory(int $id): void
    {
        try {
            $this->db->delete('product_categories', "id = $id");
            Response::success(null, 'Category deleted successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * List Product Units
     */
    public function units(): void
    {
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';

        try {
            $params = [];
            $sql = "SELECT * FROM product_units WHERE 1=1";

            if ($search) {
                $sql .= " AND (name LIKE ? OR code LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            if ($status) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY name ASC";

            $units = $this->db->fetchAll($sql, $params);
            Response::success(['units' => $units]);
        } catch (\Exception $e) {
            Response::error('DB Error: ' . $e->getMessage(), 500);
        }
    }

    public function getUnit(int $id): void
    {
        try {
            $unit = $this->db->fetch("SELECT * FROM product_units WHERE id = ?", [$id]);
            if ($unit) {
                Response::success($unit);
            } else {
                Response::error('Unit not found', 404);
            }
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function storeUnit(): void
    {
        $input = jsonInput();
        if (empty($input['name']) || empty($input['code'])) {
            Response::error('Unit name and code are required', 422);
            return;
        }

        try {
            $id = $this->db->insert('product_units', [
                'name' => $input['name'],
                'code' => strtoupper($input['code']),
                'description' => $input['description'] ?? '',
                'status' => $input['status'] ?? 'active',
                'created_by' => $this->user['user_id'] ?? null
            ]);
            Response::success(['id' => $id], 'Unit created successfully', 201);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function updateUnit(int $id): void
    {
        $input = jsonInput();
        try {
            $this->db->update('product_units', [
                'name' => $input['name'],
                'code' => strtoupper($input['code']),
                'description' => $input['description'] ?? '',
                'status' => $input['status'] ?? 'active'
            ], "id = $id");
            Response::success(null, 'Unit updated successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function deleteUnit(int $id): void
    {
        try {
            $this->db->delete('product_units', "id = $id");
            Response::success(null, 'Unit deleted successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getCategory(int $id): void
    {
        try {
            $category = $this->db->fetch("SELECT * FROM product_categories WHERE id = ?", [$id]);
            if ($category) {
                Response::success($category);
            } else {
                Response::error('Category not found', 404);
            }
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Get PO History for a product
     * GET /api/v1/inventory/products/{id}/po-history
     */
    public function getProductPOHistory(int $id): void
    {
        try {
            $sql = "SELECT 
                        po.po_no,
                        po.created_at as po_date,
                        s.name as supplier_name,
                        poi.qty_ordered as quantity,
                        poi.unit_price,
                        poi.amount as total_amount,
                        po.status
                    FROM purchase_order_items poi
                    INNER JOIN purchase_orders po ON poi.po_id = po.po_id
                    LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
                    WHERE poi.product_id = ?
                    ORDER BY po.created_at DESC";

            $history = $this->db->fetchAll($sql, [$id]);
            Response::success(['po_history' => $history]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch PO history: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get GRN History for a product
     * GET /api/v1/inventory/products/{id}/grn-history
     */
    public function getProductGRNHistory(int $id): void
    {
        try {
            $sql = "SELECT 
                        g.grn_no,
                        g.received_at as grn_date,
                        gi.batch_no,
                        ib.expiry_date,
                        gi.qty_received as quantity,
                        gi.unit_cost as cost_price,
                        (gi.qty_received * gi.unit_cost) as total_value,
                        s.name as supplier_name
                    FROM grn_items gi
                    INNER JOIN grns g ON gi.grn_id = g.grn_id
                    LEFT JOIN inventory_batches ib ON ib.product_id = gi.product_id AND ib.batch_no = gi.batch_no
                    LEFT JOIN purchase_orders po ON g.po_id = po.po_id
                    LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
                    WHERE gi.product_id = ?
                    ORDER BY g.received_at DESC";

            $history = $this->db->fetchAll($sql, [$id]);
            Response::success(['grn_history' => $history]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch GRN history: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Price History for a product
     * GET /api/v1/inventory/products/{id}/price-history
     */
    public function getProductPriceHistory(int $id): void
    {
        try {
            $sql = "SELECT 
                        g.received_at as date,
                        gi.batch_no as batch,
                        gi.unit_cost as cost_price,
                        s.name as supplier_name,
                        CASE 
                            WHEN LAG(gi.unit_cost) OVER (ORDER BY g.received_at) IS NULL THEN 0
                            ELSE ROUND(((gi.unit_cost - LAG(gi.unit_cost) OVER (ORDER BY g.received_at)) / LAG(gi.unit_cost) OVER (ORDER BY g.received_at)) * 100, 2)
                        END as price_change_percent
                    FROM grn_items gi
                    INNER JOIN grns g ON gi.grn_id = g.grn_id
                    LEFT JOIN purchase_orders po ON g.po_id = po.po_id
                    LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
                    WHERE gi.product_id = ?
                    ORDER BY g.received_at DESC";

            $history = $this->db->fetchAll($sql, [$id]);
            Response::success(['price_history' => $history]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch price history: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Suppliers for a product
     * GET /api/v1/inventory/products/{id}/suppliers
     */
    public function getProductSuppliers(int $id): void
    {
        try {
            $sql = "SELECT DISTINCT
                        s.supplier_id,
                        s.name,
                        s.contact_person,
                        s.mobile,
                        s.email,
                        COUNT(DISTINCT po.po_id) as total_orders,
                        SUM(poi.qty_ordered) as total_quantity,
                        AVG(poi.unit_price) as avg_price,
                        MAX(po.created_at) as last_order_date
                    FROM suppliers s
                    INNER JOIN purchase_orders po ON s.supplier_id = po.supplier_id
                    INNER JOIN purchase_order_items poi ON po.po_id = poi.po_id
                    WHERE poi.product_id = ?
                    GROUP BY s.supplier_id, s.name, s.contact_person, s.mobile, s.email
                    ORDER BY last_order_date DESC";

            $suppliers = $this->db->fetchAll($sql, [$id]);
            Response::success(['suppliers' => $suppliers]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch suppliers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Stock Details for a product
     * GET /api/v1/inventory/products/{id}/stock
     */
    public function getProductStock(int $id): void
    {
        try {
            $sql = "SELECT 
                        batch_no,
                        expiry_date,
                        qty_available,
                        cost_price,
                        (qty_available * cost_price) as total_value,
                        CASE 
                            WHEN expiry_date IS NULL THEN 'active'
                            WHEN expiry_date < CURDATE() THEN 'expired'
                            WHEN expiry_date < DATE_ADD(CURDATE(), INTERVAL 90 DAY) THEN 'expiring_soon'
                            ELSE 'active'
                        END as status
                    FROM inventory_batches
                    WHERE product_id = ? AND qty_available > 0
                    ORDER BY expiry_date ASC";

            $stock = $this->db->fetchAll($sql, [$id]);
            Response::success(['stock' => $stock]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch stock details: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Product Overview Stats
     * GET /api/v1/inventory/products/{id}/overview
     */
    public function getProductOverview(int $id): void
    {
        try {
            // Total purchased
            $totalPurchased = $this->db->fetch(
                "SELECT COALESCE(SUM(qty_ordered), 0) as total FROM purchase_order_items WHERE product_id = ?",
                [$id]
            )['total'] ?? 0;

            // Current stock
            $currentStock = $this->db->fetch(
                "SELECT COALESCE(SUM(qty_available), 0) as total FROM inventory_batches WHERE product_id = ?",
                [$id]
            )['total'] ?? 0;

            // Average cost price
            $avgCost = $this->db->fetch(
                "SELECT COALESCE(AVG(cost_price), 0) as avg FROM inventory_batches WHERE product_id = ? AND qty_available > 0",
                [$id]
            )['avg'] ?? 0;

            // Total suppliers
            $totalSuppliers = $this->db->fetch(
                "SELECT COUNT(DISTINCT s.supplier_id) as total 
                 FROM suppliers s
                 INNER JOIN purchase_orders po ON s.supplier_id = po.supplier_id
                 INNER JOIN purchase_order_items poi ON po.po_id = poi.po_id
                 WHERE poi.product_id = ?",
                [$id]
            )['total'] ?? 0;

            // Recent activity
            $recentActivity = $this->db->fetchAll(
                "SELECT 
                    'GRN' as type,
                    g.grn_no as reference,
                    g.received_at as date,
                    gi.qty_received as quantity,
                    s.name as supplier
                 FROM grn_items gi
                 INNER JOIN grns g ON gi.grn_id = g.grn_id
                 LEFT JOIN purchase_orders po ON g.po_id = po.po_id
                 LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
                 WHERE gi.product_id = ?
                 ORDER BY g.received_at DESC
                 LIMIT 5",
                [$id]
            );

            Response::success([
                'total_purchased' => (int) $totalPurchased,
                'current_stock' => (int) $currentStock,
                'avg_cost' => round((float) $avgCost, 2),
                'total_suppliers' => (int) $totalSuppliers,
                'recent_activity' => $recentActivity
            ]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch overview: ' . $e->getMessage(), 500);
        }
    }

    /**
     * List all quotations with optional filters
     * GET /api/v1/quotations
     */
    public function quotations(): void
    {
        $supplierId = $_GET['supplier_id'] ?? null;
        $status = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? '';
        $fromDate = $_GET['from_date'] ?? null;
        $toDate = $_GET['to_date'] ?? null;
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 50);
        $offset = ($page - 1) * $limit;

        $params = [];
        $where = ["1=1"];

        // Filter by supplier
        if ($supplierId) {
            $where[] = "sq.supplier_id = ?";
            $params[] = $supplierId;
        }

        // Filter by status
        if ($status) {
            $where[] = "sq.status = ?";
            $params[] = $status;
        }

        // Search by quotation number or supplier reference
        if ($search) {
            $where[] = "(sq.quotation_no LIKE ? OR sq.supplier_reference LIKE ? OR s.name LIKE ?)";
            $term = "%$search%";
            $params = array_merge($params, [$term, $term, $term]);
        }

        // Filter by date range
        if ($fromDate) {
            $where[] = "sq.quotation_date >= ?";
            $params[] = $fromDate;
        }

        if ($toDate) {
            $where[] = "sq.quotation_date <= ?";
            $params[] = $toDate;
        }

        try {
            // Build the main query
            $sql = "SELECT 
                        sq.quotation_id,
                        sq.quotation_no,
                        sq.supplier_id,
                        s.name as supplier_name,
                        sq.quotation_date,
                        sq.valid_until,
                        sq.status,
                        sq.supplier_reference,
                        sq.remarks,
                        sq.created_at,
                        COUNT(qi.quotation_item_id) as items_count,
                        COALESCE(SUM(qi.quantity * qi.unit_price), 0) as total_value
                    FROM supplier_quotations sq
                    LEFT JOIN suppliers s ON sq.supplier_id = s.supplier_id
                    LEFT JOIN quotation_items qi ON sq.quotation_id = qi.quotation_id
                    WHERE " . implode(" AND ", $where) . "
                    GROUP BY sq.quotation_id, sq.quotation_no, sq.supplier_id, s.name, 
                             sq.quotation_date, sq.valid_until, sq.status, sq.supplier_reference,
                             sq.remarks, sq.created_at
                    ORDER BY sq.quotation_date DESC, sq.quotation_id DESC
                    LIMIT ? OFFSET ?";

            $params[] = $limit;
            $params[] = $offset;

            $quotations = $this->db->fetchAll($sql, $params);

            // Get total count for pagination
            $countSql = "SELECT COUNT(DISTINCT sq.quotation_id) as total
                        FROM supplier_quotations sq
                        LEFT JOIN suppliers s ON sq.supplier_id = s.supplier_id
                        WHERE " . implode(" AND ", $where);

            $countParams = array_slice($params, 0, -2); // Remove limit and offset
            $totalResult = $this->db->fetch($countSql, $countParams);
            $total = (int) ($totalResult['total'] ?? 0);

            Response::success([
                'quotations' => $quotations,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]);
        } catch (\Exception $e) {
            Logger::error('Failed to fetch quotations', ['error' => $e->getMessage()]);
            Response::error('Failed to fetch quotations: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get quotations for a specific supplier
     * GET /api/v1/quotations/supplier/{id}
     */
    public function getSupplierQuotations(int $supplierId): void
    {
        try {
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

            $supplier = $this->db->fetch($supplierSql, [$supplierId]);

            if (!$supplier) {
                Response::error('Supplier not found', 404);
                return;
            }

            // Fetch all quotations for this supplier with item counts
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

            $quotations = $this->db->fetchAll($quotationsSql, [$supplierId]);

            Response::success([
                'supplier' => $supplier,
                'quotations' => $quotations
            ]);
        } catch (\Exception $e) {
            Logger::error('Failed to fetch supplier quotations', [
                'supplier_id' => $supplierId,
                'error' => $e->getMessage()
            ]);
            Response::error('Failed to fetch supplier quotations: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get quotation details with all items
     * GET /api/v1/quotations/{id}
     */
    public function getQuotation(int $id): void
    {
        try {
            // Fetch quotation header with supplier info
            $quotationSql = "SELECT 
                                sq.quotation_id,
                                sq.quotation_no,
                                sq.supplier_id,
                                s.name as supplier_name,
                                s.gstin as supplier_gstin,
                                s.contact_person as supplier_contact_person,
                                s.mobile as supplier_mobile,
                                s.email as supplier_email,
                                s.address as supplier_address,
                                sq.quotation_date,
                                sq.valid_until,
                                sq.supplier_reference,
                                sq.status,
                                sq.remarks,
                                sq.created_by,
                                sq.created_at,
                                sq.updated_at
                            FROM supplier_quotations sq
                            INNER JOIN suppliers s ON sq.supplier_id = s.supplier_id
                            WHERE sq.quotation_id = ?";

            $quotation = $this->db->fetch($quotationSql, [$id]);

            if (!$quotation) {
                Response::error('Quotation not found', 404);
                return;
            }

            // Fetch all quotation items with product details
            $itemsSql = "SELECT 
                            qi.quotation_item_id,
                            qi.product_id,
                            p.name as product_name,
                            p.sku,
                            p.unit,
                            p.hsn_code,
                            qi.quantity,
                            qi.unit_price,
                            qi.tax_percent,
                            qi.mrp,
                            qi.remarks
                        FROM quotation_items qi
                        INNER JOIN products p ON qi.product_id = p.product_id
                        WHERE qi.quotation_id = ?
                        ORDER BY qi.quotation_item_id ASC";

            $items = $this->db->fetchAll($itemsSql, [$id]);

            Response::success([
                'quotation' => $quotation,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            Logger::error('Failed to fetch quotation details', [
                'quotation_id' => $id,
                'error' => $e->getMessage()
            ]);
            Response::error('Failed to fetch quotation details: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new quotation
     * POST /api/v1/quotations
     */
    public function storeQuotation(): void
    {
        $input = jsonInput();

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
            Response::validationError($errors);
            return;
        }

        try {
            // Check if quotation number already exists
            $existingQuotation = $this->db->fetch(
                "SELECT quotation_id FROM supplier_quotations WHERE quotation_no = ?",
                [$input['quotation_no']]
            );

            if ($existingQuotation) {
                Response::validationError(['quotation_no' => 'Quotation number already exists']);
                return;
            }

            // Check if supplier exists
            $supplier = $this->db->fetch(
                "SELECT supplier_id FROM suppliers WHERE supplier_id = ?",
                [$input['supplier_id']]
            );

            if (!$supplier) {
                Response::validationError(['supplier_id' => 'Supplier not found']);
                return;
            }

            // Begin transaction for atomicity
            $this->db->beginTransaction();

            // Insert quotation header
            $quotationId = $this->db->insert('supplier_quotations', [
                'supplier_id' => $input['supplier_id'],
                'quotation_no' => $input['quotation_no'],
                'quotation_date' => $input['quotation_date'],
                'valid_until' => $input['valid_until'],
                'supplier_reference' => $input['supplier_reference'] ?? null,
                'status' => $input['status'] ?? 'active',
                'remarks' => $input['remarks'] ?? null,
                'created_by' => $this->user['user_id'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Insert quotation items in batch
            foreach ($input['items'] as $item) {
                // Verify product exists
                $product = $this->db->fetch(
                    "SELECT product_id FROM products WHERE product_id = ?",
                    [$item['product_id']]
                );

                if (!$product) {
                    $this->db->rollBack();
                    Response::validationError([
                        'items' => "Product with ID {$item['product_id']} not found"
                    ]);
                    return;
                }

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

            // Commit transaction
            $this->db->commit();

            Logger::info('Quotation created successfully', [
                'quotation_id' => $quotationId,
                'quotation_no' => $input['quotation_no'],
                'supplier_id' => $input['supplier_id'],
                'items_count' => count($input['items']),
                'created_by' => $this->user['user_id'] ?? null
            ]);

            Response::success([
                'quotation_id' => $quotationId
            ], 'Quotation created successfully', 201);

        } catch (\PDOException $e) {
            $this->db->rollBack();
            Logger::error('Database error in quotation creation', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            Response::error('Failed to save quotation. Please try again.', 500);
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('Unexpected error in quotation creation', [
                'error' => $e->getMessage()
            ]);
            Response::error('An unexpected error occurred', 500);
        }
    }

    /**
     * Delete/Cancel quotation (soft delete)
     * DELETE /api/v1/quotations/{id}
     */
    public function deleteQuotation(int $id): void
    {
        try {
            // Check if quotation exists
            $quotation = $this->db->fetch(
                "SELECT quotation_id, quotation_no, status FROM supplier_quotations WHERE quotation_id = ?",
                [$id]
            );

            if (!$quotation) {
                Response::error('Quotation not found', 404);
                return;
            }

            // Soft delete: Update status to 'cancelled'
            $this->db->update('supplier_quotations', [
                'status' => 'cancelled',
                'updated_at' => date('Y-m-d H:i:s')
            ], "quotation_id = $id");

            Logger::info('Quotation cancelled successfully', [
                'quotation_id' => $id,
                'quotation_no' => $quotation['quotation_no'],
                'previous_status' => $quotation['status'],
                'cancelled_by' => $this->user['user_id'] ?? null
            ]);

            Response::success(null, 'Quotation cancelled successfully');

        } catch (\PDOException $e) {
            Logger::error('Database error in quotation cancellation', [
                'quotation_id' => $id,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            Response::error('Failed to cancel quotation. Please try again.', 500);
        } catch (\Exception $e) {
            Logger::error('Unexpected error in quotation cancellation', [
                'quotation_id' => $id,
                'error' => $e->getMessage()
            ]);
            Response::error('An unexpected error occurred', 500);
        }
    }

    /**
     * Update existing quotation
     * PUT /api/v1/quotations/{id}
     */
    public function updateQuotation(int $id): void
    {
        $input = jsonInput();

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
            Response::validationError($errors);
            return;
        }

        try {
            // Check if quotation exists
            $existingQuotation = $this->db->fetch(
                "SELECT quotation_id FROM supplier_quotations WHERE quotation_id = ?",
                [$id]
            );

            if (!$existingQuotation) {
                Response::error('Quotation not found', 404);
                return;
            }

            // Check if quotation number is being changed and if it conflicts with another quotation
            $quotationNoCheck = $this->db->fetch(
                "SELECT quotation_id FROM supplier_quotations WHERE quotation_no = ? AND quotation_id != ?",
                [$input['quotation_no'], $id]
            );

            if ($quotationNoCheck) {
                Response::validationError(['quotation_no' => 'Quotation number already exists']);
                return;
            }

            // Check if supplier exists
            $supplier = $this->db->fetch(
                "SELECT supplier_id FROM suppliers WHERE supplier_id = ?",
                [$input['supplier_id']]
            );

            if (!$supplier) {
                Response::validationError(['supplier_id' => 'Supplier not found']);
                return;
            }

            // Begin transaction for atomicity
            $this->db->beginTransaction();

            // Update quotation header
            $this->db->update('supplier_quotations', [
                'supplier_id' => $input['supplier_id'],
                'quotation_no' => $input['quotation_no'],
                'quotation_date' => $input['quotation_date'],
                'valid_until' => $input['valid_until'],
                'supplier_reference' => $input['supplier_reference'] ?? null,
                'status' => $input['status'] ?? 'active',
                'remarks' => $input['remarks'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ], "quotation_id = $id");

            // Delete existing items
            $this->db->delete('quotation_items', "quotation_id = $id");

            // Insert new items
            foreach ($input['items'] as $item) {
                // Verify product exists
                $product = $this->db->fetch(
                    "SELECT product_id FROM products WHERE product_id = ?",
                    [$item['product_id']]
                );

                if (!$product) {
                    $this->db->rollBack();
                    Response::validationError([
                        'items' => "Product with ID {$item['product_id']} not found"
                    ]);
                    return;
                }

                $this->db->insert('quotation_items', [
                    'quotation_id' => $id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'],
                    'tax_percent' => $item['tax_percent'] ?? 0.00,
                    'mrp' => $item['mrp'] ?? 0.00,
                    'remarks' => $item['remarks'] ?? null
                ]);
            }

            // Commit transaction
            $this->db->commit();

            Logger::info('Quotation updated successfully', [
                'quotation_id' => $id,
                'quotation_no' => $input['quotation_no'],
                'supplier_id' => $input['supplier_id'],
                'items_count' => count($input['items']),
                'updated_by' => $this->user['user_id'] ?? null
            ]);

            Response::success(null, 'Quotation updated successfully');

        } catch (\PDOException $e) {
            $this->db->rollBack();
            Logger::error('Database error in quotation update', [
                'quotation_id' => $id,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            Response::error('Failed to update quotation. Please try again.', 500);
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('Unexpected error in quotation update', [
                'quotation_id' => $id,
                'error' => $e->getMessage()
            ]);
            Response::error('An unexpected error occurred', 500);
        }
    }

    /**
     * Validate date format (YYYY-MM-DD)
     */
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Sync Melina products to local database (batch operation)
     * POST /api/v1/inventory/sync-melina-products
     */
    public function syncMelinaProducts(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $products = $input['products'] ?? [];

            if (empty($products)) {
                Response::error('No products to sync', 400);
                return;
            }

            Logger::info('Syncing Melina products to local database', [
                'count' => count($products)
            ]);

            $syncedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;

            foreach ($products as $product) {
                try {
                    // Check if product already exists by SKU
                    $existing = $this->db->fetch(
                        "SELECT product_id FROM products WHERE sku = ?",
                        [$product['sku']]
                    );

                    if ($existing) {
                        // Product exists, update it
                        $this->db->update('products', [
                            'name' => $product['name'],
                            'description' => $product['description'] ?? '',
                            'unit' => 'PCS',
                            'tax_percent' => $product['tax_percent'] ?? 0,
                            'updated_at' => date('Y-m-d H:i:s')
                        ], 'product_id = ?', [$existing['product_id']]);

                        $skippedCount++;
                    } else {
                        // Insert new product
                        $this->db->insert('products', [
                            'name' => $product['name'],
                            'sku' => $product['sku'],
                            'description' => $product['description'] ?? '',
                            'unit' => 'PCS',
                            'hsn_code' => '',
                            'tax_percent' => $product['tax_percent'] ?? 0,
                            'is_active' => 1,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        $syncedCount++;
                    }
                } catch (\Exception $e) {
                    Logger::error('Failed to sync product', [
                        'product' => $product,
                        'error' => $e->getMessage()
                    ]);
                    $errorCount++;
                }
            }

            Logger::info('Melina products sync completed', [
                'synced' => $syncedCount,
                'skipped' => $skippedCount,
                'errors' => $errorCount
            ]);

            Response::success([
                'synced' => $syncedCount,
                'skipped' => $skippedCount,
                'errors' => $errorCount,
                'total' => count($products)
            ]);

        } catch (\Exception $e) {
            Logger::error('Failed to sync Melina products', [
                'error' => $e->getMessage()
            ]);
            Response::error('Failed to sync products: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Fetch Melina products from remote database
     * GET /api/v1/inventory/melina-products
     */
    public function melinaProducts(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        $search = $_GET['search'] ?? '';
        $limit = (int) ($_GET['limit'] ?? 100);

        // Debug: Log environment variables
        Logger::info('Melina Products - Environment Check', [
            'REMOTE_DB_HOST' => $_ENV['REMOTE_DB_HOST'] ?? 'NOT SET',
            'REMOTE_DB_DATABASE' => $_ENV['REMOTE_DB_DATABASE'] ?? 'NOT SET',
            'REMOTE_DB_USERNAME' => $_ENV['REMOTE_DB_USERNAME'] ?? 'NOT SET',
            'REMOTE_DB_PASSWORD' => !empty($_ENV['REMOTE_DB_PASSWORD']) ? '***SET***' : 'NOT SET',
        ]);

        try {
            // Get remote database connection
            $remoteDb = Database::getRemoteInstance();

            $params = [];
            $where = ["i.status = 'active'"];

            if ($search) {
                $where[] = "(i.item_name LIKE ? OR i.item_code LIKE ?)";
                $term = "%$search%";
                $params = array_merge($params, [$term, $term]);
            }

            $sql = "SELECT 
                        i.id,
                        i.item_code as sku,
                        i.item_name as name,
                        i.description,
                        i.cost_price,
                        i.mrp,
                        i.selling_price,
                        i.gst_rate,
                        wb.id as batch_id,
                        wb.batch_number,
                        wb.produced_quantity as batch_qty,
                        wb.mfg_date,
                        wb.exp_date,
                        wb.rack_no,
                        wb.mrp as batch_mrp,
                        wb.selling_price as batch_selling_price,
                        wb.qc_status,
                        wb.status as batch_status,
                        wo.wo_number
                    FROM items i
                    LEFT JOIN work_orders wo ON i.id = wo.product_id AND wo.status = 'completed'
                    LEFT JOIN work_order_batches wb ON wo.id = wb.wo_id AND wb.status = 'completed'
                    WHERE " . implode(" AND ", $where) . " 
                    ORDER BY i.item_name ASC, wb.batch_number ASC
                    LIMIT ?";

            $params[] = $limit;

            $rawProducts = $remoteDb->fetchAll($sql, $params);

            // Group products by ID and aggregate batches
            $productsMap = [];
            foreach ($rawProducts as $row) {
                $productId = $row['id'];

                if (!isset($productsMap[$productId])) {
                    $productsMap[$productId] = [
                        'id' => $row['id'],
                        'sku' => $row['sku'],
                        'name' => $row['name'],
                        'description' => $row['description'],
                        'cost_price' => $row['cost_price'],
                        'mrp' => $row['mrp'],
                        'selling_price' => $row['selling_price'],
                        'gst_rate' => $row['gst_rate'],
                        'batches' => []
                    ];
                }

                // Add batch if exists
                if ($row['batch_id']) {
                    $productsMap[$productId]['batches'][] = [
                        'batch_id' => $row['batch_id'],
                        'batch_number' => $row['batch_number'],
                        'quantity' => $row['batch_qty'],
                        'mfg_date' => $row['mfg_date'],
                        'exp_date' => $row['exp_date'],
                        'rack_no' => $row['rack_no'],
                        'mrp' => $row['batch_mrp'],
                        'selling_price' => $row['batch_selling_price'],
                        'qc_status' => $row['qc_status'],
                        'status' => $row['batch_status'],
                        'wo_number' => $row['wo_number']
                    ];
                }
            }

            $products = array_values($productsMap);

            Logger::info('Melina products fetched successfully', [
                'count' => count($products),
                'search' => $search
            ]);

            Response::success([
                'products' => $products,
                'source' => 'melina_remote',
                'count' => count($products)
            ]);

        } catch (\PDOException $e) {
            Logger::error('Failed to connect to Melina remote database', [
                'error' => $e->getMessage()
            ]);
            Response::error('Failed to connect to remote database', 500);
        }
    }

    /**
     * Get PO items for GRN
     * GET /api/v1/inventory/purchase-orders/{id}/items
     */
    public function getPOItems(int $id): void
    {
        try {
            // Fetch PO details
            $po = $this->db->fetch(
                "SELECT po.*, s.name as supplier_name 
                 FROM purchase_orders po 
                 LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id 
                 WHERE po.po_id = ?",
                [$id]
            );

            if (!$po) {
                Response::error('Purchase Order not found', 404);
                return;
            }

            // Fetch PO items with product details
            $items = $this->db->fetchAll(
                "SELECT 
                    poi.po_item_id,
                    poi.product_id,
                    p.name as product_name,
                    p.sku,
                    poi.qty_ordered,
                    poi.unit_price,
                    poi.amount,
                    (poi.unit_price * poi.qty_ordered) as mrp
                 FROM purchase_order_items poi
                 INNER JOIN products p ON poi.product_id = p.product_id
                 WHERE poi.po_id = ?
                 ORDER BY poi.po_item_id ASC",
                [$id]
            );

            // Log the items for debugging
            Logger::info('Fetched PO items', [
                'po_id' => $id,
                'items_count' => count($items),
                'first_item' => $items[0] ?? null
            ]);

            Response::success([
                'po' => $po,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            Logger::error('Failed to fetch PO items', [
                'po_id' => $id,
                'error' => $e->getMessage()
            ]);
            Response::error('Failed to fetch PO items: ' . $e->getMessage(), 500);
        }
    }

    /**
     * List all warehouses/store locations
     * GET /api/v1/inventory/warehouses
     */
    public function warehouses(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_write_close();

        $search = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $status = $_GET['status'] ?? '';

        try {
            $params = [];
            $where = ["1=1"];

            if ($search) {
                $where[] = "(name LIKE ? OR code LIKE ?)";
                $term = "%$search%";
                $params = array_merge($params, [$term, $term]);
            }

            if ($type) {
                $where[] = "type = ?";
                $params[] = $type;
            }

            if ($status) {
                $where[] = "status = ?";
                $params[] = $status;
            }

            $sql = "SELECT * FROM warehouse_locations 
                    WHERE " . implode(" AND ", $where) . " 
                    ORDER BY name ASC";

            $warehouses = $this->db->fetchAll($sql, $params);
            Response::success(['warehouses' => $warehouses]);
        } catch (\Exception $e) {
            Logger::error('Failed to fetch warehouses', ['error' => $e->getMessage()]);
            Response::error('Failed to fetch warehouses: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single warehouse
     * GET /api/v1/inventory/warehouses/{id}
     */
    public function getWarehouse(int $id): void
    {
        try {
            $warehouse = $this->db->fetch(
                "SELECT * FROM warehouse_locations WHERE id = ?",
                [$id]
            );

            if ($warehouse) {
                Response::success($warehouse);
            } else {
                Response::error('Warehouse not found', 404);
            }
        } catch (\Exception $e) {
            Response::error('Failed to fetch warehouse: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new warehouse
     * POST /api/v1/inventory/warehouses
     */
    public function storeWarehouse(): void
    {
        $input = jsonInput();

        $errors = [];
        if (empty($input['code'])) {
            $errors['code'] = 'Location code is required';
        }
        if (empty($input['name'])) {
            $errors['name'] = 'Location name is required';
        }
        if (empty($input['type'])) {
            $errors['type'] = 'Location type is required';
        }

        if (!empty($errors)) {
            Response::validationError($errors);
            return;
        }

        try {
            // Check if code already exists
            $existing = $this->db->fetch(
                "SELECT id FROM warehouse_locations WHERE code = ?",
                [$input['code']]
            );

            if ($existing) {
                Response::validationError(['code' => 'Location code already exists']);
                return;
            }

            $id = $this->db->insert('warehouse_locations', [
                'code' => $input['code'],
                'name' => $input['name'],
                'type' => $input['type'],
                'capacity' => $input['capacity'] ?? null,
                'incharge' => $input['incharge'] ?? null,
                'contact' => $input['contact'] ?? null,
                'address' => $input['address'] ?? null,
                'description' => $input['description'] ?? null,
                'status' => $input['status'] ?? 'active',
                'created_by' => $this->user['user_id'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            Response::success(['id' => $id], 'Warehouse location created successfully', 201);
        } catch (\Exception $e) {
            Logger::error('Failed to create warehouse', ['error' => $e->getMessage()]);
            Response::error('Failed to create warehouse: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update warehouse
     * PUT /api/v1/inventory/warehouses/{id}
     */
    public function updateWarehouse(int $id): void
    {
        $input = jsonInput();

        try {
            $existing = $this->db->fetch(
                "SELECT id FROM warehouse_locations WHERE id = ?",
                [$id]
            );

            if (!$existing) {
                Response::error('Warehouse not found', 404);
                return;
            }

            // Check if code conflicts with another warehouse
            if (!empty($input['code'])) {
                $codeCheck = $this->db->fetch(
                    "SELECT id FROM warehouse_locations WHERE code = ? AND id != ?",
                    [$input['code'], $id]
                );

                if ($codeCheck) {
                    Response::validationError(['code' => 'Location code already exists']);
                    return;
                }
            }

            $this->db->update('warehouse_locations', [
                'code' => $input['code'],
                'name' => $input['name'],
                'type' => $input['type'],
                'capacity' => $input['capacity'] ?? null,
                'incharge' => $input['incharge'] ?? null,
                'contact' => $input['contact'] ?? null,
                'address' => $input['address'] ?? null,
                'description' => $input['description'] ?? null,
                'status' => $input['status'] ?? 'active',
                'updated_at' => date('Y-m-d H:i:s')
            ], "id = $id");

            Response::success(null, 'Warehouse location updated successfully');
        } catch (\Exception $e) {
            Logger::error('Failed to update warehouse', ['error' => $e->getMessage()]);
            Response::error('Failed to update warehouse: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete warehouse
     * DELETE /api/v1/inventory/warehouses/{id}
     */
    public function deleteWarehouse(int $id): void
    {
        try {
            $existing = $this->db->fetch(
                "SELECT id FROM warehouse_locations WHERE id = ?",
                [$id]
            );

            if (!$existing) {
                Response::error('Warehouse not found', 404);
                return;
            }

            $this->db->delete('warehouse_locations', "id = $id");
            Response::success(null, 'Warehouse location deleted successfully');
        } catch (\Exception $e) {
            Logger::error('Failed to delete warehouse', ['error' => $e->getMessage()]);
            Response::error('Failed to delete warehouse: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get latest price for a product from a supplier
     * GET /api/v1/quotations/latest-price/{supplier_id}/{product_id}
     */
    public function getLatestPrice(int $supplierId, int $productId): void
    {
        try {
            // Check if supplier is Melina
            $supplier = $this->db->fetch(
                "SELECT supplier_id, name, gstin FROM suppliers WHERE supplier_id = ?",
                [$supplierId]
            );

            if (!$supplier) {
                Response::error('Supplier not found', 404);
                return;
            }

            $isMelina = ($supplier['gstin'] === 'MELINA-PRIM' || stripos($supplier['name'], 'melina') !== false);

            if ($isMelina) {
                // Fetch from remote Melina database
                try {
                    $remoteDb = Database::getRemoteInstance();

                    $sql = "SELECT 
                                id as product_id,
                                item_name as name,
                                item_code as sku,
                                cost_price as unit_price,
                                mrp,
                                gst_rate as tax_percent,
                                stock_qty as stock
                            FROM items 
                            WHERE id = ? AND status = 'active'";

                    $product = $remoteDb->fetch($sql, [$productId]);

                    if ($product) {
                        Response::success([
                            'price_data' => $product,
                            'source' => 'melina_direct',
                            'supplier_name' => $supplier['name']
                        ]);
                    } else {
                        Response::error('Product not found in Melina database', 404);
                    }

                } catch (\Exception $e) {
                    Logger::error('Failed to fetch price from Melina database', [
                        'supplier_id' => $supplierId,
                        'product_id' => $productId,
                        'error' => $e->getMessage()
                    ]);
                    Response::error('Unable to fetch price from Melina database', 503);
                }

            } else {
                // Fetch from latest active quotation
                $sql = "SELECT 
                            qi.unit_price,
                            qi.tax_percent,
                            qi.mrp,
                            sq.quotation_no,
                            sq.quotation_date,
                            sq.valid_until,
                            p.name as product_name,
                            p.sku,
                            p.unit
                        FROM quotation_items qi
                        INNER JOIN supplier_quotations sq ON qi.quotation_id = sq.quotation_id
                        INNER JOIN products p ON qi.product_id = p.product_id
                        WHERE sq.supplier_id = ? 
                            AND qi.product_id = ?
                            AND sq.status = 'active'
                            AND sq.valid_until >= CURDATE()
                        ORDER BY sq.quotation_date DESC, sq.quotation_id DESC
                        LIMIT 1";

                $priceData = $this->db->fetch($sql, [$supplierId, $productId]);

                if ($priceData) {
                    Response::success([
                        'price_data' => $priceData,
                        'source' => 'quotation',
                        'supplier_name' => $supplier['name']
                    ]);
                } else {
                    Response::error('No active quotation found for this product and supplier', 404);
                }
            }

        } catch (\Exception $e) {
            Logger::error('Failed to fetch latest price', [
                'supplier_id' => $supplierId,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            Response::error('Failed to fetch latest price: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Compare quotations for multiple suppliers
     * GET /api/v1/quotations/compare?ids=1,2,3
     */
    public function compareQuotations(): void
    {
        $quotationIds = $_GET['ids'] ?? '';

        if (empty($quotationIds)) {
            Response::error('Quotation IDs are required. Use ?ids=1,2,3', 422);
            return;
        }

        // Parse comma-separated IDs
        $ids = array_filter(array_map('intval', explode(',', $quotationIds)));

        if (count($ids) < 2) {
            Response::error('At least 2 quotation IDs are required for comparison', 422);
            return;
        }

        try {
            // Fetch quotation headers
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $quotationsSql = "SELECT 
                                sq.quotation_id,
                                sq.quotation_no,
                                sq.quotation_date,
                                sq.valid_until,
                                sq.status,
                                s.name as supplier_name,
                                s.supplier_id
                            FROM supplier_quotations sq
                            INNER JOIN suppliers s ON sq.supplier_id = s.supplier_id
                            WHERE sq.quotation_id IN ($placeholders)
                            ORDER BY sq.quotation_date DESC";

            $quotations = $this->db->fetchAll($quotationsSql, $ids);

            if (count($quotations) < 2) {
                Response::error('Could not find enough valid quotations for comparison', 404);
                return;
            }

            // Fetch all items from these quotations
            $itemsSql = "SELECT 
                            qi.quotation_id,
                            qi.product_id,
                            p.name as product_name,
                            p.sku,
                            p.unit,
                            qi.quantity,
                            qi.unit_price,
                            qi.tax_percent,
                            qi.mrp
                        FROM quotation_items qi
                        INNER JOIN products p ON qi.product_id = p.product_id
                        WHERE qi.quotation_id IN ($placeholders)
                        ORDER BY p.name ASC";

            $items = $this->db->fetchAll($itemsSql, $ids);

            // Group items by product_id
            $productComparison = [];

            foreach ($items as $item) {
                $productId = $item['product_id'];

                if (!isset($productComparison[$productId])) {
                    $productComparison[$productId] = [
                        'product_id' => $productId,
                        'product_name' => $item['product_name'],
                        'sku' => $item['sku'],
                        'unit' => $item['unit'],
                        'quotations' => []
                    ];
                }

                $productComparison[$productId]['quotations'][$item['quotation_id']] = [
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_percent' => $item['tax_percent'],
                    'mrp' => $item['mrp'],
                    'total' => $item['quantity'] * $item['unit_price']
                ];
            }

            // Calculate best price for each product
            foreach ($productComparison as &$product) {
                $bestPrice = null;
                $bestQuotationId = null;

                foreach ($product['quotations'] as $quotationId => $data) {
                    if ($bestPrice === null || $data['unit_price'] < $bestPrice) {
                        $bestPrice = $data['unit_price'];
                        $bestQuotationId = $quotationId;
                    }
                }

                $product['best_price'] = $bestPrice;
                $product['best_quotation_id'] = $bestQuotationId;

                // Mark best price in each quotation
                foreach ($product['quotations'] as $quotationId => &$data) {
                    $data['is_best_price'] = ($quotationId == $bestQuotationId);

                    // Calculate price difference percentage
                    if ($bestPrice > 0 && $data['unit_price'] > $bestPrice) {
                        $data['price_difference_percent'] = round((($data['unit_price'] - $bestPrice) / $bestPrice) * 100, 2);
                    } else {
                        $data['price_difference_percent'] = 0;
                    }
                }
            }

            Response::success([
                'quotations' => $quotations,
                'comparison' => array_values($productComparison),
                'total_products' => count($productComparison)
            ]);

        } catch (\Exception $e) {
            Logger::error('Failed to compare quotations', [
                'quotation_ids' => $quotationIds,
                'error' => $e->getMessage()
            ]);
            Response::error('Failed to compare quotations: ' . $e->getMessage(), 500);
        }
    }
}
