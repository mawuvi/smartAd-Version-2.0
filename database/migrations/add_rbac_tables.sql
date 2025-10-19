-- RBAC Database Schema Enhancement
-- Migration: add_rbac_tables.sql
-- This migration adds Role-Based Access Control tables to the existing schema
-- Compatible with MariaDB 10.4.32+

USE u528309675_smartdbs;

-- =====================================================
-- RBAC TABLES
-- =====================================================

-- Drop existing RBAC tables if they exist (in correct order)
DROP TABLE IF EXISTS user_permissions;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;

-- Create Permissions Table
CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    resource VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_resource_action (resource, action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Role Permissions Table
CREATE TABLE role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin', 'manager', 'user') NOT NULL,
    permission_id INT NOT NULL,
    granted BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_role_permission (role, permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create User Permissions Table (for individual overrides)
CREATE TABLE user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    granted BOOLEAN DEFAULT TRUE,
    expires_at TIMESTAMP NULL,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_user_permission (user_id, permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- RBAC INDEXES
-- =====================================================

-- Permissions table indexes
CREATE INDEX idx_permissions_resource ON permissions(resource);
CREATE INDEX idx_permissions_action ON permissions(action);
CREATE INDEX idx_permissions_code ON permissions(code);
CREATE INDEX idx_permissions_status ON permissions(status);

-- Role permissions table indexes
CREATE INDEX idx_role_permissions_role ON role_permissions(role);
CREATE INDEX idx_role_permissions_permission ON role_permissions(permission_id);
CREATE INDEX idx_role_permissions_granted ON role_permissions(granted);

-- User permissions table indexes
CREATE INDEX idx_user_permissions_user ON user_permissions(user_id);
CREATE INDEX idx_user_permissions_permission ON user_permissions(permission_id);
CREATE INDEX idx_user_permissions_granted ON user_permissions(granted);
CREATE INDEX idx_user_permissions_expires ON user_permissions(expires_at);

-- =====================================================
-- RBAC SEED DATA
-- =====================================================

-- Insert core permissions
INSERT INTO permissions (name, code, resource, action, description, created_by) VALUES
-- User Management Permissions
('View Users', 'users.view', 'users', 'view', 'View user information', 1),
('Create Users', 'users.create', 'users', 'create', 'Create new users', 1),
('Edit Users', 'users.edit', 'users', 'edit', 'Edit user information', 1),
('Delete Users', 'users.delete', 'users', 'delete', 'Delete users', 1),
('Manage User Roles', 'users.manage_roles', 'users', 'manage_roles', 'Assign and change user roles', 1),

-- Client Management Permissions
('View Clients', 'clients.view', 'clients', 'view', 'View client information', 1),
('Create Clients', 'clients.create', 'clients', 'create', 'Create new clients', 1),
('Edit Clients', 'clients.edit', 'clients', 'edit', 'Edit client information', 1),
('Delete Clients', 'clients.delete', 'clients', 'delete', 'Delete clients', 1),
('Manage Client Credit', 'clients.manage_credit', 'clients', 'manage_credit', 'Manage client credit limits', 1),

-- Booking Management Permissions
('View Bookings', 'bookings.view', 'bookings', 'view', 'View booking information', 1),
('Create Bookings', 'bookings.create', 'bookings', 'create', 'Create new bookings', 1),
('Edit Bookings', 'bookings.edit', 'bookings', 'edit', 'Edit booking information', 1),
('Delete Bookings', 'bookings.delete', 'bookings', 'delete', 'Delete bookings', 1),
('Approve Bookings', 'bookings.approve', 'bookings', 'approve', 'Approve booking requests', 1),

-- Rate Management Permissions
('View Rates', 'rates.view', 'rates', 'view', 'View rate information', 1),
('Create Rates', 'rates.create', 'rates', 'create', 'Create new rates', 1),
('Edit Rates', 'rates.edit', 'rates', 'edit', 'Edit rate information', 1),
('Delete Rates', 'rates.delete', 'rates', 'delete', 'Delete rates', 1),

-- Publication Management Permissions
('View Publications', 'publications.view', 'publications', 'view', 'View publication information', 1),
('Create Publications', 'publications.create', 'publications', 'create', 'Create new publications', 1),
('Edit Publications', 'publications.edit', 'publications', 'edit', 'Edit publication information', 1),
('Delete Publications', 'publications.delete', 'publications', 'delete', 'Delete publications', 1),

-- Report Permissions
('View Reports', 'reports.view', 'reports', 'view', 'View system reports', 1),
('Export Reports', 'reports.export', 'reports', 'export', 'Export reports to various formats', 1),
('View Dashboard', 'dashboard.view', 'dashboard', 'view', 'Access dashboard', 1),

-- System Management Permissions
('View System Settings', 'system.view_settings', 'system', 'view_settings', 'View system configuration', 1),
('Edit System Settings', 'system.edit_settings', 'system', 'edit_settings', 'Edit system configuration', 1),
('View Audit Logs', 'system.view_audit', 'system', 'view_audit', 'View system audit logs', 1),
('Manage Permissions', 'system.manage_permissions', 'system', 'manage_permissions', 'Manage user permissions', 1);

-- Assign permissions to roles
-- Admin gets all permissions
INSERT INTO role_permissions (role, permission_id, created_by)
SELECT 'admin', id, 1 FROM permissions WHERE status = 'active';

-- Manager permissions (most permissions except user management and system settings)
INSERT INTO role_permissions (role, permission_id, created_by)
SELECT 'manager', id, 1 FROM permissions 
WHERE status = 'active' 
AND code NOT IN (
    'users.delete', 'users.manage_roles',
    'system.edit_settings', 'system.manage_permissions'
);

-- User permissions (basic permissions for own work)
INSERT INTO role_permissions (role, permission_id, created_by)
SELECT 'user', id, 1 FROM permissions 
WHERE status = 'active' 
AND code IN (
    'clients.view', 'clients.create', 'clients.edit',
    'bookings.view', 'bookings.create', 'bookings.edit',
    'rates.view',
    'publications.view',
    'dashboard.view'
);

-- =====================================================
-- RBAC STORED PROCEDURES
-- =====================================================

DELIMITER //

-- Procedure to check if user has specific permission
CREATE PROCEDURE sp_check_user_permission(
    IN p_user_id INT,
    IN p_permission_code VARCHAR(50),
    OUT p_has_permission BOOLEAN
)
BEGIN
    DECLARE v_user_role ENUM('admin', 'manager', 'user');
    DECLARE v_permission_count INT DEFAULT 0;
    
    -- Get user role
    SELECT role INTO v_user_role FROM users WHERE id = p_user_id AND deleted_at IS NULL;
    
    -- Check if user has explicit permission override
    SELECT COUNT(*) INTO v_permission_count
    FROM user_permissions up
    JOIN permissions p ON up.permission_id = p.id
    WHERE up.user_id = p_user_id 
    AND p.code = p_permission_code
    AND up.granted = TRUE
    AND (up.expires_at IS NULL OR up.expires_at > NOW());
    
    -- If no explicit override, check role permission
    IF v_permission_count = 0 THEN
        SELECT COUNT(*) INTO v_permission_count
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.id
        WHERE rp.role = v_user_role
        AND p.code = p_permission_code
        AND rp.granted = TRUE;
    END IF;
    
    SET p_has_permission = (v_permission_count > 0);
END //

-- Procedure to get all user permissions
CREATE PROCEDURE sp_get_user_permissions(IN p_user_id INT)
BEGIN
    DECLARE v_user_role ENUM('admin', 'manager', 'user');
    
    -- Get user role
    SELECT role INTO v_user_role FROM users WHERE id = p_user_id AND deleted_at IS NULL;
    
    -- Get permissions from role and user overrides
    SELECT DISTINCT
        p.id,
        p.name,
        p.code,
        p.resource,
        p.action,
        p.description,
        CASE 
            WHEN up.id IS NOT NULL THEN up.granted
            ELSE rp.granted
        END as granted,
        CASE 
            WHEN up.id IS NOT NULL THEN 'user_override'
            ELSE 'role_based'
        END as permission_source,
        CASE 
            WHEN up.id IS NOT NULL THEN up.expires_at
            ELSE NULL
        END as expires_at
    FROM permissions p
    LEFT JOIN role_permissions rp ON p.id = rp.permission_id AND rp.role = v_user_role
    LEFT JOIN user_permissions up ON p.id = up.permission_id AND up.user_id = p_user_id
    WHERE p.status = 'active'
    AND (
        (rp.granted = TRUE AND up.id IS NULL) OR
        (up.granted = TRUE AND (up.expires_at IS NULL OR up.expires_at > NOW()))
    )
    ORDER BY p.resource, p.action;
END //

-- Procedure to log permission check
CREATE PROCEDURE sp_log_permission_check(
    IN p_user_id INT,
    IN p_permission_code VARCHAR(50),
    IN p_resource_id INT,
    IN p_granted BOOLEAN,
    IN p_ip_address VARCHAR(45)
)
BEGIN
    INSERT INTO audit_logs (
        table_name,
        record_id,
        action,
        new_values,
        user_id,
        ip_address,
        created_at
    ) VALUES (
        'permissions',
        p_resource_id,
        'CHECK',
        JSON_OBJECT(
            'permission_code', p_permission_code,
            'granted', p_granted
        ),
        p_user_id,
        p_ip_address,
        NOW()
    );
END //

DELIMITER ;

-- =====================================================
-- TABLE COMMENTS
-- =====================================================

ALTER TABLE permissions COMMENT = 'System permissions for RBAC';
ALTER TABLE role_permissions COMMENT = 'Role-based permission assignments';
ALTER TABLE user_permissions COMMENT = 'Individual user permission overrides';

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================

SELECT 'RBAC tables created successfully!' as message,
       'permissions' as table_1,
       'role_permissions' as table_2,
       'user_permissions' as table_3,
       NOW() as completion_time;
