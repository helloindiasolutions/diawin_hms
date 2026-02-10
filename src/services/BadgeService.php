<?php
/**
 * Badge Service
 * 
 * Handles real-time badge counter calculations for the HMS sidebar menu.
 * Provides counts for queue, outstanding bills, expiry alerts, pending estimates,
 * my patients, and available beds.
 * 
 * Requirements: 12.1, 12.2, 12.6
 */

declare(strict_types=1);

namespace App\Services;

use System\Database;

class BadgeService
{
    private Database $db;
    private int $userId;
    private int $branchId;
    
    /**
     * Constructor
     * 
     * @param Database $db Database instance
     * @param int $userId User ID
     * @param int $branchId Branch ID
     */
    public function __construct(Database $db, int $userId, int $branchId)
    {
        $this->db = $db;
        $this->userId = $userId;
        $this->branchId = $branchId;
    }
    
    /**
     * Get all badge counts
     * 
     * Returns an associative array with all badge counter values.
     * Each badge source is calculated based on current database state.
     * 
     * @return array Associative array of badge counts
     */
    public function getAllBadges(): array
    {
        return [
            'queue_count' => $this->getQueueCount(),
            'outstanding_bills' => $this->getOutstandingBillsCount(),
            'expiry_alerts' => $this->getExpiryAlertsCount(),
            'pending_estimates' => $this->getPendingEstimatesCount(),
            'my_patients' => $this->getMyPatientsCount(),
            'available_beds' => $this->getAvailableBedsCount()
        ];
    }
    
    /**
     * Get queue count
     * 
     * Returns the count of patients waiting in the appointment queue
     * for today at the current branch.
     * 
     * @return int Number of waiting patients
     */
    private function getQueueCount(): int
    {
        $sql = "SELECT COUNT(*) FROM appointment_queue 
                WHERE status = 'waiting' 
                AND DATE(date) = CURDATE() 
                AND branch_id = ?";
        
        try {
            return (int) $this->db->fetchColumn($sql, [$this->branchId]);
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get outstanding bills count
     * 
     * Returns the count of unpaid or partially paid invoices
     * at the current branch.
     * 
     * @return int Number of outstanding invoices
     */
    private function getOutstandingBillsCount(): int
    {
        $sql = "SELECT COUNT(*) FROM invoices 
                WHERE status IN ('posted', 'partial') 
                AND branch_id = ?";
        
        try {
            return (int) $this->db->fetchColumn($sql, [$this->branchId]);
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get expiry alerts count
     * 
     * Returns the count of medicine batches expiring within 30 days
     * that still have available quantity at the current branch.
     * 
     * @return int Number of expiring medicine batches
     */
    private function getExpiryAlertsCount(): int
    {
        $sql = "SELECT COUNT(*) FROM inventory_batches 
                WHERE expiry_date <= DATE_ADD(NOW(), INTERVAL 30 DAY) 
                AND qty_available > 0
                AND branch_id = ?";
        
        try {
            return (int) $this->db->fetchColumn($sql, [$this->branchId]);
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get pending estimates count
     * 
     * Returns the count of draft estimates that have not been
     * converted to invoices at the current branch.
     * 
     * @return int Number of pending estimates
     */
    private function getPendingEstimatesCount(): int
    {
        $sql = "SELECT COUNT(*) FROM invoices 
                WHERE invoice_type = 'estimate' 
                AND status = 'draft' 
                AND branch_id = ?";
        
        try {
            return (int) $this->db->fetchColumn($sql, [$this->branchId]);
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get my patients count
     * 
     * Returns the count of open visits assigned to the current user
     * (doctor) for today. This is user-specific, not branch-specific.
     * 
     * @return int Number of doctor's open visits today
     */
    private function getMyPatientsCount(): int
    {
        $sql = "SELECT COUNT(*) FROM visits 
                WHERE primary_provider_id = ? 
                AND visit_status = 'open' 
                AND DATE(visit_date) = CURDATE()";
        
        try {
            return (int) $this->db->fetchColumn($sql, [$this->userId]);
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get available beds count
     * 
     * Returns the count of beds with 'available' status
     * at the current branch.
     * 
     * @return int Number of available beds
     */
    private function getAvailableBedsCount(): int
    {
        $sql = "SELECT COUNT(*) FROM beds 
                WHERE status = 'available' 
                AND branch_id = ?";
        
        try {
            return (int) $this->db->fetchColumn($sql, [$this->branchId]);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
