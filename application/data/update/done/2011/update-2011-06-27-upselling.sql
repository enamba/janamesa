-- @author vpriem
CREATE TABLE `upselling_goods`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `restaurantId` INT UNSIGNED NOT NULL,
    `adminId` INT UNSIGNED NOT NULL,

    `countCanton2626` INT UNSIGNED DEFAULT 0,
    `costCanton2626` INT UNSIGNED DEFAULT 0,
    `countCanton2828` INT UNSIGNED DEFAULT 0,
    `costCanton2828` INT UNSIGNED DEFAULT 0,
    `countCanton3232` INT UNSIGNED DEFAULT 0,
    `costCanton3232` INT UNSIGNED DEFAULT 0,
    `countServicing` INT UNSIGNED DEFAULT 0,
    `costServicing` INT UNSIGNED DEFAULT 0,
    `countBags` INT UNSIGNED DEFAULT 0,
    `costBags` INT UNSIGNED DEFAULT 0,
    `countSticks` INT UNSIGNED DEFAULT 0,
    `costSticks` INT UNSIGNED DEFAULT 0,

    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `fkRestaurantId` (`restaurantId`),
    INDEX `fkAdminId` (`adminId`)
    
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

UPDATE `billing_sent` 
SET `created` = `on` 
WHERE `created` = '0000-00-00 00:00:00';

ALTER TABLE `billing_sent` DROP `on` ;