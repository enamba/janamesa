<?php

/**
 * Description of RatingTest
 *
 * @author mlaug
 */
/**
 * @runTestsInSeparateProcesses 
 */
class OrderApiRatingTest extends Yourdelivery_Test {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 09.01.2012
     */
    public function testGetFail() {
        $this->dispatch('/get_ratings/999999999');
        $this->assertController('get_ratings');
        $this->assertAction('get');
        $this->assertResponseCode(404);

        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $this->assertEquals('Service could not be found', $doc->getElementsByTagName("message")->item(0)->nodeValue);
    }
   
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 09.01.2012
     */
    public function testGetSuccess() {
        $db = Zend_Registry::get('dbAdapter');
        $restId = $db->fetchOne('SELECT r.id FROM restaurants r JOIN `restaurant_ratings` rr ON rr.`restaurantId` = r.id WHERE rr.status = 1 and r.deleted = 0 ORDER BY rand() LIMIT 1');

        $this->dispatch('/get_ratings/' . $restId);
        $this->assertController('get_ratings');
        $this->assertAction('get');
        $this->assertResponseCode(200);
        
        
        $data = $this->getResponse()->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $this->assertEquals(1, $doc->getElementsByTagName("count")->length);
        $this->assertGreaterThanOrEqual(1, $doc->getElementsByTagName("advise")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("qualityStars")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("quality5")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("quality4")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("quality3")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("quality2")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("quality1")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("deliveryStars")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("delivery5")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("delivery4")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("delivery3")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("delivery2")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("delivery1")->length);
        
        foreach ($doc->getElementsByTagName("profileimage") as $elem){
            $this->assertGreaterThan(5, strlen($elem->nodeValue));
        }
    }

    /**
     * @author mlaug
     * @since 24.10.2011
     */
    public function testNotFoundRating() {

        $json = '{"hash":"asd", "advise":"1", "title":"this is a test title", "quality":"5", "delivery":"5", "comment":"this is a test comment"}';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_ratings');
        $this->assertController('get_ratings');
        $this->assertAction('post');
        $this->assertResponseCode(404);
    }

    /**
     * test rating an order via api
     * @author mlaug
     * @since 24.10.211
     */
    public function testCreateSuccessfullRatingShort() {
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertFalse($order->isRated());

        $json = '{"hash":"'.$order->getHash().'", "advise":"1", "title":"this is a test title", "quality":"5", "delivery":"5", "comment":"this is a SHORT test comment"}';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_ratings');

        $this->assertController('get_ratings');
        $this->assertAction('post');

        $this->assertResponseCode(201);

        //check response
        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $fidelityConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini');
        $points = $doc->getElementsByTagName("points");
        $this->assertEquals($fidelityConfig->testing->fidelity->points->rate_low, $points->item(0)->nodeValue);

        unset($order);
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertTrue($order->isRated());

        //once the order has been rated, this should not happen again
        $this->dispatch('/get_ratings');
        $this->assertResponseCode(409);

        // check, that rating is offline
        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchRow('SELECT * FROM restaurant_ratings rr ORDER by id DESC limit 1');
        $this->assertEquals(0, $row['status']);
    }


    /**
     * test rating an order via api
     * @author mlaug
     * @since 24.10.211
     */
    public function testCreateSuccessfullRatingShortWithAuthor() {
        $customer = $this->getRandomCustomer();
        $orderId = $this->placeOrder(array('customer' => $customer));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertFalse($order->isRated());
        $order->setStatus(Yourdelivery_Model_Order::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON, 'testcase'));

        /**
         * set deliver time in past to check the unrated orders, because there is a timeout in query
         */
        $db = Zend_Registry::get('dbAdapter');
        $db->query(sprintf('Update orders SET time = "%s", deliverTime = "%s" WHERE id = %d', date('Y-m-d H:i:s', strtotime('-2 hours')),date('Y-m-d H:i:s', strtotime('-2 hours')), $order->getId()));
        
        
        $unratedOrders = $customer->getUnratedOrders(5, 0);

        $os = array();
        foreach($unratedOrders as $o){
            $os[] = $o['order_id'];
        }

        $this->assertTrue(in_array($orderId, $os));

        $json = '{"hash":"'.$order->getHash().'", "advise":"1", "title":"this is a test title", "quality":"5", "delivery":"5", "comment":"this is a SHORT test comment","author":"Felix"}';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_ratings');

        $this->assertController('get_ratings');
        $this->assertAction('post');

        $this->assertResponseCode(201);

        $unratedOrders = $customer->getUnratedOrders(5, 0);
        $os = array();
        foreach($unratedOrders as $o){
            $os[] = $o['order_id'];
        }

        $this->assertFalse(in_array($orderId, $os));

        //check response
        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $fidelityConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini');
        $points = $doc->getElementsByTagName("points");
        $this->assertEquals($fidelityConfig->testing->fidelity->points->rate_low, $points->item(0)->nodeValue);

        unset($order);
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertTrue($order->isRated());

        //once the order has been rated, this should not happen again
        $this->dispatch('/get_ratings');
        $this->assertResponseCode(409);

        // check showing in rated orders
        $ratedOrders = $customer->getRatedOrders(5, 0);
        $os = array();
        foreach($ratedOrders as $o){
            $os[] = $o['order_id'];
        }

        $this->assertTrue(in_array($orderId, $os));

        // check, that rating is offline
        
        $row = $db->fetchRow("SELECT * FROM restaurant_ratings rr WHERE orderId = {$orderId} ORDER by id DESC limit 1");
        $this->assertEquals(0, $row['status']);
        $this->assertEquals('Felix', $row['author']);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 30.01.2012
     */
    public function testCreateSuccessfullRatingLong() {
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertFalse($order->isRated());

        $json = '{"hash":"'.$order->getHash().'", "advise":"1", "title":"this is a test title", "quality":"5", "delivery":"5", "comment":"this is a LONG test comment with mire than 50 characters"}';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_ratings');

        $this->assertController('get_ratings');
        $this->assertAction('post');

        $this->assertResponseCode(201);

        //check response
        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $fidelityConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini');
        $points = $doc->getElementsByTagName("points");
        $this->assertEquals($fidelityConfig->testing->fidelity->points->rate_high, $points->item(0)->nodeValue);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 30.01.2012
     */
    public function testPostWithIncorrectJson() {

        $json = '{"hash":"ojhgjhlglglhj", "advise":"1}';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_ratings');

        $this->assertController('get_ratings');
        $this->assertAction('post');

        $this->assertResponseCode(406);
    }


    public function testPostWithInvalidData() {
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertFalse($order->isRated());

        $json = '{"hash":"'.$order->getHash().'", "advise":"3", "title":"this is a test title", "quality":"5", "delivery":"5", "comment":"this is a test comment"}';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_ratings');

        $this->assertController('get_ratings');
        $this->assertAction('post');

        $this->assertResponseCode(406);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testIndexFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_ratings?foo=bar');
        $this->assertController('get_ratings');
        $this->assertAction('index');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPutFail() {
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $this->dispatch('/get_ratings');
        $this->assertController('get_ratings');
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
        $this->dispatch('/get_ratings');
        $this->assertController('get_ratings');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }

}

