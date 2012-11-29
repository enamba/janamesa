<?php

/**
 * Get orders associated with unregistered companies from xxx days ago
 * create a report and send it via email 
 *
 * @author alex
 * @since 28.02.2011
 * @param int from - days before today to start
 */
require_once(realpath(dirname(__FILE__) . '/../base.php'));


$options = getopt("f:");
if (isset($options['f'])) {
    $days = $options['f'];
} else {
    clog('error', 'no days were entered!');
    die();
}

$from = strtotime('-' . $days . ' days');
$until = time();

$orders = Yourdelivery_Statistics_Overallstats::getUnregisteredCompanyOrders($from);

define("N", chr(13) . chr(10));

$csv = new Default_Exporter_Csv();
$csv->addCol('orderId');
$csv->addCol('Bestellzeit');
$csv->addCol('Restaurant');
$csv->addCol('Gesamtbetrag');
$csv->addCol('Vorname');
$csv->addCol('Nachname');
$csv->addCol('Email-Adresse');
$csv->addCol('Telefon');
$csv->addCol('PLZ');
$csv->addCol('Strasse');
$csv->addCol('Nr');
$csv->addCol('Firma');
$csv->addCol('Kommentar');

$countOrders = 0;
foreach ($orders as $order) {

    $csv->addRow(
            array(
                'orderId' => $order->id,
                'Bestellzeit' => $order->time,
                'Restaurant' => str_replace(';', ' ', $order->rname . ',' . $order->rstreet . ',' . $order->rhausnr . ',' . $order->rplz . ',' . $order->rtel),
                'Gesamtbetrag' => $order->total,
                'Vorname' => str_replace(';', ' ', $order->prename),
                'Nachname' => str_replace(';', ' ', $order->name),
                'Email-Adresse' => str_replace(';', ' ', $order->email),
                'Telefon' => str_replace(';', ' ', $order->tel),
                'PLZ' => $order->plz,
                'Strasse' => str_replace(';', ' ', $order->street),
                'Nr' => str_replace(';', ' ', $order->hausnr),
                'Firma' => str_replace(';', ' ', $order->company),
                'Kommentar' => str_replace(N, '', str_replace(';', ' ', $order->comment))
            )
    );
    $countOrders++;
}

$file = $csv->save();

// send file to support

$email = new Yourdelivery_Sender_Email();
$email->setBodyText('Unregistrierte Firmenbestellungen (' . date("d.m.Y H:i", $from) . ' - ' . date("d.m.Y H:i", $until) . ')');
$email->setSubject('Unregistrierte Firmenbestellungen (' . date("d.m.Y H:i", $from) . ' - ' . date("d.m.Y H:i", $until) . ')');
$email->addTo('support@lieferando.de');
// Vertriebler FFM
$email->addTo('dreber@lieferando.de');
$email->addTo('jahn@lieferando.de');
$email->addTo('stark@lieferando.de');
$email->addTo('gerbig@lieferando.de');
$attachment = $email->createAttachment(
        file_get_contents($file), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
);
$attachment->filename = 'unregisteredcompanyorders-' . date("Y-m-d-H-i", $from) . '-' . date("Y-m-d-H-i", $until) . '.csv';

if ($email->send('system')) {
    clog('info', 'Email was send with Attachment "unregisteredcompanyorders-' . date("Y-m-d-H-i", $from) . '-' . date("Y-m-d-H-i", $until) . '.csv"');
} else {
    clog('err', 'Sending Email failed');
}
?>
