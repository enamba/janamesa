<?php

/**
 * @runTestsInSeparateProcesses 
 */
class OrderPrivateControllerTest extends AbstractOrderController {

    protected $post = array();
    protected $order = null;

    public function setUp() {
        parent::setUp();
        list($this->post, $order) = $this->_preparePost();
        $customer = $order->getCustomer();
        $this->post['email'] = $customer->getEmail();
        $this->post['hausnr'] = "3";
        $this->post['name'] = $customer->getName();
        $this->post['prename'] = $customer->getPrename();
        $this->post['street'] = "teststr";
        $this->post['telefon'] = "1234567890";
        $this->order = $order;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.09.11
     */
    public function testRestAtOnce() {
        $order = $this->order;
        $this->openService($order->getService(), $order->getDeliverTime());
        $this->assertTrue($order->getService()->getOpening()->isOpen());

        $this->post['payment'] = "bar";
        $this->post['deliver-time'] = "sofort";
        $this->post['deliver-time-day'] = 0;
        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($this->post);
        $this->dispatch('/order_private/finish');

        $this->assertRedirectTo('/order_private/success');

        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from('orders')->where("restaurantId=?", $this->post['serviceId'])
                ->where('kind="priv"')
                ->where('total=?', $order->getTotal())
                ->where('time<?', date(DATETIME_DB, time()))
                ->where('id>?', $this->order->getId());

        $result = $db->query($select)->fetchAll();

        $this->assertEquals($result[0]['restaurantId'], $this->post['serviceId']);
    }

    /**
     * Test to pre-order
     * - prepeare post
     * - get next opening
     * - close service today
     * - pre-order
     * - check DB
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.07.2012
     */
    public function testRestPreOrder() {
        $countLoops = 0;
        $preOrderTime = strtotime('+32 hours');
        do {
            list($this->post, $order) = $this->_preparePost();
            $countLoops++;
        } while ($countLoops < 10 && (!$order->getService()->getOpening()->isOpen($preOrderTime)));

        if ($countLoops >= 10) {
            $from = strtotime("-2 hours", $preOrderTime);
            $until = strtotime("+3 hours", $preOrderTime);
            $service = new Yourdelivery_Model_Servicetype_Restaurant($order->getService()->getId());
            $this->assertGreaterThan(0, $service->getOpening()->addNormalOpening(
                            array('restaurantId' => $service->getId(),
                                'day' => (date('w') + 3) % 7,
                                'from' => date('H:i:s', $from),
                                'until' => date('H:i:s', $until)
                    )));
            $order = new Yourdelivery_Model_Order($order->getId());
        }

        $nextOpeningTomorrow = array_shift(array_pop(array_shift($order->getService()->getOpening()->getIntervalOfDay($preOrderTime))));

        // close service for now
        $holidayTable = new Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday();
        $holidayTable->delete(sprintf('date = "%s" and stateId = %d', date('Y-m-d'), $order->getService()->getCity()->getStateId()));
        $openingsTable = new Yourdelivery_Model_DbTable_Restaurant_Openings();
        $openingsTable->delete(sprintf('day = %d and restaurantId = %d', date('w'), $order->getService()->getId()));
        
        $deliverTime = $nextOpeningTomorrow['timestamp_from'] + 60 * 60 * 3;
         
        $this->assertTrue($order->getService()->getOpening()->isOpen($deliverTime), sprintf('service #%d is not open at nextOpening "%s"', $order->getService()->getId(), date('Y-m-d H:i', $deliverTime)));

        $this->assertGreaterThan(time(), $deliverTime, sprintf('failed to get deliver time "%s" greater than actual time "%s"', date('Y-m-d H:i:s', $deliverTime), date('Y-m-d H:i:s', time())));

        $this->post['deliverTime'] = date('Y-m-d H:i', $deliverTime);
        $this->post['deliver-time-day'] = date('d.m.Y', $deliverTime);
        $this->post['deliver-time'] = date('H:i', $deliverTime);

        $customer = $order->getCustomer();
        $this->post['email'] = $customer->getEmail();
        $this->post['hausnr'] = "3";
        $this->post['name'] = $customer->getName();
        $this->post['prename'] = $customer->getPrename();
        $this->post['street'] = "Erfurter Straße";
        $this->post['telefon'] = "1234567890";

        $this->getRequest()->setMethod('post');

        $this->getRequest()->setPost($this->post);
        $this->dispatch('/order_private/finish');
        $this->assertRedirectTo('/order_private/success', sprintf('could not finish order with params "%s - log is: %s"', print_r($this->post, true), Default_Helpers_Log::getLastLog('log', 5)));

        // check values in DB
        $db = $this->_getDbAdapter();
        $sql = $db->select()->from('orders')->order('id DESC')->limit(1);
        $lastOrderRow = $db->fetchRow($sql);
        $this->assertEquals($lastOrderRow['deliverTime'], date('Y-m-d H:i:00', $deliverTime), sprintf('deliverTime "%s" was not saved to db for order #%d', date('Y-m-d H:i', $deliverTime), $lastOrderRow['id']));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.09.2011
     */
    public function testRestPayPal() {
        list($this->post, $order) = $this->_preparePost(null, null, null, 'rest', 'priv', 'paypal', false, false);

        $this->openService($order->getService(), $order->getDeliverTime());

        $order->getService()->getTable()->resetOpening();
        $this->assertTrue($order->getService()->getOpening()->isOpen());

        $customer = $order->getCustomer();
        $this->post['email'] = $customer->getEmail();
        $this->post['hausnr'] = "3";
        $this->post['name'] = $customer->getName();
        $this->post['prename'] = $customer->getPrename();
        $this->post['street'] = "Erfurter Straße";
        $this->post['telefon'] = "1234567890";

        $post = $this->post;
        $order = $this->order;

        $post['payment'] = "paypal";
        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($post);
        $this->dispatch('/order_private/finish');

        $this->checkPayPal($order);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.09.2011
     */
    public function testRestCreditWithHeidelpay() {
        $this->config->payment->credit->gateway = "heidelpay";

        list($this->post, $order) = $this->_preparePost(null, null, null, 'rest', 'priv', 'credit', false, false);
        $this->openService($order->getService(), $order->getDeliverTime());
        $order->getService()->getTable()->resetOpening();
        $this->assertTrue($order->getService()->getOpening()->isOpen());
        $customer = $order->getCustomer();
        $this->post['email'] = $customer->getEmail();
        $this->post['hausnr'] = "3";
        $this->post['name'] = $customer->getName();
        $this->post['prename'] = $customer->getPrename();
        $this->post['street'] = "Erfurter Straße";
        $this->post['telefon'] = "1234567890";

        $post = $this->post;
        $order = $this->order;
        $service = $order->getService();
        if (!$service->isOnline($order->getCustomer())) {
            $returnUrl = "/";
        }

        $post['payment'] = "credit";
        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($post);
        $this->dispatch('/order_private/finish');
        $redirectedTo = $this->getResponse()->getHeaders();
        $redirect = sprintf('Redirected order #%s to "%s" ', $order->getId(), strlen($redirectedTo[0]['value']) > 1 ?
                        $redirectedTo[0]['value'] :
                        sprintf("RedirectedTo:'%s'\n%s", $redirectedTo[0]['value'], Default_Helpers_Log::getLastLog()));
        if ($returnUrl) {
            $this->assertRedirectTo($returnUrl, $redirect);
        } else {
            $this->assertRedirectRegex("#^" . preg_quote("https://test-heidelpay.hpcgw.net/sgw/hcoForm") . "#", $redirect);
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.09.2011
     */
    public function testRestEbanking() {
        list($this->post, $order) = $this->_preparePost(null, null, null, 'rest', 'priv', 'ebanking', false, false);
        $this->openService($order->getService(), $order->getDeliverTime());
        $order->getService()->getTable()->resetOpening();
        $this->assertTrue($order->getService()->getOpening()->isOpen());
        $customer = $order->getCustomer();
        $this->post['email'] = $customer->getEmail();
        $this->post['hausnr'] = "3";
        $this->post['name'] = $customer->getName();
        $this->post['prename'] = $customer->getPrename();
        $this->post['street'] = "Erfurter Straße";
        $this->post['telefon'] = "1234567890";


        $post = $this->post;
        $order = $this->order;

        $service = $order->getService();
        if (!$service->isOnline($order->getCustomer())) {
            $returnUrl = "/";
        }

        $post['payment'] = "ebanking";
        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($post);
        $this->dispatch('/order_private/finish');

        $eBanking = new Yourdelivery_Payment_Ebanking();
        if ($returnUrl) {
            $this->assertRedirectTo($returnUrl);
        } else {
            $this->assertRedirectRegex("#^" . preg_quote($eBanking->getRedirectUrl()) . "#");
        }
    }

    /**
     * @author mlaug
     * @since 20.09.2011
     */
    public function testOrderServiceIsOnline() {
        $service = $this->order->getService();
        $service->setIsOnline(false)->save();

        $post = $this->post;
        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($post);
        $this->dispatch('/order_private/finish');
        $this->assertRedirectTo('/');

        $service->setIsOnline(true)->save();
    }

    /**
     * @author mlaug
     * @since 20.09.2011
     */
    public function testOrderNotMatchingMinAmount() {
        $order = $this->order;
        $this->openService($order->getService(), $order->getDeliverTime());
        $service = $this->order->getService();
        $service->editLocationAll($this->order->getTotal() + 10, 0, 3600, true);

        $post = $this->post;
        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($post);
        $this->dispatch('/order_private/finish');
        $this->assertAction('finish');
        $this->assertNotRedirect();
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.09.2011
     */
    public function testOrderWithDiscount() {
        $order = $this->order;
        $this->openService($order->getService(), $order->getDeliverTime());
        $rabattCode = $this->createDiscount();
        $this->post['discount'] = $rabattCode->getCode();
        $this->post['fidelity'] = 0;
        $this->post['payment'] = "bar";

        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($this->post);
        $this->dispatch('/order_private/finish');

        $this->assertController("order_private", 'orderId: ' . $order->getId() . ' Log: ' . Default_Helpers_Log::getLastLog());
        $this->assertaction("finish", 'orderId: ' . $order->getId() . ' Log: ' . Default_Helpers_Log::getLastLog());

        $this->post['payment'] = "paypal";
        $this->getRequest()->setPost($this->post);
        $this->dispatch('/order_private/finish');

        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from('orders')->where("restaurantId=?", $this->post['serviceId'])
                ->where('kind="priv"')
                ->where('total=?', $order->getTotal())
                ->where('rabattCodeId=?', $rabattCode->getId())
                ->where('time<?', date(DATETIME_DB, time()))
                ->where('id>?', $this->order->getId());
        $result = $db->query($select)->fetchAll();

        //get count of tickets before
        $tickets = new Yourdelivery_Model_Heyho_Messages();
        $countTickets = count($tickets->getMessages());

        //check valid data
        $order = new Yourdelivery_Model_Order($result[0]['id']);
        $rabattCheck = new Yourdelivery_Model_Rabatt_Code(null, $result[0]['rabattCodeId']);
        $this->assertTrue(is_object($order->getDiscount()));
        $this->assertEquals($rabattCheck->getId(), $order->getDiscount()->getId());
        $this->assertEquals($order->getService()->getId(), $this->post['serviceId'], print_r($this->post, true));
        $this->assertEquals($order->getDiscount()->getId(), $rabattCode->getId(), print_r($this->post, true));

        //check invalidation
        $this->assertTrue($rabattCheck->isUsable());
        $order->finalizeOrderAfterPayment('paypal', false);
        $rabattCheck = new Yourdelivery_Model_Rabatt_Code(null, $result[0]['rabattCodeId']);

        if ($order->getStatus() == -3) {
            $this->assertTrue($rabattCheck->isUsable());
            $rabattCheck->setCodeUsed();
        } else {
            $this->assertFalse($rabattCheck->isUsable());
        }
        //try to finalize again, which should not be allowed
        $order->finalizeOrderAfterPayment('paypal', false, false, false, false);
        $rabattCheck = new Yourdelivery_Model_Rabatt_Code(null, $result[0]['rabattCodeId']);
        $this->assertFalse($rabattCheck->isUsable());

        $order = new Yourdelivery_Model_Order($result[0]['id']);
        $this->assertEquals($order->getState(), Yourdelivery_Model_Order_Abstract::INVALID_DISCOUNT);
        $this->assertEquals($countTickets + 1, count($tickets->getMessages()));
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.09.2011
     */
    public function testOrderWithFidelityPoints() {
        $order = $this->order;
        $this->openService($order->getService(), $order->getDeliverTime());
        $customer = $this->getRandomCustomer();
        $customer->addFidelityPoint('testcase-action', $order->getId(), 100);
        $customer->login();

        $this->assertTrue($customer->isLoggedIn());

        $this->post['fidelity'] = 1;
        $this->post['payment'] = "paypal";
        $this->post['email'] = $customer->getEmail();
        $this->post['name'] = $customer->getName();
        $this->post['prename'] = $customer->getPrename();

        $this->assertEquals($this->post['fidelity'], 1);
        $this->assertTrue($order->getBucketTotal(null, true) > 0);

        if ($order->getMostExpensiveCost($order->getCustomer()->getFidelity()->getCashInLimit()) == 0) {
            $noMealBelowEight = true;
        }

        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($this->post);
        $this->dispatch('/order_private/finish');


        $paypal = new Yourdelivery_Payment_Paypal();
        $this->assertRedirectRegex("#^" . preg_quote($paypal->getRedirectUrl()) . "#");
        //$this->assertRedirectTo("/order_basis/payment");

        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from('orders')->where("restaurantId=?", $this->post['serviceId'])
                ->where('kind="priv"')
                ->where('total=?', $order->getTotal())
                ->where('time<?', date(DATETIME_DB, time()))
                ->where('id>?', $this->order->getId());

        $result = $db->query($select)->fetchAll();

        $this->assertEquals($result[0]['restaurantId'], $this->post['serviceId']);
        if ($noMealBelowEight) {
            $this->assertNull($result[0]['rabattCodeId']);
        } else {
            $this->assertTrue(!empty($result[0]['rabattCodeId']));
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.09.2011
     */
    public function testOrderWithFidelityPointsFake() {
        $order = $this->order;
        $this->openService($order->getService(), $order->getDeliverTime());
        $customer = $order->getCustomer();

        $this->post['fidelity'] = 1;
        $this->post['payment'] = "paypal";
        $this->post['email'] = $customer->getEmail();
        $this->post['name'] = $customer->getName();
        $this->post['prename'] = $customer->getPrename();


        $this->assertEquals($this->post['fidelity'], 1);
        $this->assertTrue($order->getBucketTotal(null, true) > 0);

        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($this->post);
        $this->dispatch('/order_private/finish');


        $paypal = new Yourdelivery_Payment_Paypal();
        $this->assertRedirectRegex("#^" . preg_quote($paypal->getRedirectUrl()) . "#");
        //$this->assertRedirectTo("/order_basis/payment");

        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from('orders')->where("restaurantId=?", $this->post['serviceId'])
                ->where('kind="priv"')
                ->where('total=?', $order->getTotal())
                ->where('time<?', date(DATETIME_DB, time()))
                ->where('id>?', $this->order->getId());
        $result = $db->query($select)->fetchAll();


        $this->assertEquals($result[0]['restaurantId'], $this->post['serviceId']);
        $this->assertNull($result[0]['rabattCodeId']);
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @since 30-11-11
     */
    public function testOrderWithPermanentDiscount() {
        $customer = $this->getRandomCustomer();
        $rabattCode = $this->createDiscount(false, 0, 10, true, false, true);
        $customer->setPermanentDiscountId($rabattCode->getId());
        $customer->save();

        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => 'credit', 'discount' => $customer->getDiscount(), 'customer' => $customer)));
        $this->assertEquals($customer->getDiscount()->getId(), $rabattCode->getId());
        $this->assertEquals($order->getDiscount()->getId(), $customer->getDiscount()->getId());
        $this->assertGreaterThan(0, $order->getDiscountAmount(), 'discountAmount = ' . $order->getDiscountAmount());
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.09.2011
     */
    public function testOrderWithInvalidCityId() {
        $order = $this->order;
        $this->openService($order->getService(), $order->getDeliverTime());
        $this->post['cityId'] = "10000000";
        $this->post['payment'] = "bar";

        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($this->post);
        $this->dispatch('/order_private/finish');

        $this->assertRedirectTo('/');
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.09.2011
     */
    public function testOrderWithLocationInStateCookie() {
        $order = $this->order;
        $this->openService($order->getService(), $order->getDeliverTime());

        $customer = $this->getRandomCustomer();
        $this->post['email'] = ($customer->getEmail()) ? $customer->getEmail() : "test@test.de";
        $this->post['hausnr'] = "3";
        $this->post['name'] = ($customer->getName()) ? $customer->getName() : "Name";
        $this->post['prename'] = ($customer->getPrename()) ? $customer->getPrename() : "Prename";
        $this->post['street'] = "teststr";
        $this->post['telefon'] = "1234567890";


        $location = new Yourdelivery_Model_Location();
        $location->setData(array('customerId' => $customer->getId(),
            'street' => $this->post['street'],
            'hausnr' => $this->post['hausnr'],
            'cityId' => $order->getService()->getCityId(),
            'plz' => $order->getService()->getPlz(),
            'tel' => $this->post['telefon']
        ));


        $locId = $location->save();
        $this->assertTrue(is_numeric($locId));

        $this->post['payment'] = "bar";

        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($this->post);
        $this->getRequest()->setCookie('yd-state', base64_encode(implode("#", array($order->getService()->getCityId(),
                            $locId,
                            "priv",
                            "rest",
                        ))
                )
        );
        $this->dispatch('/order_private/finish');

        $this->assertRedirectTo('/order_private/success', Default_Helpers_Log::getLastLog());
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.09.2011
     */
    public function testOrderWithInvalidLocationInStateCookie() {
        $order = $this->order;
        $this->openService($order->getService(), $order->getDeliverTime());
        $this->post['agb'] = 1;
        $this->post['payment'] = "bar";

        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($this->post);
        $this->getRequest()->setCookie('yd-state', base64_encode(implode("#", array("null",
                            "1212123123213",
                            "priv",
                            "rest",
                        ))
                )
        );
        $this->dispatch('/order_private/finish');

        $this->assertRedirectTo('/');
    }

    /**
     * @author mlaug
     * @since 20.09.2011
     */
    public function testOrderWithInvalidFormData() {
        $post = $this->post;
        $order = $this->order;
        $this->openService($order->getService(), $order->getDeliverTime());
        //invalid email address
        $post['email'] = 'samson....tiffy';

        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($post);
        $this->dispatch('/order_private/finish');
        $this->assertNotRedirect();
    }

    /**
     * @author mlaug
     * @since 20.09.2011
     */
    public function testOrderWithDeliverAreaNotInRangeOfService() {
        $post = $this->post;
        $order = $this->order;
        $this->openService($order->getService(), $order->getDeliverTime());

        //prepare ranges
        $_ranges = $order->getService()->getRanges();
        $ranges = array();
        foreach ($_ranges as $r) {
            $ranges[] = $r['cityId'];
        }

        $db = Zend_Registry::get('dbAdapter');
        $cityIds = $db->fetchAll('select id from city');
        foreach ($cityIds as $id) {
            if (!in_array($id, $ranges)) {
                $post['cityId'] = $id;
                break;
            }
        }

        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($post);
        $this->dispatch('/order_private/finish');

        $this->assertNotRedirect();
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.09.2011
     */
    public function testWithAnInvalidCityId() {
        $post = $this->post;
        $order = $this->order;
        $this->openService($order->getService(), $order->getDeliverTime());

        //invalid cityId
        $post['cityId'] = '12232332';

        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($post);
        $this->dispatch('/order_private/finish');

        $this->assertRedirectTo('/');
    }

    public function testWrongServicetypeAndMode() {
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.09.2011
     */
    public function testCaterOrder() {


        $countLoops = 0;
        do {
            list($this->post, $order) = $this->_preparePost(null, null, null, 'cater', 'priv', 'paypal', false, false);
            $countLoops++;
        } while ($countLoops < 10 && (!$order->getService()->getOpening()->isOpen()));

        $countLoops >= 10 ? $this->markTestSkipped("please modify this testcase") : null;
        $serviceDeliverTime = $order->getService()->getDeliverTime($this->post['cityId']);

        $deliverTime = time() + $serviceDeliverTime + 1800;

        $this->assertTrue($order->getService()->getOpening()->isOpen($deliverTime), sprintf('service #%d is not open at %s, but get it as nextOpening', $order->getService()->getId(), date('d.m.Y H:i:s', $deliverTime)));


        $this->post['deliver-time'] = date('H:i', $deliverTime);
        $this->post['deliver-time-day'] = date('d.m.Y', $deliverTime);

        $customer = $order->getCustomer();
        $this->post['email'] = $customer->getEmail();
        $this->post['hausnr'] = "3";
        $this->post['name'] = $customer->getName();
        $this->post['prename'] = $customer->getPrename();
        $this->post['street'] = "Erfurter Straße";
        $this->post['telefon'] = "1234567890";
        $this->post['payment'] = 'paypal';

        $this->assertEquals($this->post['mode'], 'cater');
        $this->assertEquals($this->post['payment'], 'paypal');

        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($this->post);
        $this->dispatch('/order_private/finish');

        $paypal = new Yourdelivery_Payment_Paypal();
        $this->assertRedirectRegex("#^" . preg_quote($paypal->getRedirectUrl()) . "#", Default_Helpers_Log::getLastLog());

        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from('orders')->where("restaurantId=?", $this->post['serviceId'])
                ->where('kind="priv"')
                ->where('total=?', $order->getTotal())
                ->where('time<?', date(DATETIME_DB, time()))
                ->where('id>?', $this->order->getId());

        $result = $db->query($select)->fetchAll();

        $this->assertEquals($result[0]['restaurantId'], $this->post['serviceId']);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.09.2011
     */
    public function testGreatOrder() {

        $countLoops = 0;
        do {
            list($this->post, $order) = $this->_preparePost(null, null, null, 'great', 'priv', 'paypal', false, false);
            $countLoops++;
        } while ($countLoops < 10 && (!$order->getService()->getOpening()->isOpen()));

        $countLoops >= 10 ? $this->markTestSkipped("please modify this testcase") : null;

        $serviceDeliverTime = $order->getService()->getDeliverTime($this->post['cityId']);

        $deliverTime = time() + $serviceDeliverTime + 1800;


        $this->assertTrue($order->getService()->getOpening()->isOpen($deliverTime), sprintf('service #%d is not open at %s, but get it as nextOpening', $order->getService()->getId(), date('d.m.Y H:i:s', $deliverTime)));


        $this->post['deliver-time'] = date('H:i', $deliverTime);
        $this->post['deliver-time-day'] = date('d.m.Y', $deliverTime);

        $customer = $order->getCustomer();
        $this->post['email'] = $customer->getEmail();
        $this->post['hausnr'] = "3";
        $this->post['name'] = $customer->getName();
        $this->post['prename'] = $customer->getPrename();
        $this->post['street'] = "Erfurter Straße";
        $this->post['telefon'] = "1234567890";
        $this->post['payment'] = 'paypal';

        $this->assertEquals($this->post['mode'], 'great');
        $this->assertEquals($this->post['payment'], 'paypal');

        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($this->post);
        $this->dispatch('/order_private/finish');

        $paypal = new Yourdelivery_Payment_Paypal();


        $body = $this->getResponse()->getBody();

        $this->assertRedirectRegex("#^" . preg_quote($paypal->getRedirectUrl()) . "#", Default_Helpers_Log::getLastLog());

        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from('orders')->where("restaurantId=?", $this->post['serviceId'])
                ->where('kind="priv"')
                ->where('total=?', $order->getTotal())
                ->where('time<?', date(DATETIME_DB, time()))
                ->where('id>?', $this->order->getId());



        $result = $db->query($select)->fetchAll();

        $this->assertEquals($result[0]['restaurantId'], $this->post['serviceId']);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.09.2011
     */
    public function testOrderServiceIsOpen() {
        $order = $this->order;
        $currentTime = time();
        $this->openService($order->getService(), $currentTime);

        $this->assertTrue($order->getService()->getOpening()->isOpen($currentTime), sprintf('service #%d is not open at %s, although opening was added', $order->getService()->getId(), date('d.m.Y H:i:s', $currentTime)));
        $this->post['deliver-time'] = "sofort";

        $this->post['payment'] = "bar";

        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($this->post);
        $this->dispatch('/order_private/finish');

        $this->assertRedirectTo('/order_private/success');
    }

    /**
     * @author mlaug
     * @since 04.10.2011
     */
    public function testChangePayment() {
        $service = $this->getRandomService(array('onlinePayment' => true));

        $orderId = $this->placeOrder(array('service' => $service, 'checkForFraud' => false));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->openService($order->getService(), $order->getDeliverTime());

        $this->assertTrue($order->getService()->getOpening()->isOpen());
        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost(array(
            'payment' => 'paypal'
        ));

        $session = new Zend_Session_Namespace('Default');
        $session->currentOrderId = $orderId;

        $this->dispatch('/order_private/payment');

        $this->checkPayPal($order);

        $changedOrder = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals('paypal', $changedOrder->getPayment());
        $this->assertEquals(round($service->getTransactionCost('paypal', $order->getBucketTotal() + $order->getServiceDeliverCost())), $changedOrder->getCharge());

        $this->resetRequest();
        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost(array(
            'payment' => 'bar'
        ));
        $session->currentOrderId = $orderId;
        $this->dispatch('/order_private/payment');


        $this->assertRedirectTo('/order_private/success');

        $changedOrder = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals('bar', $changedOrder->getPayment());
        $this->assertEquals(0, $changedOrder->getCharge());
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 31.03.2012
     */
    public function testAddChange() {
        $order = $this->order;
        $this->openService($order->getService(), $order->getDeliverTime());
        $this->assertTrue($order->getService()->getOpening()->isOpen());

        $this->post['payment'] = "bar";
        $this->post['deliver-time'] = "sofort";
        $this->post['deliver-time-day'] = 0;
        $this->post['change'] = "100,00 Euro";
        $this->getRequest()->setMethod('post');
        $this->getRequest()->setPost($this->post);
        $this->dispatch('/order_private/finish');

        $this->assertRedirectTo('/order_private/success');

        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from('orders')->where("restaurantId=?", $this->post['serviceId'])
                ->where('kind="priv"')
                ->where('total=?', $order->getTotal())
                ->where('time<?', date(DATETIME_DB, time()))
                ->where('id>?', $this->order->getId());

        $result = $db->query($select)->fetchAll();

        $this->assertEquals($result[0]['change'], 10000);
    }

    /**
     * check payment response by paypal orders
     * @param Yourdelivery_Model_Order $order
     * @author mlaug
     */
    private function checkPayPal(Yourdelivery_Model_Order $order) {
        $paypal = new Yourdelivery_Payment_Paypal();
        if (!$order->getService()->isOnline($order->getCustomer())) {
            $returnUrl = "/";
        }
        if ($returnUrl) {
            $this->assertRedirectTo($returnUrl, Default_Helpers_Log::getLastLog());
        } else {
            $this->assertRedirectRegex("#^" . preg_quote($paypal->getRedirectUrl()) . "#", Default_Helpers_Log::getLastLog());
        }
    }

}
