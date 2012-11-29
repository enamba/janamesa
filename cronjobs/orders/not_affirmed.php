<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

$table = new Yourdelivery_Model_DbTable_Order();
$orders = $table->select()->where('state=0 and mode!="canteen"')->query();

clog('debug', 'starting to check ' . count($orders) . ' order');
foreach ($orders as $order) {

    try {
        $order = new Yourdelivery_Model_Order($order['id']);

        if ($order->getMode() == "great") {
            $warning_time = 60 * 12;
            continue;
        } else {
            $warning_time = 5;
        }

        $now = time();
        $time = (integer) $order->getLastStateChange();

        $time_ellapsed = round(($now - $time) / 60);
        //this order has not been affirmed for 10 minutes
        if ($time_ellapsed > $warning_time) {
            clog('debug', sprintf('order #%s not affirmed since %d minutes, last state change on %s', $order->getId(), $time_ellapsed, date('d.m.Y H:i:s', $time)));
        } else {
            clog('debug', sprintf('order #%s not affirmed but last state change in %s minutes at %s', $order->getId(), $time_ellapsed, date('d.m.Y H:i:s', $time)));
        }
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        clog('crit', sprintf('Could not find order with id %s while checking states', $order['id']));
        continue;
    }
}
