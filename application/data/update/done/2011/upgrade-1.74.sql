-- Database upgrade v1.74
-- @author vpriem
-- @since 12.11.2010

CREATE TABLE `direct_ebanking_transactions`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `orderId` INT UNSIGNED NOT NULL,
    `response` TEXT NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `fkOrderId` (`orderId`)
) ENGINE = InnoDB;