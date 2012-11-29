-- @author vpriem
-- @since 27.07.2011

CREATE TABLE `printer_topup` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `serialNumber` VARCHAR(255) NOT NULL,
    `simNumber` VARCHAR(255) NOT NULL,
    `online` TINYINT UNSIGNED NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` TIMESTAMP NULL
) ENGINE = InnoDB;

CREATE TABLE `printer_topup_queue` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `printerId` INT UNSIGNED NOT NULL,
    `orderId` INT UNSIGNED NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` TIMESTAMP NULL DEFAULT NULL,
    INDEX `fkPrinter` (`printerId`), 
    INDEX `fkOrder` (`orderId`)
) ENGINE = InnoDB;