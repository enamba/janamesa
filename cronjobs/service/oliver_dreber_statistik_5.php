<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * statistik of all orders in 5 cities
 * mail must be send on first day of every month to Oliver Dreber
 * @author alex
 * @since 31.03.2011
 */
clog('info', 'sending orders statistik per city to oliver dreber');

$db = Zend_Registry::get('dbAdapter');

try {
    $data = $db->fetchAll(
            "select
                c.city as stadt,
                count(o.id) as count,
                sum(if(r.franchiseTypeId=3, 1, 0)) as premiumCount,
                sum(o.total) as bestellwert,
                sum(o.total*(if(r.franchiseTypeId=3, 1, 0))) as bestellwertPremium
                from orders o
                    join restaurants r on o.restaurantId=r.id
                    join orders_location ol on ol.orderId=o.id
                    join city c on c.plz=ol.plz
                        where
                            MONTH(date_sub(NOW(), interval 1 month))=MONTH(o.time) and
                            YEAR(date_sub(NOW(), interval 1 month))=YEAR(o.time) and
                            o.state>0 and
                            ((c.city = 'Berlin') or (c.city = 'Düsseldorf') or (c.city = 'Frankfurt am Main') or (c.city = 'Hamburg') or (c.city = 'München'))
                            group by c.city;");
} catch (Zend_Db_Statement_Exception $e) {
    clog('err', 'Error while fetching data');
    return;
}

$csv = new Default_Exporter_Csv();
$csv->addCol('Stadt');
$csv->addCol('Anzahl der Bestellungen');
$csv->addCol('Anzahl der premium Bestellungen');
$csv->addCol('Bestellwert');
$csv->addCol('Bestellwert der premium Bestellungen');

foreach ($data as $d) {
    $csv->addRow(
            array(
                'Stadt' => $d['stadt'],
                'Anzahl der Bestellungen' => $d['count'],
                'Anzahl der premium Bestellungen' => $d['premiumCount'] . ' (' . round(($d['premiumCount'] / $d['count']) * 100, 2) . '%)',
                'Bestellwert' => intToPrice($d['bestellwert']),
                'Bestellwert der premium Bestellungen' => intToPrice($d['bestellwertPremium']) . ' (' . round(($d['bestellwertPremium'] / $d['bestellwert']) * 100, 2) . '%)',
            )
    );
}

$file = $csv->save();

// send file
$email = new Yourdelivery_Sender_Email();
$email->setBodyText('Bestellungen nach Städten sortiert');
$email->setSubject('Bestellungen nach Städten sortiert');
$email->addTo('dreber@lieferando.de');
$email->addTo('vait@lieferando.de');

$attachment = $email->createAttachment(
        file_get_contents($file), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
);


if ($email->send('system')) {
    clog('info', 'Email was send');
} else {
    clog('err', 'Sending Email failed');
}
?>