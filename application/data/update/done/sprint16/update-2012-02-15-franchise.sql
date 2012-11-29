-- @description merge from noContract, Eatstar, Bloomsbury, Butler, Premium into one column franchisTypeId 
-- @author afrank
-- @since 15-02-2012


ALTER TABLE `restaurants` ADD COLUMN `franchiseTypeId` INT DEFAULT 1 AFTER `placesStatus`;

CREATE TABLE `restaurant_franchisetype`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO `restaurant_franchisetype`(`id`, `name`) VALUES 
(1, 'Normal'),
(2, 'NoContract'),
(3, 'Premium'),
(4, 'Butler'),
(5, 'Bloomsburys'),
(6, 'Eatstar');

UPDATE restaurants set franchiseTypeId=2 where noContract>0;
UPDATE restaurants set franchiseTypeId=3 where premium>0;
UPDATE restaurants set franchiseTypeId=4 where butler>0;
UPDATE restaurants set franchiseTypeId=5 where bloomsburys>0;
UPDATE restaurants set franchiseTypeId=6 where eatstar>0;


ALTER TABLE `restaurants` DROP COLUMN noContract;
ALTER TABLE `restaurants` DROP COLUMN premium;
ALTER TABLE `restaurants` DROP COLUMN butler;
ALTER TABLE `restaurants` DROP COLUMN eatstar;
ALTER TABLE `restaurants` DROP COLUMN bloomsburys;