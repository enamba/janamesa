
-- @author vpriem
-- @since 16.11.11
-- @description

CREATE TABLE `heyho_messages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `type` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `state` TINYINT NOT NULL,
    `callbackAvailable` TEXT NOT NULL,
    `callbackTriggered` TEXT NOT NULL,
    `adminId` INT UNSIGNED NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` TIMESTAMP NULL DEFAULT NULL,
    INDEX `fkAdmin` (`adminId`)
) ENGINE = InnoDB;
