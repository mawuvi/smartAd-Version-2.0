<?php
require_once __DIR__ . '/../../bootstrap_api.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    // Require authentication for all client operations
    getAuthGuard()->requireAuthJson();
    $userId = getAuthGuard()->getCurrentUser()['id'];
    
    $clientModel = new ClientModel();

    switch ($action) {
        case 'search_clients':
            getAuthGuard()->requirePermissionJson('clients.view');
            $query = $_GET['q'] ?? '';
            $limit = intval($_GET['limit'] ?? 20);
            
            if (strlen($query) < 2) {
                ApiResponseHelper::sendError('Search query must be at least 2 characters', 400);
                break;
            }

            $clients = $clientModel->search($query, $limit);
            ApiResponseHelper::sendSuccess('Clients found', $clients);
            break;

        case 'get_client':
            getAuthGuard()->requirePermissionJson('clients.view');
            $clientId = intval($_GET['id'] ?? 0);
            
            if (!$clientId) {
                ApiResponseHelper::sendError('Client ID is required', 400);
                break;
            }

            $client = $clientModel->findById($clientId);
            if (!$client) {
                ApiResponseHelper::sendError('Client not found', 404);
                break;
            }

            ApiResponseHelper::sendSuccess('Client retrieved successfully', $client);
            break;

        case 'create_client':
            getAuthGuard()->requirePermissionJson('clients.create');
            $clientData = json_decode(file_get_contents('php://input'), true);
            
            if (!$clientData) {
                ApiResponseHelper::sendError('Invalid client data', 400);
                break;
            }

            // Add created_by from current user
            $clientData['created_by'] = getAuthGuard()->getCurrentUser()['id'];

            // Validate required fields
            $validation = $clientModel->validate($clientData);
            if (!$validation['valid']) {
                ApiResponseHelper::sendError('Validation failed: ' . implode(', ', $validation['errors']), 400);
                break;
            }

            $client = $clientModel->create($clientData);
            ApiResponseHelper::sendSuccess('Client created successfully', $client);
            break;

        case 'update_client':
            getAuthGuard()->requirePermissionJson('clients.edit');
            $clientId = intval($_GET['id'] ?? 0);
            $updateData = json_decode(file_get_contents('php://input'), true);
            
            if (!$clientId || !$updateData) {
                ApiResponseHelper::sendError('Client ID and update data are required', 400);
                break;
            }

            $updatedClient = $clientModel->update($clientId, $updateData);
            ApiResponseHelper::sendSuccess('Client updated successfully', $updatedClient);
            break;

        case 'delete_client':
            getAuthGuard()->requirePermissionJson('clients.delete');
            $clientId = intval($_GET['id'] ?? 0);
            
            if (!$clientId) {
                ApiResponseHelper::sendError('Client ID is required', 400);
                break;
            }

            $clientModel->delete($clientId);
            ApiResponseHelper::sendSuccess('Client deleted successfully');
            break;

        case 'get_clients':
            getAuthGuard()->requirePermissionJson('clients.view');
            $filters = [
                'client_type' => $_GET['client_type'] ?? null,
                'status' => $_GET['status'] ?? null,
                'limit' => intval($_GET['limit'] ?? 50),
                'offset' => intval($_GET['offset'] ?? 0)
            ];

            $clients = $clientModel->findAll($filters);
            ApiResponseHelper::sendSuccess('Clients retrieved successfully', $clients);
            break;

        case 'list_clients':
            getAuthGuard()->requirePermissionJson('clients.view');
            // Build filters from query parameters
            $filters = [];
            if (!empty($_GET['search'])) {
                $filters['search'] = $_GET['search'];
            }
            if (!empty($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            if (!empty($_GET['client_type'])) {
                $filters['client_type'] = $_GET['client_type'];
            }
            if (!empty($_GET['credit_rating'])) {
                $filters['credit_rating'] = $_GET['credit_rating'];
            }
            if (!empty($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (!empty($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }
            
            // Pagination parameters
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 20);
            $sort = $_GET['sort'] ?? 'company_name';
            $sortDirection = $_GET['sort_direction'] ?? 'ASC';
            
            $filters['page'] = $page;
            $filters['limit'] = $limit;
            $filters['sort'] = $sort;
            $filters['sort_direction'] = $sortDirection;
            
            $result = $clientModel->getAllWithPagination($filters);
            
            // Log the operation
            if (class_exists('SmartAdVault\\Helpers\\AuditLogger')) {
                AuditLogger::logOperation(
                    'client_api',
                    'list_clients',
                    'Clients listed',
                    $userId,
                    AuditLogger::LEVEL_INFO,
                    [
                        'filters' => $filters,
                        'page' => $page,
                        'results_count' => count($result['clients']),
                        'total' => $result['total'],
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    ]
                );
            }
            
            ApiResponseHelper::sendSuccess('Clients retrieved successfully', [
                'clients' => $result['clients'],
                'count' => count($result['clients']),
                'total' => $result['total'],
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($result['total'] / $limit)
            ]);
            break;

        case 'get_statistics':
            getAuthGuard()->requirePermissionJson('clients.view');
            $stats = $clientModel->getStatistics();
            
            // Log the operation
            if (class_exists('SmartAdVault\\Helpers\\AuditLogger')) {
                AuditLogger::logOperation(
                    'client_api',
                    'get_statistics',
                    'Client statistics retrieved',
                    $userId,
                    AuditLogger::LEVEL_INFO,
                    [
                        'total_clients' => $stats['total_clients'],
                        'active_clients' => $stats['active_clients'],
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    ]
                );
            }
            
            ApiResponseHelper::sendSuccess('Statistics retrieved successfully', $stats);
            break;

        case 'export_clients':
            getAuthGuard()->requirePermissionJson('clients.view');
            // Build filters for export
            $filters = [];
            if (!empty($_GET['search'])) {
                $filters['search'] = $_GET['search'];
            }
            if (!empty($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            if (!empty($_GET['client_type'])) {
                $filters['client_type'] = $_GET['client_type'];
            }
            if (!empty($_GET['credit_rating'])) {
                $filters['credit_rating'] = $_GET['credit_rating'];
            }
            if (!empty($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (!empty($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }
            
            $clients = $clientModel->getAllForExport($filters);
            $csvData = $clientModel->generateClientsCSV($clients);
            
            // Log the operation
            if (class_exists('SmartAdVault\\Helpers\\AuditLogger')) {
                AuditLogger::logOperation(
                    'client_api',
                    'export_clients',
                    'Clients exported to CSV',
                    $userId,
                    AuditLogger::LEVEL_INFO,
                    [
                        'filters' => $filters,
                        'exported_count' => count($clients),
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    ]
                );
            }
            
            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="clients_' . date('Y-m-d_H-i-s') . '.csv"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            echo $csvData;
            exit;
            break;

        case 'get_approved_discounts':
            $clientId = intval($_GET['client_id'] ?? 0);
            
            if (!$clientId) {
                ApiResponseHelper::sendError('Client ID is required', 400);
                break;
            }

            // For now, return empty array - discount system will be implemented later
            $discounts = [];
            ApiResponseHelper::sendSuccess('Approved discounts retrieved', $discounts);
            break;

        default:
            ApiResponseHelper::sendError('Invalid action', 400);
            break;
    }

} catch (Exception $e) {
    error_log("Client API Error: " . $e->getMessage());
    ApiResponseHelper::sendError('Failed to process request: ' . $e->getMessage(), 500);
}
