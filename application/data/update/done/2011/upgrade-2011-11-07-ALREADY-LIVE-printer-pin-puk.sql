
-- @author vpriem
-- @since 07.11.11
-- @description

ALTER TABLE `printer_topup` 
ADD `simPin1` VARCHAR(255) NOT NULL AFTER `simNumber`,
ADD `simPin2` VARCHAR(255) NOT NULL AFTER `simPin1`,
ADD `simPuk1` VARCHAR(255) NOT NULL AFTER `simPin2`,
ADD `simPuk2` VARCHAR(255) NOT NULL AFTER `simPuk1`;
