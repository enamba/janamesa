/*
 * subdevide the salechannels 
 *
 */


ALTER table `orders` ADD COLUMN subSaleChannel VARCHAR(255) DEFAULT NULL;
ALTER table `orders` ADD COLUMN saleChannelPage TEXT COLLATE utf8_unicode_ci DEFAULT NULL;
update `orders` set saleChannelPage=saleChannelValue;
update `orders` set saleChannelValue='';
update `orders` set subSaleChannel='DISCOUNT' where rabattCodeId>0;
update `orders` set saleChannel='DIRECT' where saleChannel='DISCOUNT';

create table `salechannel_cost` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `saleChannel` VARCHAR(255) NOT NULL,
    `subSaleChannel` VARCHAR(255) DEFAULT NULL,
    `cost` INT(11) DEFAULT 0,
    `name` VARCHAR(255) DEFAULT NULL,
    `from` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    `until` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `fkSaleChannel` (`saleChannel`),
    INDEX `fkSubSaleChannel` (`subSaleChannel`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;