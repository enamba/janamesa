<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

error_reporting(E_ERROR | E_WARNING | E_PARSE);

/**
 * set hasSpecials flag for meals
 * @author Alex Vait <vait@lieferando.de>
 * @since 15.12.2011
 */

$db = Zend_Registry::get('dbAdapterReadOnly');

// get count of all online restaurants
$rCount = $db->fetchRow("select count(id) cnt from restaurants where deleted=0 and isOnline=1");

// distribute restaurants to 30 days of month. on 31th do nothing, february sucks
$restaurantsPerDay = (int)($rCount['cnt'] / 31);

// sanity
if (intval($rCount['cnt']) <= 0) {
    clog('info', 'no restaurants to update, error in query');
    return;
}

clog('info', 'setting flag hasSpecials for ' . $restaurantsPerDay . ' restaurants');

// where to start
$date = date('d', time());

// 20 restaurants more, just in case some were uploaded recently.
$restaurants = $db->fetchAll(sprintf("select id from restaurants where deleted=0 and isOnline=1  order by updated limit %d, %d", $restaurantsPerDay*$date, $restaurantsPerDay+20));
$restaurantsDone = 1;

foreach ($restaurants as $r) {
    clog('info', 'setting flag hasSpecials for restaurant ' . $r['id'] . ', ' . $restaurantsDone . ' of ' . $restaurantsPerDay);
    
    $meals = $db->fetchAll(sprintf("select id from meals where deleted=0 and status=1 and restaurantId=%d", $r['id']));
    $mealsCount = count($meals);
    
    foreach ($meals as $m) {        
        try{
            $meal = new Yourdelivery_Model_Meals($m['id']);
            $meal->updateHasSpecials();
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
        }            
    }
    
    $restaurantsDone++;
}

