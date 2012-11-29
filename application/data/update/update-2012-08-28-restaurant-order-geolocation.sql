CREATE  TABLE IF NOT EXISTS `order_geolocation_status_log` (
  `id` INT(10) NOT NULL AUTO_INCREMENT ,
  `orderId` INT(10) NOT NULL ,
  `statusId` INT(10) NOT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) ,
  INDEX `idxOrderId` (`orderId` ASC) ,
  INDEX `idxStatusId` (`statusId` ASC))
ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `order_geolocation_position_log` (
  `id` INT(10) NOT NULL AUTO_INCREMENT ,
  `orderId` INT(10) NOT NULL ,
  `lng` FLOAT(10,7) NULL ,
  `lat` FLOAT(10,7) NULL ,
  `alt` FLOAT(10,7) NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) ,
  INDEX `idxOrderId` (`orderId` ASC) )
ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `restaurant_partner_drivers` (
    `id` INT(10) NOT NULL AUTO_INCREMENT ,
    `name` VARCHAR(100) NOT NULL,
    `restaurantId` INT(10) NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) ,
    INDEX `idxRestaurantId` (`restaurantId` ASC)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `restaurant_partner_drivers_orders` (
    `id` INT(10) NOT NULL AUTO_INCREMENT ,
    `orderId` INT(10) NOT NULL ,
    `subAccountId` INT(10) NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) ,
    UNIQUE `uqOrderId` (`orderId`), 
    INDEX `idxOrderId` (`orderId` ASC) ,
    INDEX `idxAccountId` (`subAccountId` ASC)
)ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;