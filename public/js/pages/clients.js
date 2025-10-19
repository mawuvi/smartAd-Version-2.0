/**
 * Client Management Page JavaScript
 * Location: public/js/pages/clients.js
 * Standards Applied: ES6+, async/await, error handling, debouncing
 */

(function() {
    'use strict';

    // State Management
    let currentClients = [];
    let currentPage = 1;
    let currentLimit = 20;
    let currentSort = 'company_name';
    let currentSortDirection = 'ASC';
    let currentFilters = {};
    let isLoading = false;
    let totalClients = 0;
    let totalPages = 1;

    // DOM Elements
    const totalClientsEl = document.getElementById('totalClients');
    const activeClientsEl = document.getElementById('activeClients');
    const creditWarningsEl = document.getElementById('creditWarnings');
    const totalRevenueEl = document.getElementById('totalRevenue');
    const clientSearchEl = document.getElementById('clientSearch');
    const searchBtnEl = document.getElementById('searchBtn');
    const statusFilterEl = document.getElementById('statusFilter');
    const typeFilterEl = document.getElementById('typeFilter');
    const creditFilterEl = document.getElementById('creditFilter');
    const dateFromFilterEl = document.getElementById('dateFromFilter');
    const dateToFilterEl = document.getElementById('dateToFilter');
    const clearFiltersBtnEl = document.getElementById('clearFiltersBtn');
    const applyFiltersBtnEl = document.getElementById('applyFiltersBtn');
    const createClientBtnEl = document.getElementById('createClientBtn');
    const exportClientsBtnEl = document.getElementById('exportClientsBtn');
    const itemsPerPageEl = document.getElementById('itemsPerPage');
    const paginationInfoEl = document.getElementById('paginationInfo');
    const clientsTableBodyEl = document.getElementById('clientsTableBody');
    const clientsTableEl = document.getElementById('clientsTable');
    const tableLoadingEl = document.getElementById('tableLoading');
    const noDataMessageEl = document.getElementById('noDataMessage');
    const paginationContainerEl = document.getElementById('paginationContainer');
    const loadingOverlayEl = document.getElementById('loadingOverlay');
    const loadingTextEl = document.getElementById('loadingText');

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        initializePage();
    });

    // --- Initialization ---
    
    function initializePage() {
        setupEventListeners();
        setupPermissionFiltering();
        loadStatistics();
        loadClients();
    }

    function setupPermissionFiltering() {
        // Check if user permissions are available
        if (!window.userPermissions) {
            console.warn('User permissions not available');
            return;
        }

        // Hide/show create button based on permission
        if (!window.userPermissions.includes('clients.create')) {
            createClientBtnEl.style.display = 'none';
        }

        // Hide/show export button based on permission
        if (!window.userPermissions.includes('clients.view')) {
            exportClientsBtnEl.style.display = 'none';
        }
    }

    function setupEventListeners() {
        // Search functionality
        clientSearchEl.addEventListener('input', handleSearchInput);
        searchBtnEl.addEventListener('click', () => {
            currentPage = 1;
            loadClients();
        });

        // Filter functionality
        [statusFilterEl, typeFilterEl, creditFilterEl, dateFromFilterEl, dateToFilterEl].forEach(el => {
            el.addEventListener('change', handleFilterChange);
        });

        clearFiltersBtnEl.addEventListener('click', clearFilters);
        applyFiltersBtnEl.addEventListener('click', () => {
            currentPage = 1;
            loadClients();
        });

        // Action buttons
        createClientBtnEl.addEventListener('click', handleCreateClient);
        exportClientsBtnEl.addEventListener('click', handleExportClients);
        itemsPerPageEl.addEventListener('change', handleLimitChange);

        // Table sorting
        const sortableHeaders = clientsTableEl.querySelectorAll('.sortable');
        sortableHeaders.forEach(header => {
            header.addEventListener('click', () => handleSortChange(header.dataset.sort));
        });

        // No data create button
        const createClientFromEmptyEl = document.getElementById('createClientFromEmpty');
        if (createClientFromEmptyEl) {
            createClientFromEmptyEl.addEventListener('click', handleCreateClient);
        }
    }

    // --- Data Loading Functions ---
    
    async function loadStatistics() {
        try {
            const response = await fetch(`${window.baseUrl}/api/client_api.php?action=get_statistics`);
            const result = await response.json();
            
            if (result.success) {
                const stats = result.data;
                totalClientsEl.textContent = stats.total_clients || 0;
                activeClientsEl.textContent = stats.active_clients || 0;
                creditWarningsEl.textContent = stats.credit_warnings || 0;
                totalRevenueEl.textContent = stats.total_revenue || 'GHS 0.00';
            } else {
                console.error('Failed to load statistics:', result.message);
            }
        } catch (error) {
            console.error('Error loading statistics:', error);
        }
    }

    async function loadClients() {
        if (isLoading) return;
        
        isLoading = true;
        showTableLoading();
        
        try {
            const params = new URLSearchParams({
                action: 'list_clients',
                page: currentPage,
                limit: currentLimit,
                sort: currentSort,
                sort_direction: currentSortDirection,
                ...currentFilters
            });
            
            const response = await fetch(`${window.baseUrl}/api/client_api.php?${params}`);
            const result = await response.json();
            
            if (result.success) {
                currentClients = result.data.clients || [];
                totalClients = result.data.total || 0;
                totalPages = result.data.total_pages || 1;
                
                renderClients();
                updatePagination();
                updatePaginationInfo();
            } else {
                console.error('Failed to load clients:', result.message);
                showError('Failed to load clients: ' + result.message);
            }
        } catch (error) {
            console.error('Error loading clients:', error);
            showError('Error loading clients: ' + error.message);
        } finally {
            isLoading = false;
            hideTableLoading();
        }
    }

    // --- Rendering Functions ---
    
    function renderClients() {
        if (currentClients.length === 0) {
            clientsTableEl.style.display = 'none';
            noDataMessageEl.style.display = 'block';
            paginationContainerEl.style.display = 'none';
            return;
        }

        clientsTableEl.style.display = 'table';
        noDataMessageEl.style.display = 'none';
        paginationContainerEl.style.display = 'flex';

        const tbody = clientsTableBodyEl;
        tbody.innerHTML = '';

        currentClients.forEach(client => {
            const row = createClientRow(client);
            tbody.appendChild(row);
        });
    }

    function createClientRow(client) {
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td>
                <span class="client-number">${client.client_number || 'N/A'}</span>
            </td>
            <td>
                <div class="company-info">
                    <div class="company-name">${client.company_name || 'N/A'}</div>
                    <div class="industry">${client.industry || 'N/A'}</div>
                </div>
            </td>
            <td>
                <span class="client-type">${getClientTypeLabel(client.client_type)}</span>
            </td>
            <td>
                <div class="contact-info">
                    <div class="contact-person">${client.contact_person || 'N/A'}</div>
                    <div class="contact-position">${client.contact_person_position || ''}</div>
                </div>
            </td>
            <td>
                <div class="contact-details">
                    <div class="contact-phone">${client.contact_phone || 'N/A'}</div>
                    <div class="contact-email">${client.contact_email || 'N/A'}</div>
                </div>
            </td>
            <td>
                <span class="status-badge ${client.status || 'inactive'}">${client.status || 'inactive'}</span>
            </td>
            <td>
                <div class="credit-info">
                    <div class="credit-limit">Limit: ${client.credit_limit || 'GHS 0.00'}</div>
                    <div class="outstanding-debt">Debt: ${client.outstanding_debt || 'GHS 0.00'}</div>
                    <div class="available-credit">Available: ${client.available_credit || 'GHS 0.00'}</div>
                </div>
            </td>
            <td>
                <div class="created-info">
                    <div class="created-date">${formatDate(client.created_at)}</div>
                    <div class="created-by">by ${client.created_by_name || 'Unknown'}</div>
                </div>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn btn-view" onclick="viewClient(${client.id})" title="View Client">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${window.userPermissions && window.userPermissions.includes('clients.edit') ? 
                        `<button class="action-btn btn-edit" onclick="editClient(${client.id})" title="Edit Client">
                            <i class="fas fa-edit"></i>
                        </button>` : ''}
                    ${window.userPermissions && window.userPermissions.includes('clients.delete') ? 
                        `<button class="action-btn btn-delete" onclick="deleteClient(${client.id})" title="Delete Client">
                            <i class="fas fa-trash"></i>
                        </button>` : ''}
                </div>
            </td>
        `;
        
        return row;
    }

    function updatePagination() {
        const paginationEl = document.getElementById('pagination');
        if (!paginationEl) return;

        paginationEl.innerHTML = '';

        // Previous button
        const prevBtn = createPaginationButton('Previous', currentPage - 1, currentPage <= 1);
        paginationEl.appendChild(prevBtn);

        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            paginationEl.appendChild(createPaginationButton('1', 1));
            if (startPage > 2) {
                paginationEl.appendChild(createPaginationButton('...', null, true));
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            paginationEl.appendChild(createPaginationButton(i.toString(), i, false, i === currentPage));
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationEl.appendChild(createPaginationButton('...', null, true));
            }
            paginationEl.appendChild(createPaginationButton(totalPages.toString(), totalPages));
        }

        // Next button
        const nextBtn = createPaginationButton('Next', currentPage + 1, currentPage >= totalPages);
        paginationEl.appendChild(nextBtn);
    }

    function createPaginationButton(text, page, disabled = false, active = false) {
        const li = document.createElement('li');
        li.className = `page-item ${active ? 'active' : ''} ${disabled ? 'disabled' : ''}`;
        
        const link = document.createElement('a');
        link.className = 'page-link';
        link.textContent = text;
        link.href = '#';
        
        if (!disabled && page !== null) {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                handlePageChange(page);
            });
        }
        
        li.appendChild(link);
        return li;
    }

    function updatePaginationInfo() {
        const start = (currentPage - 1) * currentLimit + 1;
        const end = Math.min(currentPage * currentLimit, totalClients);
        
        paginationInfoEl.textContent = `Showing ${start}-${end} of ${totalClients} clients`;
    }

    // --- Event Handlers ---
    
    let searchTimeout;
    function handleSearchInput(event) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1; // Reset to first page on new search
            loadClients();
        }, 500); // Debounce for 500ms
    }

    function handleFilterChange(event) {
        const filterName = event.target.id.replace('Filter', '');
        const filterValue = event.target.value;
        
        if (filterValue) {
            currentFilters[filterName] = filterValue;
        } else {
            delete currentFilters[filterName];
        }
    }

    function clearFilters() {
        currentFilters = {};
        statusFilterEl.value = '';
        typeFilterEl.value = '';
        creditFilterEl.value = '';
        dateFromFilterEl.value = '';
        dateToFilterEl.value = '';
        clientSearchEl.value = '';
        currentPage = 1;
        loadClients();
    }

    function handleSortChange(sortField) {
        if (currentSort === sortField) {
            currentSortDirection = currentSortDirection === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentSort = sortField;
            currentSortDirection = 'ASC';
        }
        
        // Update visual indicators
        document.querySelectorAll('.sortable').forEach(header => {
            header.classList.remove('active');
            const icon = header.querySelector('i');
            icon.className = 'fas fa-sort';
        });
        
        const activeHeader = document.querySelector(`[data-sort="${sortField}"]`);
        if (activeHeader) {
            activeHeader.classList.add('active');
            const icon = activeHeader.querySelector('i');
            icon.className = currentSortDirection === 'ASC' ? 'fas fa-sort-up' : 'fas fa-sort-down';
        }
        
        loadClients();
    }

    function handlePageChange(page) {
        currentPage = page;
        loadClients();
    }

    function handleLimitChange(event) {
        currentLimit = parseInt(event.target.value);
        currentPage = 1;
        loadClients();
    }

    async function handleCreateClient() {
        try {
            if (typeof ClientModule !== 'undefined') {
                await ClientModule.openCreateModal();
                // Refresh the client list after successful creation
                loadClients();
                loadStatistics();
            } else {
                showError('Client creation module not available');
            }
        } catch (error) {
            console.error('Error opening client creation modal:', error);
            showError('Failed to open client creation form');
        }
    }

    async function handleExportClients() {
        try {
            showLoading('Exporting clients...');
            
            const params = new URLSearchParams({
                action: 'export_clients',
                ...currentFilters
            });
            
            // Create a temporary link to trigger download
            const link = document.createElement('a');
            link.href = `${window.baseUrl}/api/client_api.php?${params}`;
            link.download = `clients_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            hideLoading();
            showSuccess('Clients exported successfully');
        } catch (error) {
            console.error('Error exporting clients:', error);
            hideLoading();
            showError('Failed to export clients');
        }
    }

    // --- Client Actions ---
    
    async function viewClient(clientId) {
        try {
            if (typeof ClientModule !== 'undefined') {
                await ClientModule.openViewModal(clientId);
            } else {
                showError('Client view module not available');
            }
        } catch (error) {
            console.error('Error opening client view modal:', error);
            showError('Failed to open client profile');
        }
    }

    async function editClient(clientId) {
        try {
            if (typeof ClientModule !== 'undefined') {
                await ClientModule.openEditModal(clientId);
                // Refresh the client list after successful edit
                loadClients();
                loadStatistics();
            } else {
                showError('Client edit module not available');
            }
        } catch (error) {
            console.error('Error opening client edit modal:', error);
            showError('Failed to open client edit form');
        }
    }

    async function deleteClient(clientId) {
        try {
            const confirmed = await Modal.confirm(
                'Are you sure you want to delete this client? This action cannot be undone.',
                'Delete Client'
            );
            
            if (confirmed) {
                showLoading('Deleting client...');
                
                const response = await fetch(`${window.baseUrl}/api/client_api.php?action=delete_client&id=${clientId}`, {
                    method: 'DELETE'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    hideLoading();
                    showSuccess('Client deleted successfully');
                    loadClients();
                    loadStatistics();
                } else {
                    hideLoading();
                    showError('Failed to delete client: ' + result.message);
                }
            }
        } catch (error) {
            console.error('Error deleting client:', error);
            hideLoading();
            showError('Failed to delete client');
        }
    }

    // --- UI Helper Functions ---
    
    function showTableLoading() {
        tableLoadingEl.style.display = 'flex';
    }

    function hideTableLoading() {
        tableLoadingEl.style.display = 'none';
    }

    function showLoading(message = 'Processing...') {
        loadingTextEl.textContent = message;
        loadingOverlayEl.style.display = 'flex';
    }

    function hideLoading() {
        loadingOverlayEl.style.display = 'none';
    }

    function showError(message) {
        if (typeof Modal !== 'undefined') {
            Modal.error('Error', message);
        } else {
            console.error('Error:', message);
            alert(`Error: ${message}`);
        }
    }

    function showSuccess(message) {
        if (typeof Modal !== 'undefined') {
            Modal.success('Success', message);
        } else {
            console.log('Success:', message);
        }
    }

    function showInfo(title, message) {
        if (typeof Modal !== 'undefined') {
            Modal.info(title, message);
        } else {
            alert(`${title}: ${message}`);
        }
    }

    // --- Utility Functions ---
    
    function getClientTypeLabel(type) {
        const labels = {
            'individual': 'Individual',
            'company': 'Company',
            'government': 'Government',
            'ngo': 'NGO'
        };
        return labels[type] || type;
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-GB', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    // Make functions globally available for onclick handlers
    window.viewClient = viewClient;
    window.editClient = editClient;
    window.deleteClient = deleteClient;

})();
