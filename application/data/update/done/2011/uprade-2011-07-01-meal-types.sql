
-- @author alex
-- @since 01.07.11
-- meal types

CREATE TABLE `meal_types` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,   
    `parentId` INT(10) UNSIGNED NOT NULL,
    `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uqName` (`name`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;


CREATE TABLE `meal_types_nn` (
    `id` int(11) NOT NULL auto_increment,
    `mealId` int(11) not null,
    `typeId` int(11) not null,
    PRIMARY KEY  (`id`),
    UNIQUE KEY `uqMealType` (`mealId`,`typeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;