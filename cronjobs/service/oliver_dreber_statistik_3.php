<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * statistik of all premium restaurants in a city per restaurant
 * mail must be send on first day of every month to Oliver Dreber
 * @author alex
 * @since 24.02.2011
 */
clog('info', 'sending premium restaurants statistik per restaurant to oliver dreber');

$db = Zend_Registry::get('dbAdapter');

$cities = array('Frankfurt am Main', 'Düsseldorf', 'München');

foreach ($cities as $city) {
    try {
        $orders = $db->fetchAll(
                "select
                    r.id as restaurantId,
                    r.name as restauranName,
                    o.total as bestellwert,
                    o.courierCost as kurierkosten,
                    o.courierDiscount as kurierdiscount,
                    DATE(o.time) as datum,
                    TIME(o.time) as uhrzeit,
                    o.payment as bezahlart,
                    (o.companyId is not null or LENGTH(ol.companyName)>0) as firmenkunde,
                    count(obm.id) as artikel
                    from orders o
                        join restaurants r on o.restaurantId=r.id
                        join orders_location ol on ol.orderId=o.id
                        join city c on c.plz=ol.plz
                        join orders_bucket_meals obm on obm.orderId=o.id
                            where
                                MONTH(date_sub(NOW(), interval 1 month))=MONTH(o.time) and
                                YEAR(date_sub(NOW(), interval 1 month))=YEAR(o.time) and
                                o.state>0 and
                                c.city = '" . $city . "' and
                                r.franchiseTypeId = 3
                                group by o.id;");
    } catch (Zend_Db_Statement_Exception $e) {
        clog('err', 'Error while fetching data');
        return;
    }

    $restaurants = array();

    foreach ($orders as $order) {
        if (!key_exists($order['restaurantId'], $restaurants)) {
            $restaurants[$order['restaurantId']] = array();
        }

        $restaurants[$order['restaurantId']][] = $order;
    }

    ksort($restaurants);

    $csv = new Default_Exporter_Csv();
    $csv->addCol('Info');
    $csv->addCol('Anzahl der Bestellungen');
    $csv->addCol('RestaurantId');
    $csv->addCol('Restaurant');
    $csv->addCol('Bestellwert');
    $csv->addCol('Kurierkosten');
    $csv->addCol('Kurierdiscount');
    $csv->addCol('Datum');
    $csv->addCol('Uhrzeit');
    $csv->addCol('Bezahlart');
    $csv->addCol('Firmenbestellung');
    $csv->addCol('Anzahl der Artikel');

    foreach ($restaurants as $rid => $restdata) {
        $restTotalSum = 0;
        $restCourierSum = 0;
        $restCourierDiscountSum = 0;
        $restCompSum = 0;
        $restOrdersCount = count($restdata);
        $mealsCountSum = 0;

        foreach ($restdata as $order) {
            $csv->addRow(
                    array(
                        'Info' => '',
                        'Anzahl der Bestellungen' => '1',
                        'RestaurantId' => $order['restaurantId'],
                        'Restaurant' => $order['restauranName'],
                        'Bestellwert' => intToPrice($order['bestellwert']),
                        'Kurierkosten' => intToPrice($order['kurierkosten']),
                        'Kurierdiscount' => intToPrice($order['kurierdiscount']),
                        'Datum' => $order['datum'],
                        'Uhrzeit' => $order['uhrzeit'],
                        'Bezahlart' => $order['bezahlart'],
                        'Firmenbestellung' => $order['firmenkunde'],
                        'Anzahl der Artikel' => $order['artikel'],
                    )
            );

            $restTotalSum += intval($order['bestellwert']);
            $restCourierSum += intval($order['kurierkosten']);
            $restCourierDiscountSum += intval($order['kurierdiscount']);
            $mealsCountSum += intval($order['artikel']);

            if ($order['firmenkunde'] == '1') {
                $restCompSum++;
            }
        }

        $csv->addRow(
                array(
                    'Info' => 'Summen/Restaurant #' . $rid,
                    'Anzahl der Bestellungen' => $restOrdersCount,
                    'RestaurantId' => '',
                    'Restaurant' => '',
                    'Bestellwert' => intToPrice($restTotalSum),
                    'Kurierkosten' => intToPrice($restCourierSum),
                    'Kurierdiscount' => intToPrice($restCourierDiscountSum),
                    'Datum' => '',
                    'Uhrzeit' => '',
                    'Bezahlart' => '',
                    'Firmenbestellung' => $restCompSum,
                    'Anzahl der Artikel' => $mealsCountSum
                )
        );

        $csv->addRow(
                array(
                    'Info' => '',
                    'Anzahl der Bestellungen' => '',
                    'RestaurantId' => '',
                    'Restaurant' => '',
                    'Bestellwert' => 'Durchsch. Bestellwert: ' . intToPrice(intval($restTotalSum / $restOrdersCount)),
                    'Kurierkosten' => '',
                    'Kurierdiscount' => '',
                    'Datum' => '',
                    'Uhrzeit' => '',
                    'Bezahlart' => '',
                    'Firmenbestellung' => round($restCompSum * 100 / $restOrdersCount, 2) . ' % aller Bst Sind Firmenbestellungen',
                    'Anzahl der Artikel' => ''
                )
        );

        $csv->addRow(
                array(
                    'Info' => '',
                    'Anzahl der Bestellungen' => '',
                    'RestaurantId' => '',
                    'Restaurant' => '',
                    'Bestellwert' => '',
                    'Kurierkosten' => '',
                    'Kurierdiscount' => '',
                    'Datum' => '',
                    'Uhrzeit' => '',
                    'Bezahlart' => '',
                    'Firmenbestellung' => '',
                    'Anzahl der Artikel' => ''
                )
        );
    }

    $file = $csv->save();

    // send file
    $email = new Yourdelivery_Sender_Email();
    $email->setBodyText('Premium Dienstleister in ' . $city . ', sortiert nach nach Dienstleister');
    $email->setSubject('Premium Dienstleister in ' . $city . ', Sheet 3');
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
}
?>