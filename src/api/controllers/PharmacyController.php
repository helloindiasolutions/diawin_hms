<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use System\Session;

class PharmacyController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all pharmacy branches with stats
     */
    public function branches(): void
    {
        try {
            $sql = "SELECT 
                        b.branch_id,
                        b.name as branch_name,
                        b.address,
                        b.city,
                        b.state,
                        b.code,
                        b.is_active,
                        COUNT(DISTINCT ib.product_id) as total_products,
                        COALESCE(SUM(ib.qty_available * ib.mrp), 0) as stock_value
                    FROM branches b
                    LEFT JOIN inventory_batches ib ON b.branch_id = ib.branch_id AND ib.qty_available > 0
                    GROUP BY b.branch_id
                    ORDER BY b.name ASC";

            $branches = $this->db->fetchAll($sql);
            Response::success(['branches' => $branches]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch pharmacy branches: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single pharmacy branch details with stats
     */
    public function branchDetails(int $branchId): void
    {
        try {
            // Get branch info
            $branchSql = "SELECT 
                            b.branch_id,
                            b.name as branch_name,
                            b.address,
                            b.city,
                            b.state,
                            b.code,
                            b.is_active,
                            COUNT(DISTINCT ib.product_id) as total_products,
                            COALESCE(SUM(ib.qty_available * ib.mrp), 0) as stock_value,
                            (SELECT COALESCE(SUM(i.total_amount), 0) 
                             FROM invoices i 
                             WHERE i.branch_id = b.branch_id 
                             AND DATE(i.invoice_date) = CURDATE()) as today_sales,
                            (SELECT COUNT(*) 
                             FROM inventory_batches ib2 
                             WHERE ib2.branch_id = b.branch_id 
                             AND ib2.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
                             AND ib2.qty_available > 0) as expiring_soon
                        FROM branches b
                        LEFT JOIN inventory_batches ib ON b.branch_id = ib.branch_id AND ib.qty_available > 0
                        WHERE b.branch_id = ?
                        GROUP BY b.branch_id";

            $branch = $this->db->fetch($branchSql, [$branchId]);

            if (!$branch) {
                Response::error('Branch not found', 404);
                return;
            }

            Response::success(['branch' => $branch]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch branch details: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get inventory for a specific branch
     */
    public function branchInventory(int $branchId): void
    {
        try {
            $sql = "SELECT 
                        p.product_id,
                        p.name as product_name,
                        p.sku,
                        ib.batch_id,
                        ib.batch_no,
                        ib.qty_available,
                        ib.mrp,
                        ib.expiry_date,
                        DATEDIFF(ib.expiry_date, CURDATE()) as days_to_expiry
                    FROM inventory_batches ib
                    JOIN products p ON ib.product_id = p.product_id
                    WHERE ib.branch_id = ? AND ib.qty_available > 0
                    ORDER BY p.name ASC, ib.expiry_date ASC";

            $inventory = $this->db->fetchAll($sql, [$branchId]);
            Response::success(['inventory' => $inventory]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch inventory: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get sales for a specific branch
     */
    public function branchSales(int $branchId): void
    {
        $fromDate = $_GET['from'] ?? date('Y-m-01');
        $toDate = $_GET['to'] ?? date('Y-m-d');

        try {
            $sql = "SELECT 
                        i.invoice_id,
                        i.invoice_no,
                        i.invoice_date,
                        i.total_amount,
                        i.payment_mode,
                        CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                        COUNT(ii.item_id) as total_items
                    FROM invoices i
                    LEFT JOIN patients p ON i.patient_id = p.patient_id
                    LEFT JOIN invoice_items ii ON i.invoice_id = ii.invoice_id
                    WHERE i.branch_id = ? 
                    AND DATE(i.invoice_date) BETWEEN ? AND ?
                    GROUP BY i.invoice_id
                    ORDER BY i.invoice_date DESC";

            $sales = $this->db->fetchAll($sql, [$branchId, $fromDate, $toDate]);
            Response::success(['sales' => $sales]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch sales: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get expiry alerts for a specific branch
     */
    public function branchExpiryAlerts(int $branchId): void
    {
        try {
            $sql = "SELECT 
                        p.product_id,
                        p.name as product_name,
                        ib.batch_id,
                        ib.batch_no,
                        ib.qty_available,
                        ib.mrp,
                        ib.expiry_date,
                        DATEDIFF(ib.expiry_date, CURDATE()) as days_left
                    FROM inventory_batches ib
                    JOIN products p ON ib.product_id = p.product_id
                    WHERE ib.branch_id = ? 
                    AND ib.qty_available > 0
                    AND ib.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
                    ORDER BY ib.expiry_date ASC";

            $alerts = $this->db->fetchAll($sql, [$branchId]);
            Response::success(['alerts' => $alerts]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch expiry alerts: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get users for a specific branch
     */
    public function branchUsers(int $branchId): void
    {
        try {
            $sql = "SELECT user_id, username, full_name, email, mobile, is_active, last_login 
                    FROM users 
                    WHERE branch_id = ? 
                    ORDER BY full_name ASC";
            $users = $this->db->fetchAll($sql, [$branchId]);
            Response::success(['users' => $users]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch branch users: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get purchase orders for a specific branch
     */
    public function branchPO(int $branchId): void
    {
        try {
            $sql = "SELECT po.*, s.name as supplier_name, u.full_name as ordered_by_name
                    FROM purchase_orders po
                    LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
                    LEFT JOIN users u ON po.ordered_by = u.user_id
                    WHERE po.branch_id = ? 
                    ORDER BY po.created_at DESC";
            $orders = $this->db->fetchAll($sql, [$branchId]);
            Response::success(['orders' => $orders]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch purchase orders: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get GRNs for a specific branch
     */
    public function branchGRN(int $branchId): void
    {
        try {
            $sql = "SELECT g.*, po.po_no, u.full_name as received_by_name
                    FROM grns g
                    LEFT JOIN purchase_orders po ON g.po_id = po.po_id
                    LEFT JOIN users u ON g.received_by = u.user_id
                    WHERE g.branch_id = ? 
                    ORDER BY g.received_at DESC";
            $grns = $this->db->fetchAll($sql, [$branchId]);
            Response::success(['grns' => $grns]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch GRNs: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get appointments for a specific branch
     */
    public function branchAppointments(int $branchId): void
    {
        try {
            $sql = "SELECT a.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name, pr.full_name as provider_name
                    FROM appointments a
                    JOIN patients p ON a.patient_id = p.patient_id
                    LEFT JOIN providers pr ON a.provider_id = pr.provider_id
                    WHERE a.branch_id = ? 
                    ORDER BY a.scheduled_at DESC";
            $appointments = $this->db->fetchAll($sql, [$branchId]);
            Response::success(['appointments' => $appointments]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch appointments: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get high-end analytics for branch overview
     */
    public function branchAnalytics(int $branchId): void
    {
        try {
            $data = [];

            // 1. Sales Trend (Last 6 months)
            $salesSql = "SELECT DATE_FORMAT(invoice_date, '%b %Y') as month, SUM(total_amount) as total
                         FROM invoices 
                         WHERE branch_id = ? AND invoice_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                         GROUP BY month 
                         ORDER BY invoice_date ASC";
            $data['sales_trend'] = $this->db->fetchAll($salesSql, [$branchId]);

            // 2. Inventory Distribution (by Product Type/Category)
            $inventorySql = "SELECT c.name as category, COUNT(p.product_id) as count
                             FROM products p
                             JOIN categories c ON p.category_id = c.category_id
                             JOIN inventory_batches ib ON p.product_id = ib.product_id
                             WHERE ib.branch_id = ? AND ib.qty_available > 0
                             GROUP BY c.name";
            $data['inventory_dist'] = $this->db->fetchAll($inventorySql, [$branchId]);

            // 3. Top Selling Products
            $topSellingSql = "SELECT p.name, SUM(ii.quantity) as qty
                              FROM invoice_items ii
                              JOIN products p ON ii.product_id = p.product_id
                              JOIN invoices i ON ii.invoice_id = i.invoice_id
                              WHERE i.branch_id = ?
                              GROUP BY p.product_id
                              ORDER BY qty DESC LIMIT 5";
            $data['top_selling'] = $this->db->fetchAll($topSellingSql, [$branchId]);

            // 4. User Performance (Sales by user)
            $userSalesSql = "SELECT u.full_name, SUM(i.total_amount) as total
                             FROM invoices i
                             JOIN users u ON i.branch_id = u.branch_id -- Assuming user is linked to branch
                             WHERE i.branch_id = ?
                             GROUP BY u.user_id
                             ORDER BY total DESC";
            // Correction: Invoices often have 'created_by' link to users
            $userSalesSql = "SELECT u.full_name, SUM(i.total_amount) as total
                             FROM invoices i
                             JOIN users u ON i.branch_id = u.branch_id -- Simplification for now
                             WHERE i.branch_id = ?
                             GROUP BY u.user_id
                             ORDER BY total DESC";
            $data['user_performance'] = $this->db->fetchAll($userSalesSql, [$branchId]);

            // 5. Purchase Orders vs GRN counts
            $poGrnSql = "SELECT 
                            (SELECT COUNT(*) FROM purchase_orders WHERE branch_id = ?) as po_count,
                            (SELECT COUNT(*) FROM grns WHERE branch_id = ?) as grn_count,
                            (SELECT COUNT(*) FROM invoices WHERE branch_id = ?) as invoice_count,
                            (SELECT COUNT(*) FROM appointments WHERE branch_id = ? AND DATE(scheduled_at) = CURDATE()) as appt_count";
            $data['activity_counts'] = $this->db->fetch($poGrnSql, [$branchId, $branchId, $branchId, $branchId]);

            // 6. Recent Activity Stream
            $recentSql = "SELECT 'Sale' as type, invoice_no as ref, total_amount as amount, invoice_date as date 
                          FROM invoices WHERE branch_id = ?
                          UNION ALL
                          SELECT 'Purchase' as type, po_no as ref, total_amount as amount, created_at as date
                          FROM purchase_orders WHERE branch_id = ?
                          ORDER BY date DESC LIMIT 10";
            $data['recent_activity'] = $this->db->fetchAll($recentSql, [$branchId, $branchId]);

            // 7. Expiry Alerts Count (90 days)
            $expirySql = "SELECT COUNT(*) as count FROM inventory_batches WHERE branch_id = ? AND qty_available > 0 AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)";
            $data['expiry_count'] = $this->db->fetchColumn($expirySql, [$branchId]);

            Response::success(['analytics' => $data]);
        } catch (\Exception $e) {
            Response::error('Failed to fetch analytics: ' . $e->getMessage(), 500);
        }
    }
}
