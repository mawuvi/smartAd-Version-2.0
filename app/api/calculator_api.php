<?php
/**
 * Calculator API - Standalone Rate Calculator
 * Handles rate calculation with cascading tax logic
 * Location: app/api/calculator_api.php
 */

// Disable error display to prevent HTML in JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../bootstrap.php';

// Initialize authentication variables
$authGuard = getAuthGuard();
$currentUser = $authGuard->getCurrentUser();

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Debug API call
error_log("Calculator API called - Action: " . $action);
error_log("Calculator API - Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Calculator API - Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Calculator API - User agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'NONE'));

// If no action in GET/POST, try to get from JSON body
if (empty($action) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
}

try {
    switch ($action) {
        case 'calculate_rate':
            calculateRate();
            break;
        case 'calculate_rate_with_discount':
            calculateRateWithDiscountAction();
            break;
        case 'get_reference_data':
            getReferenceData();
            break;
        case 'test':
            // Simple test endpoint without authentication
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Calculator API is working',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'action' => $action,
            'file' => __FILE__,
            'line' => $e->getLine()
        ]
    ]);
} catch (Error $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'debug' => [
            'action' => $action,
            'file' => __FILE__,
            'line' => $e->getLine(),
            'message' => $e->getMessage()
        ]
    ]);
}

/**
 * Calculate rate with dynamic tax rules (no discount)
 */
function calculateRate() {
    // Get parameters
    $criteria = [
        'publication_id' => (int)($_GET['publication_id'] ?? 0),
        'color_type_id' => (int)($_GET['color_type_id'] ?? 0),
        'ad_category_id' => (int)($_GET['ad_category_id'] ?? 0),
        'ad_size_id' => (int)($_GET['ad_size_id'] ?? 0),
        'page_position_id' => (int)($_GET['page_position_id'] ?? 0),
        'insertions' => (int)($_GET['insertions'] ?? 1)
    ];
    
    // Call the main calculation function without discount
    calculateRateWithDiscount($criteria, 0, 'fixed');
}

/**
 * Calculate rate with discount action handler
 */
function calculateRateWithDiscountAction() {
    // Get parameters from GET or POST
    $criteria = [
        'publication_id' => (int)($_GET['publication_id'] ?? $_POST['publication_id'] ?? 0),
        'color_type_id' => (int)($_GET['color_type_id'] ?? $_POST['color_type_id'] ?? 0),
        'ad_category_id' => (int)($_GET['ad_category_id'] ?? $_POST['ad_category_id'] ?? 0),
        'ad_size_id' => (int)($_GET['ad_size_id'] ?? $_POST['ad_size_id'] ?? 0),
        'page_position_id' => (int)($_GET['page_position_id'] ?? $_POST['page_position_id'] ?? 0),
        'insertions' => (int)($_GET['insertions'] ?? $_POST['insertions'] ?? 1)
    ];
    
    $discount_amount = (float)($_GET['discount_amount'] ?? $_POST['discount_amount'] ?? 0);
    $discount_type = $_GET['discount_type'] ?? $_POST['discount_type'] ?? 'fixed';
    
    // Validate discount type
    if (!in_array($discount_type, ['fixed', 'percentage'])) {
        $discount_type = 'fixed';
    }
    
    // Call the main calculation function with discount
    calculateRateWithDiscount($criteria, $discount_amount, $discount_type);
}

/**
 * Get reference data for dropdowns
 */
function getReferenceData() {
    global $authGuard;
    
    try {
        header('Content-Type: application/json');
        
        // Debug authentication
        error_log("Calculator API - User authenticated: " . ($authGuard->isAuthenticated() ? 'YES' : 'NO'));
        error_log("Calculator API - User role: " . ($authGuard->getUser()['role'] ?? 'NONE'));
        error_log("Calculator API - Has calculator.use permission: " . ($authGuard->hasPermission('calculator.use') ? 'YES' : 'NO'));
        error_log("Calculator API - Action: " . ($_GET['action'] ?? 'NONE'));
        
        // Check permission
        $authGuard->requirePermission('calculator.use');
        
        $db = Database::getInstance();
    
    // Get all active publications
    $publications = $db->query("
        SELECT id, name, code 
        FROM publications 
        WHERE deleted_at IS NULL AND LOWER(status) = 'active' 
        ORDER BY name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Log the query result
    error_log("Calculator API - Publications query result: " . json_encode($publications));
    
    // Get all active color types
    $colorTypes = $db->query("
        SELECT id, name, code 
        FROM color_types 
        WHERE deleted_at IS NULL AND LOWER(status) = 'active' 
        ORDER BY name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all active categories
    $categories = $db->query("
        SELECT id, name, code 
        FROM ad_categories 
        WHERE deleted_at IS NULL AND LOWER(status) = 'active' 
        ORDER BY name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all active sizes
    $sizes = $db->query("
        SELECT id, name, code 
        FROM ad_sizes 
        WHERE deleted_at IS NULL AND LOWER(status) = 'active' 
        ORDER BY name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all active positions
    $positions = $db->query("
        SELECT id, name, code 
        FROM page_positions 
        WHERE deleted_at IS NULL AND LOWER(status) = 'active' 
        ORDER BY name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'publications' => $publications,
            'colorTypes' => $colorTypes,
            'categories' => $categories,
            'sizes' => $sizes,
            'positions' => $positions
        ],
        'debug' => [
            'publications_count' => count($publications),
            'color_types_count' => count($colorTypes),
            'categories_count' => count($categories),
            'sizes_count' => count($sizes),
            'positions_count' => count($positions)
        ]
    ]);
    
    } catch (Exception $e) {
        error_log("Calculator API - getReferenceData error: " . $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to load reference data',
            'debug' => [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ]);
    }
}

/**
 * Get taxes for publication with dynamic tier logic and discount rules
 * @param PDO $db
 * @param int $publication_id
 * @return array
 */
function getTaxesWithTierLogic($db, $publication_id) {
    // Get tax rules with priority and discount configuration
    $stmt = $db->prepare("
        SELECT 
            t.id, 
            t.name, 
            t.rate, 
            t.tax_type,
            COALESCE(tr.priority, 
                CASE 
                    WHEN t.tax_type = 'vat' THEN 2 
                    ELSE 1 
                END
            ) as priority,
            COALESCE(tr.apply_on, 
                CASE 
                    WHEN t.tax_type = 'vat' THEN 'cumulative' 
                    ELSE 'base' 
                END
            ) as apply_on,
            COALESCE(tr.discount_applicable, 1) as discount_applicable,
            COALESCE(tr.discount_before_tax, 1) as discount_before_tax,
            tr.notes as tax_rule_notes
        FROM tax_configurations tc
        JOIN taxes t ON tc.tax_id = t.id
        LEFT JOIN tax_rules tr ON t.id = tr.tax_id AND tr.tax_configuration_id = tc.id
        WHERE tc.publication_id = ? 
        AND tc.status = 'active'
        AND t.status = 'active'
        AND t.effective_from <= CURDATE()
        AND (t.effective_to IS NULL OR t.effective_to >= CURDATE())
        AND tc.deleted_at IS NULL
        AND t.deleted_at IS NULL
        ORDER BY 
            COALESCE(tr.priority, 
                CASE 
                    WHEN t.tax_type = 'vat' THEN 2 
                    ELSE 1 
                END
            ) ASC,
            t.name ASC
    ");
    
    $stmt->execute([$publication_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Calculate rate with dynamic tax rules and optional discount
 * @param array $criteria - Rate criteria
 * @param float $discount_amount - Optional discount amount
 * @param string $discount_type - 'percentage' or 'fixed'
 * @return array
 */
function calculateRateWithDiscount($criteria, $discount_amount = 0, $discount_type = 'fixed') {
    global $authGuard;
    
    header('Content-Type: application/json');
    
    // Check permission
    $authGuard->requirePermission('calculator.use');
    
    // Validate required fields
    if (!$criteria['publication_id'] || !$criteria['color_type_id'] || 
        !$criteria['ad_category_id'] || !$criteria['ad_size_id'] || !$criteria['page_position_id']) {
        throw new Exception('All rate criteria must be provided');
    }
    
    if ($criteria['insertions'] < 1) {
        throw new Exception('Insertions must be at least 1');
    }
    
    $db = Database::getInstance();
    $rateModel = new RateModel();
    
    // 1. Find rate using RateModel->findRate()
    $rate = $rateModel->findRate($criteria);
    if (!$rate) {
        throw new Exception('No rate found for the selected criteria');
    }
    
    $base_rate = (float)$rate['base_rate'];
    $insertions = (int)$criteria['insertions'];
    
    // 2. Calculate base subtotal
    $base_subtotal = $base_rate * $insertions;
    
    // 3. Apply discount if provided
    $discount_amount_calculated = 0;
    if ($discount_amount > 0) {
        if ($discount_type === 'percentage') {
            $discount_amount_calculated = ($base_subtotal * $discount_amount) / 100;
        } else {
            $discount_amount_calculated = $discount_amount;
        }
    }
    
    $subtotal_after_discount = $base_subtotal - $discount_amount_calculated;
    
    // 4. Get dynamic tax rules for publication
    $tax_rules = getTaxesWithTierLogic($db, $criteria['publication_id']);
    
    // 5. Apply taxes based on dynamic rules
    $tax_calculations = [];
    $running_total = $subtotal_after_discount;
    
    foreach ($tax_rules as $rule) {
        $tax_amount = 0;
        $calculation_base = $running_total;
        
        // Determine calculation base based on rule
        if ($rule['apply_on'] === 'base') {
            $calculation_base = $subtotal_after_discount;
        } else {
            $calculation_base = $running_total;
        }
        
        // Apply discount rules
        if ($rule['discount_applicable'] && $discount_amount > 0) {
            if ($rule['discount_before_tax']) {
                // Discount already applied to calculation_base
                $tax_amount = ($calculation_base * $rule['rate']) / 100;
            } else {
                // Apply tax to original amount, then subtract discount
                $tax_amount = (($calculation_base + $discount_amount_calculated) * $rule['rate']) / 100;
            }
        } else {
            // No discount consideration
            $tax_amount = ($calculation_base * $rule['rate']) / 100;
        }
        
        $tax_calculations[] = [
            'name' => $rule['name'],
            'rate' => $rule['rate'],
            'amount' => $tax_amount,
            'amount_formatted' => DataStandardizationHelper::formatCurrency($tax_amount),
            'priority' => $rule['priority'],
            'apply_on' => $rule['apply_on'],
            'discount_applicable' => (bool)$rule['discount_applicable'],
            'discount_before_tax' => (bool)$rule['discount_before_tax'],
            'calculation_base' => $calculation_base
        ];
        
        // Update running total for cumulative taxes
        if ($rule['apply_on'] === 'cumulative') {
            $running_total += $tax_amount;
        }
    }
    
    // 6. Calculate final total
    $total_tax = array_sum(array_column($tax_calculations, 'amount'));
    $final_total = $subtotal_after_discount + $total_tax;
    
    // Get metadata for display
    $metadata = [
        'publication_name' => $rate['publication_name'] ?? 'Unknown',
        'color_type' => $rate['color_type_name'] ?? 'Unknown',
        'category' => $rate['ad_category_name'] ?? 'Unknown',
        'size' => $rate['ad_size_name'] ?? 'Unknown',
        'position' => $rate['page_position_name'] ?? 'Unknown',
        'effective_from' => $rate['effective_from'] ?? null,
        'effective_to' => $rate['effective_to'] ?? null,
        'discount_applied' => $discount_amount_calculated > 0,
        'discount_amount' => $discount_amount_calculated,
        'discount_type' => $discount_type
    ];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'rate_id' => $rate['id'],
            'base_rate' => $base_rate,
            'base_rate_formatted' => DataStandardizationHelper::formatCurrency($base_rate),
            'insertions' => $insertions,
            'base_subtotal' => $base_subtotal,
            'base_subtotal_formatted' => DataStandardizationHelper::formatCurrency($base_subtotal),
            'discount_amount' => $discount_amount_calculated,
            'discount_amount_formatted' => DataStandardizationHelper::formatCurrency($discount_amount_calculated),
            'subtotal_after_discount' => $subtotal_after_discount,
            'subtotal_after_discount_formatted' => DataStandardizationHelper::formatCurrency($subtotal_after_discount),
            'tax_calculations' => $tax_calculations,
            'total_tax' => $total_tax,
            'total_tax_formatted' => DataStandardizationHelper::formatCurrency($total_tax),
            'final_total' => $final_total,
            'final_total_formatted' => DataStandardizationHelper::formatCurrency($final_total),
            'metadata' => $metadata
        ]
    ]);
}
