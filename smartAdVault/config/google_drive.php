<?php
/**
 * Google Drive API Configuration
 */
if (!defined('SECURITY_CHECK')) { die('Direct access not allowed.'); }

return [
    'client_id'     => $_ENV['GOOGLE_CLIENT_ID'] ?? '',
    'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
    'redirect_uri'  => $_ENV['GOOGLE_REDIRECT_URI'] ?? '',
];