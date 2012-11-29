
-- @author alex
-- @since 27.04.2011
-- update table satellites

CREATE TABLE `satellite_pictures` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `satelliteId` INT(10) UNSIGNED NOT NULL,
    `description` TEXT COLLATE utf8_unicode_ci,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    INDEX `fkSatelliteId` (`satelliteId`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO satellite_pictures (satelliteId, description) SELECT id, text FROM satellites;

ALTER TABLE `satellites`
    ADD COLUMN `impressum` TEXT DEFAULT NULL AFTER `robots`,
    ADD COLUMN `css` TEXT DEFAULT NULL AFTER `robots`,
    DROP COLUMN `logoAlt`,
    DROP COLUMN `logo`,
    DROP COLUMN `text`,
    DROP COLUMN `cssColor`;

CREATE TABLE `satellite_css` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `satelliteId` INT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `value` VARCHAR(11) NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    INDEX `fkSatelliteId` (`satelliteId`),
    UNIQUE `uqSatelliteName` (`satelliteId` , `name`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `orders` add column satellite VARCHAR(255) DEFAULT NULL;