<?php
/**
 * Notifications API
 * Handles notification-related operations
 */

require_once __DIR__ . '/../../bootstrap_api.php';

header('Content-Type: application/json');

try {
    // Require authentication
    getAuthGuard()->requireAuthJson();
    
    // Get current user
    $currentUser = getAuthGuard()->getCurrentUser();
    if (!$currentUser) {
        ApiResponseHelper::sendError('User not authenticated', 401);
        exit;
    }
    
    // For now, return empty notifications to prevent 404 errors
    // This can be expanded later with actual notification functionality
    $notifications = [
        // Example notification structure:
        // [
        //     'id' => 1,
        //     'type' => 'info',
        //     'title' => 'Welcome!',
        //     'message' => 'Welcome to SmartAd',
        //     'read' => false,
        //     'created_at' => date('Y-m-d H:i:s')
        // ]
    ];
    
    ApiResponseHelper::sendSuccess('Notifications retrieved', [
        'notifications' => $notifications,
        'unread_count' => 0,
        'total_count' => count($notifications)
    ]);
    
} catch (Exception $e) {
    error_log("Notifications API Error: " . $e->getMessage());
    ApiResponseHelper::sendError('Failed to load notifications', 500);
}
