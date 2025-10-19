-- Migration: Add rates_staging table for bulk upload staging area
-- Date: 2025-01-08
-- Purpose: Support staging area for bulk rate uploads with validation and selection

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
    selected_publication_id INT NULL COMMENT 'User-selected publication ID for merge',
    merge_action ENUM('create_new', 'use_existing', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (upload_session_id),
    INDEX idx_status (validation_status),
    INDEX idx_selected (is_selected)
);

-- Add comments for documentation
ALTER TABLE rates_staging COMMENT = 'Staging table for bulk rate uploads with validation and user selection';

