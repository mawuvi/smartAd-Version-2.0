# MasterProject.V2.md
**Project:** SmartAd  
**Version:** 6.2 (Setup Module Complete & Module-by-Module Strategy)  
**Date:** 2025-01-08  
**Status:** Production Ready with Complete Setup Module Reference Implementation

---

## 📋 Project Overview

SmartAd is a comprehensive PHP/MySQL web application designed for advertising campaign management with integrated SMS capabilities, Google Drive storage, and enterprise-grade Role-Based Access Control (RBAC). The system provides protected APIs, authenticated pages, dynamic role-aware dashboards, reporting functionality, and external integrations while maintaining enterprise-level security standards.

---

## 🏗️ Project Architecture

### Directory Structure
```
C:\xampp\htdocs\SmartAd\
├── app\                    ← Non-public business logic
│   ├── api\               ← Protected API endpoints
│   ├── models\            ← Database query logic
│   ├── pages\             ← Protected PHP pages (post-login)
│   └── views\             ← Presentation-only templates
│
├── public\                ← Webroot (exposed to browser)
│   ├── css\               ← Static stylesheets
│   │   ├── components\    ← Reusable UI components
│   │   └── pages\         ← Page-specific styles
│   ├── js\                ← JavaScript files
│   │   ├── modules\       ← Reusable JavaScript modules
│   │   └── pages\         ← Page-specific scripts
│   ├── public_pages\      ← Public pages (login, register, forgot-password)
│   ├── api_login.php      ← Public login API (bypasses AuthGuard)
│   └── api_logout.php     ← Public logout API (bypasses AuthGuard)
│
├── smartAdVault\          ← Secure folder (within project, protected by .htaccess)
│   ├── auth\              ← Authentication & RBAC modules
│   ├── config\            ← Configuration files
│   ├── helpers\           ← Helper utilities (RBAC, Session, etc.)
│   └── logs\              ← Application logs and audit trails
│
└── database\              ← Database schemas and migrations
    ├── migrations\        ← SQL migration files
    └── seeds\             ← SQL seed data files
```

### Security Architecture
- **Public Entry Points**: login, registration, password reset
- **Protected System**: All other APIs and pages require authentication via AuthGuard
- **RBAC System**: Role-Based Access Control with granular permissions (30+ permissions)
- **Session Management**: Secure session handling with timeout, regeneration, and RBAC data
- **API Protection**: All API endpoints require valid authentication and permission checks
- **Config & Secrets**: Loaded exclusively from `smartAdVault/config/` (within project directory, protected by .htaccess)
- **Helpers**: All helper utilities reside in `smartAdVault/helpers/` and are auto-loaded via `HelperLoader.php`
- **Audit Trail**: Comprehensive logging of all user actions and permission checks
- **Directory Protection**: `smartAdVault/` directory protected by `.htaccess` with "Require all denied" to prevent web access

---

## 🎯 Functional Requirements

### 3.1 User Management & RBAC
- **FR-1**: System allows registration of new users via `public/public_pages/register.php`
- **FR-2**: Login validates against stored credentials and issues authenticated session with RBAC data
- **FR-3**: Password reset available for users who forget credentials
- **FR-4**: After login, all pages require authentication and appropriate permissions
- **FR-5**: User roles (Admin, Manager, User) define access permissions with granular control
- **FR-6**: RBAC system provides 30+ granular permissions across all modules
- **FR-7**: Individual user permission overrides available for specific cases
- **FR-8**: Session management includes role and permission data with security features

### 3.2 Client Management
- **FR-9**: System allows CRUD operations for clients (create, view, update, archive)
- **FR-10**: Client data stored securely and tied to authenticated users
- **FR-11**: Client credit management with limits and usage tracking

### 3.3 Booking Management
- **FR-12**: Users can create and manage advertisement bookings
- **FR-13**: Booking system supports draft management with configurable limits
- **FR-14**: Rate calculation with tax breakdown and confirmation workflows
- **FR-15**: Booking workflow includes client search, rate calculation, and discount application

### 3.4 Rate Management
- **FR-16**: System supports comprehensive rate configuration by publication, color type, category, size, and position
- **FR-17**: Rate calculation includes tax calculations (VAT, NHIL, COVID, GETFUND)
- **FR-18**: Rate calculator module reusable across interfaces

### 3.5 Dynamic Dashboard System
- **FR-19**: Role-aware dashboard adapts content based on user permissions
- **FR-20**: Dashboard displays role-specific statistics, widgets, and quick actions
- **FR-21**: Real-time notifications and activity feeds based on user role
- **FR-22**: Revenue charts and analytics filtered by user permissions

### 3.6 Bulk SMS Integration
- **FR-23**: System sends SMS messages via external provider APIs
- **FR-24**: SMS credentials (API key, sender ID, base URL) loaded from `smartAdVault/config/sms.php`
- **FR-25**: SMS delivery status logged and tied to campaigns

### 3.7 Google Drive Integration
- **FR-26**: System uploads and retrieves files using Google Drive API
- **FR-27**: OAuth credentials loaded from `smartAdVault/config/google_drive.php`
- **FR-28**: Access tokens refreshed securely and never stored in public scope

### 3.8 Reporting & Analytics
- **FR-29**: System generates reports on bookings, clients, and SMS activity
- **FR-30**: Reports exportable (CSV, PDF) with role-based filtering
- **FR-31**: Access to reports respects user roles and permissions

### 3.9 System Setup & Configuration
- **FR-32**: Centralized setup page for managing all system parameters and reference data
- **FR-33**: Admin-only access to configuration interface (system.settings permission)
- **FR-34**: CRUD operations for all reference tables (publications, taxes, ad categories, sizes, positions, etc.)
- **FR-35**: Bulk upload functionality with Excel/CSV templates for mass data import
- **FR-36**: Auto-creation of dependent records during bulk uploads
- **FR-37**: Strict duplicate checking with skip or update options
- **FR-38**: Template generation for each entity type with validation rules
- **FR-39**: Preview and rollback capabilities for bulk imports
- **FR-40**: Audit trail for all configuration changes

### 3.10 UI & Styling - GitHub-Inspired Design System
- **FR-41**: All CSS centralized in `/public/css/` with GitHub-inspired design principles
- **FR-42**: Inline CSS prohibited except for dynamic runtime overrides
- **FR-43**: Common UI components (buttons, forms, tables) reuse shared GitHub-style patterns
- **FR-44**: Modal system for confirmations and alerts with GitHub-quality interactions
- **FR-45**: Navigation interfaces use GitHub-inspired sidebar patterns with collapsible groups
- **FR-46**: Typography follows GitHub standards: clear hierarchy, appropriate weights, letter-spacing
- **FR-47**: Color palette uses GitHub philosophy: subtle backgrounds, clear active states, professional contrast
- **FR-48**: Spacing follows GitHub generous whitespace principles for visual comfort
- **FR-49**: Micro-interactions use GitHub-quality smooth transitions (150ms cubic-bezier easing)
- **FR-50**: Focus states and accessibility follow GitHub's keyboard navigation standards

### 3.11 Module-by-Module Design System Implementation
- **FR-51**: Design system evolves module-by-module with focused refinement
- **FR-52**: Each module extends and perfects the GitHub-inspired theme
- **FR-53**: First module (Setup) serves as reference implementation
- **FR-54**: Subsequent modules extend proven patterns without deviation
- **FR-55**: Centralized styling rules enforced through MasterProject.md standards
- **FR-56**: No scope creep - complete current module before moving to next
- **FR-57**: Theme consistency verified before module completion
- **FR-58**: Design system documentation updated with each module completion
- **FR-59**: Page headings must be centered to avoid visual conflict with app name
- **FR-60**: All module pages follow centered heading pattern for consistency
- **FR-61**: Module headers use global `.module-header` container with centered `.module-header-content` box
- **FR-62**: Module title boxes are compact, fit-content width with light blue gradient theme
- **FR-63**: Global header and footer use complementary light blue gradient themes
- **FR-64**: Header and footer elements maintain consistent blue color scheme across all modules

### 3.13 Module Header Implementation Standards
- **FR-65**: All modules must use `.module-header` container for consistent centering
- **FR-66**: Module titles wrapped in `.module-header-content` for compact blue-themed boxes
- **FR-67**: Maximum width of 600px for module title boxes to prevent excessive stretching
- **FR-68**: Responsive design ensures proper scaling on mobile devices
- **FR-69**: Hover effects and transitions maintain professional interaction feedback

#### Module Header HTML Structure:
```html
<div class="module-header">
    <div class="module-header-content">
        <h1 class="module-title">Module Name</h1>
        <p class="module-subtitle">Module description</p>
    </div>
</div>
```

#### Implementation Benefits:
- **Consistent Centering**: Avoids visual conflict with app name
- **Compact Design**: Title boxes fit content, not full page width
- **Reusable**: Same structure works for all modules
- **Professional**: Light blue gradient theme with accent bars
- **Responsive**: Adapts to different screen sizes

### 3.15 Template Design Standards
- **FR-70**: Bulk upload templates must NOT include ID columns
- **FR-71**: Template field names must match database schema exactly
- **FR-72**: Sample data must use realistic values for user guidance
- **FR-73**: Both CSV and Excel templates must have identical field structure

### 3.16 Same Publication Different Rates Handling
- **FR-74**: System allows multiple rates for same publication with different combinations
- **FR-75**: Duplicate detection based on: publication + ad_category + ad_size + page_position + color_type + effective_from
- **FR-76**: Same publication can have different rates for different ad categories (Display vs Classified)
- **FR-77**: Same publication can have different rates for different ad sizes (Full Page vs Half Page)
- **FR-78**: Same publication can have different rates for different page positions (Front Page vs Inside)
- **FR-79**: Same publication can have different rates for different color types (Color vs B&W)
- **FR-80**: Same publication can have different rates for different effective dates
- **FR-81**: System prevents true duplicates (identical combination) but allows legitimate variations

#### Example Scenarios:
```
Daily Graphic + Display + Full Page + Front Page + Color + 2024-01-01 = Rate A
Daily Graphic + Display + Half Page + Front Page + Color + 2024-01-01 = Rate B (Different)
Daily Graphic + Classified + Full Page + Front Page + Color + 2024-01-01 = Rate C (Different)
Daily Graphic + Display + Full Page + Inside + Color + 2024-01-01 = Rate D (Different)
Daily Graphic + Display + Full Page + Front Page + B&W + 2024-01-01 = Rate E (Different)
```

### 3.17 Session Management & Security
- **FR-82**: Session timeout must be tracked as logout with "SessionOut" remark
- **FR-83**: System must distinguish between manual logout and session timeout
- **FR-84**: Session timeout events must be logged for security audit purposes
- **FR-85**: User activity tracking must include session duration and timeout events

### 3.18 Publication Duplicate Detection & Prevention
- **FR-86**: System uses case-insensitive publication name matching during bulk upload
- **FR-87**: Fuzzy string matching (85% similarity threshold) detects potential duplicates
- **FR-88**: Similar publications flagged as warnings in staging area with similarity scores
- **FR-89**: Users can merge upload data with existing publications via dropdown selection
- **FR-90**: Publication names and codes standardized to UPPERCASE before storage
- **FR-91**: Publication matching checks both code and name (case-insensitive)
- **FR-92**: Merge actions: create_new, use_existing, or pending user decision

### 3.19 Staging Session Management & Cleanup
- **FR-93**: Staging data associated with user account (uploaded_by) for multi-device access
- **FR-94**: Users can view and resume pending staging sessions from any device
- **FR-95**: Processed staging rows automatically deleted after successful rate creation
- **FR-96**: Staging data older than 48 hours automatically purged via maintenance script
- **FR-97**: Session listing shows upload date, row counts, and validation statistics
- **FR-98**: Users can manually delete abandoned staging sessions
- **FR-99**: Audit trail preserved before staging data deletion
- **FR-100**: processed_at and processed_by columns track processing completion

### 3.21 Centralized Date Handling
- **FR-121**: All date operations use centralized DateHelper class
- **FR-122**: System auto-detects user's locale and date format preferences
- **FR-123**: DateHelper supports multiple formats: dd/mm/yyyy, mm/dd/yyyy, yyyy-mm-dd, etc.
- **FR-124**: Excel files automatically use system locale format (no user configuration needed)
- **FR-125**: Database storage format is always yyyy-mm-dd (ISO standard)
- **FR-126**: DateHelper provides validation, conversion, and utility functions
- **FR-127**: All modules must use DateHelper for date operations
- **FR-128**: Date validation includes helpful error messages with supported formats
- **FR-129**: System adapts to user's environment, not vice versa

### 3.22 Smart Dependency Resolution (Binary Logic)
- **FR-130**: All rate dependencies use case-insensitive exact matching to prevent duplicates
- **FR-131**: 100% exact matches auto-use existing records (no user prompt)
- **FR-132**: 0% matches (new items) auto-create new records
- **FR-133**: Fuzzy matches (1-99%) cause validation failure with helpful error message
- **FR-134**: System enforces data quality by rejecting ambiguous entries

### 3.22 Pre-Implementation Verification Checklist
Before implementing ANY feature or modification, developers MUST complete this checklist:

#### Database Schema Verification
- **FR-101**: Check if table exists using `glob_file_search` for migration files
- **FR-102**: Read actual table schema from migration files before planning changes
- **FR-103**: Verify column names, types, and constraints match MasterSchema.md
- **FR-104**: Check for existing indexes and foreign keys before adding new ones

#### Existing Code Pattern Discovery
- **FR-105**: Use `codebase_search` to find similar implementations before creating new code
- **FR-106**: Check for existing helper functions in `smartAdVault/helpers/` directory
- **FR-107**: Verify if API endpoints already exist before creating duplicates
- **FR-108**: Read functions you plan to modify to understand current implementation

#### Integration Point Verification
- **FR-109**: List directory contents to discover available helpers and utilities
- **FR-110**: Check existing audit logging patterns before implementing new logging
- **FR-111**: Verify authentication patterns used in similar API endpoints
- **FR-112**: Review existing validation logic before adding new validations

#### Standards Compliance Check
- **FR-113**: Verify file location follows MasterProject.md categorization rules
- **FR-114**: Check CSS centralization requirements for UI changes
- **FR-115**: Confirm JavaScript follows module pattern standards
- **FR-116**: Ensure database queries are MariaDB 10.4.32+ compatible

#### Documentation Review
- **FR-117**: Read MasterSchema.md for current database structure
- **FR-118**: Review pending.md for related tasks and dependencies
- **FR-119**: Check MasterProject.md for established patterns and requirements
- **FR-120**: Document all assumptions and verify them before implementation

### 3.14 System Logging & Audit
- **FR-53**: Reusable JavaScript modules in `public/js/modules/` for shared functionality
- **FR-54**: Rate calculator module must be reusable across interfaces (rates.php and booking.php)
- **FR-55**: Modal system for confirmations and alerts must be consistent across all interfaces
- **FR-56**: No code duplication - extract common logic into modules

### 3.14 Data Integrity and Business Rules
- **FR-57**: Rate ID must be mandatory for all bookings - no null values allowed
- **FR-58**: Draft limit enforcement per client (configurable, default: 1)
- **FR-59**: Client credit tracking with limits and usage monitoring
- **FR-60**: Tax calculations must include all applicable taxes (VAT, NHIL, COVID, GETFUND)

---

## 🔧 Technical Implementation

### File Creation & Categorization Rules
When creating **new files**:

1. **Static assets** → `public/css`, `public/js`, `public/assets`, or `public/logo`
2. **Public pages** (login, register, forgot password) → `public/public_pages/`
3. **Protected APIs** (booking, client, setup, rate, user APIs) → `app/api/` (routed via `/api/`)
4. **Public APIs** (login, logout APIs only) → `public/` (bypasses AuthGuard)
5. **Protected pages (post-login)** → `app/pages/`
6. **Presentation-only templates** → `app/views/`
7. **Database queries & ORM models** → `app/models/`
8. **Reusable services** (SMS handler, Drive integration, reporting) → `app/services/`
9. **Helpers** → `smartAdVault/helpers/` (never in public or app)
10. **Configs & API keys** → `smartAdVault/config/`
11. **JavaScript modules** → `public/js/modules/` (reusable components)
12. **Database migrations** → `database/migrations/`
13. **Database seeds** → `database/seeds/`

### Authentication Enforcement
Every PHP file (API or page) must begin with:
```php
require_once __DIR__ . '/../bootstrap.php';
```

`bootstrap.php` automatically loads:
- Database config
- Main config
- HelperLoader
- AuthGuard, **unless file is in the public whitelist**

#### Public Whitelist (no AuthGuard):
- `public_pages/login.php`
- `public_pages/register.php`
- `public_pages/forgot_password.php`
- `api_login.php`
- `api_logout.php`
- `index.php`

All other files are **protected by default**.

### Security Features
- **File Access Control**: Only entry points accessible from webroot
- **API Protection**: All API files blocked from direct access
- **Config Protection**: Database and auth configs completely hidden
- **Helper Protection**: All helper modules secured
- **HTTP Security Headers**: Comprehensive security headers implemented
- **Directory Protection**: Disabled directory browsing, blocked backup files

---

## 🗄️ Database Schema & Migration Rules

### 0.1 MANDATORY: Database Schema Verification

**CRITICAL RULE:** Before working on ANY interface that interacts with the database, you MUST:

1. **Check MasterSchema.md FIRST** - Always consult MasterSchema.md for current database structure
2. **MasterSchema.md is the single source of truth** - All database schema information must be verified against this document
3. **Update MasterSchema.md for any changes** - Any schema modifications must be documented in MasterSchema.md
4. **Get current table structure** - Use `DESCRIBE table_name` or `SHOW CREATE TABLE table_name` to verify against MasterSchema.md
5. **Verify all required columns exist** - Check that all fields referenced in code exist in the database
6. **Check data types match** - Ensure column types match what the code expects
7. **Verify foreign key constraints** - Check that referenced tables and columns exist
8. **Document any missing columns** - Add missing columns to pending.md as CRITICAL tasks

**Required Commands:**
```sql
-- Check table structure
DESCRIBE bookings;
DESCRIBE clients;
DESCRIBE rates;

-- Check specific columns
SHOW COLUMNS FROM bookings LIKE 'subtotal';
SHOW COLUMNS FROM bookings LIKE 'discount_%';
```

**Failure to verify database schema will result in SQL errors and broken functionality. This is NON-NEGOTIABLE.**

### 0.2 MANDATORY: MariaDB/MySQL Compatibility

**CRITICAL RULE:** All SQL statements MUST be compatible with MariaDB 10.4.32 and MySQL 8.0+:

1. **NO `ALTER TABLE IF EXISTS`** - MariaDB does not support `IF EXISTS` clause for `ALTER TABLE`
2. **Use `CREATE TABLE IF NOT EXISTS`** - Only for table creation
3. **Use `DROP TABLE IF EXISTS`** - Only for table dropping
4. **For column additions**: Check existence first, then add if missing
5. **For index additions**: Use `CREATE INDEX IF NOT EXISTS` (MySQL 8.0+) or check existence first

### 0.3 MANDATORY: API Endpoint Accessibility

**CRITICAL RULE:** All API endpoints MUST be accessible via clean routing with security-first architecture:

1. **Use `/app/api/` structure** - Business logic APIs in protected directory outside webroot
2. **Route via .htaccess** - All `/api/` requests routed to `app/api/` via `RewriteRule ^api/(.*)$ ../app/api/$1 [L]`
3. **Clean URLs** - JavaScript calls use `BASE_URL + '/api/endpoint.php'` for consistent routing
4. **Security first** - APIs protected from direct access, preventing HTML contamination in JSON responses
5. **Consistent error handling** - APIs return proper JSON responses, never HTML
6. **Public APIs exception** - Only login/logout APIs remain in `public/` directory

### 0.4 MANDATORY: Task Management and Progress Tracking

**CRITICAL RULE:** All development work MUST follow structured task management:

1. **Use pending.md** - Single source of truth for all pending tasks
2. **Cross out completed tasks** - Mark with ✅ when done, preserve original for audit trail
3. **Add tasks BEFORE starting work** - Document what needs to be done first
4. **Update status in real-time** - Mark in_progress, completed, or cancelled
5. **Reference MasterProject.V2.md** - Always check pending tasks and remind of gaps

**CORRECT Pattern:**
```sql
-- Check if column exists before adding
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'bookings' 
AND column_name = 'discount_amount';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE bookings ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00', 
    'SELECT "Column already exists" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
```

**INCORRECT Pattern:**
```sql
-- This will FAIL in MariaDB 10.4.32
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) DEFAULT 0.00;
```

**Migration Execution Order:**
To avoid foreign key constraint errors, execute schemas in this exact order:

#### Phase 1: Core Dependencies
1. `2025_01_08_create_complete_schema.sql` - Complete database structure
2. `2025_01_08_create_performance_views.sql` - Database views
3. `2025_01_08_create_stored_procedures.sql` - Stored procedures

#### Phase 2: Data Population
4. `2025_01_08_seed_complete_data.sql` - Comprehensive seed data

#### Phase 3: Migration Runner
5. `run_complete_migration.php` - Automated migration execution

---

## 🛡️ Non-Functional Requirements

- **NFR-1**: System denies direct access to all non-public folders (enforced via `.htaccess`)
- **NFR-2**: System portable across Windows XAMPP and Linux Apache
- **NFR-3**: Performance: API response time under 500ms for standard operations
- **NFR-4**: Security: All inputs validated and sanitized before DB operations
- **NFR-5**: Logging: Errors and exceptions logged without exposing sensitive data
- **NFR-6**: Database: All SQL statements compatible with MariaDB 10.4.32+
- **NFR-7**: Performance: Database queries optimized with views and stored procedures

---

## ✅ Validation & Testing

- **V-1**: Authentication: Accessing any page/API post-login without valid session redirects to login
- **V-2**: Public whitelist: Only login, register, forgot-password, and login API bypass AuthGuard
- **V-3**: Configs: If SMS/Google Drive configs missing, system fails gracefully with logged error
- **V-4**: CSS: Audit shows zero inline CSS across protected pages
- **V-5**: Database: Schema enforces relational integrity and prevents orphan records
- **V-6**: Reports: Exports match on-screen filtered results
- **V-7**: Helpers: All helper files auto-loaded through `HelperLoader.php` and accessible via bootstrap only
- **V-8**: Logging: All system events properly logged and accessible for debugging and audit purposes
- **V-9**: Database: All migrations execute successfully on MariaDB 10.4.32+
- **V-10**: Performance: Database views and procedures improve query performance

---

## 🎨 Frontend Development Standards - GitHub-Inspired Design System

### Design Philosophy
The SmartAd application follows GitHub's proven design principles for enterprise-grade user interfaces:
- **Clean Typography**: Clear hierarchy with appropriate font weights and letter-spacing
- **Generous Whitespace**: Breathing room between elements for visual comfort
- **Subtle Colors**: Low-contrast backgrounds with clear active states
- **Smooth Interactions**: Refined hover and active state transitions
- **Consistent Iconography**: Unified icon style and sizing throughout
- **Professional Polish**: Enterprise-grade visual design standards

### Typography Standards (GitHub-Inspired)
- **Navigation Headers**: 13px, weight 600, letter-spacing 0.5px
- **Navigation Items**: 14px, weight 400, line-height 1.5
- **Body Text**: 14px, weight 400, optimal readability
- **Counts/Badges**: 12px, weight 500, subtle backgrounds
- **Font Rendering**: `-webkit-font-smoothing: antialiased` for crisp text
- **Color Hierarchy**: Primary (#24292f), Secondary (#57606a), Active (#0969da)

### Color Palette (GitHub Philosophy)
- **Primary Text**: #24292f (GitHub's primary text color)
- **Secondary Text**: #57606a (GitHub's secondary text color)
- **Active/Interactive**: #0969da (GitHub's primary blue)
- **Backgrounds**: #f6f8fa (GitHub's light background)
- **Borders**: #d0d7de (GitHub's subtle border color)
- **Hover States**: rgba(9, 105, 218, 0.08) (Subtle blue tint)
- **Active States**: rgba(9, 105, 218, 0.15) (Clear but not harsh)
- **Error States**: #d1242f (GitHub's error red)

### Spacing Standards (GitHub Generous Whitespace)
- **Sidebar Width**: 296px for desktop comfort
- **Group Margins**: 8px vertical spacing between navigation groups
- **Header Padding**: 12px vertical, 20px horizontal
- **Item Padding**: 10px vertical, 20px horizontal
- **Content Padding**: 32px for main content areas
- **Group Indent**: 12px for child navigation items

### Module-by-Module Implementation Strategy

The SmartAd application follows a **module-by-module design system implementation** approach to ensure consistent, professional UI across all interfaces:

#### **Phase 1: Reference Module (Setup)**
- **Complete GitHub-inspired design system** implementation
- **All UI components** refined to GitHub standards
- **Comprehensive styling** for forms, buttons, tables, modals
- **Accessibility compliance** and responsive design
- **MasterProject.md standards** established and documented

#### **Phase 2: Module Extension Process**
When implementing each new module:

1. **Extend Existing Theme** - Apply established GitHub patterns
2. **Maintain Consistency** - Use proven styling without deviation
3. **Verify Standards** - Ensure MasterProject.md compliance
4. **Test Integration** - Verify theme works across all components
5. **Document Updates** - Update standards with any refinements

#### **Phase 3: Quality Assurance**
- **Visual Consistency** - All modules match reference implementation
- **Interaction Patterns** - Consistent hover, focus, and active states
- **Responsive Behavior** - Uniform breakpoints and mobile experience
- **Accessibility Standards** - WCAG AA compliance maintained
- **Performance Standards** - Smooth 60fps interactions

#### **Implementation Order**
1. ✅ **Setup Module** - Reference implementation (COMPLETED)
2. 📋 **Dashboard Module** - Apply GitHub sidebar patterns
3. 📋 **Reports Module** - Extend table and form styling
4. 📋 **User Management** - Apply consistent design language
5. 📋 **Booking Module** - Extend form and modal patterns
6. 📋 **Client Module** - Apply established component styles

#### **Benefits of This Approach**
- **Focused Development** - No context switching or scope creep
- **Quality Over Speed** - Each module becomes a reference implementation
- **Evolutionary Design** - Theme improves with each module
- **Consistent UX** - Uniform experience across entire application
- **Maintainable Code** - Centralized styling rules and patterns

### Micro-Interactions (GitHub Quality)
- **Transition Timing**: 150ms cubic-bezier(0.4, 0, 0.2, 1) for smooth animations
- **Hover Effects**: Subtle background and color changes
- **Focus States**: Visible keyboard navigation indicators (2px solid #0969da)
- **Active Feedback**: Clear visual confirmation of user actions
- **Scrollbar Styling**: Thin, subtle scrollbars matching GitHub's aesthetic

### Accessibility Standards
- **Focus Management**: Clear focus indicators for keyboard navigation
- **Focus-Visible**: Proper focus management with `:focus:not(:focus-visible)`
- **ARIA Compliance**: Semantic structure maintained throughout
- **Color Contrast**: WCAG AA compliance for all text and interactive elements
- **Keyboard Navigation**: Full keyboard accessibility for all interfaces

### JavaScript Architecture
- **ES6+ Standards**: Use modern JavaScript features (arrow functions, async/await, destructuring)
- **Module Pattern**: Organize code into reusable modules with clear APIs
- **State Management**: Implement proper state management for complex interactions
- **Error Handling**: Comprehensive try-catch blocks with user-friendly error messages
- **API Integration**: Standardized fetch wrapper with consistent error handling

### Form Handling
- **Real-time Validation**: Client-side validation with visual feedback
- **Debouncing**: Implement debouncing for search inputs (500ms delay)
- **Auto-save**: Automatic form data persistence for long forms
- **Progressive Enhancement**: Forms work without JavaScript, enhanced with JS
- **Accessibility**: Proper ARIA labels and keyboard navigation support

### User Interface Standards
- **Modal System**: Reusable modal framework for consistent user interactions
- **Loading States**: Visual feedback during API calls and data processing
- **Responsive Design**: Mobile-first approach with breakpoints at 768px and 480px
- **Card-based Layout**: Modern card design for content organization
- **Status Indicators**: Clear visual indicators for different states (active, inactive, warnings)

### Performance Standards
- **Lazy Loading**: Load data only when needed
- **Pagination**: Implement pagination for large datasets (20 items per page default)
- **Caching**: Use localStorage for temporary data persistence
- **Optimized Queries**: Minimize database calls with efficient filtering
- **Bundle Optimization**: Minimize JavaScript bundle size

---

## 🧪 Testing Standards

### Frontend Testing
- **Unit Tests**: Test individual JavaScript functions and modules
- **Integration Tests**: Test API integration and data flow
- **User Interface Tests**: Test form validation and user interactions
- **Cross-browser Testing**: Ensure compatibility with modern browsers
- **Mobile Testing**: Verify responsive design on various screen sizes

### Backend Testing
- **API Testing**: Test all API endpoints with various input scenarios
- **Database Testing**: Verify data integrity and query performance
- **Security Testing**: Test authentication and authorization mechanisms
- **Error Handling**: Test error scenarios and edge cases
- **Performance Testing**: Load testing for concurrent users

### Security Testing
- **Input Validation**: Test all user inputs for XSS and injection attacks
- **Authentication**: Test session management and token validation
- **Authorization**: Verify proper access control for protected resources
- **Data Protection**: Test data encryption and secure transmission
- **Audit Logging**: Verify all operations are properly logged

---

## ⚡ Performance Standards

### Page Load Performance
- **Initial Load**: Pages should load within 2 seconds
- **API Response**: API calls should respond within 500ms
- **Database Queries**: Complex queries should execute within 100ms
- **Image Optimization**: Compress and optimize all images
- **Caching Strategy**: Implement appropriate caching mechanisms

### User Experience Performance
- **Smooth Animations**: 60fps animations with CSS transitions
- **Responsive Interactions**: UI should respond within 100ms
- **Progressive Loading**: Show content as it becomes available
- **Error Recovery**: Graceful handling of network failures
- **Offline Support**: Basic functionality when offline

### Scalability Considerations
- **Database Indexing**: Proper indexes for frequently queried fields
- **Query Optimization**: Efficient SQL queries with minimal joins
- **Resource Management**: Proper cleanup of resources and event listeners
- **Memory Management**: Avoid memory leaks in long-running applications
- **Concurrent Users**: Support for multiple simultaneous users

---

## 🔗 Dependencies

- PHP 8+ (XAMPP)
- MariaDB 10.4.32+ or MySQL 8.0+
- Apache with mod_rewrite enabled
- Bulk SMS provider API
- Google Drive API

---

## 📝 Change Control

- Any new module or API must follow placement rules in **TechRules.md v5.1**
- All functional changes must be documented as new FR entries with unique IDs
- Security-sensitive updates (Auth, Config, Vault) must be reviewed before deployment
- New features added as appendix to requirements document, preserving original requirement write-up
- **Database migrations MUST be tested on MariaDB 10.4.32+ before deployment**
- **Always address root causes, not symptoms** - Implement robust solutions that prevent issues
- **Take time to understand project intricacies** before starting work on any interface

---

## 🚀 Current Status

### Completed
- ✅ Complete system restoration with enhanced database
- ✅ Secure directory structure created
- ✅ Authentication system implemented
- ✅ Complete database schema with views and procedures
- ✅ Security refactoring completed
- ✅ File categorization rules established
- ✅ Helper system architecture defined
- ✅ Booking management system with rate calculator
- ✅ Client management with credit tracking
- ✅ Draft management with configurable limits
- ✅ Performance-optimized database queries

### In Progress
- 🔄 System testing and validation
- 🔄 Performance optimization verification

### Planned
- 📋 GitHub-inspired design system implementation across all interfaces
- 📋 Dashboard navigation refinement with GitHub patterns
- 📋 Reports interface with GitHub-inspired styling
- 📋 User management pages with consistent design language
- 📋 Advanced reporting system
- 📋 Discount management system
- 📋 Hostinger migration preparation

---

## 📝 Changelog

### Version 6.3 (2025-01-08) - Pre-Implementation Standards & Quality Assurance
- ✅ **Added Pre-Implementation Verification Checklist** - FR-93 to FR-112 for mandatory verification steps
- ✅ **Database Schema Verification Rules** - Check existing tables and columns before modifications
- ✅ **Code Pattern Discovery Requirements** - Search for existing implementations before creating new code
- ✅ **Integration Point Verification** - List and verify existing helpers and utilities
- ✅ **Standards Compliance Checks** - Ensure file locations and patterns follow established rules
- ✅ **Documentation Review Process** - Mandatory consultation of MasterSchema.md and related docs
- ✅ **Quality Assurance Framework** - Prevent duplicate code and ensure consistency

### Version 6.2 (2025-01-08) - Setup Module Completion & Module-by-Module Strategy
- ✅ **Completed Setup Module Refinements** - Applied GitHub patterns to all form elements, buttons, tables, and modals
- ✅ **Enhanced Form Styling** - GitHub-quality inputs, selects, textareas with proper focus states
- ✅ **Standardized Button System** - Consistent button styles with hover effects and accessibility
- ✅ **Refined Table Design** - GitHub-inspired data tables with proper spacing and interactions
- ✅ **Enhanced Modal System** - Professional modal dialogs with smooth animations
- ✅ **Polished Input Fields** - GitHub-quality form controls with validation states
- ✅ **Established Module-by-Module Strategy** - Comprehensive implementation approach for consistent UX
- ✅ **Updated MasterProject.md Standards** - Module-by-module implementation rules and quality assurance process
- ✅ **Setup Module Complete** - Reference implementation ready for extension to other modules

### Version 6.1 (2025-01-08) - GitHub-Inspired Design System
- ✅ **Established GitHub-Inspired Design Standards** - Comprehensive design system based on GitHub's proven UI patterns
- ✅ **Implemented Professional Sidebar Navigation** - Clean, collapsible navigation with GitHub-quality interactions
- ✅ **Applied GitHub Color Palette** - Subtle backgrounds, clear active states, professional contrast ratios
- ✅ **Enhanced Typography Standards** - Clear hierarchy, appropriate weights, letter-spacing for optimal readability
- ✅ **Added Micro-Interaction Standards** - Smooth transitions, hover effects, focus states matching GitHub quality
- ✅ **Implemented Accessibility Standards** - WCAG AA compliance, keyboard navigation, focus management
- ✅ **Created Spacing Guidelines** - Generous whitespace principles for visual comfort and professional appearance
- ✅ **Updated MasterProject.md Standards** - GitHub-inspired design system now the official standard for all interfaces

### Version 5.0 (2025-01-08) - Frontend Standards & Client Management
- ✅ **Added Frontend Development Standards** - Comprehensive JavaScript, form handling, and UI standards
- ✅ **Added Testing Standards** - Frontend, backend, and security testing guidelines
- ✅ **Added Performance Standards** - Page load, user experience, and scalability requirements
- ✅ **Implemented Professional Client Management System** - Complete client management interface
- ✅ **Created Reusable Modal System** - Framework for consistent user interactions
- ✅ **Enhanced Client API** - Added list_clients, get_statistics, export_clients endpoints
- ✅ **Implemented Multi-step Client Creation Wizard** - Professional 5-step client creation process
- ✅ **Updated Booking Integration** - Replaced inline modal with ClientModule integration
- ✅ **Added Advanced Client Model Methods** - Pagination, statistics, and export functionality

### Version 4.0 (2025-01-08) - Consolidated & Enhanced
- ✅ Complete system restoration with enhanced database
- ✅ Secure directory structure created
- ✅ Authentication system implemented
- ✅ Complete database schema with views and procedures
- ✅ Security refactoring completed
- ✅ File categorization rules established
- ✅ Helper system architecture defined
- ✅ Booking management system with rate calculator
- ✅ Client management with credit tracking
- ✅ Draft management with configurable limits
- ✅ Performance-optimized database queries

---

## 📚 Related Documentation

- **MasterReq.md v2.1** - Detailed functional requirements
- **TechRules.md v5.0** - Architecture and file placement rules
- **SECURE_REFACTOR_SUMMARY.md** - Security implementation details
- **pending.md** - Current task tracking and progress
- **XAMPP_INSTALLATION_GUIDE.md** - Development environment setup

---

## 🎯 Immediate Next Steps

1. **Apply GitHub-Inspired Design System** - Implement the established design standards across all interfaces
2. **Refine Dashboard Navigation** - Apply GitHub sidebar patterns to dashboard interface
3. **Update Reports Interface** - Implement GitHub-inspired styling for reports and analytics
4. **Enhance User Management** - Apply consistent design language to user management pages
5. **Test Design Consistency** - Verify GitHub-inspired patterns work across all interfaces
6. **Run complete database migration** - Execute `php database/run_complete_migration.php` to set up entire database
7. **Test the complete system** - Verify all functionality works end-to-end
8. **Create test bookings** - Use the seeded data to test the booking workflow
9. **Explore all interfaces** - Test booking creation, management, and rate calculation
10. **Verify system performance** - Test with the optimized views and procedures

---

✅ **End of MasterProject.V2.md v6.2**

*This document consolidates all project requirements, architecture decisions, implementation guidelines, GitHub-inspired design standards, module-by-module implementation strategy, and critical database compatibility rules for the SmartAd project.*
