
-- @author Alex Vait
-- @since 31.07.12 
-- @data of partners

DROP TABLE IF EXISTS `partner_restaurants`;

CREATE TABLE `partner_restaurants` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `restaurantId` INT UNSIGNED NOT NULL,
    `email` VARCHAR(100) DEFAULT '',
    `mobile` VARCHAR(50) DEFAULT NULL,
    `temporarypassword` VARCHAR(50) DEFAULT NULL,
    `temporarypasswordsend` TIMESTAMP NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` TIMESTAMP NULL,
    INDEX `fkRestaurantId` (`RestaurantId`),
    UNIQUE `uqRestaurantId` (`restaurantId`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci