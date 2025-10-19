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

        <div class="page-header">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back! Here's your advertising overview.</p>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <?php if ($authGuard->hasPermission('bookings.view')): ?>
                <div class="quick-stat">
                    <div class="stat-icon blue">📊</div>
                    <div class="stat-value"><?php echo number_format($statistics['total_bookings'] ?? 0); ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
            <?php endif; ?>
            
            <?php if ($authGuard->hasPermission('bookings.view')): ?>
                <div class="quick-stat">
                    <div class="stat-icon green">✅</div>
                    <div class="stat-value"><?php echo number_format($statistics['confirmed_bookings'] ?? 0); ?></div>
                    <div class="stat-label">Confirmed</div>
                </div>
            <?php endif; ?>
            
            <?php if ($authGuard->hasPermission('bookings.view')): ?>
                <div class="quick-stat">
                    <div class="stat-icon orange">⏳</div>
                    <div class="stat-value"><?php echo number_format($statistics['draft_bookings'] ?? 0); ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            <?php endif; ?>
            
            <?php if ($authGuard->hasPermission('bookings.view')): ?>
                <div class="quick-stat">
                    <div class="stat-icon red">💰</div>
                    <div class="stat-value">₵<?php echo number_format($statistics['total_revenue'] ?? 0); ?></div>
                    <div class="stat-label">Revenue</div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Main Grid -->
        <div class="main-grid">
            <!-- Recent Activity -->
            <?php if (!empty($recentActivity)): ?>
                <div class="main-card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activity</h3>
                        <a href="#" class="card-action">View All</a>
                    </div>
                    
                    <?php foreach ($recentActivity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon <?php echo $activity['type']; ?>">
                                <?php echo $activity['type'] === 'booking' ? '📊' : '👥'; ?>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></div>
                                <div class="activity-desc"><?php echo htmlspecialchars($activity['description']); ?></div>
                            </div>
                            <div class="activity-time"><?php echo date('g:i A', strtotime($activity['created_at'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Sidebar -->
            <div>
                <!-- Performance Metrics -->
                <div class="sidebar-card">
                    <h4 class="card-title">Performance</h4>
                    <div class="metric-item">
                        <span class="metric-label">Conversion Rate</span>
                        <div>
                            <span class="metric-value">12.5%</span>
                            <span class="metric-change positive">+2.1%</span>
                        </div>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Click Rate</span>
                        <div>
                            <span class="metric-value">3.2%</span>
                            <span class="metric-change positive">+0.8%</span>
                        </div>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Bounce Rate</span>
                        <div>
                            <span class="metric-value">45.2%</span>
                            <span class="metric-change negative">-1.2%</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="sidebar-card">
                    <h4 class="card-title">Quick Actions</h4>
                    <div class="d-grid gap-2">
                        <?php if ($authGuard->hasPermission('calculator.use')): ?>
                            <button class="btn btn-primary" data-action="open-calculator">
                                <i class="fas fa-calculator"></i> Rate Calculator
                            </button>
                        <?php endif; ?>
                        
                        <?php if (!empty($quickActions)): ?>
                            <?php foreach ($quickActions as $action): ?>
                                <a href="<?php echo BASE_URL . $action['url']; ?>" class="btn btn-primary"><?php echo $action['title']; ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Approvals (Managers/Admins only) -->
                <?php if (!empty($pendingApprovals) && in_array($userRole, ['manager', 'admin'])): ?>
                    <div class="sidebar-card">
                        <h4 class="card-title">Pending Approvals</h4>
                        <?php foreach ($pendingApprovals as $approval): ?>
                            <div class="metric-item">
                                <div>
                                    <div class="metric-label"><?php echo htmlspecialchars($approval['title']); ?></div>
                                    <div class="metric-value">₵<?php echo number_format($approval['amount'], 2); ?></div>
                                </div>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-success" onclick="approveBooking(<?php echo $approval['id']; ?>)">✓</button>
                                    <button class="btn btn-sm btn-danger" onclick="rejectBooking(<?php echo $approval['id']; ?>)">✗</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Revenue Chart -->
        <?php if (!empty($revenueChartData) && $authGuard->hasPermission('bookings.view')): ?>
            <div class="main-card">
                <div class="card-header">
                    <h3 class="card-title">Revenue Trend</h3>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Modal and Rate Calculator CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/components/modal.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/components/rate-calculator.css">
    
    <!-- Core JavaScript -->
    <script src="<?php echo BASE_URL; ?>/public/js/modal.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/js/modules/rateCalculator.js"></script>
    
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
        window.dashboardData = {
            statistics: <?php echo json_encode($statistics); ?>,
            recentActivity: <?php echo json_encode($recentActivity); ?>,
            notifications: <?php echo json_encode($notifications); ?>,
            pendingApprovals: <?php echo json_encode($pendingApprovals); ?>,
            topClients: <?php echo json_encode($topClients); ?>
        };
    </script>

    <?php include __DIR__ . '/../views/footer.php'; ?>