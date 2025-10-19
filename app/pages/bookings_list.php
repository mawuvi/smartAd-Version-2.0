<?php
require_once __DIR__ . '/../../bootstrap.php';

// Check authentication and permissions
$authGuard = new AuthGuard();
$authGuard->requirePermission('bookings.view');

// Get current user
$currentUser = $authGuard->getCurrentUser();

// Include header
include __DIR__ . '/../views/header.php';
?>

    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1>Bookings Management</h1>
                <p>View and manage all bookings</p>
            </div>
            <div class="page-actions">
                <a href="<?php echo BASE_URL; ?>/app/pages/booking.php" class="btn btn-primary">
                    <span class="btn-icon">➕</span>
                    New Booking
                </a>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card">
            <div class="card-header">
                <h3>Filters</h3>
            </div>
            <div class="card-body">
                <div class="filters-grid">
                    <div class="form-group">
                        <label for="clientFilter">Client</label>
                        <select id="clientFilter" class="form-control">
                            <option value="">All Clients</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="statusFilter">Status</label>
                        <select id="statusFilter" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="publicationFilter">Publication</label>
                        <select id="publicationFilter" class="form-control">
                            <option value="">All Publications</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="dateFromFilter">Date From</label>
                        <input type="date" id="dateFromFilter" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="dateToFilter">Date To</label>
                        <input type="date" id="dateToFilter" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="filter-actions">
                            <button type="button" class="btn btn-primary" id="applyFiltersBtn">Apply Filters</button>
                            <button type="button" class="btn btn-secondary" id="clearFiltersBtn">Clear</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings List -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-content">
                    <h3>Bookings</h3>
                    <div class="bookings-summary">
                        <span id="bookingsCount">0 bookings found</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="bookingsList" class="bookings-list">
                    <!-- Bookings will be loaded here -->
                </div>
                
                <div id="loadingIndicator" class="loading-indicator hidden">
                    <div class="spinner"></div>
                    <span>Loading bookings...</span>
                </div>
                
                <div id="noBookingsMessage" class="no-results hidden">
                    <div class="no-results-icon">📋</div>
                    <h4>No bookings found</h4>
                    <p>Try adjusting your filters or create a new booking.</p>
                    <a href="<?php echo BASE_URL; ?>/app/pages/booking.php" class="btn btn-primary">Create New Booking</a>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-container hidden" id="paginationContainer">
            <div class="pagination-info">
                <span id="paginationInfo">Showing 1-10 of 0 bookings</span>
            </div>
            <div class="pagination-controls">
                <button type="button" class="btn btn-secondary" id="prevPageBtn" disabled>Previous</button>
                <span class="page-numbers" id="pageNumbers"></span>
                <button type="button" class="btn btn-secondary" id="nextPageBtn" disabled>Next</button>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div id="bookingDetailsModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Booking Details</h3>
                <button type="button" class="modal-close" id="closeModalBtn">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Booking details will be populated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeModalBtn2">Close</button>
                <button type="button" class="btn btn-primary" id="editBookingBtn">Edit Booking</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <button type="button" class="modal-close" id="closeDeleteModalBtn">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this booking?</p>
                <p class="warning-text">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDeleteBtn">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>

<!-- Include page-specific CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/pages/bookings_list.css">

<!-- Include required JavaScript -->
<script src="<?php echo BASE_URL; ?>/public/js/modal.js"></script>
<script src="<?php echo BASE_URL; ?>/public/js/pages/bookings_list.js"></script>

<script>
window.baseUrl = '<?php echo BASE_URL; ?>';
window.currentUser = <?php echo json_encode($currentUser); ?>;
</script>

<?php include __DIR__ . '/../views/footer.php'; ?>
