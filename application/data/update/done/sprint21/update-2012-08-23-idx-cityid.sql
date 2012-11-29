-- @author Jörg Wunder <wunder@lieferando.de>
-- @since 23.08.2012
ALTER TABLE `rabatt_city`
ADD INDEX `idxCityId` (`cityId ASC`);