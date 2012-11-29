<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * List of all online restaurants with direct links and tags
 * mail must be send every monday to Jenny
 * @author alex
 * @since 17.05.2011
 */
clog('info', 'sending csv with restaurants count per city');

$csv = new Default_Exporter_Csv();
$csv->addCol('Anzahl');
$csv->addCol('Stadt');
$csv->addCol('Bundesland');

$db = Zend_Registry::get('dbAdapter');
$data = $db->fetchAll("select count(r.id) count, c.city, c.`state` from restaurants r join city c on c.id=r.cityId where r.isOnline=1 group by c.city order by state, city");

foreach ($data as $d) {
    $csv->addRow(
            array(
                'Anzahl' => $d['count'],
                'Stadt' => $d['city'],
                'Bundesland' => $d['state']
            )
    );
}

$file = $csv->save();

$email = new Yourdelivery_Sender_Email();
$email->setSubject('Anzahl online Restaurants per Stadt');
$email->setBodyText('Liste mit der Anzahl online Restaurants per Stadt ist im Anhang');

$email->addTo('tribbels@lieferando.de');

$attachment = $email->createAttachment(
        file_get_contents($file), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
);
$attachment->filename = 'restaurants_count_per_city' . date("Y-m-d-H-i", time());

if ($email->send('system')) {
    clog('info', 'Email was send');
} else {
    clog('err', 'Sending Email failed');
}

