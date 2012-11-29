-- @author alex
-- @since 16.07.12

ALTER TABLE `restaurants` ADD `tel3` VARCHAR(50) DEFAULT NULL AFTER `tel`;
ALTER TABLE `restaurants` ADD `tel2` VARCHAR(50) DEFAULT NULL AFTER `tel`;
