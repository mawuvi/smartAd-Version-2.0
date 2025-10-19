// /public/js/pages/bookings_list.js
// Bookings list management functionality

const BASE_URL = window.baseUrl;
let currentBookings = [];
let currentFilters = {};
let currentPage = 1;
let totalPages = 1;
let itemsPerPage = 10;

// DOM Elements
const clientFilter = document.getElementById('clientFilter');
const statusFilter = document.getElementById('statusFilter');
const publicationFilter = document.getElementById('publicationFilter');
const dateFromFilter = document.getElementById('dateFromFilter');
const dateToFilter = document.getElementById('dateToFilter');
const applyFiltersBtn = document.getElementById('applyFiltersBtn');
const clearFiltersBtn = document.getElementById('clearFiltersBtn');
const bookingsList = document.getElementById('bookingsList');
const loadingIndicator = document.getElementById('loadingIndicator');
const noBookingsMessage = document.getElementById('noBookingsMessage');
const bookingsCount = document.getElementById('bookingsCount');
const paginationContainer = document.getElementById('paginationContainer');
const paginationInfo = document.getElementById('paginationInfo');
const prevPageBtn = document.getElementById('prevPageBtn');
const nextPageBtn = document.getElementById('nextPageBtn');
const pageNumbers = document.getElementById('pageNumbers');

// Modal Elements
const bookingDetailsModal = document.getElementById('bookingDetailsModal');
const modalTitle = document.getElementById('modalTitle');
const modalBody = document.getElementById('modalBody');
const closeModalBtn = document.getElementById('closeModalBtn');
const closeModalBtn2 = document.getElementById('closeModalBtn2');
const editBookingBtn = document.getElementById('editBookingBtn');

// Delete Modal Elements
const deleteConfirmModal = document.getElementById('deleteConfirmModal');
const closeDeleteModalBtn = document.getElementById('closeDeleteModalBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

let selectedBookingForDelete = null;

document.addEventListener('DOMContentLoaded', function() {
    initializePage();
    bindEvents();
});

function initializePage() {
    try {
        loadPublications();
        loadClients();
        applyUrlFilters();
        loadBookings();
    } catch (error) {
        console.error('Failed to initialize page:', error);
        showError('Failed to initialize page');
    }
}

function bindEvents() {
    // Filter events
    applyFiltersBtn.addEventListener('click', applyFilters);
    clearFiltersBtn.addEventListener('click', clearFilters);
    
    // Pagination events
    prevPageBtn.addEventListener('click', () => changePage(currentPage - 1));
    nextPageBtn.addEventListener('click', () => changePage(currentPage + 1));
    
    // Modal events
    closeModalBtn.addEventListener('click', closeBookingModal);
    closeModalBtn2.addEventListener('click', closeBookingModal);
    editBookingBtn.addEventListener('click', editBooking);
    
    // Delete modal events
    closeDeleteModalBtn.addEventListener('click', closeDeleteModal);
    cancelDeleteBtn.addEventListener('click', closeDeleteModal);
    confirmDeleteBtn.addEventListener('click', confirmDelete);
    
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === bookingDetailsModal) {
            closeBookingModal();
        }
        if (event.target === deleteConfirmModal) {
            closeDeleteModal();
        }
    });
}

function applyUrlFilters() {
    const urlParams = new URLSearchParams(window.location.search);
    const clientId = urlParams.get('client_id');
    const status = urlParams.get('status');
    
    if (status && statusFilter) {
        statusFilter.value = status;
    }
    
    if (clientId) {
        currentFilters.client_id = clientId;
        // Set client filter when clients are loaded
        setTimeout(() => {
            if (clientFilter) {
                clientFilter.value = clientId;
            }
        }, 1000);
    }
    
    return !!(clientId || status);
}

async function loadPublications() {
    try {
        const response = await fetch(`${BASE_URL}/locations_api.php?action=get_publications`);
        const result = await response.json();
        
        if (result.success) {
            populateSelect(publicationFilter, result.data, 'id', 'name');
        }
    } catch (error) {
        console.error('Failed to load publications:', error);
    }
}

async function loadClients() {
    try {
        const response = await fetch(`${BASE_URL}/client_api.php?action=get_clients&limit=100`);
        const result = await response.json();
        
        if (result.success) {
            populateSelect(clientFilter, result.data, 'id', 'company_name');
        }
    } catch (error) {
        console.error('Failed to load clients:', error);
    }
}

function populateSelect(selectElement, data, valueField, textField) {
    // Clear existing options except the first one
    const firstOption = selectElement.options[0];
    selectElement.innerHTML = '';
    selectElement.appendChild(firstOption);
    
    data.forEach(item => {
        const option = document.createElement('option');
        option.value = item[valueField];
        option.textContent = item[textField];
        selectElement.appendChild(option);
    });
}

function applyFilters() {
    currentFilters = {
        client_id: clientFilter.value || null,
        status: statusFilter.value || null,
        publication_id: publicationFilter.value || null,
        date_from: dateFromFilter.value || null,
        date_to: dateToFilter.value || null
    };
    
    currentPage = 1;
    loadBookings();
}

function clearFilters() {
    clientFilter.value = '';
    statusFilter.value = '';
    publicationFilter.value = '';
    dateFromFilter.value = '';
    dateToFilter.value = '';
    
    currentFilters = {};
    currentPage = 1;
    loadBookings();
}

async function loadBookings() {
    showLoading();
    
    try {
        const params = new URLSearchParams({
            ...currentFilters,
            limit: itemsPerPage,
            offset: (currentPage - 1) * itemsPerPage
        });
        
        const response = await fetch(`${BASE_URL}/booking_api.php?action=get_bookings&${params}`);
        const result = await response.json();
        
        if (result.success) {
            currentBookings = result.data;
            displayBookings(currentBookings);
            updateBookingsCount(currentBookings.length);
            updatePagination();
        } else {
            throw new Error(result.message || 'Failed to load bookings');
        }
    } catch (error) {
        console.error('Failed to load bookings:', error);
        showError('Failed to load bookings: ' + error.message);
        displayBookings([]);
    } finally {
        hideLoading();
    }
}

function displayBookings(bookings) {
    if (!bookings || bookings.length === 0) {
        bookingsList.innerHTML = '';
        noBookingsMessage.classList.remove('hidden');
        paginationContainer.classList.add('hidden');
        return;
    }
    
    noBookingsMessage.classList.add('hidden');
    
    const bookingsHtml = bookings.map(booking => `
        <div class="booking-item" data-booking-id="${booking.id}">
            <div class="booking-header">
                <div class="booking-info">
                    <h4 class="booking-number">${booking.booking_number}</h4>
                    <span class="booking-status status-${booking.status}">${booking.status}</span>
                </div>
                <div class="booking-actions">
                    <button type="button" class="btn btn-sm btn-secondary view-booking-btn" data-booking-id="${booking.id}">
                        View
                    </button>
                    <button type="button" class="btn btn-sm btn-primary edit-booking-btn" data-booking-id="${booking.id}">
                        Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-danger delete-booking-btn" data-booking-id="${booking.id}">
                        Delete
                    </button>
                </div>
            </div>
            
            <div class="booking-details">
                <div class="detail-row">
                    <span class="detail-label">Client:</span>
                    <span class="detail-value">${booking.client_name || 'N/A'}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Publication:</span>
                    <span class="detail-value">${booking.publication_name || 'N/A'}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">${formatDate(booking.publication_date)}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount:</span>
                    <span class="detail-value">${formatCurrency(booking.total_amount || 0)}</span>
                </div>
            </div>
            
            <div class="booking-meta">
                <div class="meta-item">
                    <span class="meta-label">Created:</span>
                    <span class="meta-value">${formatDateTime(booking.created_at)}</span>
                </div>
                ${booking.notes ? `
                <div class="meta-item">
                    <span class="meta-label">Notes:</span>
                    <span class="meta-value">${booking.notes}</span>
                </div>
                ` : ''}
            </div>
        </div>
    `).join('');
    
    bookingsList.innerHTML = bookingsHtml;
    
    // Bind event listeners
    bindBookingEvents();
}

function bindBookingEvents() {
    // View booking buttons
    document.querySelectorAll('.view-booking-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const bookingId = parseInt(this.dataset.bookingId);
            viewBooking(bookingId);
        });
    });
    
    // Edit booking buttons
    document.querySelectorAll('.edit-booking-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const bookingId = parseInt(this.dataset.bookingId);
            editBooking(bookingId);
        });
    });
    
    // Delete booking buttons
    document.querySelectorAll('.delete-booking-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const bookingId = parseInt(this.dataset.bookingId);
            showDeleteConfirmation(bookingId);
        });
    });
}

async function viewBooking(bookingId) {
    try {
        const response = await fetch(`${BASE_URL}/booking_api.php?action=get_booking&id=${bookingId}`);
        const result = await response.json();
        
        if (result.success) {
            showBookingModal(result.data, 'view');
        } else {
            throw new Error(result.message || 'Failed to load booking');
        }
    } catch (error) {
        console.error('Failed to view booking:', error);
        showError('Failed to load booking: ' + error.message);
    }
}

function editBooking(bookingId) {
    if (bookingId) {
        // Navigate to booking page with edit mode
        window.location.href = `${BASE_URL}/app/pages/booking.php?edit=${bookingId}`;
    } else {
        // Edit current booking in modal
        const bookingId = editBookingBtn.dataset.bookingId;
        if (bookingId) {
            window.location.href = `${BASE_URL}/app/pages/booking.php?edit=${bookingId}`;
        }
    }
}

function showBookingModal(booking, mode = 'view') {
    modalTitle.textContent = `Booking ${booking.booking_number}`;
    editBookingBtn.dataset.bookingId = booking.id;
    
    const modalContent = `
        <div class="booking-modal-content">
            <div class="booking-section">
                <h4>Basic Information</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Booking Number:</span>
                        <span class="info-value">${booking.booking_number}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="info-value status-${booking.status}">${booking.status}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Client:</span>
                        <span class="info-value">${booking.client_name || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Publication:</span>
                        <span class="info-value">${booking.publication_name || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Publication Date:</span>
                        <span class="info-value">${formatDate(booking.publication_date)}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Insertions:</span>
                        <span class="info-value">${booking.insertions || 1}</span>
                    </div>
                </div>
            </div>
            
            <div class="booking-section">
                <h4>Ad Details</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Color Type:</span>
                        <span class="info-value">${booking.color_type_name || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Category:</span>
                        <span class="info-value">${booking.ad_category_name || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Size:</span>
                        <span class="info-value">${booking.ad_size_name || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Position:</span>
                        <span class="info-value">${booking.page_position_name || 'N/A'}</span>
                    </div>
                </div>
            </div>
            
            <div class="booking-section">
                <h4>Financial Details</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Base Rate:</span>
                        <span class="info-value">${formatCurrency(booking.base_rate || 0)}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Tax:</span>
                        <span class="info-value">${formatCurrency(booking.total_tax || 0)}</span>
                    </div>
                    ${booking.discount_amount > 0 ? `
                    <div class="info-item">
                        <span class="info-label">Discount:</span>
                        <span class="info-value">${formatCurrency(booking.discount_amount)}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Discount Reason:</span>
                        <span class="info-value">${booking.discount_reason || 'N/A'}</span>
                    </div>
                    ` : ''}
                    <div class="info-item total">
                        <span class="info-label">Total Amount:</span>
                        <span class="info-value">${formatCurrency(booking.total_amount || 0)}</span>
                    </div>
                </div>
            </div>
            
            ${booking.notes ? `
            <div class="booking-section">
                <h4>Notes</h4>
                <div class="notes-content">
                    ${booking.notes}
                </div>
            </div>
            ` : ''}
            
            <div class="booking-section">
                <h4>System Information</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Created:</span>
                        <span class="info-value">${formatDateTime(booking.created_at)}</span>
                    </div>
                    ${booking.updated_at ? `
                    <div class="info-item">
                        <span class="info-label">Last Updated:</span>
                        <span class="info-value">${formatDateTime(booking.updated_at)}</span>
                    </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
    
    modalBody.innerHTML = modalContent;
    bookingDetailsModal.style.display = 'block';
}

function closeBookingModal() {
    bookingDetailsModal.style.display = 'none';
    editBookingBtn.removeAttribute('data-booking-id');
}

function showDeleteConfirmation(bookingId) {
    selectedBookingForDelete = bookingId;
    deleteConfirmModal.style.display = 'block';
}

function closeDeleteModal() {
    deleteConfirmModal.style.display = 'none';
    selectedBookingForDelete = null;
}

async function confirmDelete() {
    if (!selectedBookingForDelete) return;
    
    try {
        const response = await fetch(`${BASE_URL}/booking_api.php?action=delete_booking&id=${selectedBookingForDelete}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess('Booking deleted successfully');
            closeDeleteModal();
            loadBookings(); // Refresh the list
        } else {
            throw new Error(result.message || 'Failed to delete booking');
        }
    } catch (error) {
        console.error('Failed to delete booking:', error);
        showError('Failed to delete booking: ' + error.message);
    }
}

function changePage(page) {
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    loadBookings();
}

function updatePagination() {
    // This is a simplified pagination - in a real app, you'd get total count from the API
    const hasBookings = currentBookings.length > 0;
    paginationContainer.classList.toggle('hidden', !hasBookings);
    
    if (hasBookings) {
        prevPageBtn.disabled = currentPage <= 1;
        nextPageBtn.disabled = currentPage >= totalPages;
        
        // Simple pagination info
        const startItem = (currentPage - 1) * itemsPerPage + 1;
        const endItem = Math.min(currentPage * itemsPerPage, currentBookings.length);
        paginationInfo.textContent = `Showing ${startItem}-${endItem} of ${currentBookings.length} bookings`;
    }
}

function updateBookingsCount(count) {
    bookingsCount.textContent = `${count} booking${count !== 1 ? 's' : ''} found`;
}

function showLoading() {
    loadingIndicator.classList.remove('hidden');
    bookingsList.classList.add('loading');
}

function hideLoading() {
    loadingIndicator.classList.add('hidden');
    bookingsList.classList.remove('loading');
}

function showError(message) {
    // Use existing modal system or console error
    if (typeof Modal !== 'undefined') {
        Modal.error('Error', message);
    } else {
        console.error('Error:', message);
        alert(`Error: ${message}`);
    }
}

function showSuccess(message) {
    // Use existing modal system or console log
    if (typeof Modal !== 'undefined') {
        Modal.success('Success', message);
    } else {
        console.log('Success:', message);
        alert(`Success: ${message}`);
    }
}

// Utility Functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-GH', {
        style: 'currency',
        currency: 'GHS',
        minimumFractionDigits: 2
    }).format(amount);
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-GH');
}

function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString('en-GH');
}
