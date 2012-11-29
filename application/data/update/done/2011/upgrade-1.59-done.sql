/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.59
 *
 * @author vpriem
 */
ALTER TABLE `prompt_transactions`
    ADD `orderId` INT NOT NULL AFTER `id`,
    ADD INDEX `fkOrderId` (`orderId`);

UPDATE `courier_plz`
    SET `deliverTime` = 20
    WHERE `courierId` = 4;