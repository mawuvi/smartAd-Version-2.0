<?php
// Simple test file to debug template generation
require_once __DIR__ . '/bootstrap_api.php';

try {
    echo "Testing template generation...\n";
    
    require_once __DIR__ . '/app/helpers/RatesTemplateGenerator.php';
    
    $generator = new RatesTemplateGenerator();
    echo "Generator created successfully\n";
    
    $template = $generator->generateExcelTemplate();
    echo "Template generated successfully\n";
    echo "Filename: " . $template['filename'] . "\n";
    echo "Filepath: " . $template['filepath'] . "\n";
    echo "Type: " . $template['type'] . "\n";
    
    if (file_exists($template['filepath'])) {
        echo "File exists, size: " . filesize($template['filepath']) . " bytes\n";
        echo "First 200 characters:\n";
        echo substr(file_get_contents($template['filepath']), 0, 200) . "\n";
    } else {
        echo "ERROR: File does not exist!\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
