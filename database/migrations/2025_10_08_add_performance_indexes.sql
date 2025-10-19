-- Database Performance Indexes
-- Migration: 2025_10_08_add_performance_indexes.sql
-- This migration adds strategic indexes to optimize common search patterns

-- Bookings table indexes
CREATE INDEX IF NOT EXISTS idx_bookings_client_status ON bookings(client_id, status);
CREATE INDEX IF NOT EXISTS idx_bookings_publication_date ON bookings(publication_date);
CREATE INDEX IF NOT EXISTS idx_bookings_created_at ON bookings(created_at);
CREATE INDEX IF NOT EXISTS idx_bookings_status ON bookings(status);
CREATE INDEX IF NOT EXISTS idx_bookings_publication_id ON bookings(publication_id);
CREATE INDEX IF NOT EXISTS idx_bookings_rate_id ON bookings(rate_id);
CREATE INDEX IF NOT EXISTS idx_bookings_deleted_at ON bookings(deleted_at);

-- Clients table indexes
CREATE INDEX IF NOT EXISTS idx_clients_company_name ON clients(company_name);
CREATE INDEX IF NOT EXISTS idx_clients_client_number ON clients(client_number);
CREATE INDEX IF NOT EXISTS idx_clients_client_type ON clients(client_type);
CREATE INDEX IF NOT EXISTS idx_clients_status ON clients(status);
CREATE INDEX IF NOT EXISTS idx_clients_city ON clients(city);
CREATE INDEX IF NOT EXISTS idx_clients_region ON clients(region);
CREATE INDEX IF NOT EXISTS idx_clients_industry ON clients(industry);
CREATE INDEX IF NOT EXISTS idx_clients_deleted_at ON clients(deleted_at);

-- Full-text search index for client company names
CREATE INDEX IF NOT EXISTS idx_clients_company_search ON clients(company_name);

-- Rates table indexes
CREATE INDEX IF NOT EXISTS idx_rates_lookup ON rates(publication_id, color_type_id, ad_category_id, ad_size_id, page_position_id);
CREATE INDEX IF NOT EXISTS idx_rates_publication_id ON rates(publication_id);
CREATE INDEX IF NOT EXISTS idx_rates_effective_dates ON rates(effective_from, effective_to);
CREATE INDEX IF NOT EXISTS idx_rates_status ON rates(status);
CREATE INDEX IF NOT EXISTS idx_rates_deleted_at ON rates(deleted_at);

-- Publications table indexes
CREATE INDEX IF NOT EXISTS idx_publications_name ON publications(name);
CREATE INDEX IF NOT EXISTS idx_publications_code ON publications(code);
CREATE INDEX IF NOT EXISTS idx_publications_type ON publications(publication_type);
CREATE INDEX IF NOT EXISTS idx_publications_status ON publications(status);
CREATE INDEX IF NOT EXISTS idx_publications_deleted_at ON publications(deleted_at);

-- Color types table indexes
CREATE INDEX IF NOT EXISTS idx_color_types_name ON color_types(name);
CREATE INDEX IF NOT EXISTS idx_color_types_code ON color_types(code);
CREATE INDEX IF NOT EXISTS idx_color_types_status ON color_types(status);
CREATE INDEX IF NOT EXISTS idx_color_types_deleted_at ON color_types(deleted_at);

-- Ad categories table indexes
CREATE INDEX IF NOT EXISTS idx_ad_categories_name ON ad_categories(name);
CREATE INDEX IF NOT EXISTS idx_ad_categories_code ON ad_categories(code);
CREATE INDEX IF NOT EXISTS idx_ad_categories_parent_id ON ad_categories(parent_id);
CREATE INDEX IF NOT EXISTS idx_ad_categories_level ON ad_categories(level);
CREATE INDEX IF NOT EXISTS idx_ad_categories_status ON ad_categories(status);
CREATE INDEX IF NOT EXISTS idx_ad_categories_deleted_at ON ad_categories(deleted_at);

-- Ad sizes table indexes
CREATE INDEX IF NOT EXISTS idx_ad_sizes_name ON ad_sizes(name);
CREATE INDEX IF NOT EXISTS idx_ad_sizes_code ON ad_sizes(code);
CREATE INDEX IF NOT EXISTS idx_ad_sizes_area ON ad_sizes(area_sqcm);
CREATE INDEX IF NOT EXISTS idx_ad_sizes_status ON ad_sizes(status);
CREATE INDEX IF NOT EXISTS idx_ad_sizes_deleted_at ON ad_sizes(deleted_at);

-- Page positions table indexes
CREATE INDEX IF NOT EXISTS idx_page_positions_name ON page_positions(name);
CREATE INDEX IF NOT EXISTS idx_page_positions_code ON page_positions(code);
CREATE INDEX IF NOT EXISTS idx_page_positions_type ON page_positions(position_type);
CREATE INDEX IF NOT EXISTS idx_page_positions_status ON page_positions(status);
CREATE INDEX IF NOT EXISTS idx_page_positions_deleted_at ON page_positions(deleted_at);

-- Users table indexes
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);
CREATE INDEX IF NOT EXISTS idx_users_last_login ON users(last_login_at);
CREATE INDEX IF NOT EXISTS idx_users_deleted_at ON users(deleted_at);

-- Client credit table indexes
CREATE INDEX IF NOT EXISTS idx_client_credit_client_id ON client_credit(client_id);
CREATE INDEX IF NOT EXISTS idx_client_credit_status ON client_credit(credit_status);
CREATE INDEX IF NOT EXISTS idx_client_credit_limit ON client_credit(credit_limit);

-- Tax configurations table indexes
CREATE INDEX IF NOT EXISTS idx_tax_configs_publication_id ON tax_configurations(publication_id);
CREATE INDEX IF NOT EXISTS idx_tax_configs_tax_id ON tax_configurations(tax_id);
CREATE INDEX IF NOT EXISTS idx_tax_configs_status ON tax_configurations(status);
CREATE INDEX IF NOT EXISTS idx_tax_configs_deleted_at ON tax_configurations(deleted_at);

-- Taxes table indexes
CREATE INDEX IF NOT EXISTS idx_taxes_name ON taxes(name);
CREATE INDEX IF NOT EXISTS idx_taxes_code ON taxes(code);
CREATE INDEX IF NOT EXISTS idx_taxes_type ON taxes(tax_type);
CREATE INDEX IF NOT EXISTS idx_taxes_effective_dates ON taxes(effective_from, effective_to);
CREATE INDEX IF NOT EXISTS idx_taxes_status ON taxes(status);
CREATE INDEX IF NOT EXISTS idx_taxes_deleted_at ON taxes(deleted_at);

-- Composite indexes for complex queries
CREATE INDEX IF NOT EXISTS idx_bookings_client_status_date ON bookings(client_id, status, publication_date);
CREATE INDEX IF NOT EXISTS idx_bookings_publication_status_date ON bookings(publication_id, status, publication_date);
CREATE INDEX IF NOT EXISTS idx_rates_publication_effective ON rates(publication_id, effective_from, effective_to);
CREATE INDEX IF NOT EXISTS idx_clients_type_status ON clients(client_type, status);

-- Indexes for audit and logging
CREATE INDEX IF NOT EXISTS idx_audit_logs_table_name ON audit_logs(table_name);
CREATE INDEX IF NOT EXISTS idx_audit_logs_action ON audit_logs(action);
CREATE INDEX IF NOT EXISTS idx_audit_logs_user_id ON audit_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_audit_logs_created_at ON audit_logs(created_at);

-- Indexes for SMS logs
CREATE INDEX IF NOT EXISTS idx_sms_logs_recipient ON sms_logs(recipient_phone);
CREATE INDEX IF NOT EXISTS idx_sms_logs_status ON sms_logs(status);
CREATE INDEX IF NOT EXISTS idx_sms_logs_created_at ON sms_logs(created_at);

-- Indexes for file uploads
CREATE INDEX IF NOT EXISTS idx_file_uploads_client_id ON file_uploads(client_id);
CREATE INDEX IF NOT EXISTS idx_file_uploads_booking_id ON file_uploads(booking_id);
CREATE INDEX IF NOT EXISTS idx_file_uploads_type ON file_uploads(file_type);
CREATE INDEX IF NOT EXISTS idx_file_uploads_status ON file_uploads(status);
CREATE INDEX IF NOT EXISTS idx_file_uploads_created_at ON file_uploads(created_at);

-- Add comments to indexes for documentation
ALTER TABLE bookings COMMENT = 'Bookings table with performance indexes for client, status, and date queries';
ALTER TABLE clients COMMENT = 'Clients table with performance indexes for name, type, and location queries';
ALTER TABLE rates COMMENT = 'Rates table with performance indexes for lookup and effective date queries';
ALTER TABLE publications COMMENT = 'Publications table with performance indexes for name and type queries';
ALTER TABLE users COMMENT = 'Users table with performance indexes for username, email, and role queries';

