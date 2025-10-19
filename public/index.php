<?php
/**
 * SmartAd Main Entry Point
 * Initializes the application with RBAC and redirects authenticated users to the dashboard.
 */

// Initialize the application (handles configs, helpers, and authentication).
require_once __DIR__ . '/../bootstrap.php';

// Get AuthGuard instance for RBAC checks
$authGuard = new AuthGuard();

// Check if user is authenticated
if (!$authGuard->isAuthenticated()) {
    // User not authenticated, redirect to login
    header('Location: ' . BASE_URL . '/public_pages/login.php');
    exit();
}

// User is authenticated, check if they can access dashboard
if (!$authGuard->hasPermission('dashboard.view')) {
    // User doesn't have dashboard permission
    $authGuard->getSessionHelper()->setFlashMessage('error', 'You do not have permission to access the dashboard.');
    header('Location: ' . BASE_URL . '/public_pages/login.php');
    exit();
}

// Get user role for role-specific routing
$userRole = $authGuard->getUserRole();

// Log successful access
try {
    $db = getDatabaseConnection();
    $stmt = $db->prepare("
        INSERT INTO audit_logs (
            table_name, record_id, action, new_values, 
            user_id, ip_address, user_agent, created_at
        ) VALUES (
            'dashboard', ?, 'ACCESS', JSON_OBJECT(
                'role', ?,
                'entry_point', 'index.php'
            ), ?, ?, ?, NOW()
        )
    ");
    
    $stmt->execute([
        $authGuard->getSessionHelper()->getUserId(),
        $userRole,
        $authGuard->getSessionHelper()->getUserId(),
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
} catch (PDOException $e) {
    error_log("Index.php Error (audit log): " . $e->getMessage());
}

// Redirect to dashboard based on user role
switch ($userRole) {
    case 'admin':
        header('Location: ' . BASE_URL . '/app/pages/dashboard.php?role=admin');
        break;
    case 'manager':
        header('Location: ' . BASE_URL . '/app/pages/dashboard.php?role=manager');
        break;
    case 'user':
    default:
        header('Location: ' . BASE_URL . '/app/pages/dashboard.php?role=user');
        break;
}
exit();