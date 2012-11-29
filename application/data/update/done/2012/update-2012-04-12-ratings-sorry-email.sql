-- @author vpriem
-- @since 1.04.12

ALTER TABLE `restaurant_ratings` 
    ADD `crmEmail` TINYINT UNSIGNED NOT NULL AFTER `status`,
    ADD INDEX `idxStatus` (`status`);

CREATE TABLE `restaurant_ratings_crm` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `ratingId` INT UNSIGNED NOT NULL,
    `adminId` INT UNSIGNED NOT NULL,
    `callName` VARCHAR(100) NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` TIMESTAMP NULL DEFAULT NULL,
    INDEX `fkRatingId` (`ratingId`),
    INDEX `fkAdminId` (`adminId`)
) ENGINE = InnoDB;