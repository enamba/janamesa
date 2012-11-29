<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 18.06.2012
 * @runTestsInSeparateProcesses
 */
class Default_Helpers_Fraud_OrderTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012 
     */
    public function setUp() {
        
        parent::setUp();
        
        $db = Zend_Registry::get('dbAdapter');
        $db->query('TRUNCATE blacklist');
        $db->query('TRUNCATE blacklist_values');
        $db->query('TRUNCATE blacklist_matching');
        
        Yourdelivery_Model_Support_Blacklist::flushList();
        
        $this->config->payment->bar->max = 30000;
        $this->config->payment->credit->max = 30000;
        $this->config->payment->paypal->max = 30000;
    }
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param type $payment 
     */
    public function setMaxPayment($payment){
        $this->config->payment->bar->max = $payment;
        $this->config->payment->credit->max = $payment;
        $this->config->payment->paypal->max = $payment;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.06.2012
     */
    public function testBucketBar() {
        
        $orderId = $this->placeOrder(array('payment' => "bar", 'checkForFraud' => false, 'totalBelow' => 25000));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->config->payment->bar->max = $order->getAbsTotal()+10;
        $this->assertFalse(Default_Helpers_Fraud_Order::detect($order), $order->getAbsTotal());
        
        $this->config->payment->bar->max = 5;
        $this->assertTrue(Default_Helpers_Fraud_Order::detect($order));
        $state = $order->getLastState();
        $this->assertEquals($state['status'], Yourdelivery_Model_Order::FAKE);
        
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.06.2012
     */
    public function testBucketCredit() {
        
        $orderId = $this->placeOrder(array('payment' => "credit", 'checkForFraud' => false, 'totalBelow' => 25000));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->config->payment->credit->max = $order->getAbsTotal()+10;
        $this->assertFalse(Default_Helpers_Fraud_Order::detect($order));
        
        $this->config->payment->credit->max = 5;
        $this->assertTrue(Default_Helpers_Fraud_Order::detect($order));
        $state = $order->getLastState();
        $this->assertEquals($state['status'], Yourdelivery_Model_Order::FAKE);
        
        $order->setPayment("ebanking");
        $this->assertFalse(Default_Helpers_Fraud_Order::detect($order));
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.06.2012
     */
    public function testBucketPaypal() {
        
        $orderId = $this->placeOrder(array('payment' => "paypal", 'checkForFraud' => false, 'totalBelow' => 25000));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->config->payment->paypal->max = $order->getAbsTotal()+10;
        $this->assertFalse(Default_Helpers_Fraud_Order::detect($order));
        
        $this->config->payment->paypal->max = 5;
        $this->assertTrue(Default_Helpers_Fraud_Order::detect($order));
        $state = $order->getLastState();
        $this->assertEquals($state['status'], Yourdelivery_Model_Order::FAKE);
        
        $order->setPayment("bill");
        $this->assertFalse(Default_Helpers_Fraud_Order::detect($order));
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     */
    public function testDetectAddress() {
        
        $orderId = $this->placeOrder(array('payment' => "bar", 'checkForFraud' => false, 'totalBelow' => 25000));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertFalse(Default_Helpers_Fraud_Order::detect($order));
        $orderLocation = $order->getLocation();
        
        $blacklist = new Yourdelivery_Model_Support_Blacklist();
        $value = $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_ADDRESS, 
            $orderLocation->getStreet() . " " . 
            $orderLocation->getHausnr() . " " . 
            $orderLocation->getPlz() . " " . 
            $orderLocation->getCity()->getCity());
        $blacklist->save();
        $this->assertTrue(Default_Helpers_Fraud_Order::detect($order));
        
        $state = $order->getLastState();
        $this->assertEquals($state['status'], $value->getBehaviourToOrderState());
        $this->assertTrue(strpos($state['comment'], "'" . $value->getType() . "'") !== false);
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     */
    public function testDetectCompany() {
        
        $orderId = $this->placeOrder(array('payment' => "bar", 'checkForFraud' => false));
        $order = new Yourdelivery_Model_Order($orderId);
        
        // be sure to not mark order as fraud because of too high order amount
        while($order->getAbsTotal() >= 10000){
            $orderId = $this->placeOrder(array('payment' => "bar", 'checkForFraud' => false, 'totalBelow' => 10000));
            $order = new Yourdelivery_Model_Order($orderId);
        }
        
        $this->assertFalse(Default_Helpers_Fraud_Order::detect($order), Default_Helpers_Log::getLastLog('log', 10));
        $orderLocation = $order->getLocation();
        
        $blacklist = new Yourdelivery_Model_Support_Blacklist();
        $value = $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_COMPANY, $orderLocation->getCompanyName());
        $blacklist->save();
        $this->assertTrue(Default_Helpers_Fraud_Order::detect($order));
        
        $state = $order->getLastState();
        $this->assertEquals($state['status'], $value->getBehaviourToOrderState());
        $this->assertTrue(strpos($state['comment'], "'" . $value->getType() . "'") !== false);
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     */
    public function testDetectCustomer() {
        
        $orderId = $this->placeOrder(array('payment' => "bar", 'checkForFraud' => false,  'totalBelow' => 25000));
        $order = new Yourdelivery_Model_Order($orderId);        
        $this->setMaxPayment($order->getAbsTotal()+10);        
        $this->assertFalse(Default_Helpers_Fraud_Order::detect($order),Default_Helpers_Log::getLastLog());
        $orderCustomer = $order->getCustomer();
        
        $blacklist = new Yourdelivery_Model_Support_Blacklist();
        $value = $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_CUSTOMER, $orderCustomer->getFullname());
        $blacklist->save();
        $this->assertTrue(Default_Helpers_Fraud_Order::detect($order));
        
        $state = $order->getLastState();
        $this->assertEquals($state['status'], $value->getBehaviourToOrderState());
        $this->assertTrue(strpos($state['comment'], "'" . $value->getType() . "'") !== false);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     */
    public function testDetectTel() {
        
        $orderId = $this->placeOrder(array('payment' => "bar", 'checkForFraud' => false,  'totalBelow' => 25000));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->setMaxPayment($order->getAbsTotal()+10);        
        $this->assertFalse(Default_Helpers_Fraud_Order::detect($order));
        $orderLocation = $order->getLocation();
        
        $blacklist = new Yourdelivery_Model_Support_Blacklist();
        $value = $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_TEL, $orderLocation->getTel());
        $blacklist->save();
        $this->assertTrue(Default_Helpers_Fraud_Order::detect($order));
        
        $state = $order->getLastState();
        $this->assertEquals($state['status'], $value->getBehaviourToOrderState());
        $this->assertTrue(strpos($state['comment'], "'" . $value->getType() . "'") !== false);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     */
    public function testDetectUuid() {
        
        $orderId = $this->placeOrder(array('payment' => "bar", 'checkForFraud' => false,  'totalBelow' => 25000));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->setMaxPayment($order->getAbsTotal()+10);        
        $this->assertFalse(Default_Helpers_Fraud_Order::detect($order));
        
        $order->setUuid(sha1("Amber Heard"));
        $order->save();
        
        $blacklist = new Yourdelivery_Model_Support_Blacklist();
        $value = $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_UUID, $order->getUuid());
        $blacklist->save();
        $this->assertTrue(Default_Helpers_Fraud_Order::detect($order));
        
        $state = $order->getLastState();
        $this->assertEquals($state['status'], $value->getBehaviourToOrderState());
        $this->assertTrue(strpos($state['comment'], "'" . $value->getType() . "'") !== false);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     */
    public function testDetectEmail() {
        
        $orderId = $this->placeOrder(array('payment' => "bar", 'checkForFraud' => false,  'totalBelow' => 25000));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->setMaxPayment($order->getAbsTotal()+10);        
        $this->assertFalse(Default_Helpers_Fraud_Order::detect($order));
        $orderCustomer = $order->getCustomer();
        
        $blacklist = new Yourdelivery_Model_Support_Blacklist();
        $value = $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL, $orderCustomer->getEmail());
        $blacklist->save();
        $this->assertTrue(Default_Helpers_Fraud_Order::detect($order));
        
        $state = $order->getLastState();
        $this->assertEquals($state['status'], $value->getBehaviourToOrderState());
        $this->assertTrue(strpos($state['comment'], "'" . $value->getType() . "'") !== false);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     */
    public function testDetectEmailPart() {
        
        $orderId = $this->placeOrder(array('payment' => "bar", 'checkForFraud' => false, 'totalBelow' => 25000));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->setMaxPayment($order->getAbsTotal()+10);        
        $this->assertFalse(Default_Helpers_Fraud_Order::detect($order));
        $orderCustomer = $order->getCustomer();
        
        $blacklist = new Yourdelivery_Model_Support_Blacklist();
        $value = $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL_MINUTEMAILER, $orderCustomer->getEmail());
        $blacklist->save();
        $this->assertTrue(Default_Helpers_Fraud_Order::detect($order));
        
        $state = $order->getLastState();
        $this->assertEquals($state['status'], $value->getBehaviourToOrderState());
        $this->assertTrue(strpos($state['comment'], "'" . $value->getType() . "'") !== false);
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     */
    public function testDetectIp() {
        
        $orderId = $this->placeOrder(array('payment' => "bar", 'checkForFraud' => false, 'totalBelow' => 25000));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->setMaxPayment($order->getAbsTotal()+10);        
        $this->assertFalse(Default_Helpers_Fraud_Order::detect($order));
        
        $blacklist = new Yourdelivery_Model_Support_Blacklist();
        $value = $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_IP, $order->getIpAddr());
        $blacklist->save();
        $this->assertTrue(Default_Helpers_Fraud_Order::detect($order));
        
        $state = $order->getLastState();
        $this->assertEquals($state['status'], $value->getBehaviourToOrderState());
        $this->assertTrue(strpos($state['comment'], "'" . $value->getType() . "'") !== false);
        
        $row = $value->getTable()->getCurrent();
        $row->created = date("Y-m-d H:i:s", strtotime("last week"));
        $row->save();
        Yourdelivery_Model_Support_Blacklist::flushList();
        $this->assertFalse(Default_Helpers_Fraud_Order::detect($order));
    }
    
}
