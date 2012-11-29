<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

$db = Zend_Registry::get('dbAdapter');
$prepayments = $db->query('select id,time from orders where state=-5 and time < (NOW() - INTERVAL 60 MINUTE)');
foreach ($prepayments as $o) {
    try {
        $order = new Yourdelivery_Model_Order($o['id']);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::STORNO, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_STORNO_SIMPLE));
        clog('info',sprintf('PREPAYMENT CRONJOB: cancel order %d due to timeout', $o['id']));
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        clog('crit',sprintf('PREPAYMENT CRONJOB: could not find order with id %d', $o['id']));
    }
}