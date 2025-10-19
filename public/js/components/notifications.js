/* Notifications JavaScript */
/* Location: public/js/components/notifications.js */

class NotificationsManager {
    constructor() {
        this.notificationsBtn = document.getElementById('notificationsBtn');
        this.notificationsMenu = document.getElementById('notificationsMenu');
        this.markAllReadBtn = document.getElementById('markAllRead');
        this.isOpen = false;
        this.notifications = [];
        this.pollInterval = null;
        this.pollIntervalMs = 30000; // 30 seconds
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.startPolling();
        this.loadNotifications();
    }
    
    setupEventListeners() {
        // Notifications dropdown
        if (this.notificationsBtn) {
            this.notificationsBtn.addEventListener('click', (e) => this.toggleNotifications(e));
        }
        
        // Mark all as read
        if (this.markAllReadBtn) {
            this.markAllReadBtn.addEventListener('click', (e) => this.markAllAsRead(e));
        }
        
        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.notifications-dropdown')) {
                this.closeNotifications();
            }
        });
        
        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeNotifications();
            }
        });
        
        // Visibility change (tab focus/blur)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopPolling();
            } else {
                this.startPolling();
                this.loadNotifications(); // Refresh when tab becomes visible
            }
        });
    }
    
    toggleNotifications(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (this.isOpen) {
            this.closeNotifications();
        } else {
            this.openNotifications();
        }
    }
    
    openNotifications() {
        this.notificationsMenu?.classList.add('show');
        this.isOpen = true;
        
        // Add animation
        this.notificationsMenu?.classList.add('fade-in');
        
        // Mark notifications as viewed (not read)
        this.markAsViewed();
    }
    
    closeNotifications() {
        this.notificationsMenu?.classList.remove('show');
        this.isOpen = false;
        
        // Remove animation class
        setTimeout(() => {
            this.notificationsMenu?.classList.remove('fade-in');
        }, 200);
    }
    
    async loadNotifications() {
        try {
            const response = await fetch(`${window.baseUrl}/app/api/notifications.php`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`Failed to load notifications: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.notifications = result.data || [];
                this.updateNotificationBadge();
                this.renderNotifications();
            } else {
                throw new Error(result.message || 'Failed to load notifications');
            }
        } catch (error) {
            console.error('Notifications error:', error);
            this.showNotificationError('Failed to load notifications');
        }
    }
    
    renderNotifications() {
        const notificationsList = this.notificationsMenu?.querySelector('.notifications-list');
        if (!notificationsList) return;
        
        if (this.notifications.length === 0) {
            notificationsList.innerHTML = `
                <div class="notification-item notification-empty">
                    <span class="notification-icon">✅</span>
                    <span class="notification-text">No new notifications</span>
                </div>
            `;
            return;
        }
        
        const html = this.notifications.map(notification => `
            <div class="notification-item notification-${notification.type}" data-id="${notification.id}">
                <span class="notification-icon">
                    ${this.getNotificationIcon(notification.type)}
                </span>
                <div class="notification-content">
                    <div class="notification-title">${this.escapeHtml(notification.title)}</div>
                    <div class="notification-message">${this.escapeHtml(notification.message)}</div>
                    <div class="notification-time">${this.formatTime(notification.created_at)}</div>
                </div>
                ${notification.action_url ? `
                    <a href="${window.baseUrl}${notification.action_url}" 
                       class="notification-action"
                       onclick="window.notificationsManager.markAsRead(${notification.id})">
                        View
                    </a>
                ` : ''}
                <button class="notification-dismiss" 
                        onclick="window.notificationsManager.dismissNotification(${notification.id})"
                        title="Dismiss">
                    ×
                </button>
            </div>
        `).join('');
        
        notificationsList.innerHTML = html;
    }
    
    updateNotificationBadge() {
        const badge = this.notificationsBtn?.querySelector('.notification-badge');
        const unreadCount = this.notifications.filter(n => !n.read).length;
        
        if (badge) {
            if (unreadCount > 0) {
                badge.textContent = unreadCount > 99 ? '99+' : unreadCount.toString();
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }
        
        // Update page title if there are unread notifications
        this.updatePageTitle(unreadCount);
    }
    
    updatePageTitle(unreadCount) {
        const baseTitle = document.title.replace(/^\(\d+\)\s*/, '');
        
        if (unreadCount > 0) {
            document.title = `(${unreadCount}) ${baseTitle}`;
        } else {
            document.title = baseTitle;
        }
    }
    
    async markAsRead(notificationId) {
        try {
            const response = await fetch(`${window.baseUrl}/app/api/notifications.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'mark_read',
                    notification_id: notificationId
                }),
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error('Failed to mark notification as read');
            }
            
            const result = await response.json();
            
            if (result.success) {
                // Update local state
                const notification = this.notifications.find(n => n.id === notificationId);
                if (notification) {
                    notification.read = true;
                    this.updateNotificationBadge();
                }
            }
        } catch (error) {
            console.error('Mark as read error:', error);
        }
    }
    
    async markAllAsRead(e) {
        e.preventDefault();
        
        try {
            const response = await fetch(`${window.baseUrl}/app/api/notifications.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'mark_all_read'
                }),
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error('Failed to mark all notifications as read');
            }
            
            const result = await response.json();
            
            if (result.success) {
                // Update local state
                this.notifications.forEach(notification => {
                    notification.read = true;
                });
                
                this.updateNotificationBadge();
                this.renderNotifications();
                
                this.showToast('All notifications marked as read', 'success');
            }
        } catch (error) {
            console.error('Mark all as read error:', error);
            this.showToast('Failed to mark notifications as read', 'error');
        }
    }
    
    async markAsViewed() {
        try {
            await fetch(`${window.baseUrl}/app/api/notifications.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'mark_viewed'
                }),
                credentials: 'same-origin'
            });
        } catch (error) {
            console.error('Mark as viewed error:', error);
        }
    }
    
    async dismissNotification(notificationId) {
        try {
            const response = await fetch(`${window.baseUrl}/app/api/notifications.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'dismiss',
                    notification_id: notificationId
                }),
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error('Failed to dismiss notification');
            }
            
            const result = await response.json();
            
            if (result.success) {
                // Remove from local state
                this.notifications = this.notifications.filter(n => n.id !== notificationId);
                this.updateNotificationBadge();
                this.renderNotifications();
            }
        } catch (error) {
            console.error('Dismiss notification error:', error);
        }
    }
    
    startPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
        }
        
        this.pollInterval = setInterval(() => {
            this.loadNotifications();
        }, this.pollIntervalMs);
    }
    
    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }
    
    getNotificationIcon(type) {
        const icons = {
            info: 'ℹ️',
            success: '✅',
            warning: '⚠️',
            error: '❌',
            reminder: '⏰',
            approval: '👥',
            system: '⚙️',
            booking: '📊',
            client: '👤',
            rate: '💰',
            publication: '📰'
        };
        
        return icons[type] || icons.info;
    }
    
    formatTime(timestamp) {
        const now = new Date();
        const time = new Date(timestamp);
        const diffMs = now - time;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        
        if (diffMins < 1) {
            return 'Just now';
        } else if (diffMins < 60) {
            return `${diffMins}m ago`;
        } else if (diffHours < 24) {
            return `${diffHours}h ago`;
        } else if (diffDays < 7) {
            return `${diffDays}d ago`;
        } else {
            return time.toLocaleDateString();
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    showNotificationError(message) {
        const notificationsList = this.notificationsMenu?.querySelector('.notifications-list');
        if (notificationsList) {
            notificationsList.innerHTML = `
                <div class="notification-item notification-error">
                    <span class="notification-icon">⚠️</span>
                    <span class="notification-text">${message}</span>
                </div>
            `;
        }
    }
    
    showToast(message, type = 'info', duration = 3000) {
        // Use the global toast system if available
        if (window.userMenuManager && window.userMenuManager.showToast) {
            window.userMenuManager.showToast(message, type, duration);
        } else {
            console.log(`Toast: ${message}`);
        }
    }
    
    // Public methods
    refresh() {
        this.loadNotifications();
    }
    
    setPollInterval(intervalMs) {
        this.pollIntervalMs = intervalMs;
        this.startPolling();
    }
    
    getStats() {
        return {
            total: this.notifications.length,
            unread: this.notifications.filter(n => !n.read).length,
            isOpen: this.isOpen,
            pollInterval: this.pollIntervalMs
        };
    }
    
    // Cleanup
    destroy() {
        this.stopPolling();
        this.closeNotifications();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.notificationsManager = new NotificationsManager();
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationsManager;
}
