<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

$db = Zend_Registry::get('dbAdapterReadOnly');
$count = 0;

$mailingTable = new Yourdelivery_Model_DbTable_Mailing_Optivo();

$mailings = $mailingTable->fetchAll("start < now() and end > now() and status=1");

clog('info', sprintf('Willkommensmail Api-Calls: started'));

foreach ($mailings as $m) {


    $mailing = new Yourdelivery_Model_Mailing_Optivo($m['id']);


    $citys = $mailing->getCitys();

    $selectedCitys = array();

    foreach ($citys as $city) {
        $selectedCitys[] = $city->getId();
    }

    
    $select = $db->select()
            ->from(array('o' => 'orders'), array('email' => 'oc.email', 'userName' => 'oc.prename', 'restName' => 'r.name'))
            ->join(array('oc' => 'orders_customer'), 'o.id=oc.orderId', array())
            ->join(array('r' => 'restaurants'), 'r.id=o.restaurantId', array())
            ->join(array('ol' => 'orders_location'), 'o.id = ol.orderId', array())
            ->where('time <= (now()-INTERVAL 2 HOUR)')
            ->where('time >= (now()-INTERVAL 4 HOUR)')
            ->where('o.state > 0');
            
    if($mailing->getInvertCity() == 1) {
        $select->where('ol.cityId NOT IN (?)', $selectedCitys);
    }else {
        $select->where('ol.cityId in (?)', $selectedCitys);
    }        
                         
    $orders = $db->fetchAll($select);
    
    $orderCount = $mailing->getOrderCountAsArray();


    foreach ($orders as $order) {
        $select = $db->select()
                ->from(array('oc' => 'orders_customer'), array('email' => 'oc.email', 'userName' => 'oc.prename', 'restaurantName' => 'r.name'))
                ->join(array('o' => 'orders'), 'o.id=oc.orderId', array())
                ->join(array('r' => 'restaurants'), 'r.id=o.restaurantId', array())
                ->where('oc.email = ?', $order['email'])
                ->having('count(oc.id) in (?)', $orderCount);
         
        $row = $db->fetchRow($select);
        if (isset($row['email'])) {
            $email = new Yourdelivery_Sender_Email_Optivo();
            $email->setbmRecipientId($order['email']);
            $email->setbmMailingId($mailing->getMailingId());
            
             if($mailing->hasParameter("UserPrename")) {          
                 $email->setUserPrename($order['userName']);
             }       
                    
             if($mailing->hasParameter("LastOrderServiceName")) {
                 $email->setLastOrderServiceName($order['restName']);
             }
                               
             
            if ($email->send('WELCOME_MAILS')) {
                clog('info', sprintf('Willkommensmail Api-Calls: send email to %s', $order['email']));
                $count++;
            }                       
        }
    }
}

clog('info', sprintf('Willkommensmail Api-Calls: sent %s Emails', $count));