<?php
/**
 * Staging Data Cleanup Script
 * Purges staging data older than 48 hours
 * Run via cron: 0 2 * * * php /path/to/cleanup_staging.php
 */

require_once __DIR__ . '/../../bootstrap.php';

try {
    $db = Database::getInstance();
    
    // Delete staging data older than 48 hours
    $stmt = $db->prepare("
        DELETE FROM rates_staging 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 48 HOUR)
    ");
    $stmt->execute();
    
    $deletedCount = $stmt->rowCount();
    
    // Log cleanup activity
    error_log("[STAGING_CLEANUP] Deleted {$deletedCount} old staging rows (older than 48 hours)");
    
    echo json_encode([
        'success' => true,
        'deleted' => $deletedCount,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    error_log("[STAGING_CLEANUP] ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
