<?php
require_once __DIR__ . '/../../bootstrap.php';

// AuthGuard automatically enforces authentication
// Check specific permission for setup operations
$authGuard = new AuthGuard();
$authGuard->requirePermission('system.settings');

// Get request parameters
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$importId = $_GET['import_id'] ?? $_POST['import_id'] ?? null;
$duplicateMode = $_POST['duplicate_mode'] ?? 'skip';

// Initialize response helper
$response = new ApiResponseHelper();

try {
    $bulkUploadProcessor = new BulkUploadProcessor();
    
    switch ($action) {
        case 'upload':
            // Process file upload
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No file uploaded or upload error occurred');
            }
            
            $entityType = $_POST['type'] ?? '';
            if (empty($entityType)) {
                throw new Exception('Entity type is required');
            }
            
            $result = $bulkUploadProcessor->processUpload($_FILES['file'], $entityType);
            $response->success('File processed successfully', $result);
            break;
            
        case 'preview':
            // Get import preview
            if (empty($importId)) {
                throw new Exception('Import ID is required');
            }
            
            $result = $bulkUploadProcessor->getImportPreview($importId);
            $response->success('Preview retrieved successfully', $result);
            break;
            
        case 'commit':
            // Commit import
            if (empty($importId)) {
                throw new Exception('Import ID is required');
            }
            
            $result = $bulkUploadProcessor->commitImport($importId, $duplicateMode);
            $response->success('Import committed successfully', $result);
            break;
            
        case 'rollback':
            // Rollback import
            if (empty($importId)) {
                throw new Exception('Import ID is required');
            }
            
            $result = $bulkUploadProcessor->rollbackImport($importId);
            $response->success('Import rolled back successfully', $result);
            break;
            
        case 'report':
            // Generate import report
            if (empty($importId)) {
                throw new Exception('Import ID is required');
            }
            
            $result = $this->generateImportReport($importId);
            
            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="import_report_' . $importId . '.csv"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            
            echo $result;
            exit;
            
        default:
            throw new Exception('Invalid action specified');
    }
    
} catch (Exception $e) {
    error_log("Bulk Upload API Error: " . $e->getMessage());
    $response->error($e->getMessage());
}

/**
 * Generate import report
 */
function generateImportReport($importId) {
    try {
        $db = Database::getInstance();
        
        // Get import details
        $sql = "SELECT * FROM bulk_imports WHERE id = :import_id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':import_id', $importId, PDO::PARAM_INT);
        $stmt->execute();
        $import = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$import) {
            throw new Exception('Import record not found');
        }
        
        // Get import data
        $sql = "SELECT * FROM bulk_import_data WHERE import_id = :import_id ORDER BY row_number";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':import_id', $importId, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Generate CSV report
        $csv = '';
        
        // Add import summary
        $csv .= "Import Report\n";
        $csv .= "Import ID: {$importId}\n";
        $csv .= "Entity Type: {$import['entity_type']}\n";
        $csv .= "Filename: {$import['filename']}\n";
        $csv .= "Total Rows: {$import['total_rows']}\n";
        $csv .= "Status: {$import['status']}\n";
        $csv .= "Created: {$import['created_at']}\n";
        if ($import['completed_at']) {
            $csv .= "Completed: {$import['completed_at']}\n";
        }
        $csv .= "\n";
        
        // Add results summary
        if ($import['results']) {
            $results = json_decode($import['results'], true);
            $csv .= "Results Summary\n";
            $csv .= "Created: " . ($results['created'] ?? 0) . "\n";
            $csv .= "Updated: " . ($results['updated'] ?? 0) . "\n";
            $csv .= "Skipped: " . ($results['skipped'] ?? 0) . "\n";
            $csv .= "Errors: " . ($results['errors'] ?? 0) . "\n";
            $csv .= "\n";
        }
        
        // Add detailed row data
        $csv .= "Row Details\n";
        $csv .= "Row Number,Status,Final Status,Data,Errors,Warnings,Final Message\n";
        
        foreach ($rows as $row) {
            $data = json_decode($row['data'], true);
            $dataStr = is_array($data) ? implode('|', $data) : $data;
            
            $csv .= implode(',', [
                $row['row_number'],
                $row['status'],
                $row['final_status'] ?? '',
                '"' . str_replace('"', '""', $dataStr) . '"',
                '"' . str_replace('"', '""', $row['errors']) . '"',
                '"' . str_replace('"', '""', $row['warnings']) . '"',
                '"' . str_replace('"', '""', $row['final_message'] ?? '') . '"'
            ]) . "\n";
        }
        
        return $csv;
        
    } catch (Exception $e) {
        error_log("Generate Import Report Error: " . $e->getMessage());
        return "Error generating report: " . $e->getMessage();
    }
}
