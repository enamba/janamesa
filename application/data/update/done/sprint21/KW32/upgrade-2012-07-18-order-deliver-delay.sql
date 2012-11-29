-- @author vpriem
-- @since 18.07.2012

CREATE TABLE `order_deliverdelay` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `orderId` INT UNSIGNED NOT NULL,
    `serviceDeliverDelay` INT UNSIGNED NOT NULL,
    `courierDeliverDelay` INT UNSIGNED NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` TIMESTAMP NULL DEFAULT NULL,
    INDEX `fkOrder` (`orderId`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;