/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.62
 *
 * @author vpriem
 */
-- CREATE TABLE `filters_applied` LIKE `filters`;
-- ALTER TABLE `meals` DROP INDEX `idxDescription`;

ALTER TABLE `links` DROP `extraBar`;

CREATE TABLE `links_restaurant` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `linkId` INT UNSIGNED NOT NULL,
    `restaurantId` INT UNSIGNED NOT NULL,
    UNIQUE `uqLinkRestaurant` (`linkId` , `restaurantId`)
) ENGINE = InnoDB;