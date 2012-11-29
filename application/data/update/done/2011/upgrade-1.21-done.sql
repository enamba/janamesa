/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.21
 * @author vait
 */

ALTER TABLE restaurants ADD COLUMN `googleDescription` VARCHAR(200)  CHARACTER SET utf8 DEFAULT NULL;