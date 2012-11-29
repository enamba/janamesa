/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.35
 *
 * @author vpriem
 */
ALTER TABLE `courier`
    CHANGE `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ADD `subvention` DECIMAL(5, 2) UNSIGNED NOT NULL AFTER `notify`;

CREATE TABLE `courier_costmodel` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `courierId` INT UNSIGNED NOT NULL,
    `startCost` INT UNSIGNED NOT NULL,
    `kmInclusive` TINYINT UNSIGNED NOT NULL,
    `kmCost` INT UNSIGNED NOT NULL,
    `tax` INT UNSIGNED NOT NULL,
    `taxInclusive` TINYINT UNSIGNED NOT NULL,
    UNIQUE `uqCourierId` (`courierId`)
) ENGINE = InnoDB;

ALTER TABLE `orders`
    ADD `courierDiscount` INT DEFAULT 0 AFTER `courierCost`;