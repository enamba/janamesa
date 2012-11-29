/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.47
 *
 * @author mlaug
 */


alter table orders_customer_notregistered add column ktoNr varchar(50) default Null;
alter table orders_customer_notregistered add column ktoBlz varchar(50) default Null;
