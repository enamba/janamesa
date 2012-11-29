<?php

/**
 * Description of OrderTest
 *
 * @author mlaug
 */

/**
 * @runTestsInSeparateProcesses 
 */
class OrderTest extends Yourdelivery_Test {

    public function setUp() {
        parent::setUp();
    }

    /**
     * test a private restaurant order in single mode
     */
    public function testPrivateRestaurantSingleOrder() {
        $this->placeOrder();
    }

    /**
     * @author vpriem
     * @since 02.11.2010
     * @modified 22.11.2011 - Felix Haferkorn <haferkorn@lieferando.de>
     * @modified 02.03.2012 - afrank
     */
    public function testCreateFromHash() {
        $order = Yourdelivery_Model_Order::createFromHash(73151);
        $this->assertFalse($order);
        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchRow('select hashtag from orders order by RAND() LIMIT 1');
        unset($order);
        $order = Yourdelivery_Model_Order::createFromHash($row['hashtag']);
        $this->assertTrue($order instanceof Yourdelivery_Model_Order);
        $this->assertTrue($order->isPersistent());
    }

    public function testTaxes() {
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    public function testNetto() {
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @author mlaug
     * @since 27.04.2011
     */
    public function testUpdateCustomer() {
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);
        $customer = $order->getCustomer();
        $this->assertFalse((boolean) $customer->isLoggedIn());
        $this->assertNull($order->getCustomerId());
        $this->assertFalse((boolean) $order->getRegisteredAfterSale());
        $order->updateCustomer($customer);

        unset($order);
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertTrue((boolean) $order->getRegisteredAfterSale());
        $this->assertEquals($order->getCustomerId(), $customer->getId());
    }

    public function testGetMealIds() {
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertGreaterThan(0, count($order->getMealIds()));
    }

    /**
     * check all service classes
     */
    public function testGetServiceClassFromOrder() {
        $customer = new Yourdelivery_Model_Customer_Anonym();

        //Restaurant
        $order = new Yourdelivery_Model_Order_Private();
        $order->setup($customer, Yourdelivery_Model_Servicetype_Abstract::RESTAURANT);
        $this->assertTrue($order->getServiceClass() instanceof Yourdelivery_Model_Servicetype_Restaurant);

        //Caterer
        $order = new Yourdelivery_Model_Order_Private();
        $order->setup($customer, Yourdelivery_Model_Servicetype_Abstract::CATER);
        $this->assertTrue($order->getServiceClass() instanceof Yourdelivery_Model_Servicetype_Cater);

        //Great
        $order = new Yourdelivery_Model_Order_Private();
        $order->setup($customer, Yourdelivery_Model_Servicetype_Abstract::GREAT);
        $this->assertTrue($order->getServiceClass() instanceof Yourdelivery_Model_Servicetype_Great);
    }

    /**
     * @expectedException Yourdelivery_Exception_InvalidAction
     */
    public function testFailedSetupCompanyOrder() {
        $customer = new Yourdelivery_Model_Customer_Anonym();
        $order = new Yourdelivery_Model_Order_Company();
        $order->setup($customer);
    }

    /**
     * test satellite commission
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 06.12.2011 
     */
    public function testSatelliteCommission() {
        $config = Zend_Registry::get('configuration');
        $order = new Yourdelivery_Model_Order($this->placeOrder());

        //get service and reset all satellite commission to zero
        $service = $order->getService();
        $service->setFeeSat(0)
                ->setItemSat(0)
                ->setKommSat(0)
                ->save();

        //get current commission for that order
        $comm = $order->getCommission();
        $item = $order->getCommissionItem();
        $fee = $order->getCommissionEach();

        //get regular fee
        $this->assertEquals($service->getFee(), $order->getCommissionEach());
        //alter satellite fee to another value than normal value
        $service->setFeeSat($service->getFee() + 10)
                ->setItemSat($service->getItem() + 10)
                ->setKommSat($service->getKomm() + 10)
                ->save();
        $order->setDomain($config->domain->base);
        $this->assertFalse($order->isSatellite(), $order->getId());
        //make it a satellite order
        $order->setDomain('www.samson.de');
        $this->assertTrue($order->isSatellite());

        //must differ from original comissions
        $this->assertEquals($service->getFeeSat(), $order->getCommissionEach());
        $this->assertNotEquals($comm, $order->getCommissionPercent());
        $this->assertNotEquals($item, $order->getCommissionItem());
        $this->assertNotEquals($fee, $order->getCommissionEach());
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 30.03.2012 
     */
    public function testSatellite() {
        $config = Zend_Registry::get('configuration');
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $order->setDomain($config->domain->base);
        $this->assertFalse($order->isSatellite());
        $order->setDomain('eat-star.de');
        $this->assertFalse($order->isSatellite());
        $order->setDomain('www.samson.de');
        $this->assertTrue($order->isSatellite());
    }

    /**
     * place an unregistered order without discount, earning fidelity point for this order
     * confirm and storno several times
     * points and transactions have to be correct
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 06.12.2011
     */
    public function testChangeStatusWithDiscount() {
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => 'paypal', 'discount' => true)));
        $discount = $order->getDiscount();
        $this->assertInstanceof(Yourdelivery_Model_Rabatt_Code, $discount, $order->getId());

        // no fidelity points for order with discount
        $this->assertEquals(0, $order->getCustomer()->getFidelity()->getPoints(), sprintf('customer #%d - Email: "%s" - order #%d', $order->getCustomer()->getId(), $order->getCustomer()->getEmail(), $order->getId()));
        $this->assertTrue($discount instanceof Yourdelivery_Model_Rabatt_Code);
        if ($order->getStatus() == -3) {
            $this->assertTrue($discount->isUsable());
            $order->finalizeOrderAfterPayment($order->getPayment(), false, false, false, false);
        } else {
            $this->assertFalse($discount->isUsable());
        }
        $order->setStatus(Yourdelivery_Model_Order::PAYMENT_NOT_AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON, 'testcase'));
        $this->assertEquals(0, $order->getCustomer()->getFidelity()->getPoints());
        $this->assertFalse($discount->isUsable());
        $order->setStatus(Yourdelivery_Model_Order::STORNO, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON, 'testcase'));
        $this->assertEquals(0, $order->getCustomer()->getFidelity()->getPoints());
        $this->assertTrue($discount->isUsable());
        $order->setStatus(Yourdelivery_Model_Order::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON, 'testcase'));
        $this->assertEquals(0, $order->getCustomer()->getFidelity()->getPoints());
        $this->assertFalse($discount->isUsable());
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 23.12.2011
     */
    public function testRemoveDiscount() {
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => 'paypal', 'discount' => true)));
        $discount = $order->getDiscount();
        if ($order->getStatus() == -3) {
            $this->assertTrue($discount->isUsable());
        } else {
            $this->assertFalse($discount->isUsable());
        }
        $this->assertTrue($discount instanceof Yourdelivery_Model_Rabatt_Code);
        $this->assertTrue($order->removeDiscount());
        $this->assertTrue($discount->isUsable());
        $this->assertNull($order->getDiscount());
        $this->assertEquals($order->getDiscountAmount(), 0);
        //test if db is also updated

        $newOrder = new Yourdelivery_Model_Order($order->getId());
        $this->assertNull($newOrder->getDiscount());
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 23.12.2011
     */
    public function testAddDiscount() {
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => 'paypal')));
        $this->assertNull($order->getDiscount());

        $discount = $this->createDiscount();
        $this->assertTrue($discount->isUsable());
        $order->addDiscount($discount);
        if ($order->getStatus() == 3) {
            $order->setStatus(Yourdelivery_Model_Order_Abstract::DELIVERED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "Testcase addDiscount confirm after fake"));
        }

        $order->finalizeOrderAfterPayment($order->getPayment(), false, false, false, false);
        $this->assertTrue($discount instanceof Yourdelivery_Model_Rabatt_Code);
        $this->assertFalse($discount->isUsable());

        //test if db is also updated
        $newOrder = new Yourdelivery_Model_Order($order->getId());
        $this->assertTrue($newOrder->getDiscount() instanceof Yourdelivery_Model_Rabatt_Code);
    }

    /**
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 04.05.2012
     */
    public function testIsFinished() {
        $order = Yourdelivery_Model_Order::isFinished();
        $this->assertTrue($order);
    }

    /**
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 04.05.2012
     */
    public function testAll() {

        $this->markTestSkipped('Performance Killer');

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('orders', 'id');
        $result = $db->fetchAll($query);
        $order = Yourdelivery_Model_Order::all();
        $this->assertEquals($order->count(), count($result));
    }

    /**
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 04.05.2012
     */
    public function testAllFast() {

        $this->markTestSkipped('Performance Killer');

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('orders');
        $result = $db->fetchAll($query);
        $this->assertEquals(count($result), count(Yourdelivery_Model_Order::allFast()));
    }

    /**
     * Test getHash, and getHashFromNr
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 04.05.2012
     */
    public function testGetHash() {
        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()
                        ->from('orders', array('id' => 'id', 'hash' => 'hashtag'))
                        ->order('RAND()')->limit(1);

        $row = $db->fetchRow($query);
        $orderId = $row['id'];
        $hash = $row['hash'];
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($hash, $order->getHash());
        $this->assertEquals(md5(SALT . $order->getNr()), $order->getHashFromNr());
    }

    /**
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 04.05.2012
     */
    public function testAllCompany() {

        $this->markTestSkipped('Performance Killer');

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('orders')->where("kind= 'comp'");
        $result = $db->fetchAll($query);
        $this->assertEquals(count($result), count(Yourdelivery_Model_Order::allCompany()));
    }

    /**
     * Test isFavourite, getFavName, getFavId functions.
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 04.05.2012
     */
    public function testIsFavourite() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $this->assertFalse($order->isFavourite());
        $this->assertEquals(NULL, $order->getFavName());
        $this->assertEquals(NULL, $order->getFavId());
        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('favourites', array('id' => 'id', 'orderId' => 'orderId', 'name' => 'name',))->order('RAND()')->limit(1);
        $result = $db->fetchRow($query);
        $order = new Yourdelivery_Model_Order($result['orderId']);
        $this->assertTrue($order->isFavourite());
        $this->assertEquals(lcfirst($result['name']), $order->getFavName());
        $this->assertEquals($result['id'], $order->getFavId());
    }

    /**
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 04.05.2012
     */
    public function testCalculateDeliverRange() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $this->assertEquals(10, $order->calculateDeliverRange());
    }

    /**
     * test cases for getLastStateChange, getLastStateComment, getCurrentState, getStateHistory, getSendbyHistory, getStateName, getStateFromHistory
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 07.05.2012
     */
    public function testGetLastStateChange() {
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('checkForFraud' => 'false')));
        $orderId = $order->getId();
        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('order_status')->where('orderId=' . $orderId)->order('id DESC');
        $State = $db->fetchAll($query);

        $this->assertEquals(strtotime($State[0]['created']), $order->getLastStateChange());
        $this->assertEquals($State[0]['comment'], $order->getLastStateComment());
        $this->assertEquals($State, $order->getStateHistory()->toArray());

        $this->assertEquals($State[0]['comment'], $order->getStateFromHistory($State[0]['status'])->comment);

        $select = $db->select()->from('order_sendby')->where('orderId=' . $orderId);
        $SendBy = $db->fetchAll($select);
        $this->assertEquals($SendBy, $order->getSendbyHistory()->toArray());

        $this->assertEquals('Nicht bestätigt', $order->getStateName());
        $order->setStatus(1, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON));
        $this->assertEquals('Bestätigt', $order->getStateName());
        $order->setStatus(2, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON));
        $this->assertEquals('Ausgeliefert', $order->getStateName());
        $order->setStatus(-1, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON));
        $this->assertEquals('Fehler', $order->getStateName());
        $order->setStatus(-15, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON));
        $this->assertEquals('Fax eventuell durchgegangen (NO_TRAIN)', $order->getStateName());
        $order->setStatus(-2, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON));
        $this->assertEquals('Storniert', $order->getStateName());
        $order->setStatus(-3, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON));
        $this->assertEquals('Fake Bestellung', $order->getStateName());
        $order->setStatus(-5, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON));
        $this->assertEquals('Bezahlung unbestätigt', $order->getStateName());
        $order->setStatus(-7, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON));
        $this->assertEquals('Storniert wegen ungültigem Gutschein', $order->getStateName());

        $order->setStatus(Yourdelivery_Model_Order_Abstract::FAX_ERROR_NO_TRAIN, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON));
        $this->assertEquals('Fax eventuell durchgegangen (NO_TRAIN)', $order->getStateName());
        $this->assertEquals($order->getLastStateStatus(), -15);
    }

    /*
     * test cases for getCourier -- false test
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 07.05.2012
     */

    public function testGetCourier() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $this->assertNull($order->getCourier());

        $courier = $this->getRandomCourier();
        $this->assertInstanceof('Yourdelivery_Model_Courier', $courier);
    }

    /*
     * test cases for getModeReadable
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 07.05.2012
     */

    public function testGetModeReadable() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $this->assertEquals('Lieferservice', $order->getModeReadable());

        $order->setMode('cater');
        $this->assertEquals('Catering', $order->getModeReadable());
        $order->setMode('great');
        $this->assertEquals('Großhandel', $order->getModeReadable());
    }

    /*
     * test cases for getTotalNoTax
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 07.05.2012
     */

    public function testGetNumber() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $data = $order->getData();
        $Nr = $data['nr'];
        $this->assertEquals($Nr, $order->getNumber());
    }

    /*
     * test cases for getPfand
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 07.05.2012
     */

    public function testGetPfand() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $this->assertEquals(0, $order->getPfand());

        $order->setData(array('pfand' => 1));
        $this->assertEquals(1, $order->getPfand());
    }

    /*
     * test cases for isGroupOrder, isSingleOrder, isCompanyGroupOrder, isCompanyOrder
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 07.05.2012
     */

    public function testSingleGroupOrder() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $this->assertFalse($order->isGroupOrder());
        $this->assertTrue($order->isSingleOrder());
        $this->assertFalse($order->isCompanyGroupOrder());

        $data = $order->getData();
        $kind = $data['kind'];
        $this->assertEquals($kind == $order::COMPANYORDER, $order->isCompanyOrder());
    }

    /*
     * test cases for getOrigCustomer
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 07.05.2012
     */

    public function testGetOrigCustomer() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $this->assertInstanceof('Yourdelivery_Model_Customer_Anonym', $order->getOrigCustomer());
    }

    /*
     * test cases for addToFraudMatching
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 08.05.2012
     */

    public function testAddToFraudMatching() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $post = array();
        $post['holder'] = 'tester';
        $post['number'] = '12369854785';
        $post['verification'] = '123';

        $fraud = $order->addToFraudMatching($post);
        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('order_fraud_matching')->where('orderId=' . $order->getId());
        $result = $db->fetchRow($query);
        $this->assertEquals($result['id'], $fraud);
    }

    /*
     * test cases for getCourierFaxClass
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 08.05.2012
     */

    public function testGetCourierFaxClass() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $this->assertInstanceof('Yourdelivery_Model_Order_Pdf_Private_Single_FaxCourier', $order->getCourierFaxClass());

        $order->setData(array('kind' => 'comp'));
        $this->assertInstanceof('Yourdelivery_Model_Order_Pdf_Company_Single_FaxCourier', $order->getCourierFaxClass());
    }

    /*
     * test cases for changeTaxOfMeal, changeTaxOfExtra, changeTaxOfOption, changeCountOfMeal, addBucketMeal, deleteMeal
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 08.05.2012
     */

    public function testGetTaxOfMeal() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $tax = 7;
        $mealIds = $order->getMealIds();
        $this->assertFalse($order->changeTaxOfMeal($mealIds, $tax));
        $this->assertFalse($order->changeTaxOfExtra($mealIds, 0, $tax));
        $this->assertFalse($order->changeTaxOfOption($mealIds, 0, $tax));
        $this->assertFalse($order->changeCountOfMeal($mealIds, 1));
        $this->assertFalse($order->addBucketMeal($mealIds));
        $this->assertFalse($order->deleteMeal($mealIds));

        //get meal bucket
        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->from('orders_bucket_meals')->where('mealId=' . $mealIds[0]);
        $result = $db->fetchRow($query);

        //create BucketMeals Object.
        $meal = new Yourdelivery_Model_Order_BucketMeals($result['id']);

        $this->assertTrue($order->changeTaxOfMeal($meal, $tax));
        $this->assertTrue($order->changeTaxOfExtra($meal, 0, $tax));
        $this->assertTrue($order->changeTaxOfOption($meal, 0, $tax));
        $this->assertTrue($order->changeCountOfMeal($meal, 3));
        $this->assertTrue($order->addBucketMeal($meal));
    }

    /*
     * test cases for changeLocation, changeComment
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 14.05.2012
     */

    public function testChangeOrderLocation() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $Location = $this->getRandomLocation();

        $this->assertFalse($order->changeLocation($order));
        $this->assertTrue($order->changeLocation($Location));

        $this->assertFalse($order->changeComment());
        $comment = 'This is a location Comment.';
        $this->assertTrue($order->changeComment($comment));
    }

    /*
     * test cases for getPromptTrackingId
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 14.05.2012
     */

    public function testGetpromptTrackingId() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $this->assertNull($order->getPromptTrackingId());
        $Track = new Yourdelivery_Model_DbTable_Prompt_Tracking();
        $Row = $Track->createRow(array('orderId' => $order->getId(), 'trackingId' => md5($order->getId())));
        $Row->save();

        $this->assertEquals(md5($order->getId()), $order->getPromptTrackingId());
    }

    /*
     * test cases for getSecret
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 14.05.2012
     */

    public function testGetSecret() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $ident = $order->getData();
        $ident = $ident['ident'];
        $this->assertEquals($ident, $order->getSecret());
    }

    /*
     * test cases for isCompanyCredit
     * @author Mohammad RAWAQA <rawaqah@lieferando.de>
     * @since 14.05.2012
     */

    public function testisCompanyCredit() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $this->assertFalse($order->isCompanyCredit());

        $order->setData(array('kind' => 'comp'));
        $this->assertFalse($order->isCompanyCredit());

        $db = Zend_Registry::get("dbAdapter");

        $select = $db->select('id')->from('order_company_group')
                        ->where('privAmount > 0')
                        ->where("payment = 'credit'")
                        ->order('RAND()')->limit(1);
        $result = $db->fetchRow($select);

        $db->update('order_company_group', array('orderId' => $order->getId()), 'id=' . $result['id']);
        $this->assertTrue($order->isCompanyCredit());
    }

    /**
     * @author Jens Naie <naie@lieferando.de>
     * @since 25.06.2012
     */
    public function testLatestFromCustomer() {
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select('customerId')->from('orders')
                        ->where('customerId IS NOT null')
                        ->where('total > 0')
                        ->order('RAND()')->limit(1);
        $result = $db->fetchRow($select);
        $orders = Yourdelivery_Model_Order::latestFromCustomer($result['customerId']);
        $this->assertGreaterThan(0, count($orders));
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 05.07.2012 
     */
    public function testStoreProv() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $table = new Yourdelivery_Model_DbTable_Order_Provission();
        $row = $table->fetchRow(sprintf('orderId=%d', $order->getId()));
        $service = $order->getService();
        $this->assertEquals($service->getKomm(), $row->prov);
        $this->assertEquals($service->getItem(), $row->item);
        $this->assertEquals($service->getFee(), $row->fee);
    }

    /**
     * check if after deleting a meal we cannot repeat this order
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 31.07.2012
     * @return void
     */
    public function testIsRepeatableDeleteMeal() {
        $order = $this->findRepeatableOrder();

        //delete meal
        foreach ($order->getCard() as $customerBucket) {
            foreach ($customerBucket as $bucket) {
                foreach ($bucket as $item) {
                    $meal = $item['meal'];
                    $meal->setDeleted(true);
                    $meal->save();
                    $orderClone = new Yourdelivery_Model_Order($order->getId());
                    $this->assertFalse($orderClone->isRepeatable());
                    return;
                }
            }
        }
    }

    /**
     * check if after setting a meal ofline we cannot repeat this order
     * 
     * @author Allen Frank <frank@lieferando.de>
     * @since 31.07.2012
     * @return void
     */
    public function testIsRepeatableOfflineMeal() {
        $order = $this->findRepeatableOrder();

        //delete meal
        foreach ($order->getCard() as $customerBucket) {
            foreach ($customerBucket as $bucket) {
                foreach ($bucket as $item) {
                    $meal = $item['meal'];
                    $meal->setStatus(0);
                    $meal->save();
                    $orderClone = new Yourdelivery_Model_Order($order->getId());
                    $this->assertFalse($orderClone->isRepeatable());
                    return;
                }
            }
        }
    }

    /**
     * check if after deleting a extra we cannot repeat this order
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 31.07.2012
     * @return void
     */
    public function testIsRepeatableDeleteExtra() {
        $order = $this->findRepeatableOrder();

        //delete extra
        foreach ($order->getCard() as $customerBucket) {
            foreach ($customerBucket as $bucket) {
                foreach ($bucket as $item) {
                    $meal = $item['meal'];
                    foreach ($meal->getCurrentExtras() as $extra) {
                        $meal->getTable()->getAdapter()->query('delete from meal_extras_relations where extraId=?', $extra->getId());
                        $meal->getTable()->getAdapter()->query('delete from meal_extras where id=?', $extra->getId());
                        $orderClone = new Yourdelivery_Model_Order($order->getId());
                        $this->assertFalse($orderClone->isRepeatable());
                        return;
                    }
                }
            }
        }
    }

    /**
     * check if after deleting a option we cannot repeat this order
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 31.07.2012
     * @return void
     */
    public function testIsRepeatableDeleteOption() {
        $order = $this->findRepeatableOrder();

        //delete option
        foreach ($order->getCard() as $customerBucket) {
            foreach ($customerBucket as $bucket) {
                foreach ($bucket as $item) {
                    $meal = $item['meal'];
                    foreach ($meal->getCurrentOptions() as $option) {
                        $meal->getTable()->getAdapter()->query('delete from meal_options_nn where optionId=?', $option->getId());
                        $meal->getTable()->getAdapter()->query('delete from meal_options where id=?', $option->getId());
                        $orderClone = new Yourdelivery_Model_Order($order->getId());
                        $this->assertFalse($orderClone->isRepeatable());
                        return;
                    }
                }
            }
        }
    }
    
    private function findRepeatableOrder(){
        $repeatable = false;
        $i = 0;
        while (!$repeatable) {
            $i++;
            $order = new Yourdelivery_Model_Order($this->placeOrder());            
            $repeatable = $order->isRepeatable();                        
            if($i>=42){
                $this->assertFalse(TRUE,"Couldn't find a repeatable order.");
           }
        }
        return $order;
    }
}
