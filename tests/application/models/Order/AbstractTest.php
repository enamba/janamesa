<?php

/**
 * @runTestsInSeparateProcesses 
 */
class Order_AbstractTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.02.2012
     */
    public function testHasNewCustomerDiscount() {

        $discount = $this->createDiscount();
        $discountParent = $discount->getParent();
        $orderId = $this->placeOrder(array('discount' => $discount));

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertFalse($order->hasNewCustomerDiscount());

        $discountParent->setType(1);
        $discountParent->save();
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertTrue($order->hasNewCustomerDiscount());
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.04.2012 
     */
    public function testGetServicesByCityId() {

        $cityId = $this->getRandomCityId();

        //get services with numberic parameter
        $services = Yourdelivery_Model_Order_Abstract::getServicesByCityId($cityId, 1);

        //get services with parameter as string
        $services2 = Yourdelivery_Model_Order_Abstract::getServicesByCityId($cityId, 'rest');
        $this->assertEquals(count($services), count($services2));

        //get services with numberic parameter
        $services = Yourdelivery_Model_Order_Abstract::getServicesByCityId($cityId, 2);

        //get services with parameter as string
        $services2 = Yourdelivery_Model_Order_Abstract::getServicesByCityId($cityId, 'cater');
        $this->assertEquals(count($services), count($services2));

        //get services with numberic parameter
        $services = Yourdelivery_Model_Order_Abstract::getServicesByCityId($cityId, 3);

        //get services with parameter as string
        $services3 = Yourdelivery_Model_Order_Abstract::getServicesByCityId($cityId, 'great');
        $this->assertEquals(count($services), count($services3));
        
        $deadLockPreventer = 0;
        $secondCityId = 0;
        do {
            $secondCityId = $this->getRandomCityId();
        }
        while( ($cityId != $secondCityId) && ($deadlockPreventer<MAX_LOOPS));        

        $services = Yourdelivery_Model_Order_Abstract::getServicesByCityId($cityId, 1);
        $secondServices = Yourdelivery_Model_Order_Abstract::getServicesByCityId($secondCityId, 1);
        $combined = Yourdelivery_Model_Order_Abstract::getServicesByCityId(array($cityId, $secondCityId), 1);
        $this->assertTrue(count($services) + count($secondServices) >= count($combined)); //must always be more or equals, since duplicates are sorted out

    }

    /**
     * test send order with restaurant having notification per fax
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 23.11.2011
     */
    public function testSendOrderPerFax() {

        $restaurant = $this->getRandomService();
        $notify = $restaurant->getNotify();
        $restaurant->setNotify('fax');
        $restaurant->save();

        $orderId = $this->placeOrder(array('service'=>  $restaurant));
        $order = new Yourdelivery_Model_Order($orderId);

        $order->send(false);

        $history = $order->getStateHistory();
        $status = $history->current();
        $this->assertEquals($status['comment'], 'Send out order to service via fax ('.$restaurant->getFaxService().") to ".$restaurant->getFax());

        $rows = $order->getSendbyHistory();
        foreach ($rows as $row) {
            $this->assertEquals($row->sendBy, 'fax');
        }

        // reset service to origin notify
        $restaurant->setNotify($notify);
        $restaurant->save();

    }

    /**
     * test send order with restaurant having notification per email
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 23.11.2011
     */
    public function testSendOrderPerEmail() {

        $orderId = $this->placeOrder(array('finalize' => false));
        $order = new Yourdelivery_Model_Order($orderId);

        $restaurant = $order->getService();
        $restaurant->setNotify('email');
        $email = $restaurant->getEmail();
        if(empty($email)){
            $restaurant->setEmail(Default_Helper::generateRandomString(5)."@test.ru");
        }
        $restaurant->save();

        $order->setService($restaurant);
        $order->send(false);

        $history = $order->getStateHistory();
        $comments = array();
        foreach ($history as $entry) {
            $comments[] = $entry['comment'];
        }

        $this->assertTrue((in_array('Send out order to service via email', $comments) || in_array('Send out order to service via all', $comments)), implode(', ', $comments));
        $this->assertTrue((in_array('Sending out order via email to '. $restaurant->getEmail(). ', no confirmation expected', $comments) || in_array('Sending out order via all, no confirmation expected', $comments)), implode(', ', $comments));
        $this->assertEquals(2, count($history));

        $rows = $order->getSendbyHistory();
        foreach ($rows as $row) {
            $this->assertEquals($row->sendBy, 'email');
        }
    }

    /**
     * test send order with restaurant having notification per sms
     * @author Alex Vait <vait@lieferando.de>
     * @since 23.11.2011
     */
    public function testSendOrderPerSms() {

        $orderId = $this->placeOrder(array('finalize' => false));
        $order = new Yourdelivery_Model_Order($orderId);

        $restaurant = $order->getService();
        $restaurant->setNotify('sms');
        $restaurant->save();

        $order->setService($restaurant);
        $order->send(false);

        $history = $order->getStateHistory()->toArray();
        $status = array_pop($history);
        $this->assertEquals($status['comment'], 'Send out order to service via sms');

        $rows = $order->getSendbyHistory();
        foreach ($rows as $row) {
            $this->assertEquals($row->sendBy, 'sms');
        }
    }

    /**
     * test send order with restaurant having notification per email and fax
     * @author Alex Vait <vait@lieferando.de>
     * @since 23.11.2011
     */
    public function testSendOrderPerEmailAndFax() {

        $orderId = $this->placeOrder(array('finalize' => false));
        $order = new Yourdelivery_Model_Order($orderId);

        $restaurant = $order->getService();
        $restaurant->setNotify('all');
        $restaurant->save();

        $order->setService($restaurant);
        $order->send(false);

        $history = $order->getStateHistory()->toArray();
        $status = array_pop($history);
        $this->assertEquals($status['comment'], 'Send out order to service via all');

        $rows = $order->getSendbyHistory();
        foreach ($rows as $row) {
            $this->assertEquals($row->sendBy, 'all');
        }
    }

    /**
     * test send order with restaurant having notification per email and fax
     * @author Alex Vait <vait@lieferando.de>
     * @since 23.11.2011
     */
    public function testSendOrderPerSmsAndEmail() {

        $orderId = $this->placeOrder(array('finalize' => false));
        $order = new Yourdelivery_Model_Order($orderId);

        $restaurant = $order->getService();
        $restaurant->setNotify('smsemail');
        $restaurant->save();

        $order->setService($restaurant);
        $order->send(false);

          $history = $order->getStateHistory()->toArray();
        $status = array_pop($history);
        $this->assertEquals($status['comment'], 'Send out order to service via smsemail');

        $rows = $order->getSendbyHistory();
        foreach ($rows as $row) {
            $this->assertEquals($row->sendBy, 'smsemail');
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 13.12.2011
     */
    public function testDeleteFromFavorite() {

        $customer = $this->getRandomCustomer();
        $orderId = $this->placeOrder(array('customer' => $customer));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertTrue($order->addToFavorite($customer));
        $this->assertTrue($order->isFavourite());

        $this->assertTrue($order->deleteFromFavorite());
        $this->assertFalse($order->isFavourite());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 15.12.2011
     */
    public function testIsRated() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $this->assertFalse($order->isRated());

        // rate this order
        $order->rate($order->getCustomerId(), 3, 3, 'Test-Kommentar', 'Test-Titel', 0, 'Samson');
        $this->assertTrue($order->isRated());
    }

    public function testRateLowOrHigh() {
        //no comment, activate at once
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $customer = $order->getCustomer();
        $points = $customer->getFidelity()->getPoints();
        $order->rate($order->getCustomerId(), 3, 3, '', 'Test-Titel', 0, 'Samson');
        $customer->getFidelity()->clearCache();
        $this->assertEquals($points + 2, $customer->getFidelity()->getPoints());

        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $customer = $order->getCustomer();
        $points = $customer->getFidelity()->getPoints();
        $order->rate($order->getCustomerId(), 3, 3, 'Test-Kommentar', 'Test-Titel', 0, 'Samson');
        $rating = new Yourdelivery_Model_Servicetype_Rating($order->getRating()->current()->id);
        $rating->activate();
        $customer->getFidelity()->clearCache();
        $this->assertEquals($points + 2, $customer->getFidelity()->getPoints());

        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $customer = $order->getCustomer();
        $points = $customer->getFidelity()->getPoints();
        $order->rate($order->getCustomerId(), 3, 3, 'Test Test Test Test Test Test Test TEst TEst Test Test TEst Test Test TEst Test Test Test Test Test Test Test Test', 'Test-Titel', 0, 'Samson');
        $rating = new Yourdelivery_Model_Servicetype_Rating($order->getRating()->current()->id);
        $rating->activate();
        $customer->getFidelity()->clearCache();
        $this->assertEquals($points + 2, $customer->getFidelity()->getPoints());

        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $customer = $order->getCustomer();
        $points = $customer->getFidelity()->getPoints();
        $order->rate($order->getCustomerId(), 3, 3, 'Das ist jezt aber ein vernünftiger Deutscher Satz, der alle Anforderungn erfüllen sollte...', 'Test-Titel', 0, 'Samson');
        $rating = new Yourdelivery_Model_Servicetype_Rating($order->getRating()->current()->id);
        $rating->activate();
        $customer->getFidelity()->clearCache();
        $this->assertEquals($points + 5, $customer->getFidelity()->getPoints());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 15.12.2011
     */
    public function testShowRatingLink() {
        $service = $this->getRandomService(array(
            'notify' => 'fax'
        ));
        $order = new Yourdelivery_Model_Order($this->placeOrder(array(
            'service' => $service
        )));
        $this->assertFalse($order->showRatingLink(), $order->getId());
        
        $order->setStatus(Yourdelivery_Model_Order_Abstract::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'bestätige für Testcase'));
        $o = new Yourdelivery_Model_Order($order->getId());
        $this->assertEquals(1, $o->getState());
        $this->assertTrue($o->showRatingLink(), Default_Helpers_Log::getLastLog());

        // rate this order
        $o->rate($o->getCustomerId(), 3, 3, 'Test-Kommentar', 'Test-Titel', 0, 'Samson');
        $this->assertFalse($o->showRatingLink());
    }

    /**
     * setPayment should throw exception, if discount with payment bar with
     * an open amount is set
     *
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function testSetPaymentBarWithDiscount() {
        $order = new Yourdelivery_Model_Order_Private();
        $order->setup($this->getRandomCustomer(), 'rest');
        $order->setService($this->getRandomService(array('onlinePayment' => true, 'barPayment' => true)));
        $order->addMeal($this->getRandomMealFromService($order->getService()), array(), 10);
        $order->setPayment('bar');
        $this->setExpectedException('Yourdelivery_Exception', 'cannot set payment bar, not allowed:');
        $discount = $this->createDiscount();
        $order->setDiscount($discount);
        $order->setPayment('bar');
    }

    /**
     * if we have a discount the deliver time must be at once
     *
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function testDiscountMustBeDeliveredAtOnce() {
        $order = new Yourdelivery_Model_Order($this->placeOrder(
                                array(
                                    'discount' => true
                                )
                ));
        $this->assertTrue($order->getDeliverTime() <= $order->getTime());
    }
    
    
    /**
     * Dataprovider for testSendStornoNotificationToRestaurant
     * @return <string> notify
     */    
    public static function notifies(){
        return array(
            array('all'),
            array('fax'),
            array('email'),
            array('sms'),
            array('smsemail')
            );
    }
    
    /**
     * test if a notification is send to the restaurant with the correct notify
     * @author Allen Frank <frank@lieferando.de>
     * @since 22-03-12
     * 
     * @dataProvider notifies
     */
    public function testSendStornoNotificationToRestaurant($notify){
        $db = Zend_Registry::get('dbAdapter');
        $orderId = $this->placeOrder(array('notify' => $notify, 'checkForFraud' => false));
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::STORNO,  new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'set by testSendStornoNotificationToRestaurant'));              
        $order->sendStornoNotificationToRestaurant();
        $msg = Default_Helpers_Log::getLastLog();
        $this->assertEquals($notify, $order->getService()->getNotify(), $msg);
        $row = $db->fetchRow($db->quoteInto('select * from order_sendby where orderId = ?', $orderId));
        $this->assertEquals($notify, $row['sendBy'], $msg);
        $this->assertEquals($order->getId(), $row['orderId'], $msg);
        
        switch($notify){
            default :
            case 'fax':
                $this->assertFileExists(sprintf('%s/../storage/stornos/%s/%s-stornosheet-restaurant.pdf'
                        ,APPLICATION_PATH, date('d-m-Y', time()), $order->getId()), $msg);
                break;
            case 'sms':
            case 'smsemail':
                $row = $db->fetchRow($db->quoteInto('select count(id) count from printer_topup_queue where orderId = ?', $order->getId()));
                $this->assertEquals(2, $row['count'], $msg);
                //how to check for created email?!
                break;
            case 'email':
                //how to check for created email?!
                break;
            case 'all':
                $this->assertFileExists(sprintf('%s/../storage/stornos/%s/%s-stornosheet-restaurant.pdf'
                        ,APPLICATION_PATH, date('d-m-Y', time()), $order->getId()), $msg);
                //how to check for created email?!
                break;
        }
        
        
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function testSetPaymentAddition(){
        
        $customer = $this->getRandomCustomer();
        $order = new Yourdelivery_Model_Order_Private();
        $order->setup($customer, 'rest');
        $order->setPayment('bar');
        
        $order->setPaymentAddition('ec');
        $this->assertEquals($order->getPaymentAddition(), 'ec');
        $order->setPaymentAddition('irgendwas');
        $this->assertEquals($order->getPaymentAddition(), 'ec');
        $order->setPaymentAddition('creditcardathome');
        $this->assertEquals($order->getPaymentAddition(), 'creditcardathome');
        $order->setPaymentAddition('irgendwas');
        $this->assertEquals($order->getPaymentAddition(), 'creditcardathome');
        $order->setPayment('credit', false);
        $this->assertNull($order->getPaymentAddition());
        $order->setPaymentAddition('ec');
        $this->assertNull($order->getPaymentAddition());
        $order->setPayment('bar');
        $order->setPaymentAddition('ec');
        $this->assertEquals($order->getPaymentAddition(), 'ec');
    }
    
    
    /**
     * Test if constant from Yourdelivery_Model_Order_Abstract could be properly set to an order.
     * @author Allen Frank <frank@lieferando.de>
     * @since 24.05.2012 
     */
    public function testSetState(){
        
        $reflect = new ReflectionClass(get_class(new Yourdelivery_Model_Order_Private()));
        $states = $reflect->getConstants();
        $notAState = array("COMPANYORDER", "PRIVATEORDER", "NOTIFY_AMOUNT", "GROUPORDER", "SINGLEORDER");
        foreach($notAState as $state){
            unset($states[$state]);
        }
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);
        foreach($states as $state => $stateValue){
            $order->setStatus($stateValue,  new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "TestCase"));
            $currentState = $order->getStateHistory()->current();
            $this->assertEquals($stateValue, $currentState['status'], sprintf('State %s => %s could not be set.', $state, $stateValue));
        }
    }
    
    /**
     * test that the domain row shall not be null
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 16.07.2012
     */
    public function testDefaultDomain(){
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $this->assertNotNull($order->getDomain());      
    }
}
