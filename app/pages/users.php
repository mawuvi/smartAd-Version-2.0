<?php
require_once __DIR__ . '/../../bootstrap.php';

// Check authentication and permissions
$authGuard = new AuthGuard();
$authGuard->requirePermission('users.view');

// Get current user
$currentUser = $authGuard->getCurrentUser();

// Include header
include __DIR__ . '/../views/header.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">Manage system users and their permissions</p>
    </div>

    <div class="users-container">
        <div class="card">
            <div class="card-header">
                <h3>User Management</h3>
            </div>
            <div class="card-body">
                <p>User management functionality will be implemented here.</p>
            </div>
        </div>
    </div>
</div>

<!-- Include page-specific CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/pages/users.css">

<!-- Include required JavaScript -->
<script src="<?php echo BASE_URL; ?>/public/js/pages/users.js"></script>

<script>
window.baseUrl = '<?php echo BASE_URL; ?>';
window.currentUser = <?php echo json_encode($currentUser); ?>;
</script>

<?php include __DIR__ . '/../views/footer.php'; ?>