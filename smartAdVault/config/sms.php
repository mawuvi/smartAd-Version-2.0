<?php
/**
 * SMS Provider Configuration
 */
if (!defined('SECURITY_CHECK')) { die('Direct access not allowed.'); }

return [
    'api_key'   => $_ENV['SMS_API_KEY'] ?? '',
    'sender_id' => $_ENV['SMS_SENDER_ID'] ?? 'SmartAd',
    'base_url'  => $_ENV['SMS_BASE_URL'] ?? '',
];