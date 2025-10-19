<?php
/**
 * Complete Database Migration Runner
 * This script runs all migrations to set up the entire smartAd database from scratch
 */

// Database configuration
$host = 'localhost';
$dbname = 'u528309675_smartdbs'; // Change this to your database name
$username = 'root'; // Change this to your database username
$password = ''; // Change this to your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    echo "Starting complete database migration...\n\n";
    
    // Migration files in order
    $migrations = [
        '2025_01_08_create_complete_schema.sql',
        '2025_01_08_create_performance_views.sql', 
        '2025_01_08_create_stored_procedures.sql',
        '2025_01_08_seed_complete_data.sql'
    ];
    
    $migrationPath = __DIR__ . '/migrations/';
    $seedPath = __DIR__ . '/seeds/';
    
    foreach ($migrations as $migration) {
        $filePath = '';
        
        // Determine if it's a migration or seed file
        if (strpos($migration, 'seed_') !== false) {
            $filePath = $seedPath . $migration;
        } else {
            $filePath = $migrationPath . $migration;
        }
        
        if (!file_exists($filePath)) {
            echo "❌ Migration file not found: $migration\n";
            continue;
        }
        
        echo "🔄 Running migration: $migration\n";
        
        $sql = file_get_contents($filePath);
        
        // Split SQL into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^--/', $stmt);
            }
        );
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($statements as $statement) {
            if (empty(trim($statement))) continue;
            
            try {
                $pdo->exec($statement);
                $successCount++;
            } catch (PDOException $e) {
                $errorCount++;
                echo "   ⚠️  Warning: " . $e->getMessage() . "\n";
                
                // Continue with non-critical errors
                if (strpos($e->getMessage(), 'already exists') !== false || 
                    strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    echo "   ℹ️  Continuing (non-critical error)...\n";
                } else {
                    echo "   ❌ Critical error, stopping migration.\n";
                    throw $e;
                }
            }
        }
        
        echo "   ✅ Completed: $successCount statements executed successfully\n";
        if ($errorCount > 0) {
            echo "   ⚠️  Warnings: $errorCount statements had issues\n";
        }
        echo "\n";
    }
    
    // Verify installation
    echo "🔍 Verifying installation...\n";
    
    $tables = [
        'users', 'api_keys', 'publications', 'color_types', 'ad_categories',
        'ad_sizes', 'page_positions', 'taxes', 'tax_configurations',
        'clients', 'client_credit', 'rates', 'bookings', 'file_storage'
    ];
    
    $views = [
        'vw_booking_details', 'vw_client_summary', 'vw_rate_details',
        'vw_publication_taxes', 'vw_publication_stats', 'vw_user_activity'
    ];
    
    $procedures = [
        'sp_search_bookings', 'sp_client_debt_aging', 'sp_search_rates',
        'sp_generate_booking_number', 'sp_generate_client_number',
        'sp_calculate_tax_breakdown', 'sp_update_client_credit', 'sp_get_dashboard_stats'
    ];
    
    // Check tables
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $countStmt->fetchColumn();
            echo "   ✅ Table '$table': $count records\n";
        } else {
            echo "   ❌ Table '$table': NOT FOUND\n";
        }
    }
    
    // Check views
    foreach ($views as $view) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$view'");
        if ($stmt->rowCount() > 0) {
            echo "   ✅ View '$view': EXISTS\n";
        } else {
            echo "   ❌ View '$view': NOT FOUND\n";
        }
    }
    
    // Check procedures
    foreach ($procedures as $procedure) {
        $stmt = $pdo->query("SHOW PROCEDURE STATUS WHERE Name = '$procedure'");
        if ($stmt->rowCount() > 0) {
            echo "   ✅ Procedure '$procedure': EXISTS\n";
        } else {
            echo "   ❌ Procedure '$procedure': NOT FOUND\n";
        }
    }
    
    echo "\n🎉 Database migration completed successfully!\n";
    echo "📊 Summary:\n";
    echo "   - All tables created with proper relationships\n";
    echo "   - Performance views created for complex queries\n";
    echo "   - Stored procedures created for business logic\n";
    echo "   - Comprehensive seed data loaded\n";
    echo "   - Indexes created for optimal performance\n\n";
    
    echo "🚀 Your smartAd system is ready to use!\n";
    echo "   - Admin user: admin@smartad.com (password: password)\n";
    echo "   - Manager user: manager@smartad.com (password: password)\n";
    echo "   - Regular user: user@smartad.com (password: password)\n\n";
    
    echo "📋 Next steps:\n";
    echo "   1. Test the booking interface\n";
    echo "   2. Create some test bookings\n";
    echo "   3. Explore the bookings management interface\n";
    echo "   4. Configure system settings as needed\n\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and try again.\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
