<?php
/**
 * Rate Management API Endpoint
 */

// Use the main bootstrap file, which handles security.
require_once __DIR__ . '/../../bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
$action = $_REQUEST['action'] ?? null;
$refModel = new ReferenceModel();

try {
    switch ($action) {
        case 'get_publications':
            $data = $refModel->getAll('publications');
            ApiResponseHelper::sendSuccess('Publications retrieved', $data);
            break;

        case 'get_color_types':
            $data = $refModel->getAll('color_types');
            ApiResponseHelper::sendSuccess('Color types retrieved', $data);
            break;
            
        // Add other cases for categories, sizes, etc. here.

        default:
            ApiResponseHelper::sendError('Invalid action for rate_api.', 400);
            break;
    }
} catch (Exception $e) {
    error_log("Rate API Error: " . $e->getMessage());
    ApiResponseHelper::sendError('An internal server error occurred.', 500);
}