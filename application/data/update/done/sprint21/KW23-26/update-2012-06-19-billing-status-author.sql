-- @author alex
-- @since 19.06.12

ALTER TABLE `billing_status` DROP COLUMN `descr`;
ALTER TABLE `billing_status` DROP COLUMN `updated`;
ALTER TABLE `billing_status` ADD `billingId` INT(11) DEFAULT '0' AFTER `id`;
ALTER TABLE `billing_status` ADD `adminId` INT(11) DEFAULT '0' AFTER `id`;
ALTER TABLE `billing_status` ADD `status` INT(11) DEFAULT '0' AFTER `id`;
