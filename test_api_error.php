<?php
// Test the calculator API directly to see the error
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing Calculator API...\n";

// Simulate the API call
$_GET['action'] = 'get_reference_data';

try {
    require_once 'app/api/calculator_api.php';
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
