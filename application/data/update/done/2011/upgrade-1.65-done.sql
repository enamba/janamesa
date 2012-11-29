/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.65
 *
 * @author alex
 */

alter table restaurants add column billDeliver enum('fax','email', 'post') default 'fax';