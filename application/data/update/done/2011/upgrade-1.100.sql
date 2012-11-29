/**
*   Database upgrade v1.100
*   @author alex
*   @since 26.01.2011
*/

TRUNCATE TABLE `elpedido.es`.`admin_access_groups`;
TRUNCATE TABLE `elpedido.es`.`admin_access_resources`;
TRUNCATE TABLE `elpedido.es`.`admin_access_rights`;
TRUNCATE TABLE `elpedido.es`.`admin_access_users`;

TRUNCATE TABLE `lieferando.at`.`admin_access_groups`;
TRUNCATE TABLE `lieferando.at`.`admin_access_resources`;
TRUNCATE TABLE `lieferando.at`.`admin_access_rights`;
TRUNCATE TABLE `lieferando.at`.`admin_access_users`;

TRUNCATE TABLE `lieferando.ch`.`admin_access_groups`;
TRUNCATE TABLE `lieferando.ch`.`admin_access_resources`;
TRUNCATE TABLE `lieferando.ch`.`admin_access_rights`;
TRUNCATE TABLE `lieferando.ch`.`admin_access_users`;

TRUNCATE TABLE `taxiresto.fr`.`admin_access_groups`;
TRUNCATE TABLE `taxiresto.fr`.`admin_access_resources`;
TRUNCATE TABLE `taxiresto.fr`.`admin_access_rights`;
TRUNCATE TABLE `taxiresto.fr`.`admin_access_users`;


INSERT INTO `elpedido.es`.`admin_access_groups` SELECT * FROM yourdelivery.admin_access_groups;
INSERT INTO `elpedido.es`.`admin_access_resources` SELECT * FROM yourdelivery.admin_access_resources;
INSERT INTO `elpedido.es`.`admin_access_rights` SELECT * FROM yourdelivery.admin_access_rights;
INSERT INTO `elpedido.es`.`admin_access_users` SELECT * FROM yourdelivery.admin_access_users;


INSERT INTO `lieferando.at`.`admin_access_groups` SELECT * FROM yourdelivery.admin_access_groups;
INSERT INTO `lieferando.at`.`admin_access_resources` SELECT * FROM yourdelivery.admin_access_resources;
INSERT INTO `lieferando.at`.`admin_access_rights` SELECT * FROM yourdelivery.admin_access_rights;
INSERT INTO `lieferando.at`.`admin_access_users` SELECT * FROM yourdelivery.admin_access_users;


INSERT INTO `lieferando.ch`.`admin_access_groups` SELECT * FROM yourdelivery.admin_access_groups;
INSERT INTO `lieferando.ch`.`admin_access_resources` SELECT * FROM yourdelivery.admin_access_resources;
INSERT INTO `lieferando.ch`.`admin_access_rights` SELECT * FROM yourdelivery.admin_access_rights;
INSERT INTO `lieferando.ch`.`admin_access_users` SELECT * FROM yourdelivery.admin_access_users;


INSERT INTO `taxiresto.fr`.`admin_access_groups` SELECT * FROM yourdelivery.admin_access_groups;
INSERT INTO `taxiresto.fr`.`admin_access_resources` SELECT * FROM yourdelivery.admin_access_resources;
INSERT INTO `taxiresto.fr`.`admin_access_rights` SELECT * FROM yourdelivery.admin_access_rights;
INSERT INTO `taxiresto.fr`.`admin_access_users` SELECT * FROM yourdelivery.admin_access_users;
