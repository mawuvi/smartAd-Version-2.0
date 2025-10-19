-- Database Views for Complex Queries
-- Migration: 2025_01_08_create_performance_views.sql
-- This migration creates views to optimize complex JOIN queries

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
        MAX(created_at) as last_booking_date
    FROM bookings 
    WHERE deleted_at IS NULL
    GROUP BY created_by
) recent_activity ON u.id = recent_activity.created_by
WHERE u.deleted_at IS NULL;

-- Add comments to views
ALTER VIEW vw_booking_details COMMENT = 'Complete booking details with all related information';
ALTER VIEW vw_client_summary COMMENT = 'Client summary with credit and booking statistics';
ALTER VIEW vw_rate_details COMMENT = 'Rate details with all related information and calculations';
ALTER VIEW vw_publication_taxes COMMENT = 'Publication tax configurations';
ALTER VIEW vw_publication_stats COMMENT = 'Publication statistics and performance metrics';
ALTER VIEW vw_user_activity COMMENT = 'User activity and performance statistics';
