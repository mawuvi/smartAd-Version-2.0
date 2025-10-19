<?php
if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

/**
 * StatsHelper - Centralized statistics and counter system
 * Provides consistent counting methods across the application
 */
class StatsHelper {
    private static $db;
    
    private static function getDb() {
        if (!self::$db) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }
    
    /**
     * Get count of active publications
     */
    public static function getPublicationsCount() {
        $db = self::getDb();
        return $db->query("SELECT COUNT(*) as count FROM publications WHERE deleted_at IS NULL AND status = 'active'")->fetch()['count'];
    }
    
    /**
     * Get count of active ad categories
     */
    public static function getAdCategoriesCount() {
        $db = self::getDb();
        return $db->query("SELECT COUNT(*) as count FROM ad_categories WHERE deleted_at IS NULL AND status = 'active'")->fetch()['count'];
    }
    
    /**
     * Get count of active ad sizes
     */
    public static function getAdSizesCount() {
        $db = self::getDb();
        return $db->query("SELECT COUNT(*) as count FROM ad_sizes WHERE deleted_at IS NULL AND status = 'active'")->fetch()['count'];
    }
    
    /**
     * Get count of active page positions
     */
    public static function getPagePositionsCount() {
        $db = self::getDb();
        return $db->query("SELECT COUNT(*) as count FROM page_positions WHERE deleted_at IS NULL AND status = 'active'")->fetch()['count'];
    }
    
    /**
     * Get count of active color types
     */
    public static function getColorTypesCount() {
        $db = self::getDb();
        return $db->query("SELECT COUNT(*) as count FROM color_types WHERE deleted_at IS NULL AND status = 'active'")->fetch()['count'];
    }
    
    /**
     * Get count of all rates (including inactive)
     */
    public static function getRatesCount() {
        $db = self::getDb();
        return $db->query("SELECT COUNT(*) as count FROM rates WHERE deleted_at IS NULL")->fetch()['count'];
    }
    
    /**
     * Get count of active users
     */
    public static function getUsersCount() {
        $db = self::getDb();
        return $db->query("SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL")->fetch()['count'];
    }
    
    /**
     * Get count of active clients
     */
    public static function getClientsCount() {
        $db = self::getDb();
        return $db->query("SELECT COUNT(*) as count FROM clients WHERE deleted_at IS NULL")->fetch()['count'];
    }
    
    /**
     * Get count of bookings by status
     * @param string|null $status - Optional status filter
     */
    public static function getBookingsCount($status = null) {
        $db = self::getDb();
        if ($status) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings WHERE deleted_at IS NULL AND status = ?");
            $stmt->execute([$status]);
            return $stmt->fetch()['count'];
        }
        return $db->query("SELECT COUNT(*) as count FROM bookings WHERE deleted_at IS NULL")->fetch()['count'];
    }
    
    /**
     * Get count of draft bookings
     */
    public static function getDraftBookingsCount() {
        return self::getBookingsCount('draft');
    }
    
    /**
     * Get count of confirmed bookings
     */
    public static function getConfirmedBookingsCount() {
        return self::getBookingsCount('confirmed');
    }
    
    /**
     * Get count of pending bookings
     */
    public static function getPendingBookingsCount() {
        return self::getBookingsCount('pending');
    }
    
    /**
     * Get count of cancelled bookings
     */
    public static function getCancelledBookingsCount() {
        return self::getBookingsCount('cancelled');
    }
    
    /**
     * Get all counts in a single call for efficiency
     */
    public static function getAllCounts() {
        return [
            'publications' => self::getPublicationsCount(),
            'ad_categories' => self::getAdCategoriesCount(),
            'ad_sizes' => self::getAdSizesCount(),
            'page_positions' => self::getPagePositionsCount(),
            'color_types' => self::getColorTypesCount(),
            'rates' => self::getRatesCount(),
            'users' => self::getUsersCount(),
            'clients' => self::getClientsCount(),
            'bookings' => self::getBookingsCount(),
            'draft_bookings' => self::getDraftBookingsCount(),
            'confirmed_bookings' => self::getConfirmedBookingsCount(),
            'pending_bookings' => self::getPendingBookingsCount(),
            'cancelled_bookings' => self::getCancelledBookingsCount()
        ];
    }
    
    /**
     * Get dashboard-specific counts
     */
    public static function getDashboardCounts() {
        return [
            'total_bookings' => self::getBookingsCount(),
            'draft_bookings' => self::getDraftBookingsCount(),
            'confirmed_bookings' => self::getConfirmedBookingsCount(),
            'pending_bookings' => self::getPendingBookingsCount(),
            'total_clients' => self::getClientsCount(),
            'total_rates' => self::getRatesCount(),
            'total_publications' => self::getPublicationsCount()
        ];
    }
    
    /**
     * Get setup-specific counts
     */
    public static function getSetupCounts() {
        return [
            'publications' => self::getPublicationsCount(),
            'ad_categories' => self::getAdCategoriesCount(),
            'ad_sizes' => self::getAdSizesCount(),
            'page_positions' => self::getPagePositionsCount(),
            'color_types' => self::getColorTypesCount(),
            'rates' => self::getRatesCount(),
            'users' => self::getUsersCount(),
            'clients' => self::getClientsCount()
        ];
    }
}
