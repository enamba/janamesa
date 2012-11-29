/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.53
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
ALTER TABLE rabatt ADD COLUMN `notStartedInfo` VARCHAR(100) DEFAULT NULL;
/**
 * ATTTENTION: put INDEXes into your created tables
 */