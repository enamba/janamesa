<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * List count of all restaurants with corresponding status
 * mail must be send every sunday, 12:00
 * @author alex
 * @since 08.02.2011
 */
clog('info', 'sending csv with restaurants status count');

$csv = new Default_Exporter_Csv();
$csv->addCol('StatusId');
$csv->addCol('Status');
$csv->addCol('Anzahl');

$db = Zend_Registry::get('dbAdapter');
$data = $db->fetchAll("SELECT rs.id statusId, rs.status, count(if(r.deleted=0, r.id, null)) anzahl
        from restaurants r 
         right outer join restaurant_statis rs         
          on rs.id=r.status
                   group by rs.id
                        order by rs.id");

$offlineStatis = Yourdelivery_Model_Servicetype_Abstract::getStati();

foreach ($data as $d) {
    $csv->addRow(
            array(
                'StatusId' => $d['statusId'],
                'Status' => $offlineStatis[$d['statusId']],
                'Anzahl' => $d['anzahl']
            )
    );
}

$file = $csv->save();

$email = new Yourdelivery_Sender_Email();
$email->setSubject('Anzahl Restaurants nach Status')
        ->setBodyHtml('Datei mit der Anzahl der Restaurants nach Status ist im im Anhang');

$email->addTo('gia@lieferando.de');
$email->addTo('ohrmann@lieferando.de');

$attachment = $email->createAttachment(
        file_get_contents($file),
        'text/comma-separated-values',
        Zend_Mime::DISPOSITION_ATTACHMENT,
        Zend_Mime::ENCODING_BASE64);

$attachment->filename = 'anzahl_restaurants_per_status-' . date("Y-m-d-H-i", time());

if($email->send('system')){
    clog('info', 'Email was send');
}
else{
    clog('err', 'Sending Email failed');
}