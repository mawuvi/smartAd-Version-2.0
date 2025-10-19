<?php
/**
 * Bootstrap.php - Central Configuration and Authentication Loader
 */

// --- 1. Core Definitions & Security ---
define('SECURITY_CHECK', true);
define('ROOT_PATH', __DIR__);
// Path to the vault, now located inside the project root.
define('VAULT_PATH', __DIR__ . '/smartAdVault'); 
define('BASE_URL', '/smartAd');

// --- 2. Load Core Configurations ---
if (!is_dir(VAULT_PATH)) {
    error_log("FATAL: smartAdVault directory not found at: " . VAULT_PATH);
    http_response_code(500);
    die('System configuration error. Please contact an administrator.');
}
$envFile = VAULT_PATH . '/.env';
if (file_exists($envFile)) {
    // Read .env file directly to bypass .htaccess protection
    $envContent = file_get_contents($envFile);
    $lines = explode("\n", $envContent);
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line) && strpos($line, '#') !== 0 && strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key); 
            $value = trim($value);
            $_ENV[$key] = $value; 
            putenv("$key=$value");
        }
    }
}
require_once VAULT_PATH . '/config/config.php';

// --- 3. Dynamic Session Initialization ---
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
$isProduction = (Config::getAppSettings()['environment'] === 'production');
ini_set('session.cookie_secure', $isProduction ? 1 : 0);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 4. Load Remaining Dependencies ---
// Auto-load all model files.
$modelFiles = glob(__DIR__ . '/app/models/*.php');
foreach ($modelFiles as $modelFile) {
    require_once $modelFile;
}

$configs = ['database.php', 'sms.php', 'google_drive.php', 'auth.php'];
foreach ($configs as $configFile) {
    $filePath = VAULT_PATH . '/config/' . $configFile;
    if (file_exists($filePath)) { require_once $filePath; }
}
require_once VAULT_PATH . '/helpers/HelperLoader.php';
require_once VAULT_PATH . '/auth/AuthGuard.php';

// --- 5. Authentication Enforcement ---
function getAuthGuard(): AuthGuard {
    static $authGuardInstance = null;
    if ($authGuardInstance === null) { 
        $authGuardInstance = new AuthGuard(); 
    }
    return $authGuardInstance;
}

// Public Whitelist - Files that bypass AuthGuard security check
// Per MasterProject.md Section 3: "The only files that bypass the AuthGuard security check 
// are explicitly listed in the $publicWhitelist array within bootstrap.php"
$publicWhitelist = [
    'public_pages/login.php',
    'public_pages/register.php',
    'public_pages/forgot-password.php',
    'api_login.php',
    'api_logout.php',
    'index.php',  // Main entry point
    'public/index.php',  // Public entry point
    '/smartAd/public/index.php',  // Full path
    '/smartAd/index.php'  // Root path
];

// Get the current script path relative to the project root
$currentScript = $_SERVER['SCRIPT_NAME'];
$projectRoot = '/smartAd/';
$relativePath = str_replace($projectRoot, '', $currentScript);

// Also check the full script name
$scriptBasename = basename($currentScript);

// Check if current page is in public whitelist
$isPublicPage = false;
foreach ($publicWhitelist as $whitelistedPath) {
    // Check relative path, full path, and basename
    if (strpos($relativePath, $whitelistedPath) !== false || 
        strpos($currentScript, $whitelistedPath) !== false ||
        $scriptBasename === basename($whitelistedPath)) {
        $isPublicPage = true;
        break;
    }
}

// If not a public page, require authentication
if (!$isPublicPage && !getAuthGuard()->isAuthenticated()) {
    // Redirect to login page
    header('Location: ' . BASE_URL . '/public/public_pages/login.php');
    exit();
}