-- Database upgrade v1.90
-- @author alex
-- @since 22.12.2010

CREATE TABLE `restaurant_commission` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `restaurantId` INT UNSIGNED NOT NULL,
    `item` INT UNSIGNED DEFAULT 0 COMMENT 'wieviel cent Provision pro bestelltem artikel zahlt der DL',
    `komm` INT UNSIGNED DEFAULT 0,
    `fee` INT UNSIGNED DEFAULT 0,
    `from` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
    `until` TIMESTAMP DEFAULT '0000-00-00 00:00:00'
) ENGINE = InnoDB;
