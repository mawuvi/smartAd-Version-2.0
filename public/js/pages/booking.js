// /public/js/pages/booking.js
// Booking System - Client Search, Rate Calculation, and Booking Management
// Standards Applied: Section 15.4 (Rate API), Section 18 (Modal System)

(function() {
    'use strict';

    // State Management
    let selectedClient = null;
    let currentRateData = null;
    let referenceData = {
        publications: [],
        colorTypes: [],
        categories: [],
        adSizes: [],
        pagePositions: []
    };

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        // Read config from data attributes to avoid inline scripts (Section 3: No inline)
        const bodyEl = document.body;
        const dataBaseUrl = bodyEl.getAttribute('data-base-url');
        if (dataBaseUrl) {
            window.baseUrl = dataBaseUrl;
        }
        // Fallback to known base path if not present
        if (!window.baseUrl) {
            window.baseUrl = '/smartAd';
        }
        try {
            window.currentUser = JSON.parse(bodyEl.getAttribute('data-current-user') || '{}');
        } catch (e) {
            window.currentUser = {};
        }

        initializeEventListeners();
        loadReferenceData();
        initializeDocumentUpload();
        checkPendingBookingFromCalculator();
    });

    // ===== Event Listeners =====
    function initializeEventListeners() {
        // Client Search
        const searchBtn = document.getElementById('searchClientBtn');
        const searchInput = document.getElementById('clientSearch');
        const createClientBtn = document.getElementById('createNewClientBtn');

        if (searchBtn) {
            searchBtn.addEventListener('click', performClientSearch);
        }

        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                    e.preventDefault();
                    performClientSearch();
            }
        });
    }

        if (createClientBtn) {
            createClientBtn.addEventListener('click', showCreateClientModal);
        }

        // Booking Form
        const bookingForm = document.getElementById('bookingForm');
        if (bookingForm) {
            bookingForm.addEventListener('submit', handleBookingSubmit);
        }

        // Rate calculation triggers
        const rateInputs = ['publication', 'colorType', 'adCategory', 'adSize', 'pagePosition', 'insertions'];
        rateInputs.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('change', calculateRate);
            }
        });

        // Recalculate button
        const recalculateBtn = document.getElementById('recalculateBtn');
        if (recalculateBtn) {
            recalculateBtn.addEventListener('click', calculateRate);
        }

        // Save draft button
        const saveDraftBtn = document.getElementById('saveDraftBtn');
        if (saveDraftBtn) {
            saveDraftBtn.addEventListener('click', saveAsDraft);
        }
    }

    // ===== Client Search =====
    async function performClientSearch() {
        const searchInput = document.getElementById('clientSearch');
        const searchTerm = searchInput.value.trim();

        if (searchTerm.length < 2) {
            await Modal.warning('Please enter at least 2 characters to search', 'Search Required');
            return;
        }

        showLoading(true);

        try {
            const response = await fetch(
                `${window.baseUrl}/api/client_api.php?action=search_clients&search_term=${encodeURIComponent(searchTerm)}`
            );

            const data = await response.json();

            if (data.success) {
                displaySearchResults(data.data.clients);
            } else {
                await Modal.error(data.message || 'Search failed', 'Search Error');
            }
        } catch (error) {
            console.error('Search error:', error);
            await Modal.error('Failed to search clients. Please try again.', 'Connection Error');
        } finally {
            showLoading(false);
        }
    }

    function displaySearchResults(clients) {
        const resultsContainer = document.getElementById('searchResults');
        const resultsList = document.getElementById('resultsList');
        const resultsCount = document.getElementById('resultsCount');

        if (!clients || clients.length === 0) {
            resultsList.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">🔍</div>
                    <div class="empty-state-message">No clients found</div>
                </div>
            `;
            resultsCount.textContent = '0 results';
        } else {
            resultsList.innerHTML = clients.map(client => `
                <div class="client-result-item" onclick="selectClient(${client.id})">
                    <h4>${escapeHtml(client.company_name)}</h4>
                    <p><strong>${client.client_number}</strong></p>
                    <p>Contact: ${escapeHtml(client.contact_person)} - ${escapeHtml(client.contact_phone || 'N/A')}</p>
                    <p>Credit Rating: <strong>${client.credit_rating}</strong> | Available Credit: <strong>${client.available_credit_formatted}</strong></p>
                </div>
            `).join('');
            resultsCount.textContent = `${clients.length} result${clients.length > 1 ? 's' : ''}`;
        }

        resultsContainer.classList.remove('hidden');
    }

    // ===== Client Selection =====
    window.selectClient = async function(clientId) {
        showLoading(true);

        try {
            const response = await fetch(
                `${window.baseUrl}/api/client_api.php?action=get_client&id=${clientId}`
            );

            const data = await response.json();

            if (data.success) {
                selectedClient = data.data.client;
                displaySelectedClient(selectedClient);
                showBookingForm();
            } else {
                await Modal.error(data.message || 'Failed to load client', 'Error');
            }
        } catch (error) {
            console.error('Client load error:', error);
            await Modal.error('Failed to load client details', 'Connection Error');
        } finally {
            showLoading(false);
        }
    };

    function displaySelectedClient(client) {
        document.getElementById('selectedClientName').textContent = client.company_name;
        document.getElementById('selectedClientNumber').textContent = client.client_number;
        document.getElementById('selectedClientContact').textContent = 
            `${client.contact_person} - ${client.contact_phone || 'N/A'}`;
        
        document.getElementById('clientCreditRating').textContent = client.credit_rating;
        document.getElementById('clientCreditLimit').textContent = client.credit_limit_formatted;
        document.getElementById('clientAvailableCredit').textContent = client.available_credit_formatted;
        document.getElementById('clientOutstandingDebt').textContent = client.outstanding_debt_formatted;

        // Debt breakdown
        document.getElementById('debtCurrent').textContent = client.debt_breakdown.current.formatted;
        document.getElementById('debt30Days').textContent = client.debt_breakdown.days_31_60.formatted;
        document.getElementById('debt60Days').textContent = client.debt_breakdown.days_61_90.formatted;
        document.getElementById('debt90Days').textContent = client.debt_breakdown.days_90_plus.formatted;

        document.getElementById('selectedClientInfo').style.display = 'block';
    }

    function showBookingForm() {
        document.getElementById('bookingFormCard').classList.remove('hidden');
        document.getElementById('bookingFormCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // ===== Create New Client Modal =====
    async function showCreateClientModal() {
        try {
            if (typeof ClientModule !== 'undefined') {
                await ClientModule.openCreateModal();
                // Refresh client list after successful creation
                await loadClients();
            } else {
                console.error('ClientModule not available');
                Modal.error('Error', 'Client creation module not available');
            }
        } catch (error) {
            console.error('Error opening client creation modal:', error);
            Modal.error('Error', 'Failed to open client creation form');
        }
    }
    
    // ===== Document Upload =====
    function initializeDocumentUpload() {
        if (typeof DocumentUploadModule !== 'undefined') {
            DocumentUploadModule.initializeDropZone('documentUploadZone', {
                maxFiles: 5,
                maxFileSize: 10 * 1024 * 1024, // 10MB
                allowedTypes: ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                onFileAdded: (fileData) => {
                    console.log('File added:', fileData.name);
                },
                onFileRemoved: (fileData) => {
                    console.log('File removed:', fileData.name);
                }
            });
        } else {
            console.warn('DocumentUploadModule not available');
        }
    }

    // ===== Load Reference Data =====
    async function loadReferenceData() {
        try {
            // Load all reference data in parallel
            const [publications, colorTypes, categories, adSizes, pagePositions] = await Promise.all([
                fetchReferenceData('get_publications'),
                fetchReferenceData('get_color_types'),
                fetchReferenceData('get_categories'),
                fetchReferenceData('get_ad_sizes'),
                fetchReferenceData('get_page_positions')
            ]);

            referenceData.publications = publications;
            referenceData.colorTypes = colorTypes;
            referenceData.categories = categories;
            referenceData.adSizes = adSizes;
            referenceData.pagePositions = pagePositions;

            populateDropdowns();
        } catch (error) {
            console.error('Failed to load reference data:', error);
            await Modal.error('Failed to load form options. Please refresh the page.', 'Loading Error');
        }
    }

    async function fetchReferenceData(action) {
        const response = await fetch(`${window.baseUrl}/api/rate_api.php?action=${action}`);
        const data = await response.json();
        
        if (data.success) {
            // Extract the array from the response (e.g., data.data.publications)
            const key = Object.keys(data.data)[0];
            return data.data[key] || [];
        }
        return [];
    }

    function populateDropdowns() {
        populateDropdown('publication', referenceData.publications);
        populateDropdown('colorType', referenceData.colorTypes);
        populateDropdown('adCategory', referenceData.categories);
        populateDropdown('adSize', referenceData.adSizes);
        populateDropdown('pagePosition', referenceData.pagePositions);
    }

    function populateDropdown(elementId, items) {
        const select = document.getElementById(elementId);
        if (!select) return;

        // Keep the first option (placeholder)
        const placeholder = select.querySelector('option[value=""]');
        select.innerHTML = '';
        
        if (placeholder) {
            select.appendChild(placeholder);
        }

        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            select.appendChild(option);
        });
    }

    // ===== Rate Calculation (Source of Truth: rate_api.php) =====
    async function calculateRate() {
        const publication = document.getElementById('publication').value;
        const colorType = document.getElementById('colorType').value;
        const category = document.getElementById('adCategory').value;
        const size = document.getElementById('adSize').value;
        const position = document.getElementById('pagePosition').value;
        const insertions = parseInt(document.getElementById('insertions').value) || 1;
        const publicationDate = document.getElementById('publicationDate').value || new Date().toISOString().split('T')[0];

        // Validate all required fields
        if (!publication || !colorType || !category || !size || !position) {
            return; // Don't calculate until all fields are filled
        }

        try {
            // Call rate_api.php as source of truth (Section 15.4)
            const url = `${window.baseUrl}/api/rate_api.php?action=calculate_rate` +
                `&publication_id=${publication}` +
                `&color_type_id=${colorType}` +
                `&category_id=${category}` +
                `&size_id=${size}` +
                `&position_id=${position}` +
                `&effective_date=${publicationDate}`;

            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                currentRateData = data.data;
                displayRateCalculation(insertions);
            } else {
                await Modal.warning(data.message || 'No rate found for selected criteria', 'Rate Not Found');
                clearRateDisplay();
            }
        } catch (error) {
            console.error('Rate calculation error:', error);
            await Modal.error('Failed to calculate rate. Please try again.', 'Calculation Error');
        }
    }

    function displayRateCalculation(insertions) {
        if (!currentRateData) return;

        const baseRate = parseFloat(currentRateData.base_rate);
        const subtotal = baseRate * insertions;
        
        // Display base rate and subtotal
        document.getElementById('baseRate').textContent = currentRateData.base_rate_formatted;
        document.getElementById('insertionCount').textContent = insertions;
        document.getElementById('subtotal').textContent = formatCurrency(subtotal);

        // Display tax breakdown
        const taxList = document.getElementById('taxList');
        if (currentRateData.tax_breakdown && currentRateData.tax_breakdown.length > 0) {
            taxList.innerHTML = currentRateData.tax_breakdown.map(tax => `
                <div class="tax-item">
                    <span class="tax-name">${escapeHtml(tax.name)} (${tax.rate}%)</span>
                    <span class="tax-amount">${tax.amount_formatted}</span>
                </div>
            `).join('');
        } else {
            taxList.innerHTML = '<p class="empty-state-message">No taxes configured</p>';
        }

        // Calculate and display total
        const taxTotal = parseFloat(currentRateData.total_tax) * insertions;
        const total = subtotal + taxTotal;
        
        document.getElementById('totalAmount').textContent = formatCurrency(total);
    }

    function clearRateDisplay() {
        document.getElementById('baseRate').textContent = 'GHS 0.00';
        document.getElementById('subtotal').textContent = 'GHS 0.00';
        document.getElementById('taxList').innerHTML = '';
        document.getElementById('totalAmount').textContent = 'GHS 0.00';
        currentRateData = null;
    }

    // ===== Booking Submission =====
    async function handleBookingSubmit(e) {
        e.preventDefault();

        if (!selectedClient) {
            await Modal.warning('Please select a client first', 'Client Required');
            return;
        }

        if (!currentRateData) {
            await Modal.warning('Please complete all rate fields to calculate pricing', 'Rate Required');
            return;
        }

        const confirmed = await Modal.confirm(
            'Create this booking and save to the system?',
            'Confirm Booking',
            'Cancel',
            'Yes, Create Booking'
        );

        if (!confirmed) return;

        await saveBooking('draft');
    }

    async function saveAsDraft() {
        if (!selectedClient) {
            await Modal.warning('Please select a client first', 'Client Required');
            return;
        }

        await saveBooking('draft');
    }

    async function saveBooking(status) {
        const insertions = parseInt(document.getElementById('insertions').value) || 1;
        const baseRate = parseFloat(currentRateData.base_rate);
        const subtotal = baseRate * insertions;
        const taxTotal = parseFloat(currentRateData.total_tax) * insertions;
        const total = subtotal + taxTotal;

        const bookingData = {
            client_id: selectedClient.id,
            publication_id: document.getElementById('publication').value,
            color_type_id: document.getElementById('colorType').value,
            ad_category_id: document.getElementById('adCategory').value,
            ad_size_id: document.getElementById('adSize').value,
            page_position_id: document.getElementById('pagePosition').value,
            publication_date: document.getElementById('publicationDate').value,
            insertions: insertions,
            base_rate: baseRate,
            subtotal: subtotal,
            tax_total: taxTotal,
            total: total,
            tax_configuration_id: currentRateData.tax_configuration?.id || null,
            special_instructions: document.getElementById('specialInstructions').value,
            status: status
        };

        try {
            const response = await fetch(`${window.baseUrl}/api/booking_api.php?action=create_booking`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(bookingData)
            });

            const data = await response.json();

            if (data.success) {
                const bookingId = data.data.booking?.id;
                
                // Upload documents if any
                if (typeof DocumentUploadModule !== 'undefined') {
                    const uploadedFiles = DocumentUploadModule.getUploadedFiles();
                    if (uploadedFiles.length > 0 && bookingId) {
                        try {
                            await DocumentUploadModule.uploadFiles(bookingId);
                            console.log('Documents uploaded successfully');
                        } catch (uploadError) {
                            console.error('Document upload error:', uploadError);
                            await Modal.warning(
                                'Booking created successfully, but some documents failed to upload. You can upload them later.',
                                'Partial Success'
                            );
                        }
                    }
                }
                
                await Modal.success(
                    `Booking created successfully! Booking Number: ${data.data.booking?.booking_number || 'N/A'}`,
                    'Success'
                );
                
                // Reset form
                document.getElementById('bookingForm').reset();
                clearRateDisplay();
                
                // Reinitialize document upload zone
                initializeDocumentUpload();
                
                // Ask if user wants to create another booking
                const createAnother = await Modal.confirm(
                    'Would you like to create another booking for this client?',
                    'Create Another?',
                    'No, Done',
                    'Yes, New Booking'
                );

                if (!createAnother) {
                    // Reload page or redirect
                    window.location.reload();
                }
            } else {
                await Modal.error(data.message || 'Failed to create booking', 'Error');
            }
        } catch (error) {
            console.error('Booking save error:', error);
            await Modal.error('Failed to save booking. Please try again.', 'Connection Error');
        }
    }

    // ===== Utility Functions =====
    function showLoading(show) {
        const loading = document.getElementById('searchLoading');
        if (loading) {
            loading.classList[show ? 'remove' : 'add']('hidden');
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatCurrency(amount) {
        return `GHS ${parseFloat(amount).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}`;
    }

    // ===== Calculator Integration =====
    function checkPendingBookingFromCalculator() {
        const pendingBooking = sessionStorage.getItem('pendingBooking');
        
        if (pendingBooking) {
            try {
                const data = JSON.parse(pendingBooking);
                
                // Populate booking form fields
                document.getElementById('publication').value = data.publication_id;
                document.getElementById('colorType').value = data.color_type_id;
                document.getElementById('adCategory').value = data.ad_category_id;
                document.getElementById('adSize').value = data.ad_size_id;
                document.getElementById('pagePosition').value = data.page_position_id;
                document.getElementById('insertions').value = data.insertions;
                
                // Set discount if applicable
                if (data.discount_amount > 0) {
                    document.getElementById('discountSelect').value = 'custom';
                    // You might need to add custom discount handling here
                }
                
                // Trigger rate calculation to display results
                calculateRate();
                
                // Clear sessionStorage
                sessionStorage.removeItem('pendingBooking');
                
                // Show notification
                Modal.success('Rate Loaded', 'Rate configuration transferred from calculator. Please select a client to continue.');
                
                // Focus on client search field
                const clientSearch = document.getElementById('clientSearch');
                if (clientSearch) {
                    setTimeout(() => clientSearch.focus(), 500);
                }
                
            } catch (error) {
                console.error('Error loading pending booking:', error);
                sessionStorage.removeItem('pendingBooking');
                Modal.error('Error', 'Failed to load rate configuration from calculator.');
            }
        }
    }

})();