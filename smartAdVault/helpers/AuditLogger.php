<?php
/**
 * Audit Logger - SECURE FILE
 * Provides comprehensive audit logging functionality.
 * Location: smartAdVault/helpers/AuditLogger.php
 */

// Prevent direct script access.
if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

class AuditLogger
{
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_SECURITY = 'security';

    /**
     * Log an operation
     *
     * @param string $module
     * @param string $action
     * @param string $description
     * @param int|null $userId
     * @param string $level
     * @param array $metadata
     */
    public static function logOperation(
        string $module,
        string $action,
        string $description,
        ?int $userId = null,
        string $level = self::LEVEL_INFO,
        array $metadata = []
    ): void {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'user_id' => $userId ?? ($_SESSION['user_id'] ?? null),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'level' => $level,
            'metadata' => json_encode($metadata)
        ];

        self::writeToLog($logEntry);
    }

    /**
     * Log an error
     *
     * @param string $module
     * @param string $action
     * @param string $message
     * @param int|null $userId
     * @param array $metadata
     */
    public static function logError(
        string $module,
        string $action,
        string $message,
        ?int $userId = null,
        array $metadata = []
    ): void {
        self::logOperation($module, $action, $message, $userId, self::LEVEL_ERROR, $metadata);
    }

    /**
     * Log a security event
     *
     * @param string $module
     * @param string $action
     * @param string $description
     * @param int|null $userId
     * @param array $metadata
     */
    public static function logSecurity(
        string $module,
        string $action,
        string $description,
        ?int $userId = null,
        array $metadata = []
    ): void {
        self::logOperation($module, $action, $description, $userId, self::LEVEL_SECURITY, $metadata);
    }

    /**
     * Write log entry to file
     *
     * @param array $logEntry
     */
    private static function writeToLog(array $logEntry): void
    {
        $logFile = VAULT_PATH . '/logs/operations.log';
        $logLine = implode(' | ', $logEntry) . PHP_EOL;
        
        // Ensure log directory exists
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
}
