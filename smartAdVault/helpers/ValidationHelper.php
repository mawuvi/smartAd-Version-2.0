<?php
/**
 * Validation Helper - SECURE FILE
 * Provides data validation utilities.
 * Location: smartAdVault/helpers/ValidationHelper.php
 */

// Prevent direct script access.
if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

class ValidationHelper
{
    /**
     * Validate email address
     *
     * @param string $email
     * @return bool
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number (basic format)
     *
     * @param string $phone
     * @return bool
     */
    public static function isValidPhone(string $phone): bool
    {
        // Remove all non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's a valid length (7-15 digits)
        return strlen($cleaned) >= 7 && strlen($cleaned) <= 15;
    }

    /**
     * Validate required fields
     *
     * @param array $data
     * @param array $requiredFields
     * @return array Array of missing fields
     */
    public static function validateRequired(array $data, array $requiredFields): array
    {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missing[] = $field;
            }
        }
        
        return $missing;
    }

    /**
     * Sanitize string input
     *
     * @param string $input
     * @return string
     */
    public static function sanitizeString(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate numeric value
     *
     * @param mixed $value
     * @param float|null $min
     * @param float|null $max
     * @return bool
     */
    public static function isValidNumber($value, ?float $min = null, ?float $max = null): bool
    {
        if (!is_numeric($value)) {
            return false;
        }
        
        $num = (float)$value;
        
        if ($min !== null && $num < $min) {
            return false;
        }
        
        if ($max !== null && $num > $max) {
            return false;
        }
        
        return true;
    }

    /**
     * Validate password strength
     *
     * @param string $password
     * @param int $minLength
     * @return array Array of validation errors
     */
    public static function validatePassword(string $password, int $minLength = 8): array
    {
        $errors = [];
        
        if (strlen($password) < $minLength) {
            $errors[] = "Password must be at least {$minLength} characters long";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        return $errors;
    }
}