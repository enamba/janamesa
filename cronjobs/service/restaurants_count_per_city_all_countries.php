<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * List of all cities with the count of restaurants for each country in one table
 * mail must be send every tuesday at 6 to sem_reports
 * @author jens naie <naie@lieferando.de>
 * @since 14.08.2012
 */
clog('info', 'sending csv with restaurants count per city for all countries');

$csv = new Default_Exporter_Csv(false);
$csv->addCol('Land');
$csv->addCol('Stadt');
$csv->addCol('Anzahl');
$csv->addCol('KW');

$db = Zend_Registry::get('dbAdapterReadOnly');
$data = $db->fetchAll("
    #Nach Liefergebieten:
SELECT
    'DE' as Land
    ,SUBSTRING_INDEX(IF(c2.city is not null, c2.city, c.city),',',1) as Stadt
    ,COUNT(DISTINCT(rpcs.`restaurantId`)) as Anzahl
    ,WEEKOFYEAR(now()) as KW
FROM `lieferando.de`.`data_view_affiliate_feed_restaurantid_per_city_sub` rpcs
JOIN `lieferando.de`.city c on c.id = rpcs.cid
LEFT JOIN `lieferando.de`.city c2 on c2.id = c.parentCityId
GROUP BY
    Stadt
#AT:
UNION
SELECT
    'AT' as Land
    ,SUBSTRING_INDEX(IF(c2.city is not null, c2.city, c.city),',',1) as Stadt
    ,COUNT(DISTINCT(rpcs.`restaurantId`)) as Anzahl
    ,WEEKOFYEAR(now()) as KW
FROM `lieferando.at`.`data_view_affiliate_feed_restaurantid_per_city_sub` rpcs
JOIN `lieferando.at`.city c on c.id = rpcs.cid
LEFT JOIN `lieferando.at`.city c2 on c2.id = c.parentCityId
GROUP BY
    Stadt
#CH:
UNION
SELECT
    'CH' as Land
    ,SUBSTRING_INDEX(IF(c2.city is not null, c2.city, c.city),',',1) as Stadt
    ,COUNT(DISTINCT(rpcs.`restaurantId`)) as Anzahl
    ,WEEKOFYEAR(now()) as KW
FROM `lieferando.ch`.`data_view_affiliate_feed_restaurantid_per_city_sub` rpcs
JOIN `lieferando.ch`.city c on c.id = rpcs.cid
LEFT JOIN `lieferando.ch`.city c2 on c2.id = c.parentCityId
GROUP BY
    Stadt
#FR
UNION
SELECT
    'FR' as Land
    ,SUBSTRING_INDEX(IF(c2.city is not null, c2.city, c.city),',',1) as Stadt
    ,COUNT(DISTINCT(rpcs.`restaurantId`)) as Anzahl
    ,WEEKOFYEAR(now()) as KW
FROM `taxiresto.fr`.`data_view_affiliate_feed_restaurantid_per_city_sub` rpcs
JOIN `taxiresto.fr`.city c on c.id = rpcs.cid
LEFT JOIN `taxiresto.fr`.city c2 on c2.id = c.parentCityId
GROUP BY
    Stadt
#PL
UNION
SELECT
    'PL' as Land
    ,SUBSTRING_INDEX(IF(c2.city is not null, c2.city, c.city),',',1) as Stadt
    ,COUNT(DISTINCT(rpcs.`restaurantId`)) as Anzahl
    ,WEEKOFYEAR(now()) as KW
FROM `smakuje.pl`.`data_view_affiliate_feed_restaurantid_per_city_sub` rpcs
JOIN `smakuje.pl`.city c on c.id = rpcs.cid
LEFT JOIN `smakuje.pl`.city c2 on c2.id = c.parentCityId
GROUP BY
    Stadt

ORDER BY Land, Anzahl DESC
;");

foreach ($data as $d) {
    $csv->addRow(
            array(
                'Land' => $d['Land'],
                'Stadt' => $d['Stadt'],
                'Anzahl' => $d['Anzahl'],
                'KW' => $d['KW']
            )
    );
}

$file = $csv->save();

$email = new Yourdelivery_Sender_Email();
$email->setSubject('Anzahl Restaurants in jeder Stadt in allen Länder');
$email->setBodyText('Liste mit der Anzahl Restaurants in jeder Stadt in allen Länder im Anhang');

$email->addTo('sem_reports@lieferando.de');

$attachment = $email->createAttachment(
        file_get_contents($file), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
);
$attachment->filename = date("Ymd", time()) . '-restaurants-per-delivery-area.csv';

if ($email->send('system')) {
    clog('info', 'Email was send');
} else {
    clog('err', 'Sending Email failed');
}

