<?php

/**
 * Description of AdyenTest
 *
 * @author mlaug
 */
/**
 * @runTestsInSeparateProcesses 
 */
class AdyenTest extends Yourdelivery_Test {
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.08.2012
     */
    public function testNoInitAllowed(){       
        $session = new Zend_Session_Namespace('Default');       
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $session->currentOrderId = $order->getId();
        $order->setStatus(Yourdelivery_Model_Order_Abstract::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'adyen test confirm'));       
        $this->dispatch('/payment_adyen/initialize');
        $this->assertRedirectTo('/');
    }
    
}

?>
