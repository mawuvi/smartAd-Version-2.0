<?php
/**
 * Database Setup Runner for smartAd System
 * 
 * This script executes the complete database setup SQL file with proper
 * error handling, verification, and detailed reporting.
 * 
 * Usage: php database/run_database_setup.php
 * 
 * @author smartAd Development Team
 * @version 1.0
 * @date 2025-01-08
 */

// Prevent direct web access
if (isset($_SERVER['HTTP_HOST'])) {
    http_response_code(403);
    die('Direct web access not allowed. Run this script from command line.');
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once __DIR__ . '/../smartAdVault/config/database.php';

class DatabaseSetupRunner
{
    private $pdo;
    private $logFile;
    private $startTime;
    
    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->logFile = __DIR__ . '/../smartAdVault/logs/database_setup_' . date('Y-m-d_H-i-s') . '.log';
        
        // Ensure logs directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $this->log("=== smartAd Database Setup Started ===");
        $this->log("Timestamp: " . date('Y-m-d H:i:s'));
        $this->log("PHP Version: " . PHP_VERSION);
        $this->log("Script: " . __FILE__);
    }
    
    /**
     * Log message to both console and file
     */
    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}";
        
        echo $logMessage . PHP_EOL;
        file_put_contents($this->logFile, $logMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Connect to database
     */
    private function connect()
    {
        try {
            $dsn = "mysql:host={$GLOBALS['DB_HOST']};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $GLOBALS['DB_USER'], $GLOBALS['DB_PASS'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            
            $this->log("✓ Database connection established");
            $this->log("Host: {$GLOBALS['DB_HOST']}");
            $this->log("User: {$GLOBALS['DB_USER']}");
            
            return true;
        } catch (PDOException $e) {
            $this->log("✗ Database connection failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute SQL file
     */
    private function executeSqlFile()
    {
        $sqlFile = __DIR__ . '/complete_database_setup.sql';
        
        if (!file_exists($sqlFile)) {
            $this->log("✗ SQL file not found: {$sqlFile}");
            return false;
        }
        
        $this->log("✓ SQL file found: {$sqlFile}");
        $this->log("File size: " . number_format(filesize($sqlFile)) . " bytes");
        
        try {
            $sql = file_get_contents($sqlFile);
            
            if ($sql === false) {
                $this->log("✗ Failed to read SQL file");
                return false;
            }
            
            $this->log("✓ SQL file loaded successfully");
            
            // Split SQL into individual statements
            $statements = $this->splitSqlStatements($sql);
            $this->log("✓ SQL split into " . count($statements) . " statements");
            
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($statements as $index => $statement) {
                $statement = trim($statement);
                
                if (empty($statement) || $this->isComment($statement)) {
                    continue;
                }
                
                try {
                    $this->pdo->exec($statement);
                    $successCount++;
                    
                    // Log major operations
                    if ($this->isMajorOperation($statement)) {
                        $this->log("✓ Executed: " . $this->getOperationDescription($statement));
                    }
                    
                } catch (PDOException $e) {
                    $errorCount++;
                    $this->log("✗ Statement " . ($index + 1) . " failed: " . $e->getMessage());
                    $this->log("Statement: " . substr($statement, 0, 100) . "...");
                    
                    // Continue with other statements unless it's a critical error
                    if (!$this->isCriticalError($e)) {
                        continue;
                    } else {
                        $this->log("✗ Critical error encountered, stopping execution");
                        return false;
                    }
                }
            }
            
            $this->log("✓ SQL execution completed");
            $this->log("Successful statements: {$successCount}");
            $this->log("Failed statements: {$errorCount}");
            
            return $errorCount === 0;
            
        } catch (Exception $e) {
            $this->log("✗ SQL execution failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Split SQL into individual statements
     */
    private function splitSqlStatements($sql)
    {
        // Remove comments and normalize
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split by semicolon, but be careful with stored procedures
        $statements = [];
        $current = '';
        $inDelimiter = false;
        $delimiter = ';';
        
        $lines = explode("\n", $sql);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Check for delimiter changes
            if (preg_match('/^DELIMITER\s+(.+)$/i', $line, $matches)) {
                if ($inDelimiter) {
                    // End of delimiter block
                    $inDelimiter = false;
                    $delimiter = ';';
                } else {
                    // Start of delimiter block
                    $inDelimiter = true;
                    $delimiter = trim($matches[1]);
                }
                continue;
            }
            
            $current .= $line . "\n";
            
            // Check if statement is complete
            if (!$inDelimiter && substr(rtrim($line), -1) === ';') {
                $statements[] = trim($current);
                $current = '';
            } elseif ($inDelimiter && substr(rtrim($line), -strlen($delimiter)) === $delimiter) {
                $statements[] = trim($current);
                $current = '';
            }
        }
        
        // Add any remaining statement
        if (!empty(trim($current))) {
            $statements[] = trim($current);
        }
        
        return array_filter($statements);
    }
    
    /**
     * Check if line is a comment
     */
    private function isComment($line)
    {
        $line = trim($line);
        return empty($line) || 
               strpos($line, '--') === 0 || 
               strpos($line, '/*') === 0 ||
               strpos($line, '#') === 0;
    }
    
    /**
     * Check if statement is a major operation
     */
    private function isMajorOperation($statement)
    {
        $statement = strtoupper(trim($statement));
        return strpos($statement, 'CREATE TABLE') === 0 ||
               strpos($statement, 'CREATE VIEW') === 0 ||
               strpos($statement, 'CREATE PROCEDURE') === 0 ||
               strpos($statement, 'CREATE INDEX') === 0 ||
               strpos($statement, 'CREATE DATABASE') === 0 ||
               strpos($statement, 'USE ') === 0;
    }
    
    /**
     * Get operation description
     */
    private function getOperationDescription($statement)
    {
        $statement = strtoupper(trim($statement));
        
        if (strpos($statement, 'CREATE TABLE') === 0) {
            preg_match('/CREATE TABLE\s+(\w+)/i', $statement, $matches);
            return "CREATE TABLE " . ($matches[1] ?? 'unknown');
        }
        
        if (strpos($statement, 'CREATE VIEW') === 0) {
            preg_match('/CREATE.*VIEW\s+(\w+)/i', $statement, $matches);
            return "CREATE VIEW " . ($matches[1] ?? 'unknown');
        }
        
        if (strpos($statement, 'CREATE PROCEDURE') === 0) {
            preg_match('/CREATE PROCEDURE\s+(\w+)/i', $statement, $matches);
            return "CREATE PROCEDURE " . ($matches[1] ?? 'unknown');
        }
        
        if (strpos($statement, 'CREATE INDEX') === 0) {
            preg_match('/CREATE INDEX\s+(\w+)/i', $statement, $matches);
            return "CREATE INDEX " . ($matches[1] ?? 'unknown');
        }
        
        if (strpos($statement, 'CREATE DATABASE') === 0) {
            preg_match('/CREATE DATABASE\s+(\w+)/i', $statement, $matches);
            return "CREATE DATABASE " . ($matches[1] ?? 'unknown');
        }
        
        if (strpos($statement, 'USE ') === 0) {
            preg_match('/USE\s+(\w+)/i', $statement, $matches);
            return "USE DATABASE " . ($matches[1] ?? 'unknown');
        }
        
        return "SQL operation";
    }
    
    /**
     * Check if error is critical
     */
    private function isCriticalError($e)
    {
        $message = strtolower($e->getMessage());
        
        // Non-critical errors that we can continue with
        $nonCritical = [
            'already exists',
            'duplicate',
            'table already exists',
            'database already exists'
        ];
        
        foreach ($nonCritical as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Verify database setup
     */
    private function verifySetup()
    {
        $this->log("=== Verifying Database Setup ===");
        
        try {
            // Check database exists
            $stmt = $this->pdo->query("SELECT DATABASE() as current_db");
            $result = $stmt->fetch();
            $this->log("✓ Current database: " . $result['current_db']);
            
            // Count tables
            $stmt = $this->pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->log("✓ Tables created: " . count($tables));
            
            // List all tables
            foreach ($tables as $table) {
                $this->log("  - {$table}");
            }
            
            // Count views
            $stmt = $this->pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
            $views = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->log("✓ Views created: " . count($views));
            
            foreach ($views as $view) {
                $this->log("  - {$view}");
            }
            
            // Count stored procedures
            $stmt = $this->pdo->query("SHOW PROCEDURE STATUS WHERE Db = DATABASE()");
            $procedures = $stmt->fetchAll();
            $this->log("✓ Stored procedures created: " . count($procedures));
            
            foreach ($procedures as $procedure) {
                $this->log("  - {$procedure['Name']}");
            }
            
            // Check for seed data
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM users");
            $userCount = $stmt->fetch()['count'];
            $this->log("✓ Users seeded: {$userCount}");
            
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM publications");
            $pubCount = $stmt->fetch()['count'];
            $this->log("✓ Publications seeded: {$pubCount}");
            
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM clients");
            $clientCount = $stmt->fetch()['count'];
            $this->log("✓ Clients seeded: {$clientCount}");
            
            return true;
            
        } catch (PDOException $e) {
            $this->log("✗ Verification failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Run the complete setup process
     */
    public function run()
    {
        $this->log("Starting database setup process...");
        
        // Step 1: Connect to database
        if (!$this->connect()) {
            $this->log("✗ Setup failed at connection step");
            return false;
        }
        
        // Step 2: Execute SQL file
        if (!$this->executeSqlFile()) {
            $this->log("✗ Setup failed at SQL execution step");
            return false;
        }
        
        // Step 3: Verify setup
        if (!$this->verifySetup()) {
            $this->log("✗ Setup failed at verification step");
            return false;
        }
        
        // Calculate execution time
        $executionTime = microtime(true) - $this->startTime;
        $this->log("✓ Database setup completed successfully!");
        $this->log("Execution time: " . number_format($executionTime, 2) . " seconds");
        $this->log("Log file: {$this->logFile}");
        
        return true;
    }
}

// Run the setup
echo "smartAd Database Setup Runner\n";
echo "============================\n\n";

$runner = new DatabaseSetupRunner();
$success = $runner->run();

if ($success) {
    echo "\n✓ Database setup completed successfully!\n";
    echo "You can now use the smartAd system.\n";
    exit(0);
} else {
    echo "\n✗ Database setup failed!\n";
    echo "Check the log file for details.\n";
    exit(1);
}
