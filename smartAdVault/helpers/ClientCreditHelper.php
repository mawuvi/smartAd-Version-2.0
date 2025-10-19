<?php
/**
 * Client Credit Helper - SECURE FILE
 * Handles credit-related operations for clients.
 * Location: smartAdVault/helpers/ClientCreditHelper.php
 */

// Prevent direct script access.
if (!defined('SECURITY_CHECK')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

class ClientCreditHelper
{
    /**
     * Initializes a credit record for a new client.
     */
    public static function initialize(int $clientId, float $creditLimit = 10000.00): bool
    {
        $db = Database::getInstance();
        $user = getAuthGuard()->getCurrentUser();

        $stmt = $db->prepare(
            "INSERT INTO client_credit (client_id, credit_limit, created_by) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$clientId, $creditLimit, $user['id'] ?? 1]);
    }

    /**
     * Adds a charge to a client's account, increasing their balance.
     */
    public static function addCharge(int $clientId, float $amount): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "UPDATE client_credit SET current_balance = current_balance + ? WHERE client_id = ?"
        );
        return $stmt->execute([$amount, $clientId]);
    }
    
    // Other credit-related methods (addPayment, getClientCredit, etc.) would follow...
}