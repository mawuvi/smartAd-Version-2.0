<?php
require_once __DIR__ . '/../../bootstrap.php';

// Check authentication and permissions
$authGuard = new AuthGuard();
$authGuard->requirePermission('bookings.create');

// Get current user
$currentUser = $authGuard->getCurrentUser();

// Include header
include __DIR__ . '/../views/header.php';
?>

    <div class="main-container">
        <!-- Client Search Section -->
        <div class="card">
            <div class="card-header">
                <h2>Select Client</h2>
            </div>
            <div class="card-body">
                <div class="client-search-section">
                    <div class="form-group">
                        <label for="clientSearch">Search Client</label>
                        <div class="search-input-group">
                            <input type="text" id="clientSearch" class="form-control" placeholder="Type client name or company...">
                            <button type="button" class="btn btn-primary" id="searchClientBtn">Search</button>
                        </div>
                    </div>
                    
                    <div id="clientSearchResults" class="search-results hidden">
                        <!-- Search results will be populated here -->
                    </div>
                    
                    <div id="clientSearchMessage" class="message hidden">
                        <!-- Messages will be shown here -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Selected Client Info -->
        <div class="card hidden" id="selectedClientCard">
            <div class="card-header">
                <h3>Selected Client</h3>
                <button type="button" class="btn btn-secondary btn-sm" id="changeClientBtn">Change Client</button>
            </div>
            <div class="card-body">
                <div id="selectedClientInfo">
                    <!-- Client info will be populated here -->
                </div>
                
                <div class="client-actions">
                    <button type="button" class="btn btn-secondary" id="viewDraftsBtn">
                        <span class="btn-icon">📋</span>
                        View Drafts
                        <span class="badge" id="draftCountBadge">0</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="card hidden" id="bookingFormCard">
            <div class="card-header">
                <h3>Create Booking</h3>
            </div>
            <div class="card-body">
                <form id="bookingForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="publication">Publication *</label>
                            <select id="publication" class="form-control" required>
                                <option value="">Select Publication</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="colorType">Color Type *</label>
                            <select id="colorType" class="form-control" required>
                                <option value="">Select Color Type</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="adCategory">Ad Category *</label>
                            <select id="adCategory" class="form-control" required>
                                <option value="">Select Category</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="adSize">Ad Size *</label>
                            <select id="adSize" class="form-control" required>
                                <option value="">Select Size</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="pagePosition">Page Position *</label>
                            <select id="pagePosition" class="form-control" required>
                                <option value="">Select Position</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="insertions">Insertions *</label>
                            <input type="number" id="insertions" class="form-control" value="1" min="1" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="publicationDate">Publication Date *</label>
                            <input type="date" id="publicationDate" class="form-control" required>
                        </div>
                    </div>

                    <!-- Rate Calculation Section -->
                    <div class="rate-calculation-section">
                        <div class="section-header">
                            <h4>Rate Calculation</h4>
                            <button type="button" class="btn btn-primary" id="recalculateBtn">
                                Calculate Rate
                            </button>
                        </div>
                        
                        <div class="rate-results">
                            <div class="rate-item">
                                <span class="rate-label">Base Rate:</span>
                                <span class="rate-value" id="baseRate">GHS 0.00</span>
                            </div>
                            
                            <div class="rate-item">
                                <span class="rate-label">Subtotal:</span>
                                <span class="rate-value" id="subtotal">GHS 0.00</span>
                            </div>
                            
                            <div class="tax-breakdown">
                                <h5>Tax Breakdown</h5>
                                <div id="taxList">
                                    <!-- Tax items will be populated here -->
                                </div>
                                <div class="rate-item">
                                    <span class="rate-label">Total Tax:</span>
                                    <span class="rate-value" id="totalTax">GHS 0.00</span>
                                </div>
                            </div>
                            
                            <div class="rate-item total">
                                <span class="rate-label">Total Amount:</span>
                                <span class="rate-value" id="total">GHS 0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Discount Section -->
                    <div class="discount-section">
                        <div class="form-group">
                            <label for="discountSelect">
                                Apply Discount (Optional)
                                <span class="info-icon" title="Select from approved discounts for this client">ℹ️</span>
                            </label>
                            <select id="discountSelect" class="form-control">
                                <option value="">No discount</option>
                                <!-- Will be populated from API based on client -->
                            </select>
                            <small class="form-hint">Only approved discounts are available for selection.</small>
                        </div>
                        
                        <div class="discount-info hidden" id="discountInfo">
                            <div class="discount-details">
                                <span class="discount-label">Discount:</span>
                                <span class="discount-value" id="discountValue">GHS 0.00</span>
                            </div>
                            <div class="discount-reason">
                                <span class="reason-label">Reason:</span>
                                <span class="reason-value" id="discountReasonDisplay">-</span>
                            </div>
                        </div>
                        
                        <div class="no-discounts-message hidden" id="noDiscountsMessage">
                            <span class="info-icon">ℹ️</span>
                            <span>No approved discounts available for this client.</span>
                        </div>
                    </div>

                    <!-- Document Upload -->
                    <div class="form-group">
                        <label>Upload Documents (Artwork, Briefs, etc.)</label>
                        <div id="documentUploadZone"></div>
                        <small class="form-hint">Accepted: Images (JPG, PNG, GIF), PDF, Word documents - Max 10MB per file, up to 5 files</small>
                    </div>

                    <!-- Special Instructions -->
                    <div class="form-group">
                        <label for="specialInstructions">Special Instructions</label>
                        <textarea id="specialInstructions" class="form-control" rows="3" placeholder="Any special instructions for this booking..."></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn">Save Draft</button>
                        <button type="button" class="btn btn-primary" id="createBookingBtn">Create Booking</button>
                        <button type="button" class="btn btn-outline" id="clearFormBtn">Clear Form</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div id="createClientModal" class="modal" style="display: none;">
        <!-- Client creation modal will be populated here -->
    </div>

<!-- Include page-specific CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/pages/booking.css">

<!-- Include required JavaScript -->
<script src="<?php echo BASE_URL; ?>/public/js/modal.js"></script>
<script src="<?php echo BASE_URL; ?>/public/js/modules/clientModule.js"></script>
<script src="<?php echo BASE_URL; ?>/public/js/modules/documentUploadModule.js"></script>
<script src="<?php echo BASE_URL; ?>/public/js/modules/rateCalculatorModule.js"></script>
<script src="<?php echo BASE_URL; ?>/public/js/pages/booking.js"></script>

<script>
window.baseUrl = '<?php echo BASE_URL; ?>';
window.currentUser = <?php echo json_encode($currentUser); ?>;
</script>

<?php include __DIR__ . '/../views/footer.php'; ?>