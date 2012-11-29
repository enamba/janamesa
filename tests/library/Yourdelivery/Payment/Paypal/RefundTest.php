<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 26.01.2012
 */
class Yourdelivery_Payment_Paypal_RefundTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.01.2012
     */
    public function testIsRefunded() {

        $orderId = $this->placeOrder(array('payment' => 'paypal'));
        $order = new Yourdelivery_Model_Order($orderId);

        $this->assertFalse($order->isRefunded());
        
        $dbTable = new Yourdelivery_Model_DbTable_Paypal_Transactions();
        $dbTable->createRow(array(
            'orderId' => $orderId,
            'params' => 'a:4:{s:6:"METHOD";s:17:"RefundTransaction";s:13:"TRANSACTIONID";s:17:"1M6691000B330662V";s:10:"REFUNDTYPE";s:4:"Full";s:12:"CURRENCYCODE";s:3:"EUR";}',
            'response' => 'a:10:{s:19:"REFUNDTRANSACTIONID";s:17:"70J26206LG680545K";s:12:"FEEREFUNDAMT";s:4:"0.43";s:14:"GROSSREFUNDAMT";s:4:"4.08";s:12:"NETREFUNDAMT";s:4:"3.65";s:12:"CURRENCYCODE";s:3:"EUR";s:9:"TIMESTAMP";s:20:"2011-01-23T14:28:46Z";s:13:"CORRELATIONID";s:13:"74a6967dab281";s:3:"ACK";s:7:"Fuckess";s:7:"VERSION";s:4:"65.0";s:5:"BUILD";s:7:"1646991";}',
        ))->save();
        
        $this->assertFalse($order->isRefunded());
        
        $dbTable->createRow(array(
            'orderId' => $orderId,
            'params' => 'a:4:{s:6:"METHOD";s:17:"RefundTransaction";s:13:"TRANSACTIONID";s:17:"1M6691000B330662V";s:10:"REFUNDTYPE";s:4:"Full";s:12:"CURRENCYCODE";s:3:"EUR";}',
            'response' => 'a:10:{s:19:"REFUNDTRANSACTIONID";s:17:"70J26206LG680545K";s:12:"FEEREFUNDAMT";s:4:"0.43";s:14:"GROSSREFUNDAMT";s:4:"4.08";s:12:"NETREFUNDAMT";s:4:"3.65";s:12:"CURRENCYCODE";s:3:"EUR";s:9:"TIMESTAMP";s:20:"2011-01-23T14:28:46Z";s:13:"CORRELATIONID";s:13:"74a6967dab281";s:3:"ACK";s:7:"Success";s:7:"VERSION";s:4:"65.0";s:5:"BUILD";s:7:"1646991";}',
        ))->save();
        
        $this->assertTrue($order->isRefunded());
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 31.01.2012
     * @expectedException Yourdelivery_Payment_Paypal_Exception
     */
    public function testFailToRefund() {
        
        $orderId = $this->placeOrder(array('payment' => 'paypal'));
        $order = new Yourdelivery_Model_Order($orderId);
        
        $paypal = new Yourdelivery_Payment_Paypal();
        $paypal->refundTransaction($order);
    }
}
