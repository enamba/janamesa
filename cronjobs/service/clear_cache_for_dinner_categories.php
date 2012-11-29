<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
* search for restaurants with meal categories having 'from' field between now and (now + 11 Minuten)
* and clear the cache for these restaurants. This way the dinner categories will be shown as soon, as they become
* available for orders, i.e, "from" or "to" value is between the 11 minutes range of actual time
 * must run every 5 minutes, so the cache will be cleared twice, just to be sure
* @author alex
* @since 19.01.2011
*/

clog('debug', 'clearing cache for restaurants, having categories with time slot between now and (now+6) minutes. On midnight cache will be cleared for all restaurants');

$restaurantDb = new Yourdelivery_Model_DbTable_Restaurant();
$sql = "SELECT DISTINCT(restaurantId) AS rid FROM meal_categories mc WHERE
    CURTIME() BETWEEN mc.`from` AND SEC_TO_TIME(TIME_TO_SEC(mc.`from`)+1920)
    OR
    CURTIME() BETWEEN mc.`to` AND SEC_TO_TIME(TIME_TO_SEC(mc.`to`)+1920)";

foreach($restaurantDb->getAdapter()->query($sql)->fetchAll() as $r){
    try{
        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($r['rid']);
        $restaurant->uncache();
        clog('info', sprintf('cache cleared for restaurant %s (#%d)', $restaurant->getName(), $restaurant->getId()));
    }
    catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
        clog('crit', sprintf('couldn\'t create restaurant object for id %d', $r['rid']));
        continue;
    }
}
