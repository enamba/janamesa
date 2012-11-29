/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.45
 *
 * @author mlaug
 */

CREATE TABLE `billing_merge`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `kind` ENUM('company','rest'),
    `parent` int(11) not null,
    `child` int(11) not null
) ENGINE = InnoDB;

CREATE TABLE `prompt_tracking`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `orderId` int(11) not null,
    `trackingId` int(11) not null,
    `time` timestamp default current_timestamp,
    INDEX `fkorderId` (`orderId`),
    INDEX `fktrackingId` (`trackingId`)
) ENGINE = InnoDB;

CREATE TABLE `prompt_transactions`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `params` Blob default null,
    `result` Blob default null,
    `action` varchar(50) not null,
    `success` tinyint(4) default 0,
    `time` timestamp default current_timestamp
) ENGINE = InnoDB;

alter table customers add column debit tinyint(4) default 0;
alter table customers add column ktoNr varchar(50) default Null;
alter table customers add column ktoBlz varchar(50) default Null;
