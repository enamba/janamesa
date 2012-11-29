CREATE TABLE `upselling_storage` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `product` VARCHAR(255) NOT NULL,
    `count` INT(10) DEFAULT 0,
    `orderedAt` TIMESTAMP DEFAULT '0000-00-00 00:00:0000',
    `createdBy` INT(10) NOT NULL,
    `producer` VARCHAR(255) DEFAULT 'unknown',
    `costProduct` INT(10) DEFAULT 0,
    `costDelivery` INT(10) DEFAULT 0,
    `deliverEstimation` TIMESTAMP DEFAULT '0000-00-00 00:00:0000',
    `delivered` TIMESTAMP DEFAULT '0000-00-00 00:00:0000',
    `comment` TEXT,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `fkCreateBy` (`createdBy`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;