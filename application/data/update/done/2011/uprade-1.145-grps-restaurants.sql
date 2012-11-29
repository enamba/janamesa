
-- @author alex
-- @since 08.06.11
-- gprs printer to restaurant association

CREATE TABLE `restaurant_gprs_printer` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `restaurantId` INT(10) UNSIGNED NOT NULL,   
    `gprsPrinterId` INT(10) UNSIGNED NOT NULL,  
    `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,   
    `updated` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO `restaurant_gprs_printer` (`gprsPrinterId`, `restaurantId`)  (SELECT id, restaurantId FROM gprs_printer WHERE restaurantId > 0);

ALTER TABLE gprs_printer DROP COLUMN restaurantId;