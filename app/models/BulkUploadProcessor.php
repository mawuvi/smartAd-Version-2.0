<?php

class BulkUploadProcessor {
    private $db;
    private $setupModel;
    private $templateGenerator;
    private $auditLogger;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->setupModel = new SetupModel();
        $this->templateGenerator = new TemplateGenerator();
        $this->auditLogger = new AuditLogger();
    }

    /**
     * Process uploaded file
     */
    public function processUpload($file, $entityType) {
        try {
            // Validate file
            $this->validateFile($file);
            
            // Parse file
            $data = $this->parseFile($file);
            
            // Validate data
            $validationResult = $this->validateData($data, $entityType);
            
            // Check for duplicates
            $duplicateResult = $this->checkDuplicates($data, $entityType);
            
            // Create import record
            $importId = $this->createImportRecord($entityType, $file['name'], count($data));
            
            // Store processed data
            $this->storeProcessedData($importId, $data, $validationResult, $duplicateResult);
            
            return [
                'success' => true,
                'import_id' => $importId,
                'total_rows' => count($data),
                'valid_rows' => $validationResult['valid_count'],
                'error_rows' => $validationResult['error_count'],
                'warning_rows' => $validationResult['warning_count'],
                'duplicate_rows' => $duplicateResult['duplicate_count'],
                'errors' => $validationResult['errors'],
                'warnings' => $validationResult['warnings'],
                'summary' => [
                    'total_rows' => count($data),
                    'valid_rows' => $validationResult['valid_count'],
                    'error_rows' => $validationResult['error_count'],
                    'warning_rows' => $validationResult['warning_count'],
                    'duplicate_rows' => $duplicateResult['duplicate_count']
                ]
            ];
            
        } catch (Exception $e) {
            error_log("BulkUploadProcessor::processUpload error: " . $e->getMessage());
            throw new Exception("Upload processing failed: " . $e->getMessage());
        }
    }

    /**
     * Get preview of import data
     */
    public function getImportPreview($importId) {
        try {
            $sql = "SELECT * FROM bulk_imports WHERE id = :import_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':import_id', $importId, PDO::PARAM_INT);
            $stmt->execute();
            $import = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$import) {
                throw new Exception('Import record not found');
            }
            
            $sql = "SELECT * FROM bulk_import_data WHERE import_id = :import_id ORDER BY row_number";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':import_id', $importId, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'import' => $import,
                'rows' => $rows,
                'total_rows' => count($rows)
            ];
            
        } catch (Exception $e) {
            error_log("BulkUploadProcessor::getImportPreview error: " . $e->getMessage());
            throw new Exception("Failed to get preview: " . $e->getMessage());
        }
    }

    /**
     * Commit import to database
     */
    public function commitImport($importId, $duplicateMode = 'skip') {
        try {
            $this->db->beginTransaction();
            
            // Get import data
            $sql = "SELECT * FROM bulk_imports WHERE id = :import_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':import_id', $importId, PDO::PARAM_INT);
            $stmt->execute();
            $import = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$import) {
                throw new Exception('Import record not found');
            }
            
            $sql = "SELECT * FROM bulk_import_data WHERE import_id = :import_id AND status = 'valid' ORDER BY row_number";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':import_id', $importId, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $results = [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => 0
            ];
            
            foreach ($rows as $row) {
                try {
                    $data = json_decode($row['data'], true);
                    $result = $this->processRow($import['entity_type'], $data, $duplicateMode);
                    
                    switch ($result['action']) {
                        case 'created':
                            $results['created']++;
                            break;
                        case 'updated':
                            $results['updated']++;
                            break;
                        case 'skipped':
                            $results['skipped']++;
                            break;
                        case 'error':
                            $results['errors']++;
                            break;
                    }
                    
                    // Update row status
                    $this->updateRowStatus($row['id'], $result['action'], $result['message']);
                    
                } catch (Exception $e) {
                    $results['errors']++;
                    $this->updateRowStatus($row['id'], 'error', $e->getMessage());
                }
            }
            
            // Update import status
            $this->updateImportStatus($importId, 'completed', $results);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'results' => $results
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("BulkUploadProcessor::commitImport error: " . $e->getMessage());
            throw new Exception("Import commit failed: " . $e->getMessage());
        }
    }

    /**
     * Rollback import
     */
    public function rollbackImport($importId) {
        try {
            $this->db->beginTransaction();
            
            // Delete import data
            $sql = "DELETE FROM bulk_import_data WHERE import_id = :import_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':import_id', $importId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Delete import record
            $sql = "DELETE FROM bulk_imports WHERE id = :import_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':import_id', $importId, PDO::PARAM_INT);
            $stmt->execute();
            
            $this->db->commit();
            
            return ['success' => true];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("BulkUploadProcessor::rollbackImport error: " . $e->getMessage());
            throw new Exception("Import rollback failed: " . $e->getMessage());
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Invalid file upload');
        }
        
        $allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Invalid file type. Only CSV and Excel files are allowed.');
        }
        
        if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
            throw new Exception('File size too large. Maximum size is 10MB.');
        }
    }

    /**
     * Parse uploaded file
     */
    private function parseFile($file) {
        $fileType = mime_content_type($file['tmp_name']);
        $data = [];
        
        if ($fileType === 'text/csv') {
            $data = $this->parseCSV($file['tmp_name']);
        } elseif (in_array($fileType, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) {
            $data = $this->parseExcel($file['tmp_name']);
        } else {
            throw new Exception('Unsupported file format');
        }
        
        if (empty($data)) {
            throw new Exception('No data found in file');
        }
        
        return $data;
    }

    /**
     * Parse CSV file
     */
    private function parseCSV($filePath) {
        $data = [];
        $handle = fopen($filePath, 'r');
        
        if (!$handle) {
            throw new Exception('Could not open CSV file');
        }
        
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new Exception('Could not read CSV headers');
        }
        
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }
        
        fclose($handle);
        return $data;
    }

    /**
     * Parse Excel file (simplified - would need PhpSpreadsheet for full implementation)
     */
    private function parseExcel($filePath) {
        // For now, treat Excel files as CSV
        // In a full implementation, you would use PhpSpreadsheet
        return $this->parseCSV($filePath);
    }

    /**
     * Validate data against entity type rules
     */
    private function validateData($data, $entityType) {
        $rules = $this->templateGenerator->getValidationRules($entityType);
        $errors = [];
        $warnings = [];
        $validCount = 0;
        $errorCount = 0;
        $warningCount = 0;
        
        foreach ($data as $rowIndex => $row) {
            $rowErrors = [];
            $rowWarnings = [];
            
            foreach ($rules as $field => $fieldRules) {
                $value = $row[$field] ?? '';
                
                foreach ($fieldRules as $rule) {
                    $result = $this->validateField($field, $value, $rule, $entityType);
                    if ($result['type'] === 'error') {
                        $rowErrors[] = "Row " . ($rowIndex + 1) . ": {$result['message']}";
                    } elseif ($result['type'] === 'warning') {
                        $rowWarnings[] = "Row " . ($rowIndex + 1) . ": {$result['message']}";
                    }
                }
            }
            
            if (empty($rowErrors)) {
                $validCount++;
                if (!empty($rowWarnings)) {
                    $warningCount++;
                    $warnings = array_merge($warnings, $rowWarnings);
                }
            } else {
                $errorCount++;
                $errors = array_merge($errors, $rowErrors);
            }
        }
        
        return [
            'valid_count' => $validCount,
            'error_count' => $errorCount,
            'warning_count' => $warningCount,
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Validate individual field
     */
    private function validateField($field, $value, $rule, $entityType) {
        switch ($rule) {
            case 'required':
                if (empty(trim($value))) {
                    return ['type' => 'error', 'message' => "{$field} is required"];
                }
                break;
                
            case 'string':
                if (!is_string($value)) {
                    return ['type' => 'error', 'message' => "{$field} must be text"];
                }
                break;
                
            case 'numeric':
                if (!is_numeric($value)) {
                    return ['type' => 'error', 'message' => "{$field} must be a number"];
                }
                break;
                
            case 'date':
                if (!empty($value) && !strtotime($value)) {
                    return ['type' => 'error', 'message' => "{$field} must be a valid date"];
                }
                break;
                
            case 'unique':
                // Check uniqueness in database
                $duplicate = $this->setupModel->checkDuplicate($entityType, [$field => $value]);
                if ($duplicate) {
                    return ['type' => 'warning', 'message' => "{$field} '{$value}' already exists"];
                }
                break;
                
            default:
                if (strpos($rule, 'max:') === 0) {
                    $max = (int) substr($rule, 4);
                    if (strlen($value) > $max) {
                        return ['type' => 'error', 'message' => "{$field} must not exceed {$max} characters"];
                    }
                } elseif (strpos($rule, 'min:') === 0) {
                    $min = (float) substr($rule, 4);
                    if (is_numeric($value) && $value < $min) {
                        return ['type' => 'error', 'message' => "{$field} must be at least {$min}"];
                    }
                } elseif (strpos($rule, 'in:') === 0) {
                    $options = explode(',', substr($rule, 3));
                    if (!empty($value) && !in_array($value, $options)) {
                        return ['type' => 'error', 'message' => "{$field} must be one of: " . implode(', ', $options)];
                    }
                }
                break;
        }
        
        return ['type' => 'valid', 'message' => ''];
    }

    /**
     * Check for duplicate records
     */
    private function checkDuplicates($data, $entityType) {
        $duplicates = [];
        $duplicateCount = 0;
        
        foreach ($data as $rowIndex => $row) {
            $duplicate = $this->setupModel->checkDuplicate($entityType, $row);
            if ($duplicate) {
                $duplicates[] = "Row " . ($rowIndex + 1) . ": Duplicate record found";
                $duplicateCount++;
            }
        }
        
        return [
            'duplicates' => $duplicates,
            'duplicate_count' => $duplicateCount
        ];
    }

    /**
     * Process individual row for import
     */
    private function processRow($entityType, $data, $duplicateMode) {
        // Check for duplicates
        $duplicate = $this->setupModel->checkDuplicate($entityType, $data);
        
        if ($duplicate) {
            if ($duplicateMode === 'skip') {
                return ['action' => 'skipped', 'message' => 'Duplicate record skipped'];
            } elseif ($duplicateMode === 'update') {
                $success = $this->setupModel->updateRecord($entityType, $duplicate['id'], $data);
                if ($success) {
                    return ['action' => 'updated', 'message' => 'Record updated successfully'];
                } else {
                    return ['action' => 'error', 'message' => 'Failed to update record'];
                }
            }
        }
        
        // Auto-create dependencies for complex entities
        if ($entityType === 'base_rates') {
            $data = $this->autoCreateDependencies($data);
        }
        
        // Create new record
        $id = $this->setupModel->createRecord($entityType, $data);
        if ($id) {
            return ['action' => 'created', 'message' => 'Record created successfully'];
        } else {
            return ['action' => 'error', 'message' => 'Failed to create record'];
        }
    }

    /**
     * Auto-create dependencies for base rates
     */
    private function autoCreateDependencies($data) {
        // Get or create publication
        if (isset($data['Publication Name'])) {
            $publicationId = $this->setupModel->getOrCreateRecord('publications', 
                ['name' => $data['Publication Name']], 
                ['code' => strtoupper(substr($data['Publication Name'], 0, 3)), 'status' => 'active']
            );
            $data['publication_id'] = $publicationId;
        }
        
        // Get or create color type
        if (isset($data['Color Type Name'])) {
            $colorTypeId = $this->setupModel->getOrCreateRecord('color_types', 
                ['name' => $data['Color Type Name']], 
                ['code' => strtoupper(substr($data['Color Type Name'], 0, 3)), 'status' => 'active']
            );
            $data['color_type_id'] = $colorTypeId;
        }
        
        // Get or create ad category
        if (isset($data['Ad Category Name'])) {
            $adCategoryId = $this->setupModel->getOrCreateRecord('ad_categories', 
                ['name' => $data['Ad Category Name']], 
                ['code' => strtoupper(substr($data['Ad Category Name'], 0, 3)), 'status' => 'active']
            );
            $data['ad_category_id'] = $adCategoryId;
        }
        
        // Get or create ad size
        if (isset($data['Ad Size Name'])) {
            $adSizeId = $this->setupModel->getOrCreateRecord('ad_sizes', 
                ['name' => $data['Ad Size Name']], 
                ['code' => strtoupper(substr($data['Ad Size Name'], 0, 3)), 'width' => 10, 'height' => 10, 'status' => 'active']
            );
            $data['ad_size_id'] = $adSizeId;
        }
        
        // Get or create page position
        if (isset($data['Page Position Name'])) {
            $pagePositionId = $this->setupModel->getOrCreateRecord('page_positions', 
                ['name' => $data['Page Position Name']], 
                ['code' => strtoupper(substr($data['Page Position Name'], 0, 3)), 'status' => 'active']
            );
            $data['page_position_id'] = $pagePositionId;
        }
        
        return $data;
    }

    /**
     * Create import record
     */
    private function createImportRecord($entityType, $filename, $totalRows) {
        $sql = "INSERT INTO bulk_imports (entity_type, filename, total_rows, status, created_at) VALUES (:entity_type, :filename, :total_rows, 'processing', :created_at)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':entity_type', $entityType);
        $stmt->bindValue(':filename', $filename);
        $stmt->bindValue(':total_rows', $totalRows, PDO::PARAM_INT);
        $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }

    /**
     * Store processed data
     */
    private function storeProcessedData($importId, $data, $validationResult, $duplicateResult) {
        $sql = "INSERT INTO bulk_import_data (import_id, row_number, data, status, errors, warnings) VALUES (:import_id, :row_number, :data, :status, :errors, :warnings)";
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $index => $row) {
            $status = 'valid';
            $errors = '';
            $warnings = '';
            
            // Determine status based on validation
            if ($index < $validationResult['error_count']) {
                $status = 'error';
            } elseif ($index < $validationResult['error_count'] + $validationResult['warning_count']) {
                $status = 'warning';
            }
            
            $stmt->bindValue(':import_id', $importId, PDO::PARAM_INT);
            $stmt->bindValue(':row_number', $index + 1, PDO::PARAM_INT);
            $stmt->bindValue(':data', json_encode($row));
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':errors', $errors);
            $stmt->bindValue(':warnings', $warnings);
            $stmt->execute();
        }
    }

    /**
     * Update row status
     */
    private function updateRowStatus($rowId, $action, $message) {
        $sql = "UPDATE bulk_import_data SET final_status = :status, final_message = :message WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $action);
        $stmt->bindValue(':message', $message);
        $stmt->bindValue(':id', $rowId, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Update import status
     */
    private function updateImportStatus($importId, $status, $results = null) {
        $sql = "UPDATE bulk_imports SET status = :status, completed_at = :completed_at";
        if ($results) {
            $sql .= ", results = :results";
        }
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':completed_at', date('Y-m-d H:i:s'));
        if ($results) {
            $stmt->bindValue(':results', json_encode($results));
        }
        $stmt->bindValue(':id', $importId, PDO::PARAM_INT);
        $stmt->execute();
    }
}
