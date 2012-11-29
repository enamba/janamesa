/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.34
 *
 * @author vpriem
 */
CREATE TABLE `filters` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `type` VARCHAR(255) NOT NULL,
    `search` VARCHAR(255) NOT NULL,
    `replace` VARCHAR(255) NOT NULL,
    `limit` TINYINT NOT NULL DEFAULT -1,
    `priority` TINYINT NOT NULL,
    INDEX `idxName` (`name`)
) ENGINE = InnoDB;