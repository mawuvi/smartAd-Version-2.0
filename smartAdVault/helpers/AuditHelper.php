<?php
/**
 * Audit Helper - SECURE FILE
 * Provides audit trail and activity logging functionality.
 * Location: smartAdVault/helpers/AuditHelper.php
 */

// Prevent direct script access.
if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

class AuditHelper
{
    /**
     * Logs a user activity in the audit_logs table.
     */
    public static function logActivity(
        int $userId,
        string $action,
        string $entityType,
        int $entityId,
        ?array $details = null
    ): bool 
    {
        $db = Database::getInstance();
        $sql = "INSERT INTO audit_logs 
                    (user_id, action, entity_type, entity_id, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);

        $params = [
            $userId,
            $action,
            $entityType,
            $entityId,
            json_encode($details),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        return $stmt->execute($params);
    }
}