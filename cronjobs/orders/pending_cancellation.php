<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

// We wait until order state will change no longer than value defined below [min.]
define('TIMEOUT_DELAY', 10);

$logger = Zend_Registry::get('logger');
$table = new Yourdelivery_Model_DbTable_Order();
$orders = $table->select()->from($table, 'id')
    ->where('state = 0')
    ->where('mode = ?', 'rest')
    ->where('DATE_ADD(time, INTERVAL ? MINUTE) < NOW()', TIMEOUT_DELAY)
    ->query();

clog('debug', sprintf(
    'Starting to cancel %d order(s) pending more than %d minutes',
    count($orders), TIMEOUT_DELAY
));
foreach ($orders as $rawOrder) {
    try {
        $orderId = $rawOrder['id'];
        $order = new Yourdelivery_Model_Order($orderId);
        // Setting order as cancelled
        $order->setStatus(Yourdelivery_Model_Order_Abstract::STORNO,
                new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_STORNO_SIMPLE)
               );
        $order->save();
        // Reverting payment operation (if any)
        $payment = $order->getPayment();
        $paymentComment = __b("Bestellung wurde erfolgreich storniert");
        $paymentMessages = array();
        switch ($payment) {
            case 'paypal': Yourdelivery_Helpers_Payment::refundPaypal($order, $logger, array($paymentComment));
                break;
            case 'ebanking': Yourdelivery_Helpers_Payment::refundEbanking($order, $logger, array($paymentComment), 'Order cancelled due to a timeout');
                break;
            case 'credit': Yourdelivery_Helpers_Payment::refundCredit($order, $logger, array($paymentComment));
                break;
        }
        // Notifying the customer
        $order->sendStornoEmailToUser();

        clog('warn', sprintf(
            'Timeouted order with id %s has been cancelled due to %d minute(s) timeout',
             $rawOrder['id'], TIMEOUT_DELAY
        ));
    } catch (Yourdelivery_Exception_Database_Inconsistency $ex) {
        clog('crit', sprintf(
            'Could not find order with id: %d to be cancelled due to %d minute(s) timeout',
             $rawOrder['id'], TIMEOUT_DELAY
        ));
    }
}
