/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.23
 *
 * @author vpriem
 */

ALTER TABLE `satellites`
    CHANGE `robots` `robots` VARCHAR(20) NOT NULL DEFAULT 'all';

CREATE TABLE `links` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `url` VARCHAR(255) NOT NULL,
    `template` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `keywords` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `robots` VARCHAR(20) NOT NULL DEFAULT 'all',
    `h1` VARCHAR(255) NOT NULL,
    `h21` VARCHAR(255) NOT NULL,
    `h22` VARCHAR(255) NOT NULL,
    `h23` VARCHAR(255) NOT NULL,
    `h24` VARCHAR(255) NOT NULL,
    `h25` VARCHAR(255) NOT NULL,
    `h31` VARCHAR(255) NOT NULL,
    `h32` VARCHAR(255) NOT NULL,
    `h33` VARCHAR(255) NOT NULL,
    `h34` VARCHAR(255) NOT NULL,
    `h35` VARCHAR(255) NOT NULL,
    `text1` TEXT NOT NULL,
    `text2` TEXT NOT NULL,
    `text3` TEXT NOT NULL,
    `text4` TEXT NOT NULL,
    `text5` TEXT NOT NULL,
    `picture` VARCHAR(255) NOT NULL,
    `pictureAlt` VARCHAR(255) NOT NULL,
    `button` VARCHAR(255) NOT NULL,
    UNIQUE `uqUrl` (`url`)
) ENGINE = InnoDB;