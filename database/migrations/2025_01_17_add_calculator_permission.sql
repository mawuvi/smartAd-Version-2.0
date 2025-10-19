-- =====================================================
-- Add Calculator Permission and Tax Rules Enhancement
-- Date: 2025-01-17
-- Purpose: Add calculator.use permission and tax_rules table for dynamic tax configuration
-- =====================================================

-- Add calculator.use permission
INSERT INTO permissions (code, description, resource, action, created_at) 
VALUES ('calculator.use', 'Access rate calculator tool', 'calculator', 'use', NOW());

-- Assign calculator permission to all roles
INSERT INTO role_permissions (role, permission_id, created_at)
SELECT 'admin', id, NOW() FROM permissions WHERE code = 'calculator.use'
UNION
SELECT 'manager', id, NOW() FROM permissions WHERE code = 'calculator.use'
UNION
SELECT 'user', id, NOW() FROM permissions WHERE code = 'calculator.use';

-- Create tax_rules table for dynamic tax configuration
CREATE TABLE IF NOT EXISTS tax_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tax_configuration_id INT NOT NULL,
    tax_id INT NOT NULL,
    priority INT DEFAULT 1 COMMENT 'Tax calculation order (1=first tier, 2=second tier)',
    apply_on ENUM('base', 'cumulative') DEFAULT 'base' COMMENT 'Apply on base subtotal or cumulative subtotal',
    discount_applicable BOOLEAN DEFAULT TRUE COMMENT 'Whether discount affects this tax',
    discount_before_tax BOOLEAN DEFAULT TRUE COMMENT 'Whether discount is applied before tax calculation',
    notes TEXT NULL COMMENT 'Additional notes for this tax rule',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (tax_configuration_id) REFERENCES tax_configurations(id) ON DELETE CASCADE,
    FOREIGN KEY (tax_id) REFERENCES taxes(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_tax_configuration_rule (tax_configuration_id, tax_id),
    INDEX idx_priority (priority),
    INDEX idx_apply_on (apply_on),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes to tax_configurations for better performance
ALTER TABLE tax_configurations 
ADD INDEX IF NOT EXISTS idx_status (status),
ADD INDEX IF NOT EXISTS idx_publication_status (publication_id, status);

-- Add indexes to taxes for better performance  
ALTER TABLE taxes
ADD INDEX IF NOT EXISTS idx_tax_type (tax_type),
ADD INDEX IF NOT EXISTS idx_status_effective (status, effective_from, effective_to);
