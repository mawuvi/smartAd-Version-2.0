/**
 * Dashboard JavaScript
 * Location: public/js/pages/dashboard.js
 * 
 * Handles dashboard-specific functionality including charts, 
 * real-time updates, and user interactions.
 */

class DashboardManager {
    constructor() {
        this.charts = {};
        this.refreshInterval = null;
        this.init();
    }

    init() {
        this.initializeCharts();
        this.setupEventListeners();
        this.startAutoRefresh();
        this.initializeTooltips();
    }

    /**
     * Initialize all dashboard charts
     */
    initializeCharts() {
        // Revenue Chart
        if (window.revenueChartData && window.revenueChartData.length > 0) {
            this.createRevenueChart();
        }

        // Additional charts can be added here
        this.createActivityChart();
    }

    /**
     * Create revenue trend chart
     */
    createRevenueChart() {
        const ctx = document.getElementById('revenueChart');
        if (!ctx) return;

        const labels = window.revenueChartData.map(item => {
            const date = new Date(item.month + '-01');
            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        });

        const data = window.revenueChartData.map(item => parseFloat(item.revenue) || 0);

        this.charts.revenue = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (₵)',
                    data: data,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                return `Revenue: ₵${context.parsed.y.toLocaleString()}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 12
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6'
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                return '₵' + value.toLocaleString();
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    /**
     * Create activity chart (placeholder for future implementation)
     */
    createActivityChart() {
        // This can be implemented when activity data is available
        console.log('Activity chart placeholder - ready for implementation');
    }

    /**
     * Setup event listeners for dashboard interactions
     */
    setupEventListeners() {
        // Quick action buttons
        document.querySelectorAll('.quick-action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.handleQuickAction(e);
            });
        });

        // Notification actions
        document.querySelectorAll('.notification-action').forEach(link => {
            link.addEventListener('click', (e) => {
                this.handleNotificationAction(e);
            });
        });

        // Approval buttons
        document.querySelectorAll('.approval-actions button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.handleApprovalAction(e);
            });
        });

        // Refresh button (if exists)
        const refreshBtn = document.getElementById('refreshDashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refreshDashboard();
            });
        }
    }

    /**
     * Handle quick action button clicks
     */
    handleQuickAction(event) {
        const btn = event.currentTarget;
        const action = btn.dataset.action;
        
        // Add loading state
        btn.classList.add('loading');
        btn.disabled = true;

        // Simulate action processing
        setTimeout(() => {
            btn.classList.remove('loading');
            btn.disabled = false;
            
            // Track action in analytics
            this.trackAction('quick_action', action);
        }, 1000);
    }

    /**
     * Handle notification action clicks
     */
    handleNotificationAction(event) {
        const link = event.currentTarget;
        const notificationId = link.dataset.notificationId;
        
        // Track notification interaction
        this.trackAction('notification_action', notificationId);
    }

    /**
     * Handle approval action clicks
     */
    handleApprovalAction(event) {
        const btn = event.currentTarget;
        const action = btn.textContent.toLowerCase();
        const bookingId = btn.dataset.bookingId;
        
        if (!bookingId) {
            console.error('Booking ID not found for approval action');
            return;
        }

        // Confirm action
        const confirmed = confirm(`Are you sure you want to ${action} this booking?`);
        if (!confirmed) return;

        // Add loading state
        btn.classList.add('loading');
        btn.disabled = true;

        // Make API call
        this.processApproval(bookingId, action)
            .then(response => {
                if (response.success) {
                    this.showNotification(`Booking ${action}d successfully`, 'success');
                    this.removeApprovalItem(bookingId);
                } else {
                    this.showNotification(response.message || 'Action failed', 'error');
                }
            })
            .catch(error => {
                console.error('Approval error:', error);
                this.showNotification('An error occurred while processing the request', 'error');
            })
            .finally(() => {
                btn.classList.remove('loading');
                btn.disabled = false;
            });
    }

    /**
     * Process approval via API
     */
    async processApproval(bookingId, action) {
        try {
            const response = await fetch(`${window.baseUrl}/app/api/booking_api.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: action === 'approve' ? 'approve_booking' : 'reject_booking',
                    booking_id: bookingId
                })
            });

            return await response.json();
        } catch (error) {
            throw new Error('Network error: ' + error.message);
        }
    }

    /**
     * Remove approval item from DOM
     */
    removeApprovalItem(bookingId) {
        const approvalItem = document.querySelector(`[data-booking-id="${bookingId}"]`).closest('.approval-item');
        if (approvalItem) {
            approvalItem.style.opacity = '0';
            setTimeout(() => {
                approvalItem.remove();
                this.checkEmptyApprovals();
            }, 300);
        }
    }

    /**
     * Check if approvals section is empty and hide if needed
     */
    checkEmptyApprovals() {
        const approvalsSection = document.querySelector('.pending-approvals');
        if (approvalsSection && approvalsSection.children.length === 0) {
            const section = approvalsSection.closest('.widget-section');
            if (section) {
                section.style.display = 'none';
            }
        }
    }

    /**
     * Start auto-refresh for dashboard data
     */
    startAutoRefresh() {
        // Refresh every 5 minutes
        this.refreshInterval = setInterval(() => {
            this.refreshDashboardData();
        }, 300000);
    }

    /**
     * Refresh dashboard data
     */
    async refreshDashboardData() {
        try {
            const response = await fetch(`${window.baseUrl}/app/api/dashboard_api.php?action=get_stats`);
            const data = await response.json();
            
            if (data.success) {
                this.updateStatistics(data.statistics);
                this.updateCharts(data.chartData);
            }
        } catch (error) {
            console.error('Dashboard refresh error:', error);
        }
    }

    /**
     * Update statistics cards
     */
    updateStatistics(statistics) {
        Object.keys(statistics).forEach(key => {
            const element = document.querySelector(`[data-stat="${key}"]`);
            if (element) {
                const value = statistics[key];
                element.textContent = typeof value === 'number' ? value.toLocaleString() : value;
                
                // Add animation
                element.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    element.style.transform = 'scale(1)';
                }, 200);
            }
        });
    }

    /**
     * Update charts with new data
     */
    updateCharts(chartData) {
        if (chartData.revenue && this.charts.revenue) {
            this.charts.revenue.data.datasets[0].data = chartData.revenue;
            this.charts.revenue.update('active');
        }
    }

    /**
     * Show notification to user
     */
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-icon">
                ${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}
            </div>
            <div class="notification-content">
                <p>${message}</p>
            </div>
        `;

        // Add to page
        const container = document.querySelector('.notifications') || document.body;
        container.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }

    /**
     * Track user actions for analytics
     */
    trackAction(action, details) {
        // Send to analytics API
        fetch(`${window.baseUrl}/app/api/analytics_api.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: action,
                details: details,
                user_id: window.currentUser.id,
                timestamp: new Date().toISOString()
            })
        }).catch(error => {
            console.error('Analytics tracking error:', error);
        });
    }

    /**
     * Initialize tooltips for dashboard elements
     */
    initializeTooltips() {
        // Add tooltips to stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            const title = card.querySelector('p').textContent;
            card.setAttribute('title', title);
        });

        // Add tooltips to quick actions
        document.querySelectorAll('.quick-action-btn').forEach(btn => {
            const title = btn.textContent.trim();
            btn.setAttribute('title', title);
        });
    }

    /**
     * Destroy dashboard manager and clean up
     */
    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        
        Object.values(this.charts).forEach(chart => {
            chart.destroy();
        });
    }
}

// Global functions for inline event handlers
window.approveBooking = function(bookingId) {
    const btn = document.querySelector(`[data-booking-id="${bookingId}"]`);
    if (btn) {
        btn.click();
    }
};

window.rejectBooking = function(bookingId) {
    const btn = document.querySelector(`[data-booking-id="${bookingId}"]`);
    if (btn) {
        btn.click();
    }
};

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.dashboardManager = new DashboardManager();
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (window.dashboardManager) {
        window.dashboardManager.destroy();
    }
});
