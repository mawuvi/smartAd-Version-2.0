<?php
/**
 * Public Login API Endpoint with RBAC Support
 * Location: public/api_login.php
 */

// Set the content type to JSON immediately.
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Use the lean API bootstrap.
try {
    require_once __DIR__ . '/../bootstrap_api.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'System initialization error',
        'error' => $e->getMessage()
    ]);
    exit;
}

try {
    // 1. Get the JSON data sent from the login form.
    $rawInput = file_get_contents('php://input');
    error_log("Login API - Raw input: " . $rawInput);
    
    $input = json_decode($rawInput, true);
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    $rememberMe = $input['remember_me'] ?? false;
    
    error_log("Login API - Username: " . $username . ", Password length: " . strlen($password));

    if (empty($username) || empty($password)) {
        ApiResponseHelper::sendError('Username and password are required.', 400);
        exit;
    }

    // 2. Check brute force protection
    $sessionHelper = SessionHelper::getInstance();
    if (!$sessionHelper->checkBruteForceProtection($username)) {
        ApiResponseHelper::sendError('Too many failed login attempts. Please try again later.', 429);
        exit;
    }

    // 3. Find the user in the database.
    $userModel = new UserModel();
    $user = $userModel->findByUsername($username);

    // 4. Verify the password.
    if (!$user || !PasswordHelper::verifyPassword($password, $user['password_hash'])) {
        // Record failed login attempt
        $sessionHelper->recordFailedLoginAttempt($username);
        
        // Log failed login attempt
        try {
            $db = getDatabaseConnection();
            $stmt = $db->prepare("
                INSERT INTO audit_logs (
                    table_name, record_id, action, new_values, 
                    user_id, ip_address, user_agent, created_at
                ) VALUES (
                    'users', ?, 'LOGIN_FAILED', JSON_OBJECT(
                        'username', ?,
                        'reason', 'invalid_credentials'
                    ), ?, ?, ?, NOW()
                )
            ");
            
            $stmt->execute([
                $user ? $user['id'] : 0,
                $username,
                $user ? $user['id'] : 0,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        } catch (PDOException $e) {
            error_log("Login API Error (audit log): " . $e->getMessage());
        }
        
        ApiResponseHelper::sendError('Invalid username or password.', 401);
        exit;
    }

    // 5. Check if user is active
    if ($user['status'] !== 'active') {
        ApiResponseHelper::sendError('Your account is not active. Please contact your administrator.', 403);
        exit;
    }

    // 6. Clear failed login attempts
    $sessionHelper->clearFailedLoginAttempts($username);

    // 7. Initialize RBAC session
    $authGuard = new AuthGuard();
    $authGuard->initializeRBACSession($user['id'], $user);

    // 8. Handle remember me functionality
    if ($rememberMe) {
        // Generate remember token (in a real app, store this in database)
        $rememberToken = bin2hex(random_bytes(32));
        $sessionHelper->setRememberMeCookie($user['id'], $rememberToken);
    }

    // 9. Log successful login
    try {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("
            INSERT INTO audit_logs (
                table_name, record_id, action, new_values, 
                user_id, ip_address, user_agent, created_at
            ) VALUES (
                'users', ?, 'LOGIN_SUCCESS', JSON_OBJECT(
                    'username', ?,
                    'role', ?,
                    'remember_me', ?
                ), ?, ?, ?, NOW()
            )
        ");
        
        $stmt->execute([
            $user['id'],
            $username,
            $user['role'],
            $rememberMe,
            $user['id'],
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        error_log("Login API Error (audit log): " . $e->getMessage());
    }

    // 10. Get user permissions for response
    $rbacHelper = new RBACHelper($user['id']);
    $permissions = $rbacHelper->getPermissions();
    $quickActions = $rbacHelper->getQuickActions();
    $menuItems = $rbacHelper->getAccessibleMenuItems();

    // 11. Send success response with RBAC data
    ApiResponseHelper::sendSuccess('Login successful!', [
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role'],
            'email' => $user['email']
        ],
        'permissions' => $permissions,
        'quick_actions' => $quickActions,
        'menu_items' => $menuItems,
        'redirect_url' => BASE_URL . '/app/pages/dashboard.php?role=' . $user['role']
    ]);

} catch (Exception $e) {
    error_log('Login API Error: ' . $e->getMessage());
    ApiResponseHelper::sendError('An internal server error occurred.', 500);
}