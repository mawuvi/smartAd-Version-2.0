<?php

class SetupModel {
    private $db;
    private $auditLogger;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->auditLogger = new AuditLogger();
    }

    /**
     * Get count of records for a specific entity type
     */
    public function getCount($entityType) {
        $table = $this->getTableName($entityType);
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE deleted_at IS NULL";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $result['count'];
        } catch (PDOException $e) {
            error_log("SetupModel::getCount error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get all records for a specific entity type with pagination
     */
    public function getRecords($entityType, $page = 1, $limit = 20, $search = '', $status = '') {
        $table = $this->getTableName($entityType);
        $offset = ($page - 1) * $limit;
        
        $whereConditions = ['deleted_at IS NULL'];
        $params = [];
        
        if (!empty($search)) {
            $searchFields = $this->getSearchFields($entityType);
            $searchConditions = [];
            foreach ($searchFields as $field) {
                $searchConditions[] = "{$field} LIKE :search";
            }
            $whereConditions[] = '(' . implode(' OR ', $searchConditions) . ')';
            $params[':search'] = "%{$search}%";
        }
        
        if (!empty($status)) {
            $whereConditions[] = "status = :status";
            $params[':status'] = $status;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$table} WHERE {$whereClause}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get records
        $sql = "SELECT * FROM {$table} WHERE {$whereClause} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'records' => $records,
            'total' => $totalRecords,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($totalRecords / $limit)
        ];
    }

    /**
     * Get a single record by ID
     */
    public function getRecord($entityType, $id) {
        $table = $this->getTableName($entityType);
        $sql = "SELECT * FROM {$table} WHERE id = :id AND deleted_at IS NULL";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SetupModel::getRecord error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new record
     */
    public function createRecord($entityType, $data) {
        $table = $this->getTableName($entityType);
        $fields = $this->getFields($entityType);
        
        // Filter data to only include valid fields
        $filteredData = array_intersect_key($data, array_flip($fields));
        
        // Add timestamps
        $filteredData['created_at'] = date('Y-m-d H:i:s');
        $filteredData['updated_at'] = date('Y-m-d H:i:s');
        
        $columns = implode(', ', array_keys($filteredData));
        $placeholders = ':' . implode(', :', array_keys($filteredData));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($filteredData as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            $stmt->execute();
            
            $id = $this->db->lastInsertId();
            
            // Log audit
            $this->auditLogger->log('CREATE', $entityType, $id, $filteredData);
            
            return $id;
        } catch (PDOException $e) {
            error_log("SetupModel::createRecord error: " . $e->getMessage());
            throw new Exception("Failed to create record: " . $e->getMessage());
        }
    }

    /**
     * Update an existing record
     */
    public function updateRecord($entityType, $id, $data) {
        $table = $this->getTableName($entityType);
        $fields = $this->getFields($entityType);
        
        // Filter data to only include valid fields
        $filteredData = array_intersect_key($data, array_flip($fields));
        
        // Add updated timestamp
        $filteredData['updated_at'] = date('Y-m-d H:i:s');
        
        $setParts = [];
        foreach ($filteredData as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE id = :id AND deleted_at IS NULL";
        
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($filteredData as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Log audit
                $this->auditLogger->log('UPDATE', $entityType, $id, $filteredData);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("SetupModel::updateRecord error: " . $e->getMessage());
            throw new Exception("Failed to update record: " . $e->getMessage());
        }
    }

    /**
     * Soft delete a record
     */
    public function deleteRecord($entityType, $id) {
        $table = $this->getTableName($entityType);
        $sql = "UPDATE {$table} SET deleted_at = :deleted_at WHERE id = :id AND deleted_at IS NULL";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':deleted_at', date('Y-m-d H:i:s'));
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Log audit
                $this->auditLogger->log('DELETE', $entityType, $id, ['deleted_at' => date('Y-m-d H:i:s')]);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("SetupModel::deleteRecord error: " . $e->getMessage());
            throw new Exception("Failed to delete record: " . $e->getMessage());
        }
    }

    /**
     * Toggle status of a record
     */
    public function toggleStatus($entityType, $id) {
        $table = $this->getTableName($entityType);
        $sql = "UPDATE {$table} SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END, updated_at = :updated_at WHERE id = :id AND deleted_at IS NULL";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'));
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Get new status for audit
                $record = $this->getRecord($entityType, $id);
                $this->auditLogger->log('STATUS_TOGGLE', $entityType, $id, ['status' => $record['status']]);
                return $record['status'];
            }
            return false;
        } catch (PDOException $e) {
            error_log("SetupModel::toggleStatus error: " . $e->getMessage());
            throw new Exception("Failed to toggle status: " . $e->getMessage());
        }
    }

    /**
     * Get or create a dependent record (for bulk uploads)
     */
    public function getOrCreateRecord($entityType, $searchData, $createData = null) {
        $table = $this->getTableName($entityType);
        $searchFields = $this->getUniqueFields($entityType);
        
        $whereConditions = ['deleted_at IS NULL'];
        $params = [];
        
        foreach ($searchFields as $field) {
            if (isset($searchData[$field])) {
                $whereConditions[] = "{$field} = :{$field}";
                $params[":{$field}"] = $searchData[$field];
            }
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        $sql = "SELECT * FROM {$table} WHERE {$whereClause}";
        
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        $stmt->execute();
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($record) {
                return $record['id'];
            }
            
            // Create new record if not found
            if ($createData) {
                $mergedData = array_merge($searchData, $createData);
                return $this->createRecord($entityType, $mergedData);
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("SetupModel::getOrCreateRecord error: " . $e->getMessage());
            throw new Exception("Failed to get or create record: " . $e->getMessage());
        }
    }

    /**
     * Check for duplicate records
     */
    public function checkDuplicate($entityType, $data) {
        $table = $this->getTableName($entityType);
        $uniqueFields = $this->getUniqueFields($entityType);
        
        $whereConditions = ['deleted_at IS NULL'];
        $params = [];
        
        foreach ($uniqueFields as $field) {
            if (isset($data[$field])) {
                $whereConditions[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        $sql = "SELECT * FROM {$table} WHERE {$whereClause}";
        
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SetupModel::checkDuplicate error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get table name for entity type
     */
    private function getTableName($entityType) {
        $tables = [
            'publications' => 'publications',
            'taxes' => 'taxes',
            'tax_configurations' => 'tax_configurations',
            'ad_categories' => 'ad_categories',
            'ad_sizes' => 'ad_sizes',
            'page_positions' => 'page_positions',
            'color_types' => 'color_types',
            'payment_types' => 'payment_types',
            'industries' => 'industries',
            'currencies' => 'currencies',
            'base_rates' => 'base_rates'
        ];
        
        return $tables[$entityType] ?? $entityType;
    }

    /**
     * Get searchable fields for entity type
     */
    private function getSearchFields($entityType) {
        $searchFields = [
            'publications' => ['name', 'code', 'description'],
            'taxes' => ['name', 'code', 'description'],
            'tax_configurations' => ['publication_name', 'tax_name'],
            'ad_categories' => ['name', 'code', 'description'],
            'ad_sizes' => ['name', 'code', 'description'],
            'page_positions' => ['name', 'code', 'description'],
            'color_types' => ['name', 'code', 'description'],
            'payment_types' => ['name', 'code', 'description'],
            'industries' => ['name', 'code', 'description'],
            'currencies' => ['name', 'code', 'symbol'],
            'base_rates' => ['publication_name', 'ad_category_name', 'ad_size_name']
        ];
        
        return $searchFields[$entityType] ?? ['name', 'code'];
    }

    /**
     * Get unique fields for entity type (for duplicate checking)
     */
    private function getUniqueFields($entityType) {
        $uniqueFields = [
            'publications' => ['code'],
            'taxes' => ['code'],
            'tax_configurations' => ['publication_id', 'tax_id'],
            'ad_categories' => ['code'],
            'ad_sizes' => ['code'],
            'page_positions' => ['code'],
            'color_types' => ['code'],
            'payment_types' => ['code'],
            'industries' => ['code'],
            'currencies' => ['code'],
            'base_rates' => ['publication_id', 'color_type_id', 'ad_category_id', 'ad_size_id', 'page_position_id', 'effective_from']
        ];
        
        return $uniqueFields[$entityType] ?? ['code'];
    }

    /**
     * Get editable fields for entity type
     */
    private function getFields($entityType) {
        $fields = [
            'publications' => ['name', 'code', 'description', 'type', 'circulation', 'frequency', 'base_rate', 'status'],
            'taxes' => ['name', 'code', 'rate', 'type', 'effective_from', 'effective_to', 'description', 'status'],
            'tax_configurations' => ['publication_id', 'tax_id', 'is_applicable', 'status'],
            'ad_categories' => ['name', 'code', 'description', 'type', 'multiplier', 'status'],
            'ad_sizes' => ['name', 'code', 'width', 'height', 'description', 'status'],
            'page_positions' => ['name', 'code', 'description', 'type', 'multiplier', 'status'],
            'color_types' => ['name', 'code', 'description', 'mode', 'multiplier', 'status'],
            'payment_types' => ['name', 'code', 'description', 'method_type', 'status'],
            'industries' => ['name', 'code', 'description', 'sector', 'status'],
            'currencies' => ['name', 'code', 'symbol', 'exchange_rate', 'status'],
            'base_rates' => ['publication_id', 'color_type_id', 'ad_category_id', 'ad_size_id', 'page_position_id', 'base_rate', 'effective_from', 'effective_to', 'notes', 'status']
        ];
        
        return $fields[$entityType] ?? ['name', 'code', 'description', 'status'];
    }

    /**
     * Validate data for entity type
     */
    public function validateData($entityType, $data) {
        $errors = [];
        $fields = $this->getFields($entityType);
        
        // Check required fields
        $requiredFields = $this->getRequiredFields($entityType);
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        // Validate specific field types
        foreach ($data as $field => $value) {
            if (empty($value)) continue;
            
            switch ($field) {
                case 'rate':
                case 'multiplier':
                case 'exchange_rate':
                case 'base_rate':
                    if (!is_numeric($value) || $value < 0) {
                        $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be a positive number';
                    }
                    break;
                    
                case 'width':
                case 'height':
                    if (!is_numeric($value) || $value <= 0) {
                        $errors[] = ucfirst($field) . ' must be a positive number';
                    }
                    break;
                    
                case 'effective_from':
                case 'effective_to':
                    if (!strtotime($value)) {
                        $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be a valid date';
                    }
                    break;
                    
                case 'status':
                    if (!in_array($value, ['active', 'inactive'])) {
                        $errors[] = 'Status must be either active or inactive';
                    }
                    break;
            }
        }
        
        return $errors;
    }

    /**
     * Get required fields for entity type
     */
    private function getRequiredFields($entityType) {
        $requiredFields = [
            'publications' => ['name', 'code'],
            'taxes' => ['name', 'code', 'rate'],
            'tax_configurations' => ['publication_id', 'tax_id'],
            'ad_categories' => ['name', 'code'],
            'ad_sizes' => ['name', 'code', 'width', 'height'],
            'page_positions' => ['name', 'code'],
            'color_types' => ['name', 'code'],
            'payment_types' => ['name', 'code'],
            'industries' => ['name', 'code'],
            'currencies' => ['name', 'code', 'symbol'],
            'base_rates' => ['publication_id', 'color_type_id', 'ad_category_id', 'ad_size_id', 'page_position_id', 'base_rate']
        ];
        
        return $requiredFields[$entityType] ?? ['name', 'code'];
    }

    /**
     * Export data for entity type
     */
    public function exportData($entityType, $format = 'csv') {
        $records = $this->getRecords($entityType, 1, 10000); // Get all records
        
        if ($format === 'csv') {
            return $this->generateCSV($entityType, $records['records']);
        } elseif ($format === 'excel') {
            return $this->generateExcel($entityType, $records['records']);
        }
        
        return false;
    }

    /**
     * Generate CSV data
     */
    private function generateCSV($entityType, $records) {
        if (empty($records)) {
            return '';
        }
        
        $fields = $this->getFields($entityType);
        $csv = implode(',', $fields) . "\n";
        
        foreach ($records as $record) {
            $row = [];
            foreach ($fields as $field) {
                $value = $record[$field] ?? '';
                // Escape CSV values
                if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
                    $value = '"' . str_replace('"', '""', $value) . '"';
                }
                $row[] = $value;
            }
            $csv .= implode(',', $row) . "\n";
        }
        
        return $csv;
    }

    /**
     * Generate Excel data (simplified - would need PhpSpreadsheet for full implementation)
     */
    private function generateExcel($entityType, $records) {
        // For now, return CSV format
        // In a full implementation, you would use PhpSpreadsheet to create actual Excel files
        return $this->generateCSV($entityType, $records);
    }
}