-- overview of all user statistics currently available
-- @since 07.10.2010

-- REGISTERED customers
-- @author vpriem
-- @since 25.10.2010
DROP TABLE IF EXISTS `view_customers_registered`;
DROP VIEW IF EXISTS `view_customers_registered`;
CREATE VIEW `view_customers_registered` AS
    SELECT oc.email, IF(c2.id IS NOT NULL,c2.id,c1.id) AS customerId, 
           IF(c2.id IS NOT NULL,c2.created,c1.created) AS registered_on, 
           COUNT(*) `count`, 
           TIMESTAMPDIFF(MONTH, IF(c2.id IS NOT NULL,c2.created,c1.created), NOW()) as `ageInMonth`,
           SUM(COALESCE(o.customerId,0))/IF(c2.id IS NOT NULL,c2.id,c1.id) as countRegistered,
           SUM(o.total+o.serviceDeliverCost+o.courierCost) `sum_bucket_value`,
           AVG(o.total+o.serviceDeliverCost+o.courierCost) `average_bucket_value`,
           SUM(vs.commission) as `sum_commission`,
           AVG(r.komm) as `avg_comm_perc`
    FROM `orders_customer` oc
        INNER JOIN `orders_location` ol ON ol.orderId = oc.orderId
        INNER JOIN `orders` o ON o.id = oc.orderId
        INNER JOIN `restaurants` r on r.id = o.restaurantId
        INNER JOIN `view_sales` vs ON o.id = vs.orderId
        LEFT JOIN `customers` c1 ON (c1.email = oc.email)
        LEFT JOIN `customers` c2 ON (c2.id=o.customerId)
    WHERE
        o.kind = 'priv' 
        AND o.mode = 'rest'
        AND o.state > 0
        AND (c1.id IS NOT NULL OR c2.id IS NOT NULL)
    GROUP BY oc.email;


-- REGISTERED company customers
-- @author vpriem
-- @since 01.07.2011
DROP TABLE IF EXISTS `view_company_customers_registered`;
DROP VIEW IF EXISTS `view_company_customers_registered`;
CREATE VIEW `view_company_customers_registered` AS
    SELECT oc.email, 
           IF(c.id is not null,c.created,min(o.time)) as registered_on, 
           TIMESTAMPDIFF(MONTH, IF(c.id is not null,c.created,min(o.time)), NOW()) as `ageInMonth`,
           COUNT(*) `count`, 
           o.mode,
           SUM(o.total+o.serviceDeliverCost+o.courierCost) `sum_bucket_value`,
           AVG(o.total+o.serviceDeliverCost+o.courierCost) `average_bucket_value`,
           SUM(vs.commission) as `sum_commission`,
           AVG(r.komm) as `avg_comm_perc`
    FROM `orders_customer` oc
        INNER JOIN `orders_location` ol ON ol.orderId = oc.orderId
        INNER JOIN `orders` o on o.id = oc.orderId
        INNER JOIN `restaurants` r on r.id = o.restaurantId
        INNER JOIN `view_sales` vs ON o.id = vs.orderId
        LEFT JOIN `customers` c on c.id = o.customerId
    WHERE
        o.state > 0 
        AND (o.mode IN ('cater','great','fruit') OR o.kind <> 'priv')
    GROUP BY oc.email, o.mode;


-- how many REGISTERED customers do we have?
-- should have a registered email in customers
-- should NOT have provided a company name 2294
DROP TABLE IF EXISTS `view_customers_count_registered`;
DROP VIEW IF EXISTS `view_customers_count_registered`;
CREATE VIEW `view_customers_count_registered` AS

    SELECT COUNT(*) `count`
    FROM `view_customers_registered`;

-- how many REGISTERED company customers do we have?
DROP TABLE IF EXISTS `view_company_customers_count_registered`;
DROP VIEW IF EXISTS `view_company_customers_count_registered`;
CREATE VIEW `view_company_customers_count_registered` AS

    SELECT COUNT(*) `count`
    FROM `view_company_customers_registered`;


-- UNREGISTERED customer
-- @author vpriem
-- @since 25.10.2010
DROP TABLE IF EXISTS `view_customers_notregistered`;
DROP VIEW IF EXISTS `view_customers_notregistered`;
CREATE VIEW `view_customers_notregistered` AS

    SELECT oc.email, oc.prename, oc.name, MIN(o.time) as registered_on, 
           TIMESTAMPDIFF(MONTH, MIN(o.time), NOW()) as `ageInMonth`,
           COUNT(*) `count`, 
           SUM(o.total+o.serviceDeliverCost+o.courierCost) `sum_bucket_value`,
           AVG(o.total+o.serviceDeliverCost+o.courierCost) `average_bucket_value`,
           SUM(vs.commission) as `sum_commission`,
           AVG(r.komm) as `avg_comm_perc`
    FROM `orders_customer` oc
    INNER JOIN `orders_location` ol ON ol.orderId = oc.orderId
    INNER JOIN `orders` o on o.id = oc.orderId
    INNER JOIN `restaurants` r on r.id = o.restaurantId
    INNER JOIN `view_sales` vs ON o.id = vs.orderId
    LEFT JOIN `customers` c on c.email = oc.email
    WHERE
        o.kind = 'priv'
        AND o.customerId IS NULL
        AND o.mode = 'rest'
        AND c.id IS NULL
        AND o.state > 0
    GROUP BY oc.email;

-- how many UNREGISTERED customer do we have?
-- should NOT have a registered email in custoemrs
-- should have place a private order (what else?)
-- should NOT have provided a company name
DROP TABLE IF EXISTS `view_customers_count_notregistered`;
DROP VIEW IF EXISTS `view_customers_count_notregistered`;
CREATE VIEW `view_customers_count_notregistered` AS

    SELECT COUNT(*) `count`
    FROM `view_customers_notregistered`;

-- the first order made by a COMPANY customers
-- @since 23.12.2010
-- @author alex
-- period is the count of month from the first order since now
DROP TABLE IF EXISTS `view_customer_company_first_order`;
DROP VIEW IF EXISTS `view_customer_company_first_order`;
CREATE VIEW `view_customer_company_first_order` AS
    SELECT
        ocn.email,
        MIN(o.time) `firstTime`,
        MAX(o.time) `lastTime`,
        COUNT(*) `count`,

        PERIOD_DIFF(
            DATE_FORMAT(NOW(), '%Y%m'),
            DATE_FORMAT(MIN(o.time), '%Y%m')
        ) `period`,

        COUNT(*) / PERIOD_DIFF(
            DATE_FORMAT(NOW(), '%Y%m'),
            DATE_FORMAT(MIN(o.time), '%Y%m')
        ) `quotient`,

        MIN(o.time) `time`,
        ol.plz `plz`,
        city.city `city`

    FROM `orders` o
    INNER JOIN `orders_customer` ocn ON ocn.orderId = o.id
    INNER JOIN `orders_location` ol ON ol.orderId = o.id
    LEFT JOIN `customer_company` cc on o.customerId = cc.customerId
    INNER JOIN `city` ON city.plz = ol.plz
    WHERE `email` IN (SELECT `email` FROM `customers`)
        AND o.state > 0
        AND o.kind = 'priv'
        AND cc.companyId IS NOT NULL
    GROUP BY ocn.email;

-- the first order made by a REGISTERED customer
-- @since 07.10.2010
-- @author mlaug, felix
-- period is the count of month from the first order since now
DROP TABLE IF EXISTS `view_customer_registered_first_order`;
DROP VIEW IF EXISTS `view_customer_registered_first_order`;
CREATE VIEW `view_customer_registered_first_order` AS
    SELECT
        ocn.email,
        MIN(o.time) `firstTime`,
        MAX(o.time) `lastTime`,
        COUNT(*) `count`,

        PERIOD_DIFF(
            DATE_FORMAT(NOW(), '%Y%m'),
            DATE_FORMAT(MIN(o.time), '%Y%m')
        ) `period`,

        COUNT(*) / PERIOD_DIFF(
            DATE_FORMAT(NOW(), '%Y%m'),
            DATE_FORMAT(MIN(o.time), '%Y%m')
        ) `quotient`,
     
        MIN(o.time) `time`,
        ol.plz `plz`,
        city.city `city`

    FROM `orders` o
    INNER JOIN `orders_customer` ocn ON ocn.orderId = o.id
    INNER JOIN `orders_location` ol ON ol.orderId = o.id
    INNER JOIN `city` ON city.plz = ol.plz
    WHERE `email` IN (SELECT `email` FROM `customers`)
        AND o.state > 0
        AND o.kind = 'priv'
    GROUP BY ocn.email;

-- the first order made by an UNREGISTERED customer
-- @since 07.10.2010
-- @author mlaug, felix
-- period is the count of month from the first order since now
DROP TABLE IF EXISTS `view_customer_unregistered_first_order`;
DROP VIEW IF EXISTS `view_customer_unregistered_first_order`;
CREATE VIEW `view_customer_unregistered_first_order` AS

    SELECT
        `email`,
        MIN(o.time) `firstTime`,
        MAX(o.time) `lastTime`,
        COUNT(*) `count`,

        PERIOD_DIFF(
            DATE_FORMAT(NOW(), '%Y%m'),
            DATE_FORMAT(MIN(o.time), '%Y%m')
        ) `period`,

        COUNT(*) / PERIOD_DIFF(
            DATE_FORMAT(NOW(), '%Y%m'),
            DATE_FORMAT(MIN(o.time), '%Y%m')
        ) `quotient`,

        MIN(o.time) `time`,
        ol.plz `plz`,
        city.city `city`

    FROM `orders` o
    INNER JOIN `orders_customer` ocn ON ocn.orderId = o.id
    INNER JOIN `orders_location` ol ON ol.orderId = o.id
    INNER JOIN `city` ON city.plz = ol.plz
    WHERE `email` NOT IN (SELECT `email` FROM `customers`)
        AND o.state > 0
        AND o.kind = 'priv'
    GROUP BY ocn.email;

-- the first order made by a customer
-- @since 27.04.2011
-- @author vpriem
DROP TABLE IF EXISTS `view_customer_first_order`;
DROP VIEW IF EXISTS `view_customer_first_order`;
CREATE VIEW `view_customer_first_order` AS

    SELECT
        MIN(o.id) `orderId`,
        ocn.email
    FROM `orders` o
    INNER JOIN `orders_customer` ocn ON ocn.orderId = o.id
    INNER JOIN `orders_location` ol ON ol.orderId = o.id
    WHERE o.state > 0
        AND o.kind = 'priv'
    GROUP BY ocn.email;

-- count of orders each REGISTERED user made per month
-- @since 07.10.2010
-- @author mlaug, felix
DROP TABLE IF EXISTS `view_customer_registered_count_orders_per_month`;
DROP VIEW IF EXISTS `view_customer_registered_count_orders_per_month`;
CREATE VIEW `view_customer_registered_count_orders_per_month` AS

    SELECT
        `email`,
        MONTH(time) `month`,
        YEAR(time) `year`,
        COUNT(o.id) `count`,
        SUM(
            total +
            serviceDeliverCost +
            courierCost +
            charge
        ) `amount`,
        AVG(
            total +
            serviceDeliverCost +
            courierCost +
            charge
        ) `average`
    FROM `orders` o
    INNER JOIN `orders_customer` ocn on ocn.orderId = o.id
    WHERE (o.customerId IS NOT NULL AND o.customerId > 0)
        AND o.state > 0
        AND o.kind = 'priv'
    GROUP BY `month`, `year`, `email`;

-- count of orders each UNREGISTERED user made per month
-- @since 07.10.2010
-- @author mlaug, felix
DROP TABLE IF EXISTS `view_customer_unregistered_count_orders_per_month`;
DROP VIEW IF EXISTS `view_customer_unregistered_count_orders_per_month`;
CREATE VIEW `view_customer_unregistered_count_orders_per_month` AS

    SELECT
        `email`,
        MONTH(time) `month`,
        YEAR(time) `year`,
        COUNT(o.id) `count`,
        SUM(
            total +
            serviceDeliverCost +
            courierCost +
            charge
        ) `amount`,
        AVG(
            total +
            serviceDeliverCost +
            courierCost +
            charge
        ) `average`
    FROM `orders` o
    INNER JOIN `orders_customer` ocn on ocn.orderId = o.id
    WHERE (o.customerId IS NULL OR o.customerId = 0)
        AND o.state > 0
        AND o.kind = 'priv'
    GROUP BY `month`, `year`, `email`;


-- count of orders each company user made per month
-- @since 20.07.2011
-- @author alex
DROP TABLE IF EXISTS `view_company_customer_count_orders_per_month`;
DROP VIEW IF EXISTS `view_company_customer_count_orders_per_month`;
CREATE VIEW `view_company_customer_count_orders_per_month` AS
    SELECT
        `email`,
        MONTH(time) `month`,
        YEAR(time) `year`,
        COUNT(o.id) `count`,
        o.mode,
        SUM(
            total +
            serviceDeliverCost +
            courierCost +
            charge -
            courierDiscount
        ) `amount`,
        AVG(
            total +
            serviceDeliverCost +
            courierCost +
            charge -
            courierDiscount
        ) `average`
    FROM `orders` o
    INNER JOIN `orders_customer` ocn on ocn.orderId = o.id
    INNER JOIN `orders_location` ol ON ol.orderId = o.id
    LEFT JOIN `customer_company` cc on o.customerId = cc.customerId
    WHERE `email` IN (SELECT `email` FROM `customers`)
        AND o.state > 0
        AND o.kind = 'priv'
        AND (LENGTH(ol.companyName)>0 OR cc.companyId IS NOT NULL)
    GROUP BY `month`, `year`, `email`;


-- how many REGISTERED users returned after their first order
-- @since 07.10.2010
-- @author mlaug, felix
DROP TABLE IF EXISTS `view_customer_registered_ordered_again_after_first_order`;
DROP VIEW IF EXISTS `view_customer_registered_ordered_again_after_first_order`;
CREATE VIEW `view_customer_registered_ordered_again_after_first_order` AS

    SELECT
        MONTH(firstTime) `firstMonth`,
        YEAR(firstTime) `firstYear`,
        COUNT(*) /
            (SELECT
                COUNT(*)
                FROM view_customer_registered_first_order b
                WHERE
                    MONTH(a.firstTime) = MONTH(b.firstTime)
                    AND YEAR(a.firstTime) = YEAR(b.firstTime)) * 100 AS `count`
        FROM `view_customer_registered_first_order` a
        WHERE
            lastTime != firstTime
        GROUP BY
            MONTH(firstTime),
            YEAR(firstTime);

-- how many UNREGISTERED users returned after their first order
-- @since 07.10.2010
-- @author mlaug, felix
DROP TABLE IF EXISTS `view_customer_unregistered_ordered_again_after_first_order`;
DROP VIEW IF EXISTS `view_customer_unregistered_ordered_again_after_first_order`;
CREATE VIEW `view_customer_unregistered_ordered_again_after_first_order` AS

    SELECT
        MONTH(firstTime) `firstMonth`,
        YEAR(firstTime) `firstYear`,
        COUNT(*) /
            (SELECT
                COUNT(*)
                FROM view_customer_unregistered_first_order b
                WHERE
                    MONTH(a.firstTime) = MONTH(b.firstTime)
                    AND YEAR(a.firstTime) = YEAR(b.firstTime))*100 AS count
        FROM `view_customer_unregistered_first_order` a
        WHERE
            lastTime != firstTime
        GROUP BY
            MONTH(firstTime),
            YEAR(firstTime);

-- get the top 10 customers
-- @since 22.10.2010
-- @author vpriem
DROP TABLE IF EXISTS `view_customer_top_10`;
DROP VIEW IF EXISTS `view_customer_top_10`;
CREATE VIEW `view_customer_top_10` AS

    SELECT c.id, CONCAT(c.name, ' ', c.prename) `customer`, COUNT(o.id) `count`
    FROM `orders` o
    JOIN `customers` c ON o.customerId = c.id
    WHERE o.state > 0
        AND c.deleted = 0
        AND c.id NOT IN (SELECT `customerId` FROM `customer_company`)
    GROUP BY c.id
    ORDER BY `count` DESC
    LIMIT 10;

-- get the top 10 customers
-- @since 22.10.2010
-- @author vpriem
DROP TABLE IF EXISTS `view_customer_top_10_amount`;
DROP VIEW IF EXISTS `view_customer_top_10_amount`;
CREATE VIEW `view_customer_top_10_amount` AS

    SELECT c.id, CONCAT(c.name, ' ', c.prename) `customer`, SUM(o.total + COALESCE(o.serviceDeliverCost, 0) + COALESCE(o.courierCost, 0)) `total`
    FROM `orders` o
    JOIN `customers` c ON o.customerId = c.id
    WHERE o.state > 0
        AND c.deleted = 0
        AND c.id NOT IN (SELECT `customerId` FROM `customer_company`)
    GROUP BY c.id
    ORDER BY `total` DESC
    LIMIT 10;

-- get the top 10 customers
-- @since 22.10.2010
-- @author vpriem
DROP TABLE IF EXISTS `view_customer_top_10_amount_average`;
DROP VIEW IF EXISTS `view_customer_top_10_amount_average`;
CREATE VIEW `view_customer_top_10_amount_average` AS

    SELECT c.id, CONCAT(c.name, ' ', c.prename) `customer`, AVG(o.total + COALESCE(o.serviceDeliverCost, 0) + COALESCE(o.courierCost, 0)) `average`
    FROM `orders` o
    JOIN `customers` c ON o.customerId = c.id
    WHERE o.state > 0
        AND c.deleted = 0
        AND c.id NOT IN (SELECT `customerId` FROM `customer_company`)
    GROUP BY c.id
    ORDER BY `average` DESC
    LIMIT 10;

-- get the firma customers
-- @since 25.10.2010
-- @author vpriem
-- TODO: is it right?
DROP TABLE IF EXISTS `view_customer_companies`;
DROP VIEW IF EXISTS `view_customer_companies`;
CREATE VIEW `view_customer_companies` AS

    SELECT COUNT(DISTINCT cc.customerId)
    FROM `orders` o
    INNER JOIN `customer_company` cc ON o.customerId = cc.customerId 
    INNER JOIN `companys` c ON  cc.companyId = c.id
        AND c.deleted = 0
    WHERE o.state > 0;


-- KPI SHEET JOERG, get all registered customers sorted by year and month
-- @since 18.08.2011
-- @author mlaug
DROP TABLE IF EXISTS `view_customer_registered_by_month`;
DROP VIEW IF EXISTS `view_customer_registered_by_month`;
CREATE VIEW `view_customer_registered_by_month` AS
    
    SELECT 
        count(*) as countUsers, sum(count) as countOrders, 
        SUM(sum_bucket_value) as sum_bucket_value,
        AVG(average_bucket_value) as average_bucket_value, 
        MONTH(registered_on) as month, YEAR(registered_on) as year,
        SUM(sum_commission) as sum_commission,
        AVG(avg_comm_perc) as avg_comm_perc
    FROM view_customers_registered 
    GROUP BY MONTH(registered_on),
             YEAR(registered_on);

-- KPI SHEET JOERG, get all unregistered customers sorted by year and month
-- @since 18.08.2011
-- @author mlaug
DROP TABLE IF EXISTS `view_customer_notregistered_by_month`;
DROP VIEW IF EXISTS `view_customer_notregistered_by_month`;
CREATE VIEW `view_customer_notregistered_by_month` AS
    
    SELECT 
        count(*) as countUsers, sum(count) as countOrders, 
        SUM(sum_bucket_value) as sum_bucket_value,
        AVG(average_bucket_value) as average_bucket_value, 
        MONTH(registered_on) as month, YEAR(registered_on) as year,
        SUM(sum_commission) as sum_commission,
        AVG(avg_comm_perc) as avg_comm_perc
    FROM view_customers_notregistered 
    GROUP BY MONTH(registered_on),
             YEAR(registered_on);


-- KPI SHEET JOERG, get all unregistered customers sorted by year and month
-- @since 18.08.2011
-- @author mlaug
DROP TABLE IF EXISTS `view_company_customer_registered_by_month`;
DROP VIEW IF EXISTS `view_company_customer_registered_by_month`;
CREATE VIEW `view_company_customer_registered_by_month` AS
    
    SELECT 
        count(*) as countUsers, sum(count) as countOrders, 
        mode,
        SUM(sum_bucket_value) as sum_bucket_value,
        AVG(average_bucket_value) as average_bucket_value, 
        MONTH(registered_on) as month, YEAR(registered_on) as year,
        SUM(sum_commission) as sum_commission,
        AVG(avg_comm_perc) as avg_comm_perc
    FROM view_company_customers_registered
    GROUP BY MONTH(registered_on),
             YEAR(registered_on),
             mode;

-- KPI SHEET Order Values registered Customers
-- @since 22.08.2011
-- @author Daniel Hahn <hahn@lieferando.de>
DROP TABLE IF EXISTS `view_orders_customer_registered_by_month`;
DROP VIEW IF EXISTS `view_orders_customer_registered_by_month`;
CREATE VIEW `view_orders_customer_registered_by_month` AS

    SELECT    
        COUNT(*) `countOrders`,
        SUM(o.total + o.serviceDeliverCost + o.courierCost + o.charge) `sum_bucket_value`,
        AVG(o.total + o.serviceDeliverCost + o.courierCost + o.charge) `average_bucket_value`,
        MONTH(o.time) as month, 
        YEAR(o.time) as year,
        SUM(vs.commission) as `sum_commission`,
        AVG(r.komm) as `avg_comm_perc`	
    FROM `orders_customer` oc
        INNER JOIN `orders_location` ol ON ol.orderId = oc.orderId
        INNER JOIN `orders` o ON o.id = oc.orderId
        INNER JOIN `restaurants` r on r.id = o.restaurantId
        INNER JOIN `view_sales` vs ON o.id = vs.orderId
        LEFT JOIN `customers` c1 ON (c1.email = oc.email AND c1.deleted = 0)
        LEFT JOIN `customers` c2 ON c2.id = o.customerId
    WHERE 
        o.kind = 'priv' 
        AND o.mode = 'rest'
        AND o.state > 0
        AND (c1.id IS NOT NULL OR c2.id IS NOT NULL)
    GROUP 
        BY MONTH(o.time), 
           YEAR(o.time);


-- KPI SHEET Order Values unregistered Customers
-- @since 22.08.2011
-- @author Daniel Hahn <hahn@lieferando.de>
DROP TABLE IF EXISTS `view_orders_customer_unregistered_by_month`;
DROP VIEW IF EXISTS `view_orders_customer_unregistered_by_month`;
CREATE VIEW `view_orders_customer_unregistered_by_month` AS

    SELECT    
        COUNT(*) `countOrders`, 
        SUM(o.total + o.serviceDeliverCost + o.courierCost + o.charge) `sum_bucket_value`,
        AVG(o.total + o.serviceDeliverCost + o.courierCost + o.charge) `average_bucket_value`,
        MONTH(o.time) as month, 
        YEAR(o.time) as year,
        SUM(vs.commission) as `sum_commission`,
        AVG(r.komm) as `avg_comm_perc`
    FROM `orders_customer` oc
        INNER JOIN `orders_location` ol ON ol.orderId = oc.orderId
        INNER JOIN `orders` o ON o.id = oc.orderId
        INNER JOIN `restaurants` r on r.id = o.restaurantId
        INNER JOIN `view_sales` vs ON o.id = vs.orderId
        LEFT JOIN `customers` c ON (c.email = oc.email AND c.deleted = 0)
    WHERE  
        o.kind = 'priv'
        AND o.customerId IS NULL
        AND o.mode = 'rest'
        AND c.id IS NULL
        AND o.state > 0
        GROUP BY MONTH(o.time), YEAR(o.time);


-- KPI SHEET Order Values unregistered Customers
-- @since 22.08.2011
-- @author Daniel Hahn <hahn@lieferando.de>
DROP TABLE IF EXISTS `view_orders_company_customer_registered_by_month`;
DROP VIEW IF EXISTS `view_orders_company_customer_registered_by_month`;
CREATE VIEW `view_orders_company_customer_registered_by_month` AS

    SELECT    
        COUNT(*) `countOrders`,
        o.mode,
        SUM(o.total + o.serviceDeliverCost + o.courierCost + o.charge) `sum_bucket_value`,
        AVG(o.total + o.serviceDeliverCost + o.courierCost + o.charge) `average_bucket_value`,
        MONTH(o.time) as month, +
        YEAR(o.time) as year,
        SUM(vs.commission) as `sum_commission`,
        AVG(r.komm) as `avg_comm_perc`
    FROM `orders_customer` oc
        INNER JOIN `orders_location` ol ON ol.orderId = oc.orderId
        INNER JOIN `orders` o on o.id = oc.orderId
        INNER JOIN `restaurants` r on r.id = o.restaurantId
        INNER JOIN `view_sales` vs ON o.id = vs.orderId
    WHERE 
        o.state > 0 
        AND (o.mode IN ('cater', 'great', 'fruit') OR o.kind <> 'priv')
    GROUP 
        BY MONTH(o.time), YEAR(o.time), o.mode;
