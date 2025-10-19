-- Complete Database Schema for smartAd System
-- Migration: 2025_01_08_create_complete_schema.sql
-- This migration creates the entire database structure from scratch

-- Drop existing tables if they exist (in correct order to handle foreign keys)
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
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS api_keys;

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
);

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
);

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
);

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
);

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
);

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
);

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
);

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
);

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
);

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
);

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
);

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
);

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
);

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
);

-- Create indexes for performance
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status ON users(status);

CREATE INDEX idx_api_keys_key ON api_keys(api_key);
CREATE INDEX idx_api_keys_user_id ON api_keys(user_id);
CREATE INDEX idx_api_keys_status ON api_keys(status);

CREATE INDEX idx_publications_name ON publications(name);
CREATE INDEX idx_publications_code ON publications(code);
CREATE INDEX idx_publications_status ON publications(status);

CREATE INDEX idx_color_types_name ON color_types(name);
CREATE INDEX idx_color_types_code ON color_types(code);

CREATE INDEX idx_ad_categories_name ON ad_categories(name);
CREATE INDEX idx_ad_categories_code ON ad_categories(code);
CREATE INDEX idx_ad_categories_parent_id ON ad_categories(parent_id);

CREATE INDEX idx_ad_sizes_name ON ad_sizes(name);
CREATE INDEX idx_ad_sizes_code ON ad_sizes(code);

CREATE INDEX idx_page_positions_name ON page_positions(name);
CREATE INDEX idx_page_positions_code ON page_positions(code);

CREATE INDEX idx_taxes_name ON taxes(name);
CREATE INDEX idx_taxes_code ON taxes(code);
CREATE INDEX idx_taxes_status ON taxes(status);

CREATE INDEX idx_tax_configurations_publication_id ON tax_configurations(publication_id);
CREATE INDEX idx_tax_configurations_tax_id ON tax_configurations(tax_id);

CREATE INDEX idx_clients_client_number ON clients(client_number);
CREATE INDEX idx_clients_company_name ON clients(company_name);
CREATE INDEX idx_clients_client_type ON clients(client_type);
CREATE INDEX idx_clients_status ON clients(status);
CREATE INDEX idx_clients_created_by ON clients(created_by);

CREATE INDEX idx_client_credit_client_id ON client_credit(client_id);
CREATE INDEX idx_client_credit_status ON client_credit(credit_status);

CREATE INDEX idx_rates_publication_id ON rates(publication_id);
CREATE INDEX idx_rates_lookup ON rates(publication_id, color_type_id, ad_category_id, ad_size_id, page_position_id);
CREATE INDEX idx_rates_status ON rates(status);
CREATE INDEX idx_rates_effective_from ON rates(effective_from);

CREATE INDEX idx_bookings_booking_number ON bookings(booking_number);
CREATE INDEX idx_bookings_client_id ON bookings(client_id);
CREATE INDEX idx_bookings_client_status ON bookings(client_id, status);
CREATE INDEX idx_bookings_publication_id ON bookings(publication_id);
CREATE INDEX idx_bookings_publication_date ON bookings(publication_date);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_bookings_created_at ON bookings(created_at);
CREATE INDEX idx_bookings_rate_id ON bookings(rate_id);

CREATE INDEX idx_file_storage_client_id ON file_storage(client_id);
CREATE INDEX idx_file_storage_booking_id ON file_storage(booking_id);
CREATE INDEX idx_file_storage_file_type ON file_storage(file_type);

-- Add comments to tables
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
