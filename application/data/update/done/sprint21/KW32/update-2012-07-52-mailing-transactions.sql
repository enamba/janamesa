DROP TABLE IF EXISTS `mailing_optivo`;
CREATE TABLE `mailing_optivo` ( 
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `status` TINYINT(4) NOT NULL DEFAULT 1,
    `start` timestamp NOT NULL DEFAULT 0,
    `end`timestamp NOT NULL DEFAULT 0,   
    `mailingId` VARCHAR(100) NOT NULL,
    `customerOrderCount`  VARCHAR(100) NOT NULL,
    `parameters` VARCHAR(255) NOT NULL,
    `invertCity` TINYINT(4) DEFAULT 0,
    `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
     PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `mailing_optivo_city`;
CREATE TABLE `mailing_optivo_city` ( 
     `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     `mailingId`  int(10) unsigned NOT NULL,
     `cityId` int(10) unsigned NOT NULL,
      PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;