DROP TABLE IF EXISTS `restaurant_notepad_ticket`;

CREATE TABLE `restaurant_notepad_ticket` (
    `id` INT NOT NULL  PRIMARY KEY AUTO_INCREMENT,
    `restaurantId` INT NULL DEFAULT NULL,
    `adminId` INT NULL DEFAULT NULL,
    `comment` VARCHAR(500) NOT NULL,
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `allwaysCall` TINYINT NOT NULL DEFAULT 0,
    INDEX `restIdIdx` (`restaurantId`) 
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
