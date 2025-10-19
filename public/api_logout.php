<?php
/**
 * Logout API Endpoint
 * Location: public/api_logout.php
 * Standards Applied: Section 9.2 (Output Buffer Management)
 */

// At the start
ob_start();
require_once __DIR__ . '/../bootstrap_api.php';
// ... other includes ...

// After all includes, before output
ob_end_clean();
ob_start();

use SmartAdVault\Helpers\ApiResponseHelper;
use SmartAdVault\Helpers\AuditLogger;

try {
    // Get AuthGuard instance
    $authGuard = new AuthGuard();
    
    // Log the logout attempt
    if (class_exists('SmartAdVault\\Helpers\\AuditLogger')) {
        AuditLogger::logOperation(
            'api_logout',
            'logout_attempt',
            'User logout attempt',
            null,
            AuditLogger::LEVEL_INFO,
            ['user_id' => $_SESSION['user_id'] ?? null]
        );
    }
    
    // Perform logout
    $authGuard->logout();
    
    // Show styled logout page instead of JSON
    showLogoutPage();
    
} catch (Throwable $e) {
    if (class_exists('SmartAdVault\\Helpers\\AuditLogger')) {
        AuditLogger::logError(
            'api_logout',
            'logout_error',
            $e->getMessage(),
            null,
            ['trace' => $e->getTraceAsString()]
        );
    }
    showLogoutErrorPage($e->getMessage());
} finally {
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}

function showLogoutPage() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Logged Out - smartAd</title>
        <style>
            :root {
                --primary-color: #3b82f6;
                --secondary-color: #1e40af;
                --success-color: #10b981;
                --danger-color: #ef4444;
                --warning-color: #f59e0b;
                --light-bg: #f8fafc;
                --card-bg: #ffffff;
                --text-primary: #1f2937;
                --text-secondary: #6b7280;
                --text-muted: #9ca3af;
                --border-color: #e5e7eb;
                --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }

            .logout-container {
                background: var(--card-bg);
                border-radius: 24px;
                padding: 3rem;
                box-shadow: var(--shadow-xl);
                width: 100%;
                max-width: 500px;
                position: relative;
                overflow: hidden;
                text-align: center;
                animation: slideInUp 0.6s ease-out;
            }

            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .logout-container::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(135deg, var(--success-color), var(--primary-color));
            }

            .brand-logo {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                border-radius: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
                font-size: 36px;
                margin: 0 auto 1.5rem;
                box-shadow: var(--shadow-md);
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }

            .success-icon {
                width: 60px;
                height: 60px;
                background: linear-gradient(135deg, var(--success-color), #059669);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 28px;
                margin: 0 auto 1.5rem;
                animation: checkmark 0.8s ease-out 0.3s both;
            }

            @keyframes checkmark {
                0% {
                    opacity: 0;
                    transform: scale(0.3) rotate(-45deg);
                }
                50% {
                    opacity: 1;
                    transform: scale(1.1) rotate(-45deg);
                }
                100% {
                    opacity: 1;
                    transform: scale(1) rotate(-45deg);
                }
            }

            .logout-title {
                font-size: 2.25rem;
                font-weight: 800;
                color: var(--text-primary);
                margin-bottom: 0.75rem;
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .logout-subtitle {
                color: var(--text-secondary);
                font-size: 1.125rem;
                font-weight: 500;
                margin-bottom: 2rem;
                line-height: 1.6;
            }

            .contact-info {
                background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(16, 185, 129, 0.1));
                border-radius: 16px;
                padding: 1.5rem;
                margin: 2rem 0;
                border: 1px solid rgba(59, 130, 246, 0.2);
            }

            .contact-title {
                font-size: 1.125rem;
                font-weight: 600;
                color: var(--text-primary);
                margin-bottom: 1rem;
            }

            .contact-details {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            .contact-item {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                color: var(--text-secondary);
                font-size: 0.875rem;
            }

            .contact-icon {
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
            }

            .login-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 1rem 2rem;
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                color: white;
                text-decoration: none;
                border-radius: 12px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s ease;
                box-shadow: var(--shadow-sm);
                margin-top: 1rem;
            }

            .login-btn:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
                text-decoration: none;
                color: white;
            }

            .login-btn:active {
                transform: translateY(0);
            }

            .footer-text {
                color: var(--text-muted);
                font-size: 0.875rem;
                margin-top: 2rem;
                padding-top: 1.5rem;
                border-top: 1px solid var(--border-color);
            }

            .security-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                background: rgba(16, 185, 129, 0.1);
                color: var(--success-color);
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 0.75rem;
                font-weight: 500;
                margin-top: 1rem;
            }

            /* Responsive */
            @media (max-width: 480px) {
                .logout-container {
                    padding: 2rem;
                    margin: 1rem;
                }
                
                .logout-title {
                    font-size: 1.875rem;
                }
                
                .brand-logo {
                    width: 64px;
                    height: 64px;
                    font-size: 28px;
                }
            }
        </style>
    </head>
    <body>
        <div class="logout-container">
            <div class="brand-logo">📊</div>
            <div class="success-icon">✓</div>
            
            <h1 class="logout-title">Thank You!</h1>
            <p class="logout-subtitle">
                You have been successfully logged out of smartAd.<br>
                Your session has been securely terminated.
            </p>

            <div class="contact-info">
                <h3 class="contact-title">Need Help?</h3>
                <div class="contact-details">
                    <div class="contact-item">
                        <span class="contact-icon">📧</span>
                        <span>support@smartad.com</span>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">🕒</span>
                        <span>Mon-Fri 9AM-6PM EST</span>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">🎨</span>
                        <span>Designed by mawuvi.ACTTribe</span>
                    </div>
                </div>
            </div>

            <a href="<?php echo BASE_URL; ?>/public_pages/login.php" class="login-btn">
                <span>🔐</span>
                <span>Sign In Again</span>
            </a>

            <div class="footer-text">
                <p>Secure corporate advertising management platform</p>
                <div class="security-badge">
                    <span>🔒</span>
                    <span>SSL Encrypted & Secure</span>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function showLogoutErrorPage($errorMessage) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Logout Error - smartAd</title>
        <style>
            :root {
                --primary-color: #3b82f6;
                --secondary-color: #1e40af;
                --success-color: #10b981;
                --danger-color: #ef4444;
                --warning-color: #f59e0b;
                --light-bg: #f8fafc;
                --card-bg: #ffffff;
                --text-primary: #1f2937;
                --text-secondary: #6b7280;
                --text-muted: #9ca3af;
                --border-color: #e5e7eb;
                --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }

            .error-container {
                background: var(--card-bg);
                border-radius: 24px;
                padding: 3rem;
                box-shadow: var(--shadow-xl);
                width: 100%;
                max-width: 500px;
                position: relative;
                overflow: hidden;
                text-align: center;
                animation: slideInUp 0.6s ease-out;
            }

            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .error-container::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(135deg, var(--danger-color), var(--warning-color));
            }

            .brand-logo {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                border-radius: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
                font-size: 36px;
                margin: 0 auto 1.5rem;
                box-shadow: var(--shadow-md);
            }

            .error-icon {
                width: 60px;
                height: 60px;
                background: linear-gradient(135deg, var(--danger-color), #dc2626);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 28px;
                margin: 0 auto 1.5rem;
                animation: shake 0.5s ease-in-out;
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }

            .error-title {
                font-size: 2.25rem;
                font-weight: 800;
                color: var(--danger-color);
                margin-bottom: 0.75rem;
            }

            .error-subtitle {
                color: var(--text-secondary);
                font-size: 1.125rem;
                font-weight: 500;
                margin-bottom: 2rem;
                line-height: 1.6;
            }

            .error-details {
                background: rgba(239, 68, 68, 0.1);
                border: 1px solid rgba(239, 68, 68, 0.2);
                border-radius: 12px;
                padding: 1rem;
                margin: 1.5rem 0;
                color: var(--danger-color);
                font-size: 0.875rem;
                font-family: 'Courier New', monospace;
            }

            .retry-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 1rem 2rem;
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                color: white;
                text-decoration: none;
                border-radius: 12px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s ease;
                box-shadow: var(--shadow-sm);
                margin: 0.5rem;
            }

            .retry-btn:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
                text-decoration: none;
                color: white;
            }

            .login-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 1rem 2rem;
                background: linear-gradient(135deg, var(--success-color), #059669);
                color: white;
                text-decoration: none;
                border-radius: 12px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s ease;
                box-shadow: var(--shadow-sm);
                margin: 0.5rem;
            }

            .login-btn:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
                text-decoration: none;
                color: white;
            }

            .footer-text {
                color: var(--text-muted);
                font-size: 0.875rem;
                margin-top: 2rem;
                padding-top: 1.5rem;
                border-top: 1px solid var(--border-color);
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="brand-logo">📊</div>
            <div class="error-icon">⚠</div>
            
            <h1 class="error-title">Logout Error</h1>
            <p class="error-subtitle">
                We encountered an issue while logging you out.<br>
                Please try again or contact support if the problem persists.
            </p>

            <div class="error-details">
                Error: <?php echo htmlspecialchars($errorMessage); ?>
            </div>

            <a href="javascript:history.back()" class="retry-btn">
                <span>🔄</span>
                <span>Try Again</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/public_pages/login.php" class="login-btn">
                <span>🔐</span>
                <span>Go to Login</span>
            </a>

            <div class="footer-text">
                <p>If this issue continues, please contact our support team</p>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
