/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.64
 *
 * @author alex
 */

alter table companys add column debit tinyint(4) default 0;