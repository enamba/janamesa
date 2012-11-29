<?php

/**
 * @author Allen Frank <frank@lieferando.de>
 * @since 02.09.2011
 * @ticket YD-2730
 */
require_once(realpath(dirname(__FILE__) . '/../base.php'));

$db = Zend_Registry::get('dbAdapterReadOnly');
$count = 0;

clog('info', sprintf('Hannover-Aktion: started'));

$selectedPlz = array(
    30159, 30161, 30163, 30165, 30167, 30169, 30171, 30173, 30175, 30177, 30179, 30419, 30449, 30451, 30453,
    30455, 30457, 30459, 30519, 30521, 30539, 30559, 30625, 30627, 30629, 30655, 30657, 30659, 30669);

$select = $db->select()
        ->from(array('o' => 'orders'), array('email' => 'oc.email', 'userName' => 'oc.prename', 'restName' => 'r.name'))
        ->join(array('oc' => 'orders_customer'), 'o.id=oc.orderId', array())
        ->join(array('r' => 'restaurants'), 'r.id=o.restaurantId', array())
        ->join(array('ol' => 'orders_location'), 'o.id = ol.orderId', array())
        ->where('time <= (now()-INTERVAL 2 HOUR)')
        ->where('time >= (now()-INTERVAL 4 HOUR)')
        ->where('o.state > 0')
        ->where('ol.plz in (?)', $selectedPlz);
$orders = $db->fetchAll($select);

foreach ($orders as $order) {
    $select = $db->select()
            ->from(array('oc' => 'orders_customer'), array('email' => 'oc.email', 'userName' => 'oc.prename', 'restaurantName' => 'r.name'))
            ->join(array('o' => 'orders'), 'o.id=oc.orderId', array())
            ->join(array('r' => 'restaurants'), 'r.id=o.restaurantId', array())
            ->where('oc.email = ?', $order['email'])
            ->having('count(oc.id)=1');
    
    $row = $db->fetchRow($select);     
    if (isset($row['email'])) {
        $email = new Yourdelivery_Sender_Email_Optivo();
        $email->setbmRecipientId($order['email'])
                ->setUserPrename($order['userName'])
                ->setLastOrderServiceName($order['restName']);

        if ($email->send('HANNOVER_AKTION')) {
            clog('info', sprintf('Hannover-Aktion: send email to %s', $order['email']));
            $count++;
        }
    }
}

clog('info', sprintf('Hannover-Aktion: sent %s Emails', $count));



