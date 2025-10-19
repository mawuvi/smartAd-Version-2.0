<?php
/**
 * Reference Model
 * Fetches data for populating dropdowns and reference lists.
 */
if (!defined('SECURITY_CHECK')) { die('Direct access not allowed.'); }

class ReferenceModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generic method to get all active records from a reference table.
     */
    public function getAll(string $tableName): array
    {
        // Basic validation to prevent SQL injection with table names.
        $allowedTables = ['publications', 'color_types', 'ad_categories', 'ad_sizes', 'page_positions'];
        if (!in_array($tableName, $allowedTables)) {
            throw new \InvalidArgumentException("Invalid table name provided: " . htmlspecialchars($tableName));
        }

        $sql = "SELECT * FROM {$tableName} WHERE status = 'active' ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}