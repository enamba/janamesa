/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.50
 *
 * @author vpriem
 */
ALTER TABLE `orders` ADD `paymentResp` VARCHAR(255) NULL AFTER `payment`;