<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 18.11.2011
 */
/**
 * @runTestsInSeparateProcesses 
 */
class CustomerStatsApiTest extends Yourdelivery_Test {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 18.11.2011
     */
    public function testCustomerStatsGet() {

        // new customer should not have any orders
        $customer = $this->createCustomer();
        $access = $customer->getSalt();


        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_customer_stats/' . $access);
        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $customerId = $doc->getElementsByTagName("customerid");
        $this->assertEquals($customer->getId(), $customerId->item(0)->nodeValue);

        $this->assertEquals(1, $doc->getElementsByTagName("firstorder")->length);
        $firstorder = $doc->getElementsByTagName("firstorder");
        $this->assertEquals(null, $firstorder->item(0)->nodeValue);
        $this->assertEquals(1, $doc->getElementsByTagName("lastorder")->length);
        $lastorder = $doc->getElementsByTagName("lastorder");
        $this->assertEquals(null, $lastorder->item(0)->nodeValue);
        $this->assertEquals(1, $doc->getElementsByTagName("countorders")->length);
        $countorders = $doc->getElementsByTagName("countorders");
        $this->assertEquals(0, $countorders->item(0)->nodeValue);


        // place two orders for this customer and confirm
        $firstOrder = new Yourdelivery_Model_Order($this->placeOrder(array('customer'=> $customer)));
        $deliverTimeFirstOrder = $firstOrder->getDeliverTime();
        $firstOrder->setState(1);
        $firstOrder->save();

        $this->assertFalse($firstOrder->isRated());

        // rate first order (via api)
        $post = array(
            'hash' => $firstOrder->getHash(),
            'advise' => rand(0,1),
            'title' => 'this is a test title',
            'quality' => rand(1,5),
            'delivery' => rand(1,5),
            'comment' => 'this is a test comment'
        );
        $this->resetRequest();
        $this->resetResponse();
        $request = $this->getRequest();
        $json = '{"hash":"'.$firstOrder->getHash().'", "advise":"'.rand(0,1).'", "title":"this is a test title", "quality":"'.rand(1,5).'", "delivery":"'.rand(1,5).'", "comment":"this is a test comment"}';
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_ratings');

        $this->assertController('get_ratings');
        $this->assertAction('post');

        $this->assertTrue($firstOrder->isRated());
        $this->assertResponseCode(201);

        $db = Zend_Registry::get('dbAdapter');
        $db->query('UPDATE restaurant_ratings rr SET status = 1 WHERE orderId = '.$firstOrder->getId());
        $db->query('UPDATE customer_fidelity_transaction SET status = 0
            WHERE email = "'.$firstOrder->getCustomer()->getEmail().'"
                AND action LIKE "rate_%"
                AND transactionData = '.$firstOrder->getId());

        $lastOrder = new Yourdelivery_Model_Order($this->placeOrder(array('customer'=> $customer)));
        $deliverTimeLastOrder = $lastOrder->getDeliverTime();
        $lastOrder->setState(1);
        $lastOrder->save();

        $newDeliverTimeFirstOrder = strtotime('-1 day');
        $db->query('UPDATE orders SET deliverTime = "'.date('Y-m-d H:i:s',$newDeliverTimeFirstOrder) .'" WHERE id = '.$firstOrder->getId());

        $this->resetRequest();
        $this->resetResponse();
        $request->setMethod('GET');
        $this->dispatch('/get_customer_stats/' . $access);
        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $customerId = $doc->getElementsByTagName("customerid");
        $this->assertEquals($customer->getId(), $customerId->item(0)->nodeValue);

        $this->assertEquals(1, $doc->getElementsByTagName("firstorder")->length);
        $firstorder = $doc->getElementsByTagName("firstorder");
        $this->assertEquals($newDeliverTimeFirstOrder, strtotime($firstorder->item(0)->nodeValue));

        $this->assertEquals(1, $doc->getElementsByTagName("lastorder")->length);
        $lastorder = $doc->getElementsByTagName("lastorder");
        $this->assertEquals($deliverTimeLastOrder, strtotime($lastorder->item(0)->nodeValue));

        $this->assertEquals(1, $doc->getElementsByTagName("countorders")->length);
        $countorders = $doc->getElementsByTagName("countorders");
        $this->assertEquals(2, $countorders->item(0)->nodeValue);

        $this->assertEquals(1, $doc->getElementsByTagName("order")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("ratedorders")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("unratedorders")->length);

        $this->assertEquals(0, $doc->getElementsByTagName("maxavailablefidelitypoints")->item(0)->nodeValue);
        $this->assertEquals(2, $doc->getElementsByTagName("earnedfidelitypoints")->item(0)->nodeValue);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testGetUnratedOrders(){
        // get customer with at least one unrated order
        $customer = $this->getRandomCustomer();
        $orderId = $this->placeOrder(array('customer' => $customer));

        $db= $this->_getDbAdapter();
        $db->query(sprintf("UPDATE orders set deliverTime = '%s', state = 1 WHERE id = %d", date('Y-m-d H:i:s', strtotime('yesterday')),$orderId));

        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_customer_stats/' . $customer->getSalt());
        $this->assertController('get_customer_stats');
        $this->assertAction('get');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);
        $this->assertGreaterThanOrEqual(5,$doc->getElementsByTagName("maxavailablefidelitypoints")->item(0)->nodeValue);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testGetWithInvalidAccess(){
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_customer_stats/invalid-access' );
        $this->assertController('get_customer_stats');
        $this->assertAction('get');
        $this->assertResponseCode(404);

        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $this->assertEquals("no access", $doc->getElementsByTagName("message")->item(0)->nodeValue);

    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testIndexFail() {
        $request = $this->getRequest();
        $this->dispatch('/get_customer_stats?blub=bla');
        $this->assertController('get_customer_stats');
        $this->assertAction('index');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPostFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_customer_stats');
        $this->assertController('get_customer_stats');
        $this->assertAction('post');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPutFail() {
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $this->dispatch('/get_customer_stats');
        $this->assertController('get_customer_stats');
        $this->assertAction('put');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testDeleteFail() {
        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $this->dispatch('/get_customer_stats');
        $this->assertController('get_customer_stats');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }

}
