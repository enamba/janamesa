
-- @author alex
-- @since 24.05.2011
-- @description gprs printer

DROP TABLE IF EXISTS `gprs_printer`;
CREATE TABLE `gprs_printer` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `serialNr` VARCHAR(255) NOT NULL,
    `imei` VARCHAR(255) NOT NULL,
    `simTel` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `gprsApn` VARCHAR(255) NOT NULL,
    `gprsUserId` VARCHAR(255) NOT NULL,
    `gprsPass` VARCHAR(255) NOT NULL,
    `status` TINYINT(4) DEFAULT 0,
    `restaurantId` INT(11) UNSIGNED DEFAULT 0,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;