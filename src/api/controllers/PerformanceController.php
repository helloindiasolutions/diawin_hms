<?php
/**
 * Performance Monitoring Controller
 * Provides performance metrics and diagnostics
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Database;
use System\Response;
use System\QueryCache;

class PerformanceController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get performance metrics
     */
    public function getMetrics(): void
    {
        $startTime = microtime(true);

        // Database connection test
        $dbStart = microtime(true);
        try {
            $this->db->fetchColumn("SELECT 1");
            $dbTime = (microtime(true) - $dbStart) * 1000;
            $dbStatus = 'connected';
        } catch (\Exception $e) {
            $dbTime = (microtime(true) - $dbStart) * 1000;
            $dbStatus = 'error: ' . $e->getMessage();
        }

        // Query cache stats
        $cacheStats = QueryCache::getStats();

        // Memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);

        // Server info
        $serverInfo = [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status() !== false
        ];

        // Database stats
        $dbStats = $this->getDatabaseStats();

        $totalTime = (microtime(true) - $startTime) * 1000;

        Response::success([
            'performance' => [
                'total_time_ms' => round($totalTime, 2),
                'db_connection_ms' => round($dbTime, 2),
                'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
                'memory_peak_mb' => round($memoryPeak / 1024 / 1024, 2)
            ],
            'cache' => $cacheStats,
            'database' => [
                'status' => $dbStatus,
                'stats' => $dbStats
            ],
            'server' => $serverInfo,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get database statistics
     */
    private function getDatabaseStats(): array
    {
        try {
            // Get table sizes
            $tables = $this->db->fetchAll(
                "SELECT 
                    table_name,
                    table_rows,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                FROM information_schema.TABLES
                WHERE table_schema = DATABASE()
                ORDER BY (data_length + index_length) DESC
                LIMIT 10"
            );

            // Get database size
            $dbSize = $this->db->fetch(
                "SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES
                WHERE table_schema = DATABASE()"
            );

            return [
                'total_size_mb' => $dbSize['size_mb'] ?? 0,
                'largest_tables' => $tables
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Test query performance
     */
    public function testQuery(): void
    {
        $query = $_GET['query'] ?? 'SELECT COUNT(*) FROM patients';
        $iterations = (int) ($_GET['iterations'] ?? 1);

        $results = [];
        $totalTime = 0;

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            try {
                $this->db->query($query);
                $time = (microtime(true) - $start) * 1000;
                $results[] = round($time, 2);
                $totalTime += $time;
            } catch (\Exception $e) {
                Response::error('Query failed: ' . $e->getMessage(), 400);
                return;
            }
        }

        Response::success([
            'query' => $query,
            'iterations' => $iterations,
            'times_ms' => $results,
            'average_ms' => round($totalTime / $iterations, 2),
            'min_ms' => min($results),
            'max_ms' => max($results),
            'total_ms' => round($totalTime, 2)
        ]);
    }

    /**
     * Clear query cache
     */
    public function clearCache(): void
    {
        $pattern = $_GET['pattern'] ?? null;
        QueryCache::clear($pattern);

        Response::success([
            'message' => $pattern 
                ? "Cache cleared for pattern: {$pattern}" 
                : 'All cache cleared',
            'stats' => QueryCache::getStats()
        ]);
    }

    /**
     * Check database indexes
     */
    public function checkIndexes(): void
    {
        $table = $_GET['table'] ?? 'invoices';

        try {
            $indexes = $this->db->fetchAll("SHOW INDEX FROM `{$table}`");

            Response::success([
                'table' => $table,
                'indexes' => $indexes,
                'count' => count($indexes)
            ]);
        } catch (\Exception $e) {
            Response::error('Failed to get indexes: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Analyze slow queries
     */
    public function analyzeQuery(): void
    {
        $query = $_GET['query'] ?? '';

        if (empty($query)) {
            Response::error('Query parameter required', 400);
            return;
        }

        try {
            $explain = $this->db->fetchAll("EXPLAIN " . $query);

            Response::success([
                'query' => $query,
                'explain' => $explain
            ]);
        } catch (\Exception $e) {
            Response::error('Failed to analyze query: ' . $e->getMessage(), 400);
        }
    }
}
