/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.37
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
ALTER TABLE customers ADD COLUMN `premium` tinyint(4) DEFAULT NULL;

/**
 * ATTTENTION: put INDEXes into your created tables
 */