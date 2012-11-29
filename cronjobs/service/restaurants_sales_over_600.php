<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * statistik of all restaurants having in last month more than 600,00 total sales
 * mail must be send on first day of every month
 * @author Alex Vait
 * @since 16.08.2012
 */
clog('info', 'sending restaurants over 600,00 sales statistik');

$db = Zend_Registry::get('dbAdapterReadOnly');

    try {
        $orders = $db->fetchAll(
                "select
                    r.id as restaurantId,
                    r.name as restauranName,
                    concat(r.street, ' ' , r.hausnr) as adresse,
                    r.plz as plz,
                    c.city as stadt,
                    ROUND(SUM(o.total + o.serviceDeliverCost + o.courierCost - o.courierDiscount)/100, 2) as umsatz
                    from orders o
                        join restaurants r on o.restaurantId=r.id
                        join city c on c.id=r.cityId
                            where
                                MONTH(date_sub(NOW(), interval 1 month))=MONTH(o.time) and
                                YEAR(date_sub(NOW(), interval 1 month))=YEAR(o.time) and
                                o.state>0 and 
                                franchiseTypeId=2
                                group by r.id 
                                    having umsatz>600
                                        order by umsatz;
                ");
    } catch (Zend_Db_Statement_Exception $e) {
        clog('err', 'Error while fetching data');
        return;
    }

    $csv = new Default_Exporter_Csv();
    $csv->addCol('RestaurantId');
    $csv->addCol('Restaurant');
    $csv->addCol('Adresse');
    $csv->addCol('PLZ');
    $csv->addCol('Stadt');
    $csv->addCol('Umsatz');

    foreach ($orders as $order) {
        $csv->addRow(
            array(
                'RestaurantId' => $order['restaurantId'],
                'Restaurant' => $order['restauranName'],
                'Adresse' => $order['adresse'],
                'PLZ' => $order['plz'],
                'Stadt' => $order['stadt'],
                'Umsatz' => $order['umsatz']
            )
        );
    }

    $file = $csv->save();

    // send file
    $email = new Yourdelivery_Sender_Email();
    $email->setBodyText('Dienstleister mit Umsatz ab 600,00');
    $email->setSubject('Dienstleister mit Umsatz ab 600,00');
    $email->addTo('gia@lieferando.de');
    $email->addTo('hansen@lieferando.de');
    $email->addTo('ohrmann@lieferando.de');
    $email->addTo('pumo@lieferando.de');
    $email->addTo('oeler@lieferando.de');
    $email->addTo('mai@lieferando.de');

    $attachment = $email->createAttachment(
            file_get_contents($file), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
    );

    if ($email->send('system')) {
        clog('info', 'Email was send');
    } else {
        clog('err', 'Sending Email failed');
}
?>