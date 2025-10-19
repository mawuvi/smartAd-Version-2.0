-- Add user association and session tracking to rates_staging table
-- Date: 2025-01-08
-- Purpose: Enable multi-device access and session management for staging data

-- Add user association and tracking columns
ALTER TABLE rates_staging 
ADD COLUMN uploaded_by INT NOT NULL COMMENT 'User who uploaded the file',
ADD COLUMN processed_at TIMESTAMP NULL COMMENT 'When row was processed',
ADD COLUMN processed_by INT NULL COMMENT 'User who processed the row',
ADD INDEX idx_uploaded_by (uploaded_by),
ADD INDEX idx_processed_at (processed_at),
ADD FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
ADD FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL;
