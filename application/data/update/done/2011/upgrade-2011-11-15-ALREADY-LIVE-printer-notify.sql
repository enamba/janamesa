
-- @author vpriem
-- @since 15.11.11
-- @description

ALTER TABLE `printer_topup` 
ADD `notify` TINYINT UNSIGNED NOT NULL AFTER `signal`;
