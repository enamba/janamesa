/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.2
 * Create satellites table
 *
 * @author vpriem
 */
CREATE TABLE `satellites` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `restaurantId` INT UNSIGNED NOT NULL,
    `domain` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `keywords` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `robots` VARCHAR(10) NOT NULL,
    `logo` VARCHAR(255) NOT NULL,
    `logoAlt` VARCHAR(100) NOT NULL,
    `picture` VARCHAR(255) NOT NULL,
    `pictureAlt` VARCHAR(100) NOT NULL,
    `text` TEXT NOT NULL,
    `cssLayout` VARCHAR(50) NOT NULL,
    `cssColor` VARCHAR(50) NOT NULL,
    `disabled` TINYINT UNSIGNED NOT NULL,
    `editTime` TIMESTAMP NOT NULL,
    INDEX `fkRestaurantId` (`restaurantId`),
    UNIQUE `uqDomain` (`domain`),
    INDEX `idxDisabled` (`disabled`)
) ENGINE = InnoDB;