-- SQL command to add prescription_id column to pharmacy_transactions table
-- This allows linking transactions to prescriptions for better tracking

ALTER TABLE `pharmacy_transactions` 
ADD COLUMN `prescription_id` INT UNSIGNED NULL AFTER `patient_id`,
ADD INDEX `idx_pharmacy_transactions_prescription_id` (`prescription_id`);

-- Optional: Add foreign key constraint (uncomment if you want referential integrity)
-- ALTER TABLE `pharmacy_transactions`
-- ADD CONSTRAINT `fk_pharmacy_transactions_prescription_id`
-- FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`)
-- ON DELETE SET NULL ON UPDATE CASCADE;

