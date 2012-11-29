-- @author Alex Vait
-- @since 26.06.2012

DROP TABLE IF EXISTS `meal_ingredients`;

CREATE TABLE `meal_ingredients` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `groupId` int(10) unsigned NOT NULL,
    `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `meal_ingredients_nn`;

CREATE TABLE `meal_ingredients_nn` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `mealId` int(10) unsigned NOT NULL,
    `ingredientId` int(10) NOT NULL,
    `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uqMealIngredient` (`mealId`, `ingredientId`),
    INDEX `mealIdIdx` (`mealId`),
    INDEX `ingredientIdIdx` (`ingredientId`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `meals` ADD COLUMN `attributes` varchar(255) NULL DEFAULT NULL AFTER `mwst`;

UPDATE `meals` SET `attributes` = concat(
    if(`vegetarian`, 'vegetarian,', ''), 
    if(`bio`, 'bio,', ''), 
    if(`spicy`, 'spicy,', ''), 
    if(`garlic`, 'garlic,', ''), 
    if(`fish`, 'fish,', ''));

UPDATE `meals` SET `attributes` = SUBSTRING(`attributes`, 1, LENGTH(`attributes`)-1);

-- ALTER TABLE `meals` DROP COLUMN `vegetarian`, DROP COLUMN `bio`, DROP COLUMN `spicy`, DROP COLUMN `garlic`, DROP COLUMN `fish`;

-- already live
-- INSERT IGNORE INTO `admin_access_resources` (`action`)
-- VALUES ('administration_partnerlogin');
 
-- INSERT IGNORE INTO `admin_access_rights` (`groupId`, `resourceId`)
-- SELECT 14, `id`
-- FROM `admin_access_resources`
-- WHERE `action` = 'administration_service_meals_ingredients';

-- INSERT IGNORE INTO `admin_access_rights` (`groupId`, `resourceId`)
-- SELECT 25, `id`
-- FROM `admin_access_resources`
-- WHERE `action` = 'administration_service_meals_ingredients';