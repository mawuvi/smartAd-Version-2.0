/**
 * RateCalculatorModule - Reusable Rate Calculation Logic
 * Extracted from rates.js to be shared between rates.php and booking.php
 */

class RateCalculatorModule {
    constructor(config = {}) {
        this.config = {
            elementIds: {
                publication: 'publication',
                colorType: 'colorType',
                adCategory: 'adCategory',
                adSize: 'adSize',
                pagePosition: 'pagePosition',
                insertions: 'insertions'
            },
            resultsElementIds: {
                baseRate: 'baseRate',
                subtotal: 'subtotal',
                taxList: 'taxList',
                totalTax: 'totalTax',
                total: 'total'
            },
            apiUrl: '/api/rate_api.php',
            onCalculationComplete: null,
            autoCalculate: false,
            showConfirmOnClear: false,
            ...config
        };

        this.currentRateData = null;
        this.isCalculating = false;
        this.initialState = null;

        this.init();
    }

    init() {
        this.bindEvents();
        this.storeInitialState();
        
        if (this.config.autoCalculate) {
            this.bindCalculationTriggers();
        }
    }

    bindEvents() {
        // Bind to all form elements for calculation triggers
        Object.values(this.config.elementIds).forEach(elementId => {
            const element = document.getElementById(elementId);
            if (element) {
                element.addEventListener('change', () => {
                    if (this.config.autoCalculate) {
                        this.calculateRate();
                    }
                });
            }
        });
    }

    bindCalculationTriggers() {
        // Additional triggers for auto-calculation
        const triggers = ['publication', 'colorType', 'adCategory', 'adSize', 'pagePosition'];
        triggers.forEach(trigger => {
            const element = document.getElementById(trigger);
            if (element) {
                element.addEventListener('change', () => {
                    setTimeout(() => this.calculateRate(), 100);
                });
            }
        });
    }

    storeInitialState() {
        this.initialState = {
            publication: this.getElementValue('publication'),
            colorType: this.getElementValue('colorType'),
            adCategory: this.getElementValue('adCategory'),
            adSize: this.getElementValue('adSize'),
            pagePosition: this.getElementValue('pagePosition'),
            insertions: this.getElementValue('insertions') || '1'
        };
    }

    async calculateRate() {
        if (this.isCalculating) return null;

        const criteria = this.getCalculationCriteria();
        
        if (!this.validateCriteria(criteria)) {
            return null;
        }

        // Show confirmation modal
        const confirmed = await this.showCalculationConfirmation(criteria);
        if (!confirmed) {
            return null;
        }

        this.isCalculating = true;
        this.showCalculatingState();

        try {
            const response = await fetch(`${this.config.apiUrl}?action=calculate_rate&${this.buildQueryString(criteria)}`);
            const result = await response.json();

            if (result.success) {
                this.currentRateData = result.data;
                this.displayResults(result.data);
                
                if (this.config.onCalculationComplete) {
                    this.config.onCalculationComplete(result.data);
                }
                
                return result.data;
            } else {
                throw new Error(result.message || 'Rate calculation failed');
            }
        } catch (error) {
            console.error('Rate calculation error:', error);
            this.showError(error.message);
            return null;
        } finally {
            this.isCalculating = false;
            this.hideCalculatingState();
        }
    }

    getCalculationCriteria() {
        return {
            publication_id: this.getElementValue('publication'),
            color_type_id: this.getElementValue('colorType'),
            ad_category_id: this.getElementValue('adCategory'),
            ad_size_id: this.getElementValue('adSize'),
            page_position_id: this.getElementValue('pagePosition'),
            insertions: parseInt(this.getElementValue('insertions')) || 1
        };
    }

    validateCriteria(criteria) {
        const requiredFields = ['publication_id', 'color_type_id', 'ad_category_id', 'ad_size_id', 'page_position_id'];
        
        for (const field of requiredFields) {
            if (!criteria[field]) {
                this.showError(`Please select ${field.replace('_', ' ')}`);
                return false;
            }
        }

        if (criteria.insertions < 1) {
            this.showError('Insertions must be at least 1');
            return false;
        }

        return true;
    }

    async showCalculationConfirmation(criteria) {
        const message = this.buildConfirmationMessage(criteria);
        
        const confirmed = await Modal.confirm(
            'Confirm Rate Calculation',
            message,
            'Cancel',
            'Calculate Rate'
        );
        
        return confirmed;
    }

    buildConfirmationMessage(criteria) {
        const publicationName = this.getDisplayNameFromSelect('publication');
        const colorTypeName = this.getDisplayNameFromSelect('colorType');
        const adCategoryName = this.getDisplayNameFromSelect('adCategory');
        const adSizeName = this.getDisplayNameFromSelect('adSize');
        const pagePositionName = this.getDisplayNameFromSelect('pagePosition');

        return `
            <div class="rate-confirmation-details">
                <div class="confirmation-row">
                    <strong>Publication:</strong> ${publicationName}
                </div>
                <div class="confirmation-row">
                    <strong>Color Type:</strong> ${colorTypeName}
                </div>
                <div class="confirmation-row">
                    <strong>Category:</strong> ${adCategoryName}
                </div>
                <div class="confirmation-row">
                    <strong>Ad Size:</strong> ${adSizeName}
                </div>
                <div class="confirmation-row">
                    <strong>Position:</strong> ${pagePositionName}
                </div>
                <div class="confirmation-row">
                    <strong>Insertions:</strong> ${criteria.insertions}
                </div>
            </div>
        `;
    }

    getDisplayNameFromSelect(elementId) {
        const select = document.getElementById(elementId);
        if (!select || !select.value) return 'N/A';
        
        const selectedOption = select.options[select.selectedIndex];
        return selectedOption ? selectedOption.textContent : 'N/A';
    }

    displayResults(rateData) {
        this.updateElement('baseRate', this.formatCurrency(rateData.base_rate));
        this.updateElement('subtotal', this.formatCurrency(rateData.subtotal));
        this.updateElement('totalTax', this.formatCurrency(rateData.total_tax));
        this.updateElement('total', this.formatCurrency(rateData.total));

        // Display tax breakdown
        this.displayTaxBreakdown(rateData.tax_breakdown);
    }

    displayTaxBreakdown(taxBreakdown) {
        const taxListElement = document.getElementById(this.config.resultsElementIds.taxList);
        if (!taxListElement) return;

        if (!taxBreakdown || taxBreakdown.length === 0) {
            taxListElement.innerHTML = '<div class="no-taxes">No taxes applicable</div>';
            return;
        }

        const taxHtml = taxBreakdown.map(tax => `
            <div class="tax-item">
                <span class="tax-name">${tax.name}</span>
                <span class="tax-rate">${tax.rate}%</span>
                <span class="tax-amount">${this.formatCurrency(tax.amount)}</span>
            </div>
        `).join('');

        taxListElement.innerHTML = taxHtml;
    }

    showCalculatingState() {
        const elements = Object.values(this.config.resultsElementIds);
        elements.forEach(elementId => {
            const element = document.getElementById(elementId);
            if (element) {
                element.classList.add('calculating');
                element.textContent = 'Calculating...';
            }
        });
    }

    hideCalculatingState() {
        const elements = Object.values(this.config.resultsElementIds);
        elements.forEach(elementId => {
            const element = document.getElementById(elementId);
            if (element) {
                element.classList.remove('calculating');
            }
        });
    }

    showError(message) {
        // Use existing modal system or console error
        if (typeof Modal !== 'undefined') {
            Modal.error('Rate Calculation Error', message);
        } else {
            console.error('Rate Calculation Error:', message);
            alert(`Rate Calculation Error: ${message}`);
        }
    }

    resetToInitialState() {
        if (this.config.showConfirmOnClear) {
            const confirmed = confirm('Are you sure you want to clear all selections?');
            if (!confirmed) return;
        }

        // Reset form elements
        Object.entries(this.config.elementIds).forEach(([key, elementId]) => {
            const element = document.getElementById(elementId);
            if (element) {
                if (element.tagName === 'SELECT') {
                    element.selectedIndex = 0;
                } else {
                    element.value = this.initialState[key] || '';
                }
            }
        });

        // Reset insertions to 1
        const insertionsEl = document.getElementById('insertions');
        if (insertionsEl) insertionsEl.value = '1';

        // Reset discount fields
        const discountSelect = document.getElementById('discountSelect');
        if (discountSelect) discountSelect.value = '';

        // Clear results
        this.clearResults();
        this.currentRateData = null;
    }

    clearResults() {
        const elements = Object.values(this.config.resultsElementIds);
        elements.forEach(elementId => {
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = 'GHS 0.00';
                element.classList.remove('calculating');
            }
        });

        // Clear tax breakdown
        const taxListElement = document.getElementById(this.config.resultsElementIds.taxList);
        if (taxListElement) {
            taxListElement.innerHTML = '';
        }
    }

    getElementValue(elementId) {
        const element = document.getElementById(elementId);
        return element ? element.value : null;
    }

    updateElement(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = value;
        }
    }

    buildQueryString(params) {
        return Object.entries(params)
            .filter(([key, value]) => value !== null && value !== undefined && value !== '')
            .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(value)}`)
            .join('&');
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-GH', {
            style: 'currency',
            currency: 'GHS',
            minimumFractionDigits: 2
        }).format(amount);
    }

    getCurrentRateData() {
        return this.currentRateData;
    }

    setConfig(newConfig) {
        this.config = { ...this.config, ...newConfig };
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RateCalculatorModule;
}
