/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.6
 *
 * @author abriliano
 */
ALTER TABLE `restaurants` ADD `googleName` VARCHAR( 255 ) NULL DEFAULT NULL;