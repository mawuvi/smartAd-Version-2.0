<?php
if (!defined('SECURITY_CHECK')) { die('Direct access not allowed.'); }

// Get current user and RBAC data
$authGuard = new AuthGuard();
$currentUser = $authGuard->getCurrentUser();
$userRole = $authGuard->getUserRole();

// Get system information
$systemVersion = '6.0';
$currentYear = date('Y');
$serverStatus = 'Online'; // This could be dynamic
$lastBackup = date('M j, Y'); // This could be dynamic

// Get quick stats for admin/manager users
$quickStats = null;
if ($authGuard->hasRole(['admin', 'manager'])) {
    $dashboardModel = new DashboardModel($currentUser['id']);
    $statistics = $dashboardModel->getStatistics($currentUser['id'], $userRole);
    $quickStats = [
        'total_bookings' => $statistics['total_bookings'] ?? 0,
        'total_clients' => $statistics['total_clients'] ?? 0,
        'total_revenue' => $statistics['total_revenue'] ?? 0
    ];
}
?>

<footer class="footer">
    <div class="footer-content">
        <!-- Column 1: Company Info -->
        <div class="footer-column footer-company">
            <div class="footer-brand">
                <div class="brand-logo">📊</div>
                <div class="brand-text">
                    <h3 class="brand-name">smartAd</h3>
                    <p class="brand-tagline">Smart Advertising Management</p>
                </div>
            </div>
            <p class="company-description">
                Empowering businesses with intelligent advertising campaign management, 
                streamlined workflows, and comprehensive analytics.
            </p>
            <div class="social-links">
                <a href="#" class="social-link" title="Follow us on Twitter">
                    <span class="social-icon">🐦</span>
                </a>
                <a href="#" class="social-link" title="Connect on LinkedIn">
                    <span class="social-icon">💼</span>
                </a>
                <a href="#" class="social-link" title="Visit our website">
                    <span class="social-icon">🌐</span>
                </a>
            </div>
        </div>

        <!-- Column 2: Quick Links -->
        <div class="footer-column footer-links">
            <h4 class="footer-title">Quick Links</h4>
            <ul class="footer-menu">
                <li class="footer-menu-item">
                    <a href="<?php echo BASE_URL; ?>/app/pages/help.php" class="footer-link">
                        <span class="link-icon">❓</span>
                        Help & Documentation
                    </a>
                </li>
                <li class="footer-menu-item">
                    <a href="<?php echo BASE_URL; ?>/app/pages/api_docs.php" class="footer-link">
                        <span class="link-icon">📚</span>
                        API Documentation
                    </a>
                </li>
                <li class="footer-menu-item">
                    <a href="<?php echo BASE_URL; ?>/app/pages/support.php" class="footer-link">
                        <span class="link-icon">🎧</span>
                        Contact Support
                    </a>
                </li>
                <li class="footer-menu-item">
                    <a href="<?php echo BASE_URL; ?>/app/pages/privacy.php" class="footer-link">
                        <span class="link-icon">🔒</span>
                        Privacy Policy
                    </a>
                </li>
                <li class="footer-menu-item">
                    <a href="<?php echo BASE_URL; ?>/app/pages/terms.php" class="footer-link">
                        <span class="link-icon">📄</span>
                        Terms of Service
                    </a>
                </li>
                <li class="footer-menu-item">
                    <a href="<?php echo BASE_URL; ?>/app/pages/keyboard_shortcuts.php" class="footer-link">
                        <span class="link-icon">⌨️</span>
                        Keyboard Shortcuts
                    </a>
                </li>
            </ul>
        </div>

        <!-- Column 3: System Status -->
        <div class="footer-column footer-status">
            <h4 class="footer-title">System Status</h4>
            <div class="status-info">
                <div class="status-item">
                    <span class="status-label">Version:</span>
                    <span class="status-value"><?php echo $systemVersion; ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Status:</span>
                    <span class="status-value status-online"><?php echo $serverStatus; ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Last Backup:</span>
                    <span class="status-value"><?php echo $lastBackup; ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">User Role:</span>
                    <span class="status-value role-<?php echo $userRole; ?>"><?php echo ucfirst($userRole); ?></span>
                </div>
            </div>

            <?php if ($quickStats): ?>
                <div class="quick-stats">
                    <h5 class="stats-title">Quick Stats</h5>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <span class="stat-icon">📊</span>
                            <div class="stat-content">
                                <span class="stat-number"><?php echo number_format($quickStats['total_bookings']); ?></span>
                                <span class="stat-label">Bookings</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-icon">👥</span>
                            <div class="stat-content">
                                <span class="stat-number"><?php echo number_format($quickStats['total_clients']); ?></span>
                                <span class="stat-label">Clients</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-icon">💰</span>
                            <div class="stat-content">
                                <span class="stat-number">₵<?php echo number_format($quickStats['total_revenue'], 0); ?></span>
                                <span class="stat-label">Revenue</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="footer-actions">
                <button class="btn btn-sm btn-outline" id="refreshStatus" title="Refresh Status">
                    <span class="btn-icon">🔄</span>
                    Refresh
                </button>
                <button class="btn btn-sm btn-outline" id="toggleTheme" title="Toggle Theme">
                    <span class="btn-icon">🌙</span>
                    Theme
                </button>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom">
        <div class="footer-bottom-content">
            <div class="copyright">
                <span>&copy; <?php echo $currentYear; ?> smartAd. All rights reserved.</span>
            </div>
            <div class="footer-meta">
                <?php if ($authGuard->hasRole(['admin'])): ?>
                    <span class="meta-item">
                        <span class="meta-icon">⏱️</span>
                        <span class="meta-text">Uptime: 99.9%</span>
                    </span>
                <?php endif; ?>
                <span class="meta-item">
                    <span class="meta-icon">❤️</span>
                    <span class="meta-text">Made with love in Ghana</span>
                </span>
                <span class="meta-item">
                    <span class="meta-icon">🚀</span>
                    <span class="meta-text">Powered by smartAd</span>
                </span>
            </div>
        </div>
    </div>
</footer>

<!-- Loading Indicator -->
<div class="loading-indicator" id="loadingIndicator">
    <div class="loading-spinner"></div>
    <div class="loading-text">Loading...</div>
</div>

<!-- Toast Notifications -->
<div class="toast-container" id="toastContainer"></div>

<script>
// Pass footer data to JavaScript
window.footerData = {
    systemVersion: '<?php echo $systemVersion; ?>',
    serverStatus: '<?php echo $serverStatus; ?>',
    lastBackup: '<?php echo $lastBackup; ?>',
    userRole: '<?php echo $userRole; ?>',
    quickStats: <?php echo json_encode($quickStats); ?>
};
</script>
