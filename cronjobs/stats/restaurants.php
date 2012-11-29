<?php

/**
 * @deprecatedshould be removed
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 13.10.2011
 */
require_once(realpath(dirname(__FILE__) . '/../base.php'));


return;

// get db
$db = Zend_Registry::get('dbAdapter');


// restaurants per cities statistics
$results = $db->fetchAll("select status, count(id) as count from restaurants where deleted=0 group by status order by status");

$statis = Yourdelivery_Model_Servicetype_Abstract::getStati();

$restaurants_stats = array();


foreach ($results as $res) {

    $restaurants_stats[$res['status']] = $res['count'];
}

foreach ($restaurants_stats as $key => $value) {
    $db->insert('restaurant_status_history', array('status' => $key,
        'count' => $value
    ));
}