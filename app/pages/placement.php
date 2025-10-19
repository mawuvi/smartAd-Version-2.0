<?php
require_once __DIR__ . '/../../bootstrap.php';

// Check authentication and permissions
$authGuard = new AuthGuard();
$authGuard->requirePermission('placements.view');

// Get current user
$currentUser = $authGuard->getCurrentUser();

// Include header
include __DIR__ . '/../views/header.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Placement Management</h1>
        <p class="page-subtitle">Manage advertisement placements and positions</p>
    </div>

    <div class="placement-container">
        <div class="card">
            <div class="card-header">
                <h3>Placement Settings</h3>
            </div>
            <div class="card-body">
                <p>Placement management functionality will be implemented here.</p>
            </div>
        </div>
    </div>
</div>

<!-- Include page-specific CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/pages/placement.css">

<!-- Include required JavaScript -->
<script src="<?php echo BASE_URL; ?>/public/js/pages/placement.js"></script>

<script>
window.baseUrl = '<?php echo BASE_URL; ?>';
window.currentUser = <?php echo json_encode($currentUser); ?>;
</script>

<?php include __DIR__ . '/../views/footer.php'; ?>