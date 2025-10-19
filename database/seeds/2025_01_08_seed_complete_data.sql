-- Comprehensive Seed Data for smartAd System
-- Migration: 2025_01_08_seed_complete_data.sql
-- This migration populates all tables with realistic test data

-- Clear existing data (in reverse order to handle foreign keys)
DELETE FROM file_storage;
DELETE FROM bookings;
DELETE FROM rates;
DELETE FROM client_credit;
DELETE FROM clients;
DELETE FROM tax_configurations;
DELETE FROM taxes;
DELETE FROM page_positions;
DELETE FROM ad_sizes;
DELETE FROM ad_categories;
DELETE FROM color_types;
DELETE FROM publications;
DELETE FROM api_keys;
DELETE FROM users;

-- Reset auto increment counters
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE api_keys AUTO_INCREMENT = 1;
ALTER TABLE publications AUTO_INCREMENT = 1;
ALTER TABLE color_types AUTO_INCREMENT = 1;
ALTER TABLE ad_categories AUTO_INCREMENT = 1;
ALTER TABLE ad_sizes AUTO_INCREMENT = 1;
ALTER TABLE page_positions AUTO_INCREMENT = 1;
ALTER TABLE taxes AUTO_INCREMENT = 1;
ALTER TABLE tax_configurations AUTO_INCREMENT = 1;
ALTER TABLE clients AUTO_INCREMENT = 1;
ALTER TABLE client_credit AUTO_INCREMENT = 1;
ALTER TABLE rates AUTO_INCREMENT = 1;
ALTER TABLE bookings AUTO_INCREMENT = 1;
ALTER TABLE file_storage AUTO_INCREMENT = 1;

-- Insert Users
INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, created_by) VALUES
('admin', 'admin@smartad.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', 'active', 1),
('manager1', 'manager@smartad.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Manager', 'manager', 'active', 1),
('user1', 'user@smartad.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'User', 'user', 'active', 1);

-- Insert Publications
INSERT INTO publications (name, code, description, publication_type, frequency, circulation, target_audience, contact_person, contact_phone, contact_email, address, city, region, status, created_by) VALUES
('Daily Graphic', 'DG', 'Ghana\'s leading daily newspaper', 'newspaper', 'daily', 150000, 'General public, professionals', 'Kwame Asante', '+233-24-123-4567', 'kasante@graphic.com.gh', 'Graphic Road, Accra', 'Accra', 'Greater Accra', 'active', 1),
('Ghanaian Times', 'GT', 'Government-owned daily newspaper', 'newspaper', 'daily', 120000, 'Government officials, civil servants', 'Ama Serwaa', '+233-24-234-5678', 'aserwaa@ghanaiantimes.com.gh', 'Times House, Accra', 'Accra', 'Greater Accra', 'active', 1),
('Business & Financial Times', 'BFT', 'Business and financial news', 'newspaper', 'daily', 45000, 'Business professionals, investors', 'Kofi Mensah', '+233-24-345-6789', 'kmensah@bft.com.gh', 'BFT House, Accra', 'Accra', 'Greater Accra', 'active', 1),
('The Mirror', 'MIRROR', 'Weekend newspaper', 'newspaper', 'weekly', 80000, 'Weekend readers, families', 'Efua Boateng', '+233-24-456-7890', 'eboateng@mirror.com.gh', 'Mirror House, Accra', 'Accra', 'Greater Accra', 'active', 1),
('Citi FM', 'CITI', 'Leading radio station', 'radio', 'daily', 2000000, 'Urban youth, professionals', 'Yaw Osei', '+233-24-567-8901', 'yosei@citifmonline.com', 'Citi FM House, Accra', 'Accra', 'Greater Accra', 'active', 1);

-- Insert Color Types
INSERT INTO color_types (name, code, description, multiplier, status, created_by) VALUES
('Black & White', 'BW', 'Standard black and white advertisement', 1.00, 'active', 1),
('Spot Color', 'SC', 'Black and white with one additional color', 1.25, 'active', 1),
('Two Color', '2C', 'Two colors including black', 1.50, 'active', 1),
('Full Color', '4C', 'Full color (CMYK) advertisement', 2.00, 'active', 1),
('Premium Color', 'PC', 'High-quality color printing', 2.50, 'active', 1);

-- Insert Ad Categories
INSERT INTO ad_categories (name, code, description, parent_id, level, status, created_by) VALUES
('Business & Finance', 'BF', 'Business and financial services', NULL, 1, 'active', 1),
('Banking', 'BANK', 'Banking services', 1, 2, 'active', 1),
('Insurance', 'INS', 'Insurance services', 1, 2, 'active', 1),
('Investment', 'INV', 'Investment services', 1, 2, 'active', 1),
('Consumer Goods', 'CG', 'Consumer products and services', NULL, 1, 'active', 1),
('Food & Beverage', 'FB', 'Food and beverage products', 5, 2, 'active', 1),
('Electronics', 'ELEC', 'Electronic products', 5, 2, 'active', 1),
('Fashion', 'FASH', 'Fashion and clothing', 5, 2, 'active', 1),
('Healthcare', 'HC', 'Healthcare services', NULL, 1, 'active', 1),
('Hospitals', 'HOSP', 'Hospital services', 9, 2, 'active', 1),
('Pharmaceuticals', 'PHARM', 'Pharmaceutical products', 9, 2, 'active', 1),
('Education', 'EDU', 'Educational services', NULL, 1, 'active', 1),
('Schools', 'SCH', 'Educational institutions', 12, 2, 'active', 1),
('Training', 'TRAIN', 'Professional training', 12, 2, 'active', 1),
('Government', 'GOV', 'Government announcements', NULL, 1, 'active', 1),
('Public Notice', 'PN', 'Public notices and announcements', 15, 2, 'active', 1),
('Tender', 'TEND', 'Government tenders', 15, 2, 'active', 1);

-- Insert Ad Sizes
INSERT INTO ad_sizes (name, code, description, width_cm, height_cm, area_sqcm, status, created_by) VALUES
('Full Page', 'FP', 'Full page advertisement', 30.0, 40.0, 1200.0, 'active', 1),
('Half Page', 'HP', 'Half page advertisement', 30.0, 20.0, 600.0, 'active', 1),
('Quarter Page', 'QP', 'Quarter page advertisement', 15.0, 20.0, 300.0, 'active', 1),
('Eighth Page', 'EP', 'Eighth page advertisement', 15.0, 10.0, 150.0, 'active', 1),
('Business Card', 'BC', 'Business card size', 9.0, 5.5, 49.5, 'active', 1),
('Strip', 'STRIP', 'Horizontal strip', 30.0, 5.0, 150.0, 'active', 1),
('Column Inch', 'CI', 'Single column inch', 4.0, 2.5, 10.0, 'active', 1),
('Double Column', 'DC', 'Double column', 8.0, 5.0, 40.0, 'active', 1);

-- Insert Page Positions
INSERT INTO page_positions (name, code, description, position_type, multiplier, status, created_by) VALUES
('Front Page', 'FP', 'Front page advertisement', 'front_page', 3.00, 'active', 1),
('Back Page', 'BP', 'Back page advertisement', 'back_page', 2.50, 'active', 1),
('Inside Front', 'IF', 'Inside front page', 'inside_page', 1.50, 'active', 1),
('Inside Back', 'IB', 'Inside back page', 'inside_page', 1.50, 'active', 1),
('Center Spread', 'CS', 'Center spread', 'special_page', 2.00, 'active', 1),
('Top of Page', 'TOP', 'Top of any page', 'inside_page', 1.25, 'active', 1),
('Bottom of Page', 'BOT', 'Bottom of any page', 'inside_page', 1.00, 'active', 1),
('Middle of Page', 'MID', 'Middle of any page', 'inside_page', 1.00, 'active', 1);

-- Insert Taxes
INSERT INTO taxes (name, code, rate, description, tax_type, status, effective_from, effective_to, created_by) VALUES
('Value Added Tax', 'VAT', 15.00, 'Standard VAT rate', 'vat', 'active', '2020-01-01', NULL, 1),
('National Health Insurance Levy', 'NHIL', 2.50, 'NHIL for healthcare funding', 'nhil', 'active', '2020-01-01', NULL, 1),
('COVID-19 Health Levy', 'CHL', 1.00, 'COVID-19 health levy', 'covid', 'active', '2021-01-01', NULL, 1),
('Ghana Education Trust Fund', 'GETFUND', 2.50, 'Education trust fund levy', 'other', 'active', '2020-01-01', NULL, 1);

-- Insert Tax Configurations (apply all taxes to all publications)
INSERT INTO tax_configurations (publication_id, tax_id, status, created_by)
SELECT p.id, t.id, 'active', 1
FROM publications p
CROSS JOIN taxes t
WHERE p.status = 'active' AND t.status = 'active';

-- Insert Clients
INSERT INTO clients (client_number, company_name, client_type, contact_person, contact_phone, contact_email, email, address, client_address, city, region, postal_code, country, website, industry, company_size, annual_revenue, credit_limit, payment_terms, preferred_contact_method, notes, status, created_by) VALUES
('CLI-2025-0001', 'Ghana Commercial Bank', 'corporate', 'Kwame Asante', '+233-24-123-4567', 'kasante@gcb.com.gh', 'info@gcb.com.gh', 'High Street, Accra', 'High Street, Accra', 'Accra', 'Greater Accra', 'GA-123-4567', 'Ghana', 'https://gcb.com.gh', 'Banking', 'large', 1000000000.00, 50000.00, '30 days', 'email', 'Major banking client with regular advertising needs', 'active', 1),
('CLI-2025-0002', 'MTN Ghana', 'corporate', 'Ama Serwaa', '+233-24-234-5678', 'aserwaa@mtn.com.gh', 'corporate@mtn.com.gh', 'MTN House, Accra', 'MTN House, Accra', 'Accra', 'Greater Accra', 'GA-234-5678', 'Ghana', 'https://mtn.com.gh', 'Telecommunications', 'large', 2000000000.00, 75000.00, '45 days', 'phone', 'Leading telecom provider with extensive advertising campaigns', 'active', 1),
('CLI-2025-0003', 'Shoprite Ghana', 'corporate', 'Kofi Mensah', '+233-24-345-6789', 'kmensah@shoprite.com.gh', 'marketing@shoprite.com.gh', 'Accra Mall, Accra', 'Accra Mall, Accra', 'Accra', 'Greater Accra', 'GA-345-6789', 'Ghana', 'https://shoprite.com.gh', 'Retail', 'large', 500000000.00, 30000.00, '30 days', 'email', 'Retail chain with weekly promotional advertising', 'active', 1),
('CLI-2025-0004', 'Ecobank Ghana', 'corporate', 'Efua Boateng', '+233-24-456-7890', 'eboateng@ecobank.com.gh', 'marketing@ecobank.com.gh', 'Independence Avenue, Accra', 'Independence Avenue, Accra', 'Accra', 'Greater Accra', 'GA-456-7890', 'Ghana', 'https://ecobank.com.gh', 'Banking', 'large', 800000000.00, 40000.00, '30 days', 'email', 'Pan-African bank with regular financial product advertising', 'active', 1),
('CLI-2025-0005', 'Unilever Ghana', 'corporate', 'Yaw Osei', '+233-24-567-8901', 'yosei@unilever.com.gh', 'brands@unilever.com.gh', 'Industrial Area, Tema', 'Industrial Area, Tema', 'Tema', 'Greater Accra', 'GA-567-8901', 'Ghana', 'https://unilever.com.gh', 'Consumer Goods', 'large', 300000000.00, 60000.00, '45 days', 'email', 'FMCG company with multiple brand advertising needs', 'active', 1),
('CLI-2025-0006', 'Kumasi Asante Kotoko FC', 'corporate', 'Nana Yaw', '+233-24-678-9012', 'nyaw@kotoko.com.gh', 'info@kotoko.com.gh', 'Baba Yara Stadium, Kumasi', 'Baba Yara Stadium, Kumasi', 'Kumasi', 'Ashanti', 'AS-678-9012', 'Ghana', 'https://kotoko.com.gh', 'Sports', 'medium', 5000000.00, 10000.00, '15 days', 'phone', 'Premier league football club with seasonal advertising', 'active', 1),
('CLI-2025-0007', 'Ghana Education Service', 'government', 'Dr. Comfort Asante', '+233-24-789-0123', 'casante@ges.gov.gh', 'info@ges.gov.gh', 'Ministry of Education, Accra', 'Ministry of Education, Accra', 'Accra', 'Greater Accra', 'GA-789-0123', 'Ghana', 'https://ges.gov.gh', 'Education', 'large', 100000000.00, 25000.00, '60 days', 'email', 'Government education agency with public service announcements', 'active', 1),
('CLI-2025-0008', 'Melcom Ghana', 'corporate', 'Patience Adjei', '+233-24-890-1234', 'padjei@melcom.com.gh', 'marketing@melcom.com.gh', 'Ring Road, Accra', 'Ring Road, Accra', 'Accra', 'Greater Accra', 'GA-890-1234', 'Ghana', 'https://melcom.com.gh', 'Retail', 'large', 200000000.00, 35000.00, '30 days', 'email', 'Electronics and appliances retailer with promotional campaigns', 'active', 1),
('CLI-2025-0009', 'Vodafone Ghana', 'corporate', 'Samuel Tetteh', '+233-24-901-2345', 'stetteh@vodafone.com.gh', 'corporate@vodafone.com.gh', 'Vodafone House, Accra', 'Vodafone House, Accra', 'Accra', 'Greater Accra', 'GA-901-2345', 'Ghana', 'https://vodafone.com.gh', 'Telecommunications', 'large', 1500000000.00, 55000.00, '45 days', 'phone', 'Telecom provider with competitive advertising campaigns', 'active', 1),
('CLI-2025-0010', 'Fan Milk Ghana', 'corporate', 'Akosua Agyeman', '+233-24-012-3456', 'aagyeman@fanmilk.com.gh', 'marketing@fanmilk.com.gh', 'Industrial Area, Accra', 'Industrial Area, Accra', 'Accra', 'Greater Accra', 'GA-012-3456', 'Ghana', 'https://fanmilk.com.gh', 'Food & Beverage', 'medium', 50000000.00, 15000.00, '30 days', 'email', 'Dairy products company with seasonal advertising', 'active', 1),
('CLI-2025-0011', 'Ghana Revenue Authority', 'government', 'Mr. Kwaku Appiah', '+233-24-123-4567', 'kappiah@gra.gov.gh', 'info@gra.gov.gh', 'GRA Headquarters, Accra', 'GRA Headquarters, Accra', 'Accra', 'Greater Accra', 'GA-123-4567', 'Ghana', 'https://gra.gov.gh', 'Government', 'large', 50000000.00, 20000.00, '90 days', 'email', 'Tax authority with public awareness campaigns', 'active', 1),
('CLI-2025-0012', 'Coca-Cola Ghana', 'corporate', 'Grace Mensah', '+233-24-234-5678', 'gmensah@coca-cola.com.gh', 'brands@coca-cola.com.gh', 'Coca-Cola Plant, Accra', 'Coca-Cola Plant, Accra', 'Accra', 'Greater Accra', 'GA-234-5678', 'Ghana', 'https://coca-cola.com.gh', 'Food & Beverage', 'large', 300000000.00, 45000.00, '30 days', 'email', 'Beverage company with brand advertising campaigns', 'active', 1),
('CLI-2025-0013', 'Ghana Ports Authority', 'government', 'Captain James Quaye', '+233-24-345-6789', 'jquaye@gpa.gov.gh', 'info@gpa.gov.gh', 'Tema Port, Tema', 'Tema Port, Tema', 'Tema', 'Greater Accra', 'GA-345-6789', 'Ghana', 'https://gpa.gov.gh', 'Transportation', 'large', 100000000.00, 30000.00, '60 days', 'email', 'Port authority with trade and shipping advertisements', 'active', 1),
('CLI-2025-0014', 'Kasapreko Company', 'corporate', 'Nana Kwame', '+233-24-456-7890', 'nkwame@kasapreko.com.gh', 'marketing@kasapreko.com.gh', 'Kasapreko Road, Accra', 'Kasapreko Road, Accra', 'Accra', 'Greater Accra', 'GA-456-7890', 'Ghana', 'https://kasapreko.com.gh', 'Food & Beverage', 'medium', 40000000.00, 12000.00, '30 days', 'phone', 'Local beverage manufacturer with promotional advertising', 'active', 1),
('CLI-2025-0015', 'Ghana National Petroleum Corporation', 'government', 'Dr. Kofi Addo', '+233-24-567-8901', 'kaddo@gnpc.gov.gh', 'info@gnpc.gov.gh', 'GNPC House, Accra', 'GNPC House, Accra', 'Accra', 'Greater Accra', 'GA-567-8901', 'Ghana', 'https://gnpc.gov.gh', 'Energy', 'large', 2000000000.00, 80000.00, '90 days', 'email', 'National oil company with energy sector advertising', 'active', 1),
('CLI-2025-0016', 'Golden Tulip Hotel', 'corporate', 'Mrs. Akosua Boateng', '+233-24-678-9012', 'aboateng@goldentulip.com.gh', 'reservations@goldentulip.com.gh', 'Liberation Road, Accra', 'Liberation Road, Accra', 'Accra', 'Greater Accra', 'GA-678-9012', 'Ghana', 'https://goldentulip.com.gh', 'Hospitality', 'medium', 15000000.00, 8000.00, '30 days', 'email', 'Hotel chain with tourism and hospitality advertising', 'active', 1),
('CLI-2025-0017', 'Ghana Broadcasting Corporation', 'government', 'Mr. Samuel Ofori', '+233-24-789-0123', 'sofori@gbc.gov.gh', 'info@gbc.gov.gh', 'GBC House, Accra', 'GBC House, Accra', 'Accra', 'Greater Accra', 'GA-789-0123', 'Ghana', 'https://gbc.gov.gh', 'Media', 'large', 50000000.00, 25000.00, '45 days', 'email', 'National broadcaster with public service announcements', 'active', 1),
('CLI-2025-0018', 'Ghana Post Company', 'government', 'Mrs. Comfort Asiedu', '+233-24-890-1234', 'casiedu@ghanapost.gov.gh', 'info@ghanapost.gov.gh', 'Ghana Post House, Accra', 'Ghana Post House, Accra', 'Accra', 'Greater Accra', 'GA-890-1234', 'Ghana', 'https://ghanapost.gov.gh', 'Postal Services', 'medium', 20000000.00, 10000.00, '60 days', 'email', 'Postal service with logistics and communication advertising', 'active', 1),
('CLI-2025-0019', 'Ghana Water Company', 'government', 'Eng. Kwame Asante', '+233-24-901-2345', 'kasante@gwc.gov.gh', 'info@gwc.gov.gh', 'GWC Headquarters, Accra', 'GWC Headquarters, Accra', 'Accra', 'Greater Accra', 'GA-901-2345', 'Ghana', 'https://gwc.gov.gh', 'Utilities', 'large', 100000000.00, 30000.00, '90 days', 'email', 'Water utility with public service announcements', 'active', 1),
('CLI-2025-0020', 'Ghana Commercial Bank - Kumasi Branch', 'corporate', 'Mr. Yaw Boateng', '+233-24-012-3456', 'yboateng@gcb.com.gh', 'kumasi@gcb.com.gh', 'High Street, Kumasi', 'High Street, Kumasi', 'Kumasi', 'Ashanti', 'AS-012-3456', 'Ghana', 'https://gcb.com.gh', 'Banking', 'large', 50000000.00, 20000.00, '30 days', 'email', 'Regional branch with local advertising needs', 'active', 1);

-- Insert Client Credit Data
INSERT INTO client_credit (client_id, credit_limit, available_credit, used_credit, payment_terms, credit_status, last_credit_review, created_by) VALUES
(1, 50000.00, 45000.00, 5000.00, '30 days', 'active', CURDATE(), 1),
(2, 75000.00, 70000.00, 5000.00, '45 days', 'active', CURDATE(), 1),
(3, 30000.00, 28000.00, 2000.00, '30 days', 'active', CURDATE(), 1),
(4, 40000.00, 35000.00, 5000.00, '30 days', 'active', CURDATE(), 1),
(5, 60000.00, 55000.00, 5000.00, '45 days', 'active', CURDATE(), 1),
(6, 10000.00, 9500.00, 500.00, '15 days', 'active', CURDATE(), 1),
(7, 25000.00, 20000.00, 5000.00, '60 days', 'active', CURDATE(), 1),
(8, 35000.00, 32000.00, 3000.00, '30 days', 'active', CURDATE(), 1),
(9, 55000.00, 50000.00, 5000.00, '45 days', 'active', CURDATE(), 1),
(10, 15000.00, 14000.00, 1000.00, '30 days', 'active', CURDATE(), 1),
(11, 20000.00, 18000.00, 2000.00, '90 days', 'active', CURDATE(), 1),
(12, 45000.00, 40000.00, 5000.00, '30 days', 'active', CURDATE(), 1),
(13, 30000.00, 25000.00, 5000.00, '60 days', 'active', CURDATE(), 1),
(14, 12000.00, 11000.00, 1000.00, '30 days', 'active', CURDATE(), 1),
(15, 80000.00, 75000.00, 5000.00, '90 days', 'active', CURDATE(), 1),
(16, 8000.00, 7500.00, 500.00, '30 days', 'active', CURDATE(), 1),
(17, 25000.00, 20000.00, 5000.00, '45 days', 'active', CURDATE(), 1),
(18, 10000.00, 9000.00, 1000.00, '60 days', 'active', CURDATE(), 1),
(19, 30000.00, 25000.00, 5000.00, '90 days', 'active', CURDATE(), 1),
(20, 20000.00, 18000.00, 2000.00, '30 days', 'active', CURDATE(), 1);

-- Insert Sample Rates (comprehensive rate matrix)
INSERT INTO rates (publication_id, color_type_id, ad_category_id, ad_size_id, page_position_id, base_rate, effective_from, effective_to, status, notes, created_by)
SELECT 
    p.id as publication_id,
    ct.id as color_type_id,
    ac.id as ad_category_id,
    ads.id as ad_size_id,
    pp.id as page_position_id,
    CASE 
        WHEN p.name = 'Daily Graphic' THEN 
            CASE 
                WHEN ads.name = 'Full Page' THEN 5000.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Half Page' THEN 2500.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Quarter Page' THEN 1250.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Eighth Page' THEN 625.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Business Card' THEN 200.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Strip' THEN 800.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Column Inch' THEN 50.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Double Column' THEN 100.00 * ct.multiplier * pp.multiplier
            END
        WHEN p.name = 'Ghanaian Times' THEN 
            CASE 
                WHEN ads.name = 'Full Page' THEN 4000.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Half Page' THEN 2000.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Quarter Page' THEN 1000.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Eighth Page' THEN 500.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Business Card' THEN 150.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Strip' THEN 600.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Column Inch' THEN 40.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Double Column' THEN 80.00 * ct.multiplier * pp.multiplier
            END
        WHEN p.name = 'Business & Financial Times' THEN 
            CASE 
                WHEN ads.name = 'Full Page' THEN 3500.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Half Page' THEN 1750.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Quarter Page' THEN 875.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Eighth Page' THEN 437.50 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Business Card' THEN 125.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Strip' THEN 500.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Column Inch' THEN 35.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Double Column' THEN 70.00 * ct.multiplier * pp.multiplier
            END
        WHEN p.name = 'The Mirror' THEN 
            CASE 
                WHEN ads.name = 'Full Page' THEN 3000.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Half Page' THEN 1500.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Quarter Page' THEN 750.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Eighth Page' THEN 375.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Business Card' THEN 100.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Strip' THEN 400.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Column Inch' THEN 30.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Double Column' THEN 60.00 * ct.multiplier * pp.multiplier
            END
        WHEN p.name = 'Citi FM' THEN 
            CASE 
                WHEN ads.name = 'Full Page' THEN 2000.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Half Page' THEN 1000.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Quarter Page' THEN 500.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Eighth Page' THEN 250.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Business Card' THEN 75.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Strip' THEN 300.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Column Inch' THEN 25.00 * ct.multiplier * pp.multiplier
                WHEN ads.name = 'Double Column' THEN 50.00 * ct.multiplier * pp.multiplier
            END
    END as base_rate,
    '2025-01-01' as effective_from,
    '2025-12-31' as effective_to,
    'active' as status,
    CONCAT('Rate for ', p.name, ' - ', ads.name, ' - ', ct.name, ' - ', pp.name) as notes,
    1 as created_by
FROM publications p
CROSS JOIN color_types ct
CROSS JOIN ad_categories ac
CROSS JOIN ad_sizes ads
CROSS JOIN page_positions pp
WHERE p.status = 'active' 
AND ct.status = 'active' 
AND ac.status = 'active' 
AND ads.status = 'active' 
AND pp.status = 'active'
AND ac.level = 1; -- Only use top-level categories for rate generation

-- Insert Sample Bookings
INSERT INTO bookings (booking_number, client_id, publication_id, color_type_id, ad_category_id, ad_size_id, page_position_id, rate_id, publication_date, insertions, base_rate, subtotal, total_tax, discount_amount, discount_reason, total_amount, notes, status, created_by) VALUES
('BK-2025-0001', 1, 1, 1, 2, 1, 1, 1, '2025-01-15', 1, 15000.00, 15000.00, 2250.00, 0.00, NULL, 17250.00, 'Banking promotion for new year', 'confirmed', 1),
('BK-2025-0002', 2, 1, 4, 5, 3, 2, 2, '2025-01-20', 3, 3750.00, 11250.00, 1687.50, 1000.00, 'Bulk booking discount', 11937.50, 'MTN promotional campaign', 'confirmed', 1),
('BK-2025-0003', 3, 2, 2, 5, 2, 3, 3, '2025-01-25', 1, 3000.00, 3000.00, 450.00, 0.00, NULL, 3450.00, 'Shoprite weekly promotion', 'draft', 1),
('BK-2025-0004', 4, 1, 1, 2, 4, 4, 4, '2025-02-01', 2, 1875.00, 3750.00, 562.50, 0.00, NULL, 4312.50, 'Ecobank loan promotion', 'confirmed', 1),
('BK-2025-0005', 5, 3, 3, 5, 1, 5, 5, '2025-02-05', 1, 7000.00, 7000.00, 1050.00, 500.00, 'Corporate discount', 7550.00, 'Unilever brand campaign', 'confirmed', 1);

-- Verification Queries
SELECT 'Users' as table_name, COUNT(*) as record_count FROM users
UNION ALL
SELECT 'Publications', COUNT(*) FROM publications
UNION ALL
SELECT 'Color Types', COUNT(*) FROM color_types
UNION ALL
SELECT 'Ad Categories', COUNT(*) FROM ad_categories
UNION ALL
SELECT 'Ad Sizes', COUNT(*) FROM ad_sizes
UNION ALL
SELECT 'Page Positions', COUNT(*) FROM page_positions
UNION ALL
SELECT 'Taxes', COUNT(*) FROM taxes
UNION ALL
SELECT 'Tax Configurations', COUNT(*) FROM tax_configurations
UNION ALL
SELECT 'Clients', COUNT(*) FROM clients
UNION ALL
SELECT 'Client Credit', COUNT(*) FROM client_credit
UNION ALL
SELECT 'Rates', COUNT(*) FROM rates
UNION ALL
SELECT 'Bookings', COUNT(*) FROM bookings;
