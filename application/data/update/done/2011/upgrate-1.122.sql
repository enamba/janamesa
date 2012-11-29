/**
 * @author vpriem
 * @since 24.03.2011
 */
CREATE TABLE `paypal_notifications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `orderId` INT UNSIGNED NULL ,
    `params` TEXT NOT NULL ,
    `response` TEXT NOT NULL ,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    INDEX `fkOrderId` (`orderId`)
) ENGINE = InnoDB;