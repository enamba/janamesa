/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.24
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
ALTER TABLE `company_budgets_ydcard` ADD COLUMN `grantMinamount` TINYINT(4) DEFAULT NULL;