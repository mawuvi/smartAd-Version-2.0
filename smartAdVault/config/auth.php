<?php
/**
 * Authentication Configuration - SECURE FILE
 * Defines the Auth class for checking user session status and roles.
 */
if (!defined('SECURITY_CHECK')) { die('Direct access not allowed.'); }

class Auth
{
    public function getCurrentUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public function hasRole(string $roleName): bool
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) { return false; }
        $userModel = new UserModel();
        return $userModel->userHasRole($userId, $roleName);
    }
}