<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

$orderTable = new Yourdelivery_Model_DbTable_Order();
$orders = $orderTable->findByRestaurantId(12504);

$csv = new Default_Exporter_Csv();
$csv->addCols(array(
    'id',
    'time',
    'tax7',
    'tax19',
    'netto7',
    'netto19',
    'brutto',
    'payment'
));

foreach ($orders as $o) {
    
    $orderId = (integer) $o['id'];
    if ( $orderId <= 0 ){
        continue;
    }
    
    try {
        $order = new Yourdelivery_Model_Order($orderId);
        $csv->addRow(
                array(
                    'id' => $order->getId(),
                    'time' => date('d.m.Y H:i:s',$order->getTime()),
                    'tax7' => $order->getTax7(),
                    'tax19' => $order->getTax19(),
                    'netto7' => $order->getItem7(),
                    'netto19' =>  $order->getItem19(),
                    'brutto' => $order->getBucketTotal() + $order->getServiceDeliverCost(),
                    'payment' => $order->getPayment()
                )
        );
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        clog('err', 'could not find order by given id ' . $o['id']);
        continue;
    }
}

$csv->save();