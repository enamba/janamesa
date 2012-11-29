-- get the top 10 service
-- @since 22.10.2010
-- @author vpriem
DROP TABLE IF EXISTS `view_service_top_10`;
DROP VIEW IF EXISTS `view_service_top_10`;
CREATE VIEW `view_service_top_10` AS

    SELECT r.id, r.name `service`, SUM(o.total + COALESCE(o.serviceDeliverCost, 0) + COALESCE(o.courierCost, 0)) `total`
    FROM `orders` o
    JOIN `restaurants` r ON o.restaurantId = r.id
    WHERE o.state > 0
        AND r.deleted = 0
    GROUP BY r.id
    ORDER BY `total` DESC
    LIMIT 10;

-- services online / offline per city
-- @since 25.10.2010
-- @author vpriem
DROP TABLE IF EXISTS `view_service_online_offline_per_city`;
DROP VIEW IF EXISTS `view_service_online_offline_per_city`;
CREATE VIEW `view_service_online_offline_per_city` AS

    SELECT o.city as `ort`, SUM(r.isOnline) `online`, COUNT(r.id) - SUM(r.isOnline) `offline`
    FROM `restaurants` r
    INNER JOIN `city` o ON r.plz = o.plz
    WHERE r.deleted = 0
    GROUP BY o.city
    ORDER BY o.city;

-- mlaug
-- 22.10.2010
-- anzahl dienstleister online in verschiedenen Kategorien (rest,cater,great)
DROP TABLE IF EXISTS `view_service_online`;
DROP VIEW IF EXISTS `view_service_online`;
CREATE VIEW `view_service_online` AS

    SELECT s.name `typ`, COUNT(r.id) `count`
    FROM `restaurants` r
    INNER JOIN `restaurant_servicetype` rs ON r.id = rs.restaurantId
    INNER JOIN `servicetypes` s ON rs.servicetypeId = s.id
    WHERE r.isOnline = 1
        AND r.deleted = 0
    GROUP BY rs.servicetypeId;


-- vpriem
-- 25.10.2010
-- restaurants die auch catering anbieten
DROP TABLE IF EXISTS `view_service_with_catering`;
DROP VIEW IF EXISTS `view_service_with_catering`;
CREATE VIEW `view_service_with_catering` AS

    SELECT r.name
    FROM `restaurants` r
    INNER JOIN `restaurant_servicetype` rs ON r.id = rs.restaurantId
        AND rs.servicetypeId = 1
    WHERE r.deleted = 0
        AND r.id IN (
            SELECT `restaurantId`
            FROM `restaurant_servicetype`
            WHERE `servicetypeId` = 2
        )
    ORDER BY r.name;

-- restaurants grouped per servicetype and plz
-- dont' forget - restaurants without servicetype, i.e. without menu, are not included in this statistics
-- @since 24.01.2011
-- @author alex
DROP TABLE IF EXISTS `view_restaurants_per_servicetype_and_plz`;
DROP VIEW IF EXISTS `view_restaurants_per_servicetype_and_plz`;
CREATE VIEW `view_restaurants_per_servicetype_and_plz` AS

SELECT COUNT(r.id) AS count, s.id AS category, s.name as categoryName, r.isOnline, SUBSTRING(r.plz, 1, 1) AS  r_plz
    FROM restaurants r
    LEFT JOIN restaurant_servicetype rs ON r.id=rs.restaurantId
    LEFT JOIN servicetypes s ON rs.servicetypeId=s.id
    WHERE r.deleted=0 GROUP BY s.id, r.isOnline, r_plz ORDER BY r_plz, s.id;


-- count of online/offline restaurants, not deleted
-- @since 24.01.2011
-- @author alex
DROP TABLE IF EXISTS `view_restaurants_count_per_plz`;
DROP VIEW IF EXISTS `view_restaurants_count_per_plz`;
CREATE VIEW `view_restaurants_count_per_plz` AS

SELECT COUNT(r.id) AS count, IF (LENGTH(r.plz)=5, SUBSTRING(r.plz, 1, 1), 0) AS r_plz, r.isOnline
                from restaurants r
                where r.deleted=0 group by r.isOnline, r_plz order by r_plz;

