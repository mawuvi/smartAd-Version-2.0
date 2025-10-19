<?php
/**
 * Rates Page - Rate Calculator Interface
 * Uses RateCalculatorModule for consistent rate calculation
 */

require_once __DIR__ . '/../../bootstrap.php';

// Check authentication and permissions
$authGuard = new AuthGuard();
$authGuard->requirePermission('rates.view');

// Get current user
$currentUser = $authGuard->getCurrentUser();

// Load reference data for dropdowns
$db = Database::getInstance();
$referenceData = [];

try {
    // Load all reference data
    $referenceData['publications'] = $db->query("SELECT id, name FROM publications WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $referenceData['colorTypes'] = $db->query("SELECT id, name FROM color_types WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $referenceData['adCategories'] = $db->query("SELECT id, name FROM ad_categories WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $referenceData['adSizes'] = $db->query("SELECT id, name FROM ad_sizes WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $referenceData['pagePositions'] = $db->query("SELECT id, name FROM page_positions WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error loading reference data: " . $e->getMessage());
    $referenceData = [
        'publications' => [],
        'colorTypes' => [],
        'adCategories' => [],
        'adSizes' => [],
        'pagePositions' => []
    ];
}

// Include header
include __DIR__ . '/../views/header.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Rate Calculator</h1>
        <p class="page-subtitle">Calculate advertisement rates based on publication, size, and position</p>
    </div>

    <div class="rate-calculator-container">
        <div class="calculator-form">
            <form id="rateCalculatorForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="publication">Publication *</label>
                        <select id="publication" name="publication" required>
                            <option value="">Select Publication</option>
                            <?php foreach ($referenceData['publications'] as $publication): ?>
                                <option value="<?php echo htmlspecialchars($publication['id']); ?>">
                                    <?php echo htmlspecialchars($publication['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="colorType">Color Type *</label>
                        <select id="colorType" name="colorType" required>
                            <option value="">Select Color Type</option>
                            <?php foreach ($referenceData['colorTypes'] as $colorType): ?>
                                <option value="<?php echo htmlspecialchars($colorType['id']); ?>">
                                    <?php echo htmlspecialchars($colorType['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="adCategory">Ad Category *</label>
                        <select id="adCategory" name="adCategory" required>
                            <option value="">Select Ad Category</option>
                            <?php foreach ($referenceData['adCategories'] as $adCategory): ?>
                                <option value="<?php echo htmlspecialchars($adCategory['id']); ?>">
                                    <?php echo htmlspecialchars($adCategory['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="adSize">Ad Size *</label>
                        <select id="adSize" name="adSize" required>
                            <option value="">Select Ad Size</option>
                            <?php foreach ($referenceData['adSizes'] as $adSize): ?>
                                <option value="<?php echo htmlspecialchars($adSize['id']); ?>">
                                    <?php echo htmlspecialchars($adSize['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="pagePosition">Page Position *</label>
                        <select id="pagePosition" name="pagePosition" required>
                            <option value="">Select Page Position</option>
                            <?php foreach ($referenceData['pagePositions'] as $pagePosition): ?>
                                <option value="<?php echo htmlspecialchars($pagePosition['id']); ?>">
                                    <?php echo htmlspecialchars($pagePosition['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-calculator"></i> Calculate Rate
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="rate-result" id="rateResult" style="display: none;">
            <div class="result-card">
                <h3>Rate Calculation Result</h3>
                <div class="result-details">
                    <div class="result-item">
                        <span class="label">Base Rate:</span>
                        <span class="value" id="baseRate">₵0.00</span>
                    </div>
                    <div class="result-item">
                        <span class="label">Total Rate:</span>
                        <span class="value total" id="totalRate">₵0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include page-specific CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/pages/rates.css">

<!-- Include required JavaScript -->
<script src="<?php echo BASE_URL; ?>/public/js/modules/rateCalculatorModule.js"></script>

<script>
// Pass data to JavaScript
window.baseUrl = '<?php echo BASE_URL; ?>';
window.currentUser = <?php echo json_encode($currentUser); ?>;
window.referenceData = <?php echo json_encode($referenceData); ?>;
</script>

<?php include __DIR__ . '/../views/footer.php'; ?>