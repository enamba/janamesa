
-- @author alex
-- @since 19.07.11
-- meal types and ingredients

DROP TABLE IF EXISTS `meal_types`;
CREATE TABLE `meal_types` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,   
    `parentId` INT(10) UNSIGNED NOT NULL,
    `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `meal_types_nn`;
CREATE TABLE `meal_types_nn` (
    `id` int(11) NOT NULL auto_increment,
    `mealId` int(11) not null,
    `typeId` int(11) not null,
    PRIMARY KEY  (`id`),
    UNIQUE KEY `uqMealType` (`mealId`, `typeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `meal_ingredients`;
CREATE TABLE `meal_ingredients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mealId` int(10) unsigned NOT NULL,
  `ingredient` varchar(255) NOT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uqMealIngredient` (`mealId`,`ingredient`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
