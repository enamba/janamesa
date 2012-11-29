<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 26.01.2012
 */
class Yourdelivery_Payment_Ebanking_RefundTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.01.2012
     */
    public function testIsRefunded() {

        $orderId = $this->placeOrder(array('payment' => 'ebanking'));
        $order = new Yourdelivery_Model_Order($orderId);

        $this->assertFalse($order->isRefunded());
        
        $dbTable = new Yourdelivery_Model_DbTable_Ebanking_Refund_Transactions();
        $dbTable->createRow(array(
            'orderId' => $orderId,
            'request' => '<?xml version="1.0" encoding="UTF-8"?><refunds><refund><transaction>34820-96814-4F216E88-9954</transaction><amount>11.70</amount><comment></comment></refund></refunds>',
            'response' => '<?xml version="1.0" encoding="UTF-8" ?><errors><error><code>7000</code><message>Empty document. line: 1, char: 2</message></error></errors>',
        ))->save();
        
        $this->assertFalse($order->isRefunded());
        
        $dbTable->createRow(array(
            'orderId' => $orderId,
            'request' => '<?xml version="1.0" encoding="UTF-8"?><refunds><refund><transaction>34820-96814-4F216E88-9954</transaction><amount>11.70</amount></refund></refunds>',
            'response' => '<?xml version="1.0" encoding="UTF-8" ?><refunds><refund><transaction>34820-96814-4F216E88-9954</transaction><amount>11.70</amount><status>ok</status></refund></refunds>',
        ))->save();
        
        $this->assertTrue($order->isRefunded());
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 31.01.2012
     * @expectedException Yourdelivery_Payment_Ebanking_Exception
     */
    public function testFailToRefund() {
        
        $orderId = $this->placeOrder(array('payment' => 'ebanking'));
        $order = new Yourdelivery_Model_Order($orderId);
        
        $refund = new Yourdelivery_Payment_Ebanking_Refund();
        $refund->refund($order);
    }
}
