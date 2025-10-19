<?php
require_once __DIR__ . '/../../bootstrap.php';

// Get AuthGuard instance for RBAC checks
$authGuard = new AuthGuard();

// Require dashboard access permission
$authGuard->requirePermission('dashboard.view');

// Get current user and role
$currentUser = $authGuard->getCurrentUser();
$userRole = $authGuard->getUserRole();
$userId = $currentUser['id'];

// Initialize dashboard model
$dashboardModel = new DashboardModel($userId);

// Get role-specific dashboard data
$statistics = $dashboardModel->getStatistics($userId, $userRole);
$recentActivity = $dashboardModel->getRecentActivity($userId, $userRole, 5);
$quickActions = $dashboardModel->getQuickActions($userId, $userRole);
$notifications = $dashboardModel->getNotifications($userId);
$pendingApprovals = $dashboardModel->getPendingApprovals($userId, $userRole);
$revenueChartData = $dashboardModel->getRevenueChartData($userId, $userRole, 6);
$topClients = $dashboardModel->getTopClients($userId, $userRole, 5);
$menuItems = $dashboardModel->getDashboardMenuItems($userId);

// Get session helper for flash messages
$sessionHelper = $authGuard->getSessionHelper();
$flashMessages = $sessionHelper->getFlashMessages();

$pageTitle = ucfirst($userRole) . ' Dashboard';

// Include header
include __DIR__ . '/../views/header.php';
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Main Content -->
<main class="main-content">
    <!-- Flash Messages -->
    <?php if (!empty($flashMessages)): ?>
        <div class="flash-messages">
            <?php foreach ($flashMessages as $type => $messages): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="alert alert-<?php echo $type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="welcome-section">
            <h1>Welcome back, <?php echo htmlspecialchars($currentUser['first_name']); ?>!</h1>
            <p>Here's what's happening with your campaigns today.</p>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <?php foreach ($quickActions as $action): ?>
                <a href="<?php echo htmlspecialchars($action['url']); ?>" class="quick-action-btn">
                    <i class="<?php echo htmlspecialchars($action['icon']); ?>"></i>
                    <span><?php echo htmlspecialchars($action['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <?php foreach ($statistics as $stat): ?>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="<?php echo htmlspecialchars($stat['icon']); ?>"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo htmlspecialchars($stat['value']); ?></div>
                    <div class="stat-label"><?php echo htmlspecialchars($stat['label']); ?></div>
                    <?php if (isset($stat['change'])): ?>
                        <div class="stat-change <?php echo $stat['change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="fas fa-arrow-<?php echo $stat['change'] >= 0 ? 'up' : 'down'; ?>"></i>
                            <?php echo abs($stat['change']); ?>%
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="dashboard-grid">
        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h3>Recent Activity</h3>
                <a href="<?php echo BASE_URL; ?>/app/pages/activity.php" class="card-action">View All</a>
            </div>
            <div class="card-body">
                <div class="activity-list">
                    <?php foreach ($recentActivity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="<?php echo htmlspecialchars($activity['icon']); ?>"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text"><?php echo htmlspecialchars($activity['description']); ?></div>
                                <div class="activity-time"><?php echo htmlspecialchars($activity['time']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Top Clients -->
        <div class="card">
            <div class="card-header">
                <h3>Top Clients</h3>
                <a href="<?php echo BASE_URL; ?>/app/pages/clients.php" class="card-action">View All</a>
            </div>
            <div class="card-body">
                <div class="clients-list">
                    <?php foreach ($topClients as $client): ?>
                        <div class="client-item">
                            <div class="client-avatar">
                                <?php echo strtoupper(substr($client['name'], 0, 2)); ?>
                            </div>
                            <div class="client-info">
                                <div class="client-name"><?php echo htmlspecialchars($client['name']); ?></div>
                                <div class="client-revenue">₵<?php echo number_format($client['revenue']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <?php if (!empty($pendingApprovals)): ?>
            <div class="card">
                <div class="card-header">
                    <h3>Pending Approvals</h3>
                    <span class="badge"><?php echo count($pendingApprovals); ?></span>
                </div>
                <div class="card-body">
                    <div class="approvals-list">
                        <?php foreach ($pendingApprovals as $approval): ?>
                            <div class="approval-item">
                                <div class="approval-content">
                                    <div class="approval-title"><?php echo htmlspecialchars($approval['title']); ?></div>
                                    <div class="approval-meta"><?php echo htmlspecialchars($approval['meta']); ?></div>
                                </div>
                                <div class="approval-actions">
                                    <button class="btn-sm btn-success" onclick="approveItem(<?php echo $approval['id']; ?>)">Approve</button>
                                    <button class="btn-sm btn-danger" onclick="rejectItem(<?php echo $approval['id']; ?>)">Reject</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Revenue Chart -->
        <?php if (!empty($revenueChartData)): ?>
            <div class="card chart-card">
                <div class="card-header">
                    <h3>Revenue Trend</h3>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Core JavaScript -->
<script src="<?php echo BASE_URL; ?>/public/js/modal.js"></script>

<!-- Navigation JavaScript -->
<script src="<?php echo BASE_URL; ?>/public/js/components/navigation.js"></script>
<script src="<?php echo BASE_URL; ?>/public/js/components/userMenu.js"></script>
<script src="<?php echo BASE_URL; ?>/public/js/components/search.js"></script>
<script src="<?php echo BASE_URL; ?>/public/js/components/notifications.js"></script>

<!-- Page JavaScript -->
<script src="<?php echo BASE_URL; ?>/public/js/pages/dashboard.js"></script>
<script src="<?php echo BASE_URL; ?>/public/js/modules/rbacModule.js"></script>

<script>
    // Pass data to JavaScript
    window.currentUser = <?php echo json_encode($currentUser); ?>;
    window.userRole = '<?php echo $userRole; ?>';
    window.baseUrl = '<?php echo BASE_URL; ?>';
    window.revenueChartData = <?php echo json_encode($revenueChartData); ?>;
    window.quickActions = <?php echo json_encode($quickActions); ?>;
    window.menuItems = <?php echo json_encode($menuItems); ?>;
    window.dashboardData = {
        statistics: <?php echo json_encode($statistics); ?>,
        recentActivity: <?php echo json_encode($recentActivity); ?>,
        notifications: <?php echo json_encode($notifications); ?>,
        pendingApprovals: <?php echo json_encode($pendingApprovals); ?>,
        topClients: <?php echo json_encode($topClients); ?>
    };
</script>

<?php include __DIR__ . '/../views/footer.php'; ?>