-- Add missing booking columns
-- Migration: 2025_10_08_add_missing_booking_columns.sql

ALTER TABLE bookings 
ADD COLUMN subtotal DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN tax_total DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN total_amount DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN notes TEXT NULL;

-- Add foreign key constraint for rate_id
ALTER TABLE bookings 
ADD CONSTRAINT fk_bookings_rate 
FOREIGN KEY (rate_id) REFERENCES rates(id) ON DELETE RESTRICT;
