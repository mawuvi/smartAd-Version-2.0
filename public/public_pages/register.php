<?php
require_once __DIR__ . '/../../bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - smartAd</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/pages/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="logo">smartAd</div>
        <div class="subtitle">Create Your Account</div>
        <div id="alert-container"></div>
        <form id="register-form" autocomplete="off" novalidate>
            </form>
        <div class="footer">
            <p>Already have an account? <a href="login.php">Sign in here</a></p>
        </div>
    </div>
    <script>window.baseUrl = '<?php echo BASE_URL; ?>';</script>
    <script src="<?php echo BASE_URL; ?>/public/js/pages/register.js"></script>
</body>
</html>