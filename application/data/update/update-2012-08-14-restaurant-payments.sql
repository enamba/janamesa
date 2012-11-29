-- @author Vincent Priem <priem@lieferando.de>
-- @since 12.08.2012

ALTER TABLE `restaurant_payments` 
    DROP INDEX `restaurantId`, 
    ADD INDEX `fkRestaurant` (`restaurantId`), 
    ADD UNIQUE INDEX `uqRestaurantPayment` (`restaurantId`, `payment`) ;

INSERT INTO `restaurant_payments` (`restaurantId`, `payment`, `status`)
    SELECT `id`, 'ticketRestaurant', 1 
    FROM `restaurants`
    WHERE `ticketrestaurant` = 1;

ALTER TABLE `restaurants` 
    DROP COLUMN `ticketrestaurant`;

-- for DE
INSERT IGNORE INTO `restaurant_payments` (`restaurantId`, `payment`, `status`)
    SELECT `id`, 'ec', 1 
    FROM `restaurants`
    WHERE `id` IN (16259, 16260, 16265, 16266, 16268, 16267, 16269, 16272, 16275);