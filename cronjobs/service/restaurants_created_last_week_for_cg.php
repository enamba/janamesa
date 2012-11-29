<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * List of all restaurants created last week
 * mail must be send every monday at the morning, 6 o'clock or so
 * @author alex
 * @since 28.05.2011
 */
clog('info', 'sending restaurants created last week');

$csv = new Default_Exporter_Csv();
$csv->addCol('dienstleisterID');
$csv->addCol('Name');
$csv->addCol('Plz');
$csv->addCol('Stadt');
$csv->addCol('Straße');
$csv->addCol('Hausnummer');
$csv->addCol('URL des Partners');
$csv->addCol('Kategorie');
$csv->addCol('Online');
$csv->seperator = "\t";
$csv->extension = '.txt';

$csvID = new Default_Exporter_Csv();
$csvID->addCol('dienstleisterID');
$csvID->seperator = "\t";
$csvID->extension = '.csv';
$csvID->header = false;

$db = Zend_Registry::get('dbAdapter');
$data = $db->fetchAll(
        "SELECT r.id, r.name, r.plz, c.city, r.street, r.hausnr, r.restUrl, r.isOnline, rc.name as `category`, r.status FROM restaurants r 
        INNER JOIN city c ON r.cityId=c.id 
        INNER JOIN restaurant_categories rc on rc.id=r.categoryId
            WHERE r.deleted=0 and 
                  DATE_ADD(r.created, INTERVAL 1 WEEK) > NOW() AND 
                  WEEKOFYEAR(r.created) = WEEKOFYEAR(NOW())-1 AND
                  isOnline=1 AND
                  onlycash=0
        GROUP BY r.id order by r.id ASC");

$email = new Yourdelivery_Sender_Email();
$email->setSubject('Restaurants, die in der letzten Woche erstellt wurden')
        ->setBodyHtml('Restaurants, die in dieser Woche erstellt wurden, sind im Anhang')
        ->addTo('gerber@lieferando.de');

foreach ($data as $d) {

    $csv->addRow(
            array(
                'dienstleisterID' => $d['id'],
                'Name' => $d['name'],
                'Plz' => $d['plz'],
                'Stadt' => $d['city'],
                'Straße' => $d['street'],
                'Hausnummber' => $d['hausnr'],
                'URL des Partners' => $d['restUrl'],
                'Kategorie' => $d['category'],
                'Online' => $d['isOnline']
            )
    );
    
    $csvID->addRow(array('dienstleisterID' => $d['id']));

    //create csv file with vouchers
    $discount = new Yourdelivery_Model_Rabatt();
    $discount->setData(array(
        'name' => 'Autogenerate for ' . $d['name'],
        'rrepeat' => 0,
        'countUsage' => 0,
        'kind' => 1,
        'rabatt' => 400,
        'status' => 1,
        'type' => 4,
        'minAmount' => 700,
        'start' => date(DATETIME_DB, time()),
        'end' => date(DATETIME_DB, strtotime('+1 Year'))
    ));
    $discount->save();
    $discount->setRestaurants(array($d['id']));
    $discount->generateCodes(2500);
    
    $csvCodes = new Default_Exporter_Csv();
    $csvCodes->filename = sprintf('%d', $d['id']);
    $csvCodes->extension = '.csv';
    $csvCodes->header = false;
    $csvCodes->addCol('code');
    $csvCodes->addRow(array(sprintf('%s.pdf', $csvCodes->filename)));
    foreach($discount->getCodes() as $code){
        $csvCodes->addRow(array('code' => $code['code']));
    }
    $fileCodes = $csvCodes->save();
    $attachment = $email->createAttachment(
            file_get_contents($fileCodes), 'application/zip', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
    );
    $attachment->filename = sprintf('%d.csv',$d['id']);
}

//CSV-DATA
$file = $csv->save();
$attachment = $email->createAttachment(
        file_get_contents($file), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
);
$attachment->filename = 'restaurants_created_last_week-' . date("Y-m-d-H-i", time()).'.txt';

//CSV-ID
$fileID = $csvID->save();
$attachment = $email->createAttachment(
        file_get_contents($fileID), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
);
$attachment->filename = 'restaurants_created_last_week-ID-' . date("Y-m-d-H-i", time()).'.csv';


//now get the restaurants from last last week
$data = $db->fetchAll(
        "SELECT r.id, r.name, r.plz, c.city, r.street, r.hausnr, r.restUrl, r.isOnline, rcat.name as `category`, r.status, count(*) as anzahl FROM restaurants r 
            INNER JOIN rabatt_restaurant rr ON r.id=rr.restaurantId
            INNER JOIN rabatt_codes rc on rc.rabattId=rr.rabattId and rc.used=1
            INNER JOIN orders o on o.rabattCodeId=rc.id
            INNER JOIN city c ON r.cityId=c.id         
        	INNER JOIN restaurant_categories rcat on rcat.id=r.categoryId
            WHERE r.deleted=0 and 
                  DATE_ADD(r.created, INTERVAL 2 WEEK) > NOW() AND 
                  WEEKOFYEAR(r.created) = WEEKOFYEAR(NOW())-2 AND
                  onlycash=0
            GROUP BY r.id
            ORDER BY r.id ASC");

$csvUsed = new Default_Exporter_Csv();
$csvUsed->addCol('dienstleisterID');
$csvUsed->addCol('Name');
$csvUsed->addCol('Plz');
$csvUsed->addCol('Stadt');
$csvUsed->addCol('Straße');
$csvUsed->addCol('Hausnummer');
$csvUsed->addCol('URL des Partners');
$csvUsed->addCol('Kategorie');
$csvUsed->addCol('Online');
$csvUsed->addCol('Status');
$csvUsed->addCol('Anzahl');
$csvUsed->addRows($data);
$fileUsed = $csvUsed->save();
$attachment = $email->createAttachment(
        file_get_contents($fileUsed), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
);
$attachment->filename = 'restaurants_created_last_last_week_with_vouchers-' . date("Y-m-d-H-i", time()).'.csv';

$email->send('system');