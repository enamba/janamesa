-- @author vpriem
-- 

ALTER TABLE `orders` ADD `sendBy` VARCHAR(50) NOT NULL AFTER `uuid`;

CREATE TABLE `order_sendby`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `orderId` INT UNSIGNED NOT NULL,
    `sendBy` VARCHAR(50) NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `fkOrder` (`orderId`)
) ENGINE = InnoDB;