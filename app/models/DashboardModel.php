<?php
/**
 * Dashboard Model
 * 
 * Provides role-filtered data for the dashboard system.
 * Handles statistics, activity feeds, and notifications based on user permissions.
 * 
 * @author smartAd Development Team
 * @version 1.0
 * @date 2025-01-08
 */

if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

class DashboardModel
{
    private $db;
    private $rbacHelper;
    
    public function __construct($userId = null)
    {
        $this->db = getDatabaseConnection();
        $this->rbacHelper = new RBACHelper($userId);
    }
    
    /**
     * Get dashboard statistics filtered by user role
     * 
     * @param int $userId User ID
     * @param string $role User role
     * @return array Dashboard statistics
     */
    public function getStatistics($userId, $role)
    {
        try {
            $stats = [];
            
            switch ($role) {
                case 'admin':
                    $stats = $this->getAdminStatistics();
                    break;
                case 'manager':
                    $stats = $this->getManagerStatistics($userId);
                    break;
                case 'user':
                default:
                    $stats = $this->getUserStatistics($userId);
                    break;
            }
            
            return $stats;
        } catch (PDOException $e) {
            error_log("DashboardModel Error (getStatistics): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get admin statistics (system-wide)
     * 
     * @return array Admin statistics
     */
    private function getAdminStatistics()
    {
        $stmt = $this->db->prepare("CALL sp_get_dashboard_stats()");
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Add admin-specific metrics
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_users,
                SUM(CASE WHEN role = 'manager' THEN 1 ELSE 0 END) as manager_users,
                SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as regular_users
            FROM users 
            WHERE deleted_at IS NULL
        ");
        $stmt->execute();
        $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return array_merge($stats, $userStats);
    }
    
    /**
     * Get manager statistics (team-focused)
     * 
     * @param int $userId Manager user ID
     * @return array Manager statistics
     */
    private function getManagerStatistics($userId)
    {
        // Get team bookings (bookings created by team members)
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_bookings,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                SUM(CASE WHEN status = 'confirmed' THEN total_amount ELSE 0 END) as total_revenue,
                COUNT(DISTINCT client_id) as unique_clients
            FROM bookings 
            WHERE deleted_at IS NULL
        ");
        $stmt->execute();
        $bookingStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get team client count
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_clients
            FROM clients 
            WHERE deleted_at IS NULL
        ");
        $stmt->execute();
        $clientStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return array_merge($bookingStats, $clientStats);
    }
    
    /**
     * Get user statistics (personal)
     * 
     * @param int $userId User ID
     * @return array User statistics
     */
    private function getUserStatistics($userId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_bookings,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                SUM(CASE WHEN status = 'confirmed' THEN total_amount ELSE 0 END) as total_revenue,
                COUNT(DISTINCT client_id) as unique_clients
            FROM bookings 
            WHERE created_by = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$userId]);
        $bookingStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get personal client count
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_clients
            FROM clients 
            WHERE created_by = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$userId]);
        $clientStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return array_merge($bookingStats, $clientStats);
    }
    
    /**
     * Get recent activity based on user role
     * 
     * @param int $userId User ID
     * @param string $role User role
     * @param int $limit Number of activities to return
     * @return array Recent activities
     */
    public function getRecentActivity($userId, $role, $limit = 10)
    {
        try {
            $activities = [];
            
            // Get recent bookings
            if ($this->rbacHelper->hasPermission('bookings.view')) {
                $bookingActivities = $this->getRecentBookings($userId, $role, $limit);
                $activities = array_merge($activities, $bookingActivities);
            }
            
            // Get recent clients (managers and admins only)
            if (in_array($role, ['manager', 'admin']) && $this->rbacHelper->hasPermission('clients.view')) {
                $clientActivities = $this->getRecentClients($userId, $role, $limit);
                $activities = array_merge($activities, $clientActivities);
            }
            
            // Sort by date and limit results
            usort($activities, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            return array_slice($activities, 0, $limit);
        } catch (PDOException $e) {
            error_log("DashboardModel Error (getRecentActivity): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent bookings activity
     * 
     * @param int $userId User ID
     * @param string $role User role
     * @param int $limit Limit
     * @return array Recent bookings
     */
    private function getRecentBookings($userId, $role, $limit)
    {
        $sql = "
            SELECT 
                b.id,
                b.booking_number,
                b.status,
                b.total_amount,
                b.created_at,
                c.company_name as client_name,
                p.name as publication_name,
                u.first_name,
                u.last_name
            FROM bookings b
            LEFT JOIN clients c ON b.client_id = c.id
            LEFT JOIN publications p ON b.publication_id = p.id
            LEFT JOIN users u ON b.created_by = u.id
            WHERE b.deleted_at IS NULL
        ";
        
        if ($role === 'user') {
            $sql .= " AND b.created_by = ?";
            $params = [$userId];
        } else {
            $params = [];
        }
        
        $sql .= " ORDER BY b.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format as activities
        $activities = [];
        foreach ($bookings as $booking) {
            $activities[] = [
                'type' => 'booking',
                'id' => $booking['id'],
                'title' => "Booking {$booking['booking_number']} created",
                'description' => "{$booking['client_name']} - {$booking['publication_name']}",
                'amount' => $booking['total_amount'],
                'status' => $booking['status'],
                'created_by' => "{$booking['first_name']} {$booking['last_name']}",
                'created_at' => $booking['created_at']
            ];
        }
        
        return $activities;
    }
    
    /**
     * Get recent clients activity
     * 
     * @param int $userId User ID
     * @param string $role User role
     * @param int $limit Limit
     * @return array Recent clients
     */
    private function getRecentClients($userId, $role, $limit)
    {
        $sql = "
            SELECT 
                c.id,
                c.client_number,
                c.company_name,
                c.client_type,
                c.created_at,
                u.first_name,
                u.last_name
            FROM clients c
            LEFT JOIN users u ON c.created_by = u.id
            WHERE c.deleted_at IS NULL
            ORDER BY c.created_at DESC 
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format as activities
        $activities = [];
        foreach ($clients as $client) {
            $activities[] = [
                'type' => 'client',
                'id' => $client['id'],
                'title' => "Client {$client['client_number']} created",
                'description' => "{$client['company_name']} ({$client['client_type']})",
                'created_by' => "{$client['first_name']} {$client['last_name']}",
                'created_at' => $client['created_at']
            ];
        }
        
        return $activities;
    }
    
    /**
     * Get quick actions available to user
     * 
     * @param int $userId User ID
     * @param string $role User role
     * @return array Quick actions
     */
    public function getQuickActions($userId, $role)
    {
        return $this->rbacHelper->getQuickActions();
    }
    
    /**
     * Get user notifications
     * 
     * @param int $userId User ID
     * @return array User notifications
     */
    public function getNotifications($userId)
    {
        try {
            $notifications = [];
            
            // Get draft booking reminders
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as draft_count,
                    MAX(created_at) as last_draft_date
                FROM bookings 
                WHERE created_by = ? AND status = 'draft' AND deleted_at IS NULL
            ");
            $stmt->execute([$userId]);
            $draftInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($draftInfo['draft_count'] > 0) {
                $notifications[] = [
                    'type' => 'warning',
                    'title' => 'Draft Bookings',
                    'message' => "You have {$draftInfo['draft_count']} draft booking(s) pending completion.",
                    'action_url' => '/bookings_list.php?status=draft',
                    'created_at' => $draftInfo['last_draft_date']
                ];
            }
            
            // Get upcoming publication dates
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as upcoming_count,
                    MIN(publication_date) as next_publication
                FROM bookings 
                WHERE created_by = ? AND status = 'confirmed' 
                AND publication_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                AND deleted_at IS NULL
            ");
            $stmt->execute([$userId]);
            $upcomingInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($upcomingInfo['upcoming_count'] > 0) {
                $notifications[] = [
                    'type' => 'info',
                    'title' => 'Upcoming Publications',
                    'message' => "You have {$upcomingInfo['upcoming_count']} booking(s) publishing in the next 7 days.",
                    'action_url' => '/bookings_list.php?upcoming=1',
                    'created_at' => $upcomingInfo['next_publication']
                ];
            }
            
            return $notifications;
        } catch (PDOException $e) {
            error_log("DashboardModel Error (getNotifications): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get pending approvals (managers and admins only)
     * 
     * @param int $userId User ID
     * @param string $role User role
     * @return array Pending approvals
     */
    public function getPendingApprovals($userId, $role)
    {
        if (!in_array($role, ['manager', 'admin'])) {
            return [];
        }
        
        try {
            $approvals = [];
            
            // Get bookings pending approval (if approval workflow is implemented)
            $stmt = $this->db->prepare("
                SELECT 
                    b.id,
                    b.booking_number,
                    b.total_amount,
                    b.created_at,
                    c.company_name as client_name,
                    p.name as publication_name,
                    u.first_name,
                    u.last_name
                FROM bookings b
                LEFT JOIN clients c ON b.client_id = c.id
                LEFT JOIN publications p ON b.publication_id = p.id
                LEFT JOIN users u ON b.created_by = u.id
                WHERE b.status = 'draft' AND b.deleted_at IS NULL
                ORDER BY b.created_at DESC
                LIMIT 10
            ");
            $stmt->execute();
            $pendingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($pendingBookings as $booking) {
                $approvals[] = [
                    'type' => 'booking_approval',
                    'id' => $booking['id'],
                    'title' => "Booking {$booking['booking_number']}",
                    'description' => "{$booking['client_name']} - {$booking['publication_name']}",
                    'amount' => $booking['total_amount'],
                    'created_by' => "{$booking['first_name']} {$booking['last_name']}",
                    'created_at' => $booking['created_at']
                ];
            }
            
            return $approvals;
        } catch (PDOException $e) {
            error_log("DashboardModel Error (getPendingApprovals): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get revenue chart data based on user role
     * 
     * @param int $userId User ID
     * @param string $role User role
     * @param int $months Number of months to include
     * @return array Chart data
     */
    public function getRevenueChartData($userId, $role, $months = 6)
    {
        try {
            $sql = "
                SELECT 
                    DATE_FORMAT(publication_date, '%Y-%m') as month,
                    SUM(total_amount) as revenue,
                    COUNT(*) as booking_count
                FROM bookings 
                WHERE status = 'confirmed' 
                AND publication_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                AND deleted_at IS NULL
            ";
            
            $params = [$months];
            
            if ($role === 'user') {
                $sql .= " AND created_by = ?";
                $params[] = $userId;
            }
            
            $sql .= " GROUP BY DATE_FORMAT(publication_date, '%Y-%m') ORDER BY month";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $data;
        } catch (PDOException $e) {
            error_log("DashboardModel Error (getRevenueChartData): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get top clients based on user role
     * 
     * @param int $userId User ID
     * @param string $role User role
     * @param int $limit Number of clients to return
     * @return array Top clients
     */
    public function getTopClients($userId, $role, $limit = 5)
    {
        try {
            $sql = "
                SELECT 
                    c.id,
                    c.client_number,
                    c.company_name,
                    COUNT(b.id) as booking_count,
                    SUM(CASE WHEN b.status = 'confirmed' THEN b.total_amount ELSE 0 END) as total_revenue
                FROM clients c
                LEFT JOIN bookings b ON c.id = b.client_id AND b.deleted_at IS NULL
                WHERE c.deleted_at IS NULL
            ";
            
            $params = [];
            
            if ($role === 'user') {
                $sql .= " AND c.created_by = ?";
                $params[] = $userId;
            }
            
            $sql .= " GROUP BY c.id ORDER BY total_revenue DESC LIMIT ?";
            $params[] = $limit;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DashboardModel Error (getTopClients): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get dashboard menu items based on permissions
     * 
     * @param int $userId User ID
     * @return array Menu items
     */
    public function getDashboardMenuItems($userId)
    {
        return $this->rbacHelper->getAccessibleMenuItems();
    }
}
