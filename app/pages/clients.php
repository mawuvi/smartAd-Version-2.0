<?php
/**
 * Client Management Page
 * Location: app/pages/clients.php
 * Standards Applied: Section 3 (Protected Pages), Section 4 (Header/Footer)
 */

// Include header
require_once __DIR__ . '/../views/header.php';

// Require client view permission
getAuthGuard()->requirePermission('clients.view');

// Get current user for data attributes
$currentUser = getAuthGuard()->getCurrentUser();

// Check if we need to show the new client modal
$showNewClientModal = isset($_GET['action']) && $_GET['action'] === 'new';
?>

<div class="page-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h1>Client Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/smartAd/pages/dashboard.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Clients</li>
            </ol>
        </nav>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-cards">
        <div class="card stat-card">
            <div class="card-body">
                <h5 class="card-title">Total Clients</h5>
                <p class="stat-value" id="totalClients">0</p>
            </div>
        </div>
        <div class="card stat-card">
            <div class="card-body">
                <h5 class="card-title">Active Clients</h5>
                <p class="stat-value" id="activeClients">0</p>
            </div>
        </div>
        <div class="card stat-card warning">
            <div class="card-body">
                <h5 class="card-title">Credit Warnings</h5>
                <p class="stat-value" id="creditWarnings">0</p>
            </div>
        </div>
        <div class="card stat-card success">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <p class="stat-value" id="totalRevenue">GHS 0.00</p>
            </div>
        </div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="search-filter-bar">
        <div class="search-section">
            <div class="search-input-group">
                <input type="text" id="clientSearch" class="form-control" placeholder="Search clients by name, contact person, or client number...">
                <button class="btn btn-primary" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        
        <div class="filter-section">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="statusFilter">Status</label>
                    <select id="statusFilter" class="form-control">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="typeFilter">Client Type</label>
                    <select id="typeFilter" class="form-control">
                        <option value="">All Types</option>
                        <option value="individual">Individual</option>
                        <option value="company">Company</option>
                        <option value="government">Government</option>
                        <option value="ngo">NGO</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="creditFilter">Credit Rating</label>
                    <select id="creditFilter" class="form-control">
                        <option value="">All Ratings</option>
                        <option value="excellent">Excellent</option>
                        <option value="good">Good</option>
                        <option value="fair">Fair</option>
                        <option value="poor">Poor</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="dateFromFilter">From Date</label>
                    <input type="date" id="dateFromFilter" class="form-control">
                </div>
                
                <div class="filter-group">
                    <label for="dateToFilter">To Date</label>
                    <input type="date" id="dateToFilter" class="form-control">
                </div>
                
                <div class="filter-actions">
                    <button class="btn btn-secondary" id="clearFiltersBtn">Clear Filters</button>
                    <button class="btn btn-primary" id="applyFiltersBtn">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <div class="action-left">
            <button class="btn btn-primary" id="createClientBtn">
                <i class="fas fa-plus"></i> Create New Client
            </button>
            <button class="btn btn-success" id="exportClientsBtn">
                <i class="fas fa-download"></i> Export to CSV
            </button>
        </div>
        
        <div class="action-right">
            <div class="pagination-info">
                <span id="paginationInfo">Showing 0 of 0 clients</span>
            </div>
            <div class="items-per-page">
                <label for="itemsPerPage">Items per page:</label>
                <select id="itemsPerPage" class="form-control">
                    <option value="10">10</option>
                    <option value="20" selected>20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Clients Table -->
    <div class="table-container">
        <div class="table-loading" id="tableLoading">
            <div class="spinner"></div>
            <p>Loading clients...</p>
        </div>
        
        <div class="table-responsive">
            <table class="table clients-table" id="clientsTable">
                <thead>
                    <tr>
                        <th class="sortable" data-sort="client_number">
                            Client Number <i class="fas fa-sort"></i>
                        </th>
                        <th class="sortable" data-sort="company_name">
                            Company Name <i class="fas fa-sort"></i>
                        </th>
                        <th class="sortable" data-sort="client_type">
                            Type <i class="fas fa-sort"></i>
                        </th>
                        <th>Contact Person</th>
                        <th>Contact Info</th>
                        <th class="sortable" data-sort="status">
                            Status <i class="fas fa-sort"></i>
                        </th>
                        <th>Credit Info</th>
                        <th class="sortable" data-sort="created_at">
                            Created <i class="fas fa-sort"></i>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="clientsTableBody">
                    <!-- Client rows will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
        
        <div class="no-data" id="noDataMessage" style="display: none;">
            <div class="no-data-content">
                <i class="fas fa-users"></i>
                <h3>No clients found</h3>
                <p>Try adjusting your search criteria or create a new client.</p>
                <button class="btn btn-primary" id="createClientFromEmpty">
                    <i class="fas fa-plus"></i> Create New Client
                </button>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination-container" id="paginationContainer">
        <nav aria-label="Client pagination">
            <ul class="pagination" id="pagination">
                <!-- Pagination buttons will be populated by JavaScript -->
            </ul>
        </nav>
    </div>
</div>

<!-- Loading Indicator -->
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="loading-content">
        <div class="spinner"></div>
        <p id="loadingText">Processing...</p>
    </div>
</div>

<!-- Include page-specific CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/pages/clients.css">

<!-- Include New Client Modal -->
<?php if ($showNewClientModal): ?>
    <?php include __DIR__ . '/../views/clients/new_client_modal.php'; ?>
<?php endif; ?>

<!-- Include required JavaScript -->
<script src="<?php echo BASE_URL; ?>/public/js/modal.js"></script>
<script src="<?php echo BASE_URL; ?>/public/js/pages/clients.js"></script>
<script src="<?php echo BASE_URL; ?>/public/js/modules/clientModule.js"></script>

<script>
// Set base URL and current user data for JavaScript
window.baseUrl = '<?php echo BASE_URL; ?>';
window.currentUser = <?php echo json_encode($currentUser); ?>;
window.userPermissions = <?php echo json_encode(getAuthGuard()->getUserPermissions()); ?>;
</script>

<?php if ($showNewClientModal): ?>
<script>
// Auto-show the new client modal when action=new
document.addEventListener('DOMContentLoaded', function() {
    // Trigger the modal after a short delay to ensure everything is loaded
    setTimeout(function() {
        const createBtn = document.getElementById('createClientBtn');
        if (createBtn) {
            createBtn.click();
        }
    }, 500);
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../views/footer.php'; ?>
