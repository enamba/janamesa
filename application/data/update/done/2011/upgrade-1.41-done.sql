/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.41
 *
 * @author vpriem
 */
ALTER TABLE `links`
    ADD `linksTo` INT UNSIGNED NOT NULL,
    ADD `linksFrom` INT UNSIGNED NOT NULL;

CREATE TABLE `links_to`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `linkId` INT UNSIGNED NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    INDEX `fkLink` (`linkId`)
) ENGINE = InnoDB;

CREATE TABLE `links_from`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `linkId` INT UNSIGNED NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    INDEX `fkLink` (`linkId`)
) ENGINE = InnoDB;