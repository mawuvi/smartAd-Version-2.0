<?php
/**
 * SmartAd Setup API (v5)
 * Location: /public/setup_api.php (Public API for setup data)
 * Standards Applied: Section 9.2 (Output Buffer Management)
 */

// At the start
ob_start();
require_once __DIR__ . '/../bootstrap_api.php';
// ... other includes ...

// After all includes, before JSON output
ob_end_clean();
ob_start();

header('Content-Type: application/json; charset=utf-8');

// ---- Helpers loaded by HelperLoader.php ----
use SmartAdVault\Helpers\ApiResponseHelper;
use SmartAdVault\Helpers\AuditLogger;

// --- Get active DB connection ---
$db = Database::getInstance();
$action = $_GET['action'] ?? '';

try {
    // --- common fetch function ---
    $fetch = function (string $table, string $label) use ($db) {
        $stmt = $db->prepare("SELECT * FROM {$table} WHERE is_active = 1 ORDER BY name ASC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (class_exists('SmartAdVault\\Helpers\\AuditLogger')) {
            AuditLogger::logOperation(
                'setup_api',
                "fetch_{$label}",
                ucfirst($label) . ' retrieved',
                null,
                AuditLogger::LEVEL_INFO,
                ['count' => count($rows)]
            );
        }
        return $rows;
    };

    switch ($action) {
        case 'get_client_types':
            $data = $fetch('setup_client_types', 'client_types');
            ApiResponseHelper::sendSuccess('Data retrieved successfully', $data);
            break;

        case 'get_industries':
            $data = $fetch('setup_industries', 'industries');
            ApiResponseHelper::sendSuccess('Data retrieved successfully', $data);
            break;

        case 'list':
            $table = $_GET['table'] ?? '';
            $allowed = [
                'setup_countries' => 'countries',
                'setup_cities'    => 'cities'
            ];
            if (!isset($allowed[$table])) {
                ApiResponseHelper::sendError('Invalid table specified', 400);
                break;
            }
            $data = $fetch($table, $allowed[$table]);
            ApiResponseHelper::sendSuccess('Data retrieved successfully', $data);
            break;

        case 'get_cities_by_country':
            $countryId = (int)($_GET['country_id'] ?? 0);
            if ($countryId <= 0) {
                ApiResponseHelper::sendError('country_id is required', 400);
                break;
            }

            $stmt = $db->prepare(
                "SELECT * FROM setup_cities 
                 WHERE country_id = :cid AND is_active = 1 
                 ORDER BY name ASC"
            );
            $stmt->execute(['cid' => $countryId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (class_exists('SmartAdVault\\Helpers\\AuditLogger')) {
                AuditLogger::logOperation(
                    'setup_api',
                    'get_cities_by_country',
                    'Cities fetched for country',
                    null,
                    AuditLogger::LEVEL_INFO,
                    ['country_id' => $countryId, 'count' => count($rows)]
                );
            }

            ApiResponseHelper::sendSuccess('Data retrieved successfully', $rows);
            break;

        default:
            ApiResponseHelper::sendError('Invalid action', 400);
            break;
    }
} catch (Throwable $e) {
    if (class_exists('SmartAdVault\\Helpers\\AuditLogger')) {
        AuditLogger::logError(
            'setup_api',
            $action ?: 'unknown',
            $e->getMessage(),
            null,
            ['trace' => $e->getTraceAsString()]
        );
    }
    ApiResponseHelper::sendError('Internal server error: ' . $e->getMessage(), 500);
} finally {
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}
