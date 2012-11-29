<?php

/**
 * Description of Paypal
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 * @runTestsInSeparateProcesses
 */
class Default_Helpers_Fraud_PaypalTest extends Yourdelivery_Test {

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.06.2012
     */
    public function testIsLegitWhitelist() {

       
        list($payerId, $paypal_email) = $this->initValues();


        $orderId = $this->placeOrder(array('payment' => 'paypal'));

        $order = new Yourdelivery_Model_Order($orderId);

        //Whitelist Match true
        $this->assertTrue(Default_Helpers_Fraud_Paypal::isLegit($order, $payerId, $paypal_email));
        $this->assertEquals(Default_Helpers_Fraud_Paypal::getStatus(), -6);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.06.2012
     */
    public function testIsLegitBlacklist() {
        list($payerId, $paypal_email) = $this->initValues(Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_BLACKLIST);
        $orderId = $this->placeOrder(array('payment' => 'paypal'));
        $order = new Yourdelivery_Model_Order($orderId);
        //Blacklist Match true
        $this->assertFalse(Default_Helpers_Fraud_Paypal::isLegit($order, $payerId, $paypal_email));
        $this->assertEquals(Default_Helpers_Fraud_Paypal::getStatus(), -6);
        //Default No Match 

        $payerId = strtoupper(substr(Default_Helpers_Crypt::hash(time()), 0, 10));
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf(" INSERT INTO `paypal_transactions`
                    (`id` ,`orderId` ,`params` ,`response` ,`payerId` ,`created`) 
                     VALUES (NULL, %s,'','','%s', CURRENT_TIMESTAMP)", $orderId, $payerId);

        $db->query($sql);

        $this->assertTrue(Default_Helpers_Fraud_Paypal::isLegit($order, $payerId, ""));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.06.2012
     */
    public function testIsLegitOnceADay() {

        $payerId = Default_Helper::generateRandomString(10);
        $randString = Default_Helper::generateRandomString(20);
        $paypal_email = $randString . '@testmail.de';
        $orderId = $this->placeOrder(array('payment' => 'paypal'));
        $order = new Yourdelivery_Model_Order($orderId);
        //Test No Match If Yesterday

        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf("INSERT INTO `paypal_transactions`
                    (`id` ,`orderId` ,`params` ,`response` ,`payerId` ,`created`) 
                     VALUES (NULL, %s,'','','%s', SUBDATE(NOW(),1 ))", $orderId, $payerId);

        $db->query($sql);

        $this->assertTrue(Default_Helpers_Fraud_Paypal::isLegit($order, $payerId, $paypal_email));

        //Only Once a Day Match

        $sql = sprintf("INSERT INTO `paypal_transactions`
                    (`id` ,`orderId` ,`params` ,`response` ,`payerId` ,`created`) 
                     VALUES (NULL, %s,'','','%s', CURRENT_TIMESTAMP)", $orderId, $payerId);

        $db->query($sql);

        $orderId = $this->placeOrder(array('payment' => 'paypal'));
        
        $response = serialize(array('ACK' => 'Success'));
        
        $sql = sprintf("INSERT INTO `paypal_transactions`
                    (`id` ,`orderId` ,`params` ,`response` ,`payerId` ,`created`) 
                     VALUES (NULL, %s,'','%s','%s', CURRENT_TIMESTAMP)", $orderId, $response, $payerId);

        $db->query($sql);


        $this->assertFalse(Default_Helpers_Fraud_Paypal::isLegit($order, $payerId, $paypal_email));
        $this->assertEquals(Default_Helpers_Fraud_Paypal::getStatus(), -3);

        $db->delete('paypal_transactions', $db->quoteInto('orderId = ?', $orderId));
    }
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.06.2012
     */
    public function testIsLegitOnceADayWithFailures() {

        $payerId = Default_Helper::generateRandomString(10);
        $randString = Default_Helper::generateRandomString(20);
        $paypal_email = $randString . '@testmail.de';
        $orderId = $this->placeOrder(array('payment' => 'paypal'));
        $order = new Yourdelivery_Model_Order($orderId);
        //Test No Match If Yesterday

        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf("INSERT INTO `paypal_transactions`
                    (`id` ,`orderId` ,`params` ,`response` ,`payerId` ,`created`) 
                     VALUES (NULL, %s,'','','%s', SUBDATE(NOW(),1 ))", $orderId, $payerId);

        $db->query($sql);

        $this->assertTrue(Default_Helpers_Fraud_Paypal::isLegit($order, $payerId, $paypal_email));

        //Only Once a Day Match

        $sql = sprintf("INSERT INTO `paypal_transactions`
                    (`id` ,`orderId` ,`params` ,`response` ,`payerId` ,`created`) 
                     VALUES (NULL, %s,'','','%s', CURRENT_TIMESTAMP)", $orderId, $payerId);

        $db->query($sql);

        $orderId = $this->placeOrder(array('payment' => 'paypal'));

        $response = serialize(array('ACK' => 'Failure'));
        
        $sql = sprintf("INSERT INTO `paypal_transactions`
                    (`id` ,`orderId` ,`params` ,`response` ,`payerId` ,`created`) 
                     VALUES (NULL, %s,'','%s','%s', CURRENT_TIMESTAMP)", $orderId, $response, $payerId);

        $db->query($sql);


        $this->assertTrue(Default_Helpers_Fraud_Paypal::isLegit($order, $payerId, $paypal_email));        

        $db->delete('paypal_transactions', $db->quoteInto('orderId = ?', $orderId));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.06.2012
     */
    public function testCustomerWhitelist() {

        list($payerId, $paypal_email) = $this->initValues(Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_BLACKLIST);

        $customer = $this->getRandomCustomer();

        if ($customer->isWhitelist()) {
            $customer->setWhitelist(0);
            $customer->save();
        }

        $this->assertFalse($customer->isWhitelist());

        $orderId = $this->placeOrder(array('payment' => 'paypal', 'customer' => $customer));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertFalse(Default_Helpers_Fraud_Paypal::isLegit($order, $payerId, $paypal_email));

        $customer->setWhitelist(1);
        $customer->save();

        $order = new Yourdelivery_Model_Order($orderId);

        $this->assertTrue(Default_Helpers_Fraud_Paypal::isLegit($order, $payerId, $paypal_email));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.06.2012
     */
    public function testIsLegitBlacklistEmail() {

        list($payerId, $paypal_email) = $this->initValues(Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_BLACKLIST);
        $orderId = $this->placeOrder(array('payment' => 'paypal', 'customer' => $customer));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertFalse(Default_Helpers_Fraud_Paypal::isLegit($order, "", $paypal_email));
        $this->assertEquals(Default_Helpers_Fraud_Paypal::getStatus(), -6);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.06.2012
     */
    public function initValues($type = Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_WHITELIST) {
        Default_Helpers_Fraud_Paypal::reset();
        $payerId = Default_Helper::generateRandomString(10);
        $randString = Default_Helper::generateRandomString(20);
        $paypal_email = $randString . '@testmail.de';
        $blacklist = new Yourdelivery_Model_Support_Blacklist();

        $blacklist->setAdminId(1);
        $blacklist->setComment("Testing ...");
        $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_PAYPAL_PAYERID, $payerId, Yourdelivery_Model_Support_Blacklist::MATCHING_EXACT, $type);
        $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_PAYPAL_EMAIL, $paypal_email, Yourdelivery_Model_Support_Blacklist::MATCHING_EXACT, $type);
        $blacklist->save();


        return array($payerId, $paypal_email);
    }

}

?>
