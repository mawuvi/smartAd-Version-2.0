<?php
// Standalone template generator for rates upload

class RatesTemplateGenerator {
    private $db;
    
    public function __construct() {
        // Mock database for testing - will be replaced with real DB when ready
        $this->db = null;
    }
    
    /**
     * Generate Excel template for Rates upload
     */
    public function generateExcelTemplate() {
        // Get reference data for dropdowns
        $publications = $this->getPublications();
        $adCategories = $this->getAdCategories();
        $adSizes = $this->getAdSizes();
        $pagePositions = $this->getPagePositions();
        $colorTypes = $this->getColorTypes();
        $currencies = $this->getCurrencies();
        
        // Create Excel file
        $filename = 'rates_template_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = sys_get_temp_dir() . '/' . $filename;
        
        // For now, always create CSV since PhpSpreadsheet might not be available
        $this->createCsvFile($filepath, $publications, $adCategories, $adSizes, $pagePositions, $colorTypes, $currencies);
        
        return [
            'filename' => $filename,
            'filepath' => $filepath,
            'type' => 'csv'
        ];
    }
    
    /**
     * Create Excel file with template
     */
    private function createExcelFile($filepath, $publications, $adCategories, $adSizes, $pagePositions, $colorTypes, $currencies) {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers
        $headers = [
            'A1' => 'Publication Code',
            'B1' => 'Publication Name',
            'C1' => 'Ad Category',
            'D1' => 'Ad Size',
            'E1' => 'Page Position',
            'F1' => 'Color Type',
            'G1' => 'Base Rate',
            'H1' => 'Effective From',
            'I1' => 'Effective To',
            'J1' => 'Status',
            'K1' => 'Notes'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        
        // Style headers
        $headerRange = 'A1:K1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E3F2FD');
        
        // Add sample data
        $sampleData = [
            ['DG', 'Daily Graphic', 'Display', 'Full Page', 'Front Page', 'Color', '500.00', '2024-01-01', '2024-12-31', 'active', 'Premium placement'],
            ['GT', 'Ghanaian Times', 'Display', 'Half Page', 'Inside', 'B&W', '250.00', '2024-01-01', '2024-12-31', 'active', 'Standard rate']
        ];
        
        $row = 2;
        foreach ($sampleData as $data) {
            $col = 'A';
            foreach ($data as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }
        
        // Add reference sheets
        $this->addReferenceSheet($spreadsheet, 'Publications', $publications);
        $this->addReferenceSheet($spreadsheet, 'Ad Categories', $adCategories);
        $this->addReferenceSheet($spreadsheet, 'Ad Sizes', $adSizes);
        $this->addReferenceSheet($spreadsheet, 'Page Positions', $pagePositions);
        $this->addReferenceSheet($spreadsheet, 'Color Types', $colorTypes);
        $this->addReferenceSheet($spreadsheet, 'Currencies', $currencies);
        
        // Auto-size columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filepath);
    }
    
    /**
     * Create CSV file with template
     */
    private function createCsvFile($filepath, $publications, $adCategories, $adSizes, $pagePositions, $colorTypes, $currencies) {
        $file = fopen($filepath, 'w');
        
        // Headers
        fputcsv($file, [
            'Publication Code',
            'Publication Name', 
            'Ad Category',
            'Ad Size',
            'Page Position',
            'Color Type',
            'Base Rate',
            'Effective From',
            'Effective To',
            'Status',
            'Notes'
        ]);
        
        // Sample data
        fputcsv($file, [
            'DG', 'Daily Graphic', 'Display', 'Full Page', 'Front Page', 'Color', '500.00', '2024-01-01', '2024-12-31', 'active', 'Premium placement'
        ]);
        
        fputcsv($file, [
            'GT', 'Ghanaian Times', 'Display', 'Half Page', 'Inside', 'B&W', '250.00', '2024-01-01', '2024-12-31', 'active', 'Standard rate'
        ]);
        
        fclose($file);
        
        // Create reference file
        $refFilepath = str_replace('.csv', '_reference.csv', $filepath);
        $this->createReferenceCsv($refFilepath, $publications, $adCategories, $adSizes, $pagePositions, $colorTypes, $currencies);
    }
    
    /**
     * Add reference sheet to Excel
     */
    private function addReferenceSheet($spreadsheet, $sheetName, $data) {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($sheetName);
        
        $row = 1;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item['code']);
            $sheet->setCellValue('B' . $row, $item['name']);
            $row++;
        }
        
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
    }
    
    /**
     * Create reference CSV file
     */
    private function createReferenceCsv($filepath, $publications, $adCategories, $adSizes, $pagePositions, $colorTypes, $currencies) {
        $file = fopen($filepath, 'w');
        
        // Publications
        fputcsv($file, ['PUBLICATIONS']);
        fputcsv($file, ['Code', 'Name']);
        foreach ($publications as $pub) {
            fputcsv($file, [$pub['code'], $pub['name']]);
        }
        fputcsv($file, []); // Empty line
        
        // Ad Categories
        fputcsv($file, ['AD CATEGORIES']);
        fputcsv($file, ['Code', 'Name']);
        foreach ($adCategories as $cat) {
            fputcsv($file, [$cat['code'], $cat['name']]);
        }
        fputcsv($file, []); // Empty line
        
        // Continue for other reference data...
        
        fclose($file);
    }
    
    /**
     * Get publications for reference
     */
    private function getPublications() {
        // Return mock data for testing
        return [
            ['code' => 'DG', 'name' => 'Daily Graphic'],
            ['code' => 'GT', 'name' => 'Ghanaian Times'],
            ['code' => 'GM', 'name' => 'Graphic Showbiz'],
            ['code' => 'MW', 'name' => 'Mirror Weekly'],
            ['code' => 'ST', 'name' => 'Sports Today']
        ];
        
        // Uncomment when database tables are ready:
        /*
        $stmt = $this->db->prepare("SELECT code, name FROM publications WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        */
    }
    
    /**
     * Get ad categories for reference
     */
    private function getAdCategories() {
        // Return mock data for testing
        return [
            ['code' => 'DIS', 'name' => 'Display'],
            ['code' => 'CLS', 'name' => 'Classified'],
            ['code' => 'NOT', 'name' => 'Notice'],
            ['code' => 'ANN', 'name' => 'Announcement']
        ];
        
        // Uncomment when database tables are ready:
        /*
        $stmt = $this->db->prepare("SELECT code, name FROM ad_categories WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        */
    }
    
    /**
     * Get ad sizes for reference
     */
    private function getAdSizes() {
        // Return mock data for testing
        return [
            ['code' => 'FP', 'name' => 'Full Page'],
            ['code' => 'HP', 'name' => 'Half Page'],
            ['code' => 'QP', 'name' => 'Quarter Page'],
            ['code' => 'E8', 'name' => 'Eighth Page'],
            ['code' => 'C1', 'name' => 'Column Inch'],
            ['code' => 'SQ', 'name' => 'Square Inch']
        ];
        
        // Uncomment when database tables are ready:
        /*
        $stmt = $this->db->prepare("SELECT code, name FROM ad_sizes WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        */
    }
    
    /**
     * Get page positions for reference
     */
    private function getPagePositions() {
        // Return mock data for testing
        return [
            ['code' => 'FP', 'name' => 'Front Page'],
            ['code' => 'BP', 'name' => 'Back Page'],
            ['code' => 'IN', 'name' => 'Inside'],
            ['code' => 'SP', 'name' => 'Sports Page']
        ];
        
        // Uncomment when database tables are ready:
        /*
        $stmt = $this->db->prepare("SELECT code, name FROM page_positions WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        */
    }
    
    /**
     * Get color types for reference
     */
    private function getColorTypes() {
        // Return mock data for testing
        return [
            ['code' => 'COL', 'name' => 'Color'],
            ['code' => 'BW', 'name' => 'B&W'],
            ['code' => 'SP', 'name' => 'Spot Color']
        ];
        
        // Uncomment when database tables are ready:
        /*
        $stmt = $this->db->prepare("SELECT code, name FROM color_types WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        */
    }
    
    /**
     * Get currencies for reference
     */
    private function getCurrencies() {
        // Return mock data for testing
        return [
            ['code' => 'GHS', 'name' => 'Ghana Cedi'],
            ['code' => 'USD', 'name' => 'US Dollar'],
            ['code' => 'EUR', 'name' => 'Euro']
        ];
        
        // Uncomment when database tables are ready:
        /*
        $stmt = $this->db->prepare("SELECT code, name FROM currencies WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        */
    }
}
