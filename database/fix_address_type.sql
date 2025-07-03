-- Fix address_type column to allow custom address types
-- This migration changes the ENUM to VARCHAR to support custom text input

USE healthydash;

-- First, backup existing data
CREATE TABLE user_addresses_backup AS SELECT * FROM user_addresses;

-- Modify the address_type column to VARCHAR(50) to allow custom types
ALTER TABLE user_addresses 
MODIFY COLUMN address_type VARCHAR(50) NOT NULL DEFAULT 'Home';

-- Add a check constraint to ensure valid values (optional, for data integrity)
ALTER TABLE user_addresses 
ADD CONSTRAINT chk_address_type 
CHECK (address_type IN ('Home', 'Office', 'Other') OR LENGTH(address_type) <= 50);

-- Verify the change
DESCRIBE user_addresses;

-- Show sample data to confirm it works
SELECT address_id, user_id, address_type, LEFT(address_line, 50) as address_preview 
FROM user_addresses 
LIMIT 5; 