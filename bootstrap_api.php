<?php
/**
 * bootstrap_api.php - A lean loader for public API endpoints.
 */

// --- 1. Core Definitions & Security ---
if (!defined('SECURITY_CHECK')) { define('SECURITY_CHECK', true); }
if (!defined('PROJECT_ROOT')) { define('PROJECT_ROOT', __DIR__); }
if (!defined('ROOT_PATH')) { define('ROOT_PATH', __DIR__); }
// Path to the vault, now located inside the project root.
if (!defined('VAULT_PATH')) { define('VAULT_PATH', __DIR__ . '/smartAdVault'); }
if (!defined('BASE_URL')) { define('BASE_URL', '/smartAd'); }

// --- 2. Guard Clause: A critical check before includes ---
if (!is_dir(VAULT_PATH)) {
    http_response_code(503); // Service Unavailable
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false, 
        'message' => 'Server Configuration Error: The secure vault directory could not be found.',
        'debug_path' => VAULT_PATH
    ]);
    exit;
}

// --- 3. API-Specific Environment Settings ---
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// --- 3.5. Load Environment Variables ---
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

// --- 4. Load Minimum Required Components ---
require_once VAULT_PATH . '/config/config.php';
require_once VAULT_PATH . '/config/database.php';
require_once VAULT_PATH . '/helpers/HelperLoader.php';
require_once VAULT_PATH . '/auth/AuthGuard.php';
// Auto-load all model files for the API.
$modelFiles = glob(__DIR__ . '/app/models/*.php');
foreach ($modelFiles as $modelFile) {
    require_once $modelFile;
}


// --- 5. Secure Session Initialization ---
if (session_status() === PHP_SESSION_NONE) {
    try {
        session_start();
    } catch (Exception $e) {
        error_log("Session start error: " . $e->getMessage());
        // Continue without session if there's an issue
    }
}