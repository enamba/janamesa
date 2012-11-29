<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * Internal links clean up
 * @author vpriem
 * @since 21.01.2011
 */

$removedRestaurants = 0;
$generatedLinks = 0;
$countLinks = 0;

$dbTable = new Yourdelivery_Model_DbTable_Link();

$results = $dbTable->getWithWrongRestaurant();

foreach ($results as $result) {

    $link = $dbTable->findRow($result['linkId']);

    if ($link !== false) {

        if ($link->removeRestaurant($result['restaurantId'])) {
            $removedRestaurants++;
        }
    }
}


$rows = $dbTable->getAll();
$countLinks = count($rows);

foreach ($rows as $row) {
    
    if ($row->publish()) {
        $generatedLinks++;
    }
    
}

clog('info', sprintf("SEO: %d removed restaurants", $removedRestaurants));
clog('info', sprintf("SEO: %d of %d links generated", $generatedLinks, $countLinks));