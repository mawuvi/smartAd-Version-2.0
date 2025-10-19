<?php
/**
 * Authentication Guard - SECURE FILE
 *
 * Handles authentication checks for pages and APIs with RBAC support.
 * This class assumes the bootstrap process has completed.
 */

// Prevent direct script access.
if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

// Ensure required models and helpers are available.
require_once ROOT_PATH . '/app/models/UserModel.php';
require_once ROOT_PATH . '/smartAdVault/helpers/RBACHelper.php';
require_once ROOT_PATH . '/smartAdVault/helpers/SessionHelper.php';

class AuthGuard
{
    private SessionHelper $sessionHelper;
    private RBACHelper $rbacHelper;

    public function __construct()
    {
        $this->sessionHelper = SessionHelper::getInstance();
        $this->rbacHelper = new RBACHelper();
    }

    /**
     * Checks if a user is authenticated. If not, redirects to the login page.
     * Used for standard web pages.
     */
    public function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->sessionHelper->setFlashMessage('error', 'Please log in to access this page.');
            header('Location: ' . LOGIN_PAGE_URL);
            exit();
        }
    }

    /**
     * Checks if a user is authenticated. If not, returns a 401 JSON error.
     * Used for API endpoints.
     */
    public function requireAuthJson(): void
    {
        if (!$this->isAuthenticated()) {
            ApiResponseHelper::sendError('Authentication required.', 401);
        }
    }

    /**
     * Require specific permission for page access
     * 
     * @param string $permissionCode Permission code required
     * @param string $redirectUrl URL to redirect if no permission
     */
    public function requirePermission($permissionCode, $redirectUrl = '/dashboard.php'): void
    {
        $this->requireAuth();
        
        if (!$this->rbacHelper->hasPermission($permissionCode)) {
            $this->sessionHelper->setFlashMessage('error', $this->rbacHelper->getPermissionDeniedMessage());
            header("Location: {$redirectUrl}");
            exit();
        }
    }

    /**
     * Require specific permission for API access
     * 
     * @param string $permissionCode Permission code required
     */
    public function requirePermissionJson($permissionCode): void
    {
        $this->requireAuthJson();
        
        if (!$this->rbacHelper->hasPermission($permissionCode)) {
            ApiResponseHelper::sendError('Insufficient permissions.', 403);
        }
    }

    /**
     * Require specific role for access
     * 
     * @param string|array $requiredRoles Required role(s)
     * @param string $redirectUrl URL to redirect if no permission
     */
    public function requireRole($requiredRoles, $redirectUrl = '/dashboard.php'): void
    {
        $this->requireAuth();
        
        $userRole = $this->rbacHelper->getUserRole();
        $roles = is_array($requiredRoles) ? $requiredRoles : [$requiredRoles];
        
        if (!in_array($userRole, $roles)) {
            $this->sessionHelper->setFlashMessage('error', 'You do not have the required role to access this page.');
            header("Location: {$redirectUrl}");
            exit();
        }
    }

    /**
     * Require specific role for API access
     * 
     * @param string|array $requiredRoles Required role(s)
     */
    public function requireRoleJson($requiredRoles): void
    {
        $this->requireAuthJson();
        
        $userRole = $this->rbacHelper->getUserRole();
        $roles = is_array($requiredRoles) ? $requiredRoles : [$requiredRoles];
        
        if (!in_array($userRole, $roles)) {
            ApiResponseHelper::sendError('Insufficient role privileges.', 403);
        }
    }

    /**
     * Returns the currently logged-in user's data.
     *
     * @return array|null The user's data, or null if not logged in.
     */
    public function getCurrentUser(): ?array
    {
        $userId = $this->sessionHelper->getUserId();
        if (!$userId) {
            return null;
        }

        $userModel = new UserModel();
        return $userModel->findById($userId);
    }

    /**
     * Get current user's RBAC helper instance
     * 
     * @return RBACHelper RBAC helper instance
     */
    public function getRBACHelper(): RBACHelper
    {
        return $this->rbacHelper;
    }

    /**
     * Get current user's session helper instance
     * 
     * @return SessionHelper Session helper instance
     */
    public function getSessionHelper(): SessionHelper
    {
        return $this->sessionHelper;
    }

    /**
     * Check if current user has specific permission
     * 
     * @param string $permissionCode Permission code
     * @return bool True if user has permission
     */
    public function hasPermission($permissionCode): bool
    {
        return $this->rbacHelper->hasPermission($permissionCode);
    }

    /**
     * Check if current user has specific role
     * 
     * @param string|array $roles Role(s) to check
     * @return bool True if user has role
     */
    public function hasRole($roles): bool
    {
        $userRole = $this->rbacHelper->getUserRole();
        $roleArray = is_array($roles) ? $roles : [$roles];
        
        return in_array($userRole, $roleArray);
    }

    /**
     * Get current user's role
     * 
     * @return string|null User role or null if not authenticated
     */
    public function getUserRole(): ?string
    {
        return $this->rbacHelper->getUserRole();
    }

    /**
     * Get current user's permissions
     * 
     * @return array User permissions
     */
    public function getUserPermissions(): array
    {
        return $this->rbacHelper->getPermissions();
    }

    /**
     * A robust check to see if the user has a valid, active session.
     * It checks for a session ID AND verifies the user exists in the database.
     * Enhanced with RBAC session validation.
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        // 1. Check if session is valid using SessionHelper
        if (!$this->sessionHelper->isSessionValid()) {
            return false;
        }

        // 2. Check if user ID exists in the session
        $userId = $this->sessionHelper->getUserId();
        if ($userId === null) {
            return false; // No session ID, definitely not authenticated
        }

        // 3. Verify the user ID against the database
        $userModel = new UserModel();
        $user = $userModel->findById($userId);

        if ($user) {
            // 4. Check if user is active
            if ($user['status'] !== 'active') {
                $this->sessionHelper->destroySession();
                return false;
            }

            // 5. Update last login time
            $this->updateLastLoginTime($userId);
            
            // User exists and is active. They are authenticated.
            return true;
        } else {
            // The session ID is stale/invalid because the user doesn't exist.
            // Destroy the bad session to prevent loops.
            $this->sessionHelper->destroySession();
            return false; // Not authenticated.
        }
    }

    /**
     * Update user's last login time
     * 
     * @param int $userId User ID
     */
    private function updateLastLoginTime($userId): void
    {
        try {
            $db = getDatabaseConnection();
            $stmt = $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("AuthGuard Error (updateLastLoginTime): " . $e->getMessage());
        }
    }

    /**
     * Initialize RBAC session after successful login
     * 
     * @param int $userId User ID
     * @param array $userData User data from database
     */
    public function initializeRBACSession($userId, $userData): void
    {
        // Get user permissions
        $rbacHelper = new RBACHelper($userId);
        $permissions = $rbacHelper->getPermissions();
        
        // Initialize session with RBAC data
        $this->sessionHelper->initializeRBACSession(
            $userId,
            $userData['role'],
            $permissions
        );
    }

    /**
     * Logout user and destroy session
     */
    public function logout(): void
    {
        $this->sessionHelper->destroySession();
    }
}