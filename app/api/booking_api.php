<?php
require_once __DIR__ . '/../bootstrap_api.php';

use smartAdVault\models\BookingModel;
use smartAdVault\helpers\ApiResponseHelper;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $bookingModel = new BookingModel();

    switch ($action) {
        case 'create_booking':
            $bookingData = json_decode(file_get_contents('php://input'), true);
            
            if (!$bookingData) {
                ApiResponseHelper::sendError('Invalid booking data', 400);
                break;
            }

            // Add created_by from current user
            $bookingData['created_by'] = getAuthGuard()->getCurrentUser()['id'];

            // Validate required fields
            $validation = $bookingModel->validate($bookingData);
            if (!$validation['valid']) {
                ApiResponseHelper::sendError('Validation failed: ' . implode(', ', $validation['errors']), 400);
                break;
            }

            $booking = $bookingModel->create($bookingData);
            ApiResponseHelper::sendSuccess('Booking created successfully', $booking);
            break;

        case 'get_draft_count':
            $clientId = intval($_GET['client_id'] ?? 0);
            
            if (!$clientId) {
                ApiResponseHelper::sendError('Client ID is required', 400);
                break;
            }
            
            $count = $bookingModel->getDraftCount($clientId);
            ApiResponseHelper::sendSuccess('Draft count retrieved', ['count' => $count]);
            break;

        case 'get_bookings':
            $filters = [
                'client_id' => $_GET['client_id'] ?? null,
                'status' => $_GET['status'] ?? null,
                'publication_id' => $_GET['publication_id'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
                'limit' => intval($_GET['limit'] ?? 50),
                'offset' => intval($_GET['offset'] ?? 0)
            ];

            $bookings = $bookingModel->findAll($filters);
            ApiResponseHelper::sendSuccess('Bookings retrieved successfully', $bookings);
            break;

        case 'get_booking':
            $bookingId = intval($_GET['id'] ?? 0);
            
            if (!$bookingId) {
                ApiResponseHelper::sendError('Booking ID is required', 400);
                break;
            }

            $booking = $bookingModel->findById($bookingId);
            if (!$booking) {
                ApiResponseHelper::sendError('Booking not found', 404);
                break;
            }

            ApiResponseHelper::sendSuccess('Booking retrieved successfully', $booking);
            break;

        case 'update_booking':
            $bookingId = intval($_GET['id'] ?? 0);
            $updateData = json_decode(file_get_contents('php://input'), true);
            
            if (!$bookingId || !$updateData) {
                ApiResponseHelper::sendError('Booking ID and update data are required', 400);
                break;
            }

            $updatedBooking = $bookingModel->update($bookingId, $updateData);
            ApiResponseHelper::sendSuccess('Booking updated successfully', $updatedBooking);
            break;

        case 'delete_booking':
            $bookingId = intval($_GET['id'] ?? 0);
            
            if (!$bookingId) {
                ApiResponseHelper::sendError('Booking ID is required', 400);
                break;
            }

            $bookingModel->delete($bookingId);
            ApiResponseHelper::sendSuccess('Booking deleted successfully');
            break;

        case 'upload_documents':
            // Get booking ID
            $bookingId = intval($_POST['booking_id'] ?? 0);
            
            if (!$bookingId) {
                ApiResponseHelper::sendError('Booking ID is required', 400);
                break;
            }

            // Check if booking exists
            $booking = $bookingModel->findById($bookingId);
            if (!$booking) {
                ApiResponseHelper::sendError('Booking not found', 404);
                break;
            }

            // Check if files were uploaded
            if (empty($_FILES['files'])) {
                ApiResponseHelper::sendError('No files uploaded', 400);
                break;
            }

            // Create upload directory if it doesn't exist
            $uploadDir = __DIR__ . '/../../smartAdVault/uploads/bookings/' . $bookingId . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploadedFiles = [];
            $errors = [];

            // Process each file
            foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['files']['error'][$key] !== UPLOAD_ERR_OK) {
                    $errors[] = $_FILES['files']['name'][$key] . ': Upload error';
                    continue;
                }

                $fileName = basename($_FILES['files']['name'][$key]);
                $fileSize = $_FILES['files']['size'][$key];
                $fileType = $_FILES['files']['type'][$key];

                // Validate file size (10MB max)
                if ($fileSize > 10 * 1024 * 1024) {
                    $errors[] = $fileName . ': File too large (max 10MB)';
                    continue;
                }

                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                if (!in_array($fileType, $allowedTypes)) {
                    $errors[] = $fileName . ': File type not allowed';
                    continue;
                }

                // Generate unique filename
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                $uniqueFileName = time() . '_' . uniqid() . '.' . $extension;
                $filePath = $uploadDir . $uniqueFileName;

                // Move file
                if (move_uploaded_file($tmpName, $filePath)) {
                    $uploadedFiles[] = [
                        'original_name' => $fileName,
                        'stored_name' => $uniqueFileName,
                        'file_size' => $fileSize,
                        'file_type' => $fileType,
                        'upload_date' => date('Y-m-d H:i:s')
                    ];

                    // Log the upload
                    error_log("Document uploaded for booking {$bookingId}: {$fileName}");
                } else {
                    $errors[] = $fileName . ': Failed to save file';
                }
            }

            // Return result
            if (empty($uploadedFiles) && !empty($errors)) {
                ApiResponseHelper::sendError('All uploads failed: ' . implode(', ', $errors), 500);
            } else {
                $message = count($uploadedFiles) . ' file(s) uploaded successfully';
                if (!empty($errors)) {
                    $message .= '. Some files failed: ' . implode(', ', $errors);
                }
                ApiResponseHelper::sendSuccess($message, [
                    'uploaded_files' => $uploadedFiles,
                    'errors' => $errors
                ]);
            }
            break;

        default:
            ApiResponseHelper::sendError('Invalid action', 400);
            break;
    }

} catch (Exception $e) {
    error_log("Booking API Error: " . $e->getMessage());
    ApiResponseHelper::sendError('Failed to process request: ' . $e->getMessage(), 500);
}
