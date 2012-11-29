<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

$recipients = array(
    'friesen@lieferando.de',
    'gia@lieferando.de',
    'pumo@lieferando.de'
    );


$db = Zend_Registry::get('dbAdapterReadOnly');

// first mail
$select = "SELECT 
    r.id,
    r.name,
    r.created,
    (SELECT 
            name
        from
            admin_access_users aau
                left join
            admin_access_tracking aat ON aat.adminId = aau.id
        where
            aat.modelType = 'service' and aat.action = 'service_create' and aat.modelId = r.id
        LIMIT 1) as ersteller,
    CONCAT(s.name, ' ', s.prename) as vertriebler,
    rs.status,
    COUNT(o.id) as Bestellanzahl,
    count(if(o.state = - 2, o.id, null)) as stornierte,
    count(if(o.state > 0, o.id, null)) as affirmed_delivered,
    ROUND(SUM(if(o.state > 0,
                o.total + o.serviceDeliverCost + o.courierCost + o.charge - o.discountAmount - o.courierDiscount,
                0)) / 100,
            2) as umsatz
FROM
    restaurants r
        left join
    salesperson_restaurant sr ON r.id = sr.restaurantId
        left join
    salespersons s ON sr.salespersonId = s.id
        left join
    orders o ON o.restaurantId = r.id
        left join 
    restaurant_statis rs ON r.status = rs.id
    
where
    r.franchiseTypeId = 2 and r.deleted = 0 
group by r.id
order by id asc";

$results = $db->fetchAll($select);


$csv = new Default_Exporter_Csv();
$csv->addCol('Id');
$csv->addCol('Restaurant');
$csv->addCol('erstellt');
$csv->addCol('Ersteller');
$csv->addCol('Vertriebler');
$csv->addCol('Status');
$csv->addCol('Bestellanzahl');
$csv->addCol('stornierte');
$csv->addCol('bestätigte');
$csv->addCol('Umsatz');


foreach($results as $d) {
    
    $row = array(
            'Id' => $d['id'],
            'Restaurant' => $d['name'],
            'erstellt' => $d['created'],
            'Ersteller' => $d['ersteller'],
            'Vertriebler' => $d['vertriebler'],
            'Status' => $d['status'],
            'Bestellanzahl' => $d['Bestellanzahl'],
            'stornierte' => $d['stornierte'],
            'bestätigte' => $d['affirmed_delivered'],
            'Umsatz' => $d['umsatz']
        );
    
      $csv->addRow($row);
    
}


$file = $csv->save();

if (IS_PRODUCTION) {
    $email = new Yourdelivery_Sender_Email();
    $email->setSubject('Restaurants ohne Vertrag');
    $email->setBodyText('Liste aller Restaurants ohne Vertrag ist im Anhang');

    $email->addTo($recipients);

    $attachment = $email->createAttachment(
            file_get_contents($file), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
    );
    $attachment->filename = 'restaurants_ov_' . date("Y-m-d-H-i", time()).'.csv';

    if ($email->send('system')) {
        clog('info', 'Email was send');
    } else {
        clog('err', 'Sending Email failed');
    }
}

//second mail
$select= "SELECT r.id, r.name, r.street, r.hausnr,r.plz, c.city,ROUND(sum(o.total + o.serviceDeliverCost + o.courierCost)/100,2) as umsatz FROM restaurants r
left join orders o on r.id = o.restaurantId
left join city c on r.cityId = c.id
where franchiseTypeId = 2  and o.state > 0
group by r.id
having umsatz > 300
;";


$results = $db->fetchAll($select);


$csv = new Default_Exporter_Csv();
$csv->addCol('Id');
$csv->addCol('Restaurant');
$csv->addCol('Strasse');
$csv->addCol('Hausnr');
$csv->addCol('Plz');
$csv->addCol('Stadt');
$csv->addCol('Umsatz');

foreach($results as $d) {
    
    $row = array(
            'Id' => $d['id'],
            'Restaurant' => $d['name'],
            'Strasse' => $d['street'],
            'Hausnr' => $d['hausnr'],
            'Plz' => $d['plz'],
            'Stadt' => $d['city'],           
            'Umsatz' => $d['umsatz']
        );
    
      $csv->addRow($row);
    
}

$file = $csv->save();

if (IS_PRODUCTION) {
    $email = new Yourdelivery_Sender_Email();
    $email->setSubject('Restaurants ohne Vertrag mit Umsatz > 300');
    $email->setBodyText('Liste aller Restaurants ohne Vertrag mit Umsatz > 300 ist im Anhang');

    $email->addTo($recipients);

    $attachment = $email->createAttachment(
            file_get_contents($file), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
    );
    $attachment->filename = 'restaurants_ov_300' . date("Y-m-d-H-i", time()).'.csv';

    if ($email->send('system')) {
        clog('info', 'Email was send');
    } else {
        clog('err', 'Sending Email failed');
    }
}