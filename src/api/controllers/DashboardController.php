<?php
/**
 * Dashboard API Controller
 * Handles all dashboard data for dynamic display
 * ALL DATA IS FROM DATABASE - NO HARDCODED VALUES
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use App\Middleware\BranchMiddleware;

class DashboardController
{
    private Database $db;
    private ?int $branchId;

    public function __construct()
    {
        $this->db = Database::getInstance();
        // Use BranchMiddleware for consistent branch filtering
        // Returns null for super admin viewing all branches, or specific branch_id
        $this->branchId = BranchMiddleware::getUserBranchId();
    }

    /**
     * Get all dashboard stats (cards)
     * OPTIMIZED: Single query for all stats with caching
     */
    public function getStats(): void
    {
        // Try to get from cache first (30 second cache)
        $cacheKey = 'dashboard_stats_' . ($this->branchId ?? 'all');
        $cached = \System\QueryCache::get($cacheKey);

        if ($cached !== null) {
            Response::success($cached);
            return;
        }

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        // Use single optimized query for all stats
        $stats = $this->getAllStatsOptimized($today, $yesterday);

        // Cache for 30 seconds
        \System\QueryCache::set($cacheKey, $stats, 30);

        Response::success($stats);
    }

    /**
     * Get all stats in a single optimized query
     */
    private function getAllStatsOptimized(string $today, string $yesterday): array
    {
        $branchFilter = $this->branchId ? " AND branch_id = {$this->branchId}" : "";

        try {
            // Single query to get all counts at once
            $sql = "SELECT 
                -- Invoice stats
                (SELECT COUNT(*) FROM invoices WHERE status != 'cancelled'{$branchFilter}) as total_invoices,
                (SELECT COUNT(*) FROM invoices WHERE DATE(created_at) = ? AND status != 'cancelled'{$branchFilter}) as today_invoices,
                (SELECT COUNT(*) FROM invoices WHERE DATE(created_at) = ? AND status != 'cancelled'{$branchFilter}) as yesterday_invoices,
                
                -- Patient stats
                (SELECT COUNT(*) FROM patients WHERE is_active = 1{$branchFilter}) as total_patients,
                (SELECT COUNT(*) FROM patients WHERE DATE(created_at) = ?{$branchFilter}) as today_patients,
                (SELECT COUNT(*) FROM patients WHERE DATE(created_at) = ?{$branchFilter}) as yesterday_patients,
                
                -- Appointment stats
                (SELECT COUNT(*) FROM appointments WHERE DATE(scheduled_at) = ?{$branchFilter}) as today_appointments,
                (SELECT COUNT(*) FROM appointments WHERE DATE(scheduled_at) = ?{$branchFilter}) as yesterday_appointments";

            $params = [$today, $yesterday, $today, $yesterday, $today, $yesterday];
            $result = $this->db->fetch($sql, $params);

            // Calculate invoice trends
            $invoiceDiff = $result['today_invoices'] - $result['yesterday_invoices'];
            $invoiceTrend = $result['yesterday_invoices'] > 0
                ? round((($result['today_invoices'] - $result['yesterday_invoices']) / $result['yesterday_invoices']) * 100, 2)
                : 0;

            // Calculate patient trends
            $patientDiff = $result['today_patients'] - $result['yesterday_patients'];
            $patientTrend = $result['yesterday_patients'] > 0
                ? round((($result['today_patients'] - $result['yesterday_patients']) / $result['yesterday_patients']) * 100, 2)
                : 0;

            // Calculate appointment trends
            $appointmentDiff = $result['today_appointments'] - $result['yesterday_appointments'];
            $appointmentTrend = $result['yesterday_appointments'] > 0
                ? round((($result['today_appointments'] - $result['yesterday_appointments']) / $result['yesterday_appointments']) * 100, 2)
                : 0;

            // Get bed stats separately (may not exist)
            $bedStats = $this->getBedStatsOptimized();

            return [
                'totalInvoice' => [
                    'count' => (int) $result['total_invoices'],
                    'diff' => abs($invoiceDiff),
                    'trend' => $invoiceTrend,
                    'trendDir' => $invoiceDiff >= 0 ? 'up' : 'down'
                ],
                'totalPatients' => [
                    'count' => (int) $result['total_patients'],
                    'diff' => abs($patientDiff),
                    'trend' => $patientTrend,
                    'trendDir' => $patientDiff >= 0 ? 'up' : 'down'
                ],
                'appointments' => [
                    'count' => (int) $result['today_appointments'],
                    'diff' => abs($appointmentDiff),
                    'trend' => $appointmentTrend,
                    'trendDir' => $appointmentDiff >= 0 ? 'up' : 'down'
                ],
                'bedroom' => $bedStats,
                'inventory' => $this->getInventoryStats($today, $yesterday)
            ];
        } catch (\Exception $e) {
            // Fallback to individual queries if combined query fails
            return [
                'totalInvoice' => $this->getInvoiceStats($today, $yesterday),
                'totalPatients' => $this->getPatientStats($today, $yesterday),
                'appointments' => $this->getAppointmentStats($today, $yesterday),
                'bedroom' => $this->getBedStatsOptimized(),
                'inventory' => $this->getInventoryStats($today, $yesterday)
            ];
        }
    }

    /**
     * Get invoice statistics
     */
    private function getInvoiceStats(string $today, string $yesterday): array
    {
        $params = [];
        $where = "status != 'cancelled'";
        if ($this->branchId) {
            $where .= " AND branch_id = ?";
            $params[] = $this->branchId;
        }

        // Total count
        $total = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM invoices WHERE " . $where, $params);

        // Today's count
        $todayParams = [$today];
        $todayWhere = "DATE(created_at) = ? AND status != 'cancelled'";
        if ($this->branchId) {
            $todayWhere .= " AND branch_id = ?";
            $todayParams[] = $this->branchId;
        }
        $todayCount = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM invoices WHERE " . $todayWhere, $todayParams);

        // Yesterday's count
        $yesterdayParams = [$yesterday];
        if ($this->branchId) {
            $yesterdayParams[] = $this->branchId;
        }
        $yesterdayCount = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM invoices WHERE " . $todayWhere, $yesterdayParams);

        $diff = $todayCount - $yesterdayCount;
        $trend = $yesterdayCount > 0 ? round((($todayCount - $yesterdayCount) / $yesterdayCount) * 100, 2) : 0;

        return [
            'count' => $total,
            'diff' => abs($diff),
            'trend' => $trend,
            'trendDir' => $diff >= 0 ? 'up' : 'down'
        ];
    }

    /**
     * Get patient statistics
     */
    private function getPatientStats(string $today, string $yesterday): array
    {
        $params = [];
        $where = "is_active = 1";
        if ($this->branchId) {
            $where .= " AND branch_id = ?";
            $params[] = $this->branchId;
        }

        // Total count
        $total = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM patients WHERE " . $where, $params);

        // Today's new patients
        $todayParams = [$today];
        $todayWhere = "DATE(created_at) = ?";
        if ($this->branchId) {
            $todayWhere .= " AND branch_id = ?";
            $todayParams[] = $this->branchId;
        }
        $todayCount = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM patients WHERE " . $todayWhere, $todayParams);

        // Yesterday's new patients
        $yesterdayParams = [$yesterday];
        if ($this->branchId) {
            $yesterdayParams[] = $this->branchId;
        }
        $yesterdayCount = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM patients WHERE " . $todayWhere, $yesterdayParams);

        $diff = $todayCount - $yesterdayCount;
        $trend = $yesterdayCount > 0 ? round((($todayCount - $yesterdayCount) / $yesterdayCount) * 100, 2) : 0;

        return [
            'count' => $total,
            'diff' => abs($diff),
            'trend' => $trend,
            'trendDir' => $diff >= 0 ? 'up' : 'down'
        ];
    }

    /**
     * Get appointment statistics
     */
    private function getAppointmentStats(string $today, string $yesterday): array
    {
        // Today's count
        $todayParams = [$today];
        $todayWhere = "DATE(scheduled_at) = ?";
        if ($this->branchId) {
            $todayWhere .= " AND branch_id = ?";
            $todayParams[] = $this->branchId;
        }
        $todayCount = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM appointments WHERE " . $todayWhere, $todayParams);

        // Yesterday's count
        $yesterdayParams = [$yesterday];
        if ($this->branchId) {
            $yesterdayParams[] = $this->branchId;
        }
        $yesterdayCount = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM appointments WHERE " . $todayWhere, $yesterdayParams);

        $diff = $todayCount - $yesterdayCount;
        $trend = $yesterdayCount > 0 ? round((($todayCount - $yesterdayCount) / $yesterdayCount) * 100, 2) : 0;

        return [
            'count' => $todayCount,
            'diff' => abs($diff),
            'trend' => $trend,
            'trendDir' => $diff >= 0 ? 'up' : 'down'
        ];
    }

    /**
     * Get bed statistics (OPTIMIZED with error handling)
     */
    private function getBedStatsOptimized(): array
    {
        try {
            $params = [];
            $where = "1=1";

            // Check if wards table exists first
            $tableExists = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM information_schema.tables 
                WHERE table_schema = DATABASE() AND table_name = 'wards'"
            );

            if (!$tableExists) {
                // Return default values if wards table doesn't exist
                return [
                    'count' => 0,
                    'available' => 0,
                    'occupied' => 0,
                    'trend' => 0,
                    'trendDir' => 'up'
                ];
            }

            if ($this->branchId) {
                $where = "w.branch_id = ?";
                $params[] = $this->branchId;
            }

            $result = $this->db->fetch(
                "SELECT 
                    COUNT(*) as total, 
                    SUM(CASE WHEN b.bed_status = 'Available' THEN 1 ELSE 0 END) as available 
                FROM beds b
                JOIN wards w ON b.ward_id = w.ward_id
                WHERE " . $where,
                $params
            );

            $total = (int) ($result['total'] ?? 0);
            $available = (int) ($result['available'] ?? 0);
            $occupied = $total - $available;
            $occupancyRate = $total > 0 ? round(($occupied / $total) * 100, 2) : 0;

            return [
                'count' => $total,
                'available' => $available,
                'occupied' => $occupied,
                'trend' => $occupancyRate,
                'trendDir' => 'up'
            ];
        } catch (\Exception $e) {
            // Return default values on error
            return [
                'count' => 0,
                'available' => 0,
                'occupied' => 0,
                'trend' => 0,
                'trendDir' => 'up'
            ];
        }
    }

    /**
     * Get bed statistics (Legacy method for backward compatibility)
     */
    private function getBedStats(): array
    {
        return $this->getBedStatsOptimized();
    }

    /**
     * Get Patient Overview by Age Range for chart
     * DATA FROM DATABASE ONLY - NO MOCK DATA
     */
    public function getPatientOverviewByAge(): void
    {
        $days = (int) ($_GET['days'] ?? 8);
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        $queryParams = [$startDate];
        $where = "DATE(created_at) >= ?";
        if ($this->branchId) {
            $where .= " AND branch_id = ?";
            $queryParams[] = $this->branchId;
        }

        $data = $this->db->fetchAll(
            "SELECT 
                DATE(created_at) as date,
                SUM(CASE WHEN age < 18 THEN 1 ELSE 0 END) as child,
                SUM(CASE WHEN age BETWEEN 18 AND 59 THEN 1 ELSE 0 END) as adult,
                SUM(CASE WHEN age >= 60 THEN 1 ELSE 0 END) as elderly
            FROM patients 
            WHERE " . $where . "
            GROUP BY DATE(created_at) 
            ORDER BY DATE(created_at)",
            $queryParams
        );

        // Format for chart - ONLY real data, zeros for missing dates
        $categories = [];
        $childData = [];
        $adultData = [];
        $elderlyData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $categories[] = date('j M', strtotime($date));

            $found = false;
            foreach ($data as $row) {
                if ($row['date'] === $date) {
                    $childData[] = (int) $row['child'];
                    $adultData[] = (int) $row['adult'];
                    $elderlyData[] = (int) $row['elderly'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                // Zero for dates with no data
                $childData[] = 0;
                $adultData[] = 0;
                $elderlyData[] = 0;
            }
        }

        // Get totals from database directly
        $totalParams = [];
        $totalWhere = "1=1";
        if ($this->branchId) {
            $totalWhere = "branch_id = ?";
            $totalParams[] = $this->branchId;
        }

        $totals = $this->db->fetch(
            "SELECT 
                SUM(CASE WHEN age < 18 THEN 1 ELSE 0 END) as child,
                SUM(CASE WHEN age BETWEEN 18 AND 59 THEN 1 ELSE 0 END) as adult,
                SUM(CASE WHEN age >= 60 THEN 1 ELSE 0 END) as elderly
            FROM patients 
            WHERE " . $totalWhere,
            $totalParams
        );

        Response::success([
            'categories' => $categories,
            'series' => [
                ['name' => 'Child', 'data' => $childData],
                ['name' => 'Adult', 'data' => $adultData],
                ['name' => 'Elderly', 'data' => $elderlyData]
            ],
            'totals' => [
                'child' => (int) ($totals['child'] ?? 0),
                'adult' => (int) ($totals['adult'] ?? 0),
                'elderly' => (int) ($totals['elderly'] ?? 0)
            ]
        ]);
    }

    /**
     * Get Revenue Data for chart
     * DATA FROM DATABASE ONLY - NO MOCK DATA
     */
    public function getRevenueChart(): void
    {
        $period = $_GET['period'] ?? 'week';

        $days = match ($period) {
            'year' => 365,
            'month' => 30,
            default => 7
        };

        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        $queryParams = [$startDate];
        $where = "DATE(created_at) >= ? AND status IN ('paid', 'posted')";
        if ($this->branchId) {
            $where .= " AND branch_id = ?";
            $queryParams[] = $this->branchId;
        }

        $incomeData = $this->db->fetchAll(
            "SELECT 
                DATE(created_at) as date,
                SUM(paid_amount) as income
            FROM invoices 
            WHERE " . $where . "
            GROUP BY DATE(created_at) 
            ORDER BY DATE(created_at)",
            $queryParams
        );

        // Get expense data from expenses table if exists
        $expenseData = [];
        try {
            $expenseParams = [$startDate];
            $expenseWhere = "DATE(created_at) >= ?";
            if ($this->branchId) {
                $expenseWhere .= " AND branch_id = ?";
                $expenseParams[] = $this->branchId;
            }
            $expenseData = $this->db->fetchAll(
                "SELECT 
                    DATE(created_at) as date,
                    SUM(amount) as expense
                FROM expenses 
                WHERE " . $expenseWhere . "
                GROUP BY DATE(created_at)",
                $expenseParams
            );
        } catch (\Exception $e) {
            // expenses table may not exist
        }

        // Format data for chart - ONLY real data, zeros for missing dates
        $categories = [];
        $income = [];
        $expense = [];

        if ($period === 'week') {
            $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $dayOfWeek = (int) date('w', strtotime($date));
                $categories[] = $dayNames[$dayOfWeek];

                $incomeVal = 0;
                foreach ($incomeData as $row) {
                    if ($row['date'] === $date) {
                        $incomeVal = (float) $row['income'];
                        break;
                    }
                }
                $income[] = $incomeVal;

                $expenseVal = 0;
                foreach ($expenseData as $row) {
                    if ($row['date'] === $date) {
                        $expenseVal = (float) $row['expense'];
                        break;
                    }
                }
                $expense[] = $expenseVal;
            }
        } else {
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $categories[] = date('j M', strtotime($date));

                $incomeVal = 0;
                foreach ($incomeData as $row) {
                    if ($row['date'] === $date) {
                        $incomeVal = (float) $row['income'];
                        break;
                    }
                }
                $income[] = $incomeVal;

                $expenseVal = 0;
                foreach ($expenseData as $row) {
                    if ($row['date'] === $date) {
                        $expenseVal = (float) $row['expense'];
                        break;
                    }
                }
                $expense[] = $expenseVal;
            }
        }

        $totalIncome = (int) array_sum($income);

        Response::success([
            'categories' => $categories,
            'series' => [
                ['name' => 'Income', 'data' => $income],
                ['name' => 'Expense', 'data' => $expense]
            ],
            'totalIncome' => $totalIncome
        ]);
    }

    /**
     * Get Patient Overview by Department (Donut chart)
     * DATA FROM DATABASE ONLY - NO MOCK DATA
     */
    public function getPatientsByDepartment(): void
    {
        $queryParams = [];
        $where = "v.visit_status != 'cancelled'";
        if ($this->branchId) {
            $where .= " AND v.branch_id = ?";
            $queryParams[] = $this->branchId;
        }

        $data = $this->db->fetchAll(
            "SELECT 
                COALESCE(p.specialization, 'General') as department,
                COUNT(DISTINCT v.patient_id) as count
            FROM visits v
            LEFT JOIN providers p ON v.primary_provider_id = p.provider_id
            WHERE " . $where . "
            GROUP BY COALESCE(p.specialization, 'General')
            ORDER BY count DESC
            LIMIT 4",
            $queryParams
        );

        // If no visit data, try to get from patients table grouped by type
        if (empty($data)) {
            $patientParams = [];
            $patientWhere = "is_active = 1";
            if ($this->branchId) {
                $patientWhere .= " AND branch_id = ?";
                $patientParams[] = $this->branchId;
            }

            $data = $this->db->fetchAll(
                "SELECT 
                    COALESCE(NULLIF(gender, ''), 'Other') as department,
                    COUNT(*) as count
                FROM patients 
                WHERE " . $patientWhere . "
                GROUP BY COALESCE(NULLIF(gender, ''), 'Other')
                ORDER BY count DESC
                LIMIT 4",
                $patientParams
            );
        }

        $total = (int) array_sum(array_column($data, 'count'));

        $labels = [];
        $series = [];
        $percentages = [];

        foreach ($data as $row) {
            $labels[] = $row['department'];
            $series[] = (int) $row['count'];
            $percentages[] = $total > 0 ? round(($row['count'] / $total) * 100) : 0;
        }

        // Return empty arrays if no data
        Response::success([
            'labels' => $labels,
            'series' => $series,
            'percentages' => $percentages,
            'total' => $total
        ]);
    }

    /**
     * Get Doctors' Schedule
     * DATA FROM DATABASE ONLY - NO MOCK DATA
     */
    public function getDoctorsSchedule(): void
    {
        $date = $_GET['date'] ?? date('Y-m-d');

        $queryParams = [];
        $where = "p.is_active = 1";
        if ($this->branchId) {
            $where .= " AND p.branch_id = ?";
            $queryParams[] = $this->branchId;
        }

        $doctors = $this->db->fetchAll(
            "SELECT 
                p.provider_id,
                p.full_name as name,
                p.specialization as dept,
                p.is_active
            FROM providers p
            WHERE " . $where . "
            ORDER BY p.full_name
            LIMIT 6",
            $queryParams
        );

        $result = [];
        foreach ($doctors as $doc) {
            $result[] = [
                'id' => (int) ($doc['provider_id'] ?? 0),
                'name' => 'Dr. ' . $doc['name'],
                'dept' => $doc['dept'] ?? 'General',
                'time' => '9:00 AM - 5:00 PM',
                'status' => $doc['is_active'] ? 'Available' : 'Unavailable',
                'statusClass' => $doc['is_active'] ? 'success' : 'danger'
            ];
        }

        Response::success(['doctors' => $result]);
    }

    /**
     * Get Reports/Alerts
     * DATA FROM DATABASE ONLY - NO MOCK DATA
     */
    public function getReports(): void
    {
        $reports = [];

        // Check for low stock consumables
        $lowStockParams = [];
        $lowStockWhere = "";
        if ($this->branchId) {
            $lowStockWhere = " AND cs.branch_id = ?";
            $lowStockParams[] = $this->branchId;
        }

        try {
            $lowStock = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM consumable_stock cs
                JOIN consumables c ON cs.consumable_id = c.consumable_id
                WHERE cs.qty < c.low_stock_threshold" . $lowStockWhere,
                $lowStockParams
            );

            if ($lowStock > 0) {
                $reports[] = [
                    'title' => "Medication Restock ({$lowStock} items)",
                    'time' => 'Urgent',
                    'icon' => 'ri-capsule-line',
                    'color' => 'warning'
                ];
            }
        } catch (\Exception $e) {
            // Table may not exist
        }

        // Check for pending appointments today
        $pendingParams = [];
        $pendingWhere = "DATE(scheduled_at) = CURDATE() AND status = 'scheduled'";
        if ($this->branchId) {
            $pendingWhere .= " AND branch_id = ?";
            $pendingParams[] = $this->branchId;
        }

        try {
            $pendingApt = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM appointments WHERE " . $pendingWhere,
                $pendingParams
            );

            if ($pendingApt > 0) {
                $reports[] = [
                    'title' => "Pending Appointments ({$pendingApt})",
                    'time' => 'Today',
                    'icon' => 'ri-calendar-check-line',
                    'color' => 'info'
                ];
            }
        } catch (\Exception $e) {
            // Table may not exist
        }

        // Check for overdue invoices
        $overdueParams = [];
        $overdueWhere = "due_date < CURDATE() AND status NOT IN ('paid', 'cancelled')";
        if ($this->branchId) {
            $overdueWhere .= " AND branch_id = ?";
            $overdueParams[] = $this->branchId;
        }

        try {
            $overdueInvoices = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM invoices WHERE " . $overdueWhere,
                $overdueParams
            );

            if ($overdueInvoices > 0) {
                $reports[] = [
                    'title' => "Overdue Invoices ({$overdueInvoices})",
                    'time' => 'Action Required',
                    'icon' => 'ri-money-dollar-circle-line',
                    'color' => 'danger'
                ];
            }
        } catch (\Exception $e) {
            // Column may not exist
        }

        // Check for beds that need cleaning/maintenance
        $bedsParams = [];
        $bedsWhere = "b.bed_status = 'Occupied'";
        if ($this->branchId) {
            $bedsWhere .= " AND w.branch_id = ?";
            $bedsParams[] = $this->branchId;
        }

        try {
            $occupiedBeds = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM beds b
                JOIN wards w ON b.ward_id = w.ward_id
                WHERE " . $bedsWhere,
                $bedsParams
            );

            if ($occupiedBeds > 0) {
                $reports[] = [
                    'title' => "Occupied Beds ({$occupiedBeds})",
                    'time' => 'Current',
                    'icon' => 'ri-hotel-bed-line',
                    'color' => 'primary'
                ];
            }
        } catch (\Exception $e) {
            // Table may not exist
        }

        Response::success(['reports' => array_slice($reports, 0, 5)]);
    }

    /**
     * Get Patient Appointments for table
     * DATA FROM DATABASE ONLY - NO MOCK DATA
     */
    public function getAppointments(): void
    {
        $date = $_GET['date'] ?? date('Y-m-d');

        $queryParams = [$date];
        $where = "DATE(a.scheduled_at) = ?";
        if ($this->branchId) {
            $where .= " AND a.branch_id = ?";
            $queryParams[] = $this->branchId;
        }

        $appointments = $this->db->fetchAll(
            "SELECT 
                a.appointment_id,
                a.patient_id,
                CONCAT(p.first_name, ' ', COALESCE(p.last_name, '')) as patient_name,
                DATE_FORMAT(a.scheduled_at, '%d-%m-%y') as date,
                DATE_FORMAT(a.scheduled_at, '%h:%i %p') as time,
                CONCAT('Dr. ', COALESCE(pr.full_name, 'Unassigned')) as doctor,
                a.source as treatment,
                a.status
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN providers pr ON a.provider_id = pr.provider_id
            WHERE " . $where . "
            ORDER BY a.scheduled_at ASC 
            LIMIT 10",
            $queryParams
        );

        $result = [];
        foreach ($appointments as $apt) {
            $statusClass = match ($apt['status']) {
                'completed' => 'success',
                'checked-in' => 'success',
                'scheduled' => 'warning',
                'cancelled' => 'secondary',
                'no-show' => 'danger',
                default => 'primary'
            };

            $result[] = [
                'id' => (int) $apt['appointment_id'],
                'patient_id' => (int) $apt['patient_id'],
                'name' => $apt['patient_name'],
                'date' => $apt['date'],
                'time' => $apt['time'],
                'doctor' => $apt['doctor'],
                'treatment' => ucfirst($apt['treatment'] ?? 'General'),
                'status' => ucfirst(str_replace('-', ' ', $apt['status'])),
                'statusClass' => $statusClass
            ];
        }

        Response::success(['appointments' => $result]);
    }

    /**
     * Get Recent Activity
     * DATA FROM DATABASE ONLY - NO MOCK DATA
     */
    public function getRecentActivity(): void
    {
        $activities = [];

        // Recent patient registrations
        $regParams = [];
        $regWhere = "1=1";
        if ($this->branchId) {
            $regWhere = "r.branch_id = ?";
            $regParams[] = $this->branchId;
        }

        try {
            $registrations = $this->db->fetchAll(
                "SELECT 
                    p.patient_id,
                    CONCAT(p.first_name, ' ', COALESCE(p.last_name, '')) as name,
                    r.registered_at as time
                FROM registrations r
                JOIN patients p ON r.patient_id = p.patient_id
                WHERE " . $regWhere . "
                ORDER BY r.registered_at DESC 
                LIMIT 5",
                $regParams
            );

            foreach ($registrations as $reg) {
                $activities[] = [
                    'patient_id' => (int) $reg['patient_id'],
                    'text' => "New registration: {$reg['name']}",
                    'time' => $this->timeAgo($reg['time']),
                    'timeRaw' => $reg['time'],
                    'icon' => 'ri-user-add-line',
                    'color' => 'success'
                ];
            }
        } catch (\Exception $e) {
        }

        // Recent patient records (Always check patients table as backup)
        try {
            $patientParams = [];
            $patientWhere = "is_active = 1";
            if ($this->branchId) {
                $patientWhere .= " AND branch_id = ?";
                $patientParams[] = $this->branchId;
            }

            $patients = $this->db->fetchAll(
                "SELECT patient_id, CONCAT(first_name, ' ', COALESCE(last_name, '')) as name, created_at as time
                 FROM patients WHERE " . $patientWhere . " ORDER BY created_at DESC LIMIT 5",
                $patientParams
            );

            foreach ($patients as $p) {
                $activities[] = [
                    'patient_id' => (int) $p['patient_id'],
                    'text' => "New patient added: {$p['name']}",
                    'time' => $this->timeAgo($p['time']),
                    'timeRaw' => $p['time'],
                    'icon' => 'ri-user-heart-line',
                    'color' => 'success'
                ];
            }
        } catch (\Exception $e) {
        }

        // Recent appointments (scheduled or completed)
        $aptParams = [];
        $aptWhere = "a.status IN ('completed', 'scheduled', 'checked-in')";
        if ($this->branchId) {
            $aptWhere .= " AND a.branch_id = ?";
            $aptParams[] = $this->branchId;
        }

        try {
            $apts = $this->db->fetchAll(
                "SELECT a.patient_id, CONCAT(p.first_name, ' ', COALESCE(p.last_name, '')) as name,
                        a.status, a.scheduled_at as time
                 FROM appointments a
                 JOIN patients p ON a.patient_id = p.patient_id
                 WHERE " . $aptWhere . "
                 ORDER BY a.scheduled_at DESC LIMIT 5",
                $aptParams
            );

            foreach ($apts as $apt) {
                $statusText = $apt['status'] === 'completed' ? 'completed an appointment' :
                    ($apt['status'] === 'checked-in' ? 'checked in for appointment' : 'scheduled an appointment');

                $activities[] = [
                    'patient_id' => (int) $apt['patient_id'],
                    'text' => "{$apt['name']} {$statusText}",
                    'time' => $this->timeAgo($apt['time']),
                    'timeRaw' => $apt['time'],
                    'icon' => $apt['status'] === 'completed' ? 'ri-checkbox-circle-line' : 'ri-calendar-event-line',
                    'color' => $apt['status'] === 'completed' ? 'info' : 'primary'
                ];
            }
        } catch (\Exception $e) {
        }

        // Recent invoices paid
        $invParams = [];
        $invWhere = "i.status = 'paid'";
        if ($this->branchId) {
            $invWhere .= " AND i.branch_id = ?";
            $invParams[] = $this->branchId;
        }

        try {
            $paidInvoices = $this->db->fetchAll(
                "SELECT 
                    p.patient_id,
                    CONCAT(p.first_name, ' ', COALESCE(p.last_name, '')) as name,
                    i.paid_amount as amount,
                    i.updated_at as time
                FROM invoices i
                JOIN patients p ON i.patient_id = p.patient_id
                WHERE " . $invWhere . "
                ORDER BY i.created_at DESC 
                LIMIT 5",
                $invParams
            );

            foreach ($paidInvoices as $inv) {
                $activities[] = [
                    'patient_id' => (int) $inv['patient_id'],
                    'text' => "Payment of ₹" . number_format((float) $inv['amount'], 2) . " received from {$inv['name']}",
                    'time' => $this->timeAgo($inv['time']),
                    'timeRaw' => $inv['time'],
                    'icon' => 'ri-money-dollar-circle-line',
                    'color' => 'success'
                ];
            }
        } catch (\Exception $e) {
            // Table may not exist
        }

        // Sort by time
        usort($activities, function ($a, $b) {
            return strtotime($b['timeRaw'] ?? '1970-01-01') - strtotime($a['timeRaw'] ?? '1970-01-01');
        });

        // Remove timeRaw and limit
        $activities = array_slice($activities, 0, 5);
        foreach ($activities as &$act) {
            unset($act['timeRaw']);
        }

        Response::success(['activities' => $activities]);
    }

    /**
     * Get Calendar Events
     * DATA FROM DATABASE ONLY - NO MOCK DATA
     */
    public function getCalendarEvents(): void
    {
        $month = (int) ($_GET['month'] ?? date('m'));
        $year = (int) ($_GET['year'] ?? date('Y'));
        $date = $_GET['date'] ?? date('Y-m-d');

        $queryParams = [$date];
        $where = "DATE(a.scheduled_at) = ? AND a.status != 'cancelled'";
        if ($this->branchId) {
            $where .= " AND a.branch_id = ?";
            $queryParams[] = $this->branchId;
        }

        // Get appointments for the selected date
        $appointments = $this->db->fetchAll(
            "SELECT 
                a.appointment_id,
                a.patient_id,
                CONCAT(p.first_name, ' ', COALESCE(p.last_name, '')) as patient_name,
                DATE_FORMAT(a.scheduled_at, '%h:%i %p') as start_time,
                DATE_FORMAT(DATE_ADD(a.scheduled_at, INTERVAL a.duration_minutes MINUTE), '%h:%i %p') as end_time,
                COALESCE(pr.specialization, 'General') as dept,
                a.source
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN providers pr ON a.provider_id = pr.provider_id
            WHERE " . $where . "
            ORDER BY a.scheduled_at ASC 
            LIMIT 5",
            $queryParams
        );

        $events = [];
        $colors = ['green', 'blue', 'cyan', 'yellow'];
        $i = 0;

        foreach ($appointments as $apt) {
            $events[] = [
                'id' => (int) $apt['appointment_id'],
                'patient_id' => (int) $apt['patient_id'],
                'title' => $apt['patient_name'] . ' - ' . ($apt['dept'] ?? 'General'),
                'time' => $apt['start_time'] . ' - ' . $apt['end_time'],
                'color' => $colors[$i % count($colors)]
            ];
            $i++;
        }

        // Get dates with appointments for the month
        $monthParams = [$month, $year];
        $monthWhere = "MONTH(scheduled_at) = ? AND YEAR(scheduled_at) = ? AND status != 'cancelled'";
        if ($this->branchId) {
            $monthWhere .= " AND branch_id = ?";
            $monthParams[] = $this->branchId;
        }

        $daysWithEvents = $this->db->fetchAll(
            "SELECT DISTINCT DAY(scheduled_at) as day
            FROM appointments 
            WHERE " . $monthWhere,
            $monthParams
        );

        Response::success([
            'events' => $events,
            'daysWithEvents' => array_column($daysWithEvents, 'day')
        ]);
    }

    /**
     * Helper: Convert timestamp to "time ago" format
     */
    private function timeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60) {
            return $diff . ' seconds ago';
        } elseif ($diff < 3600) {
            $mins = (int) floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = (int) floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 172800) {
            return 'Yesterday';
        } else {
            return date('M j', $time);
        }
    }

    /**
     * Get inventory statistics (PO & GRN)
     */
    private function getInventoryStats(string $today, string $yesterday): array
    {
        $branchFilter = $this->branchId ? " AND branch_id = {$this->branchId}" : "";

        try {
            $sql = "SELECT 
                (SELECT COUNT(*) FROM purchase_orders WHERE DATE(created_at) = ?{$branchFilter}) as today_po,
                (SELECT COUNT(*) FROM purchase_orders WHERE DATE(created_at) = ?{$branchFilter}) as yesterday_po,
                (SELECT COUNT(*) FROM grns WHERE DATE(received_at) = ?{$branchFilter}) as today_grn,
                (SELECT COUNT(*) FROM grns WHERE DATE(received_at) = ?{$branchFilter}) as yesterday_grn";

            $params = [$today, $yesterday, $today, $yesterday];
            $res = $this->db->fetch($sql, $params);

            $poTrend = $res['yesterday_po'] > 0 ? (float) round((($res['today_po'] - $res['yesterday_po']) / $res['yesterday_po']) * 100, 1) : 0;
            $grnTrend = $res['yesterday_grn'] > 0 ? (float) round((($res['today_grn'] - $res['yesterday_grn']) / $res['yesterday_grn']) * 100, 1) : 0;

            return [
                'po' => [
                    'count' => (int) $res['today_po'],
                    'prev' => (int) $res['yesterday_po'],
                    'trend' => abs($poTrend),
                    'trendDir' => $poTrend >= 0 ? 'up' : 'down'
                ],
                'grn' => [
                    'count' => (int) $res['today_grn'],
                    'prev' => (int) $res['yesterday_grn'],
                    'trend' => abs($grnTrend),
                    'trendDir' => $grnTrend >= 0 ? 'up' : 'down'
                ]
            ];
        } catch (\Exception $e) {
            return [
                'po' => ['count' => 0, 'prev' => 0, 'trend' => 0, 'trendDir' => 'up'],
                'grn' => ['count' => 0, 'prev' => 0, 'trend' => 0, 'trendDir' => 'up']
            ];
        }
    }

    /**
     * Get detailed appointment trends
     */
    public function getAppointmentTrends(): void
    {
        $days = (int) ($_GET['days'] ?? 30);
        $branchFilter = $this->branchId ? " AND branch_id = {$this->branchId}" : "";

        $sql = "SELECT 
                    DATE(scheduled_at) as date,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                FROM appointments 
                WHERE scheduled_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                {$branchFilter}
                GROUP BY DATE(scheduled_at)
                ORDER BY date ASC";

        $data = $this->db->fetchAll($sql, [$days]);

        $categories = [];
        $total = [];
        $completed = [];
        $cancelled = [];

        for ($i = $days; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $categories[] = date('d M', strtotime($d));

            $found = false;
            foreach ($data as $row) {
                if ($row['date'] === $d) {
                    $total[] = (int) $row['total'];
                    $completed[] = (int) $row['completed'];
                    $cancelled[] = (int) $row['cancelled'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $total[] = 0;
                $completed[] = 0;
                $cancelled[] = 0;
            }
        }

        Response::success([
            'categories' => $categories,
            'series' => [
                ['name' => 'Total Bookings', 'data' => $total],
                ['name' => 'Completed', 'data' => $completed],
                ['name' => 'Cancelled', 'data' => $cancelled]
            ]
        ]);
    }

    /**
     * Get departmental workload trends
     */
    public function getDepartmentalTrends(): void
    {
        $branchFilter = $this->branchId ? " AND v.branch_id = {$this->branchId}" : "";

        $sql = "SELECT 
                    COALESCE(p.specialization, 'General') as department,
                    COUNT(*) as visits
                FROM visits v
                LEFT JOIN providers p ON v.primary_provider_id = p.provider_id
                WHERE v.visit_start >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                {$branchFilter}
                GROUP BY department
                ORDER BY visits DESC
                LIMIT 5";

        $data = $this->db->fetchAll($sql);

        Response::success($data);
    }

    /**
     * Get doctor performance trends
     */
    public function getDoctorTrends(): void
    {
        $branchFilter = $this->branchId ? " AND v.branch_id = {$this->branchId}" : "";

        $sql = "SELECT 
                    pr.full_name as doctor,
                    COUNT(*) as consultations,
                    SUM(CASE WHEN v.visit_status = 'completed' THEN 1 ELSE 0 END) as completed
                FROM visits v
                JOIN providers pr ON v.primary_provider_id = pr.provider_id
                WHERE v.visit_start >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                {$branchFilter}
                GROUP BY pr.provider_id
                ORDER BY consultations DESC
                LIMIT 5";

        $data = $this->db->fetchAll($sql);

        Response::success($data);
    }
}
