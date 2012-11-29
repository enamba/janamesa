/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.30
 * add some extra informations to admonation
 * @author mlaug
 */

ALTER TABLE billing_admonation add column `text` text default '';
alter table billing_admonation add column `reminder` int(11) default 14;
alter table billing_customized add column `reminder` int(11) default 14;
alter table billing_customized_single add column `reminder` int(11) default 14;