/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.18
 *
 * @author vait
 */

alter table `meals` add column `alcohol` tinyint(4) default 0;

