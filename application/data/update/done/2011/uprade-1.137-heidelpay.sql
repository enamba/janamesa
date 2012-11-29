
-- @author vpriem
-- @since 17.05.11
-- @description a table to log heidelpay transactions

CREATE TABLE `heidelpay_wpf_transactions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `orderId` INT UNSIGNED NOT NULL,
    `params` TEXT NOT NULL,
    `response` TEXT NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    INDEX `fkOrderId` (`orderId`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;