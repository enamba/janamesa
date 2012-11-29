-- how many first orders have been made each month by UNREGISTERED users?
-- @since 07.10.2010
-- @author mlaug, felix
DROP TABLE IF EXISTS `view_orders_first_unregistered_per_month`;
DROP VIEW IF EXISTS `view_orders_first_unregistered_per_month`;
CREATE VIEW `view_orders_first_unregistered_per_month` AS

    SELECT
        COUNT(*) `count`,
        MONTH(firstTime) `month`,
        YEAR(firstTime) `year`
    FROM `view_customer_unregistered_first_order`
    GROUP BY `month`, `year`
    ORDER BY `year`, `month`;


-- how many first orders have been made each month by REGISTERED users?
-- @since 07.10.2010
-- @author mlaug, felix
DROP TABLE IF EXISTS `view_orders_first_registered_per_month`;
DROP VIEW IF EXISTS `view_orders_first_registered_per_month`;
CREATE VIEW `view_orders_first_registered_per_month` AS

    SELECT
        COUNT(*) `count`,
        MONTH(firstTime) `month`,
        YEAR(firstTime) `year`
    FROM `view_customer_registered_first_order`
    GROUP BY `month`, `year`
    ORDER BY `year`, `month`;

-- percent rated orders
-- @since 25.10.2010
-- @author vpriem
DROP TABLE IF EXISTS `view_orders_percent_rated`;
DROP VIEW IF EXISTS `view_orders_percent_rated`;
CREATE VIEW `view_orders_percent_rated` AS

    SELECT COUNT(rr.id) / COUNT(o.id) * 100 `percent`
    FROM `orders` o
    LEFT JOIN `restaurant_ratings` rr ON o.id = rr.orderId
    AND o.state > 0;