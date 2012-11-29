<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));


/**
 * List of all restaurants set online this week with their direkt links, sorted by city
 * mail must be send every monday to Jenny
 * @author alex
 * @since 08.02.2011
 */
clog('info', 'sending csv with online restaurants for sem campaign');

$csv = new Default_Exporter_Csv();
$csv->addCol('Restaurant');
$csv->addCol('Strasse');
$csv->addCol('Hausnr');
$csv->addCol('PLZ');
$csv->addCol('Stadt');
$csv->addCol('Direktlink');
$csv->addCol('Online Status');
$csv->addCol('Online gestellt am');

$db = Zend_Registry::get('dbAdapter');
$data = $db->fetchAll(
        "select r.name, r.street, r.hausnr, r.plz, c.city, r.restUrl directLink, IF(count(rn.comment) > 1, 'Reaktiviert', 'Neu') 'status', MAX(rn.`time`) as freigeschaltet
        from restaurants r
            join restaurant_servicetype rs on rs.restaurantId=r.id
            join city c on c.id = r.cityId
            join restaurant_notepad rn on rn.restaurantId=r.id
                where   r.isOnline=1 and
                        rs.servicetypeId=1 and
                        LENGTH(r.restUrl)>0 and
                        rn.comment='Online gestellt'
                        group by r.id having DATE_ADD(freigeschaltet, INTERVAL 1 WEEK) > NOW() order by c.city, r.name");

foreach ($data as $d) {
    $csv->addRow(
            array(
                'Restaurant' => $d['name'],
                'Strasse' => $d['street'],
                'Hausnr' => $d['hausnr'],
                'PLZ' => $d['plz'],
                'Stadt' => $d['city'],
                'Direktlink' => $d['directLink'],
                'Online Status' => $d['status'],
                'Online gestellt am' => $d['freigeschaltet']
            )
    );
}

$file = $csv->save();

$email = new Yourdelivery_Sender_Email();
$email->setSubject('Neue Restaurants nach Stadt, für die SEM Kampagnen');
$email->setBodyText('Restaurants, die in dieser Woche freigeschaltet wurden, nach Städten sortiert, ist im Anhang');

$email->addTo('sem_reports@lieferando.de');

$attachment = $email->createAttachment(
        file_get_contents($file), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
);

$attachment->filename = 'restaurants_fo_rsem-' . date("Y-m-d-H-i", time());

if ($email->send('system')) {
    clog('info', 'Email was send');
} else {
    clog('err', 'Sending Email failed');
}
