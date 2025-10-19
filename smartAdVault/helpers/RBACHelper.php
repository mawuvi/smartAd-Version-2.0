<?php
/**
 * RBAC Helper Class
 * 
 * Provides comprehensive Role-Based Access Control functionality
 * for the smartAd system. Handles permission checking, user roles,
 * and data filtering based on permissions.
 * 
 * @author smartAd Development Team
 * @version 1.0
 * @date 2025-01-08
 */

if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

class RBACHelper
{
    private $db;
    private $userId;
    private $userRole;
    private $permissions;
    private $permissionCache;
    
    public function __construct($userId = null)
    {
        $this->db = getDatabaseConnection();
        $this->userId = $userId ?: ($_SESSION['user_id'] ?? null);
        $this->permissionCache = [];
        
        if ($this->userId) {
            $this->loadUserRole();
            $this->loadUserPermissions();
        }
    }
    
    /**
     * Load user role from database
     */
    private function loadUserRole()
    {
        try {
            $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$this->userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->userRole = $result ? $result['role'] : 'user';
        } catch (PDOException $e) {
            error_log("RBACHelper Error (loadUserRole): " . $e->getMessage());
            $this->userRole = 'user';
        }
    }
    
    /**
     * Load user permissions from database
     */
    private function loadUserPermissions()
    {
        try {
            $stmt = $this->db->prepare("CALL sp_get_user_permissions(?)");
            $stmt->execute([$this->userId]);
            $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->permissions = [];
            foreach ($permissions as $permission) {
                $this->permissions[$permission['code']] = $permission['granted'];
            }
        } catch (PDOException $e) {
            error_log("RBACHelper Error (loadUserPermissions): " . $e->getMessage());
            $this->permissions = [];
        }
    }
    
    /**
     * Check if user has specific permission
     * 
     * @param string $permissionCode Permission code (e.g., 'bookings.create')
     * @return bool True if user has permission
     */
    public function hasPermission($permissionCode)
    {
        // Check cache first
        if (isset($this->permissionCache[$permissionCode])) {
            return $this->permissionCache[$permissionCode];
        }
        
        // Admin has all permissions
        if ($this->userRole === 'admin') {
            $this->permissionCache[$permissionCode] = true;
            return true;
        }
        
        // Check loaded permissions
        $hasPermission = isset($this->permissions[$permissionCode]) && $this->permissions[$permissionCode];
        
        // Cache the result
        $this->permissionCache[$permissionCode] = $hasPermission;
        
        // Log permission check
        $this->logPermissionCheck($permissionCode, $hasPermission);
        
        return $hasPermission;
    }
    
    /**
     * Check if user can view a specific resource
     * 
     * @param string $resource Resource name (e.g., 'bookings', 'clients')
     * @param int $resourceId Resource ID (optional)
     * @return bool True if user can view
     */
    public function canView($resource, $resourceId = null)
    {
        $permissionCode = $resource . '.view';
        
        if (!$this->hasPermission($permissionCode)) {
            return false;
        }
        
        // Additional ownership check for specific resources
        if ($resourceId && in_array($resource, ['bookings', 'clients'])) {
            return $this->canAccessResource($resource, $resourceId);
        }
        
        return true;
    }
    
    /**
     * Check if user can create a specific resource
     * 
     * @param string $resource Resource name
     * @return bool True if user can create
     */
    public function canCreate($resource)
    {
        return $this->hasPermission($resource . '.create');
    }
    
    /**
     * Check if user can edit a specific resource
     * 
     * @param string $resource Resource name
     * @param int $resourceId Resource ID
     * @return bool True if user can edit
     */
    public function canEdit($resource, $resourceId)
    {
        if (!$this->hasPermission($resource . '.edit')) {
            return false;
        }
        
        // Check ownership for specific resources
        if (in_array($resource, ['bookings', 'clients'])) {
            return $this->canAccessResource($resource, $resourceId);
        }
        
        return true;
    }
    
    /**
     * Check if user can delete a specific resource
     * 
     * @param string $resource Resource name
     * @param int $resourceId Resource ID
     * @return bool True if user can delete
     */
    public function canDelete($resource, $resourceId)
    {
        if (!$this->hasPermission($resource . '.delete')) {
            return false;
        }
        
        // Check ownership for specific resources
        if (in_array($resource, ['bookings', 'clients'])) {
            return $this->canAccessResource($resource, $resourceId);
        }
        
        return true;
    }
    
    /**
     * Check if user can manage a specific resource
     * 
     * @param string $resource Resource name
     * @param string $action Specific action (e.g., 'approve', 'manage_credit')
     * @return bool True if user can perform action
     */
    public function canManage($resource, $action)
    {
        $permissionCode = $resource . '.' . $action;
        return $this->hasPermission($permissionCode);
    }
    
    /**
     * Check if user can access a specific resource (ownership check)
     * 
     * @param string $resource Resource name
     * @param int $resourceId Resource ID
     * @return bool True if user can access
     */
    private function canAccessResource($resource, $resourceId)
    {
        try {
            $table = $resource;
            $stmt = $this->db->prepare("SELECT created_by FROM {$table} WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$resourceId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return false;
            }
            
            // User can access their own resources
            if ($result['created_by'] == $this->userId) {
                return true;
            }
            
            // Managers and admins can access all resources
            if (in_array($this->userRole, ['manager', 'admin'])) {
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("RBACHelper Error (canAccessResource): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all user permissions
     * 
     * @return array Array of permissions
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
    
    /**
     * Get user role
     * 
     * @return string User role
     */
    public function getUserRole()
    {
        return $this->userRole;
    }
    
    /**
     * Filter data based on user permissions
     * 
     * @param array $data Data to filter
     * @param string $resource Resource name
     * @return array Filtered data
     */
    public function filterByPermissions($data, $resource)
    {
        if (!$this->canView($resource)) {
            return [];
        }
        
        // If user can view all, return all data
        if (in_array($this->userRole, ['admin', 'manager'])) {
            return $data;
        }
        
        // Filter by ownership for regular users
        $filteredData = [];
        foreach ($data as $item) {
            if (isset($item['created_by']) && $item['created_by'] == $this->userId) {
                $filteredData[] = $item;
            }
        }
        
        return $filteredData;
    }
    
    /**
     * Get dashboard statistics based on user role
     * 
     * @return array Dashboard statistics
     */
    public function getDashboardStats()
    {
        try {
            $stmt = $this->db->prepare("CALL sp_get_dashboard_stats()");
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Filter stats based on user role
            if ($this->userRole === 'user') {
                // User can only see their own data
                $stmt = $this->db->prepare("
                    SELECT 
                        COUNT(*) as total_bookings,
                        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_bookings,
                        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                        SUM(CASE WHEN status = 'confirmed' THEN total_amount ELSE 0 END) as total_revenue
                    FROM bookings 
                    WHERE created_by = ? AND deleted_at IS NULL
                ");
                $stmt->execute([$this->userId]);
                $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stats['total_bookings'] = $userStats['total_bookings'];
                $stats['draft_bookings'] = $userStats['draft_bookings'];
                $stats['confirmed_bookings'] = $userStats['confirmed_bookings'];
                $stats['total_revenue'] = $userStats['total_revenue'];
                $stats['total_clients'] = 0; // Users don't see client count
                $stats['active_clients'] = 0;
            }
            
            return $stats;
        } catch (PDOException $e) {
            error_log("RBACHelper Error (getDashboardStats): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get quick actions available to user
     * 
     * @return array Available quick actions
     */
    public function getQuickActions()
    {
        $actions = [];
        
        if ($this->canCreate('bookings')) {
            $actions[] = [
                'title' => 'Create Booking',
                'url' => '/booking.php',
                'icon' => 'plus-circle',
                'class' => 'btn-primary'
            ];
        }
        
        if ($this->canCreate('clients')) {
            $actions[] = [
                'title' => 'Add Client',
                'url' => '/clients.php',
                'icon' => 'user-plus',
                'class' => 'btn-success'
            ];
        }
        
        if ($this->canView('bookings')) {
            $actions[] = [
                'title' => 'View Bookings',
                'url' => '/bookings_list.php',
                'icon' => 'list',
                'class' => 'btn-info'
            ];
        }
        
        if ($this->canView('reports')) {
            $actions[] = [
                'title' => 'View Reports',
                'url' => '/reports.php',
                'icon' => 'chart-bar',
                'class' => 'btn-warning'
            ];
        }
        
        if ($this->userRole === 'admin') {
            $actions[] = [
                'title' => 'Manage Users',
                'url' => '/users.php',
                'icon' => 'users',
                'class' => 'btn-danger'
            ];
        }
        
        return $actions;
    }
    
    /**
     * Log permission check for audit trail
     * 
     * @param string $permissionCode Permission code
     * @param bool $granted Whether permission was granted
     */
    private function logPermissionCheck($permissionCode, $granted)
    {
        try {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            $stmt = $this->db->prepare("CALL sp_log_permission_check(?, ?, ?, ?, ?)");
            $stmt->execute([
                $this->userId,
                $permissionCode,
                0, // No specific resource ID for general permission checks
                $granted,
                $ipAddress
            ]);
        } catch (PDOException $e) {
            error_log("RBACHelper Error (logPermissionCheck): " . $e->getMessage());
        }
    }
    
    /**
     * Check if user can access dashboard
     * 
     * @return bool True if user can access dashboard
     */
    public function canAccessDashboard()
    {
        return $this->hasPermission('dashboard.view');
    }
    
    /**
     * Get user's accessible menu items
     * 
     * @return array Menu items user can access
     */
    public function getAccessibleMenuItems()
    {
        $menuItems = [];
        
        if ($this->canView('dashboard')) {
            $menuItems[] = ['title' => 'Dashboard', 'url' => '/dashboard.php', 'icon' => 'home'];
        }
        
        if ($this->canView('bookings')) {
            $menuItems[] = ['title' => 'Bookings', 'url' => '/bookings_list.php', 'icon' => 'calendar'];
        }
        
        if ($this->canView('clients')) {
            $menuItems[] = ['title' => 'Clients', 'url' => '/clients.php', 'icon' => 'users'];
        }
        
        if ($this->canView('rates')) {
            $menuItems[] = ['title' => 'Rates', 'url' => '/rates.php', 'icon' => 'dollar-sign'];
        }
        
        if ($this->canView('publications')) {
            $menuItems[] = ['title' => 'Publications', 'url' => '/publications.php', 'icon' => 'newspaper'];
        }
        
        if ($this->canView('reports')) {
            $menuItems[] = ['title' => 'Reports', 'url' => '/reports.php', 'icon' => 'chart-bar'];
        }
        
        if ($this->userRole === 'admin') {
            $menuItems[] = ['title' => 'Users', 'url' => '/users.php', 'icon' => 'user-cog'];
            $menuItems[] = ['title' => 'Settings', 'url' => '/settings.php', 'icon' => 'cog'];
        }
        
        return $menuItems;
    }
    
    /**
     * Require permission or redirect/deny access
     * 
     * @param string $permissionCode Permission code required
     * @param string $redirectUrl URL to redirect if no permission
     * @return bool True if permission granted
     */
    public function requirePermission($permissionCode, $redirectUrl = '/dashboard.php')
    {
        if (!$this->hasPermission($permissionCode)) {
            $_SESSION['error_message'] = 'You do not have permission to access this resource.';
            header("Location: {$redirectUrl}");
            exit;
        }
        
        return true;
    }
    
    /**
     * Get permission denied message
     * 
     * @param string $action Action that was denied
     * @return string User-friendly error message
     */
    public function getPermissionDeniedMessage($action = 'perform this action')
    {
        return "You do not have permission to {$action}. Please contact your administrator if you believe this is an error.";
    }
}
