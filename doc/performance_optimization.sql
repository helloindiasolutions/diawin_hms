-- Performance Optimization Script for Melina HMS
-- This script adds missing indexes to speed up search operations in the POS/Invoice creation screen

USE `melina_hms`;

-- 1. Optimize Patient Search (Name search is currently slow)
ALTER TABLE `patients` ADD INDEX `idx_patient_name` (`first_name`, `last_name`);
ALTER TABLE `patients` ADD INDEX `idx_patient_last_name` (`last_name`);

-- 2. Optimize Product Search (Name search is currently slow)
ALTER TABLE `products` ADD INDEX `idx_product_name` (`name`);
ALTER TABLE `products` ADD INDEX `idx_product_sku` (`sku`);

-- 3. Optimize Inventory Batches (Stock lookups)
ALTER TABLE `inventory_batches` ADD INDEX `idx_batch_product_qty` (`product_id`, `qty_available`);

-- 4. Verify existing indexes (Reference only)
-- patients already has: PRIMARY(patient_id), UNIQUE(branch_id, mrn), KEY(primary_mobile)
-- products already has: PRIMARY(product_id), KEY(branch_id)
-- inventory_batches already has: PRIMARY(batch_id), KEY(product_id), KEY(branch_id)
