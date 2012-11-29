/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.56
 *
 * @author vpriem
 */
ALTER TABLE `links_related` ADD `manual` TINYINT UNSIGNED NOT NULL;
UPDATE `links_related` SET `manual` = 1;

ALTER TABLE `links`
    ADD `extraBar` TEXT NOT NULL AFTER `sideBar`,
    ADD `categoryId` INT UNSIGNED NOT NULL,
    ADD INDEX `fkCategoryId` (`categoryId`);

CREATE TABLE `links_categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `level` TINYINT UNSIGNED NOT NULL,
    `subLevel` TINYINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL
) ENGINE = InnoDB;

INSERT INTO `links_categories` (`level`, `subLevel`, `name`) VALUES
    ('1', '1', 'Pizza'),
    ('1', '2', 'Sushi'),
    ('1', '3', 'Lieferservice'),
    ('1', '4', 'Chin/Thai/Asia/Indisch'),
    ('1', '5', 'Lieferservice Berlin'),
    ('1', '6', 'Bestellen'),
    ('1', '7', 'Fruehstueck/Brot/Broetchen/Buffet'),
    ('2', '1', 'Catering'),
    ('2', '2', 'Partyservice'),
    ('3', '1', 'Getraenke'),
    ('4', '1', 'Obst'),
    ('4', '2', 'Bio'),
    ('4', '3', 'Gemuese');

CREATE TABLE `links_navigation` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `linkId` INT UNSIGNED NOT NULL,
    `navigationLinkId` INT UNSIGNED NOT NULL,
    `manual` TINYINT UNSIGNED NOT NULL,
    UNIQUE `uqLinkNavigation` (`linkId` , `navigationLinkId`)
) ENGINE = InnoDB;

CREATE TABLE `links_list` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `linkId` INT UNSIGNED NOT NULL,
    `listLinkId` INT UNSIGNED NOT NULL,
    `manual` TINYINT UNSIGNED NOT NULL,
    UNIQUE `uqLinkList` (`linkId` , `listLinkId`)
) ENGINE = InnoDB;