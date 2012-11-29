<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * statistik of all premium restaurants in a city per week and days
 * mail must be send on first day of every month to Oliver Dreber
 * @author alex
 * @since 24.02.2011
 */
clog('info', 'sending premium restaurants statistik per day and week to oliver dreber');

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
                    (o.companyId is not null or LENGTH(ol.companyName)>0) as firmenkunde
                    from orders o
                        join restaurants r on o.restaurantId=r.id
                        join orders_location ol on ol.orderId=o.id
                        join city c on c.plz=ol.plz
                            where
                                MONTH(date_sub(NOW(), interval 1 month))=MONTH(o.time) and
                                YEAR(date_sub(NOW(), interval 1 month))=YEAR(o.time) and
                                o.state>0 and
                                c.city = '" . $city . "' and
                                r.franchiseTypeId = 3;");
    } catch (Zend_Db_Statement_Exception $e) {
        clog('err', 'Error while fetching data');
        return;
    }

    $ordersperweek = array();

    foreach ($orders as $order) {
        $week = date('W', strtotime($order['datum']));

        if (!key_exists($week, $ordersperweek)) {
            $ordersperweek[$week] = array();
        }

        if (!key_exists($order['datum'], $ordersperweek[$week])) {
            $ordersperweek[$week][$order['datum']] = array();
        }

        $ordersperweek[$week][$order['datum']][] = $order;
    }

    ksort($ordersperweek);

    $csv = new Default_Exporter_Csv();
    $csv->addCol('Info');
    $csv->addCol('Anzahl der Bestellung');
    $csv->addCol('RestaurantId');
    $csv->addCol('Restaurant');
    $csv->addCol('Bestellwert');
    $csv->addCol('Kurierkosten');
    $csv->addCol('Kurierdiscount');
    $csv->addCol('Datum');
    $csv->addCol('Uhrzeit');
    $csv->addCol('Bezahlart');
    $csv->addCol('Firmenbestellung');

    foreach ($ordersperweek as $week => $weekdata) {
        $weekTotalSum = 0;
        $weekCourierSum = 0;
        $weekCourierDiscountSum = 0;
        $weekCompSum = 0;
        $weekOrdersCount = 0;

        foreach ($weekdata as $day => $dayorders) {
            $dayTotalSum = 0;
            $dayCourierSum = 0;
            $dayCourierDiscountSum = 0;
            $dayCompSum = 0;
            $dayOrdersCount = count($dayorders);
            $weekOrdersCount += $dayOrdersCount;

            foreach ($dayorders as $order) {
                $csv->addRow(
                        array(
                            'Info' => '',
                            'Anzahl der Bestellung' => '1',
                            'RestaurantId' => $order['restaurantId'],
                            'Restaurant' => $order['restauranName'],
                            'Bestellwert' => intToPrice($order['bestellwert']),
                            'Kurierkosten' => intToPrice($order['kurierkosten']),
                            'Kurierdiscount' => intToPrice($order['kurierdiscount']),
                            'Datum' => $order['datum'],
                            'Uhrzeit' => $order['uhrzeit'],
                            'Bezahlart' => $order['bezahlart'],
                            'Firmenbestellung' => $order['firmenkunde']
                        )
                );

                $dayTotalSum += intval($order['bestellwert']);
                $weekTotalSum += intval($order['bestellwert']);

                $dayCourierSum += intval($order['kurierkosten']);
                $weekCourierSum += intval($order['kurierkosten']);

                $dayCourierDiscountSum += intval($order['kurierdiscount']);
                $weekCourierDiscountSum += intval($order['kurierdiscount']);

                if ($order['firmenkunde'] == '1') {
                    $dayCompSum++;
                    $weekCompSum++;
                }
            }

            $csv->addRow(
                    array(
                        'Info' => 'Summen/Tag ' . date('d.m.Y', strtotime($day)),
                        'Anzahl der Bestellung' => $dayOrdersCount,
                        'RestaurantId' => '',
                        'Restaurant' => '',
                        'Bestellwert' => intToPrice($dayTotalSum),
                        'Kurierkosten' => intToPrice($dayCourierSum),
                        'Kurierdiscount' => intToPrice($dayCourierDiscountSum),
                        'Datum' => '',
                        'Uhrzeit' => '',
                        'Bezahlart' => '',
                        'Firmenbestellung' => $dayCompSum
                    )
            );
            $csv->addRow(
                    array(
                        'Info' => '',
                        'Anzahl der Bestellung' => '',
                        'RestaurantId' => '',
                        'Restaurant' => '',
                        'Bestellwert' => '',
                        'Kurierkosten' => '',
                        'Kurierdiscount' => '',
                        'Datum' => '',
                        'Uhrzeit' => '',
                        'Bezahlart' => '',
                        'Firmenbestellung' => ''
                    )
            );
        }

        $csv->addRow(
                array(
                    'Info' => 'Summen/KW ' . $week,
                    'Anzahl der Bestellung' => $weekOrdersCount,
                    'RestaurantId' => '',
                    'Restaurant' => '',
                    'Bestellwert' => intToPrice($weekTotalSum),
                    'Kurierkosten' => intToPrice($weekCourierSum),
                    'Kurierdiscount' => intToPrice($weekCourierDiscountSum),
                    'Datum' => '',
                    'Uhrzeit' => '',
                    'Bezahlart' => '',
                    'Firmenbestellung' => $weekCompSum
                )
        );
        $csv->addRow(
                array(
                    'Info' => '',
                    'Anzahl der Bestellung' => '',
                    'RestaurantId' => '',
                    'Restaurant' => '',
                    'Bestellwert' => '',
                    'Kurierkosten' => '',
                    'Kurierdiscount' => '',
                    'Datum' => '',
                    'Uhrzeit' => '',
                    'Bezahlart' => '',
                    'Firmenbestellung' => ''
                )
        );
        $csv->addRow(
                array(
                    'Info' => '',
                    'Anzahl der Bestellung' => '',
                    'RestaurantId' => '',
                    'Restaurant' => '',
                    'Bestellwert' => '',
                    'Kurierkosten' => '',
                    'Kurierdiscount' => '',
                    'Datum' => '',
                    'Uhrzeit' => '',
                    'Bezahlart' => '',
                    'Firmenbestellung' => ''
                )
        );
    }

    $file = $csv->save();

    // send file
    $email = new Yourdelivery_Sender_Email();
    $email->setBodyText('Premium Dienstleister in ' . $city . ', sortiert nach Tagen und Wochen');
    $email->setSubject('Premium Dienstleister in ' . $city . ', Sheet 2');
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