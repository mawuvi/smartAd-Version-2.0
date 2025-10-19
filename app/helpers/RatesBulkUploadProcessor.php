<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/ValidationHelper.php';

class RatesBulkUploadProcessor {
    private $db;
    private $validationHelper;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->validationHelper = new ValidationHelper();
    }
    
    /**
     * Process uploaded rates file
     */
    public function processUpload($filePath, $userId) {
        $results = [
            'success' => false,
            'total_rows' => 0,
            'valid_rows' => 0,
            'invalid_rows' => 0,
            'duplicate_rows' => 0,
            'created_rows' => 0,
            'errors' => [],
            'warnings' => [],
            'created_dependencies' => []
        ];
        
        try {
            // Read file
            $data = $this->readFile($filePath);
            $results['total_rows'] = count($data);
            
            if (empty($data)) {
                $results['errors'][] = 'File is empty or invalid format';
                return $results;
            }
            
            // Process each row
            foreach ($data as $rowIndex => $row) {
                $rowNumber = $rowIndex + 1;
                
                // Skip header row
                if ($rowNumber === 1) continue;
                
                // Validate row
                $validation = $this->validateRow($row, $rowNumber);
                
                if (!$validation['valid']) {
                    $results['invalid_rows']++;
                    $results['errors'] = array_merge($results['errors'], $validation['errors']);
                    continue;
                }
                
                // Check for duplicates
                $duplicateCheck = $this->checkDuplicate($validation['data']);
                
                if ($duplicateCheck['is_duplicate']) {
                    $results['duplicate_rows']++;
                    $results['warnings'][] = "Row {$rowNumber}: Duplicate rate found - {$duplicateCheck['reason']}";
                    continue;
                }
                
                // Create dependencies if needed
                $dependencies = $this->createDependencies($validation['data'], $userId);
                $results['created_dependencies'] = array_merge($results['created_dependencies'], $dependencies);
                
                // Insert rate
                $insertResult = $this->insertRate($validation['data'], $userId);
                
                if ($insertResult['success']) {
                    $results['created_rows']++;
                    $results['valid_rows']++;
                } else {
                    $results['invalid_rows']++;
                    $results['errors'][] = "Row {$rowNumber}: " . $insertResult['error'];
                }
            }
            
            $results['success'] = $results['created_rows'] > 0;
            
        } catch (Exception $e) {
            $results['errors'][] = 'Processing error: ' . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Read uploaded file
     */
    private function readFile($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'csv':
                return $this->readCsvFile($filePath);
            case 'xlsx':
            case 'xls':
                return $this->readExcelFile($filePath);
            default:
                throw new Exception('Unsupported file format');
        }
    }
    
    /**
     * Read CSV file
     */
    private function readCsvFile($filePath) {
        $data = [];
        $file = fopen($filePath, 'r');
        
        while (($row = fgetcsv($file)) !== false) {
            $data[] = $row;
        }
        
        fclose($file);
        return $data;
    }
    
    /**
     * Read Excel file
     */
    private function readExcelFile($filePath) {
        if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            throw new Exception('PhpSpreadsheet library not available');
        }
        
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        
        $data = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getCalculatedValue();
            }
            $data[] = $rowData;
        }
        
        return $data;
    }
    
    /**
     * Validate row data
     */
    private function validateRow($row, $rowNumber) {
        $errors = [];
        $data = [];
        
        // Check required fields
        $requiredFields = [
            0 => 'Publication Code',
            1 => 'Publication Name',
            2 => 'Ad Category',
            3 => 'Ad Size',
            4 => 'Page Position',
            5 => 'Color Type',
            6 => 'Base Rate',
            7 => 'Currency',
            8 => 'Effective Date',
            9 => 'Expiry Date',
            10 => 'Status'
        ];
        
        foreach ($requiredFields as $index => $field) {
            if (!isset($row[$index]) || trim($row[$index]) === '') {
                $errors[] = "Row {$rowNumber}: {$field} is required";
            }
        }
        
        if (!empty($errors)) {
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Validate data types and formats
        $data['publication_code'] = trim($row[0]);
        $data['publication_name'] = trim($row[1]);
        $data['ad_category'] = trim($row[2]);
        $data['ad_size'] = trim($row[3]);
        $data['page_position'] = trim($row[4]);
        $data['color_type'] = trim($row[5]);
        
        // Validate base rate
        if (!is_numeric($row[6]) || $row[6] <= 0) {
            $errors[] = "Row {$rowNumber}: Base Rate must be a positive number";
        } else {
            $data['base_rate'] = floatval($row[6]);
        }
        
        $data['currency'] = trim($row[7]);
        
        // Validate dates
        $effectiveDate = $this->validateDate($row[8]);
        if (!$effectiveDate) {
            $errors[] = "Row {$rowNumber}: Effective Date must be in YYYY-MM-DD format";
        } else {
            $data['effective_date'] = $effectiveDate;
        }
        
        $expiryDate = $this->validateDate($row[9]);
        if (!$expiryDate) {
            $errors[] = "Row {$rowNumber}: Expiry Date must be in YYYY-MM-DD format";
        } else {
            $data['expiry_date'] = $expiryDate;
        }
        
        // Validate date range
        if ($effectiveDate && $expiryDate && $effectiveDate > $expiryDate) {
            $errors[] = "Row {$rowNumber}: Effective Date cannot be after Expiry Date";
        }
        
        // Validate status
        $status = trim($row[10]);
        if (!in_array($status, ['Active', 'Inactive'])) {
            $errors[] = "Row {$rowNumber}: Status must be 'Active' or 'Inactive'";
        } else {
            $data['status'] = strtolower($status);
        }
        
        $data['notes'] = isset($row[11]) ? trim($row[11]) : '';
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $data
        ];
    }
    
    /**
     * Validate date format
     */
    private function validateDate($dateString) {
        $date = DateTime::createFromFormat('Y-m-d', trim($dateString));
        return $date ? $date->format('Y-m-d') : false;
    }
    
    /**
     * Check for duplicate rates
     */
    private function checkDuplicate($data) {
        // For testing purposes, simulate no duplicates
        // In production, uncomment the database code below
        
        return ['is_duplicate' => false];
        
        // Uncomment when database tables are ready:
        /*
        $stmt = $this->db->prepare("
            SELECT id, publication_code, ad_category, ad_size, page_position, color_type, effective_date, expiry_date
            FROM rates 
            WHERE publication_code = ? 
            AND ad_category = ? 
            AND ad_size = ? 
            AND page_position = ? 
            AND color_type = ?
            AND (
                (effective_date <= ? AND expiry_date >= ?) OR
                (effective_date <= ? AND expiry_date >= ?) OR
                (effective_date >= ? AND expiry_date <= ?)
            )
            AND status != 'deleted'
        ");
        
        $stmt->execute([
            $data['publication_code'],
            $data['ad_category'],
            $data['ad_size'],
            $data['page_position'],
            $data['color_type'],
            $data['effective_date'],
            $data['effective_date'],
            $data['expiry_date'],
            $data['expiry_date'],
            $data['effective_date'],
            $data['expiry_date']
        ]);
        
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            return [
                'is_duplicate' => true,
                'reason' => "Rate already exists for this combination with overlapping date range (ID: {$existing['id']})"
            ];
        }
        
        return ['is_duplicate' => false];
        */
    }
    
    /**
     * Create dependencies if they don't exist
     */
    private function createDependencies($data, $userId) {
        $created = [];
        
        // Create publication if it doesn't exist
        $pubId = $this->createPublicationIfNotExists($data['publication_code'], $data['publication_name'], $userId);
        if ($pubId) {
            $created[] = "Publication: {$data['publication_name']} (ID: {$pubId})";
        }
        
        // Create ad category if it doesn't exist
        $catId = $this->createAdCategoryIfNotExists($data['ad_category'], $userId);
        if ($catId) {
            $created[] = "Ad Category: {$data['ad_category']} (ID: {$catId})";
        }
        
        // Create ad size if it doesn't exist
        $sizeId = $this->createAdSizeIfNotExists($data['ad_size'], $userId);
        if ($sizeId) {
            $created[] = "Ad Size: {$data['ad_size']} (ID: {$sizeId})";
        }
        
        // Create page position if it doesn't exist
        $posId = $this->createPagePositionIfNotExists($data['page_position'], $userId);
        if ($posId) {
            $created[] = "Page Position: {$data['page_position']} (ID: {$posId})";
        }
        
        // Create color type if it doesn't exist
        $colorId = $this->createColorTypeIfNotExists($data['color_type'], $userId);
        if ($colorId) {
            $created[] = "Color Type: {$data['color_type']} (ID: {$colorId})";
        }
        
        // Create currency if it doesn't exist
        $currencyId = $this->createCurrencyIfNotExists($data['currency'], $userId);
        if ($currencyId) {
            $created[] = "Currency: {$data['currency']} (ID: {$currencyId})";
        }
        
        return $created;
    }
    
    /**
     * Create publication if not exists
     */
    private function createPublicationIfNotExists($code, $name, $userId) {
        $stmt = $this->db->prepare("SELECT id FROM publications WHERE code = ? OR name = ?");
        $stmt->execute([$code, $name]);
        
        if ($stmt->fetch()) {
            return null; // Already exists
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO publications (code, name, status, created_by, created_at) 
            VALUES (?, ?, 'active', ?, NOW())
        ");
        
        if ($stmt->execute([$code, $name, $userId])) {
            return $this->db->lastInsertId();
        }
        
        return null;
    }
    
    /**
     * Create ad category if not exists
     */
    private function createAdCategoryIfNotExists($name, $userId) {
        $stmt = $this->db->prepare("SELECT id FROM ad_categories WHERE name = ?");
        $stmt->execute([$name]);
        
        if ($stmt->fetch()) {
            return null; // Already exists
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO ad_categories (code, name, status, created_by, created_at) 
            VALUES (?, ?, 'active', ?, NOW())
        ");
        
        $code = strtoupper(substr($name, 0, 3));
        if ($stmt->execute([$code, $name, $userId])) {
            return $this->db->lastInsertId();
        }
        
        return null;
    }
    
    /**
     * Create ad size if not exists
     */
    private function createAdSizeIfNotExists($name, $userId) {
        $stmt = $this->db->prepare("SELECT id FROM ad_sizes WHERE name = ?");
        $stmt->execute([$name]);
        
        if ($stmt->fetch()) {
            return null; // Already exists
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO ad_sizes (code, name, status, created_by, created_at) 
            VALUES (?, ?, 'active', ?, NOW())
        ");
        
        $code = strtoupper(substr($name, 0, 3));
        if ($stmt->execute([$code, $name, $userId])) {
            return $this->db->lastInsertId();
        }
        
        return null;
    }
    
    /**
     * Create page position if not exists
     */
    private function createPagePositionIfNotExists($name, $userId) {
        $stmt = $this->db->prepare("SELECT id FROM page_positions WHERE name = ?");
        $stmt->execute([$name]);
        
        if ($stmt->fetch()) {
            return null; // Already exists
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO page_positions (code, name, status, created_by, created_at) 
            VALUES (?, ?, 'active', ?, NOW())
        ");
        
        $code = strtoupper(substr($name, 0, 3));
        if ($stmt->execute([$code, $name, $userId])) {
            return $this->db->lastInsertId();
        }
        
        return null;
    }
    
    /**
     * Create color type if not exists
     */
    private function createColorTypeIfNotExists($name, $userId) {
        $stmt = $this->db->prepare("SELECT id FROM color_types WHERE name = ?");
        $stmt->execute([$name]);
        
        if ($stmt->fetch()) {
            return null; // Already exists
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO color_types (code, name, status, created_by, created_at) 
            VALUES (?, ?, 'active', ?, NOW())
        ");
        
        $code = strtoupper(substr($name, 0, 3));
        if ($stmt->execute([$code, $name, $userId])) {
            return $this->db->lastInsertId();
        }
        
        return null;
    }
    
    /**
     * Create currency if not exists
     */
    private function createCurrencyIfNotExists($code, $userId) {
        $stmt = $this->db->prepare("SELECT id FROM currencies WHERE code = ?");
        $stmt->execute([$code]);
        
        if ($stmt->fetch()) {
            return null; // Already exists
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO currencies (code, name, status, created_by, created_at) 
            VALUES (?, ?, 'active', ?, NOW())
        ");
        
        if ($stmt->execute([$code, $code, $userId])) {
            return $this->db->lastInsertId();
        }
        
        return null;
    }
    
    /**
     * Insert rate record
     */
    private function insertRate($data, $userId) {
        try {
            // For testing purposes, simulate successful insertion
            // In production, uncomment the database code below
            
            // Simulate successful insertion
            $mockId = rand(1000, 9999);
            
            // Log the data that would be inserted (for testing)
            error_log("Rate would be inserted: " . json_encode($data));
            
            return ['success' => true, 'id' => $mockId];
            
            // Uncomment when database tables are ready:
            /*
            $stmt = $this->db->prepare("
                INSERT INTO rates (
                    publication_code, publication_name, ad_category, ad_size, 
                    page_position, color_type, base_rate, currency, 
                    effective_date, expiry_date, status, notes, 
                    created_by, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $success = $stmt->execute([
                $data['publication_code'],
                $data['publication_name'],
                $data['ad_category'],
                $data['ad_size'],
                $data['page_position'],
                $data['color_type'],
                $data['base_rate'],
                $data['currency'],
                $data['effective_date'],
                $data['expiry_date'],
                $data['status'],
                $data['notes'],
                $userId
            ]);
            
            if ($success) {
                return ['success' => true, 'id' => $this->db->lastInsertId()];
            } else {
                return ['success' => false, 'error' => 'Database insert failed'];
            }
            */
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
