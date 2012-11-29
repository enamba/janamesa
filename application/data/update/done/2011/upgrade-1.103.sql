/**
* Database upgrade v1.103
* @author mlaug
* @since 31.01.2011
*/

CREATE TABLE `interfax_transactions`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `orderId` INT UNSIGNED DEFAULT NULL,
    `transactionId` VARCHAR(255) NOT NULL,
    `lastResponse` TEXT DEFAULT NULL,
    `params` TEXT NOT NULL,
    `currentStatus` VARCHAR(255) DEFAULT 'unconfirmed',
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     INDEX `fkOrderId` (`orderId`),
     INDEX `idxCurrentStatus` (`currentStatus`)
) ENGINE = InnoDB;

alter table `restaurants` add column faxService VARCHAR(50) Default 'retarus';
alter table `courier` add column faxService VARCHAR(50) Default 'retarus';