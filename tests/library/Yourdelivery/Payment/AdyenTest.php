<?php

/**
 * Description of AdyenTest
 *
 * @author Matthias Laug <laug@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses
 */
class YourdeliveryPaymentAdyenTest extends Yourdelivery_Test {

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.03.2012 
     */
    public function testInitPayment() {
        
        $this->markTestIncomplete('this testcase needs to be refactored !!!');
        
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => 'credit')));

        //dispatch init payment page
        $session = new Zend_Session_Namespace('Default');
        $session->currentOrderId = $order->getId();
        $this->dispatch('/payment_adyen/initialize');

        $response = $this->getResponse()->getBody();
        
        //get timecode to calculate identical signatures
        $match = array();
        preg_match('/merchantReference(.*)value="(.*)-(.*)"/', $response, $match);       
        $adyen = new Yourdelivery_Payment_Adyen();
        $data = $adyen->initPayment($order, $match[3]);
        $this->assertGreaterThan(0, count($data));
        
        //get caluclated signature
        $match = array();
        preg_match('/merchantSig(.*)value="(.*)"/', $response, $match);
        $this->assertEquals(3, count($match), $order->getId());
        $signature = $match[2];
        $this->assertEquals($data['merchantSignature'], $signature, $order->getId());    
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.03.2012 
     */
    public function testIsRefunded(){
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => 'credit')));
        $this->assertFalse($order->isRefunded(), $order->getId());
               
        $table = new Yourdelivery_Model_DbTable_Adyen_Transactions();
        $row = $table->createRow(array(
            'orderId' => $order->getId(),
            'transactionId' => time(),
            'reference' => time(),
            'redirect' => date(DATETIME_DB),
            'valid' => 1
        ));
        $row->save();
        $this->assertFalse($order->isRefunded(), $order->getId());
        $row->refunded = 1;
        $row->save();
        $this->assertTrue($order->isRefunded(), $order->getId());
    }
    
}

?>
