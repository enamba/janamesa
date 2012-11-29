<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * send email with all changes of restaurants statis made yesterday
 * run every day early, 6 AM or so
 * @author alex
 * @since 19.01.2011
 */
clog('info', 'retrieving all changed made on restaurants statis yesterday');

$changes = Yourdelivery_Model_DbTable_Restaurant_Notepad::getAllChangesOfYesterday();
if (is_null($changes)) {
    clog('info', 'no changed were made on restaurants yesterday');
    return;
}

$emailBody = "<html><title>Restaurants Statis für den " . date("%d.%m-%y", strtotime('yesterday')) . "</title>";
$emailBody .= "<table>";
$emailBody .= "<tr>";

$emailBody .= "<td width='10%'>ID</td>";
$emailBody .= "<td width='20%'>Restaurant</td>";
$emailBody .= "<td width='40%'>Kommentar</td>";
$emailBody .= "<td width='10%'>Vom</td>";
$emailBody .= "<td width='20%'>Am</td>";
$emailBody .= "</tr>";

foreach ($changes as $c) {
    if (intval($c['masterAdmin']) == 1) {
        $user = $c['aName'] . " (" . $c['aEmail'] . ")";
    } else {
        $user = $c['cPrename'] . " " . $c['cName'] . " (" . $c['cEmail'] . ")";
    }

    $emailBody .= "<tr>";
    $emailBody .= "<td>" . $c['restaurantId'] . "</td>";
    $emailBody .= "<td>" . $c['restaurantName'] . "</td>";
    $emailBody .= "<td>" . str_replace('\\', '', $c['comment']) . "</td>";
    $emailBody .= "<td>" . $user . "</td>";
    $emailBody .= "<td>" . $c['time'] . "</td>";
    $emailBody .= "</tr>";
}

$emailBody .= "</table>";
$emailBody .= "</html>";

$email = new Yourdelivery_Sender_Email();
$email->setSubject('Änderungen an Restaurant Statis')
        ->setBodyHtml($emailBody)
        ->addTo('gia@lieferando.de')
        ->addTo('ohrmann@lieferando.de')    
        ->addTo('liss@lieferando.de');

$config = Zend_Registry::get('configuration');

if($config->domain->base == 'lieferando.de') {
      $email->addTo('simon@lieferando.de');
}

clog('info', $emailBody);

if ($email->send('system')) {
    clog('info', 'Email was send');
} else {
    clog('err', 'Sending Email failed');
}