<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * statistik of all bloomsburys restaurants in a city 
 * mail must be send on first day of every month to Oliver Dreber
 * @author alex
 * @since 29.09.2011
 */
clog('info', 'sending bloomsburys restaurants statistik to oliver dreber');

$db = Zend_Registry::get('dbAdapter');

$cities = array('Berlin', 'Hamburg');

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
                    count(obm.id) as artikel,
                    cp.description as zone
                    from orders o
                        join restaurants r on o.restaurantId=r.id
                        join orders_location ol on ol.orderId=o.id
                        join city c on c.plz=ol.plz
                        join orders_bucket_meals obm on obm.orderId=o.id
                        left join courier_plz cp on cp.plz=ol.plz
                            where
                                MONTH(date_sub(NOW(), interval 1 month))=MONTH(o.time) and
                                YEAR(date_sub(NOW(), interval 1 month))=YEAR(o.time) and
                                o.state>0 and
                                c.city = '" . $city . "' and
                                r.franchiseTypeId = 5
                                group by o.id order by o.time;");
    } catch (Zend_Db_Statement_Exception $e) {
        clog('err', 'Error while fetching data');
        return;
    }

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
    $csv->addCol('Anzahl bestellter Artikel');

    $totalSum = 0;
    $totalCourierSum = 0;
    $totalCourierDiscountSum = 0;
    $zone1 = 0;
    $zone2 = 0;
    $zone3 = 0;
    $bill = 0;
    $credit = 0;
    $paypal = 0;
    $ebanking = 0;
    $compSum = 0;
    $mealsCountSum = 0;
    $ordersCount = count($orders);
    $timeZone1 = 0;
    $timeZone2 = 0;

    foreach ($orders as $order) {
        $csv->addRow(
                array(
                    'Info' => '',
                    'Anzahl der Bestellung' => '',
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

        $totalSum += $order['bestellwert'];
        $totalCourierSum += $order['kurierkosten'];
        $totalCourierDiscountSum += $order['kurierdiscount'];

        if (strcmp($order['zone'], 'Zone I') == 0) {
            $zone1++;
        } else if (strcmp($order['zone'], 'Zone II') == 0) {
            $zone2++;
        } else if (strcmp($order['zone'], 'Zone III') == 0) {
            $zone3++;
        }

        if (strcmp($order['bezahlart'], 'bill') == 0) {
            $bill++;
        } else if (strcmp($order['bezahlart'], 'credit') == 0) {
            $credit++;
        } else if (strcmp($order['bezahlart'], 'paypal') == 0) {
            $paypal++;
        } else if (strcmp($order['bezahlart'], 'ebanking') == 0) {
            $ebanking++;
        }

        if ($order['firmenkunde'] == '1') {
            $compSum++;
        }

        $mealsCountSum += intval($order['artikel']);

        if (($order['uhrzeit'] > '11:30:00') && ($order['uhrzeit'] < '14:30:00')) {
            $timeZone1++;
        } else if (($order['uhrzeit'] > '17:30:00') && ($order['uhrzeit'] < '21:30:00')) {
            $timeZone2++;
        }
    }


    $csv->addRow(
            array(
                'Info' => 'Gesamt',
                'Anzahl der Bestellung' => $ordersCount,
                'RestaurantId' => '',
                'Restaurant' => '',
                'Bestellwert' => intToPrice($totalSum),
                'Kurierkosten' => intToPrice($totalCourierSum),
                'Kurierdiscount' => intToPrice($totalCourierDiscountSum),
                'Datum' => '',
                'Uhrzeit' => '',
                'Bezahlart' => '',
                'Firmenbestellung' => $compSum,
                'Anzahl der Artikel' => $mealsCountSum
    ));

    if ($ordersCount > 0) {
        $csv->addRow(
                array(
                    'Info' => 'Durchschnitt',
                    'Anzahl der Bestellung' => '',
                    'RestaurantId' => '',
                    'Restaurant' => '',
                    'Bestellwert' => 'Durchschn. Bestellwert: ' . intToPrice(intval($totalSum / $ordersCount)),
                    'Kurierkosten' => '',
                    'Kurierdiscount' => '',
                    'Datum' => '',
                    'Uhrzeit' => '',
                    'Bezahlart' => '',
                    'Firmenbestellung' => 'Anteil der Firmenbestellungen: ' . round(($compSum / $ordersCount) * 100, 2) . ' %',
                    'Anzahl der Artikel' => 'Durchschnittlicher Anzahl der Artikel pro Bestellung:' . round($mealsCountSum / $ordersCount, 2)
        ));


        $csv->addRow(
                array(
                    'Info' => '',
                    'Anzahl der Bestellung' => 'Anteil der Zone 1: ' . round(($zone1 / $ordersCount) * 100, 2) . ' %',
                    'RestaurantId' => '',
                    'Restaurant' => '',
                    'Bestellwert' => '',
                    'Kurierkosten' => '',
                    'Kurierdiscount' => '',
                    'Datum' => 'Bestellungen zwischen 11:30 und 14:30: ' . $timeZone1 . ' (' . round(($timeZone1 / $ordersCount) * 100, 2) . '%)',
                    'Uhrzeit' => '',
                    'Bezahlart' => 'Bezahlung auf Rechnung: ' . round(($bill / $ordersCount) * 100, 2) . ' %',
                    'Firmenbestellung' => '',
                    'Anzahl der Artikel' => ''
        ));

        $csv->addRow(
                array(
                    'Info' => '',
                    'Anzahl der Bestellung' => 'Anteil der Zone 2: ' . round(($zone2 / $ordersCount) * 100, 2) . ' %',
                    'RestaurantId' => '',
                    'Restaurant' => '',
                    'Bestellwert' => '',
                    'Kurierkosten' => '',
                    'Kurierdiscount' => '',
                    'Datum' => 'Bestellungen zwischen 17:30 und 21:30: ' . $timeZone2 . ' (' . round(($timeZone2 / $ordersCount) * 100, 2) . '%)',
                    'Uhrzeit' => '',
                    'Bezahlart' => 'Bezahlung mit Creditkarte: ' . round(($credit / $ordersCount) * 100, 2) . ' %',
                    'Firmenbestellung' => '',
                    'Anzahl der Artikel' => ''
        ));

        $csv->addRow(
                array(
                    'Info' => '',
                    'Anzahl der Bestellung' => 'Anteil der Zone 3: ' . round(($zone3 / $ordersCount) * 100, 2) . ' %',
                    'RestaurantId' => '',
                    'Restaurant' => '',
                    'Bestellwert' => '',
                    'Kurierkosten' => '',
                    'Kurierdiscount' => '',
                    'Datum' => '',
                    'Uhrzeit' => '',
                    'Bezahlart' => 'Bezahlung mit Paypal: ' . round(($paypal / $ordersCount) * 100, 2) . ' %',
                    'Firmenbestellung' => '',
                    'Anzahl der Artikel' => ''
        ));

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
                    'Bezahlart' => 'Bezahlung mit Ebanking: ' . round(($ebanking / $ordersCount) * 100, 2) . ' %',
                    'Firmenbestellung' => '',
                    'Anzahl der Artikel' => ''
        ));
    }


    $file = $csv->save();

    // send file
    $email = new Yourdelivery_Sender_Email();
    $email->setBodyText('Bloomsburys Dienstleister in ' . $city);
    $email->setSubject('Bloomsburys Dienstleister in ' . $city . ', Sheet 1');
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