<?php
/**
 * Migration Runner - Complete Database Setup
 * Run this file to create all database tables and initial data
 */

// Define constants
define('ROOT_PATH', __DIR__);
define('SYSTEM_PATH', ROOT_PATH . '/system');
define('SRC_PATH', ROOT_PATH . '/src');

// Load environment variables
require_once SYSTEM_PATH . '/env.php';

require_once __DIR__ . '/system/autoload.php';

use System\Database;

echo "=== Complete Database Migration ===\n\n";

try {
    // First, connect without database to create it if needed
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? 'melina_hms';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    echo "Checking database connection...\n";
    
    try {
        $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        ]);
        
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        echo "✓ Database '$database' ready\n";
        
        // Select the database
        $pdo->exec("USE `$database`");
        
    } catch (PDOException $e) {
        die("✗ Error: Could not connect to MySQL server: " . $e->getMessage() . "\n");
    }
    
    echo "✓ Database connection established\n\n";
    
    // Get all SQL files from doc/v1.0.0 folder
    $migrationDir = __DIR__ . '/doc/v1.0.0';
    $sqlFiles = glob($migrationDir . '/*.sql');
    
    if (empty($sqlFiles)) {
        die("Error: No SQL files found in $migrationDir\n");
    }
    
    // Sort files to ensure v1.0.0.sql runs first, then others
    sort($sqlFiles);
    
    echo "Found " . count($sqlFiles) . " SQL file(s) to execute:\n";
    foreach ($sqlFiles as $file) {
        echo "  - " . basename($file) . "\n";
    }
    echo "\n";
    
    $totalSuccess = 0;
    $totalErrors = 0;
    
    foreach ($sqlFiles as $sqlFile) {
        $fileName = basename($sqlFile);
        echo "=== Executing: $fileName ===\n";
        
        $sql = file_get_contents($sqlFile);
        
        if (!$sql) {
            echo "✗ Error: Could not read $fileName\n\n";
            continue;
        }
        
        // Split by semicolons but keep the SQL statements intact
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && 
                       !preg_match('/^--/', $stmt) && 
                       !preg_match('/^\/\*/', $stmt);
            }
        );
        
        $success = 0;
        $errors = 0;
        
        foreach ($statements as $statement) {
            // Skip comments and empty statements
            if (empty(trim($statement))) continue;
            
            try {
                $pdo->exec($statement);
                $success++;
                
                // Show what was executed (only first line for brevity)
                $firstLine = strtok($statement, "\n");
                if (strlen($firstLine) > 80) {
                    $firstLine = substr($firstLine, 0, 80) . '...';
                }
                echo "✓ " . $firstLine . "\n";
                
            } catch (PDOException $e) {
                $errors++;
                $errorMsg = $e->getMessage();
                // Only show error if it's not "already exists" type
                if (stripos($errorMsg, 'already exists') === false && 
                    stripos($errorMsg, 'duplicate') === false) {
                    echo "✗ Error: " . $errorMsg . "\n";
                }
            }
        }
        
        $totalSuccess += $success;
        $totalErrors += $errors;
        
        echo "File complete: $success statements executed, $errors errors\n\n";
    }
    
    echo "=== Migration Complete ===\n";
    echo "Total Success: $totalSuccess statements\n";
    echo "Total Errors: $totalErrors statements\n";
    
    // Verify key tables were created
    echo "\n=== Verifying Key Tables ===\n";
    
    $tables = ['patients', 'appointments', 'invoices', 'products', 'product_categories', 'product_units', 'theme_settings'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✓ Table '$table' exists with {$result['count']} records\n";
        } catch (PDOException $e) {
            echo "✗ Table '$table' not found\n";
        }
    }
    
    echo "\n✓ Migration completed successfully!\n";
    echo "\nYou can now access:\n";
    echo "- Dashboard: http://localhost:8000/dashboard\n";
    echo "- Categories: http://localhost:8000/inventory/categories\n";
    echo "- Units: http://localhost:8000/inventory/units\n\n";
    
} catch (Exception $e) {
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
