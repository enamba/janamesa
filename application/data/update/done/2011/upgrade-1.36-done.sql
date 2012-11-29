/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.36
 * add premium flag to the restaurants
 * @author alex
 */

ALTER TABLE restaurants ADD COLUMN `premium` TINYINT(4) default 0;