<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * List of all online restaurants with direct links and tags
 * mail must be send every monday to Jenny
 * @author alex
 * @since 17.05.2011
 */
clog('info', 'sending csv with online restaurants for seo');

$isAT = (Zend_Registry::get('configuration')->domain->base == "lieferando.at") ? true : false;

$csv = new Default_Exporter_Csv();
$csv->addCol('RestaurantId');
$csv->addCol('Restaurant');
$csv->addCol('Strasse');
$csv->addCol('Hausnr');
$csv->addCol('PLZ');
$csv->addCol('Stadt');
//hack für at Bezirke
if ($isAT) {
    $csv->addCol('Bezirk');
}
$csv->addCol('Direct Link');
$csv->addCol('Tags');

$db = Zend_Registry::get('dbAdapter');
$data = $db->fetchAll(
        "select r.id, r.name, r.street, r.hausnr, r.plz, c.city, r.restUrl  as directLink,
        GROUP_CONCAT(rt.tag) tags 
        from restaurants r 
            join restaurant_tags rt on rt.restaurantId=r.id 
            join city c on c.id=r.cityId          

                where r.isOnline=1 group by r.id");

foreach ($data as $d) {

    if ($isAT) {

        $stadt = $d['city'];
        $bezirk = "";

        $cities_at = array("Wien", "Graz", "Klagenfurt");
        foreach ($cities_at as $city) {
            if (strstr($d['city'], $city)) {
                $stadt = $city;
                $bezirk = trim(substr($d['city'], strlen($city) + 1));
            }
        }

        $row = array(
            'RestaurantId' => $d['id'],
            'Restaurant' => $d['name'],
            'Strasse' => $d['street'],
            'Hausnr' => $d['hausnr'],
            'PLZ' => $d['plz'],
            'Stadt' => $stadt,
            'Bezirk' => $bezirk,
            'Direct Link' => $d['directLink'],
            'Tags' => $d['tags']
        );
    } else {
        $row = array(
            'RestaurantId' => $d['id'],
            'Restaurant' => $d['name'],
            'Strasse' => $d['street'],
            'Hausnr' => $d['hausnr'],
            'PLZ' => $d['plz'],
            'Stadt' => $d['city'],
            'Direct Link' => $d['directLink'],
            'Tags' => $d['tags']
        );
    }



    $csv->addRow(
            $row
    );
}

$file = $csv->save();

if (IS_PRODUCTION) {
    $email = new Yourdelivery_Sender_Email();
    $email->setSubject('Online Restaurants mit Tags');
    $email->setBodyText('Liste aller online Restaurants mit Tags ist im Anhang');

    $email->addTo('tribbels@lieferando.de');

    $attachment = $email->createAttachment(
            file_get_contents($file), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
    );
    $attachment->filename = 'restaurants_online_' . date("Y-m-d-H-i", time());

    if ($email->send('system')) {
        clog('info', 'Email was send');
    } else {
        clog('err', 'Sending Email failed');
    }
}

