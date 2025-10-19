<?php

class TemplateGenerator {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Generate template for a specific entity type
     */
    public function generateTemplate($entityType, $format = 'excel') {
        switch ($entityType) {
            case 'publications':
                return $this->generatePublicationsTemplate($format);
            case 'taxes':
                return $this->generateTaxesTemplate($format);
            case 'ad_categories':
                return $this->generateAdCategoriesTemplate($format);
            case 'ad_sizes':
                return $this->generateAdSizesTemplate($format);
            case 'page_positions':
                return $this->generatePagePositionsTemplate($format);
            case 'color_types':
                return $this->generateColorTypesTemplate($format);
            case 'payment_types':
                return $this->generatePaymentTypesTemplate($format);
            case 'industries':
                return $this->generateIndustriesTemplate($format);
            case 'currencies':
                return $this->generateCurrenciesTemplate($format);
            case 'base_rates':
                return $this->generateBaseRatesTemplate($format);
            default:
                throw new Exception("Unknown entity type: {$entityType}");
        }
    }

    /**
     * Generate Publications template
     */
    private function generatePublicationsTemplate($format) {
        $headers = ['Name', 'Code', 'Description', 'Type', 'Circulation', 'Frequency', 'Base Rate', 'Status'];
        $sampleData = [
            ['Daily Graphic', 'DG', 'Leading daily newspaper', 'newspaper', '50000', 'Daily', '100.00', 'active'],
            ['Ghanaian Times', 'GT', 'Government daily newspaper', 'newspaper', '30000', 'Daily', '80.00', 'active'],
            ['Business & Financial Times', 'BFT', 'Business newspaper', 'newspaper', '15000', 'Weekly', '120.00', 'active']
        ];

        return $this->createTemplate($headers, $sampleData, $format, 'publications');
    }

    /**
     * Generate Taxes template
     */
    private function generateTaxesTemplate($format) {
        $headers = ['Name', 'Code', 'Rate (%)', 'Type', 'Effective From', 'Effective To', 'Description', 'Status'];
        $sampleData = [
            ['VAT', 'VAT', '12.5', 'Value Added Tax', '2024-01-01', '', 'Standard VAT rate', 'active'],
            ['NHIL', 'NHIL', '2.5', 'National Health Insurance Levy', '2024-01-01', '', 'Health insurance levy', 'active'],
            ['COVID', 'COVID', '1.0', 'COVID-19 Recovery Levy', '2024-01-01', '', 'COVID recovery levy', 'active']
        ];

        return $this->createTemplate($headers, $sampleData, $format, 'taxes');
    }

    /**
     * Generate Ad Categories template
     */
    private function generateAdCategoriesTemplate($format) {
        $headers = ['Name', 'Code', 'Description', 'Type', 'Multiplier', 'Status'];
        $sampleData = [
            ['Classified', 'CLASS', 'Small text advertisements', 'text', '1.0', 'active'],
            ['Display', 'DISP', 'Large visual advertisements', 'visual', '2.0', 'active'],
            ['Business Directory', 'BIZ', 'Business listing advertisements', 'directory', '1.5', 'active']
        ];

        return $this->createTemplate($headers, $sampleData, $format, 'ad_categories');
    }

    /**
     * Generate Ad Sizes template
     */
    private function generateAdSizesTemplate($format) {
        $headers = ['Name', 'Code', 'Width (cm)', 'Height (cm)', 'Description', 'Status'];
        $sampleData = [
            ['Quarter Page', 'QP', '15.0', '20.0', 'Quarter page advertisement', 'active'],
            ['Half Page', 'HP', '30.0', '20.0', 'Half page advertisement', 'active'],
            ['Full Page', 'FP', '30.0', '40.0', 'Full page advertisement', 'active']
        ];

        return $this->createTemplate($headers, $sampleData, $format, 'ad_sizes');
    }

    /**
     * Generate Page Positions template
     */
    private function generatePagePositionsTemplate($format) {
        $headers = ['Name', 'Code', 'Description', 'Type', 'Multiplier', 'Status'];
        $sampleData = [
            ['Front Page', 'FP', 'Front page placement', 'premium', '3.0', 'active'],
            ['Back Page', 'BP', 'Back page placement', 'premium', '2.5', 'active'],
            ['Inside Page', 'IP', 'Inside page placement', 'standard', '1.0', 'active']
        ];

        return $this->createTemplate($headers, $sampleData, $format, 'page_positions');
    }

    /**
     * Generate Color Types template
     */
    private function generateColorTypesTemplate($format) {
        $headers = ['Name', 'Code', 'Description', 'Mode', 'Multiplier', 'Status'];
        $sampleData = [
            ['Black & White', 'BW', 'Black and white advertisement', 'monochrome', '1.0', 'active'],
            ['Full Color', 'FC', 'Full color advertisement', 'color', '2.0', 'active'],
            ['Spot Color', 'SC', 'Spot color advertisement', 'spot', '1.5', 'active']
        ];

        return $this->createTemplate($headers, $sampleData, $format, 'color_types');
    }

    /**
     * Generate Payment Types template
     */
    private function generatePaymentTypesTemplate($format) {
        $headers = ['Name', 'Code', 'Description', 'Method Type', 'Status'];
        $sampleData = [
            ['Cash', 'CASH', 'Cash payment', 'cash', 'active'],
            ['Bank Transfer', 'BANK', 'Bank transfer payment', 'bank', 'active'],
            ['Mobile Money', 'MM', 'Mobile money payment', 'mobile', 'active']
        ];

        return $this->createTemplate($headers, $sampleData, $format, 'payment_types');
    }

    /**
     * Generate Industries template
     */
    private function generateIndustriesTemplate($format) {
        $headers = ['Name', 'Code', 'Description', 'Sector', 'Status'];
        $sampleData = [
            ['Banking', 'BANK', 'Banking and financial services', 'financial', 'active'],
            ['Telecommunications', 'TELECOM', 'Telecommunications services', 'technology', 'active'],
            ['Healthcare', 'HEALTH', 'Healthcare and medical services', 'healthcare', 'active']
        ];

        return $this->createTemplate($headers, $sampleData, $format, 'industries');
    }

    /**
     * Generate Currencies template
     */
    private function generateCurrenciesTemplate($format) {
        $headers = ['Name', 'Code', 'Symbol', 'Exchange Rate', 'Status'];
        $sampleData = [
            ['Ghana Cedi', 'GHS', '₵', '1.0000', 'active'],
            ['US Dollar', 'USD', '$', '12.5000', 'active'],
            ['Euro', 'EUR', '€', '13.7500', 'active']
        ];

        return $this->createTemplate($headers, $sampleData, $format, 'currencies');
    }

    /**
     * Generate Base Rates template (Complex - includes dependencies)
     */
    private function generateBaseRatesTemplate($format) {
        $headers = [
            'Publication Name', 'Color Type Name', 'Ad Category Name', 
            'Ad Size Name', 'Page Position Name', 'Base Rate', 
            'Effective From', 'Effective To', 'Notes', 'Status'
        ];
        
        $sampleData = [
            [
                'Daily Graphic', 'Full Color', 'Display', 'Full Page', 'Front Page',
                '500.00', '2024-01-01', '', 'Premium placement rate', 'active'
            ],
            [
                'Ghanaian Times', 'Black & White', 'Classified', 'Quarter Page', 'Inside Page',
                '50.00', '2024-01-01', '', 'Standard classified rate', 'active'
            ],
            [
                'Business & Financial Times', 'Spot Color', 'Business Directory', 'Half Page', 'Back Page',
                '200.00', '2024-01-01', '', 'Business directory rate', 'active'
            ]
        ];

        return $this->createTemplate($headers, $sampleData, $format, 'base_rates');
    }

    /**
     * Create template in specified format
     */
    private function createTemplate($headers, $sampleData, $format, $entityType) {
        if ($format === 'csv') {
            return $this->createCSVTemplate($headers, $sampleData, $entityType);
        } elseif ($format === 'excel') {
            return $this->createExcelTemplate($headers, $sampleData, $entityType);
        } else {
            throw new Exception("Unsupported format: {$format}");
        }
    }

    /**
     * Create CSV template
     */
    private function createCSVTemplate($headers, $sampleData, $entityType) {
        $csv = '';
        
        // Add headers
        $csv .= implode(',', array_map([$this, 'escapeCSV'], $headers)) . "\n";
        
        // Add sample data
        foreach ($sampleData as $row) {
            $csv .= implode(',', array_map([$this, 'escapeCSV'], $row)) . "\n";
        }
        
        // Add instructions
        $csv .= "\n";
        $csv .= "# Instructions:\n";
        $csv .= "# 1. Fill in your data in the rows above\n";
        $csv .= "# 2. Remove sample data before uploading\n";
        $csv .= "# 3. Required fields: " . implode(', ', $this->getRequiredFields($entityType)) . "\n";
        $csv .= "# 4. Status must be 'active' or 'inactive'\n";
        $csv .= "# 5. Date format: YYYY-MM-DD\n";
        $csv .= "# 6. Numeric fields should not contain currency symbols\n";
        
        return $csv;
    }

    /**
     * Create Excel template (simplified - would need PhpSpreadsheet for full implementation)
     */
    private function createExcelTemplate($headers, $sampleData, $entityType) {
        // For now, return CSV format
        // In a full implementation, you would use PhpSpreadsheet to create actual Excel files
        return $this->createCSVTemplate($headers, $sampleData, $entityType);
    }

    /**
     * Escape CSV values
     */
    private function escapeCSV($value) {
        if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
            return '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }

    /**
     * Get required fields for entity type
     */
    private function getRequiredFields($entityType) {
        $requiredFields = [
            'publications' => ['Name', 'Code'],
            'taxes' => ['Name', 'Code', 'Rate (%)'],
            'ad_categories' => ['Name', 'Code'],
            'ad_sizes' => ['Name', 'Code', 'Width (cm)', 'Height (cm)'],
            'page_positions' => ['Name', 'Code'],
            'color_types' => ['Name', 'Code'],
            'payment_types' => ['Name', 'Code'],
            'industries' => ['Name', 'Code'],
            'currencies' => ['Name', 'Code', 'Symbol'],
            'base_rates' => ['Publication Name', 'Color Type Name', 'Ad Category Name', 'Ad Size Name', 'Page Position Name', 'Base Rate']
        ];
        
        return $requiredFields[$entityType] ?? ['Name', 'Code'];
    }

    /**
     * Get validation rules for entity type
     */
    public function getValidationRules($entityType) {
        $rules = [
            'publications' => [
                'Name' => ['required', 'string', 'max:255'],
                'Code' => ['required', 'string', 'max:50', 'unique'],
                'Type' => ['in:newspaper,magazine,online'],
                'Circulation' => ['integer', 'min:0'],
                'Base Rate' => ['numeric', 'min:0'],
                'Status' => ['required', 'in:active,inactive']
            ],
            'taxes' => [
                'Name' => ['required', 'string', 'max:255'],
                'Code' => ['required', 'string', 'max:50', 'unique'],
                'Rate (%)' => ['required', 'numeric', 'min:0', 'max:100'],
                'Type' => ['required', 'string', 'max:100'],
                'Effective From' => ['date'],
                'Effective To' => ['date', 'after:Effective From'],
                'Status' => ['required', 'in:active,inactive']
            ],
            'ad_categories' => [
                'Name' => ['required', 'string', 'max:255'],
                'Code' => ['required', 'string', 'max:50', 'unique'],
                'Multiplier' => ['numeric', 'min:0'],
                'Status' => ['required', 'in:active,inactive']
            ],
            'ad_sizes' => [
                'Name' => ['required', 'string', 'max:255'],
                'Code' => ['required', 'string', 'max:50', 'unique'],
                'Width (cm)' => ['required', 'numeric', 'min:0'],
                'Height (cm)' => ['required', 'numeric', 'min:0'],
                'Status' => ['required', 'in:active,inactive']
            ],
            'page_positions' => [
                'Name' => ['required', 'string', 'max:255'],
                'Code' => ['required', 'string', 'max:50', 'unique'],
                'Multiplier' => ['numeric', 'min:0'],
                'Status' => ['required', 'in:active,inactive']
            ],
            'color_types' => [
                'Name' => ['required', 'string', 'max:255'],
                'Code' => ['required', 'string', 'max:50', 'unique'],
                'Mode' => ['in:monochrome,color,spot'],
                'Multiplier' => ['numeric', 'min:0'],
                'Status' => ['required', 'in:active,inactive']
            ],
            'payment_types' => [
                'Name' => ['required', 'string', 'max:255'],
                'Code' => ['required', 'string', 'max:50', 'unique'],
                'Method Type' => ['string', 'max:100'],
                'Status' => ['required', 'in:active,inactive']
            ],
            'industries' => [
                'Name' => ['required', 'string', 'max:255'],
                'Code' => ['required', 'string', 'max:50', 'unique'],
                'Sector' => ['string', 'max:100'],
                'Status' => ['required', 'in:active,inactive']
            ],
            'currencies' => [
                'Name' => ['required', 'string', 'max:255'],
                'Code' => ['required', 'string', 'max:10', 'unique'],
                'Symbol' => ['required', 'string', 'max:10'],
                'Exchange Rate' => ['numeric', 'min:0'],
                'Status' => ['required', 'in:active,inactive']
            ],
            'base_rates' => [
                'Publication Name' => ['required', 'string', 'max:255'],
                'Color Type Name' => ['required', 'string', 'max:255'],
                'Ad Category Name' => ['required', 'string', 'max:255'],
                'Ad Size Name' => ['required', 'string', 'max:255'],
                'Page Position Name' => ['required', 'string', 'max:255'],
                'Base Rate' => ['required', 'numeric', 'min:0'],
                'Effective From' => ['required', 'date'],
                'Effective To' => ['date', 'after:Effective From'],
                'Status' => ['required', 'in:active,inactive']
            ]
        ];
        
        return $rules[$entityType] ?? [];
    }

    /**
     * Get field descriptions for entity type
     */
    public function getFieldDescriptions($entityType) {
        $descriptions = [
            'publications' => [
                'Name' => 'Full name of the publication',
                'Code' => 'Unique short code for the publication',
                'Description' => 'Brief description of the publication',
                'Type' => 'Type of publication (newspaper, magazine, online)',
                'Circulation' => 'Number of copies distributed',
                'Frequency' => 'How often the publication is released',
                'Base Rate' => 'Base advertising rate in GHS',
                'Status' => 'Whether the publication is active or inactive'
            ],
            'taxes' => [
                'Name' => 'Full name of the tax',
                'Code' => 'Unique short code for the tax',
                'Rate (%)' => 'Tax rate as a percentage',
                'Type' => 'Type of tax (VAT, NHIL, etc.)',
                'Effective From' => 'Date when the tax becomes effective',
                'Effective To' => 'Date when the tax expires (leave blank if ongoing)',
                'Description' => 'Brief description of the tax',
                'Status' => 'Whether the tax is active or inactive'
            ],
            'base_rates' => [
                'Publication Name' => 'Name of the publication (must exist or will be created)',
                'Color Type Name' => 'Name of the color type (must exist or will be created)',
                'Ad Category Name' => 'Name of the ad category (must exist or will be created)',
                'Ad Size Name' => 'Name of the ad size (must exist or will be created)',
                'Page Position Name' => 'Name of the page position (must exist or will be created)',
                'Base Rate' => 'Base advertising rate in GHS',
                'Effective From' => 'Date when the rate becomes effective',
                'Effective To' => 'Date when the rate expires (leave blank if ongoing)',
                'Notes' => 'Additional notes about the rate',
                'Status' => 'Whether the rate is active or inactive'
            ]
        ];
        
        return $descriptions[$entityType] ?? [];
    }
}
