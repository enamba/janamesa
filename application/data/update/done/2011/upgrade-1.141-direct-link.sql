
-- @author vpriem
-- @since 27.05.11

UPDATE `restaurants` r
INNER JOIN `restaurant_servicetype` rs ON r.id = rs.restaurantId
    AND rs.servicetypeId = 2 
SET r.directLink = REPLACE(r.directLink, 'lieferservice-', 'catering-')
WHERE r.directLink != ''
    AND r.directLink IS NOT NULL
    AND r.id NOT IN (
        SELECT `restaurantId`
        FROM `restaurant_servicetype`
        WHERE `servicetypeId` != 2
);

UPDATE `restaurants` r
INNER JOIN `restaurant_servicetype` rs ON r.id = rs.restaurantId
    AND rs.servicetypeId = 3 
SET r.directLink = REPLACE(r.directLink, 'lieferservice-', 'grosshandel-')
WHERE r.directLink != ''
    AND r.directLink IS NOT NULL
    AND r.id NOT IN (
        SELECT `restaurantId`
        FROM `restaurant_servicetype`
        WHERE `servicetypeId` != 3
);