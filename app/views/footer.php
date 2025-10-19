<?php
if (!defined('SECURITY_CHECK')) { die('Direct access not allowed.'); }

$currentYear = date('Y');
?>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-minimal">
            <span class="footer-copyright">
                &copy; <?php echo $currentYear; ?> smartAd <span class="version">v1.0</span>
            </span>
            <span class="footer-separator">|</span>
            <div class="footer-links">
                <a href="<?php echo BASE_URL; ?>/app/pages/privacy.php">Privacy</a>
                <a href="<?php echo BASE_URL; ?>/app/pages/terms.php">Terms</a>
                <a href="<?php echo BASE_URL; ?>/app/pages/support.php">Support</a>
                </div>
            <span class="footer-separator">|</span>
            <span class="footer-attribution">
                Designed by <a href="mailto:mawuvi@gmail.com" class="attribution-link">mawuvi.ACTTribe</a>
                </span>
        </div>
    </div>
</footer>
