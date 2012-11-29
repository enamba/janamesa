/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.31
 *
 * @author vpriem
 */
DROP TABLE IF EXISTS `links`;
CREATE TABLE `links` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `url` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `keywords` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `robots` VARCHAR(20) NOT NULL DEFAULT 'all',
    `googlebot` VARCHAR(20) NOT NULL DEFAULT 'all',
    `headline1` VARCHAR(255) NOT NULL,
    `headline2` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `inputPosition` VARCHAR(255) NOT NULL,
    `inputValue` VARCHAR(255) NOT NULL,
    `buttonImage` VARCHAR(255) NOT NULL,
    `buttonValue` VARCHAR(255) NOT NULL,
    `backgroundImage` VARCHAR(255) NOT NULL,
    `disturberImage` VARCHAR(255) NOT NULL,
    `disturberPosition` VARCHAR(255) NOT NULL,
    `previewImage` VARCHAR(255) NOT NULL,
    `previewText` TEXT NOT NULL,
    `navBar` TEXT NOT NULL,
    `sideBar` TEXT NOT NULL,
    UNIQUE `uqUrl` (`url`)
) ENGINE = InnoDB;

CREATE TABLE `links_related` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `linkId` INT UNSIGNED NOT NULL,
    `relatedLinkId` INT UNSIGNED NOT NULL,
    UNIQUE `uqLinkRelated` (`linkId` , `relatedLinkId`)
) ENGINE = InnoDB;