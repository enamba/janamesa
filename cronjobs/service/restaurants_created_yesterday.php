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
$csv->addCol('PLZ');
$csv->addCol('Strasse');
$csv->addCol('Hausnr');
$csv->addCol('Online');
$csv->addCol('restUrl');
$csv->addCol('caterUrl');
$csv->addCol('greatUrl');


$db = Zend_Registry::get('dbAdapter');
$data = $db->fetchAll(
    "SELECT r.id, r.name, c.city, r.plz, r.street, r.hausNr, r.isOnline, r.restUrl, r.caterUrl, r.greatUrl FROM restaurants r 
        JOIN city c ON r.cityId=c.id 
            WHERE r.deleted=0 and DATE(r.created)=DATE_SUB(CURDATE(), INTERVAL 1 DAY)");


foreach ($data as $d) {
    
    
    $csv->addRow(
            array(
                'ID' => $d['id'],
                'Restaurant' => $d['name'],
                'Stadt' => $d['city'],
                'PLZ' => $d['plz'],
                'Strasse' => $d['street'],
                'Hausnr' => $d['hausNr'],
                'Online' => $d['isOnline'],
                'restUrl' => $d['restUrl'],
                'caterUrl' => $d['caterUrl'],
                'greatUrl' => $d['greatUrl']                
            )
    );
}

$file = $csv->save();

$email = new Yourdelivery_Sender_Email();
$email->setSubject('Restaurants, die gestern erstellt wurden')
    ->setBodyHtml('Restaurants, die gestern erstellt wurden, sind im Anhang')
    ->addTo('sem_reports@lieferando.de');
$attachment = $email->createAttachment(
    file_get_contents($file),
    'text/comma-separated-values',
    Zend_Mime::DISPOSITION_ATTACHMENT,
    Zend_Mime::ENCODING_BASE64
);
$attachment->filename = 'restaurants_yesterday-' . date("Y-m-d-H-i", time());

if($email->send('system')){
    clog('info', 'Email was send');
}
else{
    clog('err', 'Sending Email failed');
}