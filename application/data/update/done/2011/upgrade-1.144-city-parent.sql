
-- @author alex
-- @since 06.06.11

ALTER TABLE `city` ADD COLUMN `parentCityId` INT(11) DEFAULT 0 AFTER stateId;

