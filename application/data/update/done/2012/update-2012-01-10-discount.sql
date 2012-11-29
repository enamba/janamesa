
-- @author alex, daniel
-- @since 10.01.12

alter table rabatt add column `referer` varchar(255) default null after status; 
alter table rabatt add unique `uqReferer` (`referer`);   

alter table rabatt add column `type` tinyint(4) default 0 comment '0 => standard, 1=> Neukundenaktion ohne Verifizierung, 2 =>  Neukundenaktion mit dynamischer Verifikation, 3 => Neukundenaktion mit statischem Code' after referer ;

alter table rabatt_check add column `name` varchar(255) default null after `email`; 
alter table rabatt_check add column `prename` varchar(255) default null after `name`;
alter table rabatt_check add column `referer` varchar(255) default null after `id`;
alter table rabatt_check add column `rabattVerificationId` INT(10) DEFAULT NULL;
alter table rabatt_check add column `telSend` TIMESTAMP NULL DEFAULT NULL AFTER `customerId`;
alter table rabatt_check add column `emailConfirmed` TIMESTAMP NULL DEFAULT NULL AFTER `customerId`;
alter table rabatt_check add column `emailSendCount` TINYINT(4) NULL DEFAULT 0;
alter table rabatt_check add column `smsSendCount` TINYINT(4) NULL DEFAULT 0;
alter table rabatt_check change column `rabattId` `rabattCodeId` INT(11) NULL DEFAULT NULL;

alter table rabatt_codes_external_mailing add column `rabattId` INT(11) NULL DEFAULT NULL;
alter table rabatt_codes_external_mailing add INDEX `idxRabatt` (`rabattId`) ;


DROP TABLE IF EXISTS `rabatt_codes_verification`;
CREATE TABLE `rabatt_codes_verification` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,       
    `registrationCode` VARCHAR(255) DEFAULT NULL,
    `send` TINYINT(4) DEFAULT 0,
    `rabattId` INT(10) DEFAULT NULL,
    `updated` TIMESTAMP NULL DEFAULT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE `uqRabattVerification` (`registrationCode`, `rabattId`),
    INDEX `idxRegistrationCode` (`registrationCode`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
