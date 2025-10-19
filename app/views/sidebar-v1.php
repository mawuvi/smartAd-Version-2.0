<?php
if (!defined('SECURITY_CHECK')) { die('Direct access not allowed.'); }

// Get current user and RBAC data
$authGuard = new AuthGuard();
$currentUser = $authGuard->getCurrentUser();
$userRole = $authGuard->getUserRole();
$userId = $currentUser['id'];

// Get dashboard model for counts
$dashboardModel = new DashboardModel($userId);

// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentPath = $_SERVER['REQUEST_URI'];

// Define menu structure with RBAC permissions
$menuItems = [
    'dashboard' => [
        'title' => 'Dashboard',
        'icon' => '🎯',
        'url' => '/app/pages/dashboard.php',
        'permission' => 'dashboard.view',
        'active' => ($currentPage === 'dashboard')
    ],
    'create' => [
        'title' => 'Quick Create',
        'icon' => '⚡',
        'permission' => null, // Parent item, check children
        'children' => [
            'new_booking' => [
                'title' => 'New Booking',
                'icon' => '📊',
                'url' => '/app/pages/booking.php?action=new',
                'permission' => 'bookings.create',
                'active' => (strpos($currentPath, 'booking.php') !== false && isset($_GET['action']) && $_GET['action'] === 'new')
            ],
            'new_client' => [
                'title' => 'New Client',
                'icon' => '👤',
                'url' => '/app/pages/clients.php?action=new',
                'permission' => 'clients.create',
                'active' => (strpos($currentPath, 'clients.php') !== false && isset($_GET['action']) && $_GET['action'] === 'new')
            ],
            'new_rate' => [
                'title' => 'New Rate',
                'icon' => '💰',
                'url' => '/app/pages/rates.php?action=new',
                'permission' => 'rates.create',
                'active' => (strpos($currentPath, 'rates.php') !== false && isset($_GET['action']) && $_GET['action'] === 'new')
            ],
            'new_publication' => [
                'title' => 'New Publication',
                'icon' => '📰',
                'url' => '/app/pages/publications.php?action=new',
                'permission' => 'publications.create',
                'active' => (strpos($currentPath, 'publications.php') !== false && isset($_GET['action']) && $_GET['action'] === 'new')
            ]
        ]
    ],
    'manage' => [
        'title' => 'Manage',
        'icon' => '📋',
        'permission' => null, // Parent item, check children
        'children' => [
            'bookings' => [
                'title' => 'Bookings',
                'icon' => '📊',
                'url' => '/app/pages/bookings.php',
                'permission' => 'bookings.view',
                'active' => ($currentPage === 'bookings' || $currentPage === 'booking'),
                'badge' => null // Will be populated with draft count
            ],
            'clients' => [
                'title' => 'Clients',
                'icon' => '👥',
                'url' => '/app/pages/clients.php',
                'permission' => 'clients.view',
                'active' => ($currentPage === 'clients'),
                'badge' => null
            ],
            'rates' => [
                'title' => 'Rates',
                'icon' => '💵',
                'url' => '/app/pages/rates.php',
                'permission' => 'rates.view',
                'active' => ($currentPage === 'rates'),
                'badge' => null
            ],
            'publications' => [
                'title' => 'Publications',
                'icon' => '📰',
                'url' => '/app/pages/publications.php',
                'permission' => 'publications.view',
                'active' => ($currentPage === 'publications'),
                'badge' => null
            ]
        ]
    ],
    'reports' => [
        'title' => 'Reports',
        'icon' => '📈',
        'permission' => null, // Parent item, check children
        'children' => [
            'analytics' => [
                'title' => 'Analytics',
                'icon' => '📊',
                'url' => '/app/pages/analytics.php',
                'permission' => 'reports.view',
                'active' => ($currentPage === 'analytics')
            ],
            'exports' => [
                'title' => 'Exports',
                'icon' => '📥',
                'url' => '/app/pages/exports.php',
                'permission' => 'reports.export',
                'active' => ($currentPage === 'exports')
            ],
            'audit_logs' => [
                'title' => 'Audit Logs',
                'icon' => '📋',
                'url' => '/app/pages/audit_logs.php',
                'permission' => 'system.logs',
                'active' => ($currentPage === 'audit_logs')
            ]
        ]
    ],
    'settings' => [
        'title' => 'Settings',
        'icon' => '⚙️',
        'permission' => null, // Parent item, check children
        'children' => [
            'users' => [
                'title' => 'Users',
                'icon' => '👤',
                'url' => '/app/pages/users.php',
                'permission' => 'users.view',
                'active' => ($currentPage === 'users'),
                'badge' => null
            ],
            'permissions' => [
                'title' => 'Permissions',
                'icon' => '🔐',
                'url' => '/app/pages/permissions.php',
                'permission' => 'users.manage',
                'active' => ($currentPage === 'permissions')
            ],
            'system' => [
                'title' => 'System',
                'icon' => '⚙️',
                'url' => '/app/pages/system.php',
                'permission' => 'system.settings',
                'active' => ($currentPage === 'system')
            ]
        ]
    ]
];

// Get badge counts for menu items
$statistics = $dashboardModel->getStatistics($userId, $userRole);
$draftBookingsCount = $statistics['draft_bookings'] ?? 0;
$pendingApprovalsCount = 0;

if ($authGuard->hasRole(['admin', 'manager'])) {
    $pendingApprovals = $dashboardModel->getPendingApprovals($userId, $userRole);
    $pendingApprovalsCount = count($pendingApprovals);
}

// Update badge counts
if (isset($menuItems['manage']['children']['bookings'])) {
    $menuItems['manage']['children']['bookings']['badge'] = $draftBookingsCount > 0 ? $draftBookingsCount : null;
}

if (isset($menuItems['settings']['children']['users']) && $pendingApprovalsCount > 0) {
    $menuItems['settings']['children']['users']['badge'] = $pendingApprovalsCount;
}
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-content">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <div class="brand-logo">📊</div>
                <div class="brand-text">
                    <span class="brand-name">smartAd</span>
                    <span class="brand-tagline">Smart Advertising</span>
                </div>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                <span class="toggle-icon">◀</span>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
            <ul class="nav-menu">
                <?php foreach ($menuItems as $key => $item): ?>
                    <?php
                    // Check if user has permission for this item
                    $hasPermission = true;
                    if ($item['permission']) {
                        $hasPermission = $authGuard->hasPermission($item['permission']);
                    } elseif (isset($item['children'])) {
                        // For parent items, check if user has permission for any child
                        $hasPermission = false;
                        foreach ($item['children'] as $child) {
                            if ($authGuard->hasPermission($child['permission'])) {
                                $hasPermission = true;
                                break;
                            }
                        }
                    }
                    
                    if (!$hasPermission) continue;
                    ?>
                    
                    <li class="nav-item <?php echo isset($item['active']) && $item['active'] ? 'active' : ''; ?>">
                        <?php if (isset($item['children'])): ?>
                            <!-- Parent Item with Children -->
                            <div class="nav-item-header" data-toggle="collapse" data-target="#nav-<?php echo $key; ?>">
                                <span class="nav-icon"><?php echo $item['icon']; ?></span>
                                <span class="nav-text"><?php echo $item['title']; ?></span>
                                <span class="nav-arrow">▼</span>
                            </div>
                            <ul class="nav-submenu collapse" id="nav-<?php echo $key; ?>">
                                <?php foreach ($item['children'] as $childKey => $child): ?>
                                    <?php if ($authGuard->hasPermission($child['permission'])): ?>
                                        <li class="nav-subitem <?php echo isset($child['active']) && $child['active'] ? 'active' : ''; ?>">
                                            <a href="<?php echo BASE_URL . $child['url']; ?>" class="nav-link">
                                                <span class="nav-icon"><?php echo $child['icon']; ?></span>
                                                <span class="nav-text"><?php echo $child['title']; ?></span>
                                                <?php if (isset($child['badge']) && $child['badge']): ?>
                                                    <span class="nav-badge"><?php echo $child['badge']; ?></span>
                                                <?php endif; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <!-- Single Item -->
                            <a href="<?php echo BASE_URL . $item['url']; ?>" class="nav-link">
                                <span class="nav-icon"><?php echo $item['icon']; ?></span>
                                <span class="nav-text"><?php echo $item['title']; ?></span>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1)); ?>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></div>
                    <div class="user-role role-<?php echo $userRole; ?>"><?php echo ucfirst($userRole); ?></div>
                </div>
            </div>
            
            <!-- Quick Stats (for managers/admins) -->
            <?php if ($authGuard->hasRole(['admin', 'manager'])): ?>
                <div class="sidebar-stats">
                    <div class="stat-item">
                        <span class="stat-label">Draft Bookings:</span>
                        <span class="stat-value"><?php echo $draftBookingsCount; ?></span>
                    </div>
                    <?php if ($pendingApprovalsCount > 0): ?>
                        <div class="stat-item">
                            <span class="stat-label">Pending:</span>
                            <span class="stat-value stat-warning"><?php echo $pendingApprovalsCount; ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</aside>

<!-- Mobile Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
// Pass menu data to JavaScript
window.menuItems = <?php echo json_encode($menuItems); ?>;
window.currentPage = '<?php echo $currentPage; ?>';
window.currentPath = '<?php echo $currentPath; ?>';
</script>
