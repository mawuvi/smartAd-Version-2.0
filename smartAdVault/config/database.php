<?php
/**
 * Database Configuration - SECURE FILE
 * Provides a singleton instance of the PDO database connection.
 */
if (!defined('SECURITY_CHECK')) { die('Direct access not allowed.'); }

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}
    public function __wakeup() { throw new \Exception("Cannot unserialize a singleton."); }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $config = Config::getEnvironmentCredentials();
                $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $options);
            } catch (PDOException $e) {
                error_log("FATAL: Database connection failed: " . $e->getMessage());
                die('A critical database error occurred.');
            }
        }
        return self::$instance;
    }
}

/**
 * Global function to get database connection
 * This function is used throughout the application for database access
 */
function getDatabaseConnection(): PDO
{
    return Database::getInstance();
}