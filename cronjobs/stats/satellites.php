<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
* Create statistics for restaurants with and without satellite, group them by city and send the message per mail
* mail must be send every week
* @author alex
* @since 19.01.2011
*/

clog('info', 'checking restaurants with and without satellites');

$message = "####################################\nRestaurants mit Satelliten:\n####################################\n";

foreach (Yourdelivery_Model_Servicetype_Restaurant::getRestaurantsWithSatellite() as $city => $data) {
    $message .= sprintf("\n---------  %s ---------\n\n", $city);

    foreach ($data as $r) {
        $str = sprintf("%s (%d) : %s\n", $r['restaurantName'], $r['restaurantId'], $r['domain']);
        $message .= $str;
    }
}

$message .= "\n\n\n\n####################################\nRestaurants ohne Satelliten:\n####################################\n";

foreach (Yourdelivery_Model_Servicetype_Restaurant::getRestaurantsWithoutSatellite() as $city => $data) {
    $message .= sprintf("\n---------  %s ---------\n\n", $city);

    foreach ($data as $r) {
        $str = sprintf("%s (%d)\n", $r['restaurantName'], $r['restaurantId']);
        $message .= $str;
    }
}

$email = new Yourdelivery_Sender_Email_Template('satellites');
$email->setSubject('Satelliten Statistik');
$email->setBodyText($message);
$email->addTo('konopka@lieferando.de');

if($email->send('system')){
    clog('info', 'Email was send');
}
else{
    clog('err', 'Sending Email failed');
}