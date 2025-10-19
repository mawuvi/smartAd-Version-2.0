<?php
require_once __DIR__ . '/../../bootstrap.php';
if (getAuthGuard()->isAuthenticated()) {
    header('Location: ' . BASE_URL . '/app/pages/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - smartAd</title>
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

        .login-container {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 420px;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .brand-section {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .brand-logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 28px;
            margin: 0 auto 1rem;
            box-shadow: var(--shadow-md);
        }

        .brand-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .brand-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 500;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: var(--card-bg);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .login-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .login-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .alert-container {
            margin-bottom: 1.5rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .footer-section {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .footer-text {
            color: var(--text-muted);
            font-size: 0.875rem;
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
            .login-container {
                padding: 2rem;
                margin: 1rem;
            }
            
            .brand-title {
                font-size: 1.75rem;
            }
        }

        /* Loading animation */
        .loading {
            position: relative;
            color: transparent;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="brand-section">
            <div class="brand-logo">📊</div>
            <h1 class="brand-title">smartAd</h1>
            <p class="brand-subtitle">Corporate Advertising Management</p>
        </div>

        <div class="alert-container" id="alert-container"></div>

        <div class="form-section">
        <form id="login-form" autocomplete="off" novalidate>
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        placeholder="Enter your username"
                        required
                        autocomplete="username"
                    >
            </div>
                
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                </div>
                
                <button type="submit" class="login-btn" id="login-btn">
                    Sign In
                </button>
            </form>
        </div>

        <div class="footer-section">
            <p class="footer-text">
                Secure corporate access portal
            </p>
            <div class="security-badge">
                <span>🔒</span>
                <span>SSL Encrypted</span>
            </div>
        </div>
    </div>

    <script>
        window.baseUrl = '<?php echo BASE_URL; ?>';
    </script>
    <script src="<?php echo BASE_URL; ?>/public/js/pages/login.js"></script>
</body>
</html>