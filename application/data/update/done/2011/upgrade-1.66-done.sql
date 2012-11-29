/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.66
 *
 * @author alex
 */

CREATE TABLE `canteen_company` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `canteenId` INT UNSIGNED NOT NULL,
    `companyId` INT UNSIGNED NOT NULL
) ENGINE = InnoDB;

INSERT INTO canteen_company(canteenId, companyId) SELECT id, companyId FROM canteen;

ALTER TABLE `canteen` DROP `companyId`;
