<?php
/**
 * Description of CallbackTest
 *
 * @author mlaug
 */
/**
 * @runTestsInSeparateProcesses 
 */
class OrderAcomCallbackTest extends AbstractOrderController {
    
    /**
     * @since 09.11.2011
     * @author mlaug 
     */
    private function dispatchCallback($status){
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);
        $service = $order->getService();
        $service->setNotify('acom');
        $service->save();
        $post = array(
            'status' => $status,
            'orderid' => $orderId,
            'text' => 'acom test'
        );
        $this->request->setMethod('POST');
        $this->request->setPost($post); 
        $this->dispatch('/order_acom_callback/status');
        return $orderId;
    }
    
    /**
     * @since 09.11.2011
     * @author mlaug 
     */
    public function testGetAcomOrders(){       
        $db = Zend_Registry::get('dbAdapter');
        $db->query('update restaurants set notify="fax" where notify="acom"');
        $orderId = $this->placeOrder();
        
        $orders = Yourdelivery_Api_Acom_Worker::getAcomOrders();
        $this->assertEquals(0,$orders->count());
        
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED,new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "testGetAcomOrders"));
        $service = $order->getService();
        $service->setNotify('acom');
        $service->save();
        
        $orders = Yourdelivery_Api_Acom_Worker::getAcomOrders($service->getId());
        $this->assertGreaterThan(0,$orders->count());
        
        $this->dispatch('/order_acom_callback/orders?service=' . $service->getId());
        $this->assertResponseCode(200);

        $data = $this->getResponse()->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $node = $doc->getElementsByTagName('order');
        $this->assertEquals(1, $node->item($node->length - 1)->getElementsByTagName('payment')->length, $data);
    }
    
    /**
     * @since 09.11.2011
     * @author mlaug 
     */
    public function testGetAcomOrdersWithDiscount(){       
        $db = Zend_Registry::get('dbAdapter');
        $db->query('update restaurants set notify="fax" where notify="acom"');
        $orderId = $this->placeOrder(array('discount' => true, 'payment' => 'paypal'));
        
        $orders = Yourdelivery_Api_Acom_Worker::getAcomOrders();
        $this->assertEquals(0,$orders->count());
        
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT,"testGetAcomOrdersWithDiscount"));
        $service = $order->getService();
        $service->setNotify('acom');
        $service->save();
        
        $orders = Yourdelivery_Api_Acom_Worker::getAcomOrders($service->getId());
        $this->assertGreaterThan(0,$orders->count());
        
        $this->dispatch('/order_acom_callback/orders?service=' . $service->getId());
        $this->assertResponseCode(200);

        $data = $this->getResponse()->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);
        
        $node = $doc->getElementsByTagName('order');
        $this->assertEquals(2, $node->item($node->length - 1)->getElementsByTagName('payment')->length, $data);
    }
    
    /**
     * @since 09.11.2011
     * @author mlaug 
     */
    public function testOrderWithoutService(){
        $this->dispatch('/order_acom_callback/orders');
        $this->assertResponseCode(404);
    }
    
    /**
     * @since 09.11.2011
     * @author mlaug 
     */
    public function testOrderWithService(){
        $service = $this->getRandomService();
        $this->dispatch('/order_acom_callback/orders?service=' . $service->getId());
        $this->assertResponseCode(200);
    }
    
    /**
     * @since 09.11.2011
     * @author mlaug 
     */
    public function testStatusNoPost(){
        $this->dispatch('/order_acom_callback/status');
        $this->assertResponseCode(400);
    }
    
    /**
     * @since 09.11.2011
     * @author mlaug 
     */
    public function testStatusNotFound(){
        $post = array(
            'status' => 1,
            'orderid' => 1423132312312312312312132,
            'text' => 'acom test'
        );
        $this->request->setMethod('POST');
        $this->request->setPost($post); 
        $this->dispatch('/order_acom_callback/status');
        $this->assertResponseCode(404);
    }
    
    /**
     * @since 09.11.2011
     * @author mlaug 
     */
    public function testStatusButNotSetToAcom(){     
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);
        $service = $order->getService();
        $service->setNotify('fax');
        $service->save();
        $post = array(
            'status' => 1,
            'orderid' => $orderId,
            'text' => 'acom test'
        );
        $this->request->setMethod('POST');
        $this->request->setPost($post); 
        $this->dispatch('/order_acom_callback/status');
        $this->assertResponseCode(500);
    }
    
    /**
     * @since 09.11.2011
     * @author mlaug 
     */
    public function testStatus1(){
        $orderId = $this->dispatchCallback(1);
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($order->getState(),1);
    }
    
    /**
     * @since 09.11.2011
     * @author mlaug 
     */
    public function testStatus2($status){
        $orderId = $this->dispatchCallback(2);
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($order->getState(),1);
    }
    
    /**
     * @since 09.11.2011
     * @author mlaug 
     */
    public function testStatus3($status){
        $orderId = $this->dispatchCallback(3);
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($order->getState(),-1);
    }
    
    /**
     * @since 09.11.2011
     * @author mlaug 
     */
    public function testStatus4($status){
        $orderId = $this->dispatchCallback(4);
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($order->getState(),1);
    }
    
    /**
     * @since 09.11.2011
     * @author mlaug 
     */
    public function testStatus5($status){
        $orderId = $this->dispatchCallback(5);
        $order = new Yourdelivery_Model_Order($orderId);
        $this->assertEquals($order->getState(),2);
    }
    
    /**
     * reset every acom service back to fax
     */
    public function tearDown() {
        $db = Zend_Registry::get('dbAdapter');
        $db->query('update restaurants set notify="fax" where notify="acom"');
        parent::tearDown();
    }
    
}

?>
