<?php

/**
 * Description of PayPalTest
 *
 * @author daniel
 */

/**
 * @runTestsInSeparateProcesses 
 */
class PaypalTest extends Yourdelivery_Test {

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 23.12.2011
     */
    public function testNewCustomerDiscountForbidden() {
        $discount = $this->_getDiscount(2);

        //if it falis here, then there are unusable Discounts in the New Customers Method
        $this->assertTrue($discount->isUsable());

        if ($discount) {

            $db = Zend_Registry::get('dbAdapter');
            $select = $db->select()->from('paypal_transactions', array('payerId'))->join('orders', 'orders.id=paypal_transactions.orderId')->where('orders.state >0');
            $row = $db->fetchRow($select);

            list($resp, $order, $payerId) = $this->_placeOrder($discount, $row['payerId']);
            $this->_insertPayerId($order->getId(), $payerId, $resp['TOKEN']);

            $session = new Zend_Session_Namespace('Default');
            $session->currentOrderId = $order->getId();

            $this->dispatch("/payment_paypal/finish?token=" . $resp['TOKEN'] . "&PayerID=" . $payerId);

            $this->assertEquals($session->newCustomerDiscountError, 1);
            $this->assertRedirectTo("/order_basis/payment", "");
            $newOrder = new Yourdelivery_Model_Order($order->getId());
            $this->assertNull($newOrder->getDiscount());
        } else {
            $this->markTestIncomplete(
                    'No New Customer Discounts in  this Domain :('
            );
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 23.12.2011
     */
    public function testUsedDiscountAgainWithPayPal() {
        $discount = $this->_getDiscount(4);

        //if it falis here, then there are unusable Discounts in the New Customers Method
        $this->assertTrue($discount->isUsable());

        if ($discount) {

            $session = new Zend_Session_Namespace('Default');

            //first order will go through
            list($resp, $order, $payerId) = $this->_placeOrder($discount);
            $session->currentOrderId = $order->getId();
            $this->dispatch("/payment_paypal/finish?token=" . $resp['TOKEN'] . "&PayerID=" . $payerId);
            $this->assertNull($session->newCustomerDiscountError);
            $this->assertRedirectTo("/order_private/success");
            $newOrder = new Yourdelivery_Model_Order($order->getId());
            $this->assertNotNull($newOrder->getDiscount());
            $newOrder->getDiscount()->setCodeUnused($newOrder); //reactivate to use again for next order
            $newOrder->setStatus(Yourdelivery_Model_Order_Abstract::DELIVERED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'testing')); //make sure right status, even thought came into fake
            //the second order will not go through with the same discount (reactivated, just need to be from the same action)
            list($resp, $order) = $this->_placeOrder($discount, $payerId);
            $session->currentOrderId = $order->getId();
            $this->dispatch("/payment_paypal/finish?token=" . $resp['TOKEN'] . "&PayerID=" . $payerId);
            $this->assertEquals($session->newCustomerDiscountError, 1);
            $this->assertRedirectTo("/order_basis/payment");
            $newOrder = new Yourdelivery_Model_Order($order->getId());
            $this->assertNull($newOrder->getDiscount());
            $newOrder->setStatus(Yourdelivery_Model_Order_Abstract::DELIVERED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'testing')); //make sure right status
            //a thrid with a new customer discount of type 4 must go through again
            $session->newCustomerDiscountError = null; //reset session
            $discount = $this->_getDiscount(4);
            list($resp, $order, $payerId) = $this->_placeOrder($discount, $payerId);
            $session->currentOrderId = $order->getId();
            $this->dispatch("/payment_paypal/finish?token=" . $resp['TOKEN'] . "&PayerID=" . $payerId);
            $this->assertNull($session->newCustomerDiscountError); //should not have been changed back to 1
            $this->assertRedirectTo("/order_private/success");
            $newOrder = new Yourdelivery_Model_Order($order->getId());
            $this->assertNotNull($newOrder->getDiscount());
        } else {
            $this->markTestIncomplete(
                    'No New Customer Discounts in  this Domain :('
            );
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $type
     * @return \Yourdelivery_Model_Rabatt_Code @
     */
    private function _getDiscount($type) {
        $rabatt = $this->createNewCustomerDiscount(array('type' => $type));

        $this->assertTrue($rabatt instanceof Yourdelivery_Model_Rabatt);
        $this->assertTrue(is_numeric($rabatt->getId()));
        $this->assertTrue($rabatt->isNewCustomerDiscount());



        $codes = new Yourdelivery_Model_DbTable_RabattCodes();
        $code = $rabatt->getId() . Default_Helper::generateRandomString();
        $codes->insert(
                array(
                    'used' => 0,
                    'code' => $code,
                    'rabattId' => $rabatt->getId()
                )
        );

        $discount = new Yourdelivery_Model_Rabatt_Code($code);
        return $discount;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     */
    private function _placeOrder($discount, $payerId = null) {

        if ($payerId === null) {
            $payerId = strtoupper(Default_Helper::generateRandomString(13));
        }

        $orderId = $this->placeOrder(array('payment' => 'paypal', 'checkForFraud' => false));

        $order = new Yourdelivery_Model_Order($orderId);
        $order->addDiscount($discount);
        $this->assertTrue($order->getDiscount() instanceof Yourdelivery_Model_Rabatt_Code);
        $this->assertEquals($order->getDiscount()->getId(), $discount->getId());

        $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "yd-testing: testNewCustomerDiscountForbidden"));

        $resp = $this->_initPayal($order);
        return array($resp, $order, $payerId);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $payerId 
     */
    private function _insertPayerId($orderId, $payerId, $token) {
        //check if PayerId already used
        $paypal = new Yourdelivery_Model_DbTable_Paypal_Transactions();
        $paypal->findByPayerId($payerId);
        $paypal->insert(array(
            'orderId' => $orderId,
            'params' => "yd-testing: testNewCustomerDiscountForbidden",
            'response' => "",
            'payerId' => $payerId,
            'token' => $token
        ));
    }

    /**
     * @author mlaug
     * @since 26.11.2010
     * @param Yourdelivery_Model_Order $order 
     */
    private function _initPayal($order) {
        // create paypal
        $paypal = new Yourdelivery_Payment_Paypal();
        // setExpressCheckout
        $resp = $paypal->setExpressCheckout($order, "http://return.php", "http://cancel.php", "http://giropay/return.php", "http://giropay/cancel.php");
        $this->assertEquals($resp['ACK'], "Success", sprintf('orderId#%s',$order->getId()));
        $this->assertArrayHasKey('TOKEN', $resp);

        return $resp;
    }

}
