
-- @author vpriem
-- @since 18.01.2012

CREATE TABLE `ebanking_refund_transactions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `orderId` INT UNSIGNED NOT NULL,
    `request` TEXT NOT NULL,
    `response` TEXT NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    INDEX `fkOrderId` (`orderId`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

