-- Database upgrade v1.69
-- @author vpriem
-- @since 12.11.2010

CREATE TABLE `heidelpay_transactions`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `orderId` INT UNSIGNED DEFAULT NULL,
    `Result` VARCHAR(255) NOT NULL,
    `Status` VARCHAR(255) NOT NULL,
    `Reason` VARCHAR(255) NOT NULL,
    `Return` VARCHAR(255) NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;