<?php

/**
 * @author mlaug
 */
/**
 * @runTestsInSeparateProcesses 
 */
class ApiCallTest extends Yourdelivery_Test {

    /**
     * @author Matthias Laug
     * @since 2011 
     */
    public function testWooplaCallInvalidArguments() {

        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_call');
        
        $this->assertController('get_call');
        $this->assertAction('post');
        $this->assertResponseCode(406);
    }

    /**
     * @author Matthias Laug
     * @since 2011 
     */
    public function testWooplaCallUnkownOrder() {

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'id' => 1111111111111111,
            'answer' => 0
        ));
        $this->dispatch('/get_call');
        
        $this->assertController('get_call');
        $this->assertAction('post');
        $this->assertResponseCode(404);
    }

    /**
     * @author Matthias Laug
     * @since 2011 
     */
    public function testWooplaCallNegative() {


        $orderId = $this->placeOrder();
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'id' => $orderId,
            'answer' => 0
        ));

        $this->dispatch('/get_call');
        
        $this->assertController('get_call');
        $this->assertAction('post');
        $this->assertResponseCode(200);

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals(Yourdelivery_Model_Order::DELIVERERROR, $order->getState());
    }
    
    /**
     * @author Matthias Laug
     * @since 2011 
     */
    public function testWooplaCallRandomNumberButNotOne() {


        $orderId = $this->placeOrder();
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'id' => $orderId,
            'answer' => rand(2, 9)
        ));

        $this->dispatch('/get_call');
        
        $this->assertController('get_call');
        $this->assertAction('post');
        $this->assertResponseCode(200);

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals(Yourdelivery_Model_Order::DELIVERERROR, $order->getState());
    }
    
    /**
     * @author Matthias Laug
     * @since 2011 
     */
    public function testWooplaWithNoResponse() {


        $orderId = $this->placeOrder();
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'id' => $orderId,
            'answer' => -1
        ));

        $this->dispatch('/get_call');
        
        $this->assertController('get_call');
        $this->assertAction('post');
        $this->assertResponseCode(200);

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals(Yourdelivery_Model_Order::DELIVERERROR, $order->getState());
    }

    /**
     * @author Matthias Laug
     * @since 2011 
     */
    public function testWooplaCallPositive() {


        $orderId = $this->placeOrder();
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'id' => $orderId,
            'answer' => 1
        ));

        $this->dispatch('/get_call');
        $this->assertController('get_call');
        $this->assertAction('post');
        $this->assertResponseCode(200);

        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals(Yourdelivery_Model_Order::DELIVERED, $order->getState());
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testGetFail(){
        $request = $this->getRequest();      
        $request->setMethod('GET');
        $this->dispatch('/get_call/some-stuff');
        $this->assertController('get_call');
        $this->assertAction('get');
        $this->assertResponseCode(403);
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testIndexFail(){
        $request = $this->getRequest();       
        $request->setMethod('GET');
        $this->dispatch('/get_call?blub=bla');
        $this->assertController('get_call');
        $this->assertAction('index');
        $this->assertResponseCode(403);
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testPutFail(){
        $request = $this->getRequest();      
        $request->setMethod('PUT');
        $this->dispatch('/get_call');
        $this->assertController('get_call');
        $this->assertAction('put');
        $this->assertResponseCode(403);
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testDeleteFail(){
        $request = $this->getRequest();      
        $request->setMethod('DELETE');
        $this->dispatch('/get_call/some-stuff');
        $this->assertController('get_call');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }

}
