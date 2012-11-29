/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.50
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
ALTER TABLE customers ADD COLUMN `ktoName` varchar(100) DEFAULT NULL after `debit` ;
ALTER TABLE rabatt ADD COLUMN `onlyPremium` TINYINT(1) DEFAULT 0 AFTER `onlyCustomer` ;
ALTER TABLE rabatt ADD COLUMN `countUsage` INT(4) DEFAULT 0 AFTER `rrepeat` ;
ALTER TABLE rabatt_codes ADD COLUMN `countUsed` INT(4) DEFAULT 0 AFTER `used` ;

/**
 * ATTTENTION: put INDEXes into your created tables
 */