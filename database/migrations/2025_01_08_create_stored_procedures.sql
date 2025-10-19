-- Stored Procedures for Business Logic
-- Migration: 2025_01_08_create_stored_procedures.sql
-- This migration creates stored procedures for complex business operations

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
    DECLARE v_credit_limit DECIMAL(10,2);
    DECLARE v_new_used DECIMAL(10,2);
    DECLARE v_new_available DECIMAL(10,2);
    
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

DELIMITER ;

-- Add comments to procedures
ALTER PROCEDURE sp_search_bookings COMMENT = 'Search bookings with multiple filters';
ALTER PROCEDURE sp_client_debt_aging COMMENT = 'Calculate client debt aging analysis';
ALTER PROCEDURE sp_search_rates COMMENT = 'Search rates with criteria and effective date';
ALTER PROCEDURE sp_generate_booking_number COMMENT = 'Generate unique booking number';
ALTER PROCEDURE sp_generate_client_number COMMENT = 'Generate unique client number';
ALTER PROCEDURE sp_calculate_tax_breakdown COMMENT = 'Calculate tax breakdown for booking';
ALTER PROCEDURE sp_update_client_credit COMMENT = 'Update client credit usage';
ALTER PROCEDURE sp_get_dashboard_stats COMMENT = 'Get dashboard statistics';
