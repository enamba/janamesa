<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));


$db = Zend_Registry::get('dbAdapter');
$result = $db->fetchAll("select r.name,r.id,r.plz,restaurantId,avg(total) as total,count(*) from orders o 
                      inner join restaurants r on r.id=o.restaurantId 
                      where state>0 and mode='rest' and payment='credit' and time > date_sub(curdate(),INTERVAL 1 Month) group by restaurantId having total > 5000 and count(*) > 3");

$body = "";
foreach ($result as $r) {
    $body .= 'Dienstleister ' . $result['name'] . '/' . $result['id'] . 'sieht verdächtig aus' . "\n";
}

Yourdelivery_Sender_Email::quickSend('Verdächtige Kreditkarten Dienstleister', $body);