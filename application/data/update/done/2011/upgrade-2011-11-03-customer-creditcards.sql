
-- @author vpriem
-- @since 03.11.11
-- @description save customer credit cards

CREATE TABLE `customer_creditcards` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `customerId` INT UNSIGNED NOT NULL,
    `uniqueId` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `brand` VARCHAR(255) NOT NULL,
    `number` VARCHAR(255) NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idxCustomer` (`customerId`),
    INDEX `idxUnique` (`uniqueId`)
) ENGINE = InnoDB;
