<?php
/**
 * API Key Model
 *
 * Handles all database operations related to the api_keys table.
 */

// Prevent direct script access.
if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

class ApiKeyModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Finds the user ID associated with an active API key.
     *
     * @param string $apiKey The API key to look up.
     * @return int|null The user ID, or null if the key is not found or inactive.
     */
    public function findUserIdByKey(string $apiKey): ?int
    {
        try {
            $query = "
                SELECT user_id FROM api_keys
                WHERE api_key = :apiKey AND is_active = 1
                LIMIT 1
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['apiKey' => $apiKey]);
            $result = $stmt->fetchColumn();

            return $result ? (int)$result : null;

        } catch (PDOException $e) {
            error_log("ApiKeyModel Error: " . $e->getMessage());
            return null;
        }
    }
}