/**
 * Rate Calculator Module - Standalone Rate Calculator
 * Location: public/js/modules/rateCalculator.js
 * Features: Two-column modal, cascading dropdowns, dynamic tax calculation, convert to booking
 */

class RateCalculator {
    constructor() {
        this.currentCalculation = null;
        this.referenceData = null;
        this.isCalculating = false;
        this.modal = null;
        
        this.init();
    }

    init() {
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                // Add a small delay to ensure Modal is initialized
                setTimeout(() => this.setupEventListeners(), 100);
            });
        } else {
            // Add a small delay to ensure Modal is initialized
            setTimeout(() => this.setupEventListeners(), 100);
        }
    }

    setupEventListeners() {
        // Global calculator trigger (can be added to any page)
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="open-calculator"]')) {
                e.preventDefault();
                this.open();
            }
        });
    }

    /**
     * Open the rate calculator modal
     */
    async open() {
        try {
            // Check if Modal is available
            if (!window.Modal) {
                throw new Error('Modal system not available. Please ensure modal.js is loaded.');
            }

            // Load reference data first (API or fallback)
            await this.loadReferenceData();
            
            // Create modal content AFTER data is loaded
            const modalContent = this.createModalContent();
            
            // Open modal using existing Modal system
            this.modal = await window.Modal.open(modalContent, {
                title: 'Rate Calculator',
                size: 'wide',
                className: 'rate-calculator-modal',
                buttons: [
                    {
                        text: 'Reset',
                        action: 'reset',
                        class: 'btn-secondary',
                        handler: () => this.resetCalculator()
                    },
                    {
                        text: 'Convert to Booking',
                        action: 'convert',
                        class: 'btn-success',
                        handler: () => this.convertToBooking()
                    },
                    {
                        text: 'Close',
                        action: 'close',
                        class: 'btn-primary'
                    }
                ]
            });

            // Setup calculator functionality
            this.setupCalculatorFunctionality();
            
            // Ensure modal is fully rendered before populating dropdowns
            setTimeout(() => {
                console.log('About to populate dropdowns, checking if elements exist...');
                const publicationSelect = document.getElementById('calc_publication');
                console.log('Publication select element:', publicationSelect);
                
                if (publicationSelect) {
                    this.populateDropdowns();
                } else {
                    console.error('calc_publication element not found, retrying in 200ms...');
                    setTimeout(() => {
                        this.populateDropdowns();
                    }, 200);
                }
            }, 50);
            
        } catch (error) {
            console.error('Error opening rate calculator:', error);
            window.Modal.error('Error', 'Failed to open rate calculator. Please try again.');
        }
    }

    /**
     * Create the two-column modal content
     */
    createModalContent() {
        return `
            <div class="calculator-grid">
                <!-- Left Column: Configuration -->
                <div class="calculator-config">
                    <form id="calculatorForm">
                        <div class="form-group">
                            <label for="calc_publication">Publication *</label>
                            <select id="calc_publication" name="publication_id" class="form-control" required>
                                <option value="">Select publication...</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="calc_colorType">Color Type *</label>
                            <select id="calc_colorType" name="color_type_id" class="form-control" required disabled>
                                <option value="">Select color type...</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="calc_category">Ad Category *</label>
                            <select id="calc_category" name="ad_category_id" class="form-control" required disabled>
                                <option value="">Select category...</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="calc_size">Ad Size *</label>
                            <select id="calc_size" name="ad_size_id" class="form-control" required disabled>
                                <option value="">Select size...</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="calc_position">Page Position *</label>
                            <select id="calc_position" name="page_position_id" class="form-control" required disabled>
                                <option value="">Select position...</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="calc_insertions">Insertions</label>
                            <input type="number" id="calc_insertions" name="insertions" class="form-control" value="1" min="1" required>
                        </div>

                        <div class="form-group">
                            <label for="calc_discount_amount">Discount Amount (Optional)</label>
                            <input type="number" id="calc_discount_amount" name="discount_amount" class="form-control" value="0" min="0" step="0.01">
                        </div>

                        <div class="form-group">
                            <label for="calc_discount_type">Discount Type</label>
                            <select id="calc_discount_type" name="discount_type" class="form-control">
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Results -->
                <div class="calculator-results">
                    <div class="result-section">
                        <h4>Base Calculation</h4>
                        <div class="result-row">
                            <span>Base Rate:</span>
                            <span id="calc_base_rate">GHS 0.00</span>
                        </div>
                        <div class="result-row">
                            <span>Insertions:</span>
                            <span id="calc_insertions_display">1</span>
                        </div>
                        <div class="result-row highlight">
                            <span>Base Subtotal:</span>
                            <span id="calc_base_subtotal">GHS 0.00</span>
                        </div>
                    </div>

                    <div class="result-section" id="discount_section" style="display: none;">
                        <h4>Discount Applied</h4>
                        <div class="result-row">
                            <span>Discount Amount:</span>
                            <span id="calc_discount_amount_display">GHS 0.00</span>
                        </div>
                        <div class="result-row highlight">
                            <span>Subtotal After Discount:</span>
                            <span id="calc_subtotal_after_discount">GHS 0.00</span>
                        </div>
                    </div>

                    <div class="result-section">
                        <h4>Tax Calculations</h4>
                        <div id="calc_tax_calculations">
                            <p class="no-data">No taxes calculated</p>
                        </div>
                        <div class="result-row highlight">
                            <span>Total Tax:</span>
                            <span id="calc_total_tax">GHS 0.00</span>
                        </div>
                    </div>

                    <div class="result-section total-section">
                        <div class="result-row total">
                            <span>Final Total:</span>
                            <span id="calc_final_total">GHS 0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Setup calculator functionality after modal opens
     */
    setupCalculatorFunctionality() {
        if (!this.modal) return;

        // Setup dropdown dependencies
        this.setupDropdownDependencies();
        
        // Setup form validation
        this.setupFormValidation();
        
        // Initial state
        this.clearResults();
    }

    /**
     * Load reference data from API
     */
    async loadReferenceData() {
        try {
            console.log('Loading reference data from:', `${window.baseUrl}/api/calculator_api.php?action=get_reference_data`);
            
            // Try to load from API first
            const response = await fetch(`${window.baseUrl}/api/calculator_api.php?action=get_reference_data`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include'
            });
            
            console.log('Response status:', response.status);
            
            if (response.ok) {
                const result = await response.json();
                console.log('Reference data result:', result);
                console.log('Publications from API:', result.data?.publications);
                
                if (result.success && result.data) {
                    this.referenceData = result.data;
                    console.log('Reference data loaded from API:', this.referenceData);
                    console.log('Publications array:', this.referenceData.publications);
                    console.log('Publications length:', this.referenceData.publications?.length);
                    return;
                } else {
                    console.error('API returned success=false or no data:', result);
                }
            } else {
                console.error('API response not OK:', response.status, response.statusText);
            }
            
        } catch (error) {
            console.error('Error loading reference data:', error);
        }
        
        // If API fails or returns no data, use fallback data
        console.log('Using fallback data...');
        this.useFallbackData();
    }

    /**
     * Use fallback reference data
     */
    useFallbackData() {
        this.referenceData = {
            publications: [
                { id: 1, name: 'Ghanaian Times', code: 'GT' },
                { id: 2, name: 'Spectator', code: 'SP' },
                { id: 3, name: 'Daily Graphic', code: 'DG' },
                { id: 4, name: 'Business & Financial Times', code: 'BFT' }
            ],
            colorTypes: [
                { id: 1, name: 'Black & White', code: 'BW' },
                { id: 2, name: 'Full Color', code: 'FC' },
                { id: 3, name: 'Spot Color', code: 'SC' },
                { id: 4, name: 'Two Color', code: 'TC' }
            ],
            categories: [
                { id: 1, name: 'Display Advertisement', code: 'DIS' },
                { id: 2, name: 'Classified Advertisement', code: 'CLS' },
                { id: 3, name: 'Recruitment Advertisement', code: 'REC' },
                { id: 4, name: 'Property Advertisement', code: 'PROP' },
                { id: 5, name: 'Automotive Advertisement', code: 'AUTO' }
            ],
            sizes: [
                { id: 1, name: 'Quarter Page', code: 'QP' },
                { id: 2, name: 'Half Page', code: 'HP' },
                { id: 3, name: 'Full Page', code: 'FP' },
                { id: 4, name: 'Double Page Spread', code: 'DPS' },
                { id: 5, name: 'Eighth Page', code: 'EP' }
            ],
            positions: [
                { id: 1, name: 'Front Page', code: 'FP' },
                { id: 2, name: 'Back Page', code: 'BP' },
                { id: 3, name: 'Inside Page', code: 'IP' },
                { id: 4, name: 'Center Spread', code: 'CS' },
                { id: 5, name: 'Sports Page', code: 'SP' }
            ]
        };
        
        console.log('Using fallback reference data:', this.referenceData);
    }

    /**
     * Populate dropdowns with reference data
     */
    populateDropdowns() {
        console.log('=== populateDropdowns called ===');
        console.log('this.referenceData:', this.referenceData);
        
        if (!this.referenceData) {
            console.error('No reference data available - this should not happen!');
            return;
        }

        console.log('Populating dropdowns with data:', this.referenceData);
        console.log('Publications to populate:', this.referenceData.publications);
        console.log('Publications is array:', Array.isArray(this.referenceData.publications));
        console.log('Publications length:', this.referenceData.publications?.length);

        // Verify we have publications data
        if (!this.referenceData.publications || !Array.isArray(this.referenceData.publications)) {
            console.error('Publications data is not an array:', this.referenceData.publications);
            return;
        }

        this.populateDropdown('calc_publication', this.referenceData.publications);
        this.populateDropdown('calc_colorType', this.referenceData.colorTypes || []);
        this.populateDropdown('calc_category', this.referenceData.categories || []);
        this.populateDropdown('calc_size', this.referenceData.sizes || []);
        this.populateDropdown('calc_position', this.referenceData.positions || []);
        
        console.log('=== populateDropdowns completed ===');
    }

    populateDropdown(elementId, items) {
        console.log(`=== populateDropdown: ${elementId} ===`);
        console.log(`Items to populate:`, items);
        
        const select = document.getElementById(elementId);
        if (!select) {
            console.warn(`Element ${elementId} not found`);
            return;
        }

        console.log(`Found element ${elementId}:`, select);
        console.log(`Populating ${elementId} with ${items.length} items:`, items);

        // Keep the first option (placeholder)
        const placeholder = select.querySelector('option[value=""]');
        select.innerHTML = '';
        
        if (placeholder) {
            select.appendChild(placeholder);
            console.log(`Added placeholder to ${elementId}`);
        }

        if (items.length === 0) {
            console.warn(`No items to populate for ${elementId}`);
            return;
        }

        items.forEach((item, index) => {
            console.log(`Adding item ${index}:`, item);
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            select.appendChild(option);
        });

        console.log(`Successfully populated ${elementId} with ${items.length} options`);
        console.log(`Final select element:`, select);
        console.log(`=== populateDropdown: ${elementId} completed ===`);
    }

    /**
     * Setup dropdown dependencies and auto-calculation
     */
    setupDropdownDependencies() {
        const publication = document.getElementById('calc_publication');
        const colorType = document.getElementById('calc_colorType');
        const category = document.getElementById('calc_category');
        const size = document.getElementById('calc_size');
        const position = document.getElementById('calc_position');
        const insertions = document.getElementById('calc_insertions');
        const discountAmount = document.getElementById('calc_discount_amount');
        const discountType = document.getElementById('calc_discount_type');

        // Initially disable all except publication
        [colorType, category, size, position].forEach(el => {
            if (el) el.disabled = true;
        });

        // Enable children when publication selected
        if (publication) {
            publication.addEventListener('change', (e) => {
                if (e.target.value) {
                    [colorType, category, size, position].forEach(el => {
                        if (el) el.disabled = false;
                    });
                } else {
                    [colorType, category, size, position].forEach(el => {
                        if (el) {
                            el.disabled = true;
                            el.value = '';
                        }
                    });
                    this.clearResults();
                }
                this.debouncedCalculate();
            });
        }

        // Trigger calculation on any field change
        [publication, colorType, category, size, position, insertions, discountAmount, discountType].forEach(el => {
            if (el) {
                el.addEventListener('change', () => this.debouncedCalculate());
                el.addEventListener('input', () => this.debouncedCalculate());
            }
        });
    }

    /**
     * Debounced calculation to avoid excessive API calls
     */
    debouncedCalculate = this.debounce(() => {
        this.calculateRate();
    }, 300);

    /**
     * Debounce utility function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Calculate rate using API
     */
    async calculateRate() {
        if (this.isCalculating) return;

        const form = document.getElementById('calculatorForm');
        if (!form) return;

        const formData = new FormData(form);
        const criteria = {
            publication_id: formData.get('publication_id'),
            color_type_id: formData.get('color_type_id'),
            ad_category_id: formData.get('ad_category_id'),
            ad_size_id: formData.get('ad_size_id'),
            page_position_id: formData.get('page_position_id'),
            insertions: formData.get('insertions')
        };

        // Validate required fields
        if (!this.validateCriteria(criteria)) {
            return;
        }

        this.isCalculating = true;
        this.showCalculatingState();

        try {
            const discountAmount = parseFloat(formData.get('discount_amount')) || 0;
            const discountType = formData.get('discount_type') || 'fixed';

            let url = `${window.baseUrl}/api/calculator_api.php?action=calculate_rate`;
            
            // Add parameters to URL
            Object.entries(criteria).forEach(([key, value]) => {
                url += `&${key}=${encodeURIComponent(value)}`;
            });

            // Add discount parameters if applicable
            if (discountAmount > 0) {
                url += `&discount_amount=${discountAmount}&discount_type=${discountType}`;
                url = url.replace('action=calculate_rate', 'action=calculate_rate_with_discount');
            }

            const response = await fetch(url);
            const result = await response.json();

            if (result.success) {
                this.currentCalculation = result.data;
                this.displayResults(result.data);
                this.enableConvertButton();
            } else {
                throw new Error(result.message || 'Rate calculation failed');
            }
        } catch (error) {
            console.error('Rate calculation error:', error);
            this.showError(error.message);
            this.clearResults();
        } finally {
            this.isCalculating = false;
            this.hideCalculatingState();
        }
    }

    /**
     * Validate calculation criteria
     */
    validateCriteria(criteria) {
        const requiredFields = ['publication_id', 'color_type_id', 'ad_category_id', 'ad_size_id', 'page_position_id'];
        
        for (const field of requiredFields) {
            if (!criteria[field]) {
                return false;
            }
        }

        if (criteria.insertions < 1) {
            return false;
        }

        return true;
    }

    /**
     * Display calculation results
     */
    displayResults(data) {
        // Base calculation
        this.updateElement('calc_base_rate', data.base_rate_formatted);
        this.updateElement('calc_insertions_display', data.insertions);
        this.updateElement('calc_base_subtotal', data.base_subtotal_formatted);

        // Discount section
        const discountSection = document.getElementById('discount_section');
        if (data.discount_amount > 0) {
            discountSection.style.display = 'block';
            this.updateElement('calc_discount_amount_display', data.discount_amount_formatted);
            this.updateElement('calc_subtotal_after_discount', data.subtotal_after_discount_formatted);
        } else {
            discountSection.style.display = 'none';
        }

        // Tax calculations
        this.displayTaxCalculations(data.tax_calculations);

        // Totals
        this.updateElement('calc_total_tax', data.total_tax_formatted);
        this.updateElement('calc_final_total', data.final_total_formatted);
    }

    /**
     * Display tax calculations breakdown
     */
    displayTaxCalculations(taxCalculations) {
        const container = document.getElementById('calc_tax_calculations');
        if (!container) return;

        if (!taxCalculations || taxCalculations.length === 0) {
            container.innerHTML = '<p class="no-data">No taxes calculated</p>';
            return;
        }

        const taxHtml = taxCalculations.map(tax => `
            <div class="tax-calculation-item">
                <div class="tax-name">${this.escapeHtml(tax.name)} (${tax.rate}%)</div>
                <div class="tax-details">
                    <span class="tax-amount">${tax.amount_formatted}</span>
                    <small class="tax-info">
                        ${tax.apply_on === 'base' ? 'on base' : 'cumulative'} 
                        ${tax.discount_applicable ? '(discount applicable)' : '(no discount)'}
                    </small>
                </div>
            </div>
        `).join('');

        container.innerHTML = taxHtml;
    }

    /**
     * Show calculating state
     */
    showCalculatingState() {
        const elements = [
            'calc_base_rate', 'calc_insertions_display', 'calc_base_subtotal',
            'calc_total_tax', 'calc_final_total'
        ];
        
        elements.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.classList.add('calculating');
                element.textContent = 'Calculating...';
            }
        });
    }

    /**
     * Hide calculating state
     */
    hideCalculatingState() {
        const elements = [
            'calc_base_rate', 'calc_insertions_display', 'calc_base_subtotal',
            'calc_total_tax', 'calc_final_total'
        ];
        
        elements.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.classList.remove('calculating');
            }
        });
    }

    /**
     * Clear results display
     */
    clearResults() {
        const resultFields = [
            'calc_base_rate', 'calc_insertions_display', 'calc_base_subtotal',
            'calc_discount_amount_display', 'calc_subtotal_after_discount',
            'calc_total_tax', 'calc_final_total'
        ];
        
        resultFields.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = id === 'calc_insertions_display' ? '1' : 'GHS 0.00';
                element.classList.remove('calculating');
            }
        });

        // Clear tax calculations
        const taxContainer = document.getElementById('calc_tax_calculations');
        if (taxContainer) {
            taxContainer.innerHTML = '<p class="no-data">No taxes calculated</p>';
        }

        // Hide discount section
        const discountSection = document.getElementById('discount_section');
        if (discountSection) {
            discountSection.style.display = 'none';
        }

        this.currentCalculation = null;
        this.disableConvertButton();
    }

    /**
     * Reset calculator to initial state
     */
    resetCalculator() {
        const fields = ['calc_publication', 'calc_colorType', 'calc_category', 'calc_size', 'calc_position'];
        fields.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.value = '';
                if (id !== 'calc_publication') {
                    element.disabled = true;
                }
            }
        });

        // Reset insertions and discount
        const insertions = document.getElementById('calc_insertions');
        const discountAmount = document.getElementById('calc_discount_amount');
        const discountType = document.getElementById('calc_discount_type');
        
        if (insertions) insertions.value = '1';
        if (discountAmount) discountAmount.value = '0';
        if (discountType) discountType.value = 'fixed';

        this.clearResults();
    }

    /**
     * Convert calculation to booking
     */
    convertToBooking() {
        if (!this.currentCalculation) {
            window.Modal.warning('No Calculation', 'Please calculate a rate before converting to booking.');
            return false;
        }

        // Store calculation data in sessionStorage
        const bookingData = {
            publication_id: document.getElementById('calc_publication').value,
            color_type_id: document.getElementById('calc_colorType').value,
            ad_category_id: document.getElementById('calc_category').value,
            ad_size_id: document.getElementById('calc_size').value,
            page_position_id: document.getElementById('calc_position').value,
            insertions: document.getElementById('calc_insertions').value,
            discount_amount: document.getElementById('calc_discount_amount').value,
            discount_type: document.getElementById('calc_discount_type').value,
            rate_id: this.currentCalculation.rate_id,
            calculated_data: this.currentCalculation
        };

        sessionStorage.setItem('pendingBooking', JSON.stringify(bookingData));

        // Close modal and redirect
        window.Modal.close();
        window.location.href = `${window.baseUrl}/app/pages/booking.php`;
        
        return true;
    }

    /**
     * Enable convert to booking button
     */
    enableConvertButton() {
        const convertBtn = document.querySelector('[data-action="convert"]');
        if (convertBtn) {
            convertBtn.disabled = false;
            convertBtn.classList.remove('btn-secondary');
            convertBtn.classList.add('btn-success');
        }
    }

    /**
     * Disable convert to booking button
     */
    disableConvertButton() {
        const convertBtn = document.querySelector('[data-action="convert"]');
        if (convertBtn) {
            convertBtn.disabled = true;
            convertBtn.classList.remove('btn-success');
            convertBtn.classList.add('btn-secondary');
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        window.Modal.error('Calculation Error', message);
    }

    /**
     * Update element text content
     */
    updateElement(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = value;
        }
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Setup form validation
     */
    setupFormValidation() {
        const form = document.getElementById('calculatorForm');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.calculateRate();
        });
    }

    /**
     * Close calculator
     */
    close() {
        if (this.modal) {
            window.Modal.close();
            this.modal = null;
        }
    }
}

// Create global instance
window.RateCalculator = new RateCalculator();

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RateCalculator;
}
