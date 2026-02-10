<?php

namespace App\Api\Controllers;

use Database;

class AnalyticsController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get comprehensive dashboard analytics
     */
    public function getDashboardAnalytics($params)
    {
        $range = $params['range'] ?? 'week';
        $branchId = $_SESSION['user']['branch_id'] ?? null;

        $dateFilter = $this->getDateFilter($range);

        $data = [
            'revenue' => $this->getRevenueMetrics($dateFilter, $branchId),
            'visits' => $this->getVisitMetrics($dateFilter, $branchId),
            'waitTime' => $this->getWaitTimeMetrics($dateFilter, $branchId),
            'satisfaction' => $this->getSatisfactionMetrics($dateFilter, $branchId),
            'topDoctors' => $this->getTopDoctors($dateFilter, $branchId),
            'departmentPerformance' => $this->getDepartmentPerformance($dateFilter, $branchId),
            'demographics' => $this->getPatientDemographics($dateFilter, $branchId),
            'appointmentStatus' => $this->getAppointmentStatus($dateFilter, $branchId),
            'topServices' => $this->getTopServices($dateFilter, $branchId)
        ];

        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * Get revenue metrics
     */
    private function getRevenueMetrics($dateFilter, $branchId)
    {
        $sql = "SELECT 
                    SUM(total_amount) as total,
                    SUM(paid_amount) as collected,
                    SUM(total_amount - paid_amount) as outstanding
                FROM invoices 
                WHERE status != 'cancelled' 
                AND DATE(created_at) {$dateFilter['current']}";
        
        if ($branchId) {
            $sql .= " AND branch_id = ?";
        }


        $stmt = $this->db->prepare($sql);
        if ($branchId) {
            $stmt->execute([$branchId]);
        } else {
            $stmt->execute();
        }
        
        $current = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Get previous period for comparison
        $sqlPrev = str_replace($dateFilter['current'], $dateFilter['previous'], $sql);
        $stmtPrev = $this->db->prepare($sqlPrev);
        if ($branchId) {
            $stmtPrev->execute([$branchId]);
        } else {
            $stmtPrev->execute();
        }
        $previous = $stmtPrev->fetch(\PDO::FETCH_ASSOC);

        $trend = $previous['total'] > 0 
            ? (($current['total'] - $previous['total']) / $previous['total']) * 100 
            : 0;

        return [
            'total' => (float)($current['total'] ?? 0),
            'collected' => (float)($current['collected'] ?? 0),
            'outstanding' => (float)($current['outstanding'] ?? 0),
            'previous' => (float)($previous['total'] ?? 0),
            'trend' => round($trend, 1)
        ];
    }

    /**
     * Get visit metrics
     */
    private function getVisitMetrics($dateFilter, $branchId)
    {
        $sql = "SELECT COUNT(*) as total FROM visits 
                WHERE DATE(visit_date) {$dateFilter['current']}";
        
        if ($branchId) {
            $sql .= " AND branch_id = ?";
        }

        $stmt = $this->db->prepare($sql);
        if ($branchId) {
            $stmt->execute([$branchId]);
        } else {
            $stmt->execute();
        }
        
        $current = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Previous period
        $sqlPrev = str_replace($dateFilter['current'], $dateFilter['previous'], $sql);
        $stmtPrev = $this->db->prepare($sqlPrev);
        if ($branchId) {
            $stmtPrev->execute([$branchId]);
        } else {
            $stmtPrev->execute();
        }
        $previous = $stmtPrev->fetch(\PDO::FETCH_ASSOC);

        $trend = $previous['total'] > 0 
            ? (($current['total'] - $previous['total']) / $previous['total']) * 100 
            : 0;

        return [
            'total' => (int)($current['total'] ?? 0),
            'previous' => (int)($previous['total'] ?? 0),
            'trend' => round($trend, 1)
        ];
    }


    /**
     * Get wait time metrics
     */
    private function getWaitTimeMetrics($dateFilter, $branchId)
    {
        // Mock data for now - implement actual wait time tracking
        return [
            'average' => 18,
            'previous' => 21,
            'trend' => -14.3
        ];
    }

    /**
     * Get satisfaction metrics
     */
    private function getSatisfactionMetrics($dateFilter, $branchId)
    {
        // Mock data for now - implement actual feedback system
        return [
            'score' => 92,
            'previous' => 89,
            'trend' => 3.4
        ];
    }

    /**
     * Get top performing doctors
     */
    private function getTopDoctors($dateFilter, $branchId)
    {
        $sql = "SELECT 
                    u.user_id,
                    CONCAT(u.first_name, ' ', u.last_name) as name,
                    COUNT(DISTINCT v.visit_id) as patients,
                    COALESCE(SUM(i.total_amount), 0) as revenue
                FROM users u
                LEFT JOIN visits v ON u.user_id = v.provider_id 
                    AND DATE(v.visit_date) {$dateFilter['current']}
                LEFT JOIN invoices i ON v.visit_id = i.visit_id
                WHERE u.role = 'doctor'";
        
        if ($branchId) {
            $sql .= " AND u.branch_id = ?";
        }
        
        $sql .= " GROUP BY u.user_id, u.first_name, u.last_name
                  ORDER BY revenue DESC
                  LIMIT 5";

        $stmt = $this->db->prepare($sql);
        if ($branchId) {
            $stmt->execute([$branchId]);
        } else {
            $stmt->execute();
        }

        $doctors = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Add mock ratings for now
        foreach ($doctors as &$doctor) {
            $doctor['rating'] = 4.5 + (rand(0, 5) / 10);
            $doctor['revenue'] = (float)$doctor['revenue'];
            $doctor['patients'] = (int)$doctor['patients'];
        }

        return $doctors;
    }


    /**
     * Get department performance
     */
    private function getDepartmentPerformance($dateFilter, $branchId)
    {
        // Mock data - implement actual department tracking
        return [
            ['name' => 'General Medicine', 'visits' => 450],
            ['name' => 'Pediatrics', 'visits' => 320],
            ['name' => 'Orthopedics', 'visits' => 180],
            ['name' => 'Cardiology', 'visits' => 150],
            ['name' => 'Dermatology', 'visits' => 120]
        ];
    }

    /**
     * Get patient demographics
     */
    private function getPatientDemographics($dateFilter, $branchId)
    {
        $sql = "SELECT 
                    CASE 
                        WHEN age BETWEEN 0 AND 10 THEN '0-10'
                        WHEN age BETWEEN 11 AND 20 THEN '11-20'
                        WHEN age BETWEEN 21 AND 30 THEN '21-30'
                        WHEN age BETWEEN 31 AND 40 THEN '31-40'
                        WHEN age BETWEEN 41 AND 50 THEN '41-50'
                        WHEN age BETWEEN 51 AND 60 THEN '51-60'
                        ELSE '60+'
                    END as age_group,
                    gender,
                    COUNT(*) as count
                FROM patients
                WHERE DATE(created_at) {$dateFilter['current']}";
        
        if ($branchId) {
            $sql .= " AND branch_id = ?";
        }
        
        $sql .= " GROUP BY age_group, gender
                  ORDER BY age_group";

        $stmt = $this->db->prepare($sql);
        if ($branchId) {
            $stmt->execute([$branchId]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get appointment status distribution
     */
    private function getAppointmentStatus($dateFilter, $branchId)
    {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM appointments
                WHERE DATE(scheduled_at) {$dateFilter['current']}";
        
        if ($branchId) {
            $sql .= " AND branch_id = ?";
        }
        
        $sql .= " GROUP BY status";

        $stmt = $this->db->prepare($sql);
        if ($branchId) {
            $stmt->execute([$branchId]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    /**
     * Get top services
     */
    private function getTopServices($dateFilter, $branchId)
    {
        // Mock data - implement actual service tracking
        return [
            ['name' => 'General Checkup', 'count' => 540],
            ['name' => 'Blood Test', 'count' => 470],
            ['name' => 'X-Ray', 'count' => 448],
            ['name' => 'ECG', 'count' => 430],
            ['name' => 'Ultrasound', 'count' => 400]
        ];
    }

    /**
     * Get date filter SQL based on range
     */
    private function getDateFilter($range)
    {
        switch ($range) {
            case 'today':
                return [
                    'current' => '= CURDATE()',
                    'previous' => '= DATE_SUB(CURDATE(), INTERVAL 1 DAY)'
                ];
            case 'week':
                return [
                    'current' => 'BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()',
                    'previous' => 'BETWEEN DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND DATE_SUB(CURDATE(), INTERVAL 7 DAY)'
                ];
            case 'month':
                return [
                    'current' => 'BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE()',
                    'previous' => 'BETWEEN DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND DATE_SUB(CURDATE(), INTERVAL 30 DAY)'
                ];
            case 'quarter':
                return [
                    'current' => 'BETWEEN DATE_SUB(CURDATE(), INTERVAL 90 DAY) AND CURDATE()',
                    'previous' => 'BETWEEN DATE_SUB(CURDATE(), INTERVAL 180 DAY) AND DATE_SUB(CURDATE(), INTERVAL 90 DAY)'
                ];
            case 'year':
                return [
                    'current' => 'BETWEEN DATE_SUB(CURDATE(), INTERVAL 365 DAY) AND CURDATE()',
                    'previous' => 'BETWEEN DATE_SUB(CURDATE(), INTERVAL 730 DAY) AND DATE_SUB(CURDATE(), INTERVAL 365 DAY)'
                ];
            default:
                return [
                    'current' => 'BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()',
                    'previous' => 'BETWEEN DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND DATE_SUB(CURDATE(), INTERVAL 7 DAY)'
                ];
        }
    }
}
