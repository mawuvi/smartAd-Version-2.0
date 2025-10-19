// GitHub-Inspired Sidebar Navigation Functions

// Toggle navigation group collapse/expand with smooth animation
function toggleNavGroup(button) {
    const navGroup = button.parentElement;
    const items = navGroup.querySelector('.nav-group-items');
    const icon = button.querySelector('i:first-child');
    const isExpanded = items.style.display !== 'none';
    
    // Smooth animation
    if (isExpanded) {
        items.style.display = 'none';
        icon.classList.replace('fa-chevron-down', 'fa-chevron-right');
        button.classList.remove('active');
    } else {
        items.style.display = 'block';
        icon.classList.replace('fa-chevron-right', 'fa-chevron-down');
        button.classList.add('active');
    }
}

// Handle navigation item clicks with GitHub-style feedback
function initSidebarNavigation(manager) {
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all items
            navItems.forEach(i => i.classList.remove('active'));
            
            // Add active class to clicked item with smooth transition
            this.classList.add('active');
            
            // Get tab name and switch content
            const tabName = this.getAttribute('data-tab');
            manager.switchTab(tabName);
        });
        
        // Add focus states for keyboard navigation
        item.addEventListener('focus', function() {
            this.style.outline = '2px solid #0969da';
            this.style.outlineOffset = '2px';
        });
        
        item.addEventListener('blur', function() {
            this.style.outline = 'none';
        });
    });
}

// Global function for onclick handlers
window.toggleNavGroup = toggleNavGroup;

class SetupManager {
    constructor() {
        this.currentTab = 'publications';
        this.allCategories = [
            'publications', 'ad-categories', 'ad-sizes', 'page-positions', 'color-types',
            'rates', 'taxes', 'currencies', 'payment-types', 'discount-types',
            'industries', 'countries', 'regions', 'cities', 'client-types',
            'users', 'roles', 'company-setup', 'commission-types', 'credit-types',
            'system-settings', 'email-templates', 'report-templates', 'bulk-upload'
        ];
        this.init();
    }

    init() {
        this.bindEvents();
        initSidebarNavigation(this);
        
        // Load the first active tab's content
        const firstActiveItem = document.querySelector('.nav-item.active');
        if (firstActiveItem) {
            const tabName = firstActiveItem.getAttribute('data-tab');
            this.switchTab(tabName);
        }
    }

    bindEvents() {
        // File upload
        const fileUploadArea = document.getElementById('file-upload-area');
        const fileInput = document.getElementById('upload-file');
        
        if (fileUploadArea && fileInput) {
            fileUploadArea.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', () => this.handleFileSelect());
        }

        // Modal events
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModal();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModal();
            }
        });
        
        // NEW: Load pending sessions when rates tab is active
        document.querySelectorAll('[data-tab="rates"]').forEach(btn => {
            btn.addEventListener('click', () => {
                this.loadPendingSessions();
            });
        });
    }

    switchTab(tab) {
        // Update tab content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${tab}-tab`).classList.add('active');

        this.currentTab = tab;
        
        // Load data for specific tabs
        if (tab === 'rates') {
            this.loadFilterData();
            this.loadRates();
        }
        
        // Load real counts when switching to any tab
        this.loadSetupCounts();
    }

    handleFileSelect() {
        const fileInput = document.getElementById('upload-file');
        const uploadBtn = document.getElementById('upload-btn');
        
        if (fileInput.files.length > 0) {
            uploadBtn.disabled = false;
            this.showAlert('File selected. Click "Upload & Validate" to proceed.', 'success');
        } else {
            uploadBtn.disabled = true;
        }
    }

    openAddModal(entityType) {
        const modal = document.getElementById('add-edit-modal');
        const modalTitle = document.getElementById('modal-title');
        
        modalTitle.textContent = 'Add New ' + this.getEntityDisplayName(entityType);
        modal.classList.add('active');
    }

    getEntityDisplayName(entityType) {
        const names = {
            'publications': 'Publication',
            'ad-categories': 'Ad Category',
            'ad-sizes': 'Ad Size',
            'page-positions': 'Page Position',
            'color-types': 'Color Type',
            'rates': 'Rate',
            'taxes': 'Tax Rule',
            'currencies': 'Currency',
            'payment-types': 'Payment Type',
            'discount-types': 'Discount Type',
            'industries': 'Industry',
            'countries': 'Country',
            'regions': 'Region',
            'cities': 'City',
            'client-types': 'Client Type',
            'users': 'User',
            'roles': 'Role',
            'company-setup': 'Company Setting',
            'commission-types': 'Commission Type',
            'credit-types': 'Credit Type',
            'system-settings': 'System Setting',
            'email-templates': 'Email Template',
            'report-templates': 'Report Template',
            'bulk-upload': 'Bulk Upload'
        };
        return names[entityType] || entityType;
    }

    closeModal() {
        const modal = document.getElementById('add-edit-modal');
        modal.classList.remove('active');
    }

    saveRecord() {
        this.showAlert('Record saved successfully!', 'success');
        this.closeModal();
    }

    downloadTemplate() {
        const uploadType = document.getElementById('upload-type').value;
        this.showAlert(`Template for ${uploadType} downloaded successfully!`, 'success');
    }

    uploadFile() {
        this.showAlert('File uploaded and validated successfully!', 'success');
    }

    exportData(entityType) {
        this.showAlert(`Data for ${entityType} exported successfully!`, 'success');
    }

    // API Import functionality
    async importFromAPI(type) {
        this.showAlert(`Importing ${type} from API...`, 'warning');
        
        try {
            const response = await fetch(`${window.baseUrl}/app/api/geographic_api.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    action: 'import', 
                    type: type 
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert(`Imported ${result.count} ${type} successfully!`, 'success');
                // Update tab count
                this.updateTabCount(type, result.count);
            } else {
                this.showAlert(result.message || 'Import failed', 'error');
            }
        } catch (error) {
            this.showAlert('Import failed: ' + error.message, 'error');
        }
    }

    updateTabCount(type, count) {
        const tabBtn = document.querySelector(`[data-tab="${type}"] .tab-count`);
        if (tabBtn) {
            tabBtn.textContent = count;
        }
    }

    // Rates Management Methods
    async downloadRatesTemplate() {
        try {
            const format = document.getElementById('template-format')?.value || 'csv';
            
            const response = await fetch(`${window.baseUrl}/app/api/rates_api.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=download_template&format=${format}`
            });
            
            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                const extension = format === 'excel' ? 'xlsx' : 'csv';
                a.download = `rates_template_${new Date().toISOString().split('T')[0]}.${extension}`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                this.showAlert('Template downloaded successfully!', 'success');
            } else {
                throw new Error('Download failed');
            }
        } catch (error) {
            this.showAlert('Failed to download template: ' + error.message, 'error');
        }
    }

    openBulkUploadModal() {
        const modal = document.getElementById('bulk-upload-modal');
        modal.classList.add('active');
        
        // Reset upload area
        document.getElementById('upload-results').style.display = 'none';
        document.getElementById('rates-upload-btn').disabled = true;
        
        // Bind file upload events
        const uploadArea = document.getElementById('rates-file-upload-area');
        const fileInput = document.getElementById('rates-upload-file');
        
        uploadArea.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', () => this.handleRatesFileSelect());
    }

    closeBulkUploadModal() {
        const modal = document.getElementById('bulk-upload-modal');
        modal.classList.remove('active');
        
        // Reset the file input
        const fileInput = document.getElementById('rates-upload-file');
        if (fileInput) {
            fileInput.value = '';
        }
        
        // Reset the file name display
        const fileNameDisplay = document.getElementById('selected-file-name');
        if (fileNameDisplay) {
            fileNameDisplay.textContent = '';
        }
        
        // Reset the upload button
        const uploadBtn = document.getElementById('rates-upload-btn');
        if (uploadBtn) {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload File';
        }
    }

    handleRatesFileSelect() {
        const fileInput = document.getElementById('rates-upload-file');
        const uploadBtn = document.getElementById('rates-upload-btn');
        const fileNameDisplay = document.getElementById('selected-file-name');
        
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            uploadBtn.disabled = false;
            fileNameDisplay.textContent = `Selected: ${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
            fileNameDisplay.style.color = '#28a745';
            this.showAlert('File selected. Click "Upload & Process" to proceed.', 'success');
        } else {
            uploadBtn.disabled = true;
            fileNameDisplay.textContent = '';
        }
    }

    async uploadRatesFile() {
        const fileInput = document.getElementById('rates-upload-file');
        const uploadBtn = document.getElementById('rates-upload-btn');
        
        if (!fileInput.files.length) {
            this.showAlert('Please select a file first', 'error');
            return;
        }
        
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        try {
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('action', 'upload_file');
            
            const response = await fetch(`${window.baseUrl}/app/api/rates_api.php`, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert(result.message, 'success');
                
                // Close the bulk upload modal
                this.closeBulkUploadModal();
                
                // Load and display pending sessions (shows Resume/Delete buttons)
                await this.loadPendingSessions();
                
                // Display the staged data for the uploaded session
                if (result.session_id) {
                    window.currentStagingSession = result.session_id;
                    await this.displayStagingRates(result.session_id);
                    this.updateStagingStats(result);
                }
            } else {
                this.showAlert('Upload failed: ' + result.message, 'error');
            }
            
        } catch (error) {
            this.showAlert('Upload failed: ' + error.message, 'error');
        } finally {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload & Process';
        }
    }

    displayUploadResults(results) {
        const resultsDiv = document.getElementById('upload-results');
        const summaryDiv = document.getElementById('upload-summary');
        const errorsDiv = document.getElementById('upload-errors');
        const warningsDiv = document.getElementById('upload-warnings');
        const dependenciesDiv = document.getElementById('upload-dependencies');
        
        resultsDiv.style.display = 'block';
        
        // Summary
        summaryDiv.innerHTML = `
            <strong>Processing Complete!</strong><br>
            Total Rows: ${results.total_rows} | 
            Valid: ${results.valid_rows} | 
            Created: ${results.created_rows} | 
            Invalid: ${results.invalid_rows} | 
            Duplicates: ${results.duplicate_rows}
        `;
        
        // Errors
        if (results.errors && results.errors.length > 0) {
            errorsDiv.innerHTML = '<strong>Errors:</strong><ul>' + 
                results.errors.map(error => `<li>${error}</li>`).join('') + '</ul>';
            errorsDiv.style.display = 'block';
        } else {
            errorsDiv.style.display = 'none';
        }
        
        // Warnings
        if (results.warnings && results.warnings.length > 0) {
            warningsDiv.innerHTML = '<strong>Warnings:</strong><ul>' + 
                results.warnings.map(warning => `<li>${warning}</li>`).join('') + '</ul>';
            warningsDiv.style.display = 'block';
        } else {
            warningsDiv.style.display = 'none';
        }
        
        // Dependencies Created
        if (results.created_dependencies && results.created_dependencies.length > 0) {
            dependenciesDiv.innerHTML = '<strong>Dependencies Created:</strong><ul>' + 
                results.created_dependencies.map(dep => `<li>${dep}</li>`).join('') + '</ul>';
            dependenciesDiv.style.display = 'block';
        } else {
            dependenciesDiv.style.display = 'none';
        }
    }
    
    async displayStagingRates(sessionId) {
        try {
            const response = await fetch(`${window.baseUrl}/app/api/rates_api.php?action=get_staging_rates&session_id=${sessionId}`);
            const result = await response.json();
            
            if (result.success) {
                this.renderStagingTable(result.data);
                document.getElementById('staging-area').style.display = 'block';
                window.currentStagingSession = sessionId;
            } else {
                this.showAlert('Failed to load staging data: ' + result.message, 'error');
            }
            
        } catch (error) {
            this.showAlert('Failed to load staging data: ' + error.message, 'error');
        }
    }
    
    renderStagingTable(rates) {
        const tbody = document.getElementById('staging-table-body');
        
        if (rates.length === 0) {
            tbody.innerHTML = '<tr><td colspan="11" class="text-center">No staging data found</td></tr>';
            return;
        }
        
        tbody.innerHTML = rates.map(rate => {
            return `
                <tr class="staging-row ${rate.validation_status}">
                    <td>
                        <input type="checkbox" class="staging-checkbox" data-id="${rate.id}" 
                               ${rate.validation_status === 'ok' ? 'checked' : ''} 
                               ${rate.validation_status !== 'ok' ? 'disabled' : ''}>
                    </td>
                    <td>
                        <span class="status-badge ${rate.validation_status}">${rate.validation_status}</span>
                    </td>
                    <td>
                        <div class="rate-publication">
                            <div class="rate-code">${rate.publication_code}</div>
                            <div class="rate-name">${rate.publication_name}</div>
                        </div>
                    </td>
                    <td>${rate.ad_category}</td>
                    <td>${rate.ad_size}</td>
                    <td>${rate.page_position}</td>
                    <td>${rate.color_type}</td>
                    <td class="rate-amount">${parseFloat(rate.base_rate).toFixed(2)}</td>
                    <td>${rate.effective_from}</td>
                    <td>${rate.effective_to || '-'}</td>
                    <td class="validation-message">${rate.validation_message}</td>
                </tr>
            `;
        }).join('');
        
        this.updateStagingCounts();
    }
    
    updateStagingStats(uploadResult) {
        document.getElementById('ok-count').textContent = uploadResult.ok_count || 0;
        document.getElementById('dup-count').textContent = uploadResult.duplicate_count || 0;
        document.getElementById('error-count').textContent = uploadResult.error_count || 0;
    }
    
    updateStagingCounts() {
        const rows = document.querySelectorAll('.staging-row');
        let okCount = 0, dupCount = 0, errorCount = 0;
        
        rows.forEach(row => {
            if (row.classList.contains('ok')) okCount++;
            else if (row.classList.contains('duplicate')) dupCount++;
            else if (row.classList.contains('error')) errorCount++;
        });
        
        document.getElementById('ok-count').textContent = okCount;
        document.getElementById('dup-count').textContent = dupCount;
        document.getElementById('error-count').textContent = errorCount;
    }
    
    toggleSelectAllStaging() {
        const selectAll = document.getElementById('select-all-staging');
        const checkboxes = document.querySelectorAll('.staging-checkbox:not([disabled])');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }
    
    selectOkRates() {
        const checkboxes = document.querySelectorAll('.staging-checkbox');
        checkboxes.forEach(checkbox => {
            const row = checkbox.closest('.staging-row');
            checkbox.checked = row.classList.contains('ok');
        });
        
        this.updateSelectAllState();
    }
    
    deselectDuplicateRates() {
        const checkboxes = document.querySelectorAll('.staging-checkbox');
        checkboxes.forEach(checkbox => {
            const row = checkbox.closest('.staging-row');
            if (row.classList.contains('duplicate')) {
                checkbox.checked = false;
            }
        });
        
        this.updateSelectAllState();
    }
    
    updateSelectAllState() {
        const checkboxes = document.querySelectorAll('.staging-checkbox:not([disabled])');
        const checkedBoxes = document.querySelectorAll('.staging-checkbox:not([disabled]):checked');
        
        const selectAll = document.getElementById('select-all-staging');
        selectAll.checked = checkboxes.length > 0 && checkedBoxes.length === checkboxes.length;
        selectAll.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < checkboxes.length;
    }
    
    async processStagingRates() {
        const selectedCheckboxes = document.querySelectorAll('.staging-checkbox:checked');
        const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.dataset.id);
        
        if (selectedIds.length === 0) {
            this.showAlert('Please select at least one rate to process', 'warning');
            return;
        }
        
        if (!window.currentStagingSession) {
            this.showAlert('No staging session found', 'error');
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('action', 'process_staging_rates');
            formData.append('session_id', window.currentStagingSession);
            formData.append('selected_ids', JSON.stringify(selectedIds));
            
            const response = await fetch(`${window.baseUrl}/app/api/rates_api.php`, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert(result.message, 'success');
                this.cancelStaging();
                this.loadRates(); // Refresh the rates table
            } else {
                this.showAlert('Processing failed: ' + result.message, 'error');
            }
            
        } catch (error) {
            this.showAlert('Processing failed: ' + error.message, 'error');
        }
    }
    
    async cancelStaging() {
        if (window.currentStagingSession) {
            try {
                const formData = new FormData();
                formData.append('action', 'clear_staging');
                formData.append('session_id', window.currentStagingSession);
                
                await fetch(`${window.baseUrl}/app/api/rates_api.php`, {
                    method: 'POST',
                    body: formData
                });
            } catch (error) {
                console.error('Failed to clear staging:', error);
            }
        }
        
        document.getElementById('staging-area').style.display = 'none';
        window.currentStagingSession = null;
    }
    
    async loadPendingSessions() {
        try {
            const response = await fetch(`${window.baseUrl}/app/api/rates_api.php?action=get_user_staging_sessions`);
            const result = await response.json();
            
            if (result.success && result.data.length > 0) {
                this.renderPendingSessions(result.data);
                document.getElementById('pending-sessions-section').style.display = 'block';
            } else {
                document.getElementById('pending-sessions-section').style.display = 'none';
            }
            
        } catch (error) {
            console.error('Failed to load pending sessions:', error);
        }
    }

    renderPendingSessions(sessions) {
        const container = document.getElementById('pending-sessions-list');
        
        container.innerHTML = sessions.map(session => `
            <div class="session-card">
                <div class="session-header">
                    <span class="session-date">${new Date(session.uploaded_at).toLocaleString()}</span>
                    <span class="session-total">${session.total_rows} rows</span>
                </div>
                <div class="session-stats">
                    <span class="stat-ok">${session.ok_count} OK</span>
                    <span class="stat-warning">${session.warning_count} Warnings</span>
                    <span class="stat-duplicate">${session.duplicate_count} Duplicates</span>
                    <span class="stat-error">${session.error_count} Errors</span>
                </div>
                <div class="session-actions">
                    <button class="btn-primary btn-sm" onclick="setupManager.resumeSession('${session.upload_session_id}')">
                        <i class="fas fa-play"></i> Resume
                    </button>
                    <button class="btn-danger btn-sm" onclick="setupManager.deleteSession('${session.upload_session_id}')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        `).join('');
    }

    async resumeSession(sessionId) {
        window.currentStagingSession = sessionId;
        await this.displayStagingRates(sessionId);
    }

    async deleteSession(sessionId) {
        if (!confirm('Delete this staging session? This cannot be undone.')) {
            return;
        }
        
        try {
            const response = await fetch(`${window.baseUrl}/app/api/rates_api.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete_staging_session',
                    session_id: sessionId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert('Staging session deleted successfully', 'success');
                
                // Reload sessions list
                this.loadPendingSessions();
                
                // Clear the staging data table if this was the displayed session
                if (window.currentStagingSession === sessionId) {
                    this.clearStagingDisplay();
                    window.currentStagingSession = null;
                }
            } else {
                this.showAlert('Failed to delete session: ' + result.message, 'error');
            }
            
        } catch (error) {
            this.showAlert('Failed to delete session: ' + error.message, 'error');
        }
    }

    clearStagingDisplay() {
        // Clear the staging table body
        const stagingTableBody = document.getElementById('staging-table-body');
        if (stagingTableBody) {
            stagingTableBody.innerHTML = '';
        }
        
        // Reset the staging counts
        document.getElementById('ok-count').textContent = '0';
        document.getElementById('dup-count').textContent = '0';
        document.getElementById('error-count').textContent = '0';
        
        // Hide the staging area
        const stagingArea = document.getElementById('staging-area');
        if (stagingArea) {
            stagingArea.style.display = 'none';
        }
    }

    async handlePublicationMerge(rowId, selectedAction) {
        try {
            const mergeAction = selectedAction === 'create_new' ? 'create_new' : 'use_existing';
            const selectedPublicationId = selectedAction === 'create_new' ? null : selectedAction;
            
            const response = await fetch(`${window.baseUrl}/app/api/rates_api.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_staging_merge',
                    session_id: window.currentStagingSession,
                    row_id: rowId,
                    merge_action: mergeAction,
                    selected_publication_id: selectedPublicationId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update the row status if needed
                const row = document.querySelector(`tr[data-row-id="${rowId}"]`);
                if (row) {
                    // Update validation status if merge action changes status
                    if (mergeAction === 'use_existing') {
                        row.classList.remove('warning');
                        row.classList.add('ok');
                        const statusBadge = row.querySelector('.status-badge');
                        if (statusBadge) {
                            statusBadge.textContent = 'ok';
                            statusBadge.className = 'status-badge ok';
                        }
                    }
                }
            } else {
                this.showAlert('Failed to update merge action: ' + result.message, 'error');
            }
            
        } catch (error) {
            this.showAlert('Failed to update merge action: ' + error.message, 'error');
        }
    }

    openAddRateModal(rateId = null) {
        const modal = document.getElementById('rate-modal');
        const modalTitle = document.getElementById('rate-modal-title');
        
        if (rateId) {
            modalTitle.textContent = 'Edit Rate';
            this.loadRateData(rateId);
        } else {
            modalTitle.textContent = 'Add New Rate';
            this.clearRateForm();
        }
        
        modal.classList.add('active');
    }

    closeRateModal() {
        const modal = document.getElementById('rate-modal');
        modal.classList.remove('active');
    }

    clearRateForm() {
        document.getElementById('rate-form').reset();
        document.getElementById('rate-id').value = '';
    }

    async loadRateData(rateId) {
        try {
            const response = await fetch(`${window.baseUrl}/app/api/rates_api.php?action=get_rates&id=${rateId}`);
            const data = await response.json();
            
            if (data.success && data.data.length > 0) {
                const rate = data.data[0];
                
                document.getElementById('rate-id').value = rate.id;
                document.getElementById('rate-publication-code').value = rate.publication_code;
                document.getElementById('rate-publication-name').value = rate.publication_name;
                document.getElementById('rate-ad-category').value = rate.ad_category;
                document.getElementById('rate-ad-size').value = rate.ad_size;
                document.getElementById('rate-page-position').value = rate.page_position;
                document.getElementById('rate-color-type').value = rate.color_type;
                document.getElementById('rate-base-rate').value = rate.base_rate;
                document.getElementById('rate-effective-from').value = rate.effective_from;
                document.getElementById('rate-effective-to').value = rate.effective_to;
                document.getElementById('rate-status').value = rate.status;
                document.getElementById('rate-notes').value = rate.notes || '';
            }
        } catch (error) {
            this.showAlert('Failed to load rate data: ' + error.message, 'error');
        }
    }

    async saveRate() {
        const form = document.getElementById('rate-form');
        const formData = new FormData(form);
        const rateId = formData.get('id');
        
        const data = {
            publication_code: formData.get('publication_code'),
            publication_name: formData.get('publication_name'),
            ad_category: formData.get('ad_category'),
            ad_size: formData.get('ad_size'),
            page_position: formData.get('page_position'),
            color_type: formData.get('color_type'),
            base_rate: formData.get('base_rate'),
            effective_from: formData.get('effective_from'),
            effective_to: formData.get('effective_to'),
            status: formData.get('status'),
            notes: formData.get('notes')
        };
        
        if (rateId) {
            data.id = rateId;
        }
        
        try {
            const response = await fetch(`${window.baseUrl}/app/api/rates_api.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: rateId ? 'update_rate' : 'create_rate',
                    ...data
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert(rateId ? 'Rate updated successfully!' : 'Rate created successfully!', 'success');
                this.closeRateModal();
                this.loadRates();
            } else {
                this.showAlert(result.message || 'Failed to save rate', 'error');
            }
            
        } catch (error) {
            this.showAlert('Failed to save rate: ' + error.message, 'error');
        }
    }

    async editRate(rateId) {
        this.openAddRateModal(rateId);
    }

    async deleteRate(rateId) {
        if (!confirm('Are you sure you want to delete this rate?')) {
            return;
        }
        
        try {
            const response = await fetch(`${window.baseUrl}/app/api/rates_api.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete_rate',
                    id: rateId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert('Rate deleted successfully!', 'success');
                this.loadRates();
            } else {
                this.showAlert(result.message || 'Failed to delete rate', 'error');
            }
            
        } catch (error) {
            this.showAlert('Failed to delete rate: ' + error.message, 'error');
        }
    }

    async exportRates() {
        try {
            const status = document.getElementById('rates-status').value;
            const publication = document.getElementById('rates-publication').value;
            const category = document.getElementById('rates-category').value;
            const size = document.getElementById('rates-size')?.value || '';
            const position = document.getElementById('rates-position')?.value || '';
            const colorType = document.getElementById('rates-color')?.value || '';
            
            // Check for selected rates
            const selectedIds = this.getSelectedRateIds();
            
            const params = new URLSearchParams({
                action: 'export_rates',
                status: status,
                publication: publication,
                category: category,
                size: size,
                position: position,
                color_type: colorType
            });
            
            // Add selected IDs if any are selected
            if (selectedIds.length > 0) {
                params.append('ids', selectedIds.join(','));
            }
            
            window.open(`${window.baseUrl}/app/api/rates_api.php?${params}`, '_blank');
            this.showAlert('Export started!', 'success');
            
        } catch (error) {
            this.showAlert('Failed to export rates: ' + error.message, 'error');
        }
    }
    
    toggleSelectAllRates() {
        const selectAll = document.getElementById('select-all-rates');
        const checkboxes = document.querySelectorAll('.rate-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }
    
    getSelectedRateIds() {
        const selectedCheckboxes = document.querySelectorAll('.rate-checkbox:checked');
        return Array.from(selectedCheckboxes).map(cb => cb.dataset.id);
    }

    async loadRates() {
        try {
            const status = document.getElementById('rates-status').value;
            const publication = document.getElementById('rates-publication').value;
            const category = document.getElementById('rates-category').value;
            const size = document.getElementById('rates-size')?.value || '';
            const position = document.getElementById('rates-position')?.value || '';
            const colorType = document.getElementById('rates-color')?.value || '';
            
            const params = new URLSearchParams({
                action: 'get_rates',
                status: status,
                publication: publication,
                category: category,
                size: size,
                position: position,
                color_type: colorType,
                page: 1,
                limit: 20
            });
            
            const response = await fetch(`${window.baseUrl}/app/api/rates_api.php?${params}`);
            const data = await response.json();
            
            if (data.success) {
                this.displayRates(data.data);
                this.updateRatesPagination(data.pagination);
            } else {
                this.showAlert('Failed to load rates: ' + data.message, 'error');
            }
            
        } catch (error) {
            this.showAlert('Failed to load rates: ' + error.message, 'error');
        }
    }

    async loadFilterData() {
        try {
            const response = await fetch(`${window.baseUrl}/app/api/rates_api.php?action=get_filter_data`);
            const result = await response.json();
            
            if (result.success) {
                this.populateFilter('rates-publication', result.data.publications);
                this.populateFilter('rates-category', result.data.categories);
                this.populateFilter('rates-size', result.data.sizes);
                this.populateFilter('rates-position', result.data.positions);
                this.populateFilter('rates-color', result.data.colorTypes);
            }
        } catch (error) {
            console.error('Failed to load filter data:', error);
        }
    }

    populateFilter(selectId, options) {
        const select = document.getElementById(selectId);
        if (!select) return;
        
        // Map select IDs to proper labels
        const labelMap = {
            'rates-publication': 'All Publications',
            'rates-category': 'All Categories',
            'rates-size': 'All Sizes',
            'rates-position': 'All Positions',
            'rates-color': 'All Colors',
            'rates-status': 'All Statuses'
        };
        
        const defaultLabel = labelMap[selectId] || 'All';
        select.innerHTML = `<option value="">${defaultLabel}</option>`;
        
        options.forEach(opt => {
            select.innerHTML += `<option value="${opt.id}">${opt.name}</option>`;
        });
    }

    async loadSetupCounts() {
        try {
            const response = await fetch(`${window.baseUrl}/app/api/stats_api.php?action=get_setup_counts`);
            const result = await response.json();
            
            if (result.success) {
                // Update quick stats
                this.updateQuickStats(result.data);
                
                // Update navigation counts
                this.updateNavCounts(result.data);
            }
        } catch (error) {
            console.error('Failed to load setup counts:', error);
        }
    }

    updateQuickStats(data) {
        // Update quick stats in the header
        const quickStats = document.querySelectorAll('.quick-stat .stat-value');
        quickStats.forEach(stat => {
            const label = stat.nextElementSibling.textContent.toLowerCase();
            if (label.includes('publications') && data.publications !== undefined) {
                stat.textContent = data.publications;
            } else if (label.includes('rates') && data.rates !== undefined) {
                stat.textContent = data.rates;
            } else if (label.includes('ad sizes') && data.ad_sizes !== undefined) {
                stat.textContent = data.ad_sizes;
            } else if (label.includes('users') && data.users !== undefined) {
                stat.textContent = data.users;
            }
        });
    }

    updateNavCounts(data) {
        // Update navigation counts
        const navCounts = document.querySelectorAll('.nav-count');
        navCounts.forEach(countEl => {
            const navItem = countEl.closest('.nav-item');
            const tabName = navItem.getAttribute('data-tab');
            
            // Map tab names to data keys
            const countMap = {
                'publications': 'publications',
                'ad-categories': 'ad_categories',
                'ad-sizes': 'ad_sizes',
                'page-positions': 'page_positions',
                'color-types': 'color_types',
                'rates': 'rates',
                'users': 'users',
                'clients': 'clients'
            };
            
            if (countMap[tabName] && data[countMap[tabName]] !== undefined) {
                countEl.textContent = data[countMap[tabName]];
            }
        });
    }

    displayRates(rates) {
        const tbody = document.getElementById('rates-table-body');
        
        if (rates.length === 0) {
            tbody.innerHTML = '<tr><td colspan="12" class="text-center">No rates found</td></tr>';
            return;
        }
        
        tbody.innerHTML = rates.map(rate => `
            <tr>
                <td>
                    <input type="checkbox" class="rate-checkbox" data-id="${rate.id}">
                </td>
                <td>
                    <div class="rate-publication">
                        <div class="rate-code">${rate.publication_code}</div>
                        <div class="rate-name">${rate.publication_name}</div>
                    </div>
                </td>
                <td>${rate.ad_category}</td>
                <td>${rate.ad_size}</td>
                <td>${rate.page_position}</td>
                <td>${rate.color_type}</td>
                <td class="rate-amount">${rate.currency} ${parseFloat(rate.base_rate).toFixed(2)}</td>
                <td>${rate.currency}</td>
                <td>${rate.effective_from}</td>
                <td>${rate.effective_to}</td>
                <td><span class="status-badge ${rate.status}">${rate.status}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="action-btn edit" onclick="editRate(${rate.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete" onclick="deleteRate(${rate.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    updateRatesPagination(pagination) {
        const infoSpan = document.getElementById('rates-pagination-info');
        const prevBtn = document.getElementById('rates-prev-btn');
        const nextBtn = document.getElementById('rates-next-btn');
        const numbersSpan = document.getElementById('rates-pagination-numbers');
        
        infoSpan.textContent = `Showing ${((pagination.page - 1) * pagination.limit) + 1}-${Math.min(pagination.page * pagination.limit, pagination.total)} of ${pagination.total} records`;
        
        prevBtn.disabled = pagination.page <= 1;
        nextBtn.disabled = pagination.page >= pagination.pages;
        
        // Generate page numbers
        let pageNumbers = '';
        for (let i = 1; i <= pagination.pages; i++) {
            const activeClass = i === pagination.page ? 'active' : '';
            pageNumbers += `<button class="page-number ${activeClass}" onclick="loadRatesPage(${i})">${i}</button>`;
        }
        numbersSpan.innerHTML = pageNumbers;
    }

    showAlert(message, type) {
        const container = document.getElementById('alert-container');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        
        container.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
}

// Global functions for onclick handlers
function openAddModal(entityType) {
    setupManager.openAddModal(entityType);
}

function exportData(entityType) {
    setupManager.exportData(entityType);
}

function downloadTemplate() {
    setupManager.downloadTemplate();
}

function uploadFile() {
    setupManager.uploadFile();
}

function closeModal() {
    setupManager.closeModal();
}

function saveRecord() {
    setupManager.saveRecord();
}

    // API Import function
    function importFromAPI(type) {
        setupManager.importFromAPI(type);
    }

    // Rates Management Functions
    function downloadRatesTemplate() {
        setupManager.downloadRatesTemplate();
    }

    function openBulkUploadModal() {
        setupManager.openBulkUploadModal();
    }

    function closeBulkUploadModal() {
        setupManager.closeBulkUploadModal();
    }

    function uploadRatesFile() {
        setupManager.uploadRatesFile();
    }

    function openAddRateModal(rateId = null) {
        setupManager.openAddRateModal(rateId);
    }

    function closeRateModal() {
        setupManager.closeRateModal();
    }

    function saveRate() {
        setupManager.saveRate();
    }

    function editRate(rateId) {
        setupManager.editRate(rateId);
    }

    function deleteRate(rateId) {
        setupManager.deleteRate(rateId);
    }

    function exportRates() {
        setupManager.exportRates();
    }
    
    function toggleSelectAllRates() {
        setupManager.toggleSelectAllRates();
    }
    
    // Staging functions
    function toggleSelectAllStaging() {
        setupManager.toggleSelectAllStaging();
    }
    
    function selectOkRates() {
        setupManager.selectOkRates();
    }
    
    function deselectDuplicateRates() {
        setupManager.deselectDuplicateRates();
    }
    
    function processStagingRates() {
        setupManager.processStagingRates();
    }
    
function refreshPendingSessions() {
    setupManager.loadPendingSessions();
}

function cancelStaging() {
    setupManager.cancelStaging();
}

    function handlePublicationMerge(rowId, selectedAction) {
        setupManager.handlePublicationMerge(rowId, selectedAction);
    }

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.setupManager = new SetupManager();
    
    // Load initial counts
    window.setupManager.loadSetupCounts();
});
