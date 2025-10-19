<?php
/**
 * Placement Model
 * Handles all database operations for placements.
 */
if (!defined('SECURITY_CHECK')) { die('Direct access not allowed.'); }

class PlacementModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retrieves a paginated list of all placements.
     */
    public function getAll(array $filters): array
    {
        $limit = (int)($filters['limit'] ?? 10);
        $page = (int)($filters['page'] ?? 1);
        $offset = ($page - 1) * $limit;

        $sql = "SELECT p.*, c.company_name as client_name 
                FROM placements p
                LEFT JOIN clients c ON p.client_id = c.id
                WHERE p.deleted_at IS NULL
                ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}