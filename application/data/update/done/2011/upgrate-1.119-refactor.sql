/**
 * @author vpriem
 * @since 25.02.2011
 */
CREATE TABLE `master_notifications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `orderId` INT UNSIGNED NULL ,
    `response` TEXT NOT NULL ,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    INDEX `fkOrderId` (`orderId`)
) ENGINE = InnoDB;