<?php
/**
 * SmartAd Diagnostic Script
 * Run this to verify the application setup
 * Access: http://localhost/smartAd/diagnostic.php
 */

// Prevent direct access in production
if ($_SERVER['SERVER_NAME'] !== 'localhost' && $_SERVER['SERVER_NAME'] !== '127.0.0.1') {
    die('Diagnostic script only available on localhost');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartAd Diagnostic</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success {
            color: #10b981;
            font-weight: bold;
        }
        .error {
            color: #ef4444;
            font-weight: bold;
        }
        .warning {
            color: #f59e0b;
            font-weight: bold;
        }
        .info {
            color: #3b82f6;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
        }
        .status-icon {
            font-size: 18px;
            margin-right: 5px;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>🔍 SmartAd Application Diagnostic</h1>
    
    <?php
    $checks = [];
    
    // 1. Check PHP Version
    $phpVersion = PHP_VERSION;
    $checks['PHP Version'] = [
        'status' => version_compare($phpVersion, '7.4.0', '>='),
        'message' => "PHP $phpVersion" . (version_compare($phpVersion, '7.4.0', '>=') ? ' ✓' : ' (Requires 7.4+)'),
        'value' => $phpVersion
    ];
    
    // 2. Check Required Extensions
    $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'session'];
    foreach ($requiredExtensions as $ext) {
        $checks["Extension: $ext"] = [
            'status' => extension_loaded($ext),
            'message' => extension_loaded($ext) ? 'Loaded ✓' : 'Missing ✗',
            'value' => extension_loaded($ext) ? 'Yes' : 'No'
        ];
    }
    
    // 3. Check File Structure
    $requiredPaths = [
        'bootstrap.php' => __DIR__ . '/bootstrap.php',
        'smartAdVault' => __DIR__ . '/smartAdVault',
        'public' => __DIR__ . '/public',
        'app' => __DIR__ . '/app',
        'database' => __DIR__ . '/database',
        'public/index.php' => __DIR__ . '/public/index.php',
        '.htaccess' => __DIR__ . '/.htaccess',
        'public/.htaccess' => __DIR__ . '/public/.htaccess'
    ];
    
    foreach ($requiredPaths as $name => $path) {
        $checks["Path: $name"] = [
            'status' => file_exists($path),
            'message' => file_exists($path) ? 'Exists ✓' : 'Missing ✗',
            'value' => $path
        ];
    }
    
    // 4. Check Permissions
    $writablePaths = [
        'smartAdVault/logs' => __DIR__ . '/smartAdVault/logs',
        'smartAdVault/dev_logs' => __DIR__ . '/smartAdVault/dev_logs'
    ];
    
    foreach ($writablePaths as $name => $path) {
        if (file_exists($path)) {
            $checks["Writable: $name"] = [
                'status' => is_writable($path),
                'message' => is_writable($path) ? 'Writable ✓' : 'Not Writable ✗',
                'value' => $path
            ];
        }
    }
    
    // 5. Check Database Connection
    try {
        require_once __DIR__ . '/bootstrap.php';
        $db = getDatabaseConnection();
        $checks['Database Connection'] = [
            'status' => true,
            'message' => 'Connected ✓',
            'value' => 'u528309675_smartdbs'
        ];
        
        // Check if tables exist
        $tables = ['users', 'bookings', 'clients', 'rates', 'permissions'];
        foreach ($tables as $table) {
            $stmt = $db->query("SHOW TABLES LIKE '$table'");
            $exists = $stmt->rowCount() > 0;
            $checks["Table: $table"] = [
                'status' => $exists,
                'message' => $exists ? 'Exists ✓' : 'Missing ✗',
                'value' => $table
            ];
        }
    } catch (Exception $e) {
        $checks['Database Connection'] = [
            'status' => false,
            'message' => 'Failed: ' . $e->getMessage(),
            'value' => 'Error'
        ];
    }
    
    // 6. Check Configuration
    if (defined('BASE_URL')) {
        $checks['BASE_URL'] = [
            'status' => true,
            'message' => BASE_URL,
            'value' => BASE_URL
        ];
    }
    
    if (defined('VAULT_PATH')) {
        $checks['VAULT_PATH'] = [
            'status' => file_exists(VAULT_PATH),
            'message' => file_exists(VAULT_PATH) ? 'Valid ✓' : 'Invalid ✗',
            'value' => VAULT_PATH
        ];
    }
    
    // 7. Check Apache Modules
    if (function_exists('apache_get_modules')) {
        $modules = apache_get_modules();
        $requiredModules = ['mod_rewrite', 'mod_headers'];
        foreach ($requiredModules as $mod) {
            $checks["Apache: $mod"] = [
                'status' => in_array($mod, $modules),
                'message' => in_array($mod, $modules) ? 'Enabled ✓' : 'Disabled ✗',
                'value' => $mod
            ];
        }
    }
    
    // Display Results
    $totalChecks = count($checks);
    $passedChecks = count(array_filter($checks, function($check) {
        return $check['status'];
    }));
    $failedChecks = $totalChecks - $passedChecks;
    
    ?>
    
    <div class="section">
        <h2>Summary</h2>
        <table>
            <tr>
                <th>Total Checks</th>
                <td><?php echo $totalChecks; ?></td>
            </tr>
            <tr>
                <th>Passed</th>
                <td class="success"><?php echo $passedChecks; ?> ✓</td>
            </tr>
            <tr>
                <th>Failed</th>
                <td class="<?php echo $failedChecks > 0 ? 'error' : 'success'; ?>">
                    <?php echo $failedChecks; ?> <?php echo $failedChecks > 0 ? '✗' : '✓'; ?>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <h2>Detailed Results</h2>
        <table>
            <thead>
                <tr>
                    <th>Check</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checks as $name => $result): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($name); ?></td>
                        <td class="<?php echo $result['status'] ? 'success' : 'error'; ?>">
                            <span class="status-icon"><?php echo $result['status'] ? '✓' : '✗'; ?></span>
                            <?php echo htmlspecialchars($result['message']); ?>
                        </td>
                        <td><code><?php echo htmlspecialchars($result['value']); ?></code></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="section">
        <h2>Next Steps</h2>
        <?php if ($failedChecks === 0): ?>
            <p class="success">✓ All checks passed! Your SmartAd installation looks good.</p>
            <p>Try accessing: <a href="/smartAd/"><strong>http://localhost/smartAd/</strong></a></p>
        <?php else: ?>
            <p class="error">✗ Some checks failed. Please review the errors above.</p>
            <p><strong>Common Solutions:</strong></p>
            <ul>
                <li>If database connection failed: Run <code>php database/run_database_setup.php</code></li>
                <li>If paths are missing: Verify your file structure</li>
                <li>If permissions failed: Set write permissions on log directories</li>
                <li>If Apache modules missing: Enable mod_rewrite and mod_headers in httpd.conf</li>
            </ul>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>System Information</h2>
        <table>
            <tr>
                <th>Server Software</th>
                <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
            </tr>
            <tr>
                <th>Document Root</th>
                <td><code><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></code></td>
            </tr>
            <tr>
                <th>Script Filename</th>
                <td><code><?php echo $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown'; ?></code></td>
            </tr>
            <tr>
                <th>PHP SAPI</th>
                <td><?php echo php_sapi_name(); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="section" style="background: #fffbeb; border-left: 4px solid #f59e0b;">
        <h2>⚠️ Security Note</h2>
        <p><strong>Important:</strong> Delete or disable this diagnostic script (<code>diagnostic.php</code>) before deploying to production!</p>
    </div>
</body>
</html>
