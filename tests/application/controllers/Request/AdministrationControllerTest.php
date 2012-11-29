<?php

/**
 * Test for administration controller in request
 * @author alex
 * @since 17.11.2010
 */

/**
 * @runTestsInSeparateProcesses 
 */
class Request_AdministrationcontrollerTest extends Yourdelivery_Test {

    public function setUp() {
        parent::setUp();
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        $this->getRequest()->setHeader('Authorization', 'Basic ' . base64_encode('gf:thisishell'));
                
        $db = Zend_Registry::get('dbAdapter');
        $db->query('TRUNCATE blacklist');
        $db->query('TRUNCATE blacklist_values');
    }

    /**
     * we expect a 404 if we do provide a not available order Id
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.07.2012
     */
    public function testIndexNotFoundAction() {
        $orderId = 0;
        //all action should loop into 404
        foreach (array('index', 'storno', 'fake', 'resend', 'confirm', 'changepayment', 'comment', 'ratingemail', 'confirmationemail', 'block', 'whitelist') as $action) {
            $request = $this->getRequest();
            $this->dispatch('/request_administration_orderedit/' . $action . '/id/' . $orderId);
            $this->assertResponseCode(404);
            $this->resetRequest();
            $this->resetResponse();
        }
    }

    /**
     * Test storno order, no status change because reason is missing
     * @author Alex Vait <vait@lieferando.de>
     * @author Matthias Laug <laug@lieferando.de>
     * @since 17.11.2010, 25.07.2012
     */
    public function testStornoOrderSimpleFailBecauseNoReason() {
        $orderId = $this->placeOrder();
        $this->setOrderState($orderId, Yourdelivery_Model_Order_Abstract::NOTAFFIRMED);
        $this->dispatch('/request_administration_orderedit/storno/id/' . $orderId);
        $this->assertResponseCode(406);
    }

    /**
     * Test storno order, no status change because already cancled
     * @author Alex Vait <vait@lieferando.de>
     * @author Matthias Laug <laug@lieferando.de>
     * @since 17.11.2010, 25.07.2012
     */
    public function testStornoOrderSimpleFailBecauseWrongState() {
        $orderId = $this->placeOrder();
        $this->setOrderState($orderId, Yourdelivery_Model_Order_Abstract::STORNO);
        $this->dispatch('/request_administration_orderedit/storno/id/' . $orderId);
        $this->assertResponseCode(406);
    }
    
    /**
     * test a simple storno using GET
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.07.2012
     */
    public function testStornoSimpleSuccessUsingGet(){
        $orderId = $this->placeOrder();
        $this->setOrderState($orderId, Yourdelivery_Model_Order_Abstract::NOTAFFIRMED);
        $resaonId = array_rand(array_keys($reasons = Yourdelivery_Model_Order_Abstract::getStornoReasons()));
        $this->dispatch('/request_administration_orderedit/storno/' . $orderId . '/reasonId/' . $resaonId);
    }
    
    /**
     * test a simple storno using POST
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.07.2012
     */
    public function testStornoSimpleSuccessUsingPost(){
        $orderId = $this->placeOrder();
        $this->setOrderState($orderId, Yourdelivery_Model_Order_Abstract::NOTAFFIRMED);
        $resaonId = array_rand(array_keys($reasons = Yourdelivery_Model_Order_Abstract::getStornoReasons()));
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'reasonId' => $resaonId
        ));
        $this->dispatch('/request_administration_orderedit/storno/' . $orderId);
    }

    /**
     * Test setting order to fake, status of the order must be -2 after that
     * @author Matthias Laug
     * @since 25.07.2012
     */
    public function testFakeOrder() {
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);
        $this->setOrderState($orderId, Yourdelivery_Model_Order_Abstract::NOTAFFIRMED);
        $this->dispatch('/request_administration_orderedit/fake/id/' . $orderId);
        $this->assertEquals(intval($this->getOrderState($orderId)), Yourdelivery_Model_Order_Abstract::FAKE_STORNO);
    }

    /**
     * Test setting order to fake, status of the order must be -2 after that
     * @author Matthias Laug
     * @since 25.07.2012
     */
    public function testFakeOrderFailAlreadyMarkedAsFake() {
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);
        $this->setOrderState($orderId, Yourdelivery_Model_Order_Abstract::FAKE_STORNO);
        $this->dispatch('/request_administration_orderedit/fake/id/' . $orderId);
        $this->assertResponseCode(406);
    }

    /**
     * confirm an order, simple way
     * @author Matthias Laug
     * @since 25.07.2012
     */
    public function testConfirmOrderSuccess() {
        $orderId = $this->placeOrder();
        $this->setOrderState($orderId, Yourdelivery_Model_Order_Abstract::STORNO);
        $this->dispatch('/request_administration_orderedit/confirm/id/' . $orderId);
        $this->assertEquals(intval($this->getOrderState($orderId)), Yourdelivery_Model_Order_Abstract::DELIVERED);
    }
    
    /**
     * but stati do not match! we expect http 406
     * @author Matthias Laug
     * @since 25.07.2012
     */
    public function testConfirmOrderFailBecauseOfWrongStatus() {
        $orderId = $this->placeOrder();
              
        //do not allow to confirm delivered and payment pending orders
        $invalidStatus = array(
            Yourdelivery_Model_Order::DELIVERED,
            Yourdelivery_Model_Order::PAYMENT_NOT_AFFIRMED,
            Yourdelivery_Model_Order::PAYMENT_PENDING
        );
        
        foreach($invalidStatus as $status){
            $this->setOrderState($orderId, $status);
            $this->dispatch('/request_administration_orderedit/confirm/id/' . $orderId);
            $this->assertResponseCode(406);
            $this->resetRequest();
            $this->resetResponse();
        }    
    }

    /**
     * Test resending order with retarus fax
     * @author alex
     * @since 02.02.2011
     */
    public function testResendOrderRetarus() {


        $orderId = $this->placeOrder();

        $order = new Yourdelivery_Model_Order($orderId);
        $service = new Yourdelivery_Model_Servicetype_Restaurant($order->getRestaurantId());

        // set notification to fax and fax service to retarus
        $service->setFaxService('retarus');
        $service->setNotify('fax');
        $service->save();

        // storno order, so the status must be -2
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/request_administration_orderedit/resend/id/' . $orderId . '/torestaurant/1/tocourier/0');

        $this->assertTrue($this->isRetarusFaxOK($orderId));
    }

    /**
     * Test resending order with interfax fax
     * @author alex
     * @since 02.02.2011
     */
    public function testResendOrderInterfax() {


        $orderId = $this->placeOrder();

        $order = new Yourdelivery_Model_Order($orderId);
        $service = new Yourdelivery_Model_Servicetype_Restaurant($order->getRestaurantId());

        // set fax service to retarus
        $service->setFaxService('interfax');
        $service->save();

        // storno order, so the status must be -2
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/request_administration_orderedit/resend/id/' . $orderId . '/torestaurant/1/tocourier/0');

        $this->assertTrue($this->isInterfaxFaxOK($orderId));
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 21.02.2012
     */
    public function testHoliday() {
        $cityId = $this->getRandomCityId();
        $city = new Yourdelivery_Model_City($cityId);

        $today = date('d.m.Y', time());
        $todaySql = date('Y-m-d', time());

        $holidayName = 'Some holiday-' . time();

        // remove all holidays for today
        Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday::removeByDate($todaySql);

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'day' => $today,
            'name' => $holidayName,
            'yd-state' => array($city->getStateId() => '')
        );

        $request->setPost($post);
        $this->dispatch('/request_administration/addholiday');

        sleep(1);

        $db = Zend_Registry::get('dbAdapter');
        $holidayId = (integer) $db->fetchOne('SELECT MAX(`id`) FROM `restaurant_openings_holidays`');

        $holidayTest = new Yourdelivery_Model_Servicetype_OpeningsHolidays($holidayId);
        $this->assertEquals($holidayTest->getDate(), $todaySql);
        $this->assertEquals($holidayTest->getName(), $holidayName);
        $this->assertEquals($holidayTest->getStateId(), $city->getStateId());

        $post = array(
            'id' => $holidayId
        );

        $request->setPost($post);
        $this->dispatch('/request_administration/removeholiday');

        $testHoliday = $db->fetchOne('SELECT * FROM `restaurant_openings_holidays` where `id`= ?', $holidayId);

        // holiday must be deleted
        $this->assertFalse($testHoliday);
    }

    /**
     * Tests autocomplete hints for backend version of plz AC
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 17.08.2012
     */
    public function testCityautocomplete() {
        $request = $this->getRequest();
        $request->setMethod('GET');

        // Preventing from infinite order
        for ($i=0; $i < 25; $i++) {
            // Random zip code prefix in range: 00-99
            $term = sprintf('%02d', rand(0, 99));
            $request->setParams(array(
                'term' => $term
            ));
            $this->dispatch('/request_administration/cityautocomplete');

            $this->assertResponseCode(200);
            $rawBody = $this->getResponse()->getBody();
            $responseArray = Zend_Json::decode($rawBody);
            $this->assertIsArray($responseArray);
            $this->assertArrayHasKey('cities', $responseArray);
            if (empty($responseArray['cities'])) {
                // Nothing to be checked on empty result - let's try once again
                $this->resetRequest();
                $this->resetResponse();
                continue ;
            }

            $randomKey = rand(0, count($responseArray['cities']) - 1);
            $this->assertIsArray($responseArray['cities'][$randomKey]);
            $this->assertArrayHasKeys(array('id', 'value'), $responseArray['cities'][$randomKey]);
            $this->assertTrue(strncmp($responseArray['cities'][$randomKey]['value'], $term, 2) == 0);

            // Breaking the loop - single test is enough
            return;
        }
        // If we are here, any accurate zip code hint could not be find - weird...
        $this->markTestSkipped('Could not find accurate zip code autocomplete hint!');
    }

    /**
     * set the state of the order
     * @author alex
     * @since 17.11.2010
     */
    private function setOrderState($orderId, $state) {
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus($state, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "Change in test"));
        $this->assertEquals($state, $order->getState());
    }

    /**
     * get the state of the order
     * @author alex
     * @since 17.11.2010
     */
    private function getOrderState($orderId) {
        $order = new Yourdelivery_Model_Order($orderId);
        return (integer) $order->getState();
    }

    /**
     * unblock the ip adress
     * @author alex
     * @since 17.11.2010
     */
    private function unblockIp($ip) {
        $dbTable = new Yourdelivery_Model_DbTable_BlockedAddr();
        $sql = sprintf("delete from blocked_ip_addr where ipAddr='%s'", $ip);
        $result = $dbTable->getAdapter()->query($sql);
    }

}

?>
