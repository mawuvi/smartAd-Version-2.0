<?php
/**
 * Password Helper - SECURE FILE
 * Provides secure password hashing, validation, and management utilities.
 */

// Prevent direct script access.
if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

class PasswordHelper
{
    /**
     * Hashes a password using PHP's secure password_hash function.
     */
    public static function hashPassword(string $password): string
    {
        if (empty($password)) {
            throw new \InvalidArgumentException("Password cannot be empty.");
        }
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verifies a password against its hash.
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        if (empty($password) || empty($hash)) {
            return false;
        }
        return password_verify($password, $hash);
    }

    /**
     * Checks if a password hash needs to be updated.
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
}