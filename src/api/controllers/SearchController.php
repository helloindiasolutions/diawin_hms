<?php

/**
 * Global Search API Controller
 * Handles search across multiple modules
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use System\Security;

class SearchController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Global search endpoint
     * GET /api/v1/search?q=query&type=all
     */
    public function search(): void
    {
        // PROFESSIONAL FIX: Release session lock immediately for high-performance concurrent searches
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $query = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? 'all';

        if (empty($query) || strlen($query) < 2) {
            Response::error('Query must be at least 2 characters', 400);
            return;
        }

        $query = Security::sanitizeInput($query);
        $results = [];

        try {
            // Search based on type
            if ($type === 'all' || $type === 'patients') {
                $results = array_merge($results, $this->searchPatients($query));
            }

            if ($type === 'all' || $type === 'invoices') {
                $results = array_merge($results, $this->searchInvoices($query));
            }

            if ($type === 'all' || $type === 'products') {
                $results = array_merge($results, $this->searchProducts($query));
            }

            if ($type === 'all' || $type === 'users') {
                $results = array_merge($results, $this->searchUsers($query));
            }

            if ($type === 'all' || $type === 'appointments') {
                $results = array_merge($results, $this->searchAppointments($query));
            }

            // Limit total results
            $results = array_slice($results, 0, 50);

            Response::success([
                'results' => $results,
                'total' => count($results),
                'query' => $query
            ]);
        } catch (\Exception $e) {
            Response::error('Search failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Search patients endpoint (for global search)
     * GET /api/v1/search/patients?q=query&limit=10
     */
    public function searchPatientsEndpoint(): void
    {
        // PROFESSIONAL FIX: Release session lock immediately for high-performance concurrent searches
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $query = $_GET['q'] ?? '';
        $limit = (int) ($_GET['limit'] ?? 10);

        if (empty($query) || strlen($query) < 2) {
            Response::success([]);
            return;
        }

        $query = Security::sanitizeInput($query);
        $limit = min($limit, 50); // Max 50 results

        try {
            $searchTerm = "%{$query}%";

            $patients = $this->db->fetchAll(
                "SELECT 
                    patient_id,
                    CONCAT(first_name, ' ', COALESCE(last_name, '')) as name,
                    first_name,
                    last_name,
                    mrn,
                    primary_mobile as mobile,
                    primary_email as email,
                    TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age
                FROM patients 
                WHERE (CONCAT(first_name, ' ', COALESCE(last_name, '')) LIKE ? 
                    OR mrn LIKE ? 
                    OR primary_mobile LIKE ? 
                    OR primary_email LIKE ?)
                    AND is_active = 1
                ORDER BY 
                    CASE 
                        WHEN CONCAT(first_name, ' ', COALESCE(last_name, '')) LIKE ? THEN 1
                        WHEN mrn LIKE ? THEN 2
                        WHEN primary_mobile LIKE ? THEN 3
                        ELSE 4
                    END,
                    first_name ASC
                LIMIT ?",
                [
                    $searchTerm,
                    $searchTerm,
                    $searchTerm,
                    $searchTerm,
                    $query . '%',
                    $query . '%',
                    $query . '%',
                    $limit
                ]
            );

            Response::success($patients);
        } catch (\Exception $e) {
            Response::error('Patient search failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Search appointments endpoint (for global search)
     * GET /api/v1/search/appointments?q=query&limit=10
     */
    public function searchAppointmentsEndpoint(): void
    {
        // PROFESSIONAL FIX: Release session lock immediately for high-performance concurrent searches
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $query = $_GET['q'] ?? '';
        $limit = (int) ($_GET['limit'] ?? 10);

        if (empty($query) || strlen($query) < 2) {
            Response::success([]);
            return;
        }

        $query = Security::sanitizeInput($query);
        $limit = min($limit, 50); // Max 50 results

        try {
            $searchTerm = "%{$query}%";

            $appointments = $this->db->fetchAll(
                "SELECT 
                    a.appointment_id,
                    DATE_FORMAT(a.scheduled_at, '%Y-%m-%d') as appointment_date,
                    DATE_FORMAT(a.scheduled_at, '%H:%i') as appointment_time,
                    a.status,
                    CONCAT(p.first_name, ' ', COALESCE(p.last_name, '')) as patient_name,
                    p.primary_mobile as patient_mobile,
                    pr.full_name as provider_name,
                    pr.specialization
                FROM appointments a
                LEFT JOIN patients p ON a.patient_id = p.patient_id
                LEFT JOIN providers pr ON a.provider_id = pr.provider_id
                WHERE (CONCAT(p.first_name, ' ', COALESCE(p.last_name, '')) LIKE ? 
                    OR p.primary_mobile LIKE ?
                    OR pr.full_name LIKE ?)
                    AND a.scheduled_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                ORDER BY 
                    CASE 
                        WHEN CONCAT(p.first_name, ' ', COALESCE(p.last_name, '')) LIKE ? THEN 1
                        WHEN p.primary_mobile LIKE ? THEN 2
                        ELSE 3
                    END,
                    a.scheduled_at DESC
                LIMIT ?",
                [
                    $searchTerm,
                    $searchTerm,
                    $searchTerm,
                    $query . '%',
                    $query . '%',
                    $limit
                ]
            );

            // Format appointment data
            foreach ($appointments as &$apt) {
                $apt['appointment_date'] = date('d M Y', strtotime($apt['appointment_date']));
            }

            Response::success($appointments);
        } catch (\Exception $e) {
            Response::error('Appointment search failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Search patients
     */
    private function searchPatients(string $query): array
    {
        $results = [];
        $searchTerm = "%{$query}%";

        $patients = $this->db->fetchAll(
            "SELECT patient_id, CONCAT(first_name, ' ', COALESCE(last_name, '')) as full_name, 
                    mrn, primary_mobile as mobile, primary_email as email 
             FROM patients 
             WHERE CONCAT(first_name, ' ', COALESCE(last_name, '')) LIKE ? 
                OR mrn LIKE ? 
                OR primary_mobile LIKE ? 
                OR primary_email LIKE ?
             LIMIT 10",
            [$searchTerm, $searchTerm, $searchTerm, $searchTerm]
        );

        foreach ($patients as $patient) {
            $results[] = [
                'type' => 'patients',
                'id' => $patient['patient_id'],
                'title' => $patient['full_name'],
                'subtitle' => "MRN: {$patient['mrn']} • {$patient['mobile']}",
                'url' => baseUrl("/patients/{$patient['patient_id']}"),
                'meta' => 'Patient Record'
            ];
        }

        return $results;
    }

    /**
     * Search invoices
     */
    private function searchInvoices(string $query): array
    {
        $results = [];
        $searchTerm = "%{$query}%";

        $invoices = $this->db->fetchAll(
            "SELECT i.invoice_id, i.invoice_no, i.total_amount, i.created_at,
                    CONCAT(p.first_name, ' ', COALESCE(p.last_name, '')) as patient_name
             FROM invoices i
             LEFT JOIN patients p ON i.patient_id = p.patient_id
             WHERE i.invoice_no LIKE ?
                OR CONCAT(p.first_name, ' ', COALESCE(p.last_name, '')) LIKE ?
                OR p.primary_mobile LIKE ?
             ORDER BY i.created_at DESC
             LIMIT 10",
            [$searchTerm, $searchTerm, $searchTerm]
        );

        foreach ($invoices as $invoice) {
            $results[] = [
                'type' => 'invoices',
                'id' => $invoice['invoice_id'],
                'title' => "Invoice #{$invoice['invoice_no']}",
                'subtitle' => ($invoice['patient_name'] ?? 'Walk-in') . " • " . date('d M Y', strtotime($invoice['created_at'])),
                'url' => baseUrl("/billing/invoices"),
                'meta' => '₹' . number_format((float) $invoice['total_amount'], 2)
            ];
        }

        return $results;
    }

    /**
     * Search products
     */
    private function searchProducts(string $query): array
    {
        $results = [];
        $searchTerm = "%{$query}%";

        $products = $this->db->fetchAll(
            "SELECT product_id, product_name, sku, category, mrp
             FROM products
             WHERE product_name LIKE ?
                OR sku LIKE ?
                OR category LIKE ?
             LIMIT 10",
            [$searchTerm, $searchTerm, $searchTerm]
        );

        foreach ($products as $product) {
            $results[] = [
                'type' => 'products',
                'id' => $product['product_id'],
                'title' => $product['product_name'],
                'subtitle' => "SKU: {$product['sku']} • {$product['category']}",
                'url' => baseUrl("/products/{$product['product_id']}"),
                'meta' => '₹' . number_format($product['mrp'], 2)
            ];
        }

        return $results;
    }

    /**
     * Search users
     */
    private function searchUsers(string $query): array
    {
        $results = [];
        $searchTerm = "%{$query}%";

        $users = $this->db->fetchAll(
            "SELECT u.user_id, u.full_name, 
                    u.username, u.mobile, u.email,
                    GROUP_CONCAT(r.name) as roles
             FROM users u
             LEFT JOIN user_roles ur ON u.user_id = ur.user_id
             LEFT JOIN roles r ON ur.role_id = r.role_id
             WHERE u.full_name LIKE ?
                OR u.username LIKE ?
                OR u.mobile LIKE ?
             GROUP BY u.user_id
             LIMIT 10",
            [$searchTerm, $searchTerm, $searchTerm]
        );

        foreach ($users as $user) {
            $results[] = [
                'type' => 'users',
                'id' => $user['user_id'],
                'title' => $user['full_name'],
                'subtitle' => "@{$user['username']} • {$user['mobile']}",
                'url' => baseUrl("/users/{$user['user_id']}/edit"),
                'meta' => $user['roles'] ?? 'Staff'
            ];
        }

        return $results;
    }

    /**
     * Search appointments
     */
    private function searchAppointments(string $query): array
    {
        $results = [];
        $searchTerm = "%{$query}%";

        $appointments = $this->db->fetchAll(
            "SELECT a.appointment_id, a.appointment_no, a.scheduled_at, a.status,
                    CONCAT(p.first_name, ' ', COALESCE(p.last_name, '')) as patient_name,
                    p.primary_mobile as mobile
             FROM appointments a
             LEFT JOIN patients p ON a.patient_id = p.patient_id
             WHERE a.appointment_no LIKE ?
                OR CONCAT(p.first_name, ' ', COALESCE(p.last_name, '')) LIKE ?
                OR p.primary_mobile LIKE ?
             ORDER BY a.scheduled_at DESC
             LIMIT 10",
            [$searchTerm, $searchTerm, $searchTerm]
        );

        foreach ($appointments as $apt) {
            $results[] = [
                'type' => 'appointments',
                'id' => $apt['appointment_id'],
                'title' => "Appointment " . ($apt['appointment_no'] ?: '#' . $apt['appointment_id']),
                'subtitle' => "{$apt['patient_name']} • " . date('d M Y', strtotime($apt['scheduled_at'])),
                'url' => baseUrl("/appointments"),
                'meta' => ucfirst($apt['status'])
            ];
        }

        return $results;
    }
}
