<?php

/**
 * Get report to the discount action
 * parameter rabattId must be set in URL !!!
 *
 * TODO: at the end of the month oder at the beginning or somehow else?
 * @author alex
 * @since 21.02.2011
 * @param string rabattId
 */
require_once(realpath(dirname(__FILE__) . '/../base.php'));

$options = getopt("r:");
if (isset($options['r'])) {
    $rabattId = $options['r'];
} else {
    // if no discount is specified, show statistic for 'cashback' discount action
    $rabattId = 4119;
}

try {
    $rabatt = new Yourdelivery_Model_Rabatt($rabattId);
} catch (Yourdelivery_Exception_Database_Inconsistency $e) {
    clog('warn', 'Discount not found!');
    return;
}

$db = Zend_Registry::get('dbAdapter');

try {
    $orders = $db->fetchAll(
            "select rc.code, oc.prename, oc.name, ol.street, ol.hausnr, ol.plz, city.city, oc.email, ROUND(o.total/10, 2) as marge
                        from orders o
                            join orders_customer oc on oc.orderId=o.id
                            join orders_location ol on ol.orderId=o.id
                            join city on city.id=ol.cityId
                            join rabatt_codes rc on o.rabattCodeId=rc.id
                                where MONTH(date_sub(NOW(), interval 1 month))=MONTH(o.time) and YEAR(date_sub(NOW(), interval 1 month))=YEAR(o.time) and o.state>0 and rabattId=" . $rabattId);
} catch (Yourdelivery_Exception_Database_Inconsistency $e) {
    clog('warn', 'Error while fetching data');
    return;
}

$csv = new Default_Exporter_Csv();
$csv->addCol('Code');
$csv->addCol('Vorname');
$csv->addCol('Nachname');
$csv->addCol('Strasse');
$csv->addCol('Hausnr');
$csv->addCol('PLZ');
$csv->addCol('Stadt');
$csv->addCol('Email');
$csv->addCol('Marge (cents)');

foreach ($orders as $order) {
    $csv->addRow(
            array(
                'Code' => $order['code'],
                'Vorname' => $order['prename'],
                'Nachname' => $order['name'],
                'Strasse' => $order['street'],
                'Hausnr' => $order['hausnr'],
                'PLZ' => $order['plz'],
                'Stadt' => $order['city'],
                'Email' => $order['email'],
                'Marge (cents)' => $order['marge']
            )
    );
}

$file = $csv->save();

// send file
$email = new Yourdelivery_Sender_Email();
$email->setBodyText(sprintf('Reporting zur Rabattaktion %s (#%d)', $rabatt->getName(), $rabatt->getId()));
$email->setSubject(sprintf('Reporting zur Rabattaktion %s (#%d)', $rabatt->getName(), $rabatt->getId()));
$email->addTo('mueller@lieferando.de');

$attachment = $email->createAttachment(
        file_get_contents($file), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
);

if ($email->send('system')) {
    clog('info', 'Email was send with Attachment "discount-reporting-' . $rabatt->getId() . '.csv"');
} else {
    clog('err', 'Sending Email failed');
}
