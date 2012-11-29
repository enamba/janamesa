/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.x
 *
 * @author mlaug
 */

alter table orders add column ident varchar(255) default null;