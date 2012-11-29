/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.58
 * @author mlaug
 */
alter table orders add column billCourier int(11) default null;
alter table courier add column plz int(11) default null;
alter table courier add column customerNr varchar(50) default null;
alter table billing_customized modify column mode enum('rest','company','reserved','courier') default null;
alter table billing_customized modify column mode enum('rest','comp','courier') default null;
alter table billing_customized modify column template varchar(255) default 'standard';
alter table courier add column komm int(11) default 0;