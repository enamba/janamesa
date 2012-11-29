-- Database upgrade v1.81
-- @author vpriem
-- @since 09.12.2010

CREATE TABLE `prompt_nr` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nr` VARCHAR(255) NOT NULL,
    `orderId` INT UNSIGNED NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE `uqNr` (`nr`),
    INDEX `fkOrderId` (`orderId`)
) ENGINE = InnoDB;