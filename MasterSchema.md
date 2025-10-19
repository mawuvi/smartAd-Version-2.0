# MasterSchema.md
**Project:** SmartAd  
**Version:** 1.0  
**Date:** 2025-01-08  
**Status:** Production Ready

---

## 📋 Database Overview

**Database Name:** `u528309675_smartdbs`  
**Engine:** InnoDB  
**Character Set:** utf8mb4  
**Collation:** utf8mb4_unicode_ci  
**Compatibility:** MariaDB 10.4.32+, MySQL 8.0+

### Schema Statistics
- **Total Tables:** 22 (18 core + 4 additional)
- **Performance Views:** 6
- **Stored Procedures:** 11 (8 core + 3 RBAC)
- **Indexes:** 55+ (including composite indexes)
- **Foreign Key Constraints:** 27+

---

## 🗄️ Table Structure

### Base Tables (No Foreign Keys)

#### users
**Purpose:** System users and authentication
```sql
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
```

#### api_keys
**Purpose:** API authentication for external integrations
```sql
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
```

### Reference Tables

#### publications
**Purpose:** Media outlets for advertising
```sql
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
```

#### color_types
**Purpose:** Color options for advertisements
```sql
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
```

#### ad_categories
**Purpose:** Advertisement categories with hierarchical structure
```sql
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
```

#### ad_sizes
**Purpose:** Advertisement dimensions and specifications
```sql
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
```

#### page_positions
**Purpose:** Page placement options for advertisements
```sql
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
```

#### taxes
**Purpose:** Tax rates and configurations
```sql
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
```

### Dependent Tables

#### tax_configurations
**Purpose:** Publication-specific tax settings
```sql
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
```

#### clients
**Purpose:** Client information and details
```sql
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
```

#### client_credit
**Purpose:** Client credit limits and usage tracking
```sql
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
```

#### rates
**Purpose:** Advertising rates by publication and criteria
```sql
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
```

### Transaction Tables

#### bookings
**Purpose:** Advertisement bookings and orders
```sql
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
```

#### file_storage
**Purpose:** File storage for documents and materials
```sql
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
```

#### placements
**Purpose:** Advertisement placements and scheduling (Future Feature)
```sql
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
);
```

#### audit_logs
**Purpose:** System audit trail and change tracking
```sql
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
);
```

#### sms_logs
**Purpose:** SMS delivery tracking and logs
```sql
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
);
```

#### file_uploads
**Purpose:** File upload tracking and management
```sql
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
);
```

#### rates_staging
**Purpose:** Staging table for bulk rate uploads with validation, user association, session management, and binary dependency resolution

**Note:** The similar_*_json and selected_*_id columns exist but are unused in the current binary logic implementation. They are preserved for potential future use.
```sql
CREATE TABLE rates_staging (
    id INT AUTO_INCREMENT PRIMARY KEY,
    upload_session_id VARCHAR(50) NOT NULL,
    row_number INT NOT NULL,
    publication_code VARCHAR(20),
    publication_name VARCHAR(100),
    ad_category VARCHAR(100),
    ad_size VARCHAR(50),
    page_position VARCHAR(50),
    color_type VARCHAR(50),
    base_rate DECIMAL(10,2),
    effective_from DATE,
    effective_to DATE,
    validation_status ENUM('ok', 'duplicate', 'error', 'warning') DEFAULT 'ok',
    validation_message TEXT,
    is_selected BOOLEAN DEFAULT TRUE,
    similar_publications_json TEXT NULL COMMENT 'JSON array of similar publication matches',
    similar_categories_json TEXT NULL COMMENT 'JSON array of similar ad category matches',
    similar_sizes_json TEXT NULL COMMENT 'JSON array of similar ad size matches',
    similar_positions_json TEXT NULL COMMENT 'JSON array of similar page position matches',
    similar_colors_json TEXT NULL COMMENT 'JSON array of similar color type matches',
    selected_publication_id INT NULL COMMENT 'User-selected publication ID for merge',
    selected_category_id INT NULL COMMENT 'User-selected ad category ID for merge',
    selected_size_id INT NULL COMMENT 'User-selected ad size ID for merge',
    selected_position_id INT NULL COMMENT 'User-selected page position ID for merge',
    selected_color_id INT NULL COMMENT 'User-selected color type ID for merge',
    merge_action ENUM('create_new', 'use_existing', 'pending') DEFAULT 'pending',
    category_action ENUM('create_new', 'use_existing', 'pending') DEFAULT 'pending',
    size_action ENUM('create_new', 'use_existing', 'pending') DEFAULT 'pending',
    position_action ENUM('create_new', 'use_existing', 'pending') DEFAULT 'pending',
    color_action ENUM('create_new', 'use_existing', 'pending') DEFAULT 'pending',
    notes TEXT,
    uploaded_by INT NOT NULL COMMENT 'User who uploaded the file',
    processed_at TIMESTAMP NULL COMMENT 'When row was processed',
    processed_by INT NULL COMMENT 'User who processed the row',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (upload_session_id),
    INDEX idx_status (validation_status),
    INDEX idx_selected (is_selected),
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_processed_at (processed_at),
    INDEX idx_category_action (category_action),
    INDEX idx_size_action (size_action),
    INDEX idx_position_action (position_action),
    INDEX idx_color_action (color_action),
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (selected_publication_id) REFERENCES publications(id) ON DELETE SET NULL,
    FOREIGN KEY (selected_category_id) REFERENCES ad_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (selected_size_id) REFERENCES ad_sizes(id) ON DELETE SET NULL,
    FOREIGN KEY (selected_position_id) REFERENCES page_positions(id) ON DELETE SET NULL,
    FOREIGN KEY (selected_color_id) REFERENCES color_types(id) ON DELETE SET NULL
) COMMENT = 'Staging table for bulk rate uploads with validation, user association, auto-cleanup, and smart dependency resolution';
```

### RBAC Tables

#### permissions
**Purpose:** Granular permission definitions for Role-Based Access Control
```sql
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
);
```

#### role_permissions
**Purpose:** Maps permissions to roles for RBAC system
```sql
CREATE TABLE role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin', 'manager', 'user') NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_role_permission (role, permission_id)
);
```

#### user_permissions
**Purpose:** Individual user permission overrides for specific cases
```sql
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
);
```

---

## 🔍 Performance Views

### vw_booking_details
**Purpose:** Complete booking details with all related information
- Joins bookings with clients, publications, rates, and user information
- Includes client credit information
- Provides calculated fields for easy access

### vw_client_summary
**Purpose:** Client summary with credit and booking statistics
- Client information with credit details
- Booking statistics (total, confirmed, draft)
- Total spending and last booking date

### vw_rate_details
**Purpose:** Rate details with all related information and calculations
- Rate information with publication, color, category, size, position details
- Calculated rate with multipliers applied
- User information for audit trail

### vw_publication_taxes
**Purpose:** Publication tax configurations
- Publication information with associated taxes
- Tax rates and effective dates
- Configuration status

### vw_publication_stats
**Purpose:** Publication statistics and performance metrics
- Booking statistics by publication
- Revenue and performance metrics
- Rate statistics (min, max, average)

### vw_user_activity
**Purpose:** User activity and performance statistics
- User information with activity counts
- Booking, client, and rate creation statistics
- Recent activity dates

---

## ⚙️ Stored Procedures

### sp_search_bookings
**Purpose:** Search bookings with multiple filters
- Parameters: client_id, status, publication_id, date_from, date_to, limit, offset
- Returns: Filtered booking results using vw_booking_details

### sp_client_debt_aging
**Purpose:** Calculate client debt aging analysis
- Parameters: client_id
- Returns: Credit analysis with debt aging breakdown and risk assessment

### sp_search_rates
**Purpose:** Search rates with criteria and effective date
- Parameters: publication_id, color_type_id, ad_category_id, ad_size_id, page_position_id, effective_date
- Returns: Matching rates with calculated values

### sp_generate_booking_number
**Purpose:** Generate unique booking number
- Output: booking_number (format: BK-YYYY-####)
- Auto-increments based on year

### sp_generate_client_number
**Purpose:** Generate unique client number
- Output: client_number (format: CLI-YYYY-####)
- Auto-increments based on year

### sp_calculate_tax_breakdown
**Purpose:** Calculate tax breakdown for booking
- Parameters: base_rate, publication_id, insertions
- Output: total_tax
- Calculates all applicable taxes for a publication

### sp_update_client_credit
**Purpose:** Update client credit usage
- Parameters: client_id, amount, operation ('add' or 'subtract')
- Returns: Updated credit information

### sp_get_dashboard_stats
**Purpose:** Get dashboard statistics
- Returns: Comprehensive system statistics for dashboard display

### RBAC Stored Procedures

### sp_get_user_permissions
**Purpose:** Get all permissions for a specific user
- Parameters: user_id
- Returns: Array of permission codes including role permissions and user overrides

### sp_check_user_permission
**Purpose:** Check if user has specific permission
- Parameters: user_id, permission_code
- Returns: Boolean result (1 = has permission, 0 = no permission)

### sp_get_role_permissions
**Purpose:** Get all permissions for a specific role
- Parameters: role_name
- Returns: Array of permission codes for the role

---

## 📊 Indexes

### Primary Indexes
- All tables have primary key indexes on `id` column
- Unique indexes on business keys (username, email, client_number, booking_number, etc.)

### Performance Indexes
- **Composite indexes** for common query patterns
- **Foreign key indexes** for join optimization
- **Status indexes** for filtering
- **Date indexes** for time-based queries
- **Full-text indexes** for search functionality

### Key Composite Indexes
- `idx_bookings_client_status_date` - Client booking queries
- `idx_bookings_publication_status_date` - Publication booking queries
- `idx_rates_lookup` - Rate lookup optimization
- `idx_clients_type_status` - Client filtering

---

## 🔗 Entity Relationships

### Core Relationships
1. **users** → **clients** (created_by)
2. **users** → **bookings** (created_by)
3. **users** → **rates** (created_by)
4. **clients** → **bookings** (client_id)
5. **clients** → **client_credit** (client_id)
6. **publications** → **rates** (publication_id)
7. **rates** → **bookings** (rate_id)

### Reference Relationships
- **color_types** → **rates** (color_type_id)
- **ad_categories** → **rates** (ad_category_id)
- **ad_sizes** → **rates** (ad_size_id)
- **page_positions** → **rates** (page_position_id)
- **taxes** → **tax_configurations** (tax_id)
- **publications** → **tax_configurations** (publication_id)

### Audit Relationships
- **users** → **audit_logs** (user_id)
- **users** → **sms_logs** (created_by)
- **users** → **file_uploads** (uploaded_by)

---

## 🌱 Seed Data

### Default Admin User
- **Username:** admin
- **Email:** admin@smartad.com
- **Role:** admin
- **Password:** Hashed (default: password)

### Sample Publications
- Daily Graphic (DG)
- Ghanaian Times (GT)
- Business & Financial Times (BFT)

### Sample Color Types
- Black & White (BW) - 1.00x multiplier
- Spot Color (SC) - 1.25x multiplier
- Two Color (TC) - 1.50x multiplier
- Full Color (FC) - 2.00x multiplier

### Sample Ad Categories
- Classified (CLS)
- Display (DSP)
- Business (BUS)
- Government (GOV)
- Personal (PER)

### Sample Ad Sizes
- Quarter Page (QP) - 500 sqcm
- Half Page (HP) - 1000 sqcm
- Full Page (FP) - 2000 sqcm
- Small Box (SB) - 25 sqcm
- Medium Box (MB) - 100 sqcm

### Sample Page Positions
- Front Page (FP) - 3.00x multiplier
- Back Page (BP) - 2.50x multiplier
- Inside Page (IP) - 1.00x multiplier
- Special Page (SP) - 1.75x multiplier

### Sample Taxes
- VAT - 12.50%
- NHIL - 2.50%
- COVID - 1.00%
- GETFUND - 2.50%

### Sample Clients
- ABC Company Ltd (CLI-2024-0001)
- XYZ Services (CLI-2024-0002)
- Tech Solutions Inc (CLI-2024-0003)

### RBAC Permissions (30+ permissions)
- **Dashboard:** dashboard.view
- **Bookings:** bookings.view, bookings.create, bookings.edit, bookings.delete, bookings.manage
- **Clients:** clients.view, clients.create, clients.edit, clients.delete, clients.manage
- **Rates:** rates.view, rates.create, rates.edit, rates.delete, rates.manage
- **Users:** users.view, users.create, users.edit, users.delete, users.manage
- **Publications:** publications.view, publications.create, publications.edit, publications.delete, publications.manage
- **Reports:** reports.view, reports.create, reports.export
- **System:** system.settings, system.backup, system.logs

---

## 🔄 Schema Version History

### Version 1.0 (2025-01-08)
- **Initial Release:** Complete database schema
- **Tables:** 17 tables created
- **Views:** 6 performance views
- **Procedures:** 8 stored procedures
- **Indexes:** 50+ indexes for optimization
- **Seed Data:** Complete reference data

### Version 1.1 (2025-01-08)
- **RBAC Enhancement:** Added Role-Based Access Control system
- **Tables:** +2 RBAC tables (permissions, role_permissions, user_permissions)
- **Procedures:** +3 RBAC stored procedures
- **Permissions:** 30+ granular permissions defined
- **Security:** Enhanced authentication and authorization

### Version 1.3 (2025-01-09)
- **Smart Dependency Resolution:** Enhanced staging system with intelligent matching for all rate dependencies
- **Features:** Fuzzy matching for publications, categories, sizes, positions, and colors
- **User Experience:** "Use Existing", "Merge Options", or "Create New" actions for each dependency
- **Data Integrity:** Case-insensitive matching prevents duplicate entries
- **Analytics Ready:** Clean data structure for accurate reporting and analytics

### Version 1.2 (2025-01-08)
- **Bulk Upload Enhancement:** Added rates_staging table for bulk upload staging
- **Tables:** +1 staging table (rates_staging)
- **Features:** Publication duplicate detection and merge functionality
- **Validation:** Enhanced staging validation with fuzzy matching
- **User Experience:** Interactive merge controls in staging area

---

## 📝 Maintenance Notes

### Schema Updates
- Always update this document when making schema changes
- Use proper migration scripts for production changes
- Test all changes on MariaDB 10.4.32+ before deployment

### Performance Monitoring
- Monitor query performance using the provided views
- Use stored procedures for complex operations
- Index usage should be monitored regularly

### Data Integrity
- All foreign key constraints are enforced
- Soft deletes implemented using `deleted_at` columns
- Audit trail available through `audit_logs` table

### Staging Data Retention Policy
- **Retention Period:** 48 hours from upload
- **Cleanup Schedule:** Daily at 2:00 AM (via cron)
- **Auto-Purge:** Processed rows deleted immediately after successful processing
- **Manual Cleanup:** Users can delete sessions via UI
- **Audit Trail:** Preserved in audit_logs before deletion
- **Script Location:** `smartAdVault/maintenance/cleanup_staging.php`
- **Cron Command:** `0 2 * * * php /path/to/cleanup_staging.php`

---

✅ **End of MasterSchema.md v1.0**

*This document serves as the single source of truth for the smartAd database schema. Always consult this document before making any database-related changes.*
