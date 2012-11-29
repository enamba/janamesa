-- all triggers for optivo
-- @since 16.03.2011

-- Trigger 1: all customers, whose birthday is known to us
-- @author mlaug
-- @since 16.03.2011
DROP TABLE IF EXISTS `view_optivo_trigger1`;
DROP VIEW IF EXISTS `view_optivo_trigger1`;
CREATE VIEW `view_optivo_trigger1` AS  
    SELECT 
        c.`email`, concat(c.`prename`,' ',c.`name`) name, c.`sex` , 
        IF((select count(*) from orders_customer oc where oc.email=c.email)>0,'registriert','nicht registriert') as status,
        c.`birthday`, c.`birthday` as birthdate
    FROM 
        customers c
        INNER JOIN newsletter_recipients nr on nr.email=c.email
    WHERE 
        nr.status=1 AND
        birthday!='0000-00-00';


-- Trigger 2: all customers, that have ordered once WITH a discount
-- @author mlaug
-- @since 16.03.2011
DROP TABLE IF EXISTS `view_optivo_trigger2`;
DROP VIEW IF EXISTS `view_optivo_trigger2`;
CREATE VIEW `view_optivo_trigger2` AS
    SELECT 
        oc.`email`,concat(oc.`prename`,' ',oc.`name`) as name,c.`sex`,
        IF((select count(*) from customers cu where cu.email=oc.email)>0,'registriert','nicht registriert') as status,
        (select ol.plz from orders_location ol where ol.orderId=oc.orderId) as `in welcher PLZ bestellt wurde` 
        
    FROM 
        orders o 
        INNER JOIN orders_customer oc ON oc.orderId=o.id
        INNER JOIN newsletter_recipients nr ON nr.email=oc.email
        LEFT OUTER JOIN customers c ON oc.email=c.email
    WHERE
        nr.status=1 AND
        o.state>0
    GROUP BY
        email 
    HAVING 
        SUM(discountAmount>0) AND COUNT(*)=1;


-- Trigger 3: all customers, that have ordered once WITHOUT a discount
-- @author mlaug
-- @since 16.03.2011
DROP TABLE IF EXISTS `view_optivo_trigger3`;
DROP VIEW IF EXISTS `view_optivo_trigger3`;
CREATE VIEW `view_optivo_trigger3` AS
    SELECT 
        oc.`email`,concat(oc.`prename`,' ',oc.`name`) as name,c.`sex`,
        IF((select count(*) from customers cu where cu.email=oc.email)>0,'registriert','nicht registriert') as status,
        (select ol.plz from orders_location ol where ol.orderId=oc.orderId) as `in welcher PLZ bestellt wurde`         
    FROM 
        orders o 
        INNER JOIN orders_customer oc ON oc.orderId=o.id
        INNER JOIN newsletter_recipients nr ON nr.email=oc.email
        LEFT OUTER JOIN customers c ON oc.email=c.email
    WHERE
        nr.status=1 AND
        o.state>0
    GROUP BY
        email 
    HAVING 
        SUM(discountAmount=0) AND COUNT(*)=1;


-- Trigger 4: all customers that have currently 6 fidelity points
-- @author mlaug
-- @since 16.03.2011
DROP TABLE IF EXISTS `view_optivo_trigger4`;
DROP VIEW IF EXISTS `view_optivo_trigger4`;
CREATE VIEW `view_optivo_trigger4` AS
    SELECT 
        cfp.`email`,concat(c.`prename`,' ',c.`name`) as name,c.`sex`, 
        IF((select count(*) from customers cu where cu.email=c.email)>0,'registriert','nicht registriert') as status
    FROM 
        customer_fidelity_points cfp
        INNER JOIN newsletter_recipients nr ON nr.email=cfp.email
        LEFT OUTER JOIN customers c ON c.email=cfp.email
    WHERE
        nr.status=1 AND
        points=6;


-- Trigger 5: last or is 4 weeks ago
-- @author mlaug
-- @since 16.03.2011
DROP TABLE IF EXISTS `view_optivo_trigger5`;
DROP VIEW IF EXISTS `view_optivo_trigger5`;
CREATE VIEW `view_optivo_trigger5` AS
    SELECT
        oc.`email`,concat(oc.`prename`,' ',oc.`name`) as name,c.`sex`,
        IF((select count(*) from customers cu where cu.email=oc.email)>0,'registriert','nicht registriert') as status
    FROM orders o
        INNER JOIN orders_customer oc ON oc.orderId=o.id
        INNER JOIN newsletter_recipients nr on nr.email=oc.email        
        LEFT OUTER JOIN customers c ON c.email=nr.email
    WHERE
        nr.status=1 AND
        o.state>0
    GROUP BY oc.email
    HAVING
    	max(o.time) between DATE_SUB(CURDATE(), INTERVAL 4 WEEK) AND CURDATE() AND
        COUNT(*)>1;

-- Trigger 6: last order is 4 weeks ago, online payment and mobile number
-- @author mlaug
-- @since 04.05.2011
DROP TABLE IF EXISTS `view_optivo_trigger6`;
DROP VIEW IF EXISTS `view_optivo_trigger6`;
CREATE VIEW `view_optivo_trigger6` AS
SELECT
        oc.email,concat(oc.`prename`,' ',oc.`name`) as name,c.`sex`
    FROM orders o
        INNER JOIN orders_customer oc ON oc.orderId=o.id
        INNER JOIN orders_location ol ON ol.orderId=o.id
        INNER JOIN newsletter_recipients nr on nr.email=oc.email
        LEFT OUTER JOIN customers c ON c.email=oc.email
    WHERE
        nr.status=1 AND
        o.state>0 AND
        o.payment!='bar' AND
        o.kind='priv' AND 
        ol.tel like '01%'
    GROUP BY oc.email
    HAVING
    	max(o.time) between DATE_SUB(CURDATE(), INTERVAL 4 WEEK) AND CURDATE() AND
        COUNT(*)>1;


-- Trigger 7: orders in a specific city
-- @author mlaug
-- @since 27.05.2011
DROP TABLE IF EXISTS `view_optivo_trigger7`;
DROP VIEW IF EXISTS `view_optivo_trigger7`;
CREATE VIEW `view_optivo_trigger7` AS
    SELECT 
        nc.email,concat(oc.`prename`,' ',oc.`name`) as name,c.`sex`,
        (select name from restaurant_categories rc where rc.id=(select categoryId from restaurants r where r.id=o.restaurantId )) as category
    FROM newsletter_recipients nc 
        INNER JOIN orders_customer oc ON oc.email=nc.email 
        INNER JOIN orders_location ol ON ol.orderId=oc.orderId 
        INNER JOIN orders o ON o.id=ol.orderId 
        INNER JOIN city ci ON ci.id=ol.cityId 
        LEFT OUTER JOIN customers c ON c.email=oc.email
    WHERE 
        nc.status=1 AND 
        o.kind='priv' 
    group by nc.email;