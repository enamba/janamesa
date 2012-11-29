
-- @author alex
-- @since 19.04.2011
-- unique index only on plz-city

ALTER TABLE city DROP index plz;
CREATE UNIQUE index uqPlzCity ON city (`plz`, `city`);

-- paid info for salespersons contracts
ALTER TABLE `salesperson_restaurant` ADD COLUMN `paid` TINYINT(4) NOT NULL DEFAULT 0 AFTER `restaurantId`;