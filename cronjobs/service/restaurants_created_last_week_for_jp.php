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
$csv->addCol('ID');
$csv->addCol('Restaurant');
$csv->addCol('Stadt');
$csv->addCol('Plz');
$csv->addCol('Online');
$csv->addCol('Status');
$csv->addCol('Vertriebler');
$csv->addCol('Angelegt');
$csv->addCol('Hat Speisekategorien');

$db = Zend_Registry::get('dbAdapter');
$data = $db->fetchAll(
    "SELECT r.id, r.name, c.city, r.isOnline, r.plz, r.status, CONCAT(sp.prename, ' ', sp.name) saler, r.created, (COUNT(mc.id)>0) hasCategory FROM restaurants r 
        JOIN city c ON r.cityId=c.id 
        LEFT JOIN meal_categories mc ON mc.restaurantId=r.id 
        LEFT JOIN salesperson_restaurant sr ON sr.restaurantId=r.id 
        LEFT JOIN salespersons sp ON sp.id=sr.salespersonId WHERE r.deleted=0 and DATE_ADD(r.created, INTERVAL 1 WEEK) > NOW() AND WEEKOFYEAR(r.created) = WEEKOFYEAR(NOW())-1 
        GROUP BY r.id order by r.created;");

$offlineStatis = Yourdelivery_Model_Servicetype_Abstract::getStati();

foreach ($data as $d) {
    $offlineStatus = $d['status'];
    
    $csv->addRow(
            array(
                'ID' => $d['id'],
                'Restaurant' => $d['name'],
                'Stadt' => $d['city'],
                'Plz' => $d['plz'],
                'Online' => $d['isOnline'],
                'Status' => $offlineStatis[$offlineStatus],
                'Vertriebler' => $d['saler'],
                'Angelegt' => $d['created'],
                'Hat Speisekategorien' => $d['hasCategory']
            )
    );
}

$file = $csv->save();

$email = new Yourdelivery_Sender_Email();
$email->setSubject('Restaurants, die in der letzten Woche erstellt wurden')
    ->setBodyHtml('Restaurants, die in dieser Woche erstellt wurden, sind im Anhang')
    ->addTo('gia@lieferando.de')
    ->addTo('generlich@lieferando.de')
    ->addTo('backofficeteam@lieferando.de');

$config = Zend_Registry::get('configuration');

if($config->domain->base == 'lieferando.at') {
    $email->addTo('dittrich@lieferando.de');
}

$attachment = $email->createAttachment(
    file_get_contents($file),
    'text/comma-separated-values',
    Zend_Mime::DISPOSITION_ATTACHMENT,
    Zend_Mime::ENCODING_BASE64
);
$attachment->filename = 'restaurants_created_last_week-' . date("Y-m-d-H-i", time());

if($email->send('system')){
    clog('info', 'Email was send');
}
else{
    clog('err', 'Sending Email failed');
}