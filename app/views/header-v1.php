<?php
if (!defined('SECURITY_CHECK')) { die('Direct access not allowed.'); }

// Get current user and RBAC data
$authGuard = new AuthGuard();
$currentUser = $authGuard->getCurrentUser();
$userRole = $authGuard->getUserRole();
$userId = $currentUser['id'];

// Get dashboard model for notifications and counts
$dashboardModel = new DashboardModel($userId);
$notifications = $dashboardModel->getNotifications($userId);
$notificationCount = count($notifications);

// Get pending approvals count for managers/admins
$pendingApprovalsCount = 0;
if ($authGuard->hasRole(['admin', 'manager'])) {
    $pendingApprovals = $dashboardModel->getPendingApprovals($userId, $userRole);
    $pendingApprovalsCount = count($pendingApprovals);
}

$pageTitle = $pageTitle ?? 'smartAd';
?>

<header class="header">
    <div class="header-content">
        <!-- Logo and Brand -->
        <div class="header-brand">
            <a href="<?php echo BASE_URL; ?>/app/pages/dashboard.php" class="brand-link">
                <div class="brand-logo">📊</div>
                <div class="brand-text">
                    <span class="brand-name">smartAd</span>
                    <span class="brand-tagline">Smart Advertising</span>
                </div>
            </a>
        </div>

        <!-- Global Search -->
        <div class="header-search">
            <div class="search-container">
                <input type="text" 
                       id="globalSearch" 
                       class="search-input" 
                       placeholder="Search bookings, clients, rates..." 
                       autocomplete="off">
                <div class="search-icon">🔍</div>
                <div class="search-results" id="searchResults"></div>
            </div>
            <div class="search-shortcut">Press <kbd>/</kbd> to search</div>
        </div>

        <!-- Header Actions -->
        <div class="header-actions">
            <!-- Quick Actions Dropdown -->
            <div class="quick-actions-dropdown">
                <button class="btn btn-primary btn-icon" id="quickActionsBtn" title="Quick Actions">
                    <span class="btn-icon-text">+</span>
                    <span class="btn-text">New</span>
                </button>
                <div class="dropdown-menu" id="quickActionsMenu">
                    <?php if ($authGuard->hasPermission('bookings.create')): ?>
                        <a href="<?php echo BASE_URL; ?>/app/pages/booking.php?action=new" class="dropdown-item">
                            <span class="item-icon">📊</span>
                            <span class="item-text">New Booking</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($authGuard->hasPermission('clients.create')): ?>
                        <a href="<?php echo BASE_URL; ?>/app/pages/clients.php?action=new" class="dropdown-item">
                            <span class="item-icon">👤</span>
                            <span class="item-text">New Client</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($authGuard->hasPermission('rates.create')): ?>
                        <a href="<?php echo BASE_URL; ?>/app/pages/rates.php?action=new" class="dropdown-item">
                            <span class="item-icon">💰</span>
                            <span class="item-text">New Rate</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($authGuard->hasPermission('publications.create')): ?>
                        <a href="<?php echo BASE_URL; ?>/app/pages/publications.php?action=new" class="dropdown-item">
                            <span class="item-icon">📰</span>
                            <span class="item-text">New Publication</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notifications -->
            <div class="notifications-dropdown">
                <button class="btn btn-icon notifications-btn" id="notificationsBtn" title="Notifications">
                    <span class="btn-icon-text">🔔</span>
                    <?php if ($notificationCount > 0): ?>
                        <span class="notification-badge"><?php echo $notificationCount; ?></span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu notifications-menu" id="notificationsMenu">
                    <div class="dropdown-header">
                        <h4>Notifications</h4>
                        <?php if ($notificationCount > 0): ?>
                            <button class="btn btn-sm btn-link" id="markAllRead">Mark all read</button>
                        <?php endif; ?>
                    </div>
                    <div class="notifications-list">
                        <?php if (empty($notifications)): ?>
                            <div class="notification-item notification-empty">
                                <span class="notification-icon">✅</span>
                                <span class="notification-text">No new notifications</span>
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item notification-<?php echo $notification['type']; ?>">
                                    <span class="notification-icon">
                                        <?php echo $notification['type'] === 'warning' ? '⚠️' : 'ℹ️'; ?>
                                    </span>
                                    <div class="notification-content">
                                        <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                        <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                                        <div class="notification-time"><?php echo date('M j, g:i A', strtotime($notification['created_at'])); ?></div>
                                    </div>
                                    <?php if (isset($notification['action_url'])): ?>
                                        <a href="<?php echo BASE_URL . $notification['action_url']; ?>" class="notification-action">View</a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Dark Mode Toggle -->
            <button class="btn btn-icon theme-toggle" id="themeToggle" title="Toggle Dark Mode">
                <span class="btn-icon-text">🌙</span>
            </button>

            <!-- User Profile Dropdown -->
            <div class="user-profile-dropdown">
                <button class="user-profile-btn" id="userProfileBtn">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></div>
                        <div class="user-role role-<?php echo $userRole; ?>"><?php echo ucfirst($userRole); ?></div>
                    </div>
                    <div class="dropdown-arrow">▼</div>
                </button>
                <div class="dropdown-menu user-menu" id="userMenu">
                    <div class="dropdown-header">
                        <div class="user-profile-summary">
                            <div class="user-avatar-large">
                                <?php echo strtoupper(substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1)); ?>
                            </div>
                            <div class="user-details">
                                <div class="user-name"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></div>
                                <div class="user-email"><?php echo htmlspecialchars($currentUser['email']); ?></div>
                                <div class="user-role role-<?php echo $userRole; ?>"><?php echo ucfirst($userRole); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo BASE_URL; ?>/app/pages/profile.php" class="dropdown-item">
                        <span class="item-icon">👤</span>
                        <span class="item-text">Profile Settings</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/app/pages/preferences.php" class="dropdown-item">
                        <span class="item-icon">⚙️</span>
                        <span class="item-text">Preferences</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/app/pages/help.php" class="dropdown-item">
                        <span class="item-icon">❓</span>
                        <span class="item-text">Help & Support</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" id="logoutBtn" class="dropdown-item dropdown-item-danger">
                        <span class="item-icon">🚪</span>
                        <span class="item-text">Logout</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="btn btn-icon mobile-menu-toggle" id="mobileMenuToggle" title="Toggle Menu">
            <span class="btn-icon-text">☰</span>
        </button>
    </div>

    <!-- Breadcrumbs -->
    <div class="breadcrumbs-container">
        <nav class="breadcrumbs" id="breadcrumbs">
            <a href="<?php echo BASE_URL; ?>/app/pages/dashboard.php" class="breadcrumb-item">
                <span class="breadcrumb-icon">🏠</span>
                <span class="breadcrumb-text">Dashboard</span>
            </a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current"><?php echo htmlspecialchars($pageTitle); ?></span>
        </nav>
    </div>
</header>

<!-- Mobile Overlay -->
<div class="mobile-overlay" id="mobileOverlay"></div>

<script>
// Pass data to JavaScript
window.currentUser = <?php echo json_encode($currentUser); ?>;
window.userRole = '<?php echo $userRole; ?>';
window.baseUrl = '<?php echo BASE_URL; ?>';
window.notificationCount = <?php echo $notificationCount; ?>;
window.pendingApprovalsCount = <?php echo $pendingApprovalsCount; ?>;
</script>
