<?php
require_once __DIR__ . '/../../bootstrap.php';

// Check authentication and permissions
$authGuard = new AuthGuard();
$authGuard->requirePermission('system.settings');

// Get current user
$currentUser = $authGuard->getCurrentUser();

// Mock data for all 33 setup categories
$counts = [
    // Row 1: Core Media Setup
    'publications' => 5,
    'ad_categories' => 4,
    'ad_sizes' => 6,
    'page_positions' => 3,
    'color_types' => 3,
    
    // Row 2: Financial Setup
    'rates' => 12,
    'taxes' => 3,
    'currencies' => 3,
    'payment_types' => 4,
    'discount_types' => 0,
    
    // Row 3: Geographic & Business Setup
    'industries' => 8,
    'countries' => 0,
    'regions' => 0,
    'cities' => 0,
    'client_types' => 0,
    
    // Row 4: User & Commission Setup
    'users' => 0,
    'roles' => 0,
    'company_setup' => 1,
    'commission_types' => 0,
    'credit_types' => 0,
    
    // Row 5: Advanced & Tools
    'system_settings' => 1,
    'email_templates' => 0,
    'report_templates' => 0,
    'sms_integration' => 1,
    'storage_integration' => 1,
    'bulk_upload' => 0
];

// RBAC Helper for granular permissions
$rbacHelper = new RBACHelper();
$userRole = $rbacHelper->getUserRole($currentUser['id']);

// Include header
include __DIR__ . '/../views/header.php';
?>

<script>
// Add setup-page class to body for CSS targeting
document.body.classList.add('setup-page');
</script>

<div class="main-content">
    <!-- Page Header -->
    <div class="module-header">
        <div class="module-header-content">
            <h1 class="module-title">System Setup & Configuration</h1>
            <p class="module-subtitle">Manage all system parameters and reference data</p>
        </div>
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
            <div class="stat-value"><?php echo $counts['rates']; ?></div>
            <div class="stat-label">Rates</div>
        </div>
        <div class="quick-stat">
            <div class="stat-icon orange">📏</div>
            <div class="stat-value"><?php echo $counts['ad_sizes']; ?></div>
            <div class="stat-label">Ad Sizes</div>
        </div>
        <div class="quick-stat">
            <div class="stat-icon red">👥</div>
            <div class="stat-value"><?php echo $counts['users']; ?></div>
            <div class="stat-label">Users</div>
        </div>
    </div>

    <!-- Setup Container with Sidebar -->
    <div class="setup-container-sidebar">
        <!-- Left Sidebar -->
        <aside class="setup-sidebar">
            <nav class="sidebar-nav">
                <!-- Core Media Setup -->
                <?php 
                $hasCoreMediaAccess = 
                    $rbacHelper->hasPermission($userRole, 'setup.publications.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.ad_categories.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.ad_sizes.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.page_positions.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.color_types.view');

                if ($hasCoreMediaAccess): 
                ?>
                <div class="nav-group">
                    <button class="nav-group-header active" onclick="toggleNavGroup(this)">
                        <i class="fas fa-chevron-down"></i>
                        <span>Core Media Setup</span>
                    </button>
                    <div class="nav-group-items">
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.publications.view')): ?>
                        <a href="#" class="nav-item active" data-tab="publications">
                            <i class="fas fa-newspaper"></i>
                            <span>Publications</span>
                            <span class="nav-count"><?php echo $counts['publications']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.ad_categories.view')): ?>
                        <a href="#" class="nav-item" data-tab="ad-categories">
                        <i class="fas fa-tags"></i>
                            <span>Ad Categories</span>
                            <span class="nav-count"><?php echo $counts['ad_categories']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.ad_sizes.view')): ?>
                        <a href="#" class="nav-item" data-tab="ad-sizes">
                        <i class="fas fa-expand-arrows-alt"></i>
                            <span>Ad Sizes</span>
                            <span class="nav-count"><?php echo $counts['ad_sizes']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.page_positions.view')): ?>
                        <a href="#" class="nav-item" data-tab="page-positions">
                        <i class="fas fa-map-marker-alt"></i>
                            <span>Page Positions</span>
                            <span class="nav-count"><?php echo $counts['page_positions']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.color_types.view')): ?>
                        <a href="#" class="nav-item" data-tab="color-types">
                        <i class="fas fa-palette"></i>
                            <span>Color Types</span>
                            <span class="nav-count"><?php echo $counts['color_types']; ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
                <?php endif; ?>
                
                <!-- Financial Setup -->
                <?php 
                $hasFinancialAccess = 
                    $rbacHelper->hasPermission($userRole, 'setup.rates.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.taxes.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.currencies.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.payment_types.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.discount_types.view');

                if ($hasFinancialAccess): 
                ?>
                <div class="nav-group">
                    <button class="nav-group-header" onclick="toggleNavGroup(this)">
                        <i class="fas fa-chevron-right"></i>
                        <span>Financial Setup</span>
                    </button>
                    <div class="nav-group-items" style="display: none;">
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.rates.view')): ?>
                        <a href="#" class="nav-item" data-tab="rates">
                            <i class="fas fa-dollar-sign"></i>
                            <span>Rates</span>
                            <span class="nav-count"><?php echo $counts['rates']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.taxes.view')): ?>
                        <a href="#" class="nav-item" data-tab="taxes">
                        <i class="fas fa-percentage"></i>
                            <span>Taxes & Rules</span>
                            <span class="nav-count"><?php echo $counts['taxes']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.currencies.view')): ?>
                        <a href="#" class="nav-item" data-tab="currencies">
                        <i class="fas fa-coins"></i>
                            <span>Currency</span>
                            <span class="nav-count"><?php echo $counts['currencies']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.payment_types.view')): ?>
                        <a href="#" class="nav-item" data-tab="payment-types">
                        <i class="fas fa-credit-card"></i>
                            <span>Payment Types</span>
                            <span class="nav-count"><?php echo $counts['payment_types']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.discount_types.view')): ?>
                        <a href="#" class="nav-item" data-tab="discount-types">
                        <i class="fas fa-tag"></i>
                            <span>Discount Types</span>
                            <span class="nav-count"><?php echo $counts['discount_types']; ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
                <?php endif; ?>
                
                <!-- Geographic & Business Setup -->
                <?php 
                $hasGeographicAccess = 
                    $rbacHelper->hasPermission($userRole, 'setup.industries.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.countries.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.regions.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.cities.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.client_types.view');

                if ($hasGeographicAccess): 
                ?>
                <div class="nav-group">
                    <button class="nav-group-header" onclick="toggleNavGroup(this)">
                        <i class="fas fa-chevron-right"></i>
                        <span>Geographic & Business Setup</span>
                    </button>
                    <div class="nav-group-items" style="display: none;">
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.industries.view')): ?>
                        <a href="#" class="nav-item" data-tab="industries">
                            <i class="fas fa-industry"></i>
                            <span>Industries</span>
                            <span class="nav-count"><?php echo $counts['industries']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.countries.view')): ?>
                        <a href="#" class="nav-item" data-tab="countries">
                        <i class="fas fa-globe"></i>
                            <span>Countries</span>
                            <span class="nav-count"><?php echo $counts['countries']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.regions.view')): ?>
                        <a href="#" class="nav-item" data-tab="regions">
                        <i class="fas fa-map"></i>
                            <span>Regions</span>
                            <span class="nav-count"><?php echo $counts['regions']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.cities.view')): ?>
                        <a href="#" class="nav-item" data-tab="cities">
                        <i class="fas fa-city"></i>
                            <span>Cities</span>
                            <span class="nav-count"><?php echo $counts['cities']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.client_types.view')): ?>
                        <a href="#" class="nav-item" data-tab="client-types">
                        <i class="fas fa-users"></i>
                            <span>Client Types</span>
                            <span class="nav-count"><?php echo $counts['client_types']; ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
                <?php endif; ?>
                
                <!-- User & Commission Setup -->
                <?php 
                $hasUserAccess = 
                    $rbacHelper->hasPermission($userRole, 'setup.users.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.roles.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.company_setup.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.commission_types.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.credit_types.view');

                if ($hasUserAccess): 
                ?>
                <div class="nav-group">
                    <button class="nav-group-header" onclick="toggleNavGroup(this)">
                        <i class="fas fa-chevron-right"></i>
                        <span>User & Commission Setup</span>
                    </button>
                    <div class="nav-group-items" style="display: none;">
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.users.view')): ?>
                        <a href="#" class="nav-item" data-tab="users">
                            <i class="fas fa-user"></i>
                            <span>Users</span>
                            <span class="nav-count"><?php echo $counts['users']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.roles.view')): ?>
                        <a href="#" class="nav-item" data-tab="roles">
                        <i class="fas fa-user-shield"></i>
                            <span>Roles</span>
                            <span class="nav-count"><?php echo $counts['roles']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.company_setup.view')): ?>
                        <a href="#" class="nav-item" data-tab="company-setup">
                        <i class="fas fa-building"></i>
                            <span>Company Setup</span>
                            <span class="nav-count"><?php echo $counts['company_setup']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.commission_types.view')): ?>
                        <a href="#" class="nav-item" data-tab="commission-types">
                        <i class="fas fa-hand-holding-usd"></i>
                            <span>Commission Types</span>
                            <span class="nav-count"><?php echo $counts['commission_types']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.credit_types.view')): ?>
                        <a href="#" class="nav-item" data-tab="credit-types">
                        <i class="fas fa-wallet"></i>
                            <span>Credit Types</span>
                            <span class="nav-count"><?php echo $counts['credit_types']; ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
                <?php endif; ?>
                
                <!-- Advanced & Tools -->
                <?php 
                $hasAdvancedAccess = 
                    $rbacHelper->hasPermission($userRole, 'setup.system_settings.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.email_templates.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.report_templates.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.sms_integration.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.storage_integration.view') ||
                    $rbacHelper->hasPermission($userRole, 'setup.bulk_upload.view');

                if ($hasAdvancedAccess): 
                ?>
                <div class="nav-group">
                    <button class="nav-group-header" onclick="toggleNavGroup(this)">
                        <i class="fas fa-chevron-right"></i>
                        <span>Advanced & Tools</span>
                    </button>
                    <div class="nav-group-items" style="display: none;">
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.system_settings.view')): ?>
                        <a href="#" class="nav-item" data-tab="system-settings">
                            <i class="fas fa-cog"></i>
                            <span>System Settings</span>
                            <span class="nav-count"><?php echo $counts['system_settings']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.email_templates.view')): ?>
                        <a href="#" class="nav-item" data-tab="email-templates">
                        <i class="fas fa-envelope"></i>
                            <span>Email Templates</span>
                            <span class="nav-count"><?php echo $counts['email_templates']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.report_templates.view')): ?>
                        <a href="#" class="nav-item" data-tab="report-templates">
                        <i class="fas fa-file-alt"></i>
                            <span>Report Templates</span>
                            <span class="nav-count"><?php echo $counts['report_templates']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.sms_integration.view')): ?>
                        <a href="#" class="nav-item" data-tab="sms-integration">
                            <i class="fas fa-sms"></i>
                            <span>SMS Integration</span>
                            <span class="nav-count"><?php echo $counts['sms_integration']; ?></span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.storage_integration.view')): ?>
                        <a href="#" class="nav-item" data-tab="storage-integration">
                            <i class="fas fa-cloud"></i>
                            <span>Storage Integration</span>
                            <span class="nav-count"><?php echo $counts['storage_integration']; ?></span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($rbacHelper->hasPermission($userRole, 'setup.bulk_upload.view')): ?>
                        <a href="#" class="nav-item" data-tab="bulk-upload">
                        <i class="fas fa-upload"></i>
                            <span>Bulk Upload</span>
                            <span class="nav-count"><?php echo $counts['bulk_upload']; ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
                <?php endif; ?>
            </nav>
        </aside>
        
        <!-- Main Content Area -->
        <main class="setup-content">

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

        <!-- Empty Tab Content Placeholders for All 33 Categories -->
        
        <!-- Ad Categories Tab -->
        <div class="tab-content" id="ad-categories-tab">
            <div class="tab-header">
                <h3>Ad Categories Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('ad-categories')">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Ad Categories management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Ad Sizes Tab -->
        <div class="tab-content" id="ad-sizes-tab">
            <div class="tab-header">
                <h3>Ad Sizes Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('ad-sizes')">
                        <i class="fas fa-plus"></i> Add Size
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Ad Sizes management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Page Positions Tab -->
        <div class="tab-content" id="page-positions-tab">
            <div class="tab-header">
                <h3>Page Positions Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('page-positions')">
                        <i class="fas fa-plus"></i> Add Position
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Page Positions management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Color Types Tab -->
        <div class="tab-content" id="color-types-tab">
            <div class="tab-header">
                <h3>Color Types Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('color-types')">
                        <i class="fas fa-plus"></i> Add Color Type
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Color Types management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Rates Tab -->
        <div class="tab-content" id="rates-tab">
            <div class="tab-toolbar-wrapper">
                <!-- Row 1: Actions -->
                <div class="tab-toolbar tab-toolbar-actions">
                <div class="tab-actions">
                        <select id="template-format" class="filter-status template-format-select">
                            <option value="csv">CSV Format</option>
                            <option value="excel">Excel Format</option>
                        </select>
                    <button class="btn-secondary" onclick="downloadRatesTemplate()">
                        <i class="fas fa-download"></i> Download Template
                    </button>
                        <button class="btn-secondary" onclick="openBulkUploadModal()">
                        <i class="fas fa-upload"></i> Bulk Upload
                    </button>
                    <button class="btn-secondary" onclick="exportRates()">
                        <i class="fas fa-file-export"></i> Export
                    </button>
                    <button class="btn-primary" onclick="openAddRateModal()">
                        <i class="fas fa-plus"></i> Add Rate
                    </button>
                    </div>
                </div>
                
                <!-- Row 2: Filters -->
                <div class="tab-toolbar tab-toolbar-filters">
                    <div class="tab-filters">
                        <select class="filter-status" id="rates-status" onchange="setupManager.loadRates()">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <select class="filter-status" id="rates-publication" onchange="setupManager.loadRates()">
                            <option value="">All Publications</option>
                            <!-- Will be populated by JavaScript -->
                        </select>
                        <select class="filter-status" id="rates-category" onchange="setupManager.loadRates()">
                            <option value="">All Categories</option>
                            <!-- Will be populated by JavaScript -->
                        </select>
                        <select class="filter-status" id="rates-size" onchange="setupManager.loadRates()">
                            <option value="">All Sizes</option>
                            <!-- Will be populated by JavaScript -->
                        </select>
                        <select class="filter-status" id="rates-position" onchange="setupManager.loadRates()">
                            <option value="">All Positions</option>
                            <!-- Will be populated by JavaScript -->
                        </select>
                        <select class="filter-status" id="rates-color" onchange="setupManager.loadRates()">
                            <option value="">All Colors</option>
                            <!-- Will be populated by JavaScript -->
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all-rates" onchange="toggleSelectAllRates()"></th>
                            <th>Publication</th>
                            <th>Category</th>
                            <th>Size</th>
                            <th>Position</th>
                            <th>Color</th>
                            <th>Base Rate</th>
                            <th>Currency</th>
                            <th>Effective Date</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="rates-table-body">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <div class="pagination-container">
                <div class="pagination-info">
                    <span id="rates-pagination-info">Loading...</span>
                </div>
                <div class="pagination-controls">
                    <button class="btn-pagination" id="rates-prev-btn" disabled>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <span class="pagination-numbers" id="rates-pagination-numbers">
                        <!-- Will be populated by JavaScript -->
                    </span>
                    <button class="btn-pagination" id="rates-next-btn" disabled>
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Taxes Tab -->
        <div class="tab-content" id="taxes-tab">
            <div class="tab-header">
                <h3>Taxes & Rules Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('taxes')">
                        <i class="fas fa-plus"></i> Add Tax Rule
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Taxes & Rules management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Currencies Tab -->
        <div class="tab-content" id="currencies-tab">
            <div class="tab-header">
                <h3>Currency Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('currencies')">
                        <i class="fas fa-plus"></i> Add Currency
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Currency management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Payment Types Tab -->
        <div class="tab-content" id="payment-types-tab">
            <div class="tab-header">
                <h3>Payment Types Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('payment-types')">
                        <i class="fas fa-plus"></i> Add Payment Type
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Payment Types management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Discount Types Tab -->
        <div class="tab-content" id="discount-types-tab">
            <div class="tab-header">
                <h3>Discount Types Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('discount-types')">
                        <i class="fas fa-plus"></i> Add Discount Type
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Discount Types management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Industries Tab -->
        <div class="tab-content" id="industries-tab">
            <div class="tab-header">
                <h3>Industries Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('industries')">
                        <i class="fas fa-plus"></i> Add Industry
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Industries management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Countries Tab -->
        <div class="tab-content" id="countries-tab">
            <div class="tab-header">
                <h3>Countries Management</h3>
                <div class="tab-actions">
                    <button class="btn-success" onclick="importFromAPI('countries')">
                        <i class="fas fa-download"></i> Import from API
                    </button>
                    <button class="btn-primary" onclick="openAddModal('countries')">
                        <i class="fas fa-plus"></i> Add Country
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Countries management will be implemented in Phase 2 with API integration.</p>
            </div>
        </div>
        
        <!-- Regions Tab -->
        <div class="tab-content" id="regions-tab">
            <div class="tab-header">
                <h3>Regions Management</h3>
                <div class="tab-actions">
                    <button class="btn-success" onclick="importFromAPI('regions')">
                        <i class="fas fa-download"></i> Import from API
                    </button>
                    <button class="btn-primary" onclick="openAddModal('regions')">
                        <i class="fas fa-plus"></i> Add Region
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Regions management will be implemented in Phase 2 with API integration.</p>
            </div>
        </div>
        
        <!-- Cities Tab -->
        <div class="tab-content" id="cities-tab">
            <div class="tab-header">
                <h3>Cities Management</h3>
                <div class="tab-actions">
                    <button class="btn-success" onclick="importFromAPI('cities')">
                        <i class="fas fa-download"></i> Import from API
                    </button>
                    <button class="btn-primary" onclick="openAddModal('cities')">
                        <i class="fas fa-plus"></i> Add City
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Cities management will be implemented in Phase 2 with API integration.</p>
            </div>
        </div>
        
        <!-- Client Types Tab -->
        <div class="tab-content" id="client-types-tab">
            <div class="tab-header">
                <h3>Client Types Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('client-types')">
                        <i class="fas fa-plus"></i> Add Client Type
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Client Types management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Users Tab -->
        <div class="tab-content" id="users-tab">
            <div class="tab-header">
                <h3>Users Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('users')">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Users management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Roles Tab -->
        <div class="tab-content" id="roles-tab">
            <div class="tab-header">
                <h3>Roles Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('roles')">
                        <i class="fas fa-plus"></i> Add Role
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Roles management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Company Setup Tab -->
        <div class="tab-content" id="company-setup-tab">
            <div class="tab-header">
                <h3>Company Setup</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('company-setup')">
                        <i class="fas fa-plus"></i> Add Setting
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Company Setup will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Commission Types Tab -->
        <div class="tab-content" id="commission-types-tab">
            <div class="tab-header">
                <h3>Commission Types Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('commission-types')">
                        <i class="fas fa-plus"></i> Add Commission Type
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Commission Types management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Credit Types Tab -->
        <div class="tab-content" id="credit-types-tab">
            <div class="tab-header">
                <h3>Credit Types Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('credit-types')">
                        <i class="fas fa-plus"></i> Add Credit Type
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Credit Types management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- System Settings Tab -->
        <div class="tab-content" id="system-settings-tab">
            <div class="tab-header">
                <h3>System Settings</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('system-settings')">
                        <i class="fas fa-plus"></i> Add Setting
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>System Settings will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Email Templates Tab -->
        <div class="tab-content" id="email-templates-tab">
            <div class="tab-header">
                <h3>Email Templates Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('email-templates')">
                        <i class="fas fa-plus"></i> Add Template
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Email Templates management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- Report Templates Tab -->
        <div class="tab-content" id="report-templates-tab">
            <div class="tab-header">
                <h3>Report Templates Management</h3>
                <div class="tab-actions">
                    <button class="btn-primary" onclick="openAddModal('report-templates')">
                        <i class="fas fa-plus"></i> Add Template
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <p>Report Templates management will be implemented in Phase 2.</p>
            </div>
        </div>
        
        <!-- SMS Integration Tab -->
        <div class="tab-content" id="sms-integration-tab">
            <div class="tab-header">
                <h3>SMS Integration Management</h3>
                <div class="tab-actions">
                    <button class="btn-success" onclick="testSmsConnection()">
                        <i class="fas fa-test-tube"></i> Test Connection
                    </button>
                    <button class="btn-primary" onclick="openSmsConfigModal()">
                        <i class="fas fa-cog"></i> Configure SMS
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <div class="integration-status">
                    <h4>SMS Provider Status</h4>
                    <div class="status-indicator">
                        <span class="status-badge active">Connected</span>
                        <span class="status-text">SMS service is operational</span>
                    </div>
                    <div class="integration-details">
                        <p><strong>Provider:</strong> Bulk SMS Service</p>
                        <p><strong>API Status:</strong> Active</p>
                        <p><strong>Last Test:</strong> 2024-01-08 14:30</p>
                        <p><strong>Credits Remaining:</strong> 1,250 SMS</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Storage Integration Tab -->
        <div class="tab-content" id="storage-integration-tab">
            <div class="tab-header">
                <h3>Storage Integration Management</h3>
                <div class="tab-actions">
                    <button class="btn-success" onclick="testStorageConnection()">
                        <i class="fas fa-test-tube"></i> Test Connection
                    </button>
                    <button class="btn-primary" onclick="openStorageConfigModal()">
                        <i class="fas fa-cog"></i> Configure Storage
                    </button>
                </div>
            </div>
            <div class="placeholder-content">
                <div class="integration-status">
                    <h4>Google Drive Integration Status</h4>
                    <div class="status-indicator">
                        <span class="status-badge active">Connected</span>
                        <span class="status-text">Storage service is operational</span>
                    </div>
                    <div class="integration-details">
                        <p><strong>Provider:</strong> Google Drive API</p>
                        <p><strong>API Status:</strong> Active</p>
                        <p><strong>Last Sync:</strong> 2024-01-08 14:25</p>
                        <p><strong>Storage Used:</strong> 2.3 GB / 15 GB</p>
                        <p><strong>Files Synced:</strong> 156 documents</p>
                    </div>
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
                            <option value="ad_categories">Ad Categories</option>
                            <option value="ad_sizes">Ad Sizes</option>
                            <option value="page_positions">Page Positions</option>
                            <option value="color_types">Color Types</option>
                            <option value="rates">Rates (Complex)</option>
                            <option value="taxes">Taxes</option>
                            <option value="currencies">Currencies</option>
                            <option value="payment_types">Payment Types</option>
                            <option value="discount_types">Discount Types</option>
                            <option value="industries">Industries</option>
                            <option value="countries">Countries</option>
                            <option value="regions">Regions</option>
                            <option value="cities">Cities</option>
                            <option value="client_types">Client Types</option>
                            <option value="users">Users</option>
                            <option value="roles">Roles</option>
                            <option value="company_setup">Company Setup</option>
                            <option value="commission_types">Commission Types</option>
                            <option value="credit_types">Credit Types</option>
                            <option value="system_settings">System Settings</option>
                            <option value="email_templates">Email Templates</option>
                            <option value="report_templates">Report Templates</option>
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
                            <input type="file" id="upload-file" accept=".xlsx,.xls,.csv" class="hidden-file-input">
                        </div>
                        <button class="btn-primary" onclick="uploadFile()" id="upload-btn" disabled>
                            <i class="fas fa-upload"></i> Upload & Validate
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pending Sessions Section -->
        <div id="pending-sessions-section" style="display:none; margin-bottom: 20px;">
            <div class="tab-header">
                <h4>Your Pending Uploads</h4>
                <button class="btn-secondary" onclick="refreshPendingSessions()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
            <div id="pending-sessions-list" class="sessions-grid">
                <!-- Populated by JavaScript -->
            </div>
        </div>
        
        <!-- Staging Area for Rates -->
        <div id="staging-area" style="display:none;">
            <div class="staging-header">
                <h4>Review Uploaded Rates</h4>
                <div class="staging-stats">
                    <span class="stat-ok">OK: <span id="ok-count">0</span></span>
                    <span class="stat-duplicate">Duplicates: <span id="dup-count">0</span></span>
                    <span class="stat-error">Errors: <span id="error-count">0</span></span>
                </div>
            </div>
            <div class="staging-actions">
                <button onclick="selectOkRates()" class="btn-success">Select All OK</button>
                <button onclick="deselectDuplicateRates()" class="btn-warning">Deselect Duplicates</button>
                <button onclick="processStagingRates()" class="btn-primary">Process Selected</button>
                <button onclick="cancelStaging()" class="btn-secondary">Cancel</button>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all-staging" onchange="toggleSelectAllStaging()"></th>
                        <th>Status</th>
                        <th>Publication</th>
                        <th>Category</th>
                        <th>Size</th>
                        <th>Position</th>
                        <th>Color</th>
                        <th>Rate</th>
                        <th>Effective From</th>
                        <th>Effective To</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody id="staging-table-body">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Rate Modal -->
<div class="modal" id="rate-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="rate-modal-title">Add New Rate</h3>
            <button class="modal-close" onclick="closeRateModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="rate-form">
                <input type="hidden" id="rate-id" name="id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Publication Code <span>*</span></label>
                        <input type="text" id="rate-publication-code" name="publication_code" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Publication Name <span>*</span></label>
                        <input type="text" id="rate-publication-name" name="publication_name" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Ad Category <span>*</span></label>
                        <input type="text" id="rate-ad-category" name="ad_category" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Ad Size <span>*</span></label>
                        <input type="text" id="rate-ad-size" name="ad_size" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Page Position <span>*</span></label>
                        <input type="text" id="rate-page-position" name="page_position" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Color Type <span>*</span></label>
                        <input type="text" id="rate-color-type" name="color_type" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Base Rate <span>*</span></label>
                        <input type="number" id="rate-base-rate" name="base_rate" class="form-input" step="0.01" min="0" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Effective From <span>*</span></label>
                        <input type="date" id="rate-effective-from" name="effective_from" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Effective To <span>*</span></label>
                        <input type="date" id="rate-effective-to" name="effective_to" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Status <span>*</span></label>
                        <select id="rate-status" name="status" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea id="rate-notes" name="notes" class="form-textarea" rows="3"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeRateModal()">Cancel</button>
            <button class="btn-primary" onclick="saveRate()">
                <i class="fas fa-save"></i> Save Rate
            </button>
        </div>
    </div>
</div>

<!-- Bulk Upload Modal -->
<div class="modal" id="bulk-upload-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Bulk Upload Rates</h3>
            <button class="modal-close" onclick="closeBulkUploadModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="upload-instructions">
                <h4>Upload Instructions:</h4>
                <ol>
                    <li>Download the template first to ensure proper formatting</li>
                    <li>Fill in your rate data following the template structure</li>
                    <li>Upload the completed file for validation and processing</li>
                    <li>Review the results before confirming the import</li>
                </ol>
            </div>
            
            <div class="file-upload-area" id="rates-file-upload-area">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Drag and drop your rates file here or click to browse</p>
                <p class="file-types">Supported formats: .xlsx, .xls, .csv</p>
                <input type="file" id="rates-upload-file" accept=".xlsx,.xls,.csv" class="hidden-file-input">
                <div id="selected-file-name" class="file-name-display"></div>
            </div>
            
            <div class="upload-results" id="upload-results" style="display: none;">
                <h4>Upload Results:</h4>
                <div id="upload-summary"></div>
                <div id="upload-errors"></div>
                <div id="upload-warnings"></div>
                <div id="upload-dependencies"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeBulkUploadModal()">Cancel</button>
            <button class="btn-primary" onclick="downloadRatesTemplate()">
                <i class="fas fa-download"></i> Download Template
            </button>
            <button class="btn-success" onclick="uploadRatesFile()" id="rates-upload-btn" disabled>
                <i class="fas fa-upload"></i> Upload & Process
            </button>
        </div>
    </div>
</div>

        </main>
</div>

<!-- Alert Container -->
<div id="alert-container"></div>

<script>
// Pass PHP data to JavaScript
window.currentUser = <?php echo json_encode($currentUser); ?>;
window.baseUrl = '<?php echo BASE_URL; ?>';
window.setupCounts = <?php echo json_encode($counts); ?>;
window.userRole = '<?php echo $userRole; ?>';
</script>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/components/header.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/pages/setup.css">
<script src="<?php echo BASE_URL; ?>/public/js/pages/setup.js"></script>

<?php include __DIR__ . '/../views/footer.php'; ?>
