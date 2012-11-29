<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));


/**
 * List of all restaurants set offline  this week with their direkt links and offline statis, sorted by city
 * mail must be send every monday to Jenny
 * @author alex
 * @since 08.02.2011
 */
clog('info', 'sending csv with offline restaurants for sem campaign');

$csv = new Default_Exporter_Csv();
$csv->addCol('Restaurant');
$csv->addCol('Strasse');
$csv->addCol('Hausnr');
$csv->addCol('PLZ');
$csv->addCol('Stadt');
$csv->addCol('Offline gestellt am');
$csv->addCol('Offline Status');

$db = Zend_Registry::get('dbAdapter');
$data = $db->fetchAll(
        "select r.name, r.status, r.street, r.hausnr, r.plz, c.city, (select time from restaurant_notepad where restaurantId=r.id and LOCATE('[offline status gesetzt:', comment)>0 order by time desc limit 1) offline
        from restaurants r
            join restaurant_servicetype rs on rs.restaurantId=r.id
            join city c on c.id=r.cityId
            join restaurant_notepad rn on rn.restaurantId=r.id
                where   r.isOnline=0 and
                        rs.servicetypeId=1
                        group by r.id having DATE_ADD(offline, INTERVAL 1 WEEK) > NOW() order by c.city, r.name");

$offlineStati = Yourdelivery_Model_Servicetype_Abstract::getStati();

foreach ($data as $d) {
    $csv->addRow(
            array(
                'Restaurant' => $d['name'],
                'Strasse' => $d['street'],
                'Hausnr' => $d['hausnr'],
                'PLZ' => $d['plz'],
                'Stadt' => $d['city'],
                'Offline gestellt am' => $d['offline'],
                'Offline Status' => $offlineStati[$d['status']]
            )
    );
}

$file = $csv->save();

$email = new Yourdelivery_Sender_Email();
$email->setSubject('Offline Restaurants nach Stadt, für die SEM Kampagnen');
$email->setBodyText('Restaurants, die in dieser Woche offline gestellt wurden, nach Städten sortiert, ist im Anhang');

$email->addTo('sem_reports@lieferando.de');

$attachment = $email->createAttachment(
        file_get_contents($file), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
);
$attachment->filename = 'restaurants_for_sem-' . date("Y-m-d-H-i", time());

if ($email->send('system')) {
    clog('info', 'Email was send');
} else {
    clog('err', 'Sending Email failed');
}

