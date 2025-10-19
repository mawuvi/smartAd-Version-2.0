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

$pageTitle = $pageTitle ?? 'smartAd';
?>

<!-- Include core CSS for consistent styling across all pages -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/pages/dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<header class="header">
    <div class="header-content">
        <a href="<?php echo BASE_URL; ?>/app/pages/dashboard.php" class="brand">
                <div class="brand-logo">📊</div>
            <div class="brand-text">smartAd</div>
        </a>
        
        <nav class="header-nav">
            <div class="nav-links">
                <a href="<?php echo BASE_URL; ?>/app/pages/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                <?php if ($authGuard->hasPermission('bookings.view')): ?>
                    <a href="<?php echo BASE_URL; ?>/app/pages/bookings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'bookings.php' ? 'active' : ''; ?>">Bookings</a>
                    <?php endif; ?>
                <?php if ($authGuard->hasPermission('clients.view')): ?>
                    <a href="<?php echo BASE_URL; ?>/app/pages/clients.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'clients.php' ? 'active' : ''; ?>">Clients</a>
                    <?php endif; ?>
                <?php if ($authGuard->hasPermission('reports.view')): ?>
                    <a href="<?php echo BASE_URL; ?>/app/pages/analytics.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'analytics.php' ? 'active' : ''; ?>">Reports</a>
                    <?php endif; ?>
                <?php if ($authGuard->hasPermission('system.settings')): ?>
                    <a href="<?php echo BASE_URL; ?>/app/pages/setup.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'setup.php' ? 'active' : ''; ?>">Setup</a>
                    <?php endif; ?>
                </div>
            
            <div class="header-actions">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="search" class="search-input" placeholder="Search..." id="globalSearch">
            </div>

            <!-- Notifications -->
            <div class="notifications-dropdown">
                    <button class="notifications-btn" id="notificationsBtn">
                        <i class="fas fa-bell"></i>
                    <?php if ($notificationCount > 0): ?>
                        <span class="notification-badge"><?php echo $notificationCount; ?></span>
                    <?php endif; ?>
                </button>
                    <div class="notifications-menu" id="notificationsMenu">
                        <div class="notifications-header">
                        <h4>Notifications</h4>
                            <button class="mark-all-read" id="markAllRead">
                                <i class="fas fa-check-double"></i> Mark all as read
                            </button>
                    </div>
                    <div class="notifications-list">
                        <?php if (empty($notifications)): ?>
                                <div class="notification-item no-notifications">
                                    <i class="fas fa-bell-slash"></i>
                                    <p>No new notifications</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                    <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>" data-id="<?php echo $notification['id']; ?>">
                                        <div class="notification-icon">
                                            <i class="<?php echo htmlspecialchars($notification['icon']); ?>"></i>
                                        </div>
                                    <div class="notification-content">
                                        <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                        <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                                            <div class="notification-time"><?php echo htmlspecialchars($notification['time_ago']); ?></div>
                                    </div>
                                        <?php if (!$notification['is_read']): ?>
                                            <div class="notification-dot"></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                        <div class="notifications-footer">
                            <a href="<?php echo BASE_URL; ?>/app/pages/notifications.php" class="view-all-notifications">
                                <i class="fas fa-external-link-alt"></i> View all notifications
                            </a>
                        </div>
                </div>
            </div>

                <div class="dropdown">
                    <div class="user-profile">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></div>
                            <div class="user-role"><?php echo ucfirst($userRole); ?></div>
                        </div>
                        <i class="fas fa-chevron-down" style="font-size: 0.75rem; color: var(--text-muted); margin-left: 0.5rem;"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-item profile-header">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1)); ?>
                            </div>
                            <div class="user-info">
                                <div class="user-name"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></div>
                                <div class="user-role"><?php echo ucfirst($userRole); ?></div>
                            </div>
                        </li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/app/pages/profile.php">
                            <i class="fas fa-user"></i> Profile
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/app/pages/preferences.php">
                            <i class="fas fa-cog"></i> Settings
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item logout" href="#" id="logoutBtn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a></li>
                    </ul>
            </div>
        </div>
        </nav>
    </div>
</header>

<script>
// Pass data to JavaScript
window.currentUser = <?php echo json_encode($currentUser); ?>;
window.userRole = '<?php echo $userRole; ?>';
window.baseUrl = '<?php echo BASE_URL; ?>';
window.notificationCount = <?php echo $notificationCount; ?>;
window.notifications = <?php echo json_encode($notifications); ?>;

// Dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const userProfile = document.querySelector('.user-profile');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    
    if (userProfile && dropdownMenu) {
        userProfile.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close notifications menu if open
            const notificationsMenu = document.getElementById('notificationsMenu');
            if (notificationsMenu) {
                notificationsMenu.classList.remove('show');
            }
            
            // Toggle dropdown
            dropdownMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userProfile.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
        
        // Close dropdown on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdownMenu.classList.remove('show');
            }
        });
    }
    
    // Notifications functionality
    const notificationsBtn = document.getElementById('notificationsBtn');
    const notificationsMenu = document.getElementById('notificationsMenu');
    
    if (notificationsBtn && notificationsMenu) {
        notificationsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close user dropdown if open
            if (dropdownMenu) {
                dropdownMenu.classList.remove('show');
            }
            
            // Toggle notifications menu
            notificationsMenu.classList.toggle('show');
        });
        
        // Close notifications menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationsBtn.contains(e.target) && !notificationsMenu.contains(e.target)) {
                notificationsMenu.classList.remove('show');
            }
        });
        
        // Mark notification as read when clicked
        const notificationItems = document.querySelectorAll('.notification-item[data-id]');
        notificationItems.forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                markNotificationAsRead(notificationId);
                this.classList.remove('unread');
                this.classList.add('read');
                
                // Remove notification dot
                const dot = this.querySelector('.notification-dot');
                if (dot) {
                    dot.remove();
                }
                
                // Update badge count
                updateNotificationBadge();
            });
        });
        
        // Mark all as read functionality
        const markAllReadBtn = document.getElementById('markAllRead');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.preventDefault();
                markAllNotificationsAsRead();
                
                // Update all notification items
                notificationItems.forEach(item => {
                    item.classList.remove('unread');
                    item.classList.add('read');
                    const dot = item.querySelector('.notification-dot');
                    if (dot) {
                        dot.remove();
                    }
                });
                
                // Update badge count
                updateNotificationBadge();
            });
        }
    }
    
    // Logout functionality
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = window.baseUrl + '/public/api_logout.php';
            }
        });
    }
});

// Notification functions
function markNotificationAsRead(notificationId) {
    fetch(window.baseUrl + '/app/api/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'mark_read',
            notification_id: notificationId
        })
    }).catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllNotificationsAsRead() {
    fetch(window.baseUrl + '/app/api/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'mark_all_read'
        })
    }).catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

function updateNotificationBadge() {
    const badge = document.querySelector('.notification-badge');
    const unreadCount = document.querySelectorAll('.notification-item.unread').length;
    
    if (unreadCount > 0) {
        if (badge) {
            badge.textContent = unreadCount;
        } else {
            // Create badge if it doesn't exist
            const notificationsBtn = document.getElementById('notificationsBtn');
            if (notificationsBtn) {
                const newBadge = document.createElement('span');
                newBadge.className = 'notification-badge';
                newBadge.textContent = unreadCount;
                notificationsBtn.appendChild(newBadge);
            }
        }
    } else {
        // Remove badge if no unread notifications
        if (badge) {
            badge.remove();
        }
    }
}
</script>