<?php

/*
 * Cronjob should run once a day and refund orders marked as fraud 
 * @author dhahn
 * @since 20.09.2011
 */
require_once(realpath(dirname(__FILE__) . '/../base.php'));


$config = Zend_Registry::get('configuration');
$logger = new Yourdelivery_Log();
$file_logger = new Zend_Log_Writer_Stream(
                sprintf($config->logging->payment, date('d-m-Y'))
);
if (APPLICATION_ENV == "production") {
    $filter = new Zend_Log_Filter_Priority(Zend_Log::INFO);
    $logger->addFilter($filter);
}

$logger->addWriter($file_logger);

$db = Zend_Registry::get('dbAdapter');

$select = $db->select()->from(array("o" => 'orders'), array('o.id'))
                                     ->joinLeft(array("os" => 'order_status'), "o.id=os.orderId", array())
                                     ->where('os.status = -6')
                                     ->where('o.state = -6')
                                     ->where('TIMESTAMPDIFF(DAY,os.created, NOW()) < 1')
                                     ->where('o.payment=?', "paypal")
                                     ->group('o.id');

$orders = $db->query($select)->fetchAll();

$messages = array();
$messages[] = "Running Paypal Refund Script...";

foreach ($orders as $orderRow) {
    $order = new Yourdelivery_Model_Order($orderRow['id']);
    $messages[] = "Storniere Order Id: " . $orderRow['id'];
    Yourdelivery_Helpers_Payment::refundPaypal($order, $logger, $messages);
}

foreach ($messages as $message) {
    $logger->info($message);
}