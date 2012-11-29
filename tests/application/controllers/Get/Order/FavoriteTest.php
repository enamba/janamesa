<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class ApiFavoriteTest extends Yourdelivery_Test {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.12.2011
     */
    public function testIndex() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_order_favorite');
        $this->assertController('get_order_favorite');
        $this->assertAction('index');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.12.2011
     */
    public function testGetSuccess() {
        $customer = $this->getRandomCustomer();

        $order = new Yourdelivery_Model_Order($this->placeOrder(array('customer' => $customer)));
        $order->addToFavorite($customer);
        $this->assertTrue($order->isFavourite());

        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_order_favorite/' . $customer->getSalt());
        $this->assertController('get_order_favorite');
        $this->assertAction('get');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $success = $doc->getElementsByTagName("success");
        $this->assertEquals('true', $success->item(0)->nodeValue);
        $this->assertEquals(1, $doc->getElementsByTagName("favorites")->length);

        $this->assertGreaterThan(0, $doc->getElementsByTagName("favorite")->length);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testGetFail(){
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_order_favorite/some-non-existing-hash');
        $this->assertController('get_order_favorite');
        $this->assertAction('get');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.12.2011
     */
    public function testPostSuccess() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_order_favorite');
        // without params
        $this->assertResponseCode(406);

        $this->resetRequest();
        $this->resetResponse();

        // order is not associated with customer -> forbidden
        $cust = $this->getRandomCustomer();
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'parameters' => json_encode(array(
                'access' => $cust->getSalt(),
                'title' => 'Test-Favourite-' . date(),
                'hash' => $order->getHash()
            ))
        ));
        $this->dispatch('/get_order_favorite');
        $this->assertResponseCode(403);
        $this->assertFalse($order->isFavourite());

        $this->resetRequest();
        $this->resetResponse();

        // order is associated with customer -> success
        $order = null;
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('customer' => $cust)));
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'parameters' => json_encode(array(
                'access' => $cust->getSalt(),
                'title' => 'Test-Favourite-' . date(),
                'hash' => $order->getHash()
            ))
        ));
        $this->dispatch('/get_order_favorite');
        $this->assertResponseCode(201);
        $this->assertTrue($order->isFavourite());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testPostFail(){
        // invalid customer hash / access
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'parameters' => json_encode(array(
                'access' => 'any-invalid-hash',
                'title' => 'Test-Favourite-' . date(),
                'hash' => 'blub'
            ))
        ));
        $this->dispatch('/get_order_favorite');
        $this->assertResponseCode(403);

        // invalid order-hash
        $this->resetRequest();
        $this->resetResponse();
        $cust = $this->getRandomCustomer();
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'parameters' => json_encode(array(
                'access' => $cust->getSalt(),
                'title' => 'Test-Favourite-' . date(),
                'hash' => 'invalid-order-hash'
            ))
        ));
        $this->dispatch('/get_order_favorite');
        $this->assertResponseCode(404);
    }


    public function testAddFavoriteTwiceFail(){
        $cust = $this->getRandomCustomer();

        $order = null;
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('customer' => $cust)));
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'parameters' => json_encode(array(
                'access' => $cust->getSalt(),
                'title' => 'Test-Favourite-' . date(),
                'hash' => $order->getHash()
            ))
        ));
        $this->dispatch('/get_order_favorite');
        $this->assertResponseCode(201);
        $this->assertTrue($order->isFavourite());

        $this->resetRequest();
        $this->resetResponse();

        // try to mark this order again as favorite
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'parameters' => json_encode(array(
                'access' => $cust->getSalt(),
                'title' => 'Test-Favourite-' . date(),
                'hash' => $order->getHash()
            ))
        ));
        $this->dispatch('/get_order_favorite');
        $this->assertResponseCode(200);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.12.2011
     */
    public function testPut() {
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $this->dispatch('/get_order_favorite');
        // forbidden
        $this->assertResponseCode(403);
    }

    /**
     * @author mlaug
     * @since 25.10.2011
     *
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 13.12.2011
     */
    public function testDelete() {

        $customer = $this->getRandomCustomer();
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('customer' => $customer)));

        $this->assertTrue($order->addToFavorite($customer));

        $orderId = $order->getId();
        unset($order);
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertTrue($order->isFavourite());

        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $request->setPost(array(
            'parameters' => json_encode(array(
                'access' => $customer->getSalt()
            ))
        ));
        $this->dispatch('/get_order_favorite/' . $order->getId());
        $this->assertResponseCode(200);

        $this->resetRequest();
        $this->resetResponse();

        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $request->setPost(array(
            'parameters' => json_encode(array(
                'access' => $customer->getSalt()
            ))
        ));
        $this->dispatch('/get_order_favorite/anInvalidId');
        $this->assertResponseCode(406);

        $this->assertTrue($order->addToFavorite($customer));

        $db = Zend_Registry::get('dbAdapter');
        $row = $db->fetchRow("SELECT * FROM favourites ORDER BY id DESC LIMIT 1");

        $this->resetRequest();
        $this->resetResponse();
        $customer = $this->getRandomCustomer();
        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $request->setPost(array(
            'parameters' => json_encode(array(
                'access' => $customer->getSalt()
            ))
        ));
        $this->dispatch('/get_order_favorite/' . $row['orderId']);
        $this->assertResponseCode(404);
    }

    public function testDeleteWithInvalidAccessFail(){
        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $request->setPost(array(
            'parameters' => json_encode(array(
                'access' => "invalid-access"
            ))
        ));
        $this->dispatch('/get_order_favorite/any-id');
        $this->assertController('get_order_favorite');
        $this->assertAction('delete');
        $this->assertResponseCode(403);

        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $this->assertEquals('false', $doc->getElementsByTagName("success")->item(0)->nodeValue);
        $this->assertEquals('no access', $doc->getElementsByTagName("message")->item(0)->nodeValue);
    }

}

?>