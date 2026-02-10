ALTER TABLE payments ADD COLUMN patient_id bigint(20) UNSIGNED DEFAULT NULL AFTER invoice_id;
ALTER TABLE invoice_items ADD COLUMN discount_pct DECIMAL(5,2) DEFAULT 0 AFTER unit_price;
