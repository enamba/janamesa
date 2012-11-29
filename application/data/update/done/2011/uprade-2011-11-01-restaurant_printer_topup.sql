
-- @author alex
-- @since 01.11.11
-- gprs printer to restaurant association

DROP TABLE IF EXISTS `restaurant_printer_topup`;

CREATE TABLE `restaurant_printer_topup` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `restaurantId` INT(10) UNSIGNED NOT NULL,   
    `printerId` INT(10) UNSIGNED NOT NULL,  
    `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,   
    `updated` timestamp NULL DEFAULT NULL,
    UNIQUE KEY `uqRestaurantPrinter` (`restaurantId`,`printerId`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
