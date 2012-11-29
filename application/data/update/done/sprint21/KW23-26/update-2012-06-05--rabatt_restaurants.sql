CREATE TABLE `rabatt_restaurant` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`rabattId` INT NOT NULL ,
`restaurantId` INT NOT NULL ,
`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
`updated` TIMESTAMP NULL DEFAULT NULL 
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `rabatt_restaurant` ADD UNIQUE `uqRabattRestaurant` ( `rabattId` , `restaurantId` );