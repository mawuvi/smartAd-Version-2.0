/**
 * Modal System JavaScript - Reusable Modal Framework
 * Location: public/js/modal.js
 * Standards Applied: ES6+, async/await, error handling
 */

class Modal {
    constructor() {
        this.activeModal = null;
        this.modalCount = 0;
        this.init();
    }

    init() {
        // Handle ESC key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModal) {
                this.close();
            }
        });

        // Handle click outside modal to close
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay') && this.activeModal) {
                this.close();
            }
        });
    }

    /**
     * Open a modal with HTML content
     * @param {string} content - HTML content for modal body
     * @param {Object} options - Modal configuration options
     * @returns {Promise} - Resolves when modal is closed
     */
    open(content, options = {}) {
        return new Promise((resolve) => {
            const config = {
                title: options.title || 'Modal',
                size: options.size || 'medium', // small, medium, large, wide, wizard
                showClose: options.showClose !== false,
                buttons: options.buttons || [],
                onClose: options.onClose || null,
                className: options.className || '',
                ...options
            };

            const modalId = `modal-${++this.modalCount}`;
            const modalHtml = this.createModalHtml(modalId, config, content);
            
            // Remove any existing modal
            this.close();
            
            // Add modal to DOM
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            const modalElement = document.getElementById(modalId);
            this.activeModal = modalElement;
            
            // Setup button handlers
            this.setupButtonHandlers(modalElement, config.buttons, resolve);
            
            // Setup close handler
            if (config.showClose) {
                const closeBtn = modalElement.querySelector('.modal-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        this.close();
                        resolve();
                    });
                }
            }
            
            // Trigger animation
            requestAnimationFrame(() => {
                modalElement.classList.add('active');
            });
        });
    }

    /**
     * Create modal HTML structure
     */
    createModalHtml(modalId, config, content) {
        const buttonsHtml = config.buttons.map(btn => 
            `<button class="btn ${btn.class || 'btn-secondary'}" data-action="${btn.action}">
                ${btn.text}
            </button>`
        ).join('');

        return `
            <div class="modal-overlay" id="${modalId}">
                <div class="modal-content modal-${config.size} ${config.className}">
                    <div class="modal-header">
                        <h3>${config.title}</h3>
                        ${config.showClose ? '<button class="modal-close">&times;</button>' : ''}
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                    ${buttonsHtml ? `
                        <div class="modal-footer">
                            ${buttonsHtml}
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    /**
     * Setup button event handlers
     */
    setupButtonHandlers(modalElement, buttons, resolve) {
        buttons.forEach(button => {
            const btnElement = modalElement.querySelector(`[data-action="${button.action}"]`);
            if (btnElement) {
                btnElement.addEventListener('click', async (e) => {
                    e.preventDefault();
                    
                    if (button.handler) {
                        try {
                            const result = await button.handler(modalElement);
                            if (result !== false) {
                                this.close();
                                resolve(result);
                            }
                        } catch (error) {
                            console.error('Modal button handler error:', error);
                            this.error('Error', error.message);
                        }
                    } else {
                        this.close();
                        resolve(button.action);
                    }
                });
            }
        });
    }

    /**
     * Close the active modal
     */
    close() {
        if (this.activeModal) {
            this.activeModal.classList.remove('active');
            setTimeout(() => {
                if (this.activeModal && this.activeModal.parentNode) {
                    this.activeModal.parentNode.removeChild(this.activeModal);
                }
                this.activeModal = null;
            }, 300);
        }
    }

    /**
     * Show confirmation dialog
     * @param {string} message - Confirmation message
     * @param {string} title - Dialog title
     * @returns {Promise<boolean>} - User's choice
     */
    confirm(message, title = 'Confirm') {
        return this.open(`
            <div class="modal-alert alert-info">
                <p>${message}</p>
            </div>
        `, {
            title,
            size: 'small',
            buttons: [
                {
                    text: 'Cancel',
                    action: 'cancel',
                    class: 'btn-secondary'
                },
                {
                    text: 'Confirm',
                    action: 'confirm',
                    class: 'btn-primary'
                }
            ]
        }).then(result => result === 'confirm');
    }

    /**
     * Show error dialog
     * @param {string} title - Error title
     * @param {string} message - Error message
     */
    error(title, message) {
        return this.open(`
            <div class="modal-alert alert-error">
                <p>${message}</p>
            </div>
        `, {
            title,
            size: 'small',
            buttons: [
                {
                    text: 'OK',
                    action: 'ok',
                    class: 'btn-primary'
                }
            ]
        });
    }

    /**
     * Show success dialog
     * @param {string} title - Success title
     * @param {string} message - Success message
     */
    success(title, message) {
        return this.open(`
            <div class="modal-alert alert-success">
                <p>${message}</p>
            </div>
        `, {
            title,
            size: 'small',
            buttons: [
                {
                    text: 'OK',
                    action: 'ok',
                    class: 'btn-primary'
                }
            ]
        });
    }

    /**
     * Show warning dialog
     * @param {string} title - Warning title
     * @param {string} message - Warning message
     */
    warning(title, message) {
        return this.open(`
            <div class="modal-alert alert-warning">
                <p>${message}</p>
            </div>
        `, {
            title,
            size: 'small',
            buttons: [
                {
                    text: 'OK',
                    action: 'ok',
                    class: 'btn-primary'
                }
            ]
        });
    }

    /**
     * Show info dialog
     * @param {string} title - Info title
     * @param {string} message - Info message
     */
    info(title, message) {
        return this.open(`
            <div class="modal-alert alert-info">
                <p>${message}</p>
            </div>
        `, {
            title,
            size: 'small',
            buttons: [
                {
                    text: 'OK',
                    action: 'ok',
                    class: 'btn-primary'
                }
            ]
        });
    }

    /**
     * Show loading state in modal
     * @param {string} message - Loading message
     */
    showLoading(message = 'Loading...') {
        if (this.activeModal) {
            const loadingHtml = `
                <div class="modal-loading">
                    <div class="spinner"></div>
                    <p style="margin-top: 1rem; color: #64748b;">${message}</p>
                </div>
            `;
            this.activeModal.querySelector('.modal-content').insertAdjacentHTML('beforeend', loadingHtml);
        }
    }

    /**
     * Hide loading state in modal
     */
    hideLoading() {
        if (this.activeModal) {
            const loadingElement = this.activeModal.querySelector('.modal-loading');
            if (loadingElement) {
                loadingElement.remove();
            }
        }
    }

    /**
     * Get form data from modal
     * @param {string} formId - Form ID within modal
     * @returns {Object} - Form data as object
     */
    getFormData(formId) {
        if (!this.activeModal) return {};
        
        const form = this.activeModal.querySelector(formId ? `#${formId}` : 'form');
        if (!form) return {};
        
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        return data;
    }

    /**
     * Set form data in modal
     * @param {Object} data - Data to populate form with
     * @param {string} formId - Form ID within modal
     */
    setFormData(data, formId) {
        if (!this.activeModal) return;
        
        const form = this.activeModal.querySelector(formId ? `#${formId}` : 'form');
        if (!form) return;
        
        Object.entries(data).forEach(([key, value]) => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = value;
                } else {
                    field.value = value;
                }
            }
        });
    }
}

// Create global Modal instance
window.Modal = new Modal();

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Modal;
}
