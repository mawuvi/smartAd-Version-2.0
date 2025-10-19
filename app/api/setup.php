<?php
require_once __DIR__ . '/../../bootstrap.php';

// AuthGuard automatically enforces authentication
// Check specific permission for setup operations
$authGuard = new AuthGuard();
$authGuard->requirePermission('system.settings');

// Get request parameters
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$type = $_GET['type'] ?? $_POST['type'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? null;

// Initialize response helper
$response = new ApiResponseHelper();

try {
    $setupModel = new SetupModel();
    
    switch ($action) {
        case 'list':
            // Get paginated list of records
            $page = (int) ($_GET['page'] ?? 1);
            $limit = (int) ($_GET['limit'] ?? 20);
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            
            if (empty($type)) {
                throw new Exception('Entity type is required');
            }
            
            $result = $setupModel->getRecords($type, $page, $limit, $search, $status);
            $response->success('Records retrieved successfully', $result);
            break;
            
        case 'get':
            // Get single record
            if (empty($type) || empty($id)) {
                throw new Exception('Entity type and ID are required');
            }
            
            $record = $setupModel->getRecord($type, $id);
            if (!$record) {
                throw new Exception('Record not found');
            }
            
            $response->success('Record retrieved successfully', $record);
            break;
            
        case 'create':
            // Create new record
            if (empty($type)) {
                throw new Exception('Entity type is required');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                $data = $_POST;
            }
            
            // Validate data
            $errors = $setupModel->validateData($type, $data);
            if (!empty($errors)) {
                throw new Exception('Validation failed: ' . implode(', ', $errors));
            }
            
            // Check for duplicates
            $duplicate = $setupModel->checkDuplicate($type, $data);
            if ($duplicate) {
                throw new Exception('A record with these details already exists');
            }
            
            $id = $setupModel->createRecord($type, $data);
            $record = $setupModel->getRecord($type, $id);
            
            $response->success('Record created successfully', $record);
            break;
            
        case 'update':
            // Update existing record
            if (empty($type) || empty($id)) {
                throw new Exception('Entity type and ID are required');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                $data = $_POST;
            }
            
            // Validate data
            $errors = $setupModel->validateData($type, $data);
            if (!empty($errors)) {
                throw new Exception('Validation failed: ' . implode(', ', $errors));
            }
            
            // Check if record exists
            $existing = $setupModel->getRecord($type, $id);
            if (!$existing) {
                throw new Exception('Record not found');
            }
            
            // Check for duplicates (excluding current record)
            $duplicate = $setupModel->checkDuplicate($type, $data);
            if ($duplicate && $duplicate['id'] != $id) {
                throw new Exception('A record with these details already exists');
            }
            
            $success = $setupModel->updateRecord($type, $id, $data);
            if (!$success) {
                throw new Exception('Failed to update record');
            }
            
            $record = $setupModel->getRecord($type, $id);
            $response->success('Record updated successfully', $record);
            break;
            
        case 'delete':
            // Soft delete record
            if (empty($type) || empty($id)) {
                throw new Exception('Entity type and ID are required');
            }
            
            $success = $setupModel->deleteRecord($type, $id);
            if (!$success) {
                throw new Exception('Failed to delete record');
            }
            
            $response->success('Record deleted successfully');
            break;
            
        case 'toggle_status':
            // Toggle record status
            if (empty($type) || empty($id)) {
                throw new Exception('Entity type and ID are required');
            }
            
            $newStatus = $setupModel->toggleStatus($type, $id);
            if ($newStatus === false) {
                throw new Exception('Failed to toggle status');
            }
            
            $record = $setupModel->getRecord($type, $id);
            $response->success('Status updated successfully', $record);
            break;
            
        case 'export':
            // Export data
            if (empty($type)) {
                throw new Exception('Entity type is required');
            }
            
            $format = $_GET['format'] ?? 'csv';
            $data = $setupModel->exportData($type, $format);
            
            if ($format === 'csv') {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $type . '_export.csv"');
                echo $data;
                exit;
            } elseif ($format === 'excel') {
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment; filename="' . $type . '_export.xls"');
                echo $data;
                exit;
            }
            
            $response->success('Data exported successfully', ['data' => $data]);
            break;
            
        case 'count':
            // Get count of records
            if (empty($type)) {
                throw new Exception('Entity type is required');
            }
            
            $count = $setupModel->getCount($type);
            $response->success('Count retrieved successfully', ['count' => $count]);
            break;
            
        case 'get_or_create':
            // Get or create record (for bulk uploads)
            if (empty($type)) {
                throw new Exception('Entity type is required');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                $data = $_POST;
            }
            
            $searchData = $data['search'] ?? [];
            $createData = $data['create'] ?? null;
            
            $id = $setupModel->getOrCreateRecord($type, $searchData, $createData);
            if ($id) {
                $record = $setupModel->getRecord($type, $id);
                $response->success('Record retrieved or created successfully', $record);
            } else {
                throw new Exception('Failed to get or create record');
            }
            break;
            
        case 'check_duplicate':
            // Check for duplicate records
            if (empty($type)) {
                throw new Exception('Entity type is required');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                $data = $_POST;
            }
            
            $duplicate = $setupModel->checkDuplicate($type, $data);
            $response->success('Duplicate check completed', [
                'is_duplicate' => !empty($duplicate),
                'duplicate_record' => $duplicate
            ]);
            break;
            
        case 'validate':
            // Validate data without saving
            if (empty($type)) {
                throw new Exception('Entity type is required');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                $data = $_POST;
            }
            
            $errors = $setupModel->validateData($type, $data);
            $response->success('Validation completed', [
                'is_valid' => empty($errors),
                'errors' => $errors
            ]);
            break;
            
        default:
            throw new Exception('Invalid action specified');
    }
    
} catch (Exception $e) {
    error_log("Setup API Error: " . $e->getMessage());
    $response->error($e->getMessage());
}
