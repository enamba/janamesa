
-- @author alex
-- @since 13.10.11

DROP TABLE IF EXISTS `meal_categories_parents`;

CREATE TABLE `meal_categories_parents` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `name` VARCHAR(255) NOT NULL,
    `type` VARCHAR(255) NOT NULL,
    `created` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
UNIQUE (
    `name`
)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;