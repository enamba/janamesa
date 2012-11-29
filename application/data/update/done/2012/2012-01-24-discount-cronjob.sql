-- @author mlaug
-- @since 24.01.2012

CREATE TABLE `rabatt_generation_jobs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rabattId` INT UNSIGNED NOT NULL,
    `count` INT UNSIGNED DEFAULT 0,
    `email` VARCHAR(100) DEFAULT 'it@lieferando.de',
    `status` INT DEFAULT 0,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    `started` TIMESTAMP NULL,
    INDEX `fkRabattId` (`rabattId`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
