-- Complete Database Setup Script for smartAd System
-- Database: u528309675_smartdbs
-- Compatible with MariaDB 10.4.32+
-- Created: 2025-01-08
-- 
-- This script creates the entire database structure from scratch
-- following strict dependency order and MariaDB compatibility requirements

-- =====================================================
-- 1. DATABASE CREATION
-- =====================================================

CREATE DATABASE IF NOT EXISTS u528309675_smartdbs 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE u528309675_smartdbs;

-- =====================================================
-- 2. BASE TABLES (No Foreign Keys)
-- =====================================================

-- Drop existing tables if they exist (in correct order to handle foreign keys)
DROP TABLE IF EXISTS user_permissions;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS sms_logs;
DROP TABLE IF EXISTS file_uploads;
DROP TABLE IF EXISTS placements;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS rates;
DROP TABLE IF EXISTS client_credit;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS file_storage;
DROP TABLE IF EXISTS tax_configurations;
DROP TABLE IF EXISTS taxes;
DROP TABLE IF EXISTS page_positions;
DROP TABLE IF EXISTS ad_sizes;
DROP TABLE IF EXISTS ad_categories;
DROP TABLE IF EXISTS color_types;
DROP TABLE IF EXISTS publications;
DROP TABLE IF EXISTS api_keys;
DROP TABLE IF EXISTS users;

-- Create Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('admin', 'manager', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create API Keys Table
CREATE TABLE api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    key_name VARCHAR(100) NOT NULL,
    api_key VARCHAR(64) UNIQUE NOT NULL,
    permissions JSON,
    status ENUM('active', 'inactive', 'revoked') DEFAULT 'active',
    expires_at TIMESTAMP NULL,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. REFERENCE TABLES
-- =====================================================

-- Create Publications Table
CREATE TABLE publications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    publication_type ENUM('newspaper', 'magazine', 'online', 'radio', 'tv') DEFAULT 'newspaper',
    frequency ENUM('daily', 'weekly', 'monthly', 'quarterly', 'irregular') DEFAULT 'daily',
    circulation INT,
    target_audience VARCHAR(200),
    contact_person VARCHAR(100),
    contact_phone VARCHAR(20),
    contact_email VARCHAR(100),
    address TEXT,
    city VARCHAR(50),
    region VARCHAR(50),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Color Types Table
CREATE TABLE color_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    multiplier DECIMAL(5,2) DEFAULT 1.00,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Ad Categories Table
CREATE TABLE ad_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    parent_id INT NULL,
    level INT DEFAULT 1,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (parent_id) REFERENCES ad_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Ad Sizes Table
CREATE TABLE ad_sizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    width_cm DECIMAL(8,2),
    height_cm DECIMAL(8,2),
    area_sqcm DECIMAL(10,2),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Page Positions Table
CREATE TABLE page_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    position_type ENUM('front_page', 'back_page', 'inside_page', 'special_page') DEFAULT 'inside_page',
    multiplier DECIMAL(5,2) DEFAULT 1.00,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Taxes Table
CREATE TABLE taxes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    rate DECIMAL(5,2) NOT NULL,
    description TEXT,
    tax_type ENUM('vat', 'nhil', 'covid', 'other') DEFAULT 'other',
    status ENUM('active', 'inactive') DEFAULT 'active',
    effective_from DATE NOT NULL,
    effective_to DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. DEPENDENT TABLES
-- =====================================================

-- Create Tax Configurations Table
CREATE TABLE tax_configurations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    publication_id INT NOT NULL,
    tax_id INT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (tax_id) REFERENCES taxes(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_publication_tax (publication_id, tax_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Clients Table
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_number VARCHAR(20) UNIQUE NOT NULL,
    company_name VARCHAR(200) NOT NULL,
    client_type ENUM('individual', 'corporate', 'government', 'ngo') DEFAULT 'corporate',
    contact_person VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    address TEXT,
    client_address TEXT,
    city VARCHAR(50),
    region VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50) DEFAULT 'Ghana',
    website VARCHAR(200),
    industry VARCHAR(100),
    company_size ENUM('small', 'medium', 'large', 'enterprise') DEFAULT 'medium',
    annual_revenue DECIMAL(15,2),
    credit_limit DECIMAL(12,2) DEFAULT 0.00,
    payment_terms VARCHAR(50) DEFAULT '30 days',
    preferred_contact_method ENUM('email', 'phone', 'sms', 'whatsapp') DEFAULT 'email',
    notes TEXT,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Client Credit Table
CREATE TABLE client_credit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    credit_limit DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    available_credit DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    used_credit DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_terms VARCHAR(50) DEFAULT '30 days',
    credit_status ENUM('active', 'suspended', 'revoked') DEFAULT 'active',
    last_credit_review DATE,
    credit_review_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_client_credit (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Rates Table
CREATE TABLE rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    publication_id INT NOT NULL,
    color_type_id INT NOT NULL,
    ad_category_id INT NOT NULL,
    ad_size_id INT NOT NULL,
    page_position_id INT NOT NULL,
    base_rate DECIMAL(10,2) NOT NULL,
    effective_from DATE NOT NULL,
    effective_to DATE NULL,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (color_type_id) REFERENCES color_types(id) ON DELETE CASCADE,
    FOREIGN KEY (ad_category_id) REFERENCES ad_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (ad_size_id) REFERENCES ad_sizes(id) ON DELETE CASCADE,
    FOREIGN KEY (page_position_id) REFERENCES page_positions(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_rate_lookup (publication_id, color_type_id, ad_category_id, ad_size_id, page_position_id, effective_from)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. TRANSACTION TABLES
-- =====================================================

-- Create Bookings Table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_number VARCHAR(20) UNIQUE NOT NULL,
    client_id INT NOT NULL,
    publication_id INT NOT NULL,
    color_type_id INT NOT NULL,
    ad_category_id INT NOT NULL,
    ad_size_id INT NOT NULL,
    page_position_id INT NOT NULL,
    rate_id INT NOT NULL,
    publication_date DATE NOT NULL,
    insertions INT NOT NULL DEFAULT 1,
    base_rate DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) DEFAULT 0.00,
    total_tax DECIMAL(10,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    discount_reason TEXT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    notes TEXT,
    status ENUM('draft', 'confirmed', 'cancelled', 'completed') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (color_type_id) REFERENCES color_types(id) ON DELETE CASCADE,
    FOREIGN KEY (ad_category_id) REFERENCES ad_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (ad_size_id) REFERENCES ad_sizes(id) ON DELETE CASCADE,
    FOREIGN KEY (page_position_id) REFERENCES page_positions(id) ON DELETE CASCADE,
    FOREIGN KEY (rate_id) REFERENCES rates(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create File Storage Table
CREATE TABLE file_storage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NULL,
    booking_id INT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_type ENUM('ad_material', 'contract', 'invoice', 'receipt', 'other') DEFAULT 'other',
    description TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    uploaded_by INT NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Placements Table (Future Feature)
CREATE TABLE placements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placement_number VARCHAR(20) UNIQUE NOT NULL,
    client_id INT NOT NULL,
    booking_id INT NOT NULL,
    publication_id INT NOT NULL,
    placement_date DATE NOT NULL,
    placement_time TIME,
    page_number INT,
    position_description TEXT,
    status ENUM('scheduled', 'placed', 'published', 'cancelled') DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Audit Logs Table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    old_values JSON,
    new_values JSON,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create SMS Logs Table
CREATE TABLE sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    message_type ENUM('booking_confirmation', 'payment_reminder', 'general', 'marketing') DEFAULT 'general',
    status ENUM('pending', 'sent', 'delivered', 'failed') DEFAULT 'pending',
    provider_response TEXT,
    cost DECIMAL(8,4) DEFAULT 0.0000,
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create File Uploads Table
CREATE TABLE file_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NULL,
    booking_id INT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_type ENUM('ad_material', 'contract', 'invoice', 'receipt', 'other') DEFAULT 'other',
    upload_status ENUM('uploading', 'completed', 'failed', 'deleted') DEFAULT 'uploading',
    upload_progress INT DEFAULT 0,
    error_message TEXT,
    description TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    uploaded_by INT NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5.1 RBAC TABLES (Role-Based Access Control)
-- =====================================================

-- Create Permissions Table
CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(100) UNIQUE NOT NULL COMMENT 'Unique permission code (e.g., bookings.create, users.manage)',
    description TEXT NULL,
    resource VARCHAR(50) NOT NULL COMMENT 'Associated resource (e.g., bookings, clients, users)',
    action VARCHAR(50) NOT NULL COMMENT 'Action (e.g., create, view, edit, delete, manage)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_resource_action (resource, action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Role Permissions Table
CREATE TABLE role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin', 'manager', 'user') NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_role_permission (role, permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create User Permissions Table
CREATE TABLE user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    granted BOOLEAN DEFAULT TRUE COMMENT 'TRUE = grant permission, FALSE = deny permission',
    reason TEXT NULL COMMENT 'Reason for override',
    expires_at TIMESTAMP NULL COMMENT 'When this override expires',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_permission (user_id, permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. PERFORMANCE VIEWS
-- =====================================================

-- View for complete booking details with all related information
CREATE OR REPLACE VIEW vw_booking_details AS
SELECT 
    b.id,
    b.booking_number,
    b.publication_date,
    b.insertions,
    b.base_rate,
    b.subtotal,
    b.total_tax,
    b.discount_amount,
    b.discount_reason,
    b.total_amount,
    b.notes,
    b.status,
    b.created_at,
    b.updated_at,
    
    -- Client Information
    c.id as client_id,
    c.client_number,
    c.company_name as client_name,
    c.client_type,
    c.contact_person,
    c.contact_phone,
    c.contact_email,
    c.city as client_city,
    c.region as client_region,
    
    -- Client Credit Information
    cc.credit_limit,
    cc.available_credit,
    cc.used_credit,
    cc.credit_status,
    
    -- Publication Information
    p.id as publication_id,
    p.name as publication_name,
    p.code as publication_code,
    p.publication_type,
    p.frequency,
    p.circulation,
    
    -- Ad Details
    ct.id as color_type_id,
    ct.name as color_type_name,
    ct.code as color_type_code,
    ct.multiplier as color_multiplier,
    
    ac.id as ad_category_id,
    ac.name as ad_category_name,
    ac.code as ad_category_code,
    
    ads.id as ad_size_id,
    ads.name as ad_size_name,
    ads.code as ad_size_code,
    ads.width_cm,
    ads.height_cm,
    ads.area_sqcm,
    
    pp.id as page_position_id,
    pp.name as page_position_name,
    pp.code as page_position_code,
    pp.position_type,
    pp.multiplier as position_multiplier,
    
    -- Rate Information
    r.id as rate_id,
    r.effective_from as rate_effective_from,
    r.effective_to as rate_effective_to,
    
    -- User Information
    u.id as created_by_id,
    u.username as created_by_username,
    u.first_name as created_by_first_name,
    u.last_name as created_by_last_name,
    CONCAT(u.first_name, ' ', u.last_name) as created_by_full_name
    
FROM bookings b
LEFT JOIN clients c ON b.client_id = c.id
LEFT JOIN client_credit cc ON c.id = cc.client_id
LEFT JOIN publications p ON b.publication_id = p.id
LEFT JOIN color_types ct ON b.color_type_id = ct.id
LEFT JOIN ad_categories ac ON b.ad_category_id = ac.id
LEFT JOIN ad_sizes ads ON b.ad_size_id = ads.id
LEFT JOIN page_positions pp ON b.page_position_id = pp.id
LEFT JOIN rates r ON b.rate_id = r.id
LEFT JOIN users u ON b.created_by = u.id
WHERE b.deleted_at IS NULL;

-- View for client summary with credit information
CREATE OR REPLACE VIEW vw_client_summary AS
SELECT 
    c.id,
    c.client_number,
    c.company_name,
    c.client_type,
    c.contact_person,
    c.contact_phone,
    c.contact_email,
    c.city,
    c.region,
    c.country,
    c.website,
    c.industry,
    c.company_size,
    c.annual_revenue,
    c.payment_terms,
    c.preferred_contact_method,
    c.status,
    c.created_at,
    c.updated_at,
    
    -- Credit Information
    cc.credit_limit,
    cc.available_credit,
    cc.used_credit,
    cc.credit_status,
    cc.last_credit_review,
    
    -- Booking Statistics
    COALESCE(booking_stats.total_bookings, 0) as total_bookings,
    COALESCE(booking_stats.confirmed_bookings, 0) as confirmed_bookings,
    COALESCE(booking_stats.draft_bookings, 0) as draft_bookings,
    COALESCE(booking_stats.total_spent, 0.00) as total_spent,
    COALESCE(booking_stats.last_booking_date, NULL) as last_booking_date,
    
    -- User Information
    u.id as created_by_id,
    u.username as created_by_username,
    CONCAT(u.first_name, ' ', u.last_name) as created_by_full_name
    
FROM clients c
LEFT JOIN client_credit cc ON c.id = cc.client_id
LEFT JOIN users u ON c.created_by = u.id
LEFT JOIN (
    SELECT 
        client_id,
        COUNT(*) as total_bookings,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_bookings,
        SUM(CASE WHEN status = 'confirmed' THEN total_amount ELSE 0 END) as total_spent,
        MAX(publication_date) as last_booking_date
    FROM bookings 
    WHERE deleted_at IS NULL
    GROUP BY client_id
) booking_stats ON c.id = booking_stats.client_id
WHERE c.deleted_at IS NULL;

-- View for rate details with all related information
CREATE OR REPLACE VIEW vw_rate_details AS
SELECT 
    r.id,
    r.base_rate,
    r.effective_from,
    r.effective_to,
    r.status,
    r.notes,
    r.created_at,
    r.updated_at,
    
    -- Publication Information
    p.id as publication_id,
    p.name as publication_name,
    p.code as publication_code,
    p.publication_type,
    p.frequency,
    p.circulation,
    p.target_audience,
    
    -- Color Type Information
    ct.id as color_type_id,
    ct.name as color_type_name,
    ct.code as color_type_code,
    ct.multiplier as color_multiplier,
    
    -- Ad Category Information
    ac.id as ad_category_id,
    ac.name as ad_category_name,
    ac.code as ad_category_code,
    ac.parent_id as ad_category_parent_id,
    ac.level as ad_category_level,
    
    -- Ad Size Information
    ads.id as ad_size_id,
    ads.name as ad_size_name,
    ads.code as ad_size_code,
    ads.width_cm,
    ads.height_cm,
    ads.area_sqcm,
    
    -- Page Position Information
    pp.id as page_position_id,
    pp.name as page_position_name,
    pp.code as page_position_code,
    pp.position_type,
    pp.multiplier as position_multiplier,
    
    -- Calculated Fields
    (r.base_rate * ct.multiplier * pp.multiplier) as calculated_rate,
    
    -- User Information
    u.id as created_by_id,
    u.username as created_by_username,
    CONCAT(u.first_name, ' ', u.last_name) as created_by_full_name
    
FROM rates r
LEFT JOIN publications p ON r.publication_id = p.id
LEFT JOIN color_types ct ON r.color_type_id = ct.id
LEFT JOIN ad_categories ac ON r.ad_category_id = ac.id
LEFT JOIN ad_sizes ads ON r.ad_size_id = ads.id
LEFT JOIN page_positions pp ON r.page_position_id = pp.id
LEFT JOIN users u ON r.created_by = u.id
WHERE r.deleted_at IS NULL;

-- View for publication with tax configuration
CREATE OR REPLACE VIEW vw_publication_taxes AS
SELECT 
    p.id as publication_id,
    p.name as publication_name,
    p.code as publication_code,
    p.publication_type,
    p.frequency,
    p.circulation,
    p.status as publication_status,
    
    -- Tax Information
    t.id as tax_id,
    t.name as tax_name,
    t.code as tax_code,
    t.rate as tax_rate,
    t.tax_type,
    t.effective_from as tax_effective_from,
    t.effective_to as tax_effective_to,
    
    -- Configuration Status
    tc.status as tax_config_status,
    tc.created_at as config_created_at
    
FROM publications p
LEFT JOIN tax_configurations tc ON p.id = tc.publication_id AND tc.deleted_at IS NULL
LEFT JOIN taxes t ON tc.tax_id = t.id AND t.deleted_at IS NULL
WHERE p.deleted_at IS NULL AND p.status = 'active';

-- View for booking statistics by publication
CREATE OR REPLACE VIEW vw_publication_stats AS
SELECT 
    p.id as publication_id,
    p.name as publication_name,
    p.code as publication_code,
    p.publication_type,
    p.frequency,
    p.circulation,
    
    -- Booking Statistics
    COALESCE(booking_stats.total_bookings, 0) as total_bookings,
    COALESCE(booking_stats.confirmed_bookings, 0) as confirmed_bookings,
    COALESCE(booking_stats.draft_bookings, 0) as draft_bookings,
    COALESCE(booking_stats.cancelled_bookings, 0) as cancelled_bookings,
    COALESCE(booking_stats.total_revenue, 0.00) as total_revenue,
    COALESCE(booking_stats.avg_booking_value, 0.00) as avg_booking_value,
    COALESCE(booking_stats.last_booking_date, NULL) as last_booking_date,
    
    -- Rate Statistics
    COALESCE(rate_stats.total_rates, 0) as total_rates,
    COALESCE(rate_stats.active_rates, 0) as active_rates,
    COALESCE(rate_stats.avg_rate, 0.00) as avg_rate,
    COALESCE(rate_stats.min_rate, 0.00) as min_rate,
    COALESCE(rate_stats.max_rate, 0.00) as max_rate
    
FROM publications p
LEFT JOIN (
    SELECT 
        publication_id,
        COUNT(*) as total_bookings,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_bookings,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
        SUM(CASE WHEN status = 'confirmed' THEN total_amount ELSE 0 END) as total_revenue,
        AVG(CASE WHEN status = 'confirmed' THEN total_amount ELSE NULL END) as avg_booking_value,
        MAX(publication_date) as last_booking_date
    FROM bookings 
    WHERE deleted_at IS NULL
    GROUP BY publication_id
) booking_stats ON p.id = booking_stats.publication_id
LEFT JOIN (
    SELECT 
        publication_id,
        COUNT(*) as total_rates,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_rates,
        AVG(base_rate) as avg_rate,
        MIN(base_rate) as min_rate,
        MAX(base_rate) as max_rate
    FROM rates 
    WHERE deleted_at IS NULL
    GROUP BY publication_id
) rate_stats ON p.id = rate_stats.publication_id
WHERE p.deleted_at IS NULL;

-- View for user activity summary
CREATE OR REPLACE VIEW vw_user_activity AS
SELECT 
    u.id,
    u.username,
    u.email,
    u.first_name,
    u.last_name,
    u.role,
    u.status,
    u.last_login_at,
    u.created_at,
    
    -- Activity Statistics
    COALESCE(booking_stats.bookings_created, 0) as bookings_created,
    COALESCE(booking_stats.total_booking_value, 0.00) as total_booking_value,
    COALESCE(client_stats.clients_created, 0) as clients_created,
    COALESCE(rate_stats.rates_created, 0) as rates_created,
    
    -- Recent Activity
    COALESCE(recent_activity.last_booking_date, NULL) as last_booking_date,
    COALESCE(recent_activity.last_client_date, NULL) as last_client_date,
    COALESCE(recent_activity.last_rate_date, NULL) as last_rate_date
    
FROM users u
LEFT JOIN (
    SELECT 
        created_by,
        COUNT(*) as bookings_created,
        SUM(total_amount) as total_booking_value,
        MAX(created_at) as last_booking_date
    FROM bookings 
    WHERE deleted_at IS NULL
    GROUP BY created_by
) booking_stats ON u.id = booking_stats.created_by
LEFT JOIN (
    SELECT 
        created_by,
        COUNT(*) as clients_created,
        MAX(created_at) as last_client_date
    FROM clients 
    WHERE deleted_at IS NULL
    GROUP BY created_by
) client_stats ON u.id = client_stats.created_by
LEFT JOIN (
    SELECT 
        created_by,
        COUNT(*) as rates_created,
        MAX(created_at) as last_rate_date
    FROM rates 
    WHERE deleted_at IS NULL
    GROUP BY created_by
) rate_stats ON u.id = rate_stats.created_by
LEFT JOIN (
    SELECT 
        created_by,
        MAX(CASE WHEN table_name = 'bookings' THEN last_date ELSE NULL END) as last_booking_date,
        MAX(CASE WHEN table_name = 'clients' THEN last_date ELSE NULL END) as last_client_date,
        MAX(CASE WHEN table_name = 'rates' THEN last_date ELSE NULL END) as last_rate_date
    FROM (
        SELECT created_by, 'bookings' as table_name, MAX(created_at) as last_date FROM bookings WHERE deleted_at IS NULL GROUP BY created_by
        UNION ALL
        SELECT created_by, 'clients' as table_name, MAX(created_at) as last_date FROM clients WHERE deleted_at IS NULL GROUP BY created_by
        UNION ALL
        SELECT created_by, 'rates' as table_name, MAX(created_at) as last_date FROM rates WHERE deleted_at IS NULL GROUP BY created_by
    ) activity_union
    GROUP BY created_by
) recent_activity ON u.id = recent_activity.created_by
WHERE u.deleted_at IS NULL;

-- =====================================================
-- 7. PERFORMANCE INDEXES
-- =====================================================

-- Users table indexes
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_last_login ON users(last_login_at);
CREATE INDEX idx_users_deleted_at ON users(deleted_at);

-- API keys table indexes
CREATE INDEX idx_api_keys_key ON api_keys(api_key);
CREATE INDEX idx_api_keys_user_id ON api_keys(user_id);
CREATE INDEX idx_api_keys_status ON api_keys(status);

-- Publications table indexes
CREATE INDEX idx_publications_name ON publications(name);
CREATE INDEX idx_publications_code ON publications(code);
CREATE INDEX idx_publications_type ON publications(publication_type);
CREATE INDEX idx_publications_status ON publications(status);
CREATE INDEX idx_publications_deleted_at ON publications(deleted_at);

-- Color types table indexes
CREATE INDEX idx_color_types_name ON color_types(name);
CREATE INDEX idx_color_types_code ON color_types(code);
CREATE INDEX idx_color_types_status ON color_types(status);
CREATE INDEX idx_color_types_deleted_at ON color_types(deleted_at);

-- Ad categories table indexes
CREATE INDEX idx_ad_categories_name ON ad_categories(name);
CREATE INDEX idx_ad_categories_code ON ad_categories(code);
CREATE INDEX idx_ad_categories_parent_id ON ad_categories(parent_id);
CREATE INDEX idx_ad_categories_level ON ad_categories(level);
CREATE INDEX idx_ad_categories_status ON ad_categories(status);
CREATE INDEX idx_ad_categories_deleted_at ON ad_categories(deleted_at);

-- Ad sizes table indexes
CREATE INDEX idx_ad_sizes_name ON ad_sizes(name);
CREATE INDEX idx_ad_sizes_code ON ad_sizes(code);
CREATE INDEX idx_ad_sizes_area ON ad_sizes(area_sqcm);
CREATE INDEX idx_ad_sizes_status ON ad_sizes(status);
CREATE INDEX idx_ad_sizes_deleted_at ON ad_sizes(deleted_at);

-- Page positions table indexes
CREATE INDEX idx_page_positions_name ON page_positions(name);
CREATE INDEX idx_page_positions_code ON page_positions(code);
CREATE INDEX idx_page_positions_type ON page_positions(position_type);
CREATE INDEX idx_page_positions_status ON page_positions(status);
CREATE INDEX idx_page_positions_deleted_at ON page_positions(deleted_at);

-- Taxes table indexes
CREATE INDEX idx_taxes_name ON taxes(name);
CREATE INDEX idx_taxes_code ON taxes(code);
CREATE INDEX idx_taxes_type ON taxes(tax_type);
CREATE INDEX idx_taxes_effective_dates ON taxes(effective_from, effective_to);
CREATE INDEX idx_taxes_status ON taxes(status);
CREATE INDEX idx_taxes_deleted_at ON taxes(deleted_at);

-- Tax configurations table indexes
CREATE INDEX idx_tax_configs_publication_id ON tax_configurations(publication_id);
CREATE INDEX idx_tax_configs_tax_id ON tax_configurations(tax_id);
CREATE INDEX idx_tax_configs_status ON tax_configurations(status);
CREATE INDEX idx_tax_configs_deleted_at ON tax_configurations(deleted_at);

-- Clients table indexes
CREATE INDEX idx_clients_client_number ON clients(client_number);
CREATE INDEX idx_clients_company_name ON clients(company_name);
CREATE INDEX idx_clients_client_type ON clients(client_type);
CREATE INDEX idx_clients_status ON clients(status);
CREATE INDEX idx_clients_city ON clients(city);
CREATE INDEX idx_clients_region ON clients(region);
CREATE INDEX idx_clients_industry ON clients(industry);
CREATE INDEX idx_clients_deleted_at ON clients(deleted_at);

-- Full-text search index for client company names
CREATE INDEX idx_clients_company_search ON clients(company_name);

-- Client credit table indexes
CREATE INDEX idx_client_credit_client_id ON client_credit(client_id);
CREATE INDEX idx_client_credit_status ON client_credit(credit_status);
CREATE INDEX idx_client_credit_limit ON client_credit(credit_limit);

-- Rates table indexes
CREATE INDEX idx_rates_publication_id ON rates(publication_id);
CREATE INDEX idx_rates_lookup ON rates(publication_id, color_type_id, ad_category_id, ad_size_id, page_position_id);
CREATE INDEX idx_rates_status ON rates(status);
CREATE INDEX idx_rates_effective_from ON rates(effective_from);
CREATE INDEX idx_rates_effective_dates ON rates(effective_from, effective_to);
CREATE INDEX idx_rates_deleted_at ON rates(deleted_at);

-- Bookings table indexes
CREATE INDEX idx_bookings_booking_number ON bookings(booking_number);
CREATE INDEX idx_bookings_client_id ON bookings(client_id);
CREATE INDEX idx_bookings_client_status ON bookings(client_id, status);
CREATE INDEX idx_bookings_publication_id ON bookings(publication_id);
CREATE INDEX idx_bookings_publication_date ON bookings(publication_date);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_bookings_created_at ON bookings(created_at);
CREATE INDEX idx_bookings_rate_id ON bookings(rate_id);
CREATE INDEX idx_bookings_deleted_at ON bookings(deleted_at);

-- File storage table indexes
CREATE INDEX idx_file_storage_client_id ON file_storage(client_id);
CREATE INDEX idx_file_storage_booking_id ON file_storage(booking_id);
CREATE INDEX idx_file_storage_file_type ON file_storage(file_type);

-- Placements table indexes
CREATE INDEX idx_placements_placement_number ON placements(placement_number);
CREATE INDEX idx_placements_client_id ON placements(client_id);
CREATE INDEX idx_placements_booking_id ON placements(booking_id);
CREATE INDEX idx_placements_publication_id ON placements(publication_id);
CREATE INDEX idx_placements_placement_date ON placements(placement_date);
CREATE INDEX idx_placements_status ON placements(status);
CREATE INDEX idx_placements_deleted_at ON placements(deleted_at);

-- Audit logs table indexes
CREATE INDEX idx_audit_logs_table_name ON audit_logs(table_name);
CREATE INDEX idx_audit_logs_action ON audit_logs(action);
CREATE INDEX idx_audit_logs_user_id ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at);

-- SMS logs table indexes
CREATE INDEX idx_sms_logs_recipient ON sms_logs(recipient_phone);
CREATE INDEX idx_sms_logs_status ON sms_logs(status);
CREATE INDEX idx_sms_logs_created_at ON sms_logs(created_at);

-- File uploads table indexes
CREATE INDEX idx_file_uploads_client_id ON file_uploads(client_id);
CREATE INDEX idx_file_uploads_booking_id ON file_uploads(booking_id);
CREATE INDEX idx_file_uploads_type ON file_uploads(file_type);
CREATE INDEX idx_file_uploads_status ON file_uploads(upload_status);
CREATE INDEX idx_file_uploads_created_at ON file_uploads(uploaded_at);

-- Composite indexes for complex queries
CREATE INDEX idx_bookings_client_status_date ON bookings(client_id, status, publication_date);
CREATE INDEX idx_bookings_publication_status_date ON bookings(publication_id, status, publication_date);
CREATE INDEX idx_rates_publication_effective ON rates(publication_id, effective_from, effective_to);
CREATE INDEX idx_clients_type_status ON clients(client_type, status);

-- =====================================================
-- 8. STORED PROCEDURES
-- =====================================================

DELIMITER //

-- Procedure to search bookings with filters
CREATE PROCEDURE sp_search_bookings(
    IN p_client_id INT,
    IN p_status VARCHAR(20),
    IN p_publication_id INT,
    IN p_date_from DATE,
    IN p_date_to DATE,
    IN p_limit INT,
    IN p_offset INT
)
BEGIN
    DECLARE v_sql TEXT DEFAULT 'SELECT * FROM vw_booking_details WHERE 1=1';
    DECLARE v_conditions TEXT DEFAULT '';
    
    -- Build dynamic WHERE conditions
    IF p_client_id IS NOT NULL THEN
        SET v_conditions = CONCAT(v_conditions, ' AND client_id = ', p_client_id);
    END IF;
    
    IF p_status IS NOT NULL AND p_status != '' THEN
        SET v_conditions = CONCAT(v_conditions, ' AND status = ''', p_status, '''');
    END IF;
    
    IF p_publication_id IS NOT NULL THEN
        SET v_conditions = CONCAT(v_conditions, ' AND publication_id = ', p_publication_id);
    END IF;
    
    IF p_date_from IS NOT NULL THEN
        SET v_conditions = CONCAT(v_conditions, ' AND publication_date >= ''', p_date_from, '''');
    END IF;
    
    IF p_date_to IS NOT NULL THEN
        SET v_conditions = CONCAT(v_conditions, ' AND publication_date <= ''', p_date_to, '''');
    END IF;
    
    -- Add conditions to SQL
    SET v_sql = CONCAT(v_sql, v_conditions);
    
    -- Add ordering
    SET v_sql = CONCAT(v_sql, ' ORDER BY created_at DESC');
    
    -- Add pagination
    IF p_limit IS NOT NULL THEN
        SET v_sql = CONCAT(v_sql, ' LIMIT ', p_limit);
        IF p_offset IS NOT NULL THEN
            SET v_sql = CONCAT(v_sql, ' OFFSET ', p_offset);
        END IF;
    END IF;
    
    -- Execute dynamic SQL
    SET @sql = v_sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //

-- Procedure to calculate client debt aging
CREATE PROCEDURE sp_client_debt_aging(IN p_client_id INT)
BEGIN
    SELECT 
        c.id as client_id,
        c.client_number,
        c.company_name,
        cc.credit_limit,
        cc.available_credit,
        cc.used_credit,
        (cc.credit_limit - cc.available_credit) as current_debt,
        
        -- Debt aging analysis
        COALESCE(debt_aging.current_debt, 0) as total_outstanding,
        COALESCE(debt_aging.overdue_30_days, 0) as overdue_30_days,
        COALESCE(debt_aging.overdue_60_days, 0) as overdue_60_days,
        COALESCE(debt_aging.overdue_90_days, 0) as overdue_90_days,
        COALESCE(debt_aging.overdue_90_plus_days, 0) as overdue_90_plus_days,
        
        -- Risk assessment
        CASE 
            WHEN (cc.credit_limit - cc.available_credit) > cc.credit_limit * 0.8 THEN 'HIGH'
            WHEN (cc.credit_limit - cc.available_credit) > cc.credit_limit * 0.6 THEN 'MEDIUM'
            ELSE 'LOW'
        END as risk_level,
        
        -- Payment behavior
        COALESCE(payment_stats.avg_payment_days, 0) as avg_payment_days,
        COALESCE(payment_stats.on_time_payments, 0) as on_time_payments,
        COALESCE(payment_stats.total_payments, 0) as total_payments
        
    FROM clients c
    LEFT JOIN client_credit cc ON c.id = cc.client_id
    LEFT JOIN (
        SELECT 
            client_id,
            SUM(total_amount) as current_debt,
            SUM(CASE WHEN DATEDIFF(CURDATE(), publication_date) > 30 AND DATEDIFF(CURDATE(), publication_date) <= 60 THEN total_amount ELSE 0 END) as overdue_30_days,
            SUM(CASE WHEN DATEDIFF(CURDATE(), publication_date) > 60 AND DATEDIFF(CURDATE(), publication_date) <= 90 THEN total_amount ELSE 0 END) as overdue_60_days,
            SUM(CASE WHEN DATEDIFF(CURDATE(), publication_date) > 90 AND DATEDIFF(CURDATE(), publication_date) <= 120 THEN total_amount ELSE 0 END) as overdue_90_days,
            SUM(CASE WHEN DATEDIFF(CURDATE(), publication_date) > 120 THEN total_amount ELSE 0 END) as overdue_90_plus_days
        FROM bookings 
        WHERE status = 'confirmed' AND deleted_at IS NULL
        GROUP BY client_id
    ) debt_aging ON c.id = debt_aging.client_id
    LEFT JOIN (
        SELECT 
            client_id,
            AVG(DATEDIFF(updated_at, created_at)) as avg_payment_days,
            SUM(CASE WHEN DATEDIFF(updated_at, created_at) <= 30 THEN 1 ELSE 0 END) as on_time_payments,
            COUNT(*) as total_payments
        FROM bookings 
        WHERE status = 'confirmed' AND deleted_at IS NULL
        GROUP BY client_id
    ) payment_stats ON c.id = payment_stats.client_id
    WHERE c.id = p_client_id AND c.deleted_at IS NULL;
END //

-- Procedure to search rates with criteria
CREATE PROCEDURE sp_search_rates(
    IN p_publication_id INT,
    IN p_color_type_id INT,
    IN p_ad_category_id INT,
    IN p_ad_size_id INT,
    IN p_page_position_id INT,
    IN p_effective_date DATE
)
BEGIN
    DECLARE v_effective_date DATE DEFAULT COALESCE(p_effective_date, CURDATE());
    
    SELECT 
        r.*,
        p.name as publication_name,
        ct.name as color_type_name,
        ac.name as ad_category_name,
        ads.name as ad_size_name,
        pp.name as page_position_name,
        (r.base_rate * ct.multiplier * pp.multiplier) as calculated_rate
    FROM rates r
    LEFT JOIN publications p ON r.publication_id = p.id
    LEFT JOIN color_types ct ON r.color_type_id = ct.id
    LEFT JOIN ad_categories ac ON r.ad_category_id = ac.id
    LEFT JOIN ad_sizes ads ON r.ad_size_id = ads.id
    LEFT JOIN page_positions pp ON r.page_position_id = pp.id
    WHERE r.deleted_at IS NULL
    AND r.status = 'active'
    AND r.effective_from <= v_effective_date
    AND (r.effective_to IS NULL OR r.effective_to >= v_effective_date)
    AND (p_publication_id IS NULL OR r.publication_id = p_publication_id)
    AND (p_color_type_id IS NULL OR r.color_type_id = p_color_type_id)
    AND (p_ad_category_id IS NULL OR r.ad_category_id = p_ad_category_id)
    AND (p_ad_size_id IS NULL OR r.ad_size_id = p_ad_size_id)
    AND (p_page_position_id IS NULL OR r.page_position_id = p_page_position_id)
    ORDER BY r.base_rate ASC;
END //

-- Procedure to generate booking number
CREATE PROCEDURE sp_generate_booking_number(OUT p_booking_number VARCHAR(20))
BEGIN
    DECLARE v_year VARCHAR(4);
    DECLARE v_prefix VARCHAR(10);
    DECLARE v_count INT;
    DECLARE v_next_number VARCHAR(4);
    
    SET v_year = YEAR(CURDATE());
    SET v_prefix = CONCAT('BK-', v_year, '-');
    
    -- Get count of existing bookings for this year
    SELECT COUNT(*) INTO v_count 
    FROM bookings 
    WHERE booking_number LIKE CONCAT(v_prefix, '%') 
    AND deleted_at IS NULL;
    
    -- Generate next number
    SET v_next_number = LPAD(v_count + 1, 4, '0');
    SET p_booking_number = CONCAT(v_prefix, v_next_number);
END //

-- Procedure to generate client number
CREATE PROCEDURE sp_generate_client_number(OUT p_client_number VARCHAR(20))
BEGIN
    DECLARE v_year VARCHAR(4);
    DECLARE v_prefix VARCHAR(10);
    DECLARE v_count INT;
    DECLARE v_next_number VARCHAR(4);
    
    SET v_year = YEAR(CURDATE());
    SET v_prefix = CONCAT('CLI-', v_year, '-');
    
    -- Get count of existing clients for this year
    SELECT COUNT(*) INTO v_count 
    FROM clients 
    WHERE client_number LIKE CONCAT(v_prefix, '%') 
    AND deleted_at IS NULL;
    
    -- Generate next number
    SET v_next_number = LPAD(v_count + 1, 4, '0');
    SET p_client_number = CONCAT(v_prefix, v_next_number);
END //

-- Procedure to calculate tax breakdown for a booking
CREATE PROCEDURE sp_calculate_tax_breakdown(
    IN p_base_rate DECIMAL(10,2),
    IN p_publication_id INT,
    IN p_insertions INT,
    OUT p_total_tax DECIMAL(10,2)
)
BEGIN
    DECLARE v_tax_amount DECIMAL(10,2);
    DECLARE v_total_tax DECIMAL(10,2) DEFAULT 0;
    DECLARE done INT DEFAULT FALSE;
    
    DECLARE tax_cursor CURSOR FOR
        SELECT t.rate
        FROM tax_configurations tc
        JOIN taxes t ON tc.tax_id = t.id
        WHERE tc.publication_id = p_publication_id
        AND tc.status = 'active'
        AND t.status = 'active'
        AND t.effective_from <= CURDATE()
        AND (t.effective_to IS NULL OR t.effective_to >= CURDATE())
        AND tc.deleted_at IS NULL
        AND t.deleted_at IS NULL;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN tax_cursor;
    
    tax_loop: LOOP
        FETCH tax_cursor INTO v_tax_amount;
        IF done THEN
            LEAVE tax_loop;
        END IF;
        
        SET v_total_tax = v_total_tax + ((p_base_rate * v_tax_amount / 100) * p_insertions);
    END LOOP;
    
    CLOSE tax_cursor;
    
    SET p_total_tax = v_total_tax;
END //

-- Procedure to update client credit usage
CREATE PROCEDURE sp_update_client_credit(
    IN p_client_id INT,
    IN p_amount DECIMAL(10,2),
    IN p_operation ENUM('add', 'subtract')
)
BEGIN
    DECLARE v_current_used DECIMAL(10,2);
    DECLARE v_credit_limit DECIMAL(12,2);
    DECLARE v_new_used DECIMAL(10,2);
    DECLARE v_new_available DECIMAL(12,2);
    
    -- Get current credit information
    SELECT used_credit, credit_limit INTO v_current_used, v_credit_limit
    FROM client_credit 
    WHERE client_id = p_client_id;
    
    -- Calculate new values
    IF p_operation = 'add' THEN
        SET v_new_used = v_current_used + p_amount;
    ELSE
        SET v_new_used = v_current_used - p_amount;
    END IF;
    
    SET v_new_available = v_credit_limit - v_new_used;
    
    -- Update client credit
    UPDATE client_credit 
    SET 
        used_credit = v_new_used,
        available_credit = v_new_available,
        updated_at = NOW()
    WHERE client_id = p_client_id;
    
    -- Return updated values
    SELECT 
        v_credit_limit as credit_limit,
        v_new_used as used_credit,
        v_new_available as available_credit;
END //

-- Procedure to get dashboard statistics
CREATE PROCEDURE sp_get_dashboard_stats()
BEGIN
    SELECT 
        -- Booking Statistics
        (SELECT COUNT(*) FROM bookings WHERE deleted_at IS NULL) as total_bookings,
        (SELECT COUNT(*) FROM bookings WHERE status = 'draft' AND deleted_at IS NULL) as draft_bookings,
        (SELECT COUNT(*) FROM bookings WHERE status = 'confirmed' AND deleted_at IS NULL) as confirmed_bookings,
        (SELECT COUNT(*) FROM bookings WHERE status = 'cancelled' AND deleted_at IS NULL) as cancelled_bookings,
        
        -- Revenue Statistics
        (SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE status = 'confirmed' AND deleted_at IS NULL) as total_revenue,
        (SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE status = 'confirmed' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND deleted_at IS NULL) as monthly_revenue,
        
        -- Client Statistics
        (SELECT COUNT(*) FROM clients WHERE deleted_at IS NULL) as total_clients,
        (SELECT COUNT(*) FROM clients WHERE status = 'active' AND deleted_at IS NULL) as active_clients,
        
        -- Publication Statistics
        (SELECT COUNT(*) FROM publications WHERE status = 'active' AND deleted_at IS NULL) as active_publications,
        (SELECT COUNT(*) FROM rates WHERE status = 'active' AND deleted_at IS NULL) as active_rates,
        
        -- Recent Activity
        (SELECT COUNT(*) FROM bookings WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND deleted_at IS NULL) as bookings_last_7_days,
        (SELECT COUNT(*) FROM clients WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND deleted_at IS NULL) as clients_last_30_days;
END //

-- RBAC Stored Procedures

-- Procedure to get all permissions for a specific user
CREATE PROCEDURE sp_get_user_permissions(IN p_user_id INT)
BEGIN
    DECLARE v_user_role VARCHAR(20);
    
    -- Get user role
    SELECT role INTO v_user_role FROM users WHERE id = p_user_id AND deleted_at IS NULL;
    
    -- Return permissions from role and user-specific overrides
    SELECT DISTINCT p.code
    FROM permissions p
    WHERE p.deleted_at IS NULL
    AND (
        -- Role permissions
        EXISTS (
            SELECT 1 FROM role_permissions rp 
            WHERE rp.role = v_user_role 
            AND rp.permission_id = p.id
        )
        OR
        -- User-specific permissions (granted)
        EXISTS (
            SELECT 1 FROM user_permissions up 
            WHERE up.user_id = p_user_id 
            AND up.permission_id = p.id 
            AND up.granted = TRUE
            AND (up.expires_at IS NULL OR up.expires_at > NOW())
        )
    )
    AND NOT EXISTS (
        -- Exclude user-specific denials
        SELECT 1 FROM user_permissions up 
        WHERE up.user_id = p_user_id 
        AND up.permission_id = p.id 
        AND up.granted = FALSE
        AND (up.expires_at IS NULL OR up.expires_at > NOW())
    )
    ORDER BY p.code;
END //

-- Procedure to check if user has specific permission
CREATE PROCEDURE sp_check_user_permission(
    IN p_user_id INT,
    IN p_permission_code VARCHAR(100),
    OUT p_has_permission BOOLEAN
)
BEGIN
    DECLARE v_user_role VARCHAR(20);
    DECLARE v_permission_count INT DEFAULT 0;
    
    -- Get user role
    SELECT role INTO v_user_role FROM users WHERE id = p_user_id AND deleted_at IS NULL;
    
    -- Check if user has permission
    SELECT COUNT(*) INTO v_permission_count
    FROM permissions p
    WHERE p.code = p_permission_code
    AND p.deleted_at IS NULL
    AND (
        -- Admin has all permissions
        v_user_role = 'admin'
        OR
        -- Role permissions
        EXISTS (
            SELECT 1 FROM role_permissions rp 
            WHERE rp.role = v_user_role 
            AND rp.permission_id = p.id
        )
        OR
        -- User-specific permissions (granted)
        EXISTS (
            SELECT 1 FROM user_permissions up 
            WHERE up.user_id = p_user_id 
            AND up.permission_id = p.id 
            AND up.granted = TRUE
            AND (up.expires_at IS NULL OR up.expires_at > NOW())
        )
    )
    AND NOT EXISTS (
        -- Exclude user-specific denials
        SELECT 1 FROM user_permissions up 
        WHERE up.user_id = p_user_id 
        AND up.permission_id = p.id 
        AND up.granted = FALSE
        AND (up.expires_at IS NULL OR up.expires_at > NOW())
    );
    
    SET p_has_permission = (v_permission_count > 0);
END //

-- Procedure to get all permissions for a specific role
CREATE PROCEDURE sp_get_role_permissions(IN p_role VARCHAR(20))
BEGIN
    SELECT p.code, p.description, p.resource, p.action
    FROM permissions p
    JOIN role_permissions rp ON p.id = rp.permission_id
    WHERE rp.role = p_role
    AND p.deleted_at IS NULL
    ORDER BY p.resource, p.action;
END //

DELIMITER ;

-- =====================================================
-- 9. SEED DATA
-- =====================================================

-- Insert default admin user
INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, created_at) VALUES
('admin', 'admin@smartad.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', 'active', NOW());

-- Insert sample publications
INSERT INTO publications (name, code, description, publication_type, frequency, circulation, target_audience, contact_person, contact_phone, contact_email, city, region, status, created_by) VALUES
('Daily Graphic', 'DG', 'Ghana\'s leading daily newspaper', 'newspaper', 'daily', 50000, 'General public, professionals', 'John Mensah', '+233-24-123-4567', 'contact@graphic.com.gh', 'Accra', 'Greater Accra', 'active', 1),
('Ghanaian Times', 'GT', 'Government-owned daily newspaper', 'newspaper', 'daily', 30000, 'Government officials, civil servants', 'Mary Asante', '+233-24-234-5678', 'info@ghanaiantimes.com.gh', 'Accra', 'Greater Accra', 'active', 1),
('Business & Financial Times', 'BFT', 'Business-focused weekly publication', 'newspaper', 'weekly', 15000, 'Business professionals, investors', 'Kwame Nkrumah', '+233-24-345-6789', 'editor@bft.com.gh', 'Accra', 'Greater Accra', 'active', 1);

-- Insert color types
INSERT INTO color_types (name, code, description, multiplier, status, created_by) VALUES
('Black & White', 'BW', 'Standard black and white advertisement', 1.00, 'active', 1),
('Spot Color', 'SC', 'Single color addition to black and white', 1.25, 'active', 1),
('Two Color', 'TC', 'Two color advertisement', 1.50, 'active', 1),
('Full Color', 'FC', 'Full color advertisement', 2.00, 'active', 1);

-- Insert ad categories
INSERT INTO ad_categories (name, code, description, level, status, created_by) VALUES
('Classified', 'CLS', 'Classified advertisements', 1, 'active', 1),
('Display', 'DSP', 'Display advertisements', 1, 'active', 1),
('Business', 'BUS', 'Business advertisements', 1, 'active', 1),
('Government', 'GOV', 'Government notices and advertisements', 1, 'active', 1),
('Personal', 'PER', 'Personal announcements', 1, 'active', 1);

-- Insert ad sizes
INSERT INTO ad_sizes (name, code, description, width_cm, height_cm, area_sqcm, status, created_by) VALUES
('Quarter Page', 'QP', 'Quarter page advertisement', 20.0, 25.0, 500.0, 'active', 1),
('Half Page', 'HP', 'Half page advertisement', 20.0, 50.0, 1000.0, 'active', 1),
('Full Page', 'FP', 'Full page advertisement', 20.0, 100.0, 2000.0, 'active', 1),
('Small Box', 'SB', 'Small box advertisement', 5.0, 5.0, 25.0, 'active', 1),
('Medium Box', 'MB', 'Medium box advertisement', 10.0, 10.0, 100.0, 'active', 1);

-- Insert page positions
INSERT INTO page_positions (name, code, description, position_type, multiplier, status, created_by) VALUES
('Front Page', 'FP', 'Front page placement', 'front_page', 3.00, 'active', 1),
('Back Page', 'BP', 'Back page placement', 'back_page', 2.50, 'active', 1),
('Inside Page', 'IP', 'Inside page placement', 'inside_page', 1.00, 'active', 1),
('Special Page', 'SP', 'Special section placement', 'special_page', 1.75, 'active', 1);

-- Insert taxes
INSERT INTO taxes (name, code, rate, description, tax_type, effective_from, status, created_by) VALUES
('VAT', 'VAT', 12.50, 'Value Added Tax', 'vat', '2024-01-01', 'active', 1),
('NHIL', 'NHIL', 2.50, 'National Health Insurance Levy', 'nhil', '2024-01-01', 'active', 1),
('COVID', 'COVID', 1.00, 'COVID-19 Health Recovery Levy', 'covid', '2024-01-01', 'active', 1),
('GETFUND', 'GETFUND', 2.50, 'Ghana Education Trust Fund Levy', 'other', '2024-01-01', 'active', 1);

-- Insert tax configurations for publications
INSERT INTO tax_configurations (publication_id, tax_id, status, created_by) VALUES
(1, 1, 'active', 1), -- Daily Graphic - VAT
(1, 2, 'active', 1), -- Daily Graphic - NHIL
(1, 3, 'active', 1), -- Daily Graphic - COVID
(2, 1, 'active', 1), -- Ghanaian Times - VAT
(2, 2, 'active', 1), -- Ghanaian Times - NHIL
(3, 1, 'active', 1), -- BFT - VAT
(3, 2, 'active', 1); -- BFT - NHIL

-- Insert sample rates
INSERT INTO rates (publication_id, color_type_id, ad_category_id, ad_size_id, page_position_id, base_rate, effective_from, status, created_by) VALUES
(1, 1, 2, 1, 3, 500.00, '2024-01-01', 'active', 1), -- Daily Graphic, BW, Display, Quarter Page, Inside
(1, 1, 2, 2, 3, 1000.00, '2024-01-01', 'active', 1), -- Daily Graphic, BW, Display, Half Page, Inside
(1, 1, 2, 3, 3, 2000.00, '2024-01-01', 'active', 1), -- Daily Graphic, BW, Display, Full Page, Inside
(1, 4, 2, 1, 3, 1000.00, '2024-01-01', 'active', 1), -- Daily Graphic, FC, Display, Quarter Page, Inside
(2, 1, 2, 1, 3, 300.00, '2024-01-01', 'active', 1), -- Ghanaian Times, BW, Display, Quarter Page, Inside
(2, 1, 2, 2, 3, 600.00, '2024-01-01', 'active', 1), -- Ghanaian Times, BW, Display, Half Page, Inside
(3, 1, 3, 1, 3, 800.00, '2024-01-01', 'active', 1), -- BFT, BW, Business, Quarter Page, Inside
(3, 1, 3, 2, 3, 1600.00, '2024-01-01', 'active', 1); -- BFT, BW, Business, Half Page, Inside

-- Insert sample clients
INSERT INTO clients (client_number, company_name, client_type, contact_person, contact_phone, contact_email, city, region, industry, company_size, credit_limit, payment_terms, status, created_by) VALUES
('CLI-2024-0001', 'ABC Company Ltd', 'corporate', 'John Doe', '+233-24-111-1111', 'john@abc.com', 'Accra', 'Greater Accra', 'Manufacturing', 'large', 50000.00, '30 days', 'active', 1),
('CLI-2024-0002', 'XYZ Services', 'corporate', 'Jane Smith', '+233-24-222-2222', 'jane@xyz.com', 'Kumasi', 'Ashanti', 'Services', 'medium', 25000.00, '15 days', 'active', 1),
('CLI-2024-0003', 'Tech Solutions Inc', 'corporate', 'Mike Johnson', '+233-24-333-3333', 'mike@tech.com', 'Accra', 'Greater Accra', 'Technology', 'medium', 30000.00, '30 days', 'active', 1);

-- Insert client credit records
INSERT INTO client_credit (client_id, credit_limit, available_credit, used_credit, payment_terms, credit_status, created_by) VALUES
(1, 50000.00, 50000.00, 0.00, '30 days', 'active', 1),
(2, 25000.00, 25000.00, 0.00, '15 days', 'active', 1),
(3, 30000.00, 30000.00, 0.00, '30 days', 'active', 1);

-- Insert RBAC Permissions
INSERT INTO permissions (code, description, resource, action, created_by) VALUES
-- Dashboard permissions
('dashboard.view', 'View dashboard', 'dashboard', 'view', 1),

-- Booking permissions
('bookings.view', 'View bookings', 'bookings', 'view', 1),
('bookings.create', 'Create bookings', 'bookings', 'create', 1),
('bookings.edit', 'Edit bookings', 'bookings', 'edit', 1),
('bookings.delete', 'Delete bookings', 'bookings', 'delete', 1),
('bookings.manage', 'Manage all bookings', 'bookings', 'manage', 1),

-- Client permissions
('clients.view', 'View clients', 'clients', 'view', 1),
('clients.create', 'Create clients', 'clients', 'create', 1),
('clients.edit', 'Edit clients', 'clients', 'edit', 1),
('clients.delete', 'Delete clients', 'clients', 'delete', 1),
('clients.manage', 'Manage all clients', 'clients', 'manage', 1),

-- Rate permissions
('rates.view', 'View rates', 'rates', 'view', 1),
('rates.create', 'Create rates', 'rates', 'create', 1),
('rates.edit', 'Edit rates', 'rates', 'edit', 1),
('rates.delete', 'Delete rates', 'rates', 'delete', 1),
('rates.manage', 'Manage all rates', 'rates', 'manage', 1),

-- User permissions
('users.view', 'View users', 'users', 'view', 1),
('users.create', 'Create users', 'users', 'create', 1),
('users.edit', 'Edit users', 'users', 'edit', 1),
('users.delete', 'Delete users', 'users', 'delete', 1),
('users.manage', 'Manage all users', 'users', 'manage', 1),

-- Publication permissions
('publications.view', 'View publications', 'publications', 'view', 1),
('publications.create', 'Create publications', 'publications', 'create', 1),
('publications.edit', 'Edit publications', 'publications', 'edit', 1),
('publications.delete', 'Delete publications', 'publications', 'delete', 1),
('publications.manage', 'Manage all publications', 'publications', 'manage', 1),

-- Report permissions
('reports.view', 'View reports', 'reports', 'view', 1),
('reports.create', 'Create reports', 'reports', 'create', 1),
('reports.export', 'Export reports', 'reports', 'export', 1),

-- System permissions
('system.settings', 'Manage system settings', 'system', 'settings', 1),
('system.backup', 'Manage backups', 'system', 'backup', 1),
('system.logs', 'View system logs', 'system', 'logs', 1);

-- Insert Role Permissions
-- Admin gets all permissions
INSERT INTO role_permissions (role, permission_id, created_by)
SELECT 'admin', id, 1 FROM permissions WHERE deleted_at IS NULL;

-- Manager permissions (everything except user management and system settings)
INSERT INTO role_permissions (role, permission_id, created_by)
SELECT 'manager', id, 1 FROM permissions 
WHERE deleted_at IS NULL 
AND code NOT IN ('users.manage', 'users.delete', 'system.settings', 'system.backup', 'system.logs');

-- User permissions (basic CRUD for own data, view-only for others)
INSERT INTO role_permissions (role, permission_id, created_by)
SELECT 'user', id, 1 FROM permissions 
WHERE deleted_at IS NULL 
AND code IN (
    'dashboard.view',
    'bookings.view', 'bookings.create', 'bookings.edit',
    'clients.view', 'clients.create', 'clients.edit',
    'rates.view',
    'publications.view',
    'reports.view'
);

-- =====================================================
-- 10. TABLE COMMENTS
-- =====================================================

ALTER TABLE users COMMENT = 'System users and authentication';
ALTER TABLE api_keys COMMENT = 'API keys for external integrations';
ALTER TABLE publications COMMENT = 'Media publications for advertising';
ALTER TABLE color_types COMMENT = 'Color types for advertisements';
ALTER TABLE ad_categories COMMENT = 'Advertisement categories';
ALTER TABLE ad_sizes COMMENT = 'Advertisement sizes';
ALTER TABLE page_positions COMMENT = 'Page positions for advertisements';
ALTER TABLE taxes COMMENT = 'Tax rates and configurations';
ALTER TABLE tax_configurations COMMENT = 'Tax configurations per publication';
ALTER TABLE clients COMMENT = 'Client information and details';
ALTER TABLE client_credit COMMENT = 'Client credit limits and usage';
ALTER TABLE rates COMMENT = 'Advertising rates by publication and criteria';
ALTER TABLE bookings COMMENT = 'Advertisement bookings and orders';
ALTER TABLE file_storage COMMENT = 'File storage for documents and materials';
ALTER TABLE placements COMMENT = 'Advertisement placements and scheduling';
ALTER TABLE audit_logs COMMENT = 'System audit trail and change tracking';
ALTER TABLE sms_logs COMMENT = 'SMS delivery tracking and logs';
ALTER TABLE file_uploads COMMENT = 'File upload tracking and management';
ALTER TABLE permissions COMMENT = 'Granular permission definitions for RBAC';
ALTER TABLE role_permissions COMMENT = 'Maps permissions to roles for RBAC system';
ALTER TABLE user_permissions COMMENT = 'Individual user permission overrides for specific cases';

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================

SELECT 'Database setup completed successfully!' as message,
       'u528309675_smartdbs' as database_name,
       NOW() as completion_time;
