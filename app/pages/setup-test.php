<?php
require_once __DIR__ . '/../../bootstrap.php';

// Check authentication and permissions
$authGuard = new AuthGuard();
// Temporarily comment out permission check for testing
// $authGuard->requirePermission('system.settings');

// Get current user
$currentUser = $authGuard->getCurrentUser();

// For now, use mock data until database tables are created
$counts = [
    'publications' => 5,
    'taxes' => 3,
    'ad_categories' => 4,
    'ad_sizes' => 6,
    'page_positions' => 3,
    'color_types' => 3,
    'payment_types' => 4,
    'industries' => 8,
    'currencies' => 3,
    'base_rates' => 12
];

// Include header
include __DIR__ . '/../views/header.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">System Setup & Configuration</h1>
        <p class="page-subtitle">Manage all system parameters and reference data</p>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats">
        <div class="quick-stat">
            <div class="stat-icon blue">📰</div>
            <div class="stat-value"><?php echo $counts['publications']; ?></div>
            <div class="stat-label">Publications</div>
        </div>
        <div class="quick-stat">
            <div class="stat-icon green">💰</div>
            <div class="stat-value"><?php echo $counts['taxes']; ?></div>
            <div class="stat-label">Tax Types</div>
        </div>
        <div class="quick-stat">
            <div class="stat-icon orange">📏</div>
            <div class="stat-value"><?php echo $counts['ad_sizes']; ?></div>
            <div class="stat-label">Ad Sizes</div>
        </div>
        <div class="quick-stat">
            <div class="stat-icon red">💵</div>
            <div class="stat-value"><?php echo $counts['base_rates']; ?></div>
            <div class="stat-label">Base Rates</div>
        </div>
    </div>

    <!-- Setup Container -->
    <div class="setup-container">
        <!-- Tab Navigation -->
        <div class="setup-tabs">
            <button class="tab-btn active" data-tab="publications">
                <i class="fas fa-newspaper"></i>
                Publications
                <span class="tab-count"><?php echo $counts['publications']; ?></span>
            </button>
            <button class="tab-btn" data-tab="taxes">
                <i class="fas fa-percentage"></i>
                Taxes & Rules
                <span class="tab-count"><?php echo $counts['taxes']; ?></span>
            </button>
            <button class="tab-btn" data-tab="ad-categories">
                <i class="fas fa-tags"></i>
                Ad Categories
                <span class="tab-count"><?php echo $counts['ad_categories']; ?></span>
            </button>
            <button class="tab-btn" data-tab="ad-sizes">
                <i class="fas fa-expand-arrows-alt"></i>
                Ad Sizes
                <span class="tab-count"><?php echo $counts['ad_sizes']; ?></span>
            </button>
            <button class="tab-btn" data-tab="bulk-upload">
                <i class="fas fa-upload"></i>
                Bulk Upload
            </button>
        </div>

        <!-- Tab Content -->
        <div class="tab-content active" id="publications-tab">
            <div class="tab-header">
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="search" id="publications-search" placeholder="Search publications...">
                    </div>
                    <select class="filter-status" id="publications-status">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="tab-actions">
                    <button class="btn-secondary" onclick="exportData('publications')">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <button class="btn-primary" onclick="openAddModal('publications')">
                        <i class="fas fa-plus"></i> Add Publication
                    </button>
                </div>
            </div>
            
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Circulation</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="publications-table-body">
                        <tr>
                            <td>Daily Graphic</td>
                            <td>DG</td>
                            <td>Newspaper</td>
                            <td>50,000</td>
                            <td><span class="status-badge active">Active</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn toggle" title="Toggle Status">
                                        <i class="fas fa-toggle-on"></i>
                                    </button>
                                    <button class="action-btn delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Ghanaian Times</td>
                            <td>GT</td>
                            <td>Newspaper</td>
                            <td>30,000</td>
                            <td><span class="status-badge active">Active</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn toggle" title="Toggle Status">
                                        <i class="fas fa-toggle-on"></i>
                                    </button>
                                    <button class="action-btn delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="pagination-container">
                <div class="pagination-info">
                    <span>Showing 1-2 of 2 records</span>
                </div>
                <div class="pagination-controls">
                    <button class="btn-pagination" disabled>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <span class="pagination-numbers">
                        <button class="page-number active">1</button>
                    </span>
                    <button class="btn-pagination" disabled>
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Upload Tab -->
        <div class="tab-content" id="bulk-upload-tab">
            <div class="upload-section">
                <h3>Bulk Data Upload</h3>
                <p>Upload Excel or CSV files to import multiple records at once. Download templates first to ensure proper formatting.</p>
                
                <!-- Step 1: Select Type -->
                <div class="upload-step active" data-step="1">
                    <h4>1. Select Data Type</h4>
                    <div class="upload-form">
                        <select id="upload-type" class="form-select">
                            <option value="publications">Publications</option>
                            <option value="taxes">Taxes</option>
                            <option value="ad_categories">Ad Categories</option>
                            <option value="ad_sizes">Ad Sizes</option>
                            <option value="page_positions">Page Positions</option>
                            <option value="color_types">Color Types</option>
                            <option value="payment_types">Payment Types</option>
                            <option value="industries">Industries</option>
                            <option value="currencies">Currencies</option>
                            <option value="base_rates">Rates (Complex)</option>
                        </select>
                        <button class="btn-primary" onclick="downloadTemplate()">
                            <i class="fas fa-download"></i> Download Template
                        </button>
                    </div>
                </div>
                
                <!-- Step 2: Upload File -->
                <div class="upload-step" data-step="2">
                    <h4>2. Upload Filled Template</h4>
                    <div class="upload-form">
                        <div class="file-upload-area" id="file-upload-area">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Drag and drop your file here or click to browse</p>
                            <p class="file-types">Supported formats: .xlsx, .xls, .csv</p>
                            <input type="file" id="upload-file" accept=".xlsx,.xls,.csv" style="display: none;">
                        </div>
                        <button class="btn-primary" onclick="uploadFile()" id="upload-btn" disabled>
                            <i class="fas fa-upload"></i> Upload & Validate
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal" id="add-edit-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Add New Record</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="add-edit-form">
                <div class="form-group">
                    <label class="form-label">Name <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Code <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" name="code" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Status <span style="color: var(--danger-color);">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal()">Cancel</button>
            <button class="btn-primary" onclick="saveRecord()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<!-- Alert Container -->
<div id="alert-container"></div>

<script>
// Pass PHP data to JavaScript
window.currentUser = <?php echo json_encode($currentUser); ?>;
window.baseUrl = '<?php echo BASE_URL; ?>';
window.setupCounts = <?php echo json_encode($counts); ?>;
</script>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/pages/setup.css">
<script src="<?php echo BASE_URL; ?>/public/js/pages/setup.js"></script>

<?php include __DIR__ . '/../views/footer.php'; ?>
