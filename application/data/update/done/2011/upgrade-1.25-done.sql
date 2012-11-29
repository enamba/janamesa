/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.25
 *
 * @author alex
 */
update restaurants set regtime=unix_timestamp('2010-01-01') where regtime=0;

alter table restaurant_plz add column `noDeliverCostAbove` INT(11);