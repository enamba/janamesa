<?php

/**
 * @author Matthias Laug <laug@lieferando.de>
 * @runTestsInSeparateProcesses
 */
class BillingRestaurantTest extends Yourdelivery_Test {

    /**
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $payment
     * @param integer $count
     * @return Yourdelivery_Model_Billing_Restaurant
     */
    private function getBill($payment = 'bar', $discount = false) {
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => $payment, 'discount' => $discount)));
        $bill = new Yourdelivery_Model_Billing_Restaurant($order->getService());

        //add orders to bill, so that they are not regenerated
        $orders = array();
        $orders[] = $order;
        $bill->orders = $orders;
        return $bill;
    }

    /**
     * test if the amount is calculated correctly
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.06.2011
     */
    public function testGetBruttoAmountOfOrder() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $bill = new Yourdelivery_Model_Billing_Restaurant($order->getService());

        $total = $order->getTotal();
        $deliver = $order->getServiceDeliverCost();
        $this->assertEquals($total + $deliver, $bill->getBruttoAmountOfOrder($order), $order->getId());

        $order->getService()->setBillDeliverCost(false);
        $this->assertEquals($total + $deliver, $bill->getBruttoAmountOfOrder($order, true), $order->getId());
    }

    /**
     * test if the amount is calculated correctly
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.06.2011
     */
    public function testGetBrutto() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $bill = new Yourdelivery_Model_Billing_Restaurant($order->getService());

        //add orders to bill, so that they are not regenerated
        $orders = array();
        $orders[] = $order;
        $bill->orders = $orders;

        $total = $order->getTotal();
        $deliver = $order->getServiceDeliverCost() * $order->getService()->getBillDeliverCost();

        if ($order->getService()->getBillingParentId() != null) {
            $this->assertEquals(0, $bill->getBrutto('bar'));
            $this->assertEquals(0, $bill->getBrutto('paypal'));
        } else {
            $this->assertEquals($total + $deliver, $bill->getBrutto('bar'));
            $this->assertEquals(0, $bill->getBrutto('paypal'));
        }
    }

    /**
     * test if the amount is calculated correctly
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.06.2011
     */
    public function testWithNegativeBalance() {
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => 'paypal')));
        $bill = new Yourdelivery_Model_Billing_Restaurant($order->getService());
        $bill->until = time() + 1000000;
        $bill->getService()->getBalance()->resetAmount();

        //add orders to bill, so that they are not regenerated
        $orders = array();
        $orders[] = $order;
        $bill->orders = $orders;

        $total = $order->getTotal();
        $deliver = $order->getServiceDeliverCost() * $order->getService()->getBillDeliverCost();

        if ($order->getService()->getBillingParentId() != null) {
            $this->assertEquals(0, $bill->getAlreadyPayed());
        } else {
            $this->assertEquals($total + $deliver, $bill->getAlreadyPayed());
            //add negative 1000 to balance
            $bill->getService()->getBalance()->addBalance(-1000);
            $this->assertEquals(-1000, $bill->getBalanceAmount());
            $this->assertEquals($total + $deliver - 1000, $bill->getAlreadyPayed(true));
            $this->assertEquals($total + $deliver, $bill->getAlreadyPayed(false));
        }
    }

    /**
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.09.2011
     */
    public function testWithNegativeBalanceWhichOvercoversVoucherAmount() {

        $this->markTestSkipped('needs to be refactored');


        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => 'paypal')));
        $bill = new Yourdelivery_Model_Billing_Restaurant($order->getService());
        $bill->until = time() + 1000000;
        $bill->getService()->getBalance()->resetAmount();

        //add orders to bill, so that they are not regenerated
        $orders = array();
        $orders[] = $order;
        $bill->orders = $orders;

        if ($order->getService()->getBillingParentId() == null) {
            $total = $order->getTotal();
            $deliver = $order->getServiceDeliverCost();
            $comm = $bill->getCommBruttoTotal('all');
            $transaction = $bill->calculateTransactionCostBrutto();

            $order->getService()->getBalance()->addBalance(($total + $deliver - $comm - $transaction) * (-1), 'added by billing system', false, null, $bill->until);

            //compare integer values, because of such errors: Failed asserting that <double:3338,3> matches expected <double:3338,3>
            $this->assertEquals(intval(($total + $deliver - $comm - $transaction)*10), intval(($bill->getVoucherAmount())*10), $order->getId());
            $this->assertEquals(0, floor($bill->getVoucherAmount(true)), $order->getId());

            $newBalance = $bill->getNewBalanceAmount();
            $this->assertEquals($newBalance, floor($total + $deliver - $comm - $transaction));
        }

    }

    /**
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.09.2011
     */
    public function testWithPositiveBalance() {
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => 'paypal')));
        $order->setStatus(Yourdelivery_Model_Order::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::NO_REASON, 'testcase'));
        $bill = new Yourdelivery_Model_Billing_Restaurant($order->getService());
        $bill->until = time() + 1000000;
        $bill->getService()->getBalance()->resetAmount();

        //add orders to bill, so that they are not regenerated
        $orders = array();
        $orders[] = $order;
        $bill->orders = $orders;

        //this service has a parent
        if ($order->getService()->getBillingParentId() != null) {
            $this->assertEquals(0, count($bill->getOrders()));
            $this->assertEquals(0, $bill->getBrutto());
        } else {
            $total = $order->getTotal();
            $deliver = $order->getServiceDeliverCost() * $order->getService()->getBillDeliverCost();

            $this->assertEquals($total + $deliver, $bill->getAlreadyPayed(), $bill->getService()->getId());

            //add negative 1000 to balance
            $bill->getService()->getBalance()->addBalance(1000);
            $this->assertEquals(1000, $bill->getBalanceAmount(), $bill->getService()->getId());
            $this->assertEquals($total + $deliver + 1000, $bill->getAlreadyPayed(true), $bill->getService()->getId());
            $this->assertEquals($total + $deliver, $bill->getAlreadyPayed(false), $bill->getService()->getId());
        }
    }

    /**
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.09.2011
     */
    public function testWithPositiveBalanceWhichOvercoversBillingAmount() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $bill = new Yourdelivery_Model_Billing_Restaurant($order->getService());
        $bill->until = time() + 1000000;
        $bill->getService()->getBalance()->resetAmount();

        //add orders to bill, so that they are not regenerated
        $orders = array();
        $orders[] = $order;
        $bill->orders = $orders;

        $order->getService()->getBalance()->addBalance($bill->getCommBruttoTotal('all') + 100, 'added by billing system', false, null, $bill->until);

        $this->assertEquals($bill->getCommBruttoTotal('all'), $bill->calculateBillingAmount(false));
        $this->assertEquals(0, $bill->calculateBillingAmount(true));
    }

    /**
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.09.2011
     */
    public function testPreflyCheck() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $bill = new Yourdelivery_Model_Billing_Restaurant($order->getService());

        //add orders to bill, so that they are not regenerated
        $orders = array();
        $orders[] = $order;
        $bill->orders = $orders;

        $this->assertTrue($bill->preflyCheck());
        $order->setTotal($order->getTotal() - 10);
        $this->assertFalse($bill->preflyCheck());
    }

    /**
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.09.2011
     */
    public function testGetPayedAmountTotal() {
        $firstOrder = new Yourdelivery_Model_Order($this->placeOrder());
        $bill = new Yourdelivery_Model_Billing_Restaurant($firstOrder->getService());

        //add orders to bill, so that they are not regenerated
        $orders = array();
        $orders[] = $firstOrder;
        $bill->orders = $orders;
        $this->assertEquals($bill->getPayedAmountTotal(), 0);

        $secondOrder = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => 'paypal')));
        $orders[] = $secondOrder;
        $bill->orders = $orders;
        $bill->ordersByPayment = array();

        $total = $secondOrder->getTotal();
        $deliver = $secondOrder->getServiceDeliverCost();

        if ($secondOrder->getService()->getBillingParentId() != null) {
            $this->markTestIncomplete('Refactor billing-testcases!');            
            $this->assertEquals($bill->getPayedAmountTotal(), 0, "failed for order " . $secondOrder->getId());
        } else {
            $this->assertEquals($bill->getPayedAmountTotal(), $total + $deliver, "failed for order " . $secondOrder->getId());
        }
    }

    /**
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.09.2011
     */
    public function testGetCashTotal() {

        $this->markTestSkipped('needs to be refactored');

        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => 'bar', 'discount' => false)));

        //add Discount after placeOrder to test if calculation is right
        $discount = $this->createDiscount();
        $order->addDiscount($discount);

        $bill = new Yourdelivery_Model_Billing_Restaurant($order->getService());

        //add orders to bill, so that they are not regenerated
        $orders = array();
        $orders[] = $order;
        $bill->orders = $orders;

        $total = $order->getTotal();
        $deliver = $order->getServiceDeliverCost() * $order->getService()->getBillDeliverCost();
        $discount = $order->getDiscountAmount();

        if ($order->getService()->getBillingParentId() != null) {

            $this->assertEquals($bill->getCashTotal(), 0);
        } else {
            $this->assertEquals($bill->getCashTotal(), $total + $deliver - $discount);
        }

        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => 'paypal')));
        $orders[] = $order;
        $bill->orders = $orders;

        $this->assertEquals($bill->getCashTotal(), $total + $deliver - $discount);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 04.10.2011
     */
    public function testExludeOrders() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        $order->setDomain('samson.tiffy.de');
        $bill = new Yourdelivery_Model_Billing_Restaurant($order->getService());

        $orders = array();
        $orders[] = $order;
        $bill->orders = $orders;

        if ($order->getService()->getBillingParentId() != null) {

            $this->assertEquals(0, count($bill->getOrders()));
            $this->assertEquals(0, count($bill->getOrders(true, false)));
            $this->assertEquals(0, count($bill->getOrders(false, true)));
            $this->assertEquals(0, count($bill->getOrders(true, true)));
        } else {

            $this->assertEquals(1, count($bill->getOrders()));
            $this->assertEquals(0, count($bill->getOrders(true, false)));
            $this->assertEquals(1, count($bill->getOrders(false, true)));
            $this->assertEquals(1, count($bill->getOrders(true, true)));
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.10.2011
     */
    public function testHasChildren() {
        $bill = $this->getBill();
        $service = $this->getRandomService();
        $this->assertNotEquals($service->getId(), $bill->getService()->getId());
        $this->assertTrue($bill->getService()->addBillingChild($service));
        $this->assertGreaterThan(0, $bill->getService()->getBillingChildren()->count());
        $this->assertTrue($bill->hasChildren());
        return array($bill, $service);
    }

    /**
     * if we append a parent to the current billed service, the bill
     * should not be created
     * @author Matthias Laug <laug@lieferando.de>
     * @since 20.10.2011
     */
    public function testParentChildBills() {

        $this->markTestSkipped('needs to be refactored');

        $bill = $this->getBill();
        $service = $this->getRandomService();

        //remove assoc if any
        $db = Zend_Registry::get('dbAdapter');
        $db->query('truncate billing_merge');

        $this->assertEquals(1, count($bill->getOrders()), $bill->getService()->getId());
        $this->assertNotEquals($service->getId(), $bill->getService()->getId());
        $this->assertTrue($service->addBillingChild($bill->getService()));
        $this->assertGreaterThan(0, $service->getBillingChildren()->count());
        $this->assertFalse($bill->hasChildren());
        $this->assertEquals(0, count($bill->getOrders()), $bill->getService()->getId());
        $order = $bill->orders[0];
        $this->assertFalse($bill->create(), sprintf('service: %s order: %s', $service->getId(), $order->getId()));
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.10.2011
     */
    public function testCalculateItem() {
        $this->markTestSkipped('need to be refactored');

        $bill = $this->getBill('bar');
        $order = $bill->orders[0];
        $config = Zend_Registry::get('configuration');
        $taxes = array_merge(array(ALL_TAX), $config->tax->types->toArray());
        foreach ($taxes as $tax) {

            if ($order->getService()->getBillingParentId() != null) {
                $this->assertEquals(0, $bill->calculateItem('all', $tax), $order->getId());
                $this->assertEquals(0, $bill->calculateItem('bar', $tax), $order->getId());
                $this->assertEquals(0, $bill->calculateItem('paypal', $tax), $order->getId());
                $this->assertEquals(0, $bill->calculateItem('credit', $tax), $order->getId());
                $this->assertEquals(0, $bill->calculateItem('ebanking', $tax), $order->getId());
            } else {
                if ($tax == ALL_TAX) {
                    //round on addition
                    $diff = abs(round($order->getItem($tax)) - $bill->calculateItem('all', $tax));
                    $diff2 = abs(round($order->getItem($tax)) - $bill->calculateItem('bar', $tax));
                    $this->assertTrue($diff <= 1, $order->getId());
                    $this->assertTrue($diff2 <= 1, $order->getId());
                } else {
                    $this->assertEquals($order->getItem($tax), $bill->calculateItem('all', $tax), $order->getId());
                    $this->assertEquals($order->getItem($tax), $bill->calculateItem('bar', $tax), $order->getId());
                }
                $this->assertEquals(0, $bill->calculateItem('paypal', $tax), $order->getId());
                $this->assertEquals(0, $bill->calculateItem('credit', $tax), $order->getId());
                $this->assertEquals(0, $bill->calculateItem('ebanking', $tax), $order->getId());
            }
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.10.2011
     */
    public function testCalculateTax() {
        $this->markTestSkipped('need to be refactored');
        
        $bill = $this->getBill('bar');
        $order = $bill->orders[0];
        $config = Zend_Registry::get('configuration');
        $taxes = array_merge(array(ALL_TAX), $config->tax->types->toArray());
        foreach ($taxes as $tax) {
            if ($order->getService()->getBillingParentId() != null) {
                $this->assertEquals(0, $bill->calculateTax('all', $tax), $bill->getService()->getId());
                $this->assertEquals(0, $bill->calculateTax('bar', $tax), $bill->getService()->getId());
                $this->assertEquals(0, $bill->calculateTax('paypal', $tax));
                $this->assertEquals(0, $bill->calculateTax('credit', $tax));
                $this->assertEquals(0, $bill->calculateTax('ebanking', $tax));
                $this->assertEquals(0, $bill->calculateTax('bill', $tax));
            } else {
                if ($tax == ALL_TAX) {
                    //round on addition
                    $diff = abs(round($order->getTax($tax, true, false, false)) - $bill->calculateTax('all', $tax));
                    $diff2 = abs(round($order->getTax($tax, true, false, false)) - $bill->calculateTax('bar', $tax));
                    $this->assertTrue($diff <= 1, $order->getId());
                    $this->assertTrue($diff2 <= 1, $order->getId());
                    $this->assertTrue(abs(round($order->getTax($tax, true, false, false)) - $bill->calculateTax('bar', $tax)) <= 1, $bill->getService()->getId());
                } else {
                    $this->assertEquals($order->getTax($tax, true, false, false), $bill->calculateTax('all', $tax), $bill->getService()->getId());
                    $this->assertEquals($order->getTax($tax, true, false, false), $bill->calculateTax('bar', $tax), $bill->getService()->getId());
                }
                $this->assertEquals(0, $bill->calculateTax('paypal', $tax));
                $this->assertEquals(0, $bill->calculateTax('credit', $tax));
                $this->assertEquals(0, $bill->calculateTax('ebanking', $tax));
                $this->assertEquals(0, $bill->calculateTax('bill', $tax));
            }
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.10.2011
     */
    public function testGetOrderByPayment() {

        $this->markTestSkipped('needs to be refactored');

        $bill = $this->getBill('bar');
        $order = $bill->orders[0];
        if ($order->getService()->getBillingParentId() != null) {
            $this->assertEquals(0, count($bill->getOrdersByPayment('all')));
            $this->assertEquals(0, count($bill->getOrdersByPayment('bar')));
        } else {
            $this->assertEquals(1, count($bill->getOrdersByPayment('all')));
            $this->assertEquals(1, count($bill->getOrdersByPayment('bar')));
        }
        $this->assertEquals(0, count($bill->getOrdersByPayment('paypal')));
        $this->assertEquals(0, count($bill->getOrdersByPayment('credit')));
        $this->assertEquals(0, count($bill->getOrdersByPayment('ebanking')));
        $this->assertEquals(0, count($bill->getOrdersByPayment('bill')));
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.10.2011
     */
    public function testGetDiscountTotal() {
        $bill = $this->getBill('credit', true);
        $order = $bill->orders[0];
        $o = new Yourdelivery_Model_Order($order->getId());
        if ($order->getService()->getBillingParentId() != null) {
            $this->assertEquals(0, $bill->getDiscountTotal('all'));
            $this->assertEquals(0, $bill->getDiscountTotal('bar'));
        } else {
            $msg = sprintf('ID(%s|%s) Total:%s Discount:%s(%s)',$order->getId(), $o->getId(), $o->getTotal(), $o->getDiscountAmount(), $o->getRabattCodeId());
            $this->assertEquals($order->getDiscountAmount(false), $bill->getDiscountTotal('all'), $msg);
            $this->assertEquals($order->getDiscountAmount(false), $bill->getDiscountTotal('credit'), $msg);
        }
        $this->assertEquals(0, $bill->getDiscountTotal('bar'));
        $this->assertEquals(0, $bill->getDiscountTotal('paypal'));
        $this->assertEquals(0, $bill->getDiscountTotal('ebanking'));
        $this->assertEquals(0, $bill->getDiscountTotal('bill'));
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.03.2012
     */
    public function testTransactionCost() {
        $this->markTestSkipped('need to be refactored');

        $bill = $this->getBill('paypal', true);
        $order = $bill->orders[0];

        //alter order with transaction cost
        $row = $order->getRow();
        $row->charge = 45;
        $row->save();

        if ($order->getService()->getBillingParentId() == null) {
            $this->assertEquals(0, $bill->calculateTransactionCost(''), $order->getId());
            $this->assertEquals(45, $bill->calculateTransactionCost('paypal'), $order->getId());
            $this->assertEquals(0, $bill->calculateTransactionCost('bar'), $order->getId());
            $this->assertEquals(45, $bill->calculateTransactionCost('paypal', true, false), $order->getId());
            $this->assertEquals(0, $bill->calculateTransactionCost('paypal', false, true), $order->getId());
        } else {
            $this->assertEquals(0, $bill->calculateTransactionCost(), $order->getId());
        }
    }

    /**
     * @author Jens Naie <naie@lieferando.de>
     * @since 08.05.2012
     */
    public function testCreateBill() {
        $this->markTestSkipped('need to be refactored');
        
        $start = strtotime(date('Y-m-01'));
        $end = time()-1;
        $service = $this->getRandomService(array('hasOrdersThisMonth'=>true));
        $bill = new Yourdelivery_Model_Billing_Restaurant($service, $start, $end, Yourdelivery_Model_Billing_Restaurant::BILL_PER_MONTH, 1);
        $msg = sprintf("Online Payment Service ID: %s", $service->getId());
        $this->assertTrue($bill->create(true), $msg);
        $this->markTestIncomplete('Nicht wirklich testbar, da LATEX-PDF Generierung mit zufälligem Dateinamen');
    }
}
