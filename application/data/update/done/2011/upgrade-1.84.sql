-- Database upgrade v1.84
-- @author alex
-- @since 15.12.2010

ALTER TABLE `billing_assets` ADD COLUMN `projectnumberId` INT UNSIGNED DEFAULT NULL after `courierId`;
ALTER TABLE `billing_assets` ADD COLUMN `departmentId` INT UNSIGNED DEFAULT NULL after `courierId`;
