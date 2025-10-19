<?php
/**
 * Session Helper Class
 * 
 * Provides secure session management functionality for the smartAd system.
 * Handles session security, RBAC session data, and session validation.
 * 
 * @author smartAd Development Team
 * @version 1.0
 * @date 2025-01-08
 */

if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

class SessionHelper
{
    private static $instance = null;
    private $sessionStarted = false;
    
    private function __construct()
    {
        $this->startSecureSession();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Start secure session with proper configuration
     */
    private function startSecureSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure session security
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            // Set session name
            session_name('SMARTAD_SESSION');
            
            // Start session
            session_start();
            $this->sessionStarted = true;
            
            // Regenerate session ID for security
            if (!isset($_SESSION['session_regenerated'])) {
                session_regenerate_id(true);
                $_SESSION['session_regenerated'] = true;
            }
        }
    }
    
    /**
     * Initialize RBAC session data
     * 
     * @param int $userId User ID
     * @param string $userRole User role
     * @param array $permissions User permissions
     */
    public function initializeRBACSession($userId, $userRole, $permissions = [])
    {
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $userRole;
        $_SESSION['permissions'] = $permissions;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['session_valid'] = true;
        
        // Set session timeout (2 hours)
        $_SESSION['session_timeout'] = time() + (2 * 60 * 60);
        
        // Log session initialization
        $this->logSessionActivity('login', $userId);
    }
    
    /**
     * Check if session is valid and not expired
     * 
     * @return bool True if session is valid
     */
    public function isSessionValid()
    {
        if (!isset($_SESSION['session_valid']) || !$_SESSION['session_valid']) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['session_timeout']) && time() > $_SESSION['session_timeout']) {
            $this->destroySession();
            return false;
        }
        
        // Check last activity (30 minutes timeout)
        if (isset($_SESSION['last_activity'])) {
            $inactivityTimeout = 30 * 60; // 30 minutes
            if (time() - $_SESSION['last_activity'] > $inactivityTimeout) {
                $this->destroySession();
                return false;
            }
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Get user ID from session
     * 
     * @return int|null User ID or null if not set
     */
    public function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get user role from session
     * 
     * @return string|null User role or null if not set
     */
    public function getUserRole()
    {
        return $_SESSION['user_role'] ?? null;
    }
    
    /**
     * Get user permissions from session
     * 
     * @return array User permissions
     */
    public function getUserPermissions()
    {
        return $_SESSION['permissions'] ?? [];
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool True if user is logged in
     */
    public function isLoggedIn()
    {
        return $this->isSessionValid() && isset($_SESSION['user_id']);
    }
    
    /**
     * Set session data
     * 
     * @param string $key Session key
     * @param mixed $value Session value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session data
     * 
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Session value or default
     */
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     * 
     * @param string $key Session key
     * @return bool True if key exists
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session data
     * 
     * @param string $key Session key
     */
    public function remove($key)
    {
        unset($_SESSION[$key]);
    }
    
    /**
     * Set flash message (temporary message that shows once)
     * 
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message content
     */
    public function setFlashMessage($type, $message)
    {
        $_SESSION['flash_messages'][$type][] = $message;
    }
    
    /**
     * Get and clear flash messages
     * 
     * @param string $type Message type (optional)
     * @return array Flash messages
     */
    public function getFlashMessages($type = null)
    {
        $messages = $_SESSION['flash_messages'] ?? [];
        
        if ($type) {
            $typeMessages = $messages[$type] ?? [];
            unset($_SESSION['flash_messages'][$type]);
            return $typeMessages;
        }
        
        $allMessages = $messages;
        unset($_SESSION['flash_messages']);
        return $allMessages;
    }
    
    /**
     * Check if flash messages exist
     * 
     * @param string $type Message type (optional)
     * @return bool True if messages exist
     */
    public function hasFlashMessages($type = null)
    {
        if ($type) {
            return !empty($_SESSION['flash_messages'][$type]);
        }
        
        return !empty($_SESSION['flash_messages']);
    }
    
    /**
     * Regenerate session ID for security
     */
    public function regenerateSessionId()
    {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    /**
     * Destroy current session
     */
    public function destroySession()
    {
        $userId = $this->getUserId();
        
        // Log session destruction
        if ($userId) {
            $this->logSessionActivity('logout', $userId);
        }
        
        // Clear session data
        $_SESSION = [];
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
        $this->sessionStarted = false;
    }
    
    /**
     * Log session activity for audit trail
     * 
     * @param string $action Action performed (login, logout, etc.)
     * @param int $userId User ID
     */
    private function logSessionActivity($action, $userId)
    {
        try {
            $db = getDatabaseConnection();
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt = $db->prepare("
                INSERT INTO audit_logs (
                    table_name, record_id, action, new_values, 
                    user_id, ip_address, user_agent, created_at
                ) VALUES (
                    'sessions', ?, ?, JSON_OBJECT(
                        'action', ?,
                        'ip_address', ?,
                        'user_agent', ?
                    ), ?, ?, ?, NOW()
                )
            ");
            
            $stmt->execute([
                $userId,
                $userId,
                $action,
                $ipAddress,
                $userAgent,
                $userId,
                $ipAddress,
                $userAgent
            ]);
        } catch (PDOException $e) {
            error_log("SessionHelper Error (logSessionActivity): " . $e->getMessage());
        }
    }
    
    /**
     * Get session information for debugging
     * 
     * @return array Session information
     */
    public function getSessionInfo()
    {
        return [
            'session_id' => session_id(),
            'user_id' => $this->getUserId(),
            'user_role' => $this->getUserRole(),
            'login_time' => $_SESSION['login_time'] ?? null,
            'last_activity' => $_SESSION['last_activity'] ?? null,
            'session_timeout' => $_SESSION['session_timeout'] ?? null,
            'is_valid' => $this->isSessionValid(),
            'session_started' => $this->sessionStarted
        ];
    }
    
    /**
     * Set CSRF token
     * 
     * @return string CSRF token
     */
    public function generateCSRFToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @return bool True if token is valid
     */
    public function validateCSRFToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Set remember me cookie
     * 
     * @param int $userId User ID
     * @param string $token Remember token
     */
    public function setRememberMeCookie($userId, $token)
    {
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days
        $cookieValue = base64_encode($userId . ':' . $token);
        
        setcookie('remember_me', $cookieValue, $expiry, '/', '', isset($_SERVER['HTTPS']), true);
    }
    
    /**
     * Get remember me cookie
     * 
     * @return array|null Array with userId and token, or null if not set
     */
    public function getRememberMeCookie()
    {
        if (!isset($_COOKIE['remember_me'])) {
            return null;
        }
        
        $decoded = base64_decode($_COOKIE['remember_me']);
        $parts = explode(':', $decoded, 2);
        
        if (count($parts) !== 2) {
            return null;
        }
        
        return [
            'user_id' => (int)$parts[0],
            'token' => $parts[1]
        ];
    }
    
    /**
     * Clear remember me cookie
     */
    public function clearRememberMeCookie()
    {
        setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
    }
    
    /**
     * Check for brute force protection
     * 
     * @param string $identifier Login identifier (username/email)
     * @return bool True if login is allowed
     */
    public function checkBruteForceProtection($identifier)
    {
        $maxAttempts = 5;
        $timeWindow = 15 * 60; // 15 minutes
        
        $key = 'login_attempts_' . md5($identifier);
        $attempts = $this->get($key, []);
        
        // Clean old attempts
        $currentTime = time();
        $attempts = array_filter($attempts, function($timestamp) use ($currentTime, $timeWindow) {
            return ($currentTime - $timestamp) < $timeWindow;
        });
        
        // Check if max attempts reached
        if (count($attempts) >= $maxAttempts) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Record failed login attempt
     * 
     * @param string $identifier Login identifier
     */
    public function recordFailedLoginAttempt($identifier)
    {
        $key = 'login_attempts_' . md5($identifier);
        $attempts = $this->get($key, []);
        $attempts[] = time();
        
        $this->set($key, $attempts);
    }
    
    /**
     * Clear failed login attempts
     * 
     * @param string $identifier Login identifier
     */
    public function clearFailedLoginAttempts($identifier)
    {
        $key = 'login_attempts_' . md5($identifier);
        $this->remove($key);
    }
}
