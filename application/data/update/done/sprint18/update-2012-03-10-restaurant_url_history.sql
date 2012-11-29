-- URL History for restaurants

DROP TABLE IF EXISTS `restaurant_url_history`;
create table `restaurant_url_history` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `restaurantId`   INT NOT NULL,
    `url` VARCHAR(255)  NOT NULL,
    `mode` VARCHAR(10)  NOT NULL,
    UNIQUE `uqUrl` (`url`, `mode`),
    INDEX `fkRestaurant` (`restaurantId`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
