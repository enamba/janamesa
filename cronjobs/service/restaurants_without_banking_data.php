<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * List of all restaurants where at least one order was made, but some banking data is missing
 * (ktoName, ktoNr, ktoBlz)
 * mail must be send every 23th of every month to Vanessa
 * -> UPDATE (23.08.2011): Vanessa isn't in our company any longer -> send to buchhaltung@lieferando.de
 * @author alex
 * @since 19.01.2011
 */
clog('info', 'checking restaurants where some banking data is missing, e.g. ktoName, ktoNr, ktoBlz');

$message = 'null;';

foreach (Yourdelivery_Model_DbTable_Restaurant::getRestaurantsWithMissingBankingData() as $data) {
    $message .= $data['restaurantName'] . " (#" . $data['restaurantId'] . ") : \t\tBestellungen: " . $data['ordersCount'] . "\n";
}


if (is_null($message)) {
    clog('info', 'no restaurants found without banking data - not sending any email');
    return;
}

$body = "Restaurants mit unvollständigen Kontodaten:\n" . $message;

$email = new Yourdelivery_Sender_Email();
$email->setSubject('Restaurants mit unvollständigen Kontodaten');
$email->setBodyText($body);
$email->addTo('gia@lieferando.de')
        ->addTo('ohrmann@lieferando.de');

if ($email->send('system')) {
    clog('info', 'Email was send');
} else {
    clog('err', 'Sending Email failed');
}
