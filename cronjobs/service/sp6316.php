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
            where r.deleted=0 and r.onlycash=0 and r.franchiseTypeId=1 and r.isOnline=1
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
        'type' => 5,
        'minAmount' => 700,
        'start' => date(DATETIME_DB, time()),
        'end' => date(DATETIME_DB, strtotime('+1 Year'))
    ));
    $discount->save();
    $discount->setRestaurants(array($d['id']));
    $discount->generateCodes(15);
    
    $csvCodes = new Default_Exporter_Csv();
    $csvCodes->filename = sprintf('%d', $d['id']);
    $csvCodes->extension = '.csv';
    $csvCodes->header = false;
    $csvCodes->addCol('code');
    foreach($discount->getCodes() as $code){
        $csvCodes->addRow(array('code' => $code['code']));
    }
    $fileCodes = $csvCodes->save();
    Default_Helpers_AmazonS3::putObject($config->domain->base, "vouchers/" . sprintf('%d.csv',$d['id']), $fileCodes);
}


$fileID = $csvID->save();
$file = $csv->save();
$config = Zend_Registry::get('configuration');
Default_Helpers_AmazonS3::putObject($config->domain->base, "vouchers/restaurants_created_last_week-" . date("Y-m-d-H-i", time()).'.txt', $file);
Default_Helpers_AmazonS3::putObject($config->domain->base, "vouchers/restaurants_created_last_week-ID" . date("Y-m-d-H-i", time()).'.txt', $fileID);