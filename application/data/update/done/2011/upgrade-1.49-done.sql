/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.49
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
ALTER TABLE customer_company ADD COLUMN `canteen` TINYINT(4) DEFAULT 0;
/**
 * ATTTENTION: put INDEXes into your created tables
 */