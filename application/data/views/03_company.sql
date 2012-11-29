-- overview of all company statistics currently available
-- @since 07.10.2010

-- get the count of employes for each company
-- with more than 0 employees
-- @since 07.10.2010
-- @author mlaug
DROP TABLE IF EXISTS `view_company_count_employees`;
DROP VIEW IF EXISTS `view_company_count_employees`;
CREATE VIEW `view_company_count_employees` AS

    SELECT c.id, c.name `company`, COUNT(*) `employees`, o.city `city`, c.plz, c.created `time`
    FROM `companys` c
    INNER JOIN `customer_company` cc ON c.id = cc.companyId
    INNER JOIN `city` o ON o.plz = c.plz
    GROUP BY cc.companyId
    HAVING `employees` > 0;

-- count of orders per month for each company
-- with a total of budget use
-- @since 07.10.2010
-- @author mlaug, felix
DROP TABLE IF EXISTS `view_company_count_orders_per_month`;
DROP VIEW IF EXISTS `view_company_count_orders_per_month`;
CREATE VIEW `view_company_count_orders_per_month` AS

    SELECT
        c.id, c.name `company`, COUNT(o.id) `orders`, MONTH(o.time) `month`, YEAR(o.time) `year`, city.city `city`, c.plz,
        AVG(amount+coveredAmount) `average_bucket_value`,
        SUM(amount+coveredAmount) `conversion`
    FROM `orders` o
    INNER JOIN `companys` c ON c.id = o.companyId
    INNER JOIN `city` ON city.plz = c.plz
    INNER JOIN `order_company_group` og on og.orderId=o.id
    WHERE o.kind = 'comp'
        AND o.payment = 'bill'
        AND o.state > 0
    GROUP BY o.companyId, `month`, `year`;

-- get the average count of orders per company
-- @since 07.10.2010
-- @author mlaug, felix
DROP TABLE IF EXISTS `view_company_average_orders`;
DROP VIEW IF EXISTS `view_company_average_orders`;
CREATE VIEW `view_company_average_orders` AS
    SELECT v.id, v.company, AVG(orders) `average_orders`, v.plz, v.city, AVG(average_bucket_value) `average_bucket_value`
    FROM `view_company_count_orders_per_month` v
    GROUP BY v.id;

-- get the average of budget usage of each company
-- @since 07.10.2010
-- @author mlaug, felix
DROP TABLE IF EXISTS `view_company_average_use_of_budget`;
DROP VIEW IF EXISTS `view_company_average_use_of_budget`;
CREATE VIEW `view_company_average_use_of_budget` AS

    SELECT c.name `company`, AVG(ocg.amount) `avg_budget`, c.plz, city.city `city`
    FROM `order_company_group` ocg
    INNER JOIN `companys` c ON ocg.companyId = c.id
    INNER JOIN `orders` o ON o.id = ocg.orderId
    INNER JOIN `city` ON city.plz = c.plz
    WHERE o.state > 0
    GROUP BY c.id
    HAVING `avg_budget` > 0;

-- get the top 10 company
-- @since 22.10.2010
-- @author vpriem
DROP TABLE IF EXISTS `view_company_top_10`;
DROP VIEW IF EXISTS `view_company_top_10`;
CREATE VIEW `view_company_top_10` AS

    SELECT c.id, c.name `company`, SUM(o.total) `total`
    FROM `orders` o
    INNER JOIN `customer_company` cc ON o.customerId = cc.customerId
    INNER JOIN `companys` c ON cc.companyId = c.id
    WHERE o.state > 0
        AND c.deleted = 0
    GROUP BY c.id
    ORDER BY `total` DESC
    LIMIT 10;

-- get the top 10 company
-- @since 22.10.2010
-- @author vpriem
DROP TABLE IF EXISTS `view_company_count`;
DROP VIEW IF EXISTS `view_company_count`;
CREATE VIEW `view_company_count` AS

    SELECT COUNT(c.id) `count`
    FROM `companys` c
    WHERE c.deleted = 0;