-- @author vpriem
-- @since 09.11.2010
DROP TABLE IF EXISTS `view_sales`;
DROP VIEW IF EXISTS `view_sales`;
CREATE VIEW `view_sales` AS
(
    SELECT
        o.id `orderId`, o.restaurantId, o.customerId, ol.plz, o.companyId, o.time, o.deliverTime, o.mode, o.kind, o.payment, 
        (o.discountAmount + o.courierDiscount) `discountAmount`,
        o.total + COALESCE(o.serviceDeliverCost, 0) + COALESCE(o.courierCost, 0) + COALESCE(o.charge, 0) `sales`,
        ((o.total + o.serviceDeliverCost * r.billDeliverCost) * r.komm / 100) + r.fee + (SUM(obm.count) * r.item) `commission`,
        o.billRest,o.billCompany,o.billCourier
    FROM `orders` o
    LEFT JOIN `restaurants` r ON o.restaurantId = r.id
    INNER JOIN `orders_bucket_meals` obm ON o.id = obm.orderId
    INNER JOIN `orders_location` ol ON ol.orderId = o.id
    WHERE o.state > 0
    GROUP BY o.id
) UNION (
    SELECT
        0, `restaurantId`, 0, c.plz, `companyId`, `timeFrom`, `timeUntil`, 'billasset', '' `kind`, 'bill', 0,
        `total` * (`mwst` + 100) / 100 `sales`,
        `total` * (`mwst` + 100) / 100 * `fee` / 100 `commission`,
        billRest,billCompany,billCourier
    FROM `billing_assets` ba
    INNER JOIN companys c on c.id=ba.companyId
);

-- get sales
-- @since 25.10.2010
-- @author vpriem
DROP TABLE IF EXISTS `view_commission`;
DROP VIEW IF EXISTS `view_commission`;
CREATE VIEW `view_commission` AS

    SELECT
        o.total + COALESCE(o.serviceDeliverCost, 0) + COALESCE(o.courierCost, 0) `umsatz`,
        ((o.total + o.serviceDeliverCost * r.billDeliverCost) * r.komm / 100) + r.fee + (SUM(obm.count) * r.item) `provision`,
        o.time
    FROM `orders` o
    LEFT JOIN `restaurants` r ON o.restaurantId = r.id
    INNER JOIN `orders_bucket_meals` obm ON o.id = obm.orderId
    WHERE o.state > 0
    GROUP BY o.id;

-- umsätze pro woche
DROP TABLE IF EXISTS `view_sales_per_week`;
DROP VIEW IF EXISTS `view_sales_per_week`;
CREATE VIEW `view_sales_per_month` AS

    SELECT WEEK(time) AS `week`, YEAR(time) AS `year`, SUM(sales) AS `amount`
    FROM `view_sales`
    GROUP BY WEEK(time), YEAR(time)
    ORDER BY WEEK(time) DESC, YEAR(time) DESC;

-- umsätze pro monat
DROP TABLE IF EXISTS `view_sales_per_month`;
DROP VIEW IF EXISTS `view_sales_per_month`;
CREATE VIEW `view_sales_per_week` AS

    SELECT MONTH(time) AS `month`, YEAR(time) AS `year`, SUM(sales) AS `amount`
    FROM `view_sales` o
    GROUP BY MONTH(time), YEAR(time)
    ORDER BY MONTH(time) DESC, YEAR(time) DESC;

-- umsatz letzte 7 tage
-- @since 03.11.2010
-- @author vpriem
DROP TABLE IF EXISTS `view_sales_this_week`;
DROP VIEW IF EXISTS `view_sales_this_week`;
CREATE VIEW `view_sales_this_week` AS

    SELECT DAY(o.time) `day`, SUM(o.total + o.serviceDeliverCost + o.courierCost + o.charge) / 100 `amount`
    FROM `orders` o
    WHERE o.state > 0
        AND DATEDIFF(NOW(), o.time) < 8
    GROUP BY DATE(o.time)
    ORDER BY o.time;

-- @author vpriem
-- @since 09.11.2010
DROP TABLE IF EXISTS `view_sales_storno`;
DROP VIEW IF EXISTS `view_sales_storno`;
CREATE VIEW `view_sales_storno` AS
(
    SELECT
        o.id `orderId`, o.restaurantId, o.customerId, o.companyId, o.time, o.deliverTime, o.mode, o.kind, o.payment, o.discountAmount,
        o.total + COALESCE(o.serviceDeliverCost, 0) + COALESCE(o.courierCost, 0) `sales`,
        ((o.total + o.serviceDeliverCost * r.billDeliverCost) * r.komm / 100) + r.fee + (SUM(obm.count) * r.item) `commission`,
        o.billRest,o.billCompany,o.billCourier
    FROM `orders` o
    INNER JOIN `restaurants` r ON o.restaurantId = r.id
    INNER JOIN `orders_bucket_meals` obm ON o.id = obm.orderId
    WHERE o.state = -2
    GROUP BY o.id
);