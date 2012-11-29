<?php

/**
 * @author MAtthias Laug <laug@lieferando.de>
 *
 * @runTestsInSeparateProcesses 
 */
class GetPartnerOrderTest extends Yourdelivery_Test {

    /**
     * test login with invalid parameters and expect a 406
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     */
    public function testStoreLocation() {
        $service = $this->getRandomService();
        $orderId = $this->placeOrder(array('service' => $service));
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::AFFIRMED,new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "testPartnerOrder"));

        $request = $this->getRequest();
        $request->setMethod('PUT');
        $request->setPost(array(
            'parameters' => sprintf('{"access":"%s","orders":[{"orderid":"%d","position":{"lat":"1","lng":"2"}}]}', $service->getSalt(), $orderId),
            'trigger' => 'position'
        ));

        $this->dispatch('/get_partner_order');
        $this->assertResponseCode(201);
    }
    

    /**
     * test change of state
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     */
    public function testSendState() {
        $service = $this->getRandomService();
        $orderId = $this->placeOrder(array('service' => $service));
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::AFFIRMED,new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "testPartnerOrder"));

        $request = $this->getRequest();
        $request->setMethod('PUT');
        $request->setPost(array(
            'parameters' => sprintf('{"access":"%s","orders":[{"orderid":"%d","driver":"samson", "state":"1"}]}', $service->getSalt(), $orderId),
            'trigger' => 'state'
       ));

        $this->dispatch('/get_partner_order');
        $this->assertResponseCode(201);
    }


    /**
     * test change of state without driver
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     */
    public function testSendSateWithoutDriver() {
        $service = $this->getRandomService();
        $orderId = $this->placeOrder(array('service' => $service));
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::AFFIRMED,new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "testPartnerOrder"));

        $request = $this->getRequest();
        $request->setMethod('PUT');
        $request->setPost(array(
            'parameters' => sprintf('{"access":"%s","orders":[{"orderid":"%d","state":"1"}]}', $service->getSalt(), $orderId),
            'trigger' => 'state'
        ));

        $this->dispatch('/get_partner_order');
        $this->assertResponseCode(406);
    }


    /**
     * test pick an order for a driver
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     */
    public function testPickOrderWithoutConflictAndConflictAfterwards() {
        $service = $this->getRandomService();
        $orderId = $this->placeOrder(array('service' => $service));
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::AFFIRMED,new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "testPartnerOrder"));

        $request = $this->getRequest();
        $request->setMethod('PUT');
        $request->setPost(array(
            'parameters' => sprintf('{"access":"%s","orders":[{"orderid":"%d","driver":"samson"}]}', $service->getSalt(), $orderId),
            'trigger' => 'pickorder'
        ));

        $this->dispatch('/get_partner_order');
        $this->assertResponseCode(200);
        
        $this->resetRequest();
        $this->resetResponse();
            
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $request->setPost(array(
            'parameters' => sprintf('{"access":"%s","orders":[{"orderid":"%d","driver":"tiffy"}]}', $service->getSalt(), $orderId),
            'trigger' => 'pickorder'
        ));
        
        $this->dispatch('/get_partner_order');
        $this->assertResponseCode(409);

    }

    /**
     * test pick an order without driver
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     */
    public function testPickOrderWithoutDriver() {
        $service = $this->getRandomService();
        $orderId = $this->placeOrder(array('service' => $service));
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::AFFIRMED,new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "testPartnerOrder"));

        $request = $this->getRequest();
        $request->setMethod('PUT');
        $request->setPost(array(
            'parameters' => sprintf('{"access":"%s","orders":[{"orderid":"%d","driver":""}]}', $service->getSalt(), $orderId),
            'trigger' => 'pickorder'
        ));

        $this->dispatch('/get_partner_order');
        $this->assertResponseCode(406);
    }

    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.08.2012 
     */
    public function testIndexWithoutService(){
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $request->setPost(array(
            'parameters' => sprintf('{"access":"%s","orders":[{"orderid":"%d","driver":""}]}', 'something-weird-access', rand(1,9)),
            'trigger' => 'pickorder'
        ));

        $this->dispatch('/get_partner_order');
        $this->assertResponseCode(404);
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.08.2012 
     */
    public function testGetFail() {
        $request = $this->getRequest();
        $this->dispatch('/get_partner_order/foo');
        $this->assertAction('get');
        $this->assertResponseCode(403);
    }
    
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.08.2012 
     */
    public function testDeleteFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setHeader("X-HTTP-Method-Override", "DELETE");
        $request->setPost(array(
            'foo' => 'bar'
        ));
        $this->dispatch('/get_partner_order');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }
    
    
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.08.2012 
     */
    public function testPostFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'foo' => 'bar'
        ));
        $this->dispatch('/get_partner_order');
        $this->assertAction('post');
        $this->assertResponseCode(403);
    }
}
