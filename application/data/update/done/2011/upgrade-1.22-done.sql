/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.22
 * @author vpriem
 */

ALTER TABLE `servicetypes` ADD `seoName` VARCHAR(100) NOT NULL AFTER `className`;

UPDATE `servicetypes` SET `seoName` = 'Lieferservice' WHERE `id` = 1;
UPDATE `servicetypes` SET `seoName` = 'Catering' WHERE `id` = 2;
UPDATE `servicetypes` SET `seoName` = 'Getränkemarkt' WHERE `id` = 3;
UPDATE `servicetypes` SET `seoName` = 'Obstversand' WHERE `id` = 4;

alter table `billing` drop column temp;
alter table `billing` drop column costcenterId;
alter table `billing` drop column refNr;
alter table `billing` drop column mwst7;
alter table `billing` drop column mwst19;

CREATE TABLE `billing_sub` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `billingId` INT NOT NULL,
    `projectId` INT DEFAULT NULL,
    `costcenterId` INT DEFAULT NULL,
    `amount` INT DEFAULT 0,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    INDEX `fkBillingId` (`billingId`)
) ENGINE = InnoDB;

alter table `billing_customized` add column projectSub tinyint(4) default 0;
alter table `billing_customized` add column costcenterSub tinyint(4) default 0;

CREATE TABLE `billing_customized_single` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `heading` varchar(255),
  `street` varchar(255),
  `hausnr` varchar(255),
  `plz` varchar(255),
  `city` varchar(255),
  `addition` varchar(255),
  `content` varchar(255),
  `template` varchar(255) default 'standard',
  `verbose` tinyint(4) default 0,
  `showProject` tinyint(4) default 1,
  `showCostcenter` tinyint(4) default 1,
  `showEmployee` tinyint(4) default 1,
  `projectSub` tinyint(4) default 0,
  `costcenterSub` tinyint(4) default 0,
  `billingId` int(11) NOT NULL,
  UNIQUE INDEX `fkBillingId` (`billingId`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

alter table billing_customized modify column template varchar(255) default 'standard';