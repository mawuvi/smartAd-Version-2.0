-- =============================================
-- SMARTAD COMPLETE DATABASE SCHEMA
-- MariaDB Compatible - No IF EXISTS limitations
-- Fresh Installation - No Migration Needed
-- =============================================

-- Create database
CREATE DATABASE smartad_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smartad_system;

-- =============================================
-- 1. CORE DEPARTMENT ARCHITECTURE
-- =============================================

-- Departments master table
CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    code VARCHAR(10) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users with department association
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    department_id INT,
    role ENUM('super_admin', 'dept_head', 'dept_staff', 'client') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Granular permissions system
CREATE TABLE user_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    department_id INT NOT NULL,
    permission_level ENUM('read', 'write', 'admin') NOT NULL,
    granted_by INT NOT NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_department (user_id, department_id)
);

-- =============================================
-- 2. ADVERTS DEPARTMENT TABLES
-- =============================================

-- Advert rates and pricing structure
CREATE TABLE advert_rates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    rate_name VARCHAR(100) NOT NULL,
    rate_type ENUM('color', 'bw', 'spot_color') NOT NULL,
    page_size ENUM('full', 'half', 'quarter', 'eighth') NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    effective_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_dept_effective (department_id, effective_date),
    INDEX idx_rate_type (rate_type, page_size),
    INDEX idx_active_rates (is_active, effective_date)
);

-- Client management
CREATE TABLE clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    tax_number VARCHAR(50),
    payment_terms ENUM('net_15', 'net_30', 'net_45') DEFAULT 'net_30',
    credit_limit DECIMAL(10,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_dept_client (department_id, client_name),
    INDEX idx_active_clients (is_active)
);

-- Campaign management
CREATE TABLE campaigns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    client_id INT NOT NULL,
    campaign_name VARCHAR(255) NOT NULL,
    campaign_code VARCHAR(50) UNIQUE NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    budget DECIMAL(12,2) NOT NULL,
    status ENUM('draft', 'approved', 'running', 'completed', 'cancelled') DEFAULT 'draft',
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_dept_status (department_id, status),
    INDEX idx_client_dates (client_id, start_date, end_date),
    INDEX idx_campaign_dates (start_date, end_date)
);

-- Individual advertisement bookings
CREATE TABLE advert_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    campaign_id INT NOT NULL,
    advert_rate_id INT NOT NULL,
    booking_date DATE NOT NULL,
    publication_date DATE NOT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    discount_percent DECIMAL(5,2) DEFAULT 0.00,
    final_price DECIMAL(10,2) NOT NULL,
    booking_status ENUM('reserved', 'confirmed', 'published', 'cancelled') DEFAULT 'reserved',
    special_instructions TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (advert_rate_id) REFERENCES advert_rates(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_publication_date (publication_date),
    INDEX idx_campaign_bookings (campaign_id, booking_status),
    INDEX idx_booking_dates (booking_date, publication_date)
);

-- =============================================
-- 3. CIRCULATION DEPARTMENT TABLES
-- =============================================

-- Geographic distribution zones
CREATE TABLE distribution_zones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    zone_name VARCHAR(100) NOT NULL,
    zone_code VARCHAR(20) NOT NULL,
    description TEXT,
    target_circulation INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_zone_code (department_id, zone_code),
    INDEX idx_zone_active (is_active)
);

-- Delivery routes and schedules
CREATE TABLE distribution_routes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    route_name VARCHAR(100) NOT NULL,
    route_code VARCHAR(20) NOT NULL,
    zone_id INT NOT NULL,
    delivery_schedule ENUM('daily', 'weekly', 'monthly') DEFAULT 'daily',
    target_quantity INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES distribution_zones(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_route_code (department_id, route_code),
    INDEX idx_route_zone (zone_id, is_active)
);

-- Circulation-based pricing multipliers
CREATE TABLE circulation_rates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    zone_id INT NOT NULL,
    circulation_tier ENUM('under_10k', '10k_50k', '50k_100k', 'over_100k') NOT NULL,
    multiplier DECIMAL(5,3) NOT NULL DEFAULT 1.000,
    effective_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES distribution_zones(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_circulation_rate (zone_id, circulation_tier, effective_date),
    INDEX idx_effective_date (effective_date, is_active)
);

-- =============================================
-- 4. AUDIT & SYSTEM TABLES
-- =============================================

-- Comprehensive audit logging
CREATE TABLE audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    department_id INT,
    action_type VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    INDEX idx_audit_user_date (user_id, performed_at),
    INDEX idx_audit_table_record (table_name, record_id),
    INDEX idx_audit_department (department_id, performed_at)
);

-- Global and department-specific settings
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NOT NULL,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    UNIQUE KEY unique_setting (department_id, setting_key),
    INDEX idx_setting_key (setting_key)
);

-- =============================================
-- 5. INITIAL DATA POPULATION
-- =============================================

-- Insert core departments
INSERT INTO departments (name, code, description) VALUES
('Adverts', 'ADV', 'Advertisement sales, rate calculation, and campaign management department'),
('Circulation', 'CIRC', 'Newspaper distribution, readership analytics, and circulation management'),
('Accounts', 'ACCT', 'Billing, invoicing, payment tracking, and financial management'),
('Management', 'MGMT', 'Executive reporting, analytics, and cross-department coordination');

-- Insert default super administrator
-- Password: 'admin123' (you should change this immediately after installation)
INSERT INTO users (username, email, password_hash, full_name, department_id, role) 
VALUES (
    'admin', 
    'admin@smartad.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'System Administrator', 
    (SELECT id FROM departments WHERE code = 'MGMT'), 
    'super_admin'
);

-- Insert sample advert rates for Adverts department
INSERT INTO advert_rates (department_id, rate_name, rate_type, page_size, base_price, currency, effective_date, created_by) 
SELECT 
    d.id,
    'Front Page Color Full',
    'color',
    'full',
    5000.00,
    'USD',
    CURDATE(),
    (SELECT id FROM users WHERE username = 'admin')
FROM departments d WHERE d.code = 'ADV';

INSERT INTO advert_rates (department_id, rate_name, rate_type, page_size, base_price, currency, effective_date, created_by) 
SELECT 
    d.id,
    'Inside Page BW Half',
    'bw',
    'half',
    1200.00,
    'USD',
    CURDATE(),
    (SELECT id FROM users WHERE username = 'admin')
FROM departments d WHERE d.code = 'ADV';

-- Insert system settings
INSERT INTO system_settings (department_id, setting_key, setting_value, setting_type, description) VALUES
(NULL, 'system_name', 'SmartAd Advertising System', 'string', 'Global system display name'),
(NULL, 'default_currency', 'USD', 'string', 'Default currency for all financial operations'),
(NULL, 'session_timeout', '3600', 'number', 'User session timeout in seconds'),
((SELECT id FROM departments WHERE code = 'ADV'), 'auto_approve_campaigns', 'false', 'boolean', 'Automatically approve new campaigns without manual review'),
((SELECT id FROM departments WHERE code = 'ADV'), 'default_discount_limit', '15', 'number', 'Maximum discount percentage staff can apply without approval');

-- =============================================
-- 6. PERFORMANCE OPTIMIZATION INDEXES
-- =============================================

-- Additional composite indexes for query performance
CREATE INDEX idx_advert_rates_comprehensive ON advert_rates (department_id, rate_type, page_size, is_active, effective_date);
CREATE INDEX idx_campaigns_comprehensive ON campaigns (department_id, client_id, status, start_date, end_date);
CREATE INDEX idx_bookings_comprehensive ON advert_bookings (department_id, campaign_id, publication_date, booking_status);
CREATE INDEX idx_clients_comprehensive ON clients (department_id, is_active, client_name);
CREATE INDEX idx_audit_comprehensive ON audit_logs (user_id, department_id, performed_at, action_type);

-- =============================================
-- 7. DATABASE USERS AND PERMISSIONS
-- =============================================

-- Note: Create database users based on your environment security requirements
-- This is environment-specific and should be configured during deployment

-- Example (adjust for your environment):
-- CREATE USER 'smartad_app'@'localhost' IDENTIFIED BY 'secure_password_here';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON smartad_system.* TO 'smartad_app'@'localhost';
-- FLUSH PRIVILEGES;

-- =============================================
-- SCHEMA CREATION COMPLETE
-- =============================================

SELECT 'SmartAd Database Schema Created Successfully' as status;
