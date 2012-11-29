<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
* List of all company orders last week
* mail must be send every monday at the morning, 6 o'clock or so
* @author Alex Vait <vait@lieferando.de>
* @since 04.06.2012
*/

clog('info', 'sending company orders of last week');

$csv = new Default_Exporter_Csv();
$csv->addCol('Bestellungen');
$csv->addCol('Umsatz Bestellungen gesamt');
$csv->addCol('Premium Bestellungen');
$csv->addCol('Umsatz Premium Bestellungen');
$csv->addCol('Bestellungen Firmen ohne Account');

$db = Zend_Registry::get('dbAdapter');
$dataCompanys = $db->fetchRow(
    "SELECT count(o.id) Bestellungen, round(sum(o.total + COALESCE(o.serviceDeliverCost, 0) + COALESCE(o.courierCost, 0))/100, 2) Umsatz
    FROM `orders` o
    INNER JOIN `customer_company` cc ON o.customerId = cc.customerId
    INNER JOIN `companys` c ON cc.companyId = c.id
    WHERE o.state > 0 AND c.deleted = 0 and DATE_ADD(o.time, INTERVAL 1 WEEK) > NOW() AND WEEKOFYEAR(o.time) = WEEKOFYEAR(NOW())-1");

$dataCompanysPremium = $db->fetchRow(
    "SELECT count(o.id) Bestellungen, round(sum(o.total + COALESCE(o.serviceDeliverCost, 0) + COALESCE(o.courierCost, 0))/100,2) Umsatz
    FROM `orders` o
    INNER JOIN `restaurants` r ON r.id= o.restaurantId
    INNER JOIN `customer_company` cc ON o.customerId = cc.customerId
    INNER JOIN `companys` c ON cc.companyId = c.id
    INNER JOIN `restaurant_franchisetype` rf ON rf.id = r.franchiseTypeId
    WHERE o.state > 0 AND c.deleted = 0 and rf.name = 'Premium' AND DATE_ADD(o.time, INTERVAL 1 WEEK) > NOW() AND WEEKOFYEAR(o.time) = WEEKOFYEAR(NOW())-1");

$dataCompanysNotRegistered = $db->fetchRow(
    "SELECT count(o.id) Bestellungen
    FROM `orders` o
    INNER JOIN `restaurants` r ON r.id= o.restaurantId
    LEFT JOIN `customer_company` cc ON o.customerId = cc.customerId
    LEFT JOIN `companys` c ON cc.companyId = c.id
    INNER JOIN `orders_location` ol ON ol.orderId = o.id
    WHERE o.state > 0 AND c.id IS NULL AND DATE_ADD(o.time, INTERVAL 1 WEEK) > NOW() AND WEEKOFYEAR(o.time) = WEEKOFYEAR(NOW())-1 AND LENGTH(ol.companyName)>0");


$csv->addRow(
            array(
                'Bestellungen' => $dataCompanys['Bestellungen'],
                'Umsatz Bestellungen gesamt' => $dataCompanys['Umsatz'],
                'Premium Bestellungen' => $dataCompanysPremium['Bestellungen'],
                'Umsatz Premium Bestellungen' => $dataCompanysPremium['Umsatz'],
                'Bestellungen Firmen ohne Account' => $dataCompanysNotRegistered['Bestellungen']
                )
    );


$file = $csv->save();

$email = new Yourdelivery_Sender_Email();
$email->setSubject('Firmenbestellungen in der letzten Woche')
    ->setBodyHtml('Firmenbestellungen in der letzten Woche')
    ->addTo('gia@lieferando.de')
    ->addTo('spott@lieferando.de')
    ->addTo('ohrmann@lieferando.de');

$attachment = $email->createAttachment(
    file_get_contents($file),
    'text/comma-separated-values',
    Zend_Mime::DISPOSITION_ATTACHMENT,
    Zend_Mime::ENCODING_BASE64
);
$attachment->filename = 'company_orders_last_week-' . date("Y-m-d-H-i", time()).'csv';

if($email->send('system')){
    clog('info', 'Email was send');
}
else{
    clog('err', 'Sending Email failed');
}