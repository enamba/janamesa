<?php

/* * *** RESTAURANT-RATINGS -- BEGIN **** */
/**
 * sum ratings from restaurant_ratings-table and set values to each restaurant every hour
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 08.07.2010
 */
require_once(realpath(dirname(__FILE__) . '/../base.php'));

clog('info', 'sum ratings from restaurant_ratings-table and set values to each restaurant');

$sql = "UPDATE restaurants r SET 
        r.ratingQuality = 
            (SELECT SUM(rr.quality)/count(rr.id) 
                FROM restaurant_ratings rr 
                WHERE rr.restaurantId = r.id 
                    AND rr.status = 1), 
        r.ratingDelivery = 
            (SELECT SUM(rr.delivery)/count(rr.id) 
                FROM restaurant_ratings rr 
                WHERE rr.restaurantId = r.id 
                    AND rr.status = 1),
        r.ratingAdvisePercentPositive = 
            (SELECT COALESCE(round(sum(advise)/count(id)*100),0) FROM restaurant_ratings rr
            WHERE rr.restaurantId = r.id 
                AND rr.status = 1)";

$db = Zend_Registry::get('dbAdapter');
$db->query($sql);

clog('info', 'restaurant_ratings updated in restaurant-table');

/***** RESTAURANT-RATINGS -- END *****/
