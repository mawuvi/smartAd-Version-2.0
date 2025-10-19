<?php
/**
 * API Response Helper - SECURE FILE
 * Standardizes API responses across all endpoints.
 */

// Prevent direct script access.
if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

class ApiResponseHelper
{
    /**
     * Sends a standardized success JSON response and terminates the script.
     */
    public static function sendSuccess(string $message, $data = null, int $httpCode = 200): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');

        $response = ['success' => true, 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Sends a standardized error JSON response and terminates the script.
     */
    public static function sendError(string $message, int $httpCode = 400, $data = null): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');

        $response = ['success' => false, 'message' => $message];
        if ($data !== null) {
            $response['errors'] = $data;
        }

        echo json_encode($response);
        exit;
    }
}