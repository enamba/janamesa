<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 *
 * @runTestsInSeparateProcesses
 */
class OrderApiTest extends Yourdelivery_Test {

    /**
     * @author Felix Haferkorn
     * @since 29.12.2011 (modified 16.02.2012)
     */
    public function testOrderWithWrongParameters() {
        list($service, $meal, $row) = $this->getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount();
        $this->openService($service, time());

        $customer = $this->getRandomCustomer();

        $jsonDataIncorrectPayment = '{
            "uuid" : "my-random-uuid-' . time() . '",
            "customer":
                {
                    "name":"' . $customer->getName() . '",
                    "prename":"' . $customer->getPrename() . '",
                    "email":"' . $customer->getEmail() . '",
                    "tel":"' . $customer->getTel() . '"
                },
            "payment":"some-inacceptable-payment",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"another-random-hausnr-' . time() . '",
                     "street":"another-random-street-' . time() . '",
                     "cityId":"' . $row['cityId'] . '",
                     "city":"blub bla",
                     "comment":"no comment",
                     "tel":"another-random-tel-' . time() . '"
                 },
            "serviceId": "' . $service->getId() . '",
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":4,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }';

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $jsonDataIncorrectPayment));
        $this->dispatch('/get_order?format=json');

        $this->assertResponseCode(406);

        $this->resetRequest();
        $this->resetResponse();

        $jsonDataIncorrectServiceId = '{
            "uuid" : "my-random-uuid-' . time() . '",
            "customer":
                {
                    "name":"' . $customer->getName() . '",
                    "prename":"' . $customer->getPrename() . '",
                    "email":"' . $customer->getEmail() . '",
                    "tel":"' . $customer->getTel() . '"
                },
            "payment":"bar",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"another-random-hausnr-' . time() . '",
                     "street":"another-random-street-' . time() . '",
                     "cityId":"' . $row['cityId'] . '",
                     "city":"blub",
                     "comment":"no comment",
                     "tel":"another-random-tel-' . time() . '"
                 },
            "serviceId": "blub",
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":4,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }';


        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $jsonDataIncorrectServiceId));
        $this->dispatch('/get_order?format=json');

        $this->assertResponseCode(406);

        $this->resetRequest();
        $this->resetResponse();

        $jsonDataIncorrectMeals = '{
            "uuid" : "my-random-uuid-' . time() . '",
            "customer":
                {
                    "name":"' . $customer->getName() . '",
                    "prename":"' . $customer->getPrename() . '",
                    "email":"' . $customer->getEmail() . '",
                    "tel":"' . $customer->getTel() . '"
                },
            "payment":"bar",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"another-random-hausnr-' . time() . '",
                     "street":"another-random-street-' . time() . '",
                     "cityId":"' . $row['cityId'] . '",
                     "city":"blub",
                     "comment":"no comment",
                     "tel":"another-random-tel-' . time() . '"
                 },
            "serviceId": "' . $service->getId() . '",
            "meals":""
            }';

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $jsonDataIncorrectMeals));
        $this->dispatch('/get_order?format=json');

        $this->assertResponseCode(406);
    }

    /**
     * @author Felix Haferkorn
     * @since 16.02.2012 (modified)
     */
    public function testOrderCashWithFallback() {
        list($service, $meal, $row) = $this->getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount();
        $this->openService($service, time());

        $jsonWithoutCityId = '{
            "uuid" : "tester",
            "customer":
                {
                    "name":"Kaas",
                    "prename":"Eli",
                    "email":"eliego@bemce.se",
                    "tel":"123123"
                },
            "payment":"bar",
            "deliverTime":
               {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"123",
                     "street":"Adsf",
                     "plz":"' . $row['plz'] . '",
                     "city":"Pseudocity",
                     "comment":"",
                     "tel":"1231237"
                 },
            "serviceId": ' . $service->getId() . ',
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":4,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }';

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $jsonWithoutCityId));
        $this->dispatch('/get_order?format=json');

        $this->assertController('get_order');
        $this->assertAction('post');

        $response = $this->getResponse()->getBody();
        $json = json_decode($response, true);

        $this->assertResponseCode(201, 'serviceId:' . $service->getId() . ' - cityId: ' . $service->getCity()->getId() . ' - stateId: ' . $service->getCity()->getStateId() . ' - Response: ' . $json['message']);
        $this->assertGreaterThan(0, $json['id']);

        //test for status code
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from('order_status')->where('orderId=?', $json['id'])->order('id DESC')->limit(1);
        $row = $db->fetchRow($select);
        $this->assertTrue(!is_null($row['status']));
    }

    /**
     * @author Felix Haferkorn
     * @since 16.02.2012 (modified)
     */
    public function testOrderCashWithoutFallback() {
        list($service, $meal, $row) = $this->getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount();
        $this->openService($service, time());

        $jsonWithoutCityId = '{
            "uuid" : "tester",
            "customer":
                {
                    "name":"Kaas",
                    "prename":"Eli",
                    "email":"eliego@bemce.se",
                    "tel":"123123"
                },
            "payment":"bar",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"123",
                     "street":"Adsf",
                     "plz":"' . $row['plz'] . '",
                     "cityId" : "' . $row['cityId'] . '",
                     "city":"Berlin",
                     "comment":"",
                     "tel":"1231237"
                 },
            "discountCode":null,
            "serviceId": ' . $service->getId() . ',
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":4,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }';

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $jsonWithoutCityId));
        $this->dispatch('/get_order?format=json');

        $response = $this->getResponse()->getBody();
        $json = json_decode($response, true);
        $this->assertGreaterThan(0, $json['id'], $json['message']);

        $this->assertResponseCode(201, $json['message']);

        //test for status code
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from('order_status')->where('orderId=?', $json['id'])->order('id DESC')->limit(1);
        $row = $db->fetchRow($select);
        $this->assertTrue(!is_null($row['status']));
    }

    /**
     * @author Felix Haferkorn
     */
    public function testloggedInOrderCash() {
        list($service, $meal, $row) = $this->getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount();
        $this->openService($service, time());

        $customer = $this->getRandomCustomer();

        $jsonWithoutCityId = '{
            "uuid" : "rand-uuid-' . rand(123456789, 987654321) . '",
            "customer":
                {
                    "access":"' . $customer->getSalt() . '",
                    "name":"' . $customer->getName() . '",
                    "prename":"' . $customer->getPrename() . '",
                    "email":"' . $customer->getEmail() . '",
                    "tel":"' . $customer->getTel() . '"
                },
            "payment":"bar",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"123",
                     "street":"Adsf",
                     "plz":"' . $row['plz'] . '",
                     "cityId" : "' . $row['cityId'] . '",
                     "city":"Berlin",
                     "comment":"",
                     "tel":"1231237"
                 },
            "discountCode":null,
            "serviceId": ' . $service->getId() . ',
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":4,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }';

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $jsonWithoutCityId));
        $this->dispatch('/get_order?format=json');

        $response = $this->getResponse()->getBody();
        $json = json_decode($response, true);
        #var_dump($json['message']);
        #var_dump($json['fidelity']['message']);
        #die;
        $this->assertGreaterThan(0, $json['id'], $json['message']);

        $this->assertResponseCode(201, $json['message']);

        //test for status code
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from('order_status')->where('orderId=?', $json['id'])->order('id DESC')->limit(1);
        $row = $db->fetchRow($select);
        $this->assertTrue(!is_null($row['status']));

        $select = $db->select()->from(array('o' => 'orders'))->where('id = ? ', $json['id'])->where('customerId = ?', $customer->getId());
        $row = $db->fetchRow($select);
        $this->assertEquals($customer->getId(), $row['customerId']);
    }

    /**
     * @author Felix Haferkorn
     * @since 16.02.2012 (modified)
     */
    public function testOrderPayPal() {
        list($service, $meal, $row) = $this->getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount();
        $this->openService($service, time());

        $jsonWithoutCityId = '{
            "uuid" : "tester",
            "customer":
                {
                    "name":"Kaas",
                    "prename":"Eli",
                    "email":"eliego@bemce.se",
                    "tel":"123123"
                },
            "payment":"paypal",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"123",
                     "street":"Adsf",
                     "plz":"' . $row['plz'] . '",
                     "cityId" : "' . $row['cityId'] . '",
                     "city":"Berlin",
                     "comment":"",
                     "tel":"1231237"
                 },
            "discountCode":null,
            "serviceId":' . $service->getId() . ',
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":4,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }';

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $jsonWithoutCityId));
        $this->dispatch('/get_order');

        $this->assertResponseCode(200);
        $body = $this->getResponse()->getBody();
        $xml = simplexml_load_string($body);
        $this->assertEquals('paypal', $xml->payment->method->__toString());
        $this->assertGreaterThan(0, strlen($xml->payment->token->__toString()));
    }

    /**
     * @author Felix Haferkorn
     * @since 05.04.2012
     */
    public function testOrderPayPalWithDiscount() {
        $code = $this->createDiscount(0, 1, 50, true, false, false, false, false, null, null, true, true);
        $this->assertTrue($code->isUsable());

        list($service, $meal, $row) = $this->getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount();
        $this->openService($service, time());

        $json = '{
            "uuid" : "tester",
            "customer":
                {
                    "name":"Kaas",
                    "prename":"Eli",
                    "email":"eliego@bemce.se",
                    "tel":"123123"
                },
            "payment":"paypal",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"123",
                     "street":"Adsf",
                     "plz":"' . $row['plz'] . '",
                     "cityId" : ' . $row['cityId'] . ',
                     "city":"Berlin",
                     "comment":"",
                     "tel":"1231237"
                 },
            "discountCode":"' . $code->getCode() . '",
            "serviceId":' . $service->getId() . ',
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":4,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }';

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_order');

        $this->assertResponseCode(200);
        $body = $this->getResponse()->getBody();
        $xml = simplexml_load_string($body);
        $this->assertEquals('paypal', $xml->payment->method->__toString());
        $this->assertGreaterThan(0, strlen($xml->payment->token->__toString()));

        // here we simulate successful payment
        $orderId = $xml->id->__toString();

        $order = new Yourdelivery_Model_Order($orderId);
        $order->finalizeOrderAfterPayment('paypal', false, false, false, false);

        $this->assertFalse($code->isUsable());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 18.01.2012
     */
    public function testOrderBelowMinamountFail() {
        list($service, $meal, $row) = $this->getRandomServiceWithDeliverRangeAndMealBelowMinamount();
        $this->openService($service, time());

        $customer = $this->getRandomCustomer(false);

        $json = sprintf('{
            "uuid" : "tester",
            "customer":
                {
                    "name":"' . $customer->getName() . '",
                    "prename":"' . $customer->getPrename() . '",
                    "email":"' . $customer->getEmail() . '",
                    "tel":"' . $customer->getTel() . '"
                },
            "payment":"bar",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"123",
                     "street":"Adsf",
                     "plz":' . $row['plz'] . ',
                     "cityId" : "' . $row['cityId'] . '",
                     "city":"Berlin",
                     "comment":"",
                     "tel":"1231237"
                 },
            "serviceId":' . $service->getId() . ',
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":1,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }');

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_order');

        $this->assertController('get_order');
        $this->assertAction('post');

        $this->assertResponseCode(406, sprintf('succeeded to order below minamount. service #%d - city #%d - meal #%d - size #%d - minamount %s', $service->getId(), $row['cityId'], $meal->getId(), $row['sizeId'], $service->getMinCost()));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 02.02.2012 (modified 16.02.2012)
     *
     * @see http://ticket.yourdelivery.local/browse/YD-1202
     */
    public function testOrderInOfflinePLZFail() {
        list($service, $meal, $row) = $this->getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount();
        $this->openService($service, time());
        // set range offline
        $db = $this->_getDbAdapter();
        $db->query(sprintf('Update restaurant_plz set status = 0 where restaurantId = %d AND cityId = %d', $service->getId(), $row['cityId']));

        $this->assertFalse($service->isRangeOnline($row['cityId']), $row['cityId'] . '-' . $service->getId() . '-' . $row['rangeId']);

        $customer = $this->getRandomCustomer(false);
        $jsonWithOfflinePLZ = sprintf('{
            "uuid" : "tester",
            "customer":
                {
                    "name":"' . $customer->getName() . '",
                    "prename":"' . $customer->getPrename() . '",
                    "email":"' . $customer->getEmail() . '",
                    "tel":"' . $customer->getTel() . '"
                },
            "payment":"bar",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"123",
                     "street":"Adsf",
                     "plz":"' . $row['plz'] . '",
                     "cityId" : "' . $row['cityId'] . '",
                     "city":"Berlin",
                     "comment":"",
                     "tel":"1231237"
                 },
            "serviceId":' . $service->getId() . ',
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":4,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }');

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $jsonWithOfflinePLZ));
        $this->dispatch('/get_order');

        $this->assertController('get_order');
        $this->assertAction('post');

        $db->query(sprintf('Update restaurant_plz set status = 1 where restaurantId = %d AND cityId = %d', $service->getId(), $row['cityId']));

        /**
         * temporarly we allow this. a loggin mail was added in controller. we will get informed via email
         * lets have a look, how often such a situation will take place
         */
        $this->assertResponseCode(201);
        #$this->assertResponseCode(407);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 18.01.2012
     *
     * @see http://ticket.yourdelivery.local/browse/YD-1201
     */
    public function testOrderWith100PercentDiscount() {
        $code100percent = $this->createDiscount(false, 0, 100);
        list($service, $meal, $row) = $this->getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount();
        $this->openService($service, time());

        $jsonWithoutCityId = '{
            "uuid" : "tester",
            "customer":
                {
                    "name":"Kaas",
                    "prename":"Eli",
                    "email":"eliego@bemce.se",
                    "tel":"123123"
                },
            "payment":"paypal",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"123",
                     "street":"Adsf",
                     "plz":"10115",
                     "cityId" : "' . $row['cityId'] . '",
                     "city":"Berlin",
                     "comment":"",
                     "tel":"1231237"
                 },
            "discountCode":"' . $code100percent->getCode() . '",
            "serviceId":' . $service->getId() . ',
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":4,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }';

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $jsonWithoutCityId));
        $this->dispatch('/get_order?format=json');

        $this->assertResponseCode(201);

        $response = $this->getResponse()->getBody();
        $json = json_decode($response, true);
        $this->assertGreaterThan(0, $json['id']);

        // some DB checks
        $table = new Yourdelivery_Model_DbTable_Order();
        $row = $table->findById($json['id']);

        $this->assertEquals($row['id'], $json['id']);
        $this->assertGreaterThan(0, $row['discountAmount']);
        $this->assertGreaterThan(0, $row['total']);
        $this->assertEquals($row['discountAmount'], ($row['total'] + $row['serviceDeliverCost'] + $row['courierCost'] - $row['courierDiscount']));
        $this->assertEquals(0, $row['charge']);
        $this->assertEquals('bar', $row['payment']);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function testIndexActionWithAccess() {
        $customer = $this->getRandomCustomer();
        $count = $customer->getOrders()->count();
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('customer' => $customer)));
        if ($order->getState() == Yourdelivery_Model_Order::FAKE) {
            // try again ;-)
            $order = new Yourdelivery_Model_Order($this->placeOrder(array('customer' => $customer)));
        }
        $this->dispatch('/get_order?access=' . $customer->getSalt());

        $response = $this->getResponse();
        $xml = $response->getBody();

        $customer->clearCache();
        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->markTestSkipped('Test wird geskipped weil er mit verwendung des dataview immer fehlschlagen würde.');

        $this->assertEquals($count + 1, $doc->getElementsByTagName("order")->length, 'customerId: ' . $customer->getId());
    }

    /**
     * @author Felix Haferkorn
     * @since 31.01.2012
     */
    public function testOrderWithoutAnyParameters() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_order');
        $this->assertResponseCode(406);
    }

    /**
     * @author Felix Haferkorn
     * @since 31.01.2012
     */
    public function testOrderIndexWithUuid() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()->from(array('o' => 'orders'))
                        ->where('uuid IS NOT NULL')
                        ->where('state > 0')
                        ->order('RAND()')->limit(1);
        $orderRow = $db->fetchRow($query);
        $this->dispatch('/get_order?uuid=' . $orderRow['uuid']);

        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $this->assertEquals(0, $doc->getElementsByTagName("order")->length);
    }

    /**
     * @author Felix Haferkorn
     * @since 31.01.2012
     */
    public function testOrderIndexWithEmptyUuid() {
        $this->dispatch('/get_order?uuid=');
        $this->assertResponseCode(404);
    }

    /**
     * @author Felix Haferkorn
     * @since 31.01.2012
     */
    public function testOrderIndexWithEmptyAccess() {
        $this->dispatch('/get_order?access=');
        $this->assertResponseCode(404);
    }

    /**
     * @author Felix Haferkorn
     * @since 31.01.2012
     */
    public function testOrderIndexWithWrongAccess() {
        $this->dispatch('/get_order?access=foobar');
        $this->assertResponseCode(406);
    }

    /**
     * @author Felix Haferkorn
     * @since 29.12.2011
     */
    public function testOrderWithEmptyParameters() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => '{}'));
        $this->dispatch('/get_order');
        $this->assertResponseCode(404);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testDeleteFail() {
        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $this->dispatch('/get_order');
        $this->assertController('get_order');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.02.2012
     */
    public function testGetWithoutOrderIdFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_order/' . Default_Helper::generateRandomString());
        $this->assertController('get_order');
        $this->assertAction('get');
        $this->assertResponseCode(404);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.02.2012
     */
    public function testGet() {
        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()
                ->from(array('o' => 'orders'), 'o.id')
                ->order('RAND()')
                ->limit(1);
        $randOrderId = $db->fetchOne($query);

        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_order/' . $randOrderId);
        $this->assertController('get_order');
        $this->assertAction('get');
        $this->assertResponseCode(200);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 09.07.2012
     */
    public function testGetAndClearCache() {
        $this->markTestSkipped('Test wird geskipped weil er mit verwendung des dataview immer fehlschlagen würde. Das Problem wird im Ticket YD-2812 bearbeitet.');
        $customer = $this->getRandomCustomer();

        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_order?access=' . $customer->getSalt());
        $this->assertController('get_order');
        $this->assertAction('index');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $countOrders = $doc->getElementsByTagName("order")->length;

        $this->resetRequest();
        $this->resetResponse();

        // place order & clear cache
        $orderId = $this->placeOrder(array('customer' => $customer));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertNotEquals(Yourdelivery_Model_Order::AFFIRMED, $order->getStatus());
        $order->setStatus(Yourdelivery_Model_Order::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON, 'testcase'));
        // cache of custoemr should be cleared by setStatus

        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_order?access=' . $customer->getSalt());
        $this->assertController('get_order');
        $this->assertAction('index');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $countOrdersNew = $doc->getElementsByTagName("order")->length;

        $this->assertGreaterThan($countOrders, $countOrdersNew);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.08.2012
     * @return \Yourdelivery_Model_Order
     */
    protected function getRandomPayPalOrder() {
        $db = Zend_Registry::get('dbAdapter');
        $orderId = (integer) $db->fetchOne('select id from orders where payment="paypal" and state>0 and time > "2012-07-01" order by RAND() limit 1');
        $this->assertGreaterThan(0, $orderId);
        return new Yourdelivery_Model_Order($orderId);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.08.2012
     */
    public function testPayPalWithNewCustomerDiscount() {
        $order = $this->getRandomPayPalOrder();
        
        //generate newcustomer discount and add to order
        $discount = $this->createNewCustomerDiscount(array(
            'type' => Yourdelivery_Model_Rabatt::TYPE_VERIFCATION_SINGLE_ACTION
        ));
        $discount->generateCodes(1);
        $codeId = $discount->getCodes()->current()->id;
        $this->assertGreaterThan(0, $codeId);
        
        //add discount
        $orderRow = $order->getRow();
        $orderRow->rabattCodeId = $codeId;
        $orderRow->save();
        
        //get payerId
        $payerId = $order->getPayerId();

        //get token
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from('paypal_transactions')
                ->where('orderId = ?', $order->getId())
                ->where('LENGTH(token) > 0')
                ->where('token IS NOT NULL');
        $result = $db->query($select)->fetchAll();
        $token = $result[0]['token'];
        
        //build up json
        $json = sprintf('{"token":"%s","payerId":"%s"}', $token, $payerId);
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $request->setParam('parameters', $json);
        $request->setPost(array('parameters' => $json));
        
        //call put, this should fail!!!
        $this->dispatch('/get_order/' . $order->getId());
        $this->assertAction('put');
        $this->assertResponseCode(406);
        
        //check result in message
        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);
        $this->assertEquals(__('Dieser Gutschein ist nur für Neukunden einlösbar. Dein Paypalkonto wurde nicht belastet.'), $doc->getElementsByTagName("message")->item(0)->nodeValue, $doc->getElementsByTagName("message")->item(0)->nodeValue);      
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.08.2012
     */
    public function testHasAlreadyBeenUsedWithUuid() {
        
        $uuid = uniqid();

        $discount0 = $this->createDiscount(false, 0, 10, false, false, false, false, false, null, null, true, true);
        $discountParent0 = $discount0->getParent();
        $this->assertFalse($discountParent0->hasAlreadyBeenUsedForThatUuid($uuid));
        
        list($service, $meal, $row) = $this->getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount();
        $this->openService($service, time());

        $json = '{
            "uuid" : "'.$uuid.'",
            "customer":
                {
                    "name":"Samson",
                    "prename":"Tiffy",
                    "email":"eliego@bemce.se",
                    "tel":"'.rand(1234567,987654321).'"
                },
            "payment":"paypal",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"123",
                     "street":"Adsf",
                     "plz":"10115",
                     "cityId" : "' . $row['cityId'] . '",
                     "city":"Berlin",
                     "comment":"",
                     "tel":"1231237"
                 },
            "discountCode":"' . $discount0->getCode() . '",
            "serviceId":' . $service->getId() . ',
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":4,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }';

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_order?format=json');

        $this->assertResponseCode(200);
        
        $response = $this->getResponse()->getBody();
        $json = json_decode($response, true);
        $order = new Yourdelivery_Model_Order($json['id']);
        $order->setStatus(Yourdelivery_Model_Order::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'testcase confirm'));

        // order again with same uuid and same discount type 0 - this has to response 200
        list($service, $meal, $row) = $this->getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount();
        $this->openService($service, time());

        $json = '{
            "uuid" : "'.$uuid.'",
            "customer":
                {
                    "name":"Samson",
                    "prename":"Tiffy",
                    "email":"eliego@bemce.se",
                    "tel":"'.rand(1234567,987654321).'"
                },
            "payment":"paypal",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"123",
                     "street":"Adsf",
                     "plz":"10115",
                     "cityId" : "' . $row['cityId'] . '",
                     "city":"Berlin",
                     "comment":"",
                     "tel":"1231237"
                 },
            "discountCode":"' . $discount0->getCode() . '",
            "serviceId":' . $service->getId() . ',
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":4,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }';

        $this->resetRequest();
        $this->resetResponse();
        
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_order?format=json');

        $this->assertResponseCode(200);
        
        // confirm order
        $response = $this->getResponse()->getBody();
        $json = json_decode($response, true);
        $order = new Yourdelivery_Model_Order($json['id']);
        $order->setStatus(Yourdelivery_Model_Order::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'testcase confirm'));

        
        // order with same uuid but different discount type 1 should pass first
        $discount1 = $this->createDiscount(false, 0, 10, false, false, false, false, false, null, null, true, true, 10, 1);
        $discountParent1 = $discount1->getParent();
        $this->assertFalse($discountParent1->hasAlreadyBeenUsedForThatUuid($uuid));
        
        list($service, $meal, $row) = $this->getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount();
        $this->openService($service, time());

        $json = '{
            "uuid" : "'.$uuid.'",
            "customer":
                {
                    "name":"Samson",
                    "prename":"Tiffy",
                    "email":"eliego@bemce.se",
                    "tel":"'.rand(1234567,987654321).'"
                },
            "payment":"paypal",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"123",
                     "street":"Adsf",
                     "plz":"10115",
                     "cityId" : "' . $row['cityId'] . '",
                     "city":"Berlin",
                     "comment":"",
                     "tel":"1231237"
                 },
            "discountCode":"' . $discount1->getCode() . '",
            "serviceId":' . $service->getId() . ',
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":4,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }';
        
        $this->resetRequest();
        $this->resetResponse();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_order?format=json');

        $this->assertResponseCode(200);
        
        // confirm order
        $response = $this->getResponse()->getBody();
        $json = json_decode($response, true);
        $order = new Yourdelivery_Model_Order($json['id']);
        $order->setStatus(Yourdelivery_Model_Order::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'testcase confirm'));
        
        
        // order again with same uuid and same discount type 1 should fail
        $this->assertTrue($discountParent1->hasAlreadyBeenUsedForThatUuid($uuid));
        
        list($service, $meal, $row) = $this->getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount();
        $this->openService($service, time());

        $json = '{
            "uuid" : "'.$uuid.'",
            "customer":
                {
                    "name":"Samson",
                    "prename":"Tiffy",
                    "email":"eliego@bemce.se",
                    "tel":"'.rand(1234567,987654321).'"
                },
            "payment":"paypal",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"123",
                     "street":"Adsf",
                     "plz":"10115",
                     "cityId" : "' . $row['cityId'] . '",
                     "city":"Berlin",
                     "comment":"",
                     "tel":"1231237"
                 },
            "discountCode":"' . $discount1->getCode() . '",
            "serviceId":' . $service->getId() . ',
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":4,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }';
        
        
        $this->resetRequest();
        $this->resetResponse();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_order?format=json');

        $this->assertResponseCode(406);
        
        
        // double check: order with same uuid and new discount type 0 
        $discount0New = $this->createDiscount(false, 0, 10, false, false, false, false, false, null, null, true, true);
        $discountParent0New = $discount0New->getParent();
        $this->assertFalse($discountParent0New->hasAlreadyBeenUsedForThatUuid($uuid));
        
        list($service, $meal, $row) = $this->getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount();
        $this->openService($service, time());

        $json = '{
            "uuid" : "'.$uuid.'",
            "customer":
                {
                    "name":"Samson",
                    "prename":"Tiffy",
                    "email":"eliego@bemce.se",
                    "tel":"'.rand(1234567,987654321).'"
                },
            "payment":"paypal",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"123",
                     "street":"Adsf",
                     "plz":"10115",
                     "cityId" : "' . $row['cityId'] . '",
                     "city":"Berlin",
                     "comment":"",
                     "tel":"1231237"
                 },
            "discountCode":"' . $discount0New->getCode() . '",
            "serviceId":' . $service->getId() . ',
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":4,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }';
        
        $this->resetRequest();
        $this->resetResponse();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_order?format=json');

        $this->assertResponseCode(200);
        
        
        // confirm order
        $response = $this->getResponse()->getBody();
        $json = json_decode($response, true);
        $order = new Yourdelivery_Model_Order($json['id']);
        $order->setStatus(Yourdelivery_Model_Order::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'testcase confirm'));
        
        
        // third check: order with same uuid and already used discount from type 0 - should pass
        $this->assertTrue($discountParent0->hasAlreadyBeenUsedForThatUuid($uuid));
        
        list($service, $meal, $row) = $this->getRandomServiceWithDeliverRangeAndMealWithCount3AboveMinamount();
        $this->openService($service, time());

        $json = '{
            "uuid" : "'.$uuid.'",
            "customer":
                {
                    "name":"Samson",
                    "prename":"Tiffy",
                    "email":"eliego@bemce.se",
                    "tel":"'.rand(1234567,987654321).'"
                },
            "payment":"paypal",
            "deliverTime":
                {
                    "day": "' . date('d.m.Y') . '",
                    "time":"' . date('H:i') . '"
                },
            "location":
                {
                     "hausnr":"123",
                     "street":"Adsf",
                     "plz":"10115",
                     "cityId" : "' . $row['cityId'] . '",
                     "city":"Berlin",
                     "comment":"",
                     "tel":"1231237"
                 },
            "discountCode":"' . $discount0->getCode() . '",
            "serviceId":' . $service->getId() . ',
            "meals":[{
                "id":' . $meal->getId() . ',
                "options":[],
                "count":4,
                "sizeId":' . $row['sizeId'] . ',
                "extras":[]
            }]
            }';
        
        $this->resetRequest();
        $this->resetResponse();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_order?format=json');

        $this->assertResponseCode(200);
        
        
    }

}
