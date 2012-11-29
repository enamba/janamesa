<?php

/**
 * Description of WiercikTest
 *
 * @author daniel
 * 
 * @runTestsInSeparateProcesses
 */
class YourdeliverySenderWiercikTest extends Yourdelivery_Test {

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 02.05.2012  
     */
    public function testGetPrinterXmlOrderContent() {

        $orderId = $this->placeOrder();

        $order = new Yourdelivery_Model_Order($orderId);

        $q = new Yourdelivery_Sender_Wiercik(new Yourdelivery_Model_Order($orderId), 1234);
        $xml = $q->getPrinterXmlOrderContent();

        $dom = new Zend_Dom_Query($xml);

        $return = $dom->queryXpath("/order");

        $this->assertEquals($return->count(), 1);
        $return = $dom->queryXpath("/order/orderId");
        $this->assertEquals($return->current()->nodeValue, $order->getNr());

        $return = $dom->queryXpath("/order/customer");

        $this->assertEquals($return->count(), 1);

        $return = $dom->queryXpath("/order/customer/name");
        $this->assertEquals($return->current()->nodeValue, $order->getCustomer()->getFullname());


        $return = $dom->queryXpath("//product/price");


        $card = $order->getCard();
        $count = 0;
        foreach ($card['bucket'] as $items) {
            foreach ($items as $item) {
                $this->assertEquals(sprintf('%.2f', $item['meal']->getCost() / 100), $return->current()->nodeValue);
                $return->next();
                $count++;
            }
        }

        $this->assertEquals($count++, $return->count());


        $payment = ($order->getPayment() == 'bar') ? 'cash' : 'epayment';

        $return = $dom->queryXpath("/order/orderInformation/paymentMethod");

        $this->assertEquals($payment, $return->current()->nodeValue);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 24.05.2012  
     */
    public function testGetPrinterXmlOrderContentWithStateChange() {

        $orderId = $this->placeOrder();

        $order = new Yourdelivery_Model_Order($orderId);

        sleep(5);
        $order->setStatus(Yourdelivery_Model_Order_Abstract::NOTAFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "test"));

        $q = new Yourdelivery_Sender_Wiercik(new Yourdelivery_Model_Order($orderId), 1234);
        $xml = $q->getPrinterXmlOrderContent();


        $dom = new Zend_Dom_Query($xml);

        $return = $dom->queryXpath("/order/orderInformation/orderTime");

        $timestampdiff = (date('O') / 100 * 3600);
        $this->assertEquals($order->getLastStateChange() + $timestampdiff, $return->current()->nodeValue);
        $this->assertNotEquals($order->getTime() + $timestampdiff, $return->current()->nodeValue);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.06.2012 
     */
    public function testGetPrinterXmlWithLargeDiscount() {
        $orderId = $this->placeOrder(array('payment' => 'paypal'));

        $order = new Yourdelivery_Model_Order($orderId);

        $amount = $order->getAbsTotal(false, false);

        $discount = $this->createDiscount(false, 1, $amount + 10);

        $order->addDiscount($discount);
        $order->getTable()->getCurrent()->payment = 'bar';
        $order->getTable()->getCurrent()->save();

        $q = new Yourdelivery_Sender_Wiercik(new Yourdelivery_Model_Order($orderId), 1234);
        $xml = $q->getPrinterXmlOrderContent();

        $dom = new Zend_Dom_Query($xml);

        $return = $dom->queryXpath("/order/orderInformation/paymentMethod");

        $this->assertEquals("epayment", $return->current()->nodeValue);
    }

}

