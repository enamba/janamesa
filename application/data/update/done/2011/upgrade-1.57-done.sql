/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.57
 *
 * @author vpriem
 */
CREATE TABLE `order_courier_state` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `orderId` INT NOT NULL ,
    `courierCalled` TIME NULL DEFAULT NULL,
    `restaurantCalled` TIME NULL DEFAULT NULL,
    `courierPickup` TIME NULL DEFAULT NULL,
    `miscellaneous` TEXT NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `fkOrderId` (`orderId`)
) ENGINE = InnoDB;
