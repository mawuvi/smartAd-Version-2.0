<?php
/**
 * Application Configuration - SECURE FILE
 * Centralized configuration management with environment variable support.
 */
if (!defined('SECURITY_CHECK')) { die('Direct access not allowed.'); }

class Config
{
    private static array $settings = [];

    private static function load(): void
    {
        if (!empty(self::$settings)) { return; }

        self::$settings['app'] = [
            'environment'      => $_ENV['APP_ENV'] ?? 'development',
            'debug'            => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'timezone'         => $_ENV['APP_TIMEZONE'] ?? 'Africa/Accra',
            'login_page'       => BASE_URL . '/public/public_pages/login.php',
        ];

        self::$settings['database'] = [
            'host'     => $_ENV['DB_HOST'] ?? 'localhost',
            'database' => $_ENV['DB_NAME'] ?? 'u528309675_smartdbs',
            'username' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASS'] ?? '', // XAMPP default is empty password
        ];
        
        self::$settings['sms'] = require __DIR__ . '/sms.php';
        self::$settings['google_drive'] = require __DIR__ . '/google_drive.php';

        self::$settings['security'] = [
            'session_lifetime'   => (int)($_ENV['SESSION_LIFETIME'] ?? 7200),
            'max_login_attempts' => (int)($_ENV['MAX_LOGIN_ATTEMPTS'] ?? 5),
            'password_policy'    => [
                'min_length' => (int)($_ENV['PASSWORD_MIN_LENGTH'] ?? 8),
            ],
        ];

        // --- Booking Settings ---
        self::$settings['booking'] = [
            'max_drafts_per_client' => 1,  // Maximum number of draft bookings per client
            'draft_expiry_days'     => 30, // Days before draft expires (future feature)
        ];

        date_default_timezone_set(self::$settings['app']['timezone']);
    }

    public static function getEnvironmentCredentials(): array { self::load(); return self::$settings['database']; }
    public static function isDebug(): bool { self::load(); return self::$settings['app']['debug']; }
    public static function getAppSettings(): array { self::load(); return self::$settings['app']; }
    public static function getSecuritySettings(): array { self::load(); return self::$settings['security']; }
    public static function getSmsSettings(): array { self::load(); return self::$settings['sms']; }
    public static function getGoogleDriveSettings(): array { self::load(); return self::$settings['google_drive']; }
    
    public static function get(string $key, $default = null)
    {
        self::load();
        $keys = explode('.', $key);
        $value = self::$settings;
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        return $value;
    }
}

define('LOGIN_PAGE_URL', Config::getAppSettings()['login_page']);