/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.54
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
ALTER TABLE rabatt_codes ADD COLUMN `edittime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
Alter Table canteen add column `active` tinyint(1) default 0;
/**
 * ATTTENTION: put INDEXes into your created tables
 */