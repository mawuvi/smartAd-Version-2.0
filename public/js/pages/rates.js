/**
 * Rates Page JavaScript
 * Uses RateCalculatorModule for consistent rate calculation
 */

(function() {
    'use strict';

    let rateCalculator = null;

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeRateCalculator();
        bindEventListeners();
    });

    function initializeRateCalculator() {
        // Initialize RateCalculatorModule with auto-calculation enabled
        rateCalculator = new RateCalculatorModule({
            autoCalculate: false, // Manual calculation via button
            showConfirmOnClear: true,
            onCalculationComplete: function(rateData) {
                console.log('Rate calculation completed:', rateData);
                // Additional actions after calculation can be added here
            }
        });
    }

    function bindEventListeners() {
        // Calculate Rate Button
        const calculateBtn = document.getElementById('calculateRateBtn');
        if (calculateBtn) {
            calculateBtn.addEventListener('click', function() {
                rateCalculator.calculateRate();
            });
        }

        // Clear Form Button
        const clearBtn = document.getElementById('clearFormBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                rateCalculator.resetToInitialState();
            });
        }

        // Auto-calculation on form changes (optional)
        const form = document.getElementById('rateCalculatorForm');
        if (form) {
            // Enable auto-calculation after first manual calculation
            let hasCalculatedOnce = false;
            
            form.addEventListener('change', function() {
                if (hasCalculatedOnce) {
                    // Auto-calculate after first manual calculation
                    setTimeout(() => {
                        rateCalculator.calculateRate();
                    }, 500);
                }
            });

            // Track when first calculation is done
            const originalCalculate = rateCalculator.calculateRate.bind(rateCalculator);
            rateCalculator.calculateRate = function() {
                const result = originalCalculate();
                if (result) {
                    hasCalculatedOnce = true;
                }
                return result;
            };
        }
    }

    // Export for global access if needed
    window.rateCalculator = rateCalculator;

})();

