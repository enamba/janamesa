/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.37
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
ALTER TABLE restaurant_ratings ADD COLUMN `advise` tinyint(4) DEFAULT NULL;
ALTER TABLE restaurant_ratings ADD COLUMN `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL;

/**
 * ATTTENTION: put INDEXes into your created tables
 */