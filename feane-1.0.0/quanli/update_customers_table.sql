USE quanli;

-- Add people_count column to customers table
ALTER TABLE customers ADD COLUMN people_count INT DEFAULT 1 AFTER gender;

-- Update existing records with random values between 1 and 8
UPDATE customers SET people_count = FLOOR(1 + RAND() * 8); 