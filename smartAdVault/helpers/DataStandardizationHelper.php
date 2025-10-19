<?php
/**
 * Data Standardization Helper - SECURE FILE
 * Handles caps policy and data formatting across all APIs.
 * Location: smartAdVault/helpers/DataStandardizationHelper.php
 */

// Prevent direct script access.
if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

class DataStandardizationHelper
{
    /**
     * Applies UPPERCASE policy to text fields in an array of data.
     */
    public static function applyCapsPolicy(array $data, string $tableName): array
    {
        $db = Database::getInstance();
        // This is a simplified version. A real implementation might cache schema.
        $stmt = $db->query("DESCRIBE `{$tableName}`");
        $schema = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        foreach ($data as $field => &$value) {
            if (isset($schema[$field]) && is_string($value)) {
                // Apply caps only to string-based column types
                if (stripos($schema[$field], 'char') !== false || stripos($schema[$field], 'text') !== false) {
                    $value = strtoupper(trim($value));
                }
            }
        }
        return $data;
    }

    /**
     * Formats a number as currency.
     */
    public static function formatCurrency($amount, string $symbol = 'GHS'): string
    {
        return $symbol . ' ' . number_format((float)$amount, 2);
    }

    /**
     * Find similar publications using fuzzy matching
     * Used during bulk upload to detect potential duplicates
     */
    public static function findSimilarPublications($name, $threshold = 85) {
        // Use ValidationHelper for consistent sanitization
        require_once __DIR__ . '/ValidationHelper.php';
        $sanitizedName = ValidationHelper::sanitizeString($name);
        $standardizedName = strtoupper(trim($sanitizedName));
        
        $db = Database::getInstance();
        
        // Get all active publications
        $stmt = $db->query("SELECT id, name, code FROM publications WHERE deleted_at IS NULL");
        $publications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $matches = [];
        foreach ($publications as $pub) {
            $existingName = strtoupper(trim($pub['name']));
            
            // Exact match
            if ($existingName === $standardizedName) {
                $matches[] = [
                    'id' => $pub['id'],
                    'name' => $pub['name'],
                    'code' => $pub['code'],
                    'similarity' => 100,
                    'match_type' => 'exact'
                ];
                continue;
            }
            
            // Fuzzy match using similar_text
            similar_text($standardizedName, $existingName, $percent);
            if ($percent >= $threshold) {
                $matches[] = [
                    'id' => $pub['id'],
                    'name' => $pub['name'],
                    'code' => $pub['code'],
                    'similarity' => round($percent, 2),
                    'match_type' => 'similar'
                ];
            }
        }
        
        // Sort by similarity descending
        usort($matches, function($a, $b) {
            return $b['similarity'] - $a['similarity'];
        });
        
        return $matches;
    }
    
    /**
     * Find similar items in any reference table
     * @param string $name Item name to search for
     * @param string $tableName Database table name
     * @param int $threshold Similarity threshold (default 85%)
     * @return array Similar items with similarity scores
     */
    public static function findSimilarItems($name, $tableName, $threshold = 85) {
        if (empty($name) || empty($tableName)) {
            return [];
        }
        
        // Use ValidationHelper for consistent sanitization
        require_once __DIR__ . '/ValidationHelper.php';
        $sanitizedName = ValidationHelper::sanitizeString($name);
        $standardizedName = strtoupper(trim($sanitizedName));
        
        $db = Database::getInstance();
        
        // Validate table name to prevent SQL injection
        $allowedTables = ['ad_categories', 'ad_sizes', 'page_positions', 'color_types'];
        if (!in_array($tableName, $allowedTables)) {
            throw new InvalidArgumentException("Invalid table name: {$tableName}");
        }
        
        // Get all active items from the specified table
        $stmt = $db->query("SELECT id, name FROM {$tableName} WHERE deleted_at IS NULL");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $matches = [];
        foreach ($items as $item) {
            $existingName = strtoupper(trim($item['name']));
            
            // Exact match
            if ($existingName === $standardizedName) {
                $matches[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'similarity' => 100,
                    'match_type' => 'exact'
                ];
                continue;
            }
            
            // Fuzzy match using similar_text
            similar_text($standardizedName, $existingName, $percent);
            if ($percent >= $threshold) {
                $matches[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'similarity' => round($percent, 2),
                    'match_type' => 'similar'
                ];
            }
        }
        
        // Sort by similarity descending
        usort($matches, function($a, $b) {
            return $b['similarity'] - $a['similarity'];
        });
        
        return $matches;
    }
}