<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));
ini_set('memory_limit', '2048M');

$config = Zend_Registry::get('configuration');

$piwik = Yourdelivery_Model_Piwik_Tracker::getInstance();
$goalId = $piwik->createGoal('email_rating_open');

// get unrated orders of last 2 hours
$orders = Yourdelivery_Model_DbTable_Order::allUnrated(2);
foreach ($orders as $o) {
    // send reminder to registered AND unregistered users
    $emailAdd = IS_PRODUCTION ? $o->getOrigCustomer()->getEmail() : $config->testing->email;
    if (!is_null($emailAdd)) {

        // send out reminding email
        try {
            $email = new Yourdelivery_Sender_Email_Template('rating');
            $email->setSubject(__('Bewerte Deine Bestellung bei %s', $o->getService()->getName()));
            $email->assign('yesadviseorderlink', 'rate/' . $o->getHash() . '/' . md5(SALT . 'yes'));
            $email->assign('noadviseorderlink', 'rate/' . $o->getHash() . '/' . md5(SALT . 'no'));
            $email->addTo($emailAdd);
            $email->assign('goal', $goalId);
            $email->assign('order', $o);
            $email->send();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            clog('info', 'could not send reminding mail to ' . $emailAdd);
        }

        clog('info', 'orderId: ' . $o->getId() . ' send to email: ' . $emailAdd);
    }
}

if ($config->domain->base == 'lieferando.de') {
    // api call from optivo, after 33 hours
    $orders = Yourdelivery_Model_DbTable_Order::allUnrated(33);
    foreach ($orders as $order) {
        $email = IS_PRODUCTION ? urlencode($order->getOrigCustomer()->getEmail()) : urlencode($config->testing->email);
        $prename = urlencode($order->getOrigCustomer()->getPrename());
        $serviceName = urlencode($order->getService()->getName());
        $date = urlencode(date('d.m.Y', $order->getTime()));
        $link = urlencode(sprintf('http://%s/rate/%s/%s', $config->domain->base, $order->getHash(), md5(SALT . 'yes')));
        $trigger = sprintf('https://api.broadmail.de/http/form/1UXR6ED-1V46OJK-XEH1BFX/sendtransactionmail?bmRecipientId=%s&bmMailingId=4063925271&UserPrename=%s&LastOrderServiceName=%s&LastOrderDate=%s&RatingLastOrderLink=%s', $email, $prename, $serviceName, $date, $link);
        clog('info', sprintf('calling optivo trigger link: %s', $trigger));
        file_get_contents($trigger);
    }
}
