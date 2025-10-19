<?php
/**
 * User Management API Endpoint
 */

// Use the main bootstrap file, which handles security.
require_once __DIR__ . '/../../bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
$action = $_REQUEST['action'] ?? null;
$userModel = new UserModel();

try {
    switch ($action) {
        case 'get_users':
            $users = $userModel->getAll();
            ApiResponseHelper::sendSuccess('Users retrieved', ['users' => $users]);
            break;
        
        case 'create_user':
            $validation = ValidationHelper::validate($_POST, [
                'first_name' => 'required',
                'last_name' => 'required',
                'username' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'role' => 'required'
            ]);
            
            if (!$validation['valid']) {
                ApiResponseHelper::sendError('Validation failed', 400, $validation['errors']);
                break;
            }
            
            // Check if username is unique
            if (!ValidationHelper::validateUnique('username', $_POST['username'], 'users')) {
                ApiResponseHelper::sendError('Username already exists', 400);
                break;
            }
            
            // Check if email is unique
            if (!ValidationHelper::validateUnique('email', $_POST['email'], 'users')) {
                ApiResponseHelper::sendError('Email already exists', 400);
                break;
            }
            
            $userId = $userModel->create($_POST);
            AuditHelper::logActivity(
                getAuthGuard()->getCurrentUser()['id'],
                'CREATE',
                'user',
                $userId,
                ['username' => $_POST['username']]
            );
            
            ApiResponseHelper::sendSuccess('User created successfully', ['user_id' => $userId]);
            break;
        
        // Add other cases like 'delete_user' here.

        default:
            ApiResponseHelper::sendError('Invalid action for user_api.', 400);
            break;
    }
} catch (Exception $e) {
    error_log("User API Error: " . $e->getMessage());
    ApiResponseHelper::sendError('An internal server error occurred.', 500);
}