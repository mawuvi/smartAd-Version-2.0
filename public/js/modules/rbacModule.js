/**
 * RBAC Module JavaScript
 * Location: public/js/modules/rbacModule.js
 * 
 * Handles client-side Role-Based Access Control functionality,
 * including permission checks, UI element visibility, and user feedback.
 */

class RBACModule {
    constructor() {
        this.permissions = window.currentUser?.permissions || [];
        this.userRole = window.currentUser?.role || 'user';
        this.menuItems = window.menuItems || [];
        this.init();
    }

    init() {
        this.applyPermissionFilters();
        this.setupPermissionTooltips();
        this.initializeRoleBasedStyling();
        this.setupPermissionChangeListeners();
    }

    /**
     * Apply permission-based filters to UI elements
     */
    applyPermissionFilters() {
        // Filter menu items
        this.filterMenuItems();
        
        // Filter dashboard widgets
        this.filterDashboardWidgets();
        
        // Filter form elements
        this.filterFormElements();
        
        // Filter action buttons
        this.filterActionButtons();
        
        // Filter table rows
        this.filterTableRows();
    }

    /**
     * Filter menu items based on permissions
     */
    filterMenuItems() {
        const menuContainer = document.querySelector('.main-nav, .sidebar-menu, .dashboard-menu');
        if (!menuContainer) return;

        const menuItems = menuContainer.querySelectorAll('.menu-item, .nav-item');
        menuItems.forEach(item => {
            const permission = item.dataset.permission;
            if (permission && !this.hasPermission(permission)) {
                this.hideElement(item);
            }
        });
    }

    /**
     * Filter dashboard widgets based on permissions
     */
    filterDashboardWidgets() {
        const widgets = document.querySelectorAll('.widget-section, .stat-card, .quick-action-btn');
        widgets.forEach(widget => {
            const permission = widget.dataset.permission;
            if (permission && !this.hasPermission(permission)) {
                this.hideElement(widget);
            }
        });
    }

    /**
     * Filter form elements based on permissions
     */
    filterFormElements() {
        const formElements = document.querySelectorAll('input, select, textarea, button');
        formElements.forEach(element => {
            const permission = element.dataset.permission;
            if (permission && !this.hasPermission(permission)) {
                this.disableElement(element);
            }
        });
    }

    /**
     * Filter action buttons based on permissions
     */
    filterActionButtons() {
        const actionButtons = document.querySelectorAll('.btn-action, .action-btn, button[data-action]');
        actionButtons.forEach(btn => {
            const permission = btn.dataset.permission;
            if (permission && !this.hasPermission(permission)) {
                this.disableElement(btn);
            }
        });
    }

    /**
     * Filter table rows based on permissions
     */
    filterTableRows() {
        const tableRows = document.querySelectorAll('tr[data-permission]');
        tableRows.forEach(row => {
            const permission = row.dataset.permission;
            if (permission && !this.hasPermission(permission)) {
                this.hideElement(row);
            }
        });
    }

    /**
     * Check if user has a specific permission
     */
    hasPermission(permissionCode) {
        // Admins have all permissions
        if (this.userRole === 'admin') {
            return true;
        }

        // Check if permission exists in user's permissions
        return this.permissions.includes(permissionCode);
    }

    /**
     * Check if user has any of the given permissions
     */
    hasAnyPermission(permissionCodes) {
        if (this.userRole === 'admin') {
            return true;
        }

        return permissionCodes.some(code => this.permissions.includes(code));
    }

    /**
     * Check if user has a specific role
     */
    hasRole(role) {
        return this.userRole === role;
    }

    /**
     * Check if user has any of the given roles
     */
    hasAnyRole(roles) {
        return roles.includes(this.userRole);
    }

    /**
     * Hide element and add permission denied styling
     */
    hideElement(element) {
        element.classList.add('permission-denied');
        element.style.display = 'none';
        
        // Add tooltip for hidden elements
        element.setAttribute('title', 'You do not have permission to view this item');
    }

    /**
     * Disable element and add permission denied styling
     */
    disableElement(element) {
        element.classList.add('permission-denied');
        element.disabled = true;
        element.style.pointerEvents = 'none';
        
        // Add tooltip for disabled elements
        element.setAttribute('title', 'You do not have permission to perform this action');
        
        // Add visual indicator
        const indicator = document.createElement('span');
        indicator.className = 'permission-indicator';
        indicator.innerHTML = '🔒';
        indicator.style.position = 'absolute';
        indicator.style.top = '4px';
        indicator.style.right = '4px';
        indicator.style.fontSize = '12px';
        
        if (element.style.position !== 'absolute' && element.style.position !== 'relative') {
            element.style.position = 'relative';
        }
        
        element.appendChild(indicator);
    }

    /**
     * Show permission denied message
     */
    showPermissionDeniedMessage(action = 'perform this action') {
        const message = `You do not have permission to ${action}.`;
        
        // Create notification
        const notification = document.createElement('div');
        notification.className = 'permission-denied-notification';
        notification.innerHTML = `
            <div class="notification-icon">🚫</div>
            <div class="notification-content">
                <h4>Access Denied</h4>
                <p>${message}</p>
            </div>
        `;
        
        // Style the notification
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 10000;
            max-width: 300px;
            animation: slideIn 0.3s ease;
        `;
        
        // Add animation CSS if not already added
        if (!document.querySelector('#rbac-animations')) {
            const style = document.createElement('style');
            style.id = 'rbac-animations';
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }

    /**
     * Setup permission tooltips
     */
    setupPermissionTooltips() {
        const elements = document.querySelectorAll('[data-permission]');
        elements.forEach(element => {
            const permission = element.dataset.permission;
            const hasPermission = this.hasPermission(permission);
            
            if (!hasPermission) {
                element.classList.add('permission-tooltip');
                element.setAttribute('data-permission', `Requires: ${permission}`);
            }
        });
    }

    /**
     * Initialize role-based styling
     */
    initializeRoleBasedStyling() {
        // Add role class to body
        document.body.classList.add(`role-${this.userRole}`);
        
        // Add role-based theme
        document.body.classList.add(`theme-${this.userRole}`);
        
        // Update role badge if exists
        const roleBadge = document.querySelector('.role-badge');
        if (roleBadge) {
            roleBadge.textContent = this.userRole.charAt(0).toUpperCase() + this.userRole.slice(1);
            roleBadge.classList.add(`role-${this.userRole}`);
        }
    }

    /**
     * Setup listeners for permission changes
     */
    setupPermissionChangeListeners() {
        // Listen for permission updates from server
        window.addEventListener('permissionsUpdated', (event) => {
            this.permissions = event.detail.permissions;
            this.userRole = event.detail.role;
            this.applyPermissionFilters();
        });
        
        // Listen for role changes
        window.addEventListener('roleChanged', (event) => {
            this.userRole = event.detail.role;
            this.initializeRoleBasedStyling();
            this.applyPermissionFilters();
        });
    }

    /**
     * Get user's accessible menu items
     */
    getAccessibleMenuItems() {
        return this.menuItems.filter(item => {
            if (!item.permission) return true;
            return this.hasPermission(item.permission);
        });
    }

    /**
     * Get user's quick actions
     */
    getQuickActions() {
        const allActions = [
            {
                title: 'New Booking',
                url: '/app/pages/booking.php',
                permission: 'bookings.create',
                icon: 'plus',
                class: 'primary'
            },
            {
                title: 'New Client',
                url: '/app/pages/clients.php?action=new',
                permission: 'clients.create',
                icon: 'user-plus',
                class: 'success'
            },
            {
                title: 'View Reports',
                url: '/app/pages/reports.php',
                permission: 'reports.view',
                icon: 'chart',
                class: 'info'
            },
            {
                title: 'Manage Users',
                url: '/app/pages/users.php',
                permission: 'users.manage',
                icon: 'users',
                class: 'warning'
            }
        ];

        return allActions.filter(action => this.hasPermission(action.permission));
    }

    /**
     * Check permission before performing action
     */
    checkPermissionBeforeAction(permission, action, callback) {
        if (this.hasPermission(permission)) {
            callback();
        } else {
            this.showPermissionDeniedMessage(action);
        }
    }

    /**
     * Wrap function with permission check
     */
    withPermission(permission, fn) {
        return (...args) => {
            if (this.hasPermission(permission)) {
                return fn(...args);
            } else {
                this.showPermissionDeniedMessage('perform this action');
                return false;
            }
        };
    }

    /**
     * Update permissions dynamically
     */
    updatePermissions(newPermissions, newRole = null) {
        this.permissions = newPermissions;
        if (newRole) {
            this.userRole = newRole;
        }
        
        // Reapply filters
        this.applyPermissionFilters();
        this.initializeRoleBasedStyling();
        
        // Dispatch event for other modules
        window.dispatchEvent(new CustomEvent('permissionsUpdated', {
            detail: {
                permissions: this.permissions,
                role: this.userRole
            }
        }));
    }

    /**
     * Get permission status for debugging
     */
    getPermissionStatus() {
        return {
            userRole: this.userRole,
            permissions: this.permissions,
            accessibleMenuItems: this.getAccessibleMenuItems().length,
            quickActions: this.getQuickActions().length
        };
    }
}

// Global RBAC functions for inline event handlers
window.hasPermission = function(permission) {
    return window.rbacModule?.hasPermission(permission) || false;
};

window.hasRole = function(role) {
    return window.rbacModule?.hasRole(role) || false;
};

window.checkPermission = function(permission, action, callback) {
    if (window.rbacModule) {
        window.rbacModule.checkPermissionBeforeAction(permission, action, callback);
    }
};

// Initialize RBAC module when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.rbacModule = new RBACModule();
    
    // Make RBAC available globally
    window.RBAC = window.rbacModule;
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RBACModule;
}
