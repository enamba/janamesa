/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.47
 *
 * @author mlaug
 */


alter table orders_bucket_meals_extras add column name varchar(255) default 'unbekanntes Extra';
alter table orders_bucket_meals_options add column name varchar(255) default 'unbekannte Option';