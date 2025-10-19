/**
 * Client Module JavaScript
 * Location: public/js/modules/clientModule.js
 * Multi-step wizard for client creation with validation and auto-save
 * Standards Applied: ES6+, async/await, error handling, state management
 */

(function() {
    'use strict';

    // State Management
    let currentStep = 1;
    let totalSteps = 5;
    let formData = {};
    let isSubmitting = false;
    let autoSaveInterval = null;

    // Wizard Configuration
    const wizardConfig = {
        steps: [
            { id: 1, title: 'Basic Information', icon: 'fas fa-building' },
            { id: 2, title: 'Contact Details', icon: 'fas fa-user' },
            { id: 3, title: 'Address & Location', icon: 'fas fa-map-marker-alt' },
            { id: 4, title: 'Credit Settings', icon: 'fas fa-credit-card' },
            { id: 5, title: 'Review & Save', icon: 'fas fa-check-circle' }
        ]
    };

    // Client Module API
    window.ClientModule = {
        openCreateModal: openCreateModal,
        openEditModal: openEditModal,
        openViewModal: openViewModal
    };

    // --- Main Functions ---

    async function openCreateModal() {
        return new Promise(async (resolve, reject) => {
            try {
                // Load modal content
                const modalContent = await loadModalContent();
                
                // Open modal with wizard content
                const modalInstance = await Modal.open(modalContent, {
                    title: 'Create New Client',
                    size: 'wizard',
                    className: 'client-creation-modal',
                    buttons: [
                    { text: 'Back', class: 'btn-secondary btn-back hidden', action: (modal) => navigateWizard(modal, -1) },
                    { text: 'Next', class: 'btn-primary btn-next', action: (modal) => navigateWizard(modal, 1) },
                        { text: 'Create Client', class: 'btn-primary btn-save hidden', action: (modal) => submitForm(modal) }
                    ]
                });

                // Initialize wizard with a small delay to ensure modal is fully loaded
                setTimeout(() => {
                    initializeWizard(modalInstance);
                }, 100);

            } catch (error) {
                console.error('Failed to open client creation modal:', error);
                reject(error);
            }
        });
    }

    async function openEditModal(clientId) {
        return new Promise(async (resolve, reject) => {
            try {
                // Load client data first
                const clientData = await loadClientData(clientId);
                if (!clientData) {
                    throw new Error('Failed to load client data');
                }

                // Load modal content
                const modalContent = await loadModalContent();
                
                // Open modal with wizard content
                const modalInstance = await Modal.open(modalContent, {
                    title: 'Edit Client',
                    size: 'wizard',
                    className: 'client-edit-modal',
                    buttons: [
                        { text: 'Back', class: 'btn-secondary btn-back hidden', action: (modal) => navigateWizard(modal, -1) },
                        { text: 'Next', class: 'btn-primary btn-next', action: (modal) => navigateWizard(modal, 1) },
                        { text: 'Update Client', class: 'btn-primary btn-save hidden', action: (modal) => submitEditForm(modal, clientId) }
                    ]
                });

                // Initialize wizard with client data
                setTimeout(() => {
                    initializeWizard(modalInstance, clientData);
                }, 100);

            } catch (error) {
                console.error('Failed to open client edit modal:', error);
                reject(error);
            }
        });
    }

    async function openViewModal(clientId) {
        return new Promise(async (resolve, reject) => {
            try {
                // Load client data
                const clientData = await loadClientData(clientId);
                if (!clientData) {
                    throw new Error('Failed to load client data');
                }

                // Create view content
                const viewContent = createClientViewContent(clientData);
                
                // Open modal
                const modalInstance = await Modal.open(viewContent, {
                    title: `Client Profile - ${clientData.company_name}`,
                    size: 'large',
                    className: 'client-view-modal',
                    buttons: [
                        { 
                            text: 'Edit Client', 
                            class: 'btn-primary', 
                            action: async () => {
                                Modal.close();
                                await openEditModal(clientId);
                            }
                        },
                        { 
                            text: 'Close', 
                            class: 'btn-secondary', 
                            action: () => {
                                Modal.close();
                            }
                        }
                    ]
                });

                resolve(modalInstance);

            } catch (error) {
                console.error('Failed to open client view modal:', error);
                reject(error);
            }
        });
    }

    function createClientViewContent(client) {
        return `
            <div class="client-view-content">
                <!-- Basic Information -->
                <div class="view-section">
                    <h4 class="section-title"><i class="fas fa-building"></i> Basic Information</h4>
                    <div class="view-grid">
                        <div class="view-item">
                            <label>Client Number</label>
                            <span>${client.client_number || 'N/A'}</span>
                        </div>
                        <div class="view-item">
                            <label>Company Name</label>
                            <span>${client.company_name || 'N/A'}</span>
                        </div>
                        <div class="view-item">
                            <label>Client Type</label>
                            <span class="badge">${getClientTypeLabel(client.client_type)}</span>
                        </div>
                        <div class="view-item">
                            <label>Industry</label>
                            <span>${client.industry || 'N/A'}</span>
                        </div>
                        <div class="view-item">
                            <label>Status</label>
                            <span class="status-badge ${client.status}">${client.status || 'inactive'}</span>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="view-section">
                    <h4 class="section-title"><i class="fas fa-user"></i> Contact Information</h4>
                    <div class="view-grid">
                        <div class="view-item">
                            <label>Contact Person</label>
                            <span>${client.contact_person || 'N/A'}</span>
                        </div>
                        <div class="view-item">
                            <label>Position/Title</label>
                            <span>${client.contact_person_position || 'N/A'}</span>
                        </div>
                        <div class="view-item">
                            <label>Phone</label>
                            <span><a href="tel:${client.contact_phone}">${client.contact_phone || 'N/A'}</a></span>
                        </div>
                        <div class="view-item">
                            <label>Email</label>
                            <span><a href="mailto:${client.contact_email}">${client.contact_email || 'N/A'}</a></span>
                        </div>
                        <div class="view-item">
                            <label>Website</label>
                            <span>${client.website ? `<a href="${client.website}" target="_blank">${client.website}</a>` : 'N/A'}</span>
                        </div>
                        <div class="view-item">
                            <label>Preferred Contact</label>
                            <span>${client.preferred_contact_method || 'N/A'}</span>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="view-section">
                    <h4 class="section-title"><i class="fas fa-map-marker-alt"></i> Address & Location</h4>
                    <div class="view-grid">
                        <div class="view-item full-width">
                            <label>Physical Address</label>
                            <span>${client.address || 'N/A'}</span>
                        </div>
                        <div class="view-item">
                            <label>City</label>
                            <span>${client.city || 'N/A'}</span>
                        </div>
                        <div class="view-item">
                            <label>Region</label>
                            <span>${client.region || 'N/A'}</span>
                        </div>
                        <div class="view-item">
                            <label>Country</label>
                            <span>${client.country || 'N/A'}</span>
                        </div>
                        <div class="view-item">
                            <label>Postal Code</label>
                            <span>${client.postal_code || 'N/A'}</span>
                        </div>
                        <div class="view-item">
                            <label>Digital Address</label>
                            <span>${client.digital_address || 'N/A'}</span>
                        </div>
                    </div>
                </div>

                <!-- Credit Information -->
                <div class="view-section">
                    <h4 class="section-title"><i class="fas fa-credit-card"></i> Credit Information</h4>
                    <div class="view-grid">
                        <div class="view-item">
                            <label>Credit Limit</label>
                            <span class="amount">${formatCurrency(client.credit_limit)}</span>
                        </div>
                        <div class="view-item">
                            <label>Outstanding Debt</label>
                            <span class="amount ${parseFloat(client.outstanding_debt || 0) > 0 ? 'text-danger' : ''}">${client.outstanding_debt || 'GHS 0.00'}</span>
                        </div>
                        <div class="view-item">
                            <label>Available Credit</label>
                            <span class="amount text-success">${client.available_credit || 'GHS 0.00'}</span>
                        </div>
                        <div class="view-item">
                            <label>Credit Rating</label>
                            <span class="badge ${client.credit_rating}">${client.credit_rating || 'N/A'}</span>
                        </div>
                        <div class="view-item">
                            <label>Payment Terms</label>
                            <span>${client.payment_terms || 'N/A'}</span>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                ${client.client_notes ? `
                <div class="view-section">
                    <h4 class="section-title"><i class="fas fa-sticky-note"></i> Notes</h4>
                    <div class="view-notes">
                        ${client.client_notes}
                    </div>
                </div>
                ` : ''}

                <!-- Audit Information -->
                <div class="view-section audit-info">
                    <div class="view-grid">
                        <div class="view-item">
                            <label>Created</label>
                            <span>${formatDateTime(client.created_at)}</span>
                        </div>
                        <div class="view-item">
                            <label>Created By</label>
                            <span>${client.created_by_name || 'N/A'}</span>
                        </div>
                        ${client.updated_at ? `
                        <div class="view-item">
                            <label>Last Updated</label>
                            <span>${formatDateTime(client.updated_at)}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>

            <style>
            .client-view-content {
                padding: 1rem 0;
            }
            
            .view-section {
                margin-bottom: 2rem;
                padding: 1.5rem;
                background: #f8fafc;
                border-radius: 8px;
            }
            
            .section-title {
                margin: 0 0 1rem 0;
                font-size: 1rem;
                font-weight: 600;
                color: #374151;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .section-title i {
                color: #3b82f6;
            }
            
            .view-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1rem;
            }
            
            .view-item {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .view-item.full-width {
                grid-column: 1 / -1;
            }
            
            .view-item label {
                font-size: 0.75rem;
                font-weight: 500;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }
            
            .view-item span {
                font-size: 0.875rem;
                color: #1e293b;
            }
            
            .view-item a {
                color: #3b82f6;
                text-decoration: none;
            }
            
            .view-item a:hover {
                text-decoration: underline;
            }
            
            .badge {
                display: inline-block;
                padding: 0.25rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 500;
                background: #e0f2fe;
                color: #0369a1;
            }
            
            .status-badge.active {
                background: #dcfce7;
                color: #166534;
            }
            
            .status-badge.inactive {
                background: #fee2e2;
                color: #991b1b;
            }
            
            .badge.excellent {
                background: #dcfce7;
                color: #166534;
            }
            
            .badge.good {
                background: #dbeafe;
                color: #1e40af;
            }
            
            .badge.fair {
                background: #fef3c7;
                color: #92400e;
            }
            
            .badge.poor {
                background: #fee2e2;
                color: #991b1b;
            }
            
            .amount {
                font-weight: 600;
            }
            
            .text-success {
                color: #059669;
            }
            
            .text-danger {
                color: #dc2626;
            }
            
            .view-notes {
                padding: 1rem;
                background: white;
                border-radius: 6px;
                font-size: 0.875rem;
                color: #475569;
                white-space: pre-wrap;
            }
            
            .audit-info {
                background: #f1f5f9;
                border-top: 2px solid #e2e8f0;
            }
            
            @media (max-width: 768px) {
                .view-grid {
                    grid-template-columns: 1fr;
                }
            }
            </style>
        `;
    }

    function formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleString('en-GB', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    async function loadClientData(clientId) {
        try {
            const response = await fetch(`${window.baseUrl}/api/client_api.php?action=get_client&id=${clientId}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            } else {
                console.error('Failed to load client:', result.message);
                return null;
            }
        } catch (error) {
            console.error('Error loading client data:', error);
            return null;
        }
    }

    async function loadModalContent() {
        try {
            const response = await fetch(`${window.baseUrl}/app/views/clients/new_client_modal.php`);
            if (!response.ok) {
                throw new Error('Failed to load modal content');
            }
            return await response.text();
        } catch (error) {
            console.error('Error loading modal content:', error);
            // Fallback to inline content if fetch fails
            return getFallbackModalContent();
        }
    }

    function getFallbackModalContent() {
        return `
            <div class="client-creation-wizard">
                <div class="wizard-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" id="wizardProgressFill"></div>
                    </div>
                </div>
                <form id="newClientForm" class="wizard-form">
                    <div class="wizard-step active" data-wizard-step="1">
                        <div class="step-content">
                            <div class="form-group">
                                <label for="company_name">Company Name *</label>
                                <input type="text" id="company_name" name="company_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="client_type">Client Type *</label>
                                <select id="client_type" name="client_type" class="form-control" required>
                                    <option value="">Select client type...</option>
                                    <option value="individual">Individual</option>
                                    <option value="company">Company</option>
                                    <option value="government">Government</option>
                                    <option value="ngo">NGO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        `;
    }

    function initializeWizard(modal, existingData = null) {
        try {
            // Reset state
            currentStep = 1;
            formData = existingData || {};
            isSubmitting = false;

            // Setup event listeners
            setupEventListeners(modal);

            // Load reference data for dropdowns
            loadReferenceData(modal);

            // If we have existing data, populate the form
            if (existingData) {
                populateFormWithData(modal, existingData);
            }

            // Setup auto-save
            setupAutoSave(modal);

            // Update wizard UI
            updateWizardProgress();
            updateWizardButtons(modal);

        } catch (error) {
            console.error('Error initializing wizard:', error);
        }
    }

    function populateFormWithData(modal, data) {
        // Wait for DOM to be ready
        setTimeout(() => {
            const form = modal.content.querySelector('#newClientForm');
            if (!form) return;

            // Populate all form fields
            Object.keys(data).forEach(key => {
                const field = form.querySelector(`[name="${key}"]`);
                if (field && data[key]) {
                    field.value = data[key];
                }
            });
        }, 200);
    }

    function setupEventListeners(modal) {
        const form = modal.content.querySelector('#newClientForm');
        if (!form) return;

        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => clearFieldError(input));
        });

        // Form submission
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            submitForm(modal);
        });
    }

    function loadReferenceData(modal) {
        const clientTypeSelect = modal.content.querySelector('#client_type');
        const industrySelect = modal.content.querySelector('#industry');

        // Load client types
        if (clientTypeSelect) {
            const clientTypesUrl = `${window.baseUrl}/api/setup_api.php?action=get_client_types`;
            fetch(clientTypesUrl)
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        populateDropdown(clientTypeSelect, result.data, 'Select client type...', 'code', 'name');
                    } else {
                        console.error('Client types API failed:', result.message);
                        clientTypeSelect.innerHTML = '<option value="">Error loading client types</option>';
                    }
                })
                .catch(error => {
                    console.error('Failed to load client types:', error);
                    clientTypeSelect.innerHTML = '<option value="">Error loading client types</option>';
                });
        }

        // Load industries
        if (industrySelect) {
            const industriesUrl = `${window.baseUrl}/api/setup_api.php?action=get_industries`;
            fetch(industriesUrl)
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        populateDropdown(industrySelect, result.data, 'Select industry...', 'name', 'name');
                    } else {
                        console.error('Industries API failed:', result.message);
                        industrySelect.innerHTML = '<option value="">Error loading industries</option>';
                    }
                })
                .catch(error => {
                    console.error('Failed to load industries:', error);
                    industrySelect.innerHTML = '<option value="">Error loading industries</option>';
                });
        }

        // Load countries (default to Ghana)
        const countrySelect = modal.content.querySelector('#country');
        if (countrySelect) {
            const countriesUrl = `${window.baseUrl}/api/setup_api.php?action=list&table=setup_countries`;
            fetch(countriesUrl)
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        populateDropdown(countrySelect, result.data, 'Select country...', 'name', 'name');
                        // Auto-select Ghana if available
                        const ghanaOption = countrySelect.querySelector('option[value*="Ghana"], option[value*="ghana"]');
                        if (ghanaOption) {
                            ghanaOption.selected = true;
                            loadRegions(modal, ghanaOption.value);
                        }
                    }
                })
                .catch(error => {
                    console.error('Failed to load countries:', error);
                });
        }

        // Setup cascading dropdowns
        setupCascadingDropdowns(modal);
    }

    function setupCascadingDropdowns(modal) {
        const countrySelect = modal.content.querySelector('#country');
        const regionSelect = modal.content.querySelector('#region');
        const citySelect = modal.content.querySelector('#city');

        if (countrySelect && regionSelect) {
            countrySelect.addEventListener('change', (e) => {
                loadRegions(modal, e.target.value);
                // Clear dependent dropdowns
                regionSelect.innerHTML = '<option value="">Select region...</option>';
                citySelect.innerHTML = '<option value="">Select city...</option>';
            });
        }

        if (regionSelect && citySelect) {
            regionSelect.addEventListener('change', (e) => {
                loadCities(modal, e.target.value);
                citySelect.innerHTML = '<option value="">Select city...</option>';
            });
        }
    }

    function loadRegions(modal, countryName) {
        const regionSelect = modal.content.querySelector('#region');
        if (!regionSelect) return;

        // For now, use a simple approach - in a real implementation, you'd have a regions API
        const regions = [
            'Greater Accra', 'Ashanti', 'Western', 'Central', 'Volta',
            'Eastern', 'Northern', 'Upper East', 'Upper West', 'Brong Ahafo'
        ];

        regionSelect.innerHTML = '<option value="">Select region...</option>';
        regions.forEach(region => {
            const option = document.createElement('option');
            option.value = region;
            option.textContent = region;
            regionSelect.appendChild(option);
        });
    }

    function loadCities(modal, regionName) {
        const citySelect = modal.content.querySelector('#city');
        if (!citySelect) return;

        // Simple city mapping - in a real implementation, you'd have a cities API
        const cityMap = {
            'Greater Accra': ['Accra', 'Tema', 'Madina', 'Adenta'],
            'Ashanti': ['Kumasi', 'Obuasi', 'Ejisu', 'Mampong'],
            'Western': ['Takoradi', 'Sekondi', 'Tarkwa', 'Prestea'],
            'Central': ['Cape Coast', 'Kasoa', 'Winneba', 'Saltpond'],
            'Volta': ['Ho', 'Keta', 'Hohoe', 'Kpando']
        };

        const cities = cityMap[regionName] || [];
        citySelect.innerHTML = '<option value="">Select city...</option>';
        cities.forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            citySelect.appendChild(option);
        });
    }

    function populateDropdown(selectElement, data, placeholder, valueField, textField) {
        if (!selectElement || !data) return;

        // Clear existing options except placeholder
        selectElement.innerHTML = `<option value="">${placeholder}</option>`;

        data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item[valueField];
                        option.textContent = item[textField];
            selectElement.appendChild(option);
        });
    }

    // --- Wizard Navigation ---

    function navigateWizard(modal, direction) {
        const newStep = currentStep + direction;
        
        if (newStep < 1 || newStep > totalSteps) {
            return;
        }

        // Validate current step before moving forward
        if (direction > 0 && !validateCurrentStep(modal)) {
            return;
        }

        // Update current step
        currentStep = newStep;

        // Update UI
        updateWizardSteps(modal);
        updateWizardProgress();
        updateWizardButtons(modal);

        // Update review step if we're on the last step
        if (currentStep === totalSteps) {
            updateReviewStep(modal);
        }
    }

    function updateWizardSteps(modal) {
        const steps = modal.content.querySelectorAll('.wizard-step');
        steps.forEach((step, index) => {
            step.classList.toggle('active', index + 1 === currentStep);
        });
    }

    function updateWizardProgress() {
        const progressFill = document.getElementById('wizardProgressFill');
        if (progressFill) {
            const progress = (currentStep / totalSteps) * 100;
            progressFill.style.width = `${progress}%`;
        }
    }

    function updateWizardButtons(modal) {
        const backBtn = modal.content.querySelector('.btn-back');
        const nextBtn = modal.content.querySelector('.btn-next');
        const saveBtn = modal.content.querySelector('.btn-save');

        if (backBtn) {
            backBtn.classList.toggle('hidden', currentStep === 1);
        }

        if (nextBtn) {
            nextBtn.classList.toggle('hidden', currentStep === totalSteps);
        }

        if (saveBtn) {
            saveBtn.classList.toggle('hidden', currentStep !== totalSteps);
        }
    }

    // --- Validation ---

    function validateCurrentStep(modal) {
        const currentStepElement = modal.content.querySelector(`[data-wizard-step="${currentStep}"]`);
        if (!currentStepElement) return true;

        const requiredFields = currentStepElement.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    function validateField(field) {
        const value = field.value.trim();
        const fieldName = field.name || field.id;
        let isValid = true;
        let errorMessage = '';

        // Required field validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = `${getFieldLabel(field)} is required`;
        }

        // Email validation
        if (field.type === 'email' && value && !isValidEmail(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address';
        }

        // Phone validation
        if (field.type === 'tel' && value && !isValidPhone(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid phone number';
        }

        // URL validation
        if (field.type === 'url' && value && !isValidUrl(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid URL';
        }

        // Show/hide error
        showValidationMessage(field, isValid, errorMessage);

        return isValid;
    }

    function showValidationMessage(field, isValid, message) {
        const errorElement = document.getElementById(`${field.name || field.id}_error`);
        if (errorElement) {
            if (isValid) {
                errorElement.classList.remove('show');
                errorElement.textContent = '';
                field.classList.remove('error');
            } else {
                errorElement.classList.add('show');
                errorElement.textContent = message;
                field.classList.add('error');
            }
        }
    }

    function clearFieldError(field) {
        const errorElement = document.getElementById(`${field.name || field.id}_error`);
        if (errorElement) {
            errorElement.classList.remove('show');
            field.classList.remove('error');
        }
    }

    function getFieldLabel(field) {
        const label = field.closest('.form-group')?.querySelector('label');
        return label ? label.textContent.replace('*', '').trim() : field.name || field.id;
    }

    // --- Review Step ---

    function updateReviewStep(modal) {
        // Collect all form data
        collectFormData(modal);

        // Update review sections
        updateReviewSection(modal, 'reviewBasicInfo', getBasicInfoReview());
        updateReviewSection(modal, 'reviewContactInfo', getContactInfoReview());
        updateReviewSection(modal, 'reviewAddressInfo', getAddressInfoReview());
        updateReviewSection(modal, 'reviewCreditInfo', getCreditInfoReview());
    }

    function collectFormData(modal) {
        const form = modal.content.querySelector('#newClientForm');
        if (!form) return;

        const formDataObj = new FormData(form);
        formData = {};
        
        for (let [key, value] of formDataObj.entries()) {
            formData[key] = value;
        }
    }

    function updateReviewSection(modal, sectionId, content) {
        const section = modal.content.querySelector(`#${sectionId}`);
        if (section) {
            section.innerHTML = content;
        }
    }

    function getBasicInfoReview() {
        return `
            <div class="review-item"><strong>Company:</strong> ${formData.company_name || 'N/A'}</div>
            <div class="review-item"><strong>Type:</strong> ${getClientTypeLabel(formData.client_type)}</div>
            <div class="review-item"><strong>Industry:</strong> ${formData.industry || 'N/A'}</div>
            <div class="review-item"><strong>Description:</strong> ${formData.client_description || 'N/A'}</div>
        `;
    }

    function getContactInfoReview() {
        return `
            <div class="review-item"><strong>Contact Person:</strong> ${formData.contact_person || 'N/A'}</div>
            <div class="review-item"><strong>Position:</strong> ${formData.contact_person_position || 'N/A'}</div>
            <div class="review-item"><strong>Phone:</strong> ${formData.contact_phone || 'N/A'}</div>
            <div class="review-item"><strong>Email:</strong> ${formData.contact_email || 'N/A'}</div>
            <div class="review-item"><strong>Alternative Contact:</strong> ${formData.alternative_contact || 'N/A'}</div>
            <div class="review-item"><strong>Website:</strong> ${formData.website || 'N/A'}</div>
        `;
    }

    function getAddressInfoReview() {
        return `
            <div class="review-item"><strong>Country:</strong> ${formData.country || 'N/A'}</div>
            <div class="review-item"><strong>Region:</strong> ${formData.region || 'N/A'}</div>
            <div class="review-item"><strong>City:</strong> ${formData.city || 'N/A'}</div>
            <div class="review-item"><strong>Address:</strong> ${formData.address || 'N/A'}</div>
            <div class="review-item"><strong>Postal Code:</strong> ${formData.postal_code || 'N/A'}</div>
            <div class="review-item"><strong>Digital Address:</strong> ${formData.digital_address || 'N/A'}</div>
        `;
    }

    function getCreditInfoReview() {
        return `
            <div class="review-item"><strong>Credit Limit:</strong> ${formatCurrency(formData.credit_limit)}</div>
            <div class="review-item"><strong>Credit Rating:</strong> ${formData.credit_rating || 'N/A'}</div>
            <div class="review-item"><strong>Payment Terms:</strong> ${formData.payment_terms || 'N/A'}</div>
            <div class="review-item"><strong>Preferred Contact:</strong> ${formData.preferred_contact_method || 'N/A'}</div>
            <div class="review-item"><strong>Notes:</strong> ${formData.client_notes || 'N/A'}</div>
        `;
    }

    // --- Auto-save ---

    function setupAutoSave(modal) {
        // Auto-save every 30 seconds
        autoSaveInterval = setInterval(() => {
            autoSave(modal);
        }, 30000);
    }

    function autoSave(modal) {
        if (isSubmitting) return;

        collectFormData(modal);
        
        // Save to localStorage
        localStorage.setItem('clientFormDraft', JSON.stringify(formData));
        
        // Show auto-save indicator
        const indicator = modal.content.querySelector('#autoSaveIndicator');
        if (indicator) {
            indicator.style.display = 'flex';
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 2000);
        }
    }

    // --- Form Submission ---

    async function submitForm(modal) {
        if (isSubmitting) return;
        
        isSubmitting = true;
        const saveBtn = modal.content.querySelector('.btn-save');
        const originalText = saveBtn.textContent;
        
        try {
            // Update button state
            saveBtn.textContent = 'Creating Client...';
            saveBtn.disabled = true;

            // Validate all steps
            let allStepsValid = true;
            for (let step = 1; step <= totalSteps; step++) {
                const stepElement = modal.content.querySelector(`[data-wizard-step="${step}"]`);
                const requiredFields = stepElement.querySelectorAll('input[required], select[required], textarea[required]');
                
                requiredFields.forEach(field => {
                    if (!validateField(field)) {
                        allStepsValid = false;
                            }
                        });
                }

            if (!allStepsValid) {
                throw new Error('Please complete all required fields');
            }

            // Prepare form data for submission
            const submitData = {
                client_type: formData.client_type,
                company_name: formData.company_name,
                industry: formData.industry,
                client_description: formData.client_description,
                contact_person: formData.contact_person,
                contact_person_position: formData.contact_person_position,
                contact_phone: formData.contact_phone,
                contact_email: formData.contact_email,
                alternative_contact: formData.alternative_contact,
                website: formData.website,
                country: formData.country,
                region: formData.region,
                city: formData.city,
                address: formData.address,
                postal_code: formData.postal_code,
                postal_address: formData.postal_address,
                digital_address: formData.digital_address,
                location_notes: formData.location_notes,
                credit_limit: parseFloat(formData.credit_limit) || 0,
                credit_rating: formData.credit_rating,
                payment_terms: formData.payment_terms,
                preferred_contact_method: formData.preferred_contact_method,
                client_notes: formData.client_notes
            };

            // Submit to API
            const response = await fetch(`${window.baseUrl}/api/client_api.php?action=create_client`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(submitData)
            });

            const result = await response.json();

            if (result.success) {
                // Clear auto-save data
                localStorage.removeItem('clientFormDraft');
                
                // Close modal
                Modal.close();
                
                // Show success message
                Modal.success('Success', 'Client created successfully!');
                
                // Trigger refresh callback if available
                if (window.onClientCreated) {
                    window.onClientCreated(result.data);
                }
            } else {
                throw new Error(result.message || 'Failed to create client');
                }

            } catch (error) {
            console.error('Error creating client:', error);
            Modal.error('Error', error.message || 'Failed to create client');
        } finally {
            isSubmitting = false;
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        }
    }

    async function submitEditForm(modal, clientId) {
        if (isSubmitting) return;
        
        isSubmitting = true;
        const saveBtn = modal.content.querySelector('.btn-save');
        const originalText = saveBtn.textContent;
        
        try {
            // Update button state
            saveBtn.textContent = 'Updating Client...';
            saveBtn.disabled = true;

            // Collect form data
            collectFormData(modal);

            // Prepare update data (same structure as create)
            const updateData = {
                client_type: formData.client_type,
                company_name: formData.company_name,
                industry: formData.industry,
                client_description: formData.client_description,
                contact_person: formData.contact_person,
                contact_person_position: formData.contact_person_position,
                contact_phone: formData.contact_phone,
                contact_email: formData.contact_email,
                alternative_contact: formData.alternative_contact,
                website: formData.website,
                country: formData.country,
                region: formData.region,
                city: formData.city,
                address: formData.address,
                postal_code: formData.postal_code,
                postal_address: formData.postal_address,
                digital_address: formData.digital_address,
                location_notes: formData.location_notes,
                credit_limit: parseFloat(formData.credit_limit) || 0,
                credit_rating: formData.credit_rating,
                payment_terms: formData.payment_terms,
                preferred_contact_method: formData.preferred_contact_method,
                client_notes: formData.client_notes
            };

            // Submit to API
            const response = await fetch(`${window.baseUrl}/api/client_api.php?action=update_client&id=${clientId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(updateData)
            });

            const result = await response.json();

            if (result.success) {
                // Clear auto-save data
                localStorage.removeItem('clientFormDraft');
                
                // Close modal
                Modal.close();
                
                // Show success message
                Modal.success('Success', 'Client updated successfully!');
                
                // Trigger refresh callback if available
                if (window.onClientUpdated) {
                    window.onClientUpdated(result.data);
                }
                
                // Reload page to show updated data
                if (typeof loadClients === 'function') {
                    loadClients();
                } else {
                    window.location.reload();
                }
            } else {
                throw new Error(result.message || 'Failed to update client');
            }

        } catch (error) {
            console.error('Error updating client:', error);
            Modal.error('Error', error.message || 'Failed to update client');
        } finally {
            isSubmitting = false;
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        }
    }

    // --- Utility Functions ---

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function isValidPhone(phone) {
        const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
        return phoneRegex.test(phone);
    }

    function isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }

    function getClientTypeLabel(type) {
        const labels = {
            'individual': 'Individual',
            'company': 'Company',
            'government': 'Government',
            'ngo': 'NGO'
        };
        return labels[type] || type;
    }

    function formatCurrency(amount) {
        if (!amount || amount === '0' || amount === '') {
            return 'GHS 0.00';
        }
        return `GHS ${parseFloat(amount).toFixed(2)}`;
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (autoSaveInterval) {
            clearInterval(autoSaveInterval);
        }
    });

})();
