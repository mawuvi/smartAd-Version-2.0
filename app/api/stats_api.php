<?php
/**
 * Stats API - Centralized statistics and counter endpoints
 * Provides consistent statistics across the application
 */

require_once __DIR__ . '/../../bootstrap.php';

$authGuard = getAuthGuard();
$currentUser = $authGuard->getCurrentUser();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_all_counts':
            $authGuard->requirePermission('dashboard.view');
            $counts = StatsHelper::getAllCounts();
            echo json_encode(['success' => true, 'data' => $counts]);
            break;
            
        case 'get_dashboard_counts':
            $authGuard->requirePermission('dashboard.view');
            $counts = StatsHelper::getDashboardCounts();
            echo json_encode(['success' => true, 'data' => $counts]);
            break;
            
        case 'get_setup_counts':
            $authGuard->requirePermission('setup.view');
            $counts = StatsHelper::getSetupCounts();
            echo json_encode(['success' => true, 'data' => $counts]);
            break;
            
        case 'get_publications_count':
            $authGuard->requirePermission('setup.publications.view');
            $count = StatsHelper::getPublicationsCount();
            echo json_encode(['success' => true, 'data' => ['count' => $count]]);
            break;
            
        case 'get_categories_count':
            $authGuard->requirePermission('setup.categories.view');
            $count = StatsHelper::getAdCategoriesCount();
            echo json_encode(['success' => true, 'data' => ['count' => $count]]);
            break;
            
        case 'get_sizes_count':
            $authGuard->requirePermission('setup.sizes.view');
            $count = StatsHelper::getAdSizesCount();
            echo json_encode(['success' => true, 'data' => ['count' => $count]]);
            break;
            
        case 'get_positions_count':
            $authGuard->requirePermission('setup.positions.view');
            $count = StatsHelper::getPagePositionsCount();
            echo json_encode(['success' => true, 'data' => ['count' => $count]]);
            break;
            
        case 'get_colors_count':
            $authGuard->requirePermission('setup.colors.view');
            $count = StatsHelper::getColorTypesCount();
            echo json_encode(['success' => true, 'data' => ['count' => $count]]);
            break;
            
        case 'get_rates_count':
            $authGuard->requirePermission('setup.rates.view');
            $count = StatsHelper::getRatesCount();
            echo json_encode(['success' => true, 'data' => ['count' => $count]]);
            break;
            
        case 'get_users_count':
            $authGuard->requirePermission('users.view');
            $count = StatsHelper::getUsersCount();
            echo json_encode(['success' => true, 'data' => ['count' => $count]]);
            break;
            
        case 'get_clients_count':
            $authGuard->requirePermission('clients.view');
            $count = StatsHelper::getClientsCount();
            echo json_encode(['success' => true, 'data' => ['count' => $count]]);
            break;
            
        case 'get_bookings_count':
            $authGuard->requirePermission('bookings.view');
            $status = $_GET['status'] ?? null;
            $count = StatsHelper::getBookingsCount($status);
            echo json_encode(['success' => true, 'data' => ['count' => $count]]);
            break;
            
        case 'get_draft_bookings_count':
            $authGuard->requirePermission('bookings.view');
            $count = StatsHelper::getDraftBookingsCount();
            echo json_encode(['success' => true, 'data' => ['count' => $count]]);
            break;
            
        case 'get_confirmed_bookings_count':
            $authGuard->requirePermission('bookings.view');
            $count = StatsHelper::getConfirmedBookingsCount();
            echo json_encode(['success' => true, 'data' => ['count' => $count]]);
            break;
            
        case 'get_pending_bookings_count':
            $authGuard->requirePermission('bookings.view');
            $count = StatsHelper::getPendingBookingsCount();
            echo json_encode(['success' => true, 'data' => ['count' => $count]]);
            break;
            
        case 'get_cancelled_bookings_count':
            $authGuard->requirePermission('bookings.view');
            $count = StatsHelper::getCancelledBookingsCount();
            echo json_encode(['success' => true, 'data' => ['count' => $count]]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
