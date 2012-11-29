<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

$db = Zend_Registry::get('dbAdapter');
$start = strtotime('first day of last month');
$end = strtotime('last day of last month');
$orders = $db->fetchAll(sprintf("
select r.id, r.name, r.street, r.hausnr, r.plz, c.city, if(o.domain is null, '', o.domain) as domain, round(sum(total+serviceDeliverCost)/100, 2) as Umsatz, count(o.id) as Anzahl from orders o 
                        inner join restaurants r on r.id=o.restaurantId 
                        inner join city c on c.id=r.cityId 
                        inner join billing b on b.refId=r.id 
                            where b.mode='rest' and r.name='PIZZA AVANTI' and b.created
                            between '%s' and '%s' group by r.id,o.domain", date(DATETIME_DB, $start), date(DATETIME_DB, $end)));

$csv = new Default_Exporter_Csv();

$init = true;
foreach ($orders as $order) {
    if ($init) {
        $csv->addCols(array_keys($order));
        $init = false;
    }
    $csv->addRow($order);
}

$file = $csv->save();

// send file
$email = new Yourdelivery_Sender_Email();
$email->setBodyText('Avanti Statistiken');
$email->setSubject('Avanti Statistiken für den ' . date(DATETIME_DB));
$email->addTo('mg-marketing@t-online.de');
$email->createAttachment(
        file_get_contents($file), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
);


if ($email->send('system')) {
    clog('info', 'Email was send');
} else {
    clog('err', 'Sending Email failed');
}
