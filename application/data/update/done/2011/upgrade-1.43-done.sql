/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.x
 *
 * @author mlaug
 */

alter table order_company_group add index (orderId);