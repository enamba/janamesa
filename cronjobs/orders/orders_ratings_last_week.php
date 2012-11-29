<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

$db = Zend_Registry::get('dbAdapterReadOnly');

$resultOrders = $db->fetchRow('select count(id) as Bestellungen from orders where time > date_sub(NOW(), INTERVAL 7 DAY) and state>0');
$resultRatings = $db->fetchRow('select count(id) as Bewertungen from restaurant_ratings where created > date_sub(NOW(), INTERVAL 7 DAY)');

$csv = new Default_Exporter_Csv();
$csv->addCol('Gesamte Bestellungen DE');
$csv->addCol('Gesamte Bewertungen DE');

$csv->addRow(
        array(
            'Gesamte Bestellungen DE' => $resultOrders['Bestellungen'],
            'Gesamte Bewertungen DE' => $resultRatings['Bewertungen']
        )
);

$file = $csv->save();


$email = new Yourdelivery_Sender_Email();
$email->setSubject('Freitags Report')
        ->setBodyHtml('Zeitraum: von Freitag letzte Woche bis Freitag aktuelle Woche')
        ->addTo('escobedo@lieferando.de');

$attachment = $email->createAttachment(
        file_get_contents($file), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
);
$attachment->filename = 'orders_ratings_last_week-' . date("Y-m-d-H-i", time()) . 'csv';

if ($email->send('system')) {
    clog('info', 'Email was send');
} else {
    clog('err', 'Sending Email failed');
}