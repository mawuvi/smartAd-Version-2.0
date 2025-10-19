<?php
/**
 * Helper Loader - SECURE FILE
 *
 * Automatically scans the /helpers/ directory and loads all PHP helper files.
 * It also provides a HelperManager class to access shared instances.
 */

// Prevent direct script access.
if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

// --- Auto-load all other .php files in this directory ---
foreach (glob(__DIR__ . '/*.php') as $filename) {
    if (basename($filename) !== 'HelperLoader.php') {
        require_once $filename;
    }
}

/**
 * HelperManager (Service Locator)
 * Provides centralized access to helper instances.
 */
class HelperManager
{
    private static array $instances = [];

    private static function getInstance(string $helperClassName)
    {
        if (!isset(self::$instances[$helperClassName])) {
            if (class_exists($helperClassName)) {
                self::$instances[$helperClassName] = new $helperClassName();
            } else {
                error_log("Helper class not found: $helperClassName");
                return null;
            }
        }
        return self::$instances[$helperClassName];
    }

    public static function getApiResponse(): ?ApiResponseHelper
    {
        return self::getInstance('ApiResponseHelper');
    }

    public static function getPasswordHelper(): ?PasswordHelper
    {
        return self::getInstance('PasswordHelper');
    }
}