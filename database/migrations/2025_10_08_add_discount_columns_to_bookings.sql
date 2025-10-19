-- Add discount columns to bookings table
-- Migration: 2025_10_08_add_discount_columns_to_bookings.sql

ALTER TABLE bookings 
ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN discount_reason TEXT NULL;
