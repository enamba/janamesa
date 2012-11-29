/**
*   Database upgrade v1.92
*   @author alex
*   @since 11.01.2011
*/

UPDATE restaurants SET acceptsPfand=1 WHERE id IN 
    (SELECT id FROM
        (SELECT r.id FROM restaurants r JOIN restaurant_servicetype rs ON rs.restaurantId=r.id WHERE rs.servicetypeId=3)
    r);