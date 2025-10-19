<?php
require_once __DIR__ . '/../../bootstrap.php';

// AuthGuard automatically enforces authentication
// Check specific permission for setup operations
$authGuard = new AuthGuard();
$authGuard->requirePermission('system.settings');

// Get request parameters
$type = $_GET['type'] ?? '';
$format = $_GET['format'] ?? 'csv';

// Initialize response helper
$response = new ApiResponseHelper();

try {
    if (empty($type)) {
        throw new Exception('Entity type is required');
    }
    
    $templateGenerator = new TemplateGenerator();
    
    // Generate template
    $templateData = $templateGenerator->generateTemplate($type, $format);
    
    // Set appropriate headers based on format
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $type . '_template.csv"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        
        echo $templateData;
        exit;
        
    } elseif ($format === 'excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $type . '_template.xls"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        
        echo $templateData;
        exit;
        
    } else {
        throw new Exception('Unsupported format. Use csv or excel.');
    }
    
} catch (Exception $e) {
    error_log("Template API Error: " . $e->getMessage());
    
    // If headers haven't been sent yet, return JSON error
    if (!headers_sent()) {
        $response->error($e->getMessage());
    } else {
        // Headers already sent, output error message
        echo "Error: " . $e->getMessage();
    }
}
