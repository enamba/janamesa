-- @author Felix Haferkorn
-- @since 16.01.2012
-- additional column for transactions

ALTER TABLE `customer_fidelity_transaction` ADD COLUMN `updated` TIMESTAMP NULL DEFAULT NULL;
