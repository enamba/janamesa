<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * statistik of all premium restaurants with datza for customers
 * mail must be send on first day of every month to Oliver Dreber
 * @author alex
 * @since 24.02.2011
 */
clog('info', 'sending premium restaurants statistik with company customers data to oliver dreber');

$db = Zend_Registry::get('dbAdapter');

$cities = array('Frankfurt am Main', 'Düsseldorf', 'München');

foreach ($cities as $city) {

    try {
        $orders = $db->fetchAll(
                "select
                    r.id as restaurantId,
                    r.name as restauranName,
                    if(r.franchiseTypeId=3, 1, 0) as premium,
                    CONCAT(oc.prename, ' ', oc.`name`) as kunde,
                    ol.street as strasse,
                    ol.hausnr as hausnr,
                    ol.plz as plz,
                    c.city as stadt,
                    comp.name as firmenname,
                    ol.companyName as olfirmenname,
                    o.total as bestellwert,
                    o.courierCost as kurierkosten,
                    o.courierDiscount as kurierdiscount,
                    DATE(o.time) as datum,
                    TIME(o.time) as uhrzeit,
                    o.payment as bezahlart,
                    cp.description as zone,
                    count(obm.id) as artikel
                    from orders o
                        join restaurants r on o.restaurantId=r.id
                        join orders_location ol on ol.orderId=o.id
                        join orders_customer oc on oc.orderId=o.id
                        join city c on c.plz=ol.plz
                        join orders_bucket_meals obm on obm.orderId=o.id
                        left join companys comp on o.companyId=comp.id
                        left join courier_plz cp on cp.plz=ol.plz
                            where
                                MONTH(date_sub(NOW(), interval 1 month))=MONTH(o.time) and
                                YEAR(date_sub(NOW(), interval 1 month))=YEAR(o.time) and
                                o.state>0 and
                                c.city = '" . $city . "' and
                                (o.companyId is not null or LENGTH(ol.companyName)>0)
                                group by o.id
                                order by firmenname, olfirmenname, datum, uhrzeit;");
    } catch (Zend_Db_Statement_Exception $e) {
        clog('err', 'Error while fetching data');
        return;
    }

    $companys = array();

    foreach ($orders as $order) {
        if (!key_exists($order['firmenname'], $companys)) {
            $companys[$order['firmenname']] = array();
        }

        $companys[$order['firmenname']][] = $order;
    }

    ksort($companys);

    $csv = new Default_Exporter_Csv();
    $csv->addCol('Info');
    $csv->addCol('Anzahl der Bestellungen');
    $csv->addCol('Anzahl der Artikel');
    $csv->addCol('RestaurantId');
    $csv->addCol('Restaurant');
    $csv->addCol('Premium');
    $csv->addCol('Kunde');
    $csv->addCol('Strasse');
    $csv->addCol('HausNr');
    $csv->addCol('PLZ');
    $csv->addCol('Stadt');
    $csv->addCol('gehört zu Firmenkunde');
    $csv->addCol('Bestellwert');
    $csv->addCol('Kurierkosten');
    $csv->addCol('Kurierdiscount');
    $csv->addCol('Datum');
    $csv->addCol('Uhrzeit');
    $csv->addCol('Bezahlart');

    $totalSum = 0;
    $totalCourierSum = 0;
    $totalCourierDiscountSum = 0;
    $zone1 = 0;
    $zone2 = 0;
    $zone3 = 0;
    $bill = 0;
    $credit = 0;
    $paypal = 0;
    $ordersCount = count($orders);

    foreach ($companys as $compName => $compdata) {
        $compOrdersCount = count($compdata);
        $compTotalSum = 0;
        $compCourierSum = 0;
        $compCourierDiscountSum = 0;
        $mealsCountSum = 0;
        $premiumCount = 0;
        $premiumSum = 0;

        foreach ($compdata as $order) {
            $firmenname = '';
            if ((strlen($compName) > 0) && !is_null($compName)) {
                $firmenname = $compName;
            } else {
                $firmenname = $order['olfirmenname'] . " (aus Bestelladresse gelesen)";
            }

            $csv->addRow(
                    array(
                        'Info' => '',
                        'Anzahl der Bestellungen' => '1',
                        'Anzahl der Artikel' => $order['artikel'],
                        'RestaurantId' => $order['restaurantId'],
                        'Restaurant' => $order['restauranName'],
                        'Premium' => $order['premium'],
                        'Kunde' => $order['kunde'],
                        'Strasse' => $order['strasse'],
                        'HausNr' => $order['hausnr'],
                        'PLZ' => $order['plz'],
                        'Stadt' => $order['stadt'],
                        'gehört zu Firmenkunde' => $firmenname,
                        'Bestellwert' => intToPrice($order['bestellwert']),
                        'Kurierkosten' => intToPrice($order['kurierkosten']),
                        'Kurierdiscount' => intToPrice($order['kurierdiscount']),
                        'Datum' => $order['datum'],
                        'Uhrzeit' => $order['uhrzeit'],
                        'Bezahlart' => $order['bezahlart']
                    )
            );

            if (isset($order['premium']) && ($order['premium'] == 1)) {
                $premiumCount++;
                $premiumSum += $order['bestellwert'];
            }

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
            }

            $compTotalSum += intval($order['bestellwert']);
            $compCourierSum += intval($order['kurierkosten']);
            $compCourierDiscountSum += intval($order['kurierdiscount']);
            $mealsCountSum += intval($order['artikel']);
        }

        $csv->addRow(
                array(
                    'Info' => 'Summen/Firma #' . $compName,
                    'Anzahl der Bestellungen' => $compOrdersCount,
                    'Anzahl der Artikel' => $mealsCountSum,
                    'RestaurantId' => '',
                    'Restaurant' => 'Premium/Normal: ' . round(($premiumCount / $compOrdersCount) * 100, 2) . '% / ' . round((($compOrdersCount - $premiumCount) / $compOrdersCount) * 100, 2) . '%',
                    'Premium' => $premiumCount . '/' . ($compOrdersCount - $premiumCount),
                    'Kunde' => '',
                    'Strasse' => '',
                    'HausNr' => '',
                    'PLZ' => '',
                    'Stadt' => '',
                    'gehört zu Firmenkunde' => '',
                    'Bestellwert (alle/premium)' => intToPrice($compTotalSum) . '(' . intToPrice($premiumSum) . ' / ' . intToPrice($compTotalSum - $premiumSum) . ')',
                    'Kurierkosten' => intToPrice($compCourierSum),
                    'Kurierdiscount' => intToPrice($compCourierDiscountSum),
                    'Datum' => '',
                    'Uhrzeit' => '',
                    'Bezahlart' => ''
        ));

        $csv->addRow(
                array(
                    'Info' => '',
                    'Anzahl der Bestellungen' => '',
                    'Anzahl der Artikel' => '',
                    'RestaurantId' => '',
                    'Restaurant' => '',
                    'Premium' => '',
                    'Kunde' => '',
                    'Strasse' => '',
                    'HausNr' => '',
                    'PLZ' => '',
                    'Stadt' => '',
                    'gehört zu Firmenkunde' => '',
                    'Bestellwert' => '',
                    'Kurierkosten' => '',
                    'Kurierdiscount' => '',
                    'Datum' => '',
                    'Uhrzeit' => '',
                    'Bezahlart' => ''
        ));

        $csv->addRow(
                array(
                    'Info' => '',
                    'Anzahl der Bestellungen' => '',
                    'Anzahl der Artikel' => '',
                    'RestaurantId' => '',
                    'Restaurant' => '',
                    'Premium' => '',
                    'Kunde' => '',
                    'Strasse' => '',
                    'HausNr' => '',
                    'PLZ' => '',
                    'Stadt' => '',
                    'gehört zu Firmenkunde' => '',
                    'Bestellwert' => '',
                    'Kurierkosten' => '',
                    'Kurierdiscount' => '',
                    'Datum' => '',
                    'Uhrzeit' => '',
                    'Bezahlart' => ''
        ));
    }

    $csv->addRow(
            array(
                'Info' => 'Gesamt',
                'Anzahl der Bestellungen' => $ordersCount,
                'Anzahl der Artikel' => '',
                'RestaurantId' => '',
                'Restaurant' => '',
                'Premium' => '',
                'Kunde' => '',
                'Strasse' => '',
                'HausNr' => '',
                'PLZ' => '',
                'Stadt' => '',
                'gehört zu Firmenkunde' => '',
                'Bestellwert' => intToPrice($totalSum),
                'Kurierkosten' => intToPrice($totalCourierSum),
                'Kurierdiscount' => intToPrice($totalCourierDiscountSum),
                'Datum' => '',
                'Uhrzeit' => '',
                'Bezahlart' => ''
    ));

    $csv->addRow(
            array(
                'Info' => 'Durchschnitt',
                'Anzahl der Bestellungen' => '',
                'Anzahl der Artikel' => '',
                'RestaurantId' => '',
                'Restaurant' => '',
                'Premium' => '',
                'Kunde' => '',
                'Strasse' => '',
                'HausNr' => '',
                'PLZ' => '',
                'Stadt' => '',
                'gehört zu Firmenkunde' => '',
                'Bestellwert' => 'Durchschn. Bestellwert: ' . intToPrice(intval($totalSum / $ordersCount)),
                'Kurierkosten' => '',
                'Kurierdiscount' => '',
                'Datum' => '',
                'Uhrzeit' => '',
                'Bezahlart' => ''
    ));


    $csv->addRow(
            array(
                'Info' => '',
                'Anzahl der Bestellungen' => 'Anteil der Zone 1: ' . round(($zone1 / $ordersCount) * 100, 2) . ' %',
                'Anzahl der Artikel' => '',
                'RestaurantId' => '',
                'Restaurant' => '',
                'Premium' => '',
                'Kunde' => '',
                'Strasse' => '',
                'HausNr' => '',
                'PLZ' => '',
                'Stadt' => '',
                'gehört zu Firmenkunde' => '',
                'Bestellwert' => '',
                'Kurierkosten' => '',
                'Kurierdiscount' => '',
                'Datum' => '',
                'Uhrzeit' => '',
                'Bezahlart' => 'Bezahlung auf Rechnung: ' . round(($bill / $ordersCount) * 100, 2) . ' %'
    ));

    $csv->addRow(
            array(
                'Info' => '',
                'Anzahl der Bestellungen' => 'Anteil der Zone 2: ' . round(($zone2 / $ordersCount) * 100, 2) . ' %',
                'Anzahl der Artikel' => '',
                'RestaurantId' => '',
                'Restaurant' => '',
                'Premium' => '',
                'Kunde' => '',
                'Strasse' => '',
                'HausNr' => '',
                'PLZ' => '',
                'Stadt' => '',
                'gehört zu Firmenkunde' => '',
                'Bestellwert' => '',
                'Kurierkosten' => '',
                'Kurierdiscount' => '',
                'Datum' => '',
                'Uhrzeit' => '',
                'Bezahlart' => 'Bezahlung mit Creditkarte: ' . round(($credit / $ordersCount) * 100, 2) . ' %'
    ));

    $csv->addRow(
            array(
                'Info' => '',
                'Anzahl der Bestellungen' => 'Anteil der Zone 3: ' . round(($zone3 / $ordersCount) * 100, 2) . ' %',
                'Anzahl der Artikel' => '',
                'RestaurantId' => '',
                'Restaurant' => '',
                'Premium' => '',
                'Kunde' => '',
                'Strasse' => '',
                'HausNr' => '',
                'PLZ' => '',
                'Stadt' => '',
                'gehört zu Firmenkunde' => '',
                'Bestellwert' => '',
                'Kurierkosten' => '',
                'Kurierdiscount' => '',
                'Datum' => '',
                'Uhrzeit' => '',
                'Bezahlart' => 'Bezahlung mit Paypal: ' . round(($paypal / $ordersCount) * 100, 2) . ' %'
    ));


    $file = $csv->save();

    // send file
    $email = new Yourdelivery_Sender_Email();
    $email->setBodyText('Bestellungen der Firmenkunden bei Premium Dienstleister in ' . $city);
    $email->setSubject('Premium Dienstleister in ' . $city . ', Sheet 4');
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