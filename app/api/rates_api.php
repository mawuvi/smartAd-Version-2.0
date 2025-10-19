<?php
/**
 * Rates API - Handles all rate-related operations
 * Updated: 2025-01-09 - Simplified dependency resolution with binary logic
 */

require_once __DIR__ . '/../../bootstrap.php';

// Initialize authentication variables
$authGuard = getAuthGuard();
$currentUser = $authGuard->getCurrentUser();

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// If no action in GET/POST, try to get from JSON body
if (empty($action) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
}

try {
    switch ($action) {
        case 'get':
        case 'get_rates':
            getRates();
            break;
        case 'create':
        case 'create_rate':
            createRate();
            break;
        case 'update':
        case 'update_rate':
            updateRate();
            break;
        case 'delete':
        case 'delete_rate':
            deleteRate();
            break;
        case 'export':
        case 'export_rates':
            exportRates();
            break;
        case 'download_template':
            downloadTemplate();
            break;
        case 'bulk_upload':
        case 'upload_file':
            bulkUploadRates();
            break;
        case 'get_staging':
        case 'get_staging_rates':
            getStagingRates();
            break;
        case 'process_staging':
        case 'process_staging_rates':
            processStagingRates();
            break;
        case 'check_existing_staging':
            checkExistingStagingSessions();
            break;
        case 'purge_user_staging':
            purgeUserStagingSessions();
            break;
        case 'get_user_staging_sessions':
            getUserStagingSessions();
            break;
        case 'update_staging_merge':
            updateStagingMerge();
            break;
        case 'delete_staging_session':
            deleteStagingSession();
            break;
        case 'get_filter_data':
            getFilterData();
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get all rates with related data
 */
function getRates() {
    global $authGuard, $currentUser;
    
    header('Content-Type: application/json');
    
    // Check permission
    $authGuard->requirePermission('setup.rates.view');
    
    $db = Database::getInstance();
    
    // Get filter parameters
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $publication = $_GET['publication'] ?? '';
    $category = $_GET['category'] ?? '';
    $size = $_GET['size'] ?? '';
    $position = $_GET['position'] ?? '';
    $colorType = $_GET['color_type'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $whereConditions = ['r.deleted_at IS NULL'];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(p.name LIKE ? OR p.code LIKE ? OR ac.name LIKE ? OR asz.name LIKE ? OR pp.name LIKE ? OR ct.name LIKE ?)";
        $searchParam = "%{$search}%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
    }
    
    if (!empty($status)) {
        $whereConditions[] = "r.status = ?";
        $params[] = $status;
    }
    
    if (!empty($publication)) {
        $whereConditions[] = "p.id = ?";
        $params[] = $publication;
    }
    
    if (!empty($category)) {
        $whereConditions[] = "ac.id = ?";
        $params[] = $category;
    }
    
    if (!empty($size)) {
        $whereConditions[] = "asz.id = ?";
        $params[] = $size;
    }
    
    if (!empty($position)) {
        $whereConditions[] = "pp.id = ?";
        $params[] = $position;
    }
    
    if (!empty($colorType)) {
        $whereConditions[] = "ct.id = ?";
        $params[] = $colorType;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Get total count
    $countStmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM rates r
        LEFT JOIN publications p ON r.publication_id = p.id
        LEFT JOIN ad_categories ac ON r.ad_category_id = ac.id
        LEFT JOIN ad_sizes asz ON r.ad_size_id = asz.id
        LEFT JOIN page_positions pp ON r.page_position_id = pp.id
        LEFT JOIN color_types ct ON r.color_type_id = ct.id
        WHERE {$whereClause}
    ");
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get rates with pagination
    $stmt = $db->prepare("
        SELECT 
            r.id,
            r.base_rate,
            r.effective_from,
            r.effective_to,
            r.status,
            r.notes,
            r.created_at,
            r.updated_at,
            p.name as publication_name,
            p.code as publication_code,
            ac.name as ad_category,
            asz.name as ad_size,
            pp.name as page_position,
            ct.name as color_type
        FROM rates r
        LEFT JOIN publications p ON r.publication_id = p.id
        LEFT JOIN ad_categories ac ON r.ad_category_id = ac.id
        LEFT JOIN ad_sizes asz ON r.ad_size_id = asz.id
        LEFT JOIN page_positions pp ON r.page_position_id = pp.id
        LEFT JOIN color_types ct ON r.color_type_id = ct.id
        WHERE {$whereClause}
        ORDER BY r.created_at DESC
        LIMIT {$limit} OFFSET {$offset}
    ");
    
    $stmt->execute($params);
    $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $rates,
        'pagination' => [
            'total' => (int)$totalCount,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($totalCount / $limit)
        ]
    ]);
}

/**
 * Create new rate
 */
function createRate() {
    global $authGuard, $currentUser;
    
    header('Content-Type: application/json');
    
    // Check permission
    $authGuard->requirePermission('setup.rates.create');
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $requiredFields = ['publication_code', 'publication_name', 'ad_category', 'ad_size', 'page_position', 'color_type', 'base_rate', 'effective_from', 'effective_to', 'status'];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            throw new Exception("Field '{$field}' is required");
        }
    }
    
    $db = Database::getInstance();
    
    // Resolve dependencies
    $publicationId = resolveOrCreatePublication($db, $data['publication_code'], $data['publication_name'], $currentUser['id']);
    $categoryId = resolveOrCreateAdCategory($db, $data['ad_category'], $currentUser['id']);
    $sizeId = resolveOrCreateAdSize($db, $data['ad_size'], $currentUser['id']);
    $positionId = resolveOrCreatePagePosition($db, $data['page_position'], $currentUser['id']);
    $colorTypeId = resolveOrCreateColorType($db, $data['color_type'], $currentUser['id']);
    
    // Check for duplicates
    $duplicateCheck = $db->prepare("
        SELECT id FROM rates 
        WHERE publication_id = ? 
          AND color_type_id = ? 
          AND ad_category_id = ? 
          AND ad_size_id = ? 
          AND page_position_id = ? 
          AND effective_from = ?
          AND deleted_at IS NULL
    ");
    
    $duplicateCheck->execute([
        $publicationId, $colorTypeId, $categoryId, $sizeId, $positionId, $data['effective_from']
    ]);
    
    if ($duplicateCheck->fetch()) {
        throw new Exception('Rate already exists for this combination');
    }
    
    // Insert rate
    $stmt = $db->prepare("
        INSERT INTO rates 
        (publication_id, color_type_id, ad_category_id, ad_size_id, page_position_id, 
         base_rate, effective_from, effective_to, status, notes, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $success = $stmt->execute([
        $publicationId, $colorTypeId, $categoryId, $sizeId, $positionId,
        floatval($data['base_rate']), $data['effective_from'], $data['effective_to'],
        $data['status'], $data['notes'] ?? '', $currentUser['id']
    ]);
    
    if ($success) {
        $rateId = $db->lastInsertId();
        
        // Log audit trail
        logAudit($db, 'rates', $rateId, 'CREATE', [], $data, $currentUser['id']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Rate created successfully',
            'id' => $rateId
        ]);
    } else {
        throw new Exception('Failed to create rate');
    }
}

/**
 * Update existing rate
 */
function updateRate() {
    global $authGuard, $currentUser;
    
    header('Content-Type: application/json');
    
    // Check permission
    $authGuard->requirePermission('setup.rates.update');
    
    $data = json_decode(file_get_contents('php://input'), true);
    $rateId = $data['id'] ?? null;
    
    if (!$rateId) {
        throw new Exception('Rate ID is required');
    }
    
    $db = Database::getInstance();
    
    // Check if rate is used in bookings
    if (isRateUsed($db, $rateId)) {
        throw new Exception('Cannot edit rate that has been used in bookings.');
    }
    
    // Validate required fields
    $requiredFields = ['publication_code', 'publication_name', 'ad_category', 'ad_size', 'page_position', 'color_type', 'base_rate', 'effective_from', 'effective_to', 'status'];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            throw new Exception("Field '{$field}' is required");
        }
    }
    
    $db = Database::getInstance();
    
    // Get old values for audit trail
    $oldStmt = $db->prepare("SELECT * FROM rates WHERE id = ? AND deleted_at IS NULL");
    $oldStmt->execute([$rateId]);
    $oldValues = $oldStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$oldValues) {
        throw new Exception('Rate not found');
    }
    
    // Resolve dependencies
    $publicationId = resolveOrCreatePublication($db, $data['publication_code'], $data['publication_name'], $currentUser['id']);
    $categoryId = resolveOrCreateAdCategory($db, $data['ad_category'], $currentUser['id']);
    $sizeId = resolveOrCreateAdSize($db, $data['ad_size'], $currentUser['id']);
    $positionId = resolveOrCreatePagePosition($db, $data['page_position'], $currentUser['id']);
    $colorTypeId = resolveOrCreateColorType($db, $data['color_type'], $currentUser['id']);
    
    // Check for duplicates (excluding current record)
    $duplicateCheck = $db->prepare("
        SELECT id FROM rates 
        WHERE publication_id = ? 
          AND color_type_id = ? 
          AND ad_category_id = ? 
          AND ad_size_id = ? 
          AND page_position_id = ? 
          AND effective_from = ?
          AND id != ?
          AND deleted_at IS NULL
    ");
    
    $duplicateCheck->execute([
        $publicationId, $colorTypeId, $categoryId, $sizeId, $positionId, $data['effective_from'], $rateId
    ]);
    
    if ($duplicateCheck->fetch()) {
        throw new Exception('Rate already exists for this combination');
    }
    
    // Update rate
    $stmt = $db->prepare("
        UPDATE rates SET 
            publication_id = ?, color_type_id = ?, ad_category_id = ?, ad_size_id = ?, 
            page_position_id = ?, base_rate = ?, effective_from = ?, effective_to = ?, 
            status = ?, notes = ?, updated_by = ?
        WHERE id = ? AND deleted_at IS NULL
    ");
    
    $success = $stmt->execute([
        $publicationId, $colorTypeId, $categoryId, $sizeId, $positionId,
        floatval($data['base_rate']), $data['effective_from'], $data['effective_to'],
        $data['status'], $data['notes'] ?? '', $currentUser['id'], $rateId
    ]);
    
    if ($success) {
        // Log audit trail
        logAudit($db, 'rates', $rateId, 'UPDATE', $oldValues, $data, $currentUser['id']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Rate updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update rate');
    }
}

/**
 * Delete rate (soft delete)
 */
function deleteRate() {
    global $authGuard, $currentUser;
    
    // Set JSON header for this function
    header('Content-Type: application/json');
    
    // Check permission
    $authGuard->requirePermission('setup.rates.delete');
    
    $data = json_decode(file_get_contents('php://input'), true);
    $rateId = $data['id'] ?? null;
    
    if (!$rateId) {
        throw new Exception('Rate ID is required');
    }
    
    $db = Database::getInstance();
    
    // Get old values for audit trail
    $oldStmt = $db->prepare("SELECT * FROM rates WHERE id = ? AND deleted_at IS NULL");
    $oldStmt->execute([$rateId]);
    $oldValues = $oldStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$oldValues) {
        throw new Exception('Rate not found');
    }
    
    // Check if rate is used in bookings
    if (isRateUsed($db, $rateId)) {
        throw new Exception('Cannot delete rate that has been used in bookings. You can set it as inactive instead.');
    }
    
    // Soft delete
    $stmt = $db->prepare("UPDATE rates SET deleted_at = NOW(), status = 'inactive', deleted_by = ? WHERE id = ?");
    $success = $stmt->execute([$currentUser['id'], $rateId]);
    
    if ($success) {
        // Log audit trail
        logAudit($db, 'rates', $rateId, 'DELETE', $oldValues, [], $currentUser['id']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Rate deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete rate');
    }
}

/**
 * Export rates to CSV
 */
function exportRates() {
    global $authGuard, $currentUser;
    
    // Check permission
    $authGuard->requirePermission('setup.rates.export');
    
    $db = Database::getInstance();
    
    $stmt = $db->query("
        SELECT 
            p.code as publication_code,
            p.name as publication_name,
            ac.name as ad_category,
            asz.name as ad_size,
            pp.name as page_position,
            ct.name as color_type,
            r.base_rate,
            r.effective_from,
            r.effective_to,
            r.status,
            r.notes
        FROM rates r
        LEFT JOIN publications p ON r.publication_id = p.id
        LEFT JOIN ad_categories ac ON r.ad_category_id = ac.id
        LEFT JOIN ad_sizes asz ON r.ad_size_id = asz.id
        LEFT JOIN page_positions pp ON r.page_position_id = pp.id
        LEFT JOIN color_types ct ON r.color_type_id = ct.id
        WHERE r.deleted_at IS NULL
        ORDER BY r.created_at DESC
    ");
    
    $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="rates_export_' . date('Y-m-d_H-i-s') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Write CSV headers
    fputcsv($output, [
        'Publication Code', 'Publication Name', 'Ad Category', 'Ad Size',
        'Page Position', 'Color Type', 'Base Rate', 'Effective From',
        'Effective To', 'Status', 'Notes'
    ]);
    
    // Write data rows
    foreach ($rates as $rate) {
        fputcsv($output, [
            $rate['publication_code'],
            $rate['publication_name'],
            $rate['ad_category'],
            $rate['ad_size'],
            $rate['page_position'],
            $rate['color_type'],
            $rate['base_rate'],
            $rate['effective_from'],
            $rate['effective_to'],
            $rate['status'],
            $rate['notes']
        ]);
    }
    
    fclose($output);
}

/**
 * Download template for bulk upload
 */
function downloadTemplate() {
    global $authGuard, $currentUser;
    
    // Check permission
    $authGuard->requirePermission('setup.rates.create');
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="rates_template_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Write CSV headers
    fputcsv($output, [
        'Publication Code', 'Publication Name', 'Ad Category', 'Ad Size',
        'Page Position', 'Color Type', 'Base Rate', 'Effective From',
        'Effective To', 'Status', 'Notes'
    ]);
    
    // Write sample data rows
    fputcsv($output, ['DG', 'Daily Graphic', 'Display', 'Full Page', 'Front Page', 'Color', '500.00', '2024-01-01', '2024-12-31', 'active', 'Sample rate']);
    fputcsv($output, ['GT', 'Ghanaian Times', 'Classified', 'Half Page', 'Inside', 'B&W', '250.00', '2024-01-01', '2024-12-31', 'active', 'Sample rate']);
    
    fclose($output);
}

/**
 * Bulk upload rates with staging
 */
function bulkUploadRates() {
    global $authGuard, $currentUser;
    
    header('Content-Type: application/json');
    
    // Check permission
    $authGuard->requirePermission('setup.rates.create');
    
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }
    
    $file = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    
    // Generate unique session ID
    $sessionId = 'upload_' . time() . '_' . uniqid();
    
    $db = Database::getInstance();
    
    $results = [
        'ok_count' => 0,
        'duplicate_count' => 0,
        'error_count' => 0,
        'total_rows' => 0,
        'session_id' => $sessionId
    ];
    
    try {
        // Read CSV file
        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new Exception('Could not open uploaded file');
        }
        
        // Skip header row
        fgetcsv($handle);
        
        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $results['total_rows']++;
            
            // Validate row data
            $validation = validateStagingRow($row);
            
            if (!$validation['valid']) {
                $results['error_count']++;
                insertStagingRow($db, $sessionId, $rowNumber, $row, 'error', implode(', ', $validation['errors']), $currentUser['id']);
                continue;
            }
            
            // Check for duplicates
            $duplicateCheck = checkStagingDuplicate($db, $validation['data']);
            
            if ($duplicateCheck['is_duplicate']) {
                $results['duplicate_count']++;
                insertStagingRow($db, $sessionId, $rowNumber, $row, 'duplicate', $duplicateCheck['message'], $currentUser['id']);
            } else {
                $status = 'ok';
                $message = 'Valid rate';
                $results['ok_count']++;
                insertStagingRow($db, $sessionId, $rowNumber, $row, $status, $message, $currentUser['id']);
            }
        }
        
        fclose($handle);
        
    } catch (Exception $e) {
        throw new Exception('Failed to parse file: ' . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'File uploaded and validated successfully. Processed ' . $results['total_rows'] . ' rows.',
        'session_id' => $sessionId,
        'data' => $results
    ]);
}

/**
 * Validate staging row data
 */
function validateStagingRow($row) {
    $errors = [];
    
    // Load required helpers
    require_once __DIR__ . '/../../smartAdVault/helpers/DateHelper.php';
    require_once __DIR__ . '/../../smartAdVault/helpers/DataStandardizationHelper.php';
    
    // Check required fields (corrected array indices for CSV without ID column)
    if (empty($row[0])) $errors[] = 'Publication code is required';
    if (empty($row[1])) $errors[] = 'Publication name is required';
    if (empty($row[2])) $errors[] = 'Ad category is required';
    if (empty($row[3])) $errors[] = 'Ad size is required';
    if (empty($row[4])) $errors[] = 'Page position is required';
    if (empty($row[5])) $errors[] = 'Color type is required';
    if (empty($row[6]) || !is_numeric($row[6])) $errors[] = 'Base rate must be a valid number';
    if (empty($row[7])) $errors[] = 'Effective from date is required';
    
    // Validate date format (auto-detect any supported format)
    if (!empty($row[7])) {
        $dateFrom = trim($row[7]);
        if (!DateHelper::isValidDate($dateFrom)) {
            $errors[] = 'Invalid effective from date format. Supported formats: ' . implode(', ', array_values(DateHelper::getSupportedFormats()));
        }
    }
    if (!empty($row[8])) {
        $dateTo = trim($row[8]);
        if (!DateHelper::isValidDate($dateTo)) {
            $errors[] = 'Invalid effective to date format. Supported formats: ' . implode(', ', array_values(DateHelper::getSupportedFormats()));
        }
    }
    
    // Check for publication duplicates/similar names (binary logic)
    if (!empty($row[1])) {
        $publicationName = trim($row[1]);
        
        $similarPublications = DataStandardizationHelper::findSimilarPublications($publicationName);
        
        if (!empty($similarPublications)) {
            $topMatch = $similarPublications[0];
            if ($topMatch['similarity'] < 100) {
                // Fuzzy match - FAIL validation
                $errors[] = "Publication '{$publicationName}' not found. Did you mean '{$topMatch['name']}' (Code: {$topMatch['code']})? Please use the exact name.";
            }
            // If similarity === 100, it's a perfect match - will auto-use, continue validation
        }
    }
    
    // Check for similar dependencies (categories, sizes, positions, colors) - binary logic
    // Check ad_category for similar matches
    if (!empty($row[2])) {
        $similarCategories = DataStandardizationHelper::findSimilarItems(trim($row[2]), 'ad_categories');
        if (!empty($similarCategories)) {
            $topMatch = $similarCategories[0];
            if ($topMatch['similarity'] < 100) {
                $errors[] = "Ad category '{$row[2]}' not found. Did you mean '{$topMatch['name']}'? Please use the exact name.";
            }
        }
    }
    
    // Check ad_size for similar matches
    if (!empty($row[3])) {
        $similarSizes = DataStandardizationHelper::findSimilarItems(trim($row[3]), 'ad_sizes');
        if (!empty($similarSizes)) {
            $topMatch = $similarSizes[0];
            if ($topMatch['similarity'] < 100) {
                $errors[] = "Ad size '{$row[3]}' not found. Did you mean '{$topMatch['name']}'? Please use the exact name.";
            }
        }
    }
    
    // Check page_position for similar matches
    if (!empty($row[4])) {
        $similarPositions = DataStandardizationHelper::findSimilarItems(trim($row[4]), 'page_positions');
        if (!empty($similarPositions)) {
            $topMatch = $similarPositions[0];
            if ($topMatch['similarity'] < 100) {
                $errors[] = "Page position '{$row[4]}' not found. Did you mean '{$topMatch['name']}'? Please use the exact name.";
            }
        }
    }
    
    // Check color_type for similar matches
    if (!empty($row[5])) {
        $similarColors = DataStandardizationHelper::findSimilarItems(trim($row[5]), 'color_types');
        if (!empty($similarColors)) {
            $topMatch = $similarColors[0];
            if ($topMatch['similarity'] < 100) {
                $errors[] = "Color type '{$row[5]}' not found. Did you mean '{$topMatch['name']}'? Please use the exact name.";
            }
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => [
            'publication_code' => $row[0] ?? '',
            'publication_name' => $row[1] ?? '',
            'ad_category' => $row[2] ?? '',
            'ad_size' => $row[3] ?? '',
            'page_position' => $row[4] ?? '',
            'color_type' => $row[5] ?? '',
            'base_rate' => $row[6] ?? '',
            'effective_from' => DateHelper::toDatabase($row[7] ?? ''),
            'effective_to' => DateHelper::toDatabase($row[8] ?? ''),
            'status' => $row[9] ?? 'active',
            'notes' => $row[10] ?? ''
        ]
    ];
}

/**
 * Check for duplicates in staging data
 */
function checkStagingDuplicate($db, $data) {
    // Check if rate already exists in main rates table
    $stmt = $db->prepare("
        SELECT r.id 
        FROM rates r
        LEFT JOIN publications p ON r.publication_id = p.id
        LEFT JOIN ad_categories ac ON r.ad_category_id = ac.id
        LEFT JOIN ad_sizes asz ON r.ad_size_id = asz.id
        LEFT JOIN page_positions pp ON r.page_position_id = pp.id
        LEFT JOIN color_types ct ON r.color_type_id = ct.id
        WHERE p.code = ? 
          AND ac.name = ? 
          AND asz.name = ? 
          AND pp.name = ? 
          AND ct.name = ? 
          AND r.effective_from = ?
          AND r.deleted_at IS NULL
    ");
    
    $stmt->execute([
        $data['publication_code'],
        $data['ad_category'],
        $data['ad_size'],
        $data['page_position'],
        $data['color_type'],
        $data['effective_from']
    ]);
    
    if ($stmt->fetch()) {
        return [
            'is_duplicate' => true,
            'message' => 'Rate already exists in main rates table'
        ];
    }
    
    return ['is_duplicate' => false];
}

/**
 * Insert row into staging table
 */
function insertStagingRow($db, $sessionId, $rowNumber, $row, $status, $message, $userId) {
    // Simplified function - no similar match parameters needed
    $stmt = $db->prepare("
        INSERT INTO rates_staging 
        (upload_session_id, row_number, publication_code, publication_name, 
         ad_category, ad_size, page_position, color_type, base_rate, 
         effective_from, effective_to, validation_status, validation_message, 
         notes, uploaded_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $sessionId,
        $rowNumber,
        $row[0] ?? '',  // publication_code
        $row[1] ?? '',  // publication_name
        $row[2] ?? '',  // ad_category
        $row[3] ?? '',  // ad_size
        $row[4] ?? '',  // page_position
        $row[5] ?? '',  // color_type
        $row[6] ?? '',  // base_rate
        DateHelper::toDatabase($row[7] ?? ''),  // effective_from (auto-converted)
        DateHelper::toDatabase($row[8] ?? ''),  // effective_to (auto-converted)
        $status,
        $message,
        $row[10] ?? '', // notes (corrected index)
        $userId  // Track uploader
    ]);
}

/**
 * Get staging rates by session ID
 */
function getStagingRates() {
    global $authGuard, $currentUser;
    
    header('Content-Type: application/json');
    
    $sessionId = $_GET['session_id'] ?? '';
    if (empty($sessionId)) {
        throw new Exception('Session ID is required');
    }
    
    $db = Database::getInstance();
    
    $stmt = $db->prepare("
        SELECT * FROM rates_staging 
        WHERE upload_session_id = ? 
        ORDER BY row_number ASC
    ");
    
    $stmt->execute([$sessionId]);
    $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $rates
    ]);
}

/**
 * Process selected staging rates
 */
function processStagingRates() {
    global $authGuard, $currentUser;
    
    header('Content-Type: application/json');
    
    $sessionId = $_POST['session_id'] ?? '';
    $selectedIdsJson = $_POST['selected_ids'] ?? '[]';
    $selectedIds = json_decode($selectedIdsJson, true) ?? [];
    
    if (empty($sessionId)) {
        throw new Exception('Session ID is required');
    }
    
    if (empty($selectedIds)) {
        throw new Exception('No rates selected for processing');
    }
    
    $db = Database::getInstance();
    
    // Get selected staging rates
    $placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';
    $stmt = $db->prepare("
        SELECT * FROM rates_staging 
        WHERE upload_session_id = ? AND id IN ($placeholders) AND validation_status = 'ok'
    ");
    
    $stmt->execute(array_merge([$sessionId], $selectedIds));
    $stagingRates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $results = [
        'processed' => 0,
        'skipped' => 0,
        'errors' => [],
        'processed_ids' => []
    ];
    
    foreach ($stagingRates as $stagingRate) {
        try {
            $rateId = processStagingRate($db, $stagingRate, $currentUser['id']);
            $results['processed']++;
            $results['processed_ids'][] = $stagingRate['id'];
        } catch (Exception $e) {
            $results['skipped']++;
            $results['errors'][] = "Row {$stagingRate['row_number']}: " . $e->getMessage();
        }
    }
    
    // NEW: Auto-purge successfully processed staging rows
    if (!empty($results['processed_ids'])) {
        $deletePlaceholders = str_repeat('?,', count($results['processed_ids']) - 1) . '?';
        $deleteStmt = $db->prepare("
            DELETE FROM rates_staging 
            WHERE upload_session_id = ? AND id IN ($deletePlaceholders)
        ");
        $deleteStmt->execute(array_merge([$sessionId], $results['processed_ids']));
        $results['purged'] = $deleteStmt->rowCount();
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Processed {$results['processed']} rates successfully",
        'data' => $results
    ]);
}

/**
 * Process individual staging rate with dependency resolution
 */
function processStagingRate($db, $stagingRate, $userId) {
    // Simply resolve or create - no user decisions needed (binary logic)
    $publicationId = resolveOrCreatePublication($db, $stagingRate['publication_code'], $stagingRate['publication_name'], $userId);
    
    // Resolve ad_category_id
    $categoryId = resolveOrCreateAdCategory($db, $stagingRate['ad_category'], $userId);
    
    // Resolve ad_size_id
    $sizeId = resolveOrCreateAdSize($db, $stagingRate['ad_size'], $userId);
    
    // Resolve page_position_id
    $positionId = resolveOrCreatePagePosition($db, $stagingRate['page_position'], $userId);
    
    // Resolve color_type_id
    $colorTypeId = resolveOrCreateColorType($db, $stagingRate['color_type'], $userId);
    
    // Check if rate already exists
    $duplicateCheck = $db->prepare("
        SELECT id FROM rates 
        WHERE publication_id = ? 
          AND color_type_id = ? 
          AND ad_category_id = ? 
          AND ad_size_id = ? 
          AND page_position_id = ? 
          AND effective_from = ?
          AND deleted_at IS NULL
    ");
    
    $duplicateCheck->execute([
        $publicationId, $colorTypeId, $categoryId, $sizeId, $positionId, $stagingRate['effective_from']
    ]);
    
    if ($duplicateCheck->fetch()) {
        throw new Exception('Rate already exists for this combination');
    }
    
    // Insert rate
    $stmt = $db->prepare("
        INSERT INTO rates 
        (publication_id, color_type_id, ad_category_id, ad_size_id, page_position_id, 
         base_rate, effective_from, effective_to, status, notes, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $success = $stmt->execute([
        $publicationId, $colorTypeId, $categoryId, $sizeId, $positionId,
        floatval($stagingRate['base_rate']), $stagingRate['effective_from'], $stagingRate['effective_to'],
        $stagingRate['status'] ?? 'active', $stagingRate['notes'] ?? '', $userId
    ]);
    
    if (!$success) {
        throw new Exception('Failed to create rate');
    }
    
    $rateId = $db->lastInsertId();
    
    // Log audit trail
    logAudit($db, 'rates', $rateId, 'CREATE', [], $stagingRate, $userId);
    
    return $rateId;
}

/**
 * Check if user has existing staging sessions
 */
function checkExistingStagingSessions() {
    global $authGuard, $currentUser;
    
    header('Content-Type: application/json');
    
    $db = Database::getInstance();
    
    $stmt = $db->prepare("
        SELECT 
            COUNT(DISTINCT upload_session_id) as session_count,
            COUNT(*) as total_rows,
            MIN(created_at) as oldest_session
        FROM rates_staging 
        WHERE uploaded_by = ?
    ");
    
    $stmt->execute([$currentUser['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'has_existing' => $result['session_count'] > 0,
        'session_count' => (int)$result['session_count'],
        'total_rows' => (int)$result['total_rows'],
        'oldest_session' => $result['oldest_session']
    ]);
}

/**
 * Purge all staging sessions for current user
 */
function purgeUserStagingSessions() {
    global $authGuard, $currentUser;
    
    header('Content-Type: application/json');
    
    $db = Database::getInstance();
    
    // Count before deletion
    $countStmt = $db->prepare("SELECT COUNT(*) as count FROM rates_staging WHERE uploaded_by = ?");
    $countStmt->execute([$currentUser['id']]);
    $beforeCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Delete all staging data for this user
    $stmt = $db->prepare("DELETE FROM rates_staging WHERE uploaded_by = ?");
    $stmt->execute([$currentUser['id']]);
    $deletedCount = $stmt->rowCount();
    
    // Log the purge operation
    error_log("[STAGING_PURGE] User {$currentUser['id']} purged {$deletedCount} staging rows");
    
    echo json_encode([
        'success' => true,
        'deleted' => $deletedCount,
        'message' => "Cleared {$deletedCount} pending staging rows"
    ]);
}

/**
 * Get user's staging sessions
 */
function getUserStagingSessions() {
    global $authGuard, $currentUser;
    
    header('Content-Type: application/json');
    
    $db = Database::getInstance();
    
    $stmt = $db->prepare("
        SELECT 
            upload_session_id,
            MIN(created_at) as uploaded_at,
            COUNT(*) as total_rows,
            SUM(CASE WHEN validation_status = 'ok' THEN 1 ELSE 0 END) as ok_count,
            SUM(CASE WHEN validation_status = 'warning' THEN 1 ELSE 0 END) as warning_count,
            SUM(CASE WHEN validation_status = 'duplicate' THEN 1 ELSE 0 END) as duplicate_count,
            SUM(CASE WHEN validation_status = 'error' THEN 1 ELSE 0 END) as error_count,
            SUM(CASE WHEN is_selected = TRUE THEN 1 ELSE 0 END) as selected_count
        FROM rates_staging 
        WHERE uploaded_by = ?
        GROUP BY upload_session_id
        ORDER BY uploaded_at DESC
        LIMIT 10
    ");
    
    $stmt->execute([$currentUser['id']]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $sessions
    ]);
}

/**
 * Update staging merge action (legacy - not used in binary logic)
 */
function updateStagingMerge() {
    global $authGuard, $currentUser;
    
    header('Content-Type: application/json');
    
    $data = json_decode(file_get_contents('php://input'), true);
    $sessionId = $data['session_id'];
    $rowId = $data['row_id'];
    $mergeAction = $data['merge_action'];
    $selectedPublicationId = $data['selected_publication_id'] ?? null;
    
    $db = Database::getInstance();
    
    $stmt = $db->prepare("
        UPDATE rates_staging 
        SET merge_action = ?, selected_publication_id = ?
        WHERE upload_session_id = ? AND id = ?
    ");
    
    $stmt->execute([$mergeAction, $selectedPublicationId, $sessionId, $rowId]);
    
    echo json_encode(['success' => true]);
}

/**
 * Resolve or create publication (case-insensitive)
 */
function resolveOrCreatePublication($db, $code, $name, $userId) {
    require_once __DIR__ . '/../../smartAdVault/helpers/DataStandardizationHelper.php';
    
    $standardizedName = strtoupper(trim($name));
    $standardizedCode = strtoupper(trim($code));
    
    // Check by code first (case-insensitive)
    $stmt = $db->prepare("SELECT id FROM publications WHERE UPPER(code) = ? AND deleted_at IS NULL");
    $stmt->execute([$standardizedCode]);
    if ($publication = $stmt->fetch()) {
        return $publication['id'];
    }
    
    // Check by name (case-insensitive)
    $stmt = $db->prepare("SELECT id FROM publications WHERE UPPER(name) = ? AND deleted_at IS NULL");
    $stmt->execute([$standardizedName]);
    if ($publication = $stmt->fetch()) {
        return $publication['id'];
    }
    
    // Create new publication with standardized data
    $stmt = $db->prepare("
        INSERT INTO publications (name, code, publication_type, status, created_by)
        VALUES (?, ?, 'newspaper', 'active', ?)
    ");
    
    $stmt->execute([$standardizedName, $standardizedCode, $userId]);
    return $db->lastInsertId();
}

/**
 * Resolve or create ad category (case-insensitive)
 */
function resolveOrCreateAdCategory($db, $name, $userId) {
    $standardizedName = strtoupper(trim($name));
    $standardizedCode = substr($standardizedName, 0, 20); // Generate code from name
    
    $stmt = $db->prepare("SELECT id FROM ad_categories WHERE UPPER(name) = ? AND deleted_at IS NULL");
    $stmt->execute([$standardizedName]);
    if ($category = $stmt->fetch()) {
        return $category['id'];
    }
    
    $stmt = $db->prepare("INSERT INTO ad_categories (name, code, status, created_by) VALUES (?, ?, 'active', ?)");
    $stmt->execute([$standardizedName, $standardizedCode, $userId]);
    return $db->lastInsertId();
}

/**
 * Resolve or create ad size (case-insensitive)
 */
function resolveOrCreateAdSize($db, $name, $userId) {
    $standardizedName = strtoupper(trim($name));
    $standardizedCode = substr($standardizedName, 0, 20); // Generate code from name
    
    $stmt = $db->prepare("SELECT id FROM ad_sizes WHERE UPPER(name) = ? AND deleted_at IS NULL");
    $stmt->execute([$standardizedName]);
    if ($size = $stmt->fetch()) {
        return $size['id'];
    }
    
    $stmt = $db->prepare("INSERT INTO ad_sizes (name, code, status, created_by) VALUES (?, ?, 'active', ?)");
    $stmt->execute([$standardizedName, $standardizedCode, $userId]);
    return $db->lastInsertId();
}

/**
 * Resolve or create page position (case-insensitive)
 */
function resolveOrCreatePagePosition($db, $name, $userId) {
    $standardizedName = strtoupper(trim($name));
    $standardizedCode = substr($standardizedName, 0, 20); // Generate code from name
    
    $stmt = $db->prepare("SELECT id FROM page_positions WHERE UPPER(name) = ? AND deleted_at IS NULL");
    $stmt->execute([$standardizedName]);
    if ($position = $stmt->fetch()) {
        return $position['id'];
    }
    
    $stmt = $db->prepare("INSERT INTO page_positions (name, code, status, created_by) VALUES (?, ?, 'active', ?)");
    $stmt->execute([$standardizedName, $standardizedCode, $userId]);
    return $db->lastInsertId();
}

/**
 * Resolve or create color type (case-insensitive)
 */
function resolveOrCreateColorType($db, $name, $userId) {
    $standardizedName = strtoupper(trim($name));
    $standardizedCode = substr($standardizedName, 0, 20); // Generate code from name
    
    $stmt = $db->prepare("SELECT id FROM color_types WHERE UPPER(name) = ? AND deleted_at IS NULL");
    $stmt->execute([$standardizedName]);
    if ($colorType = $stmt->fetch()) {
        return $colorType['id'];
    }
    
    $stmt = $db->prepare("INSERT INTO color_types (name, code, status, created_by) VALUES (?, ?, 'active', ?)");
    $stmt->execute([$standardizedName, $standardizedCode, $userId]);
    return $db->lastInsertId();
}

/**
 * Delete a staging session
 */
function deleteStagingSession() {
    global $authGuard, $currentUser;
    header('Content-Type: application/json');
    $authGuard->requirePermission('setup.rates.upload');
    
    $data = json_decode(file_get_contents('php://input'), true);
    $sessionId = $data['session_id'] ?? null;
    
    if (!$sessionId) {
        throw new Exception('Session ID is required');
    }
    
    $db = Database::getInstance();
    
    // Delete all staging records for this session (user-scoped for security)
    $stmt = $db->prepare("DELETE FROM rates_staging WHERE upload_session_id = ? AND uploaded_by = ?");
    $stmt->execute([$sessionId, $currentUser['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Staging session deleted successfully'
    ]);
}

/**
 * Check if a rate is used in any bookings
 */
function isRateUsed($db, $rateId) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings WHERE rate_id = ? AND deleted_at IS NULL");
    $stmt->execute([$rateId]);
    return $stmt->fetch()['count'] > 0;
}

/**
 * Get filter data for rates interface
 */
function getFilterData() {
    global $authGuard;
    header('Content-Type: application/json');
    $authGuard->requirePermission('setup.rates.view');
    
    $db = Database::getInstance();
    
    // Get all active publications
    $publications = $db->query("SELECT id, name, code FROM publications WHERE deleted_at IS NULL AND status = 'active' ORDER BY name")->fetchAll();
    
    // Get all active categories
    $categories = $db->query("SELECT id, name FROM ad_categories WHERE deleted_at IS NULL AND status = 'active' ORDER BY name")->fetchAll();
    
    // Get all active sizes
    $sizes = $db->query("SELECT id, name FROM ad_sizes WHERE deleted_at IS NULL AND status = 'active' ORDER BY name")->fetchAll();
    
    // Get all active positions
    $positions = $db->query("SELECT id, name FROM page_positions WHERE deleted_at IS NULL AND status = 'active' ORDER BY name")->fetchAll();
    
    // Get all active color types
    $colorTypes = $db->query("SELECT id, name FROM color_types WHERE deleted_at IS NULL AND status = 'active' ORDER BY name")->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'publications' => $publications,
            'categories' => $categories,
            'sizes' => $sizes,
            'positions' => $positions,
            'colorTypes' => $colorTypes
        ]
    ]);
}

/**
 * Log audit trail
 */
function logAudit($db, $tableName, $recordId, $action, $oldValues, $newValues, $userId) {
    $stmt = $db->prepare("
        INSERT INTO audit_logs (table_name, record_id, action, old_values, new_values, user_id, ip_address)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $tableName,
        $recordId,
        $action,
        json_encode($oldValues),
        json_encode($newValues),
        $userId,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
}