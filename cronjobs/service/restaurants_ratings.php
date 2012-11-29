<?php
/**
 * sum ratings from restaurant_ratings-table and set values to each restaurant
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 08.07.2010
 */
require_once(realpath(dirname(__FILE__) . '/../base.php'));



clog('info', 'sum ratings from restaurant_ratings-table and set values to each restaurant');



$ratingsTable = new Yourdelivery_Model_DbTable_Restaurant_Ratings();
$ratings = $ratingsTable->fetchAll('status = 1', null);

$ratingsArray = array();
foreach($ratings as $rating){
    $ratingsArray[$rating->restaurantId]['quality'] += $rating->quality;
    $ratingsArray[$rating->restaurantId]['qualityCount'] ++;
    $ratingsArray[$rating->restaurantId]['delivery'] += $rating->delivery;
    $ratingsArray[$rating->restaurantId]['deliveryCount'] ++;
}

foreach($ratingsArray as $restaurantId => $vals){
    $quality = round($vals['quality']/$vals['qualityCount']);
    $delivery = round($vals['delivery']/$vals['deliveryCount']);
    try {
        $service = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
        $service->setData(array(
            'ratingQuality' => $quality,
            'ratingDelivery' => $delivery
        ))->save();
        
    } catch (Yourdelivery_Exception_Database_Inconsistency $exc) {
        echo $exc->getTraceAsString();
        continue;
    }


}





