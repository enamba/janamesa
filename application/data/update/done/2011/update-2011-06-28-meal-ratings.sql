CREATE TABLE `meal_ratings` ( 
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `mealId` INT NOT NULL,
    `orderId` INT NOT NULL,
    `rating` INT DEFAULT 5,
    `comment` TEXT,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    INDEX `fkMealId` (`mealId`),
    INDEX `fkOrderId` (`orderId`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;