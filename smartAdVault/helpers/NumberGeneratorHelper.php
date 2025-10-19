<?php
/**
 * Number Generator Helper - SECURE FILE
 * Generates various types of sequential numbers for different entities.
 * Location: smartAdVault/helpers/NumberGeneratorHelper.php
 */

// Prevent direct script access.
if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

class NumberGeneratorHelper
{
    /**
     * Generates the next sequential number for a given entity (e.g., client, invoice).
     */
    public static function generateNextNumber(string $prefix, string $table, string $column): string
    {
        $db = Database::getInstance();
        $query = "SELECT {$column} FROM {$table} WHERE {$column} LIKE ? ORDER BY {$column} DESC LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute(["{$prefix}%"]);
        $lastNumber = $stmt->fetchColumn();

        if (!$lastNumber) {
            return $prefix . '000001';
        }

        $numericPart = (int)substr($lastNumber, strlen($prefix));
        $nextNumericPart = $numericPart + 1;
        return $prefix . str_pad($nextNumericPart, 6, '0', STR_PAD_LEFT);
    }
}