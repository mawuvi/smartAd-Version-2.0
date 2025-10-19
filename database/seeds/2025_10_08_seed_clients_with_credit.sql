-- Seed clients table with diverse test data
-- Migration: 2025_10_08_seed_clients_with_credit.sql

-- Clear existing test data to start fresh
DELETE FROM file_storage WHERE client_id IN (SELECT id FROM clients WHERE created_by = 1);
DELETE FROM client_credit WHERE created_by = 1;
DELETE FROM clients WHERE created_by = 1;

-- Insert Clients Data
INSERT INTO clients (
    client_number, company_name, client_type, contact_person,
    contact_phone, contact_email, email, address, client_address,
    city, region, postal_code, country, website, 
    industry, company_size, annual_revenue, 
    credit_limit, payment_terms, preferred_contact_method,
    notes, status, created_by, created_at
) VALUES 
('CLI-2025-0001', 'Ghana Commercial Bank', 'Corporate', 'Kwame Asante', '+233-24-123-4567', 'kasante@gcb.com.gh', 'info@gcb.com.gh', 'High Street, Accra', 'High Street, Accra', 'Accra', 'Greater Accra', 'GA-123-4567', 'Ghana', 'https://gcb.com.gh', 'Banking', 'Large', '1000000000.00', '50000.00', '30 days', 'Email', 'Major banking client with regular advertising needs', 'active', 1, NOW()),

('CLI-2025-0002', 'MTN Ghana', 'Corporate', 'Ama Serwaa', '+233-24-234-5678', 'aserwaa@mtn.com.gh', 'corporate@mtn.com.gh', 'MTN House, Accra', 'MTN House, Accra', 'Accra', 'Greater Accra', 'GA-234-5678', 'Ghana', 'https://mtn.com.gh', 'Telecommunications', 'Large', '2000000000.00', '75000.00', '45 days', 'Phone', 'Leading telecom provider with extensive advertising campaigns', 'active', 1, NOW()),

('CLI-2025-0003', 'Shoprite Ghana', 'Corporate', 'Kofi Mensah', '+233-24-345-6789', 'kmensah@shoprite.com.gh', 'marketing@shoprite.com.gh', 'Accra Mall, Accra', 'Accra Mall, Accra', 'Accra', 'Greater Accra', 'GA-345-6789', 'Ghana', 'https://shoprite.com.gh', 'Retail', 'Large', '500000000.00', '30000.00', '30 days', 'Email', 'Retail chain with weekly promotional advertising', 'active', 1, NOW()),

('CLI-2025-0004', 'Ecobank Ghana', 'Corporate', 'Efua Boateng', '+233-24-456-7890', 'eboateng@ecobank.com.gh', 'marketing@ecobank.com.gh', 'Independence Avenue, Accra', 'Independence Avenue, Accra', 'Accra', 'Greater Accra', 'GA-456-7890', 'Ghana', 'https://ecobank.com.gh', 'Banking', 'Large', '800000000.00', '40000.00', '30 days', 'Email', 'Pan-African bank with regular financial product advertising', 'active', 1, NOW()),

('CLI-2025-0005', 'Unilever Ghana', 'Corporate', 'Yaw Osei', '+233-24-567-8901', 'yosei@unilever.com.gh', 'brands@unilever.com.gh', 'Industrial Area, Tema', 'Industrial Area, Tema', 'Tema', 'Greater Accra', 'GA-567-8901', 'Ghana', 'https://unilever.com.gh', 'Consumer Goods', 'Large', '300000000.00', '60000.00', '45 days', 'Email', 'FMCG company with multiple brand advertising needs', 'active', 1, NOW()),

('CLI-2025-0006', 'Kumasi Asante Kotoko FC', 'Sports', 'Nana Yaw', '+233-24-678-9012', 'nyaw@kotoko.com.gh', 'info@kotoko.com.gh', 'Baba Yara Stadium, Kumasi', 'Baba Yara Stadium, Kumasi', 'Kumasi', 'Ashanti', 'AS-678-9012', 'Ghana', 'https://kotoko.com.gh', 'Sports', 'Medium', '5000000.00', '10000.00', '15 days', 'Phone', 'Premier league football club with seasonal advertising', 'active', 1, NOW()),

('CLI-2025-0007', 'Ghana Education Service', 'Government', 'Dr. Comfort Asante', '+233-24-789-0123', 'casante@ges.gov.gh', 'info@ges.gov.gh', 'Ministry of Education, Accra', 'Ministry of Education, Accra', 'Accra', 'Greater Accra', 'GA-789-0123', 'Ghana', 'https://ges.gov.gh', 'Education', 'Large', '100000000.00', '25000.00', '60 days', 'Email', 'Government education agency with public service announcements', 'active', 1, NOW()),

('CLI-2025-0008', 'Melcom Ghana', 'Corporate', 'Patience Adjei', '+233-24-890-1234', 'padjei@melcom.com.gh', 'marketing@melcom.com.gh', 'Ring Road, Accra', 'Ring Road, Accra', 'Accra', 'Greater Accra', 'GA-890-1234', 'Ghana', 'https://melcom.com.gh', 'Retail', 'Large', '200000000.00', '35000.00', '30 days', 'Email', 'Electronics and appliances retailer with promotional campaigns', 'active', 1, NOW()),

('CLI-2025-0009', 'Vodafone Ghana', 'Corporate', 'Samuel Tetteh', '+233-24-901-2345', 'stetteh@vodafone.com.gh', 'corporate@vodafone.com.gh', 'Vodafone House, Accra', 'Vodafone House, Accra', 'Accra', 'Greater Accra', 'GA-901-2345', 'Ghana', 'https://vodafone.com.gh', 'Telecommunications', 'Large', '1500000000.00', '55000.00', '45 days', 'Phone', 'Telecom provider with competitive advertising campaigns', 'active', 1, NOW()),

('CLI-2025-0010', 'Fan Milk Ghana', 'Corporate', 'Akosua Agyeman', '+233-24-012-3456', 'aagyeman@fanmilk.com.gh', 'marketing@fanmilk.com.gh', 'Industrial Area, Accra', 'Industrial Area, Accra', 'Accra', 'Greater Accra', 'GA-012-3456', 'Ghana', 'https://fanmilk.com.gh', 'Food & Beverage', 'Medium', '50000000.00', '15000.00', '30 days', 'Email', 'Dairy products company with seasonal advertising', 'active', 1, NOW()),

('CLI-2025-0011', 'Ghana Revenue Authority', 'Government', 'Mr. Kwaku Appiah', '+233-24-123-4567', 'kappiah@gra.gov.gh', 'info@gra.gov.gh', 'GRA Headquarters, Accra', 'GRA Headquarters, Accra', 'Accra', 'Greater Accra', 'GA-123-4567', 'Ghana', 'https://gra.gov.gh', 'Government', 'Large', '50000000.00', '20000.00', '90 days', 'Email', 'Tax authority with public awareness campaigns', 'active', 1, NOW()),

('CLI-2025-0012', 'Coca-Cola Ghana', 'Corporate', 'Grace Mensah', '+233-24-234-5678', 'gmensah@coca-cola.com.gh', 'brands@coca-cola.com.gh', 'Coca-Cola Plant, Accra', 'Coca-Cola Plant, Accra', 'Accra', 'Greater Accra', 'GA-234-5678', 'Ghana', 'https://coca-cola.com.gh', 'Food & Beverage', 'Large', '300000000.00', '45000.00', '30 days', 'Email', 'Beverage company with brand advertising campaigns', 'active', 1, NOW()),

('CLI-2025-0013', 'Ghana Ports Authority', 'Government', 'Captain James Quaye', '+233-24-345-6789', 'jquaye@gpa.gov.gh', 'info@gpa.gov.gh', 'Tema Port, Tema', 'Tema Port, Tema', 'Tema', 'Greater Accra', 'GA-345-6789', 'Ghana', 'https://gpa.gov.gh', 'Transportation', 'Large', '100000000.00', '30000.00', '60 days', 'Email', 'Port authority with trade and shipping advertisements', 'active', 1, NOW()),

('CLI-2025-0014', 'Kasapreko Company', 'Corporate', 'Nana Kwame', '+233-24-456-7890', 'nkwame@kasapreko.com.gh', 'marketing@kasapreko.com.gh', 'Kasapreko Road, Accra', 'Kasapreko Road, Accra', 'Accra', 'Greater Accra', 'GA-456-7890', 'Ghana', 'https://kasapreko.com.gh', 'Food & Beverage', 'Medium', '40000000.00', '12000.00', '30 days', 'Phone', 'Local beverage manufacturer with promotional advertising', 'active', 1, NOW()),

('CLI-2025-0015', 'Ghana National Petroleum Corporation', 'Government', 'Dr. Kofi Addo', '+233-24-567-8901', 'kaddo@gnpc.gov.gh', 'info@gnpc.gov.gh', 'GNPC House, Accra', 'GNPC House, Accra', 'Accra', 'Greater Accra', 'GA-567-8901', 'Ghana', 'https://gnpc.gov.gh', 'Energy', 'Large', '2000000000.00', '80000.00', '90 days', 'Email', 'National oil company with energy sector advertising', 'active', 1, NOW()),

('CLI-2025-0016', 'Golden Tulip Hotel', 'Hospitality', 'Mrs. Akosua Boateng', '+233-24-678-9012', 'aboateng@goldentulip.com.gh', 'reservations@goldentulip.com.gh', 'Liberation Road, Accra', 'Liberation Road, Accra', 'Accra', 'Greater Accra', 'GA-678-9012', 'Ghana', 'https://goldentulip.com.gh', 'Hospitality', 'Medium', '15000000.00', '8000.00', '30 days', 'Email', 'Hotel chain with tourism and hospitality advertising', 'active', 1, NOW()),

('CLI-2025-0017', 'Ghana Broadcasting Corporation', 'Media', 'Mr. Samuel Ofori', '+233-24-789-0123', 'sofori@gbc.gov.gh', 'info@gbc.gov.gh', 'GBC House, Accra', 'GBC House, Accra', 'Accra', 'Greater Accra', 'GA-789-0123', 'Ghana', 'https://gbc.gov.gh', 'Media', 'Large', '50000000.00', '25000.00', '45 days', 'Email', 'National broadcaster with public service announcements', 'active', 1, NOW()),

('CLI-2025-0018', 'Ghana Post Company', 'Government', 'Mrs. Comfort Asiedu', '+233-24-890-1234', 'casiedu@ghanapost.gov.gh', 'info@ghanapost.gov.gh', 'Ghana Post House, Accra', 'Ghana Post House, Accra', 'Accra', 'Greater Accra', 'GA-890-1234', 'Ghana', 'https://ghanapost.gov.gh', 'Postal Services', 'Medium', '20000000.00', '10000.00', '60 days', 'Email', 'Postal service with logistics and communication advertising', 'active', 1, NOW()),

('CLI-2025-0019', 'Ghana Water Company', 'Government', 'Eng. Kwame Asante', '+233-24-901-2345', 'kasante@gwc.gov.gh', 'info@gwc.gov.gh', 'GWC Headquarters, Accra', 'GWC Headquarters, Accra', 'Accra', 'Greater Accra', 'GA-901-2345', 'Ghana', 'https://gwc.gov.gh', 'Utilities', 'Large', '100000000.00', '30000.00', '90 days', 'Email', 'Water utility with public service announcements', 'active', 1, NOW()),

('CLI-2025-0020', 'Ghana Commercial Bank - Kumasi Branch', 'Corporate', 'Mr. Yaw Boateng', '+233-24-012-3456', 'yboateng@gcb.com.gh', 'kumasi@gcb.com.gh', 'High Street, Kumasi', 'High Street, Kumasi', 'Kumasi', 'Ashanti', 'AS-012-3456', 'Ghana', 'https://gcb.com.gh', 'Banking', 'Large', '50000000.00', '20000.00', '30 days', 'Email', 'Regional branch with local advertising needs', 'active', 1, NOW());

-- Insert Client Credit Data
INSERT INTO client_credit (
    client_id, credit_limit, available_credit, used_credit, 
    payment_terms, credit_status, last_credit_review, 
    created_by, created_at
) VALUES 
(LAST_INSERT_ID() - 19, 50000.00, 45000.00, 5000.00, '30 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 18, 75000.00, 70000.00, 5000.00, '45 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 17, 30000.00, 28000.00, 2000.00, '30 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 16, 40000.00, 35000.00, 5000.00, '30 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 15, 60000.00, 55000.00, 5000.00, '45 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 14, 10000.00, 9500.00, 500.00, '15 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 13, 25000.00, 20000.00, 5000.00, '60 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 12, 35000.00, 32000.00, 3000.00, '30 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 11, 55000.00, 50000.00, 5000.00, '45 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 10, 15000.00, 14000.00, 1000.00, '30 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 9, 20000.00, 18000.00, 2000.00, '90 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 8, 45000.00, 40000.00, 5000.00, '30 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 7, 30000.00, 25000.00, 5000.00, '60 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 6, 12000.00, 11000.00, 1000.00, '30 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 5, 80000.00, 75000.00, 5000.00, '90 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 4, 8000.00, 7500.00, 500.00, '30 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 3, 25000.00, 20000.00, 5000.00, '45 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 2, 10000.00, 9000.00, 1000.00, '60 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID() - 1, 30000.00, 25000.00, 5000.00, '90 days', 'active', NOW(), 1, NOW()),
(LAST_INSERT_ID(), 20000.00, 18000.00, 2000.00, '30 days', 'active', NOW(), 1, NOW());

-- Verification Query
SELECT 
    c.id,
    c.client_number,
    c.company_name,
    c.client_type,
    c.contact_person,
    c.contact_phone,
    cc.credit_limit,
    cc.available_credit,
    cc.used_credit,
    cc.credit_status
FROM clients c
LEFT JOIN client_credit cc ON c.id = cc.client_id
WHERE c.created_by = 1
ORDER BY c.id;
