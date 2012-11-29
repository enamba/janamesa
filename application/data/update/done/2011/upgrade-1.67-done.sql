/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.67
 *
 * @author vpriem
 */
ALTER TABLE `prompt_transactions`
    CHANGE `orderId` `orderId` INT NULL;
UPDATE `prompt_transactions`
    SET `orderId` = NULL
    WHERE `orderId` = 0;