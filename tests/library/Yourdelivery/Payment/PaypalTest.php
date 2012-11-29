<?php
/**
 * @author vpriem
 * @since 15.11.2010
 */
class YourdeliveryPaymentPaypalTest extends Yourdelivery_Test{

    /**
     * With one item
     * no deliver cost
     * no discount
     * @author mlaug
     * @since @since 15.11.2010
     */
    public function testSimpleOrder(){
        // create order
        $orderId = $this->placeOrder(array('payment' =>'paypal'));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->_initPayal($order);
    }

    /**
     * With multiple items
     * no deliver cost
     * no discount
     * @author vpriem
     * @since @since 15.11.2010
     */
    public function testComplexOrder(){
    }

    /**
     * With deliver cost
     * no discount
     * @author mlaug
     * @since @since 26.11.2010
     */
    public function testOrderWithDeliverCost(){
        // create order
        $orderId = $this->placeOrder(array('payment' =>'paypal'));
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setServiceDeliverCost(200); //overwrite deliver costs and check
        $this->_initPayal($order);
    }

    /**
     * With courier deliver cost
     * no discount
     * @author mlaug
     * @since @since 26.11.2010
     */
    public function testOrderWithCourierDeliverCost(){
        // create order
        $orderId = $this->placeOrder(array('payment' =>'paypal'));
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setCourierCost(200); //overwrite deliver costs and check
        $this->_initPayal($order);
    }

    /**
     * With deliver cost
     * and discount
     * @author vpriem
     * @since @since 26.11.2010
     */
    public function testOrderWithDeliverCostAndDiscount(){
        $orderId = $this->placeOrder(array('payment' =>'paypal', 'discount' => true));
        $order = new Yourdelivery_Model_Order($orderId);
        $this->_initPayal($order);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 23.04.2012 
     */
    public function testGetOrderIdFromToken() {
        
        $orderId = $this->placeOrder(array('payment' =>'paypal', 'discount' => true));
        $order = new Yourdelivery_Model_Order($orderId);
        
        $paypal = new Yourdelivery_Payment_Paypal();
        $resp = $this->_initPayal($order);
        
        $newOrderId = $paypal->getOrderIdFromToken($resp['TOKEN']);
        $this->assertEquals($orderId, $newOrderId);
        
        $paypal->setTokenInvalid($orderId);
        $newOrderId = $paypal->getOrderIdFromToken($resp['TOKEN']);
        $this->assertFalse($newOrderId);
    }
    
    /**
     * @author mlaug
     * @since 26.11.2010
     * @param Yourdelivery_Model_Order $order
     * @return array
     */
    private function _initPayal($order){
        
        // create paypal
        $paypal = new Yourdelivery_Payment_Paypal();
        
        // setExpressCheckout
        
        try{
            $resp = $paypal->setExpressCheckout($order, "http://return.php", "http://cancel.php", "http://giropay/return.php", "http://giropay/cancel.php");
        }catch(Yourdelivery_Payment_Paypal_Exception $e) {
             $this->markTestSkipped($e->getMessage());
        }
        $this->assertEquals($resp['ACK'], "Success");
        $this->assertArrayHasKey('TOKEN', $resp);
        return $resp;
    }
        
}
