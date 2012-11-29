/*
 * default sql script fuer tabellen, bitte alles auskommentieren
 * damit es waehrend eines builds nichts ausgefuehrt wird
 */

/* the default collation MUST be set adding a varchar, char or anything text like
 * or if we create a table
 * CHARACTER SET utf8 COLLATE utf8_general_ci
 *
 * e.g. alter table orders add column searchTerm varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci default null;
 *
 * create table `test` ( ... ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
 */

DROP TABLE IF EXISTS `inventory`;
create table `inventory`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `restaurantId` INT UNSIGNED NOT NULL,

    `countCanton2626` INT DEFAULT 0,
    `specialCostCanton2626` INT DEFAULT 0,
    `countCanton3232` INT DEFAULT 0,
    `specialCostCanton3232` INT DEFAULT 0,
    `countServicing` INT DEFAULT 0,
    `countBags` INT DEFAULT 0,
    `countSticks` INT DEFAULT 0,

    `countFlyer` INT DEFAULT 0,
    `typeFlyer` VARCHAR(255) DEFAULT NULL,
    `colorOneFlyer` VARCHAR(255) DEFAULT NULL,
    `colorTwoFlyer` VARCHAR(255) DEFAULT NULL,
    `colorThreeFlyer` VARCHAR(255) DEFAULT NULL,

    `printerCostShare` TINYINT(4) DEFAULT 0,
    `printerCostPercent` INT DEFAULT 0,
    `printerOwn` INT DEFAULT 0,
    `printerFormat` TINYINT(4) DEFAULT 1,
    `printerPrio` TINYINT(4) DEFAULT 1,
    `printerNextDate` DATETIME DEFAULT NULL,
    
    `website` TINYINT(4) DEFAULT 0,
    `websiteCost` INT DEFAULT 0,
    `colorOneWebsite` VARCHAR(255) DEFAULT NULL,
    `colorTwoWebsite` VARCHAR(255) DEFAULT NULL,
    `colorThreeWebsite` VARCHAR(255) DEFAULT NULL,
    
    `terminal` TINYINT(4) DEFAULT 0,
    `terminalBail` INT DEFAULT 0
    
)ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `inventory_status`;
CREATE TABLE `inventory_status` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `inventoryId` INT NOT NULL,
    `type` VARCHAR(255) NOT NULL,
    `status` VARCHAR(255) NOT NULL,
    `comment` TEXT NOT NULL,
    `adminId` INT NOT NULL,
    `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;