/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.44
 *
 * @author vpriem
 */
ALTER TABLE `links` ADD `tab` VARCHAR(60) NOT NULL;