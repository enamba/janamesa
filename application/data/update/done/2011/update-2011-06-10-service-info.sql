CREATE TABLE `billing_balance` ( 
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `restaurantId` INT DEFAULT NULL,
    `companyId` INT DEFAULT NULL,
    `billingId` INT DEFAULT NULL,
    `amount` INT DEFAULT 0,
    `comment` TEXT,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    INDEX `fkRestaurantId` (`restaurantId`),
    INDEX `fkCompanyId` (`companyId`),
    INDEX `fkBillingId` (`billingId`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;