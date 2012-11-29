<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

$orders = new Yourdelivery_Model_DbTable_Order();
$unaffirmed = $orders->select()->where('mode="great" and state=1 and DATE_ADD(deliverTime, INTERVAL 7 DAY) > NOW()')->query();
$list = "<html><head /><body>";
foreach($unaffirmed as $u){
    $orderId = $u['id'];
    $list .= "<p>Bestellung #$orderId hat noch kein Pfand eingetragen bekommen</p>";
    clog('warn',sprintf('found unaffirmed great order, notify support'));
}
$list .= "</body></html>";
Yourdelivery_sender_Email::warning($list);
