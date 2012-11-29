/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.25
 *
 * @author vpriem
 */
CREATE TABLE `geocoding` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `hash` VARCHAR(32) NOT NULL,
    `address` VARCHAR(255) NOT NULL,
    `response` TEXT NULL,
    `status` VARCHAR(50) NULL,
    `lat` FLOAT(10,7) NULL,
    `lng` FLOAT(10,7) NULL,
    `type` VARCHAR(50) NULL,
    `edittime` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE `uqHash` (`hash`)
) ENGINE = InnoDB;