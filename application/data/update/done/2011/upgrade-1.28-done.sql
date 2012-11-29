/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.27
 * add redundant data to billing
 * @author mlaug
 */

alter table billing drop column bar;
alter table billing drop column nrorders;
alter table billing add column `item` int(11) default 0;
alter table billing add column `komm` int(11) default 0;
update billing set amount=0 where amount<0;