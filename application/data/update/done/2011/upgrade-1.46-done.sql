/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.46
 *
 * @author mlaug
 */

CREATE TABLE `canteen_order`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `sent` timestamp default current_timestamp,
    `state` int(11) default 0
) ENGINE = InnoDB;

CREATE TABLE `canteen_order_consolidate`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `canteenOrderId` int(11) not null,
    `orderId` int(11) not null
) ENGINE = InnoDB;
