/* User Menu JavaScript */
/* Location: public/js/components/userMenu.js */

class UserMenuManager {
    constructor() {
        this.userProfileBtn = document.getElementById('userProfileBtn');
        this.userMenu = document.getElementById('userMenu');
        this.logoutBtn = document.getElementById('logoutBtn');
        this.themeToggle = document.getElementById('themeToggle');
        this.isOpen = false;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadTheme();
        this.setupKeyboardShortcuts();
    }
    
    setupEventListeners() {
        // User profile dropdown
        if (this.userProfileBtn) {
            this.userProfileBtn.addEventListener('click', (e) => this.toggleUserMenu(e));
        }
        
        // Logout button
        if (this.logoutBtn) {
            this.logoutBtn.addEventListener('click', (e) => this.handleLogout(e));
        }
        
        // Theme toggle
        if (this.themeToggle) {
            this.themeToggle.addEventListener('click', () => this.toggleTheme());
        }
        
        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.user-profile-dropdown')) {
                this.closeUserMenu();
            }
        });
        
        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeUserMenu();
            }
        });
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + Shift + L for logout
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'L') {
                e.preventDefault();
                this.handleLogout();
            }
            
            // Ctrl/Cmd + Shift + T for theme toggle
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'T') {
                e.preventDefault();
                this.toggleTheme();
            }
        });
    }
    
    toggleUserMenu(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (this.isOpen) {
            this.closeUserMenu();
        } else {
            this.openUserMenu();
        }
    }
    
    openUserMenu() {
        this.userMenu?.classList.add('show');
        this.isOpen = true;
        
        // Add animation
        this.userMenu?.classList.add('fade-in');
        
        // Focus first interactive element
        const firstButton = this.userMenu?.querySelector('a, button');
        if (firstButton) {
            setTimeout(() => firstButton.focus(), 100);
        }
    }
    
    closeUserMenu() {
        this.userMenu?.classList.remove('show');
        this.isOpen = false;
        
        // Remove animation class
        setTimeout(() => {
            this.userMenu?.classList.remove('fade-in');
        }, 200);
    }
    
    async handleLogout(e) {
        if (e) {
            e.preventDefault();
        }
        
        // Show confirmation dialog
        const confirmed = await this.showConfirmDialog(
            'Logout',
            'Are you sure you want to logout?',
            'Logout',
            'Cancel'
        );
        
        if (!confirmed) return;
        
        try {
            // Show loading indicator
            this.showLoading('Logging out...');
            
            // Call logout API
            const response = await fetch(`${window.baseUrl}/api_logout.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error('Logout failed');
            }
            
            // Check if response is HTML (successful logout page)
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('text/html')) {
                // Redirect to the logout page
                window.location.href = `${window.baseUrl}/api_logout.php`;
            } else {
                // Handle JSON response (fallback)
                const result = await response.json();
                if (result.success) {
                    this.showToast('Successfully logged out', 'success');
                    setTimeout(() => {
                        window.location.href = `${window.baseUrl}/public_pages/login.php`;
                    }, 1000);
                } else {
                    throw new Error(result.message || 'Logout failed');
                }
            }
        } catch (error) {
            console.error('Logout error:', error);
            this.showToast('Logout failed. Please try again.', 'error');
        } finally {
            this.hideLoading();
        }
    }
    
    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        this.setTheme(newTheme);
        this.saveTheme(newTheme);
        
        // Update theme toggle icon
        const icon = this.themeToggle?.querySelector('.btn-icon-text');
        if (icon) {
            icon.textContent = newTheme === 'light' ? '🌙' : '☀️';
        }
        
        this.showToast(`Switched to ${newTheme} theme`, 'info');
    }
    
    setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        
        // Update meta theme-color
        let metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (!metaThemeColor) {
            metaThemeColor = document.createElement('meta');
            metaThemeColor.name = 'theme-color';
            document.head.appendChild(metaThemeColor);
        }
        
        metaThemeColor.content = theme === 'dark' ? '#1f2937' : '#ffffff';
    }
    
    saveTheme(theme) {
        localStorage.setItem('theme', theme);
    }
    
    loadTheme() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        this.setTheme(savedTheme);
        
        // Update theme toggle icon
        const icon = this.themeToggle?.querySelector('.btn-icon-text');
        if (icon) {
            icon.textContent = savedTheme === 'light' ? '🌙' : '☀️';
        }
    }
    
    showConfirmDialog(title, message, confirmText = 'OK', cancelText = 'Cancel') {
        return new Promise((resolve) => {
            // Check if dialog already exists
            const existingDialog = document.querySelector('.confirm-dialog-overlay');
            if (existingDialog) {
                existingDialog.remove();
            }
            
            const dialog = document.createElement('div');
            dialog.className = 'confirm-dialog-overlay';
            dialog.innerHTML = `
                <div class="confirm-dialog">
                    <div class="dialog-header">
                        <h3>${title}</h3>
                    </div>
                    <div class="dialog-body">
                        <p>${message}</p>
                    </div>
                    <div class="dialog-footer">
                        <button class="btn btn-secondary dialog-cancel">${cancelText}</button>
                        <button class="btn btn-primary dialog-confirm">${confirmText}</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(dialog);
            
            const confirmBtn = dialog.querySelector('.dialog-confirm');
            const cancelBtn = dialog.querySelector('.dialog-cancel');
            
            const cleanup = () => {
                document.body.removeChild(dialog);
            };
            
            confirmBtn.addEventListener('click', () => {
                cleanup();
                resolve(true);
            });
            
            cancelBtn.addEventListener('click', () => {
                cleanup();
                resolve(false);
            });
            
            // Focus confirm button
            setTimeout(() => confirmBtn.focus(), 100);
            
            // Handle escape key
            const handleEscape = (e) => {
                if (e.key === 'Escape') {
                    cleanup();
                    resolve(false);
                    document.removeEventListener('keydown', handleEscape);
                }
            };
            document.addEventListener('keydown', handleEscape);
        });
    }
    
    showLoading(message = 'Loading...') {
        const loadingIndicator = document.getElementById('loadingIndicator');
        if (loadingIndicator) {
            const loadingText = loadingIndicator.querySelector('.loading-text');
            if (loadingText) {
                loadingText.textContent = message;
            }
            loadingIndicator.classList.add('show');
        }
    }
    
    hideLoading() {
        const loadingIndicator = document.getElementById('loadingIndicator');
        if (loadingIndicator) {
            loadingIndicator.classList.remove('show');
        }
    }
    
    showToast(message, type = 'info', duration = 3000) {
        const toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) return;
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        
        toast.innerHTML = `
            <div class="toast-icon">${icons[type] || icons.info}</div>
            <div class="toast-content">
                <div class="toast-title">${type.charAt(0).toUpperCase() + type.slice(1)}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close">×</button>
        `;
        
        toastContainer.appendChild(toast);
        
        // Add show class after a brief delay
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        // Auto remove
        setTimeout(() => {
            this.removeToast(toast);
        }, duration);
        
        // Close button
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => {
            this.removeToast(toast);
        });
    }
    
    removeToast(toast) {
        toast.classList.add('slide-out-right');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }
}

// Quick Actions Manager
class QuickActionsManager {
    constructor() {
        this.quickActionsBtn = document.getElementById('quickActionsBtn');
        this.quickActionsMenu = document.getElementById('quickActionsMenu');
        this.isOpen = false;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        if (this.quickActionsBtn) {
            this.quickActionsBtn.addEventListener('click', (e) => this.toggleMenu(e));
        }
        
        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.quick-actions-dropdown')) {
                this.closeMenu();
            }
        });
        
        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeMenu();
            }
        });
    }
    
    toggleMenu(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (this.isOpen) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    }
    
    openMenu() {
        this.quickActionsMenu?.classList.add('show');
        this.isOpen = true;
        
        // Add animation
        this.quickActionsMenu?.classList.add('fade-in');
        
        // Focus first item
        const firstItem = this.quickActionsMenu?.querySelector('.dropdown-item');
        if (firstItem) {
            setTimeout(() => firstItem.focus(), 100);
        }
    }
    
    closeMenu() {
        this.quickActionsMenu?.classList.remove('show');
        this.isOpen = false;
        
        // Remove animation class
        setTimeout(() => {
            this.quickActionsMenu?.classList.remove('fade-in');
        }, 200);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.userMenuManager = new UserMenuManager();
    window.quickActionsManager = new QuickActionsManager();
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { UserMenuManager, QuickActionsManager };
}
