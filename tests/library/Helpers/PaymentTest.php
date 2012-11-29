<?php

/**
 * @author vpriem
 * @since 15.11.2010
 * @runTestsInSeparateProcesses
 */
class HelpersPaymentTest extends Yourdelivery_Test {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.11.2010
     */
    public function testOrderingPrivateSingleRestaurantAllowedPayments() {
        $customer = $this->getRandomCustomer(false);
        // create order (private rest registered not employee)
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('customer' => $customer, 'serviceBarPayment' => true, 'serviceOnlinePayment' => true, 'premium' => false)));
        $this->assertTrue($order instanceof Yourdelivery_Model_Order);
        $this->assertEquals($order->getMode(), 'rest');
        $this->assertEquals($order->getKind(), 'priv');

        // allowed
        $this->config->payment->bar->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowBar($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowBar($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->credit->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowCredit($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowCredit($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->paypal->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowPaypal($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowPaypal($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->ebanking->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowEbanking($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowEbanking($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));


        // bill is only allowed between 9:00 and 23:59
        if ($this->config->payment->bill->enabled == 1) {
            if ((time() < mktime(9, 00)) || (time() > mktime(23, 59))) {
                $this->assertFalse(Yourdelivery_Helpers_Payment::allowBill($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
            } else {
                $this->assertTrue(Yourdelivery_Helpers_Payment::allowBill($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
            }
        } else {
            $this->assertFalse(Yourdelivery_Helpers_Payment::allowBill($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
        }

        $this->config->payment->debit->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowDebit($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowDebit($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.11.2010
     */
    public function testOrderingPrivateSingleCateringAllowedPayments() {

        $customer = $this->getRandomCustomerCompany();

        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => "credit", 'customer' => $customer, 'serviceOnlinePayment' => true, 'mode' => 'cater')));

        // create order (private cater customer is company)

        $this->assertEquals($order->getMode(), 'cater');
        $this->assertEquals($order->getKind(), 'priv');

        // allowed
        $this->config->payment->credit->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowCredit($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowCredit($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->paypal->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowPaypal($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowPaypal($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->ebanking->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowEbanking($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowEbanking($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));


        // allways disallow bill for customer_company when cater / great
        $this->assertFalse(Yourdelivery_Helpers_Payment::allowBill($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));



        $this->config->payment->debit->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowDebit($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowDebit($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // not allowed
        $this->assertFalse(Yourdelivery_Helpers_Payment::allowBar($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // create order (private cater customer is NOT company)
        $customer = $this->getRandomCustomer(false, false);
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => "credit", 'customer' => $customer, 'serviceOnlinePayment' => true, 'mode' => 'cater')));

        $this->assertEquals($order->getMode(), 'cater', 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
        $this->assertEquals($order->getKind(), 'priv', 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // allowed
        $this->config->payment->credit->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowCredit($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowCredit($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->paypal->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowPaypal($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowPaypal($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->ebanking->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowEbanking($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowEbanking($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // bill is only allowed between 9:00 and 23:59
        if ($this->config->payment->bill->enabled == 1) {
            if ((time() < mktime(9, 00)) || (time() > mktime(23, 59))) {
                $this->assertFalse(Yourdelivery_Helpers_Payment::allowBill($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
            } else {
                $this->assertTrue(Yourdelivery_Helpers_Payment::allowBill($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
            }
        } else {
            $this->assertFalse(Yourdelivery_Helpers_Payment::allowBill($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
        }

        $this->config->payment->debit->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowDebit($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowDebit($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // not allowed
        $this->assertFalse(Yourdelivery_Helpers_Payment::allowBar($order), 'orderId: #' . $order->getId() . ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.11.2010
     */
    public function testOrderingPrivateSingleGreatAllowedPayments() {

        $customer = $this->getRandomCustomerCompany();
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => "credit", 'customer' => $customer, 'discount' => true, 'serviceOnlinePayment' => true, 'mode' => 'great')));

        // create order (private great customer is company)

        $this->assertEquals($order->getMode(), 'great');
        $this->assertEquals($order->getKind(), 'priv');

        // allowed
        $this->config->payment->credit->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowCredit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowCredit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->paypal->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowPaypal($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowPaypal($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->ebanking->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowEbanking($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowEbanking($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->debit->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowDebit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowDebit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // don't allow bill because customer is associated to company
        $this->assertFalse(Yourdelivery_Helpers_Payment::allowBill($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // not allowed
        $this->assertFalse(Yourdelivery_Helpers_Payment::allowBar($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $customer = $this->getRandomCustomer();
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => "credit", 'customer' => $customer, 'discount' => true, 'serviceOnlinePayment' => true, 'mode' => 'great')));

        // create order (private great customer is NOT company)

        $customer = $this->getRandomCustomer(false, false);
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => "credit", 'customer' => $customer, 'discount' => true, 'serviceOnlinePayment' => true, 'mode' => 'great')));

        $this->assertEquals($order->getMode(), 'great');
        $this->assertEquals($order->getKind(), 'priv');

        // allowed
        $this->config->payment->credit->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowCredit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowCredit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->paypal->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowPaypal($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowPaypal($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->ebanking->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowEbanking($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowEbanking($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // bill is only allowed between 9:00 and 23:59
        if ($this->config->payment->bill->enabled == 1) {
            if ((time() < mktime(9, 00)) || (time() > mktime(23, 59))) {
                $this->assertFalse(Yourdelivery_Helpers_Payment::allowBill($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
            } else {
                $this->assertTrue(Yourdelivery_Helpers_Payment::allowBill($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
            }
        } else {
            $this->assertFalse(Yourdelivery_Helpers_Payment::allowBill($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
        }

        $this->config->payment->debit->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowDebit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowDebit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // not allowed
        $this->assertFalse(Yourdelivery_Helpers_Payment::allowBar($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
    }

    /**
     * COMPANY
     */

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.11.2010
     */
    public function testOrderingCompanySingleRestaurantAllowedPayments() {

        /**
         * get order (company rest registered employee)
         * budget used, no open amount, no discount, rest = subway, no premium
         */
        $order = new Yourdelivery_Model_Order(41435);
        $this->assertEquals($order->getMode(), 'rest');
        $this->assertEquals($order->getKind(), 'comp');

        // allowed
        $this->config->payment->bar->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowBar($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowBar($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // company allways allows bill
        $this->assertTrue(Yourdelivery_Helpers_Payment::allowBill($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // don't allow any online payment, because customer is in company and uses budget
        $this->assertFalse(Yourdelivery_Helpers_Payment::allowDebit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
        $this->assertFalse(Yourdelivery_Helpers_Payment::allowCredit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
        $this->assertFalse(Yourdelivery_Helpers_Payment::allowPaypal($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
        $this->assertFalse(Yourdelivery_Helpers_Payment::allowEbanking($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // not show payment div
        $this->assertFalse(Yourdelivery_Helpers_Payment::showPayment($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.11.2010
     */
    public function testOrderingCompanySingleCateringAllowedPayments() {
        // create order (company cater )
        $order = new Yourdelivery_Model_Order(69071);
        $this->assertEquals($order->getMode(), 'cater');
        $this->assertEquals($order->getKind(), 'comp');

        // allowed
        $this->config->payment->credit->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowCredit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowCredit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->paypal->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowPaypal($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowPaypal($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->ebanking->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowEbanking($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowEbanking($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->debit->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowDebit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowDebit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // company allways allows bill
        $this->assertTrue(Yourdelivery_Helpers_Payment::allowBill($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // not allowed
        $this->assertFalse(Yourdelivery_Helpers_Payment::allowBar($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.11.2010
     */
    public function testOrderingCompanySingleGreatAllowedPayments() {

        // create order (company great)
        $order = new Yourdelivery_Model_Order(65305);
        $this->assertEquals($order->getMode(), 'great');
        $this->assertEquals($order->getKind(), 'comp');

        // allowed
        $this->config->payment->credit->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowCredit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowCredit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->paypal->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowPaypal($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowPaypal($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->ebanking->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowEbanking($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowEbanking($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        $this->config->payment->debit->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowDebit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowDebit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // company allways allow bill
        $this->assertTrue(Yourdelivery_Helpers_Payment::allowBill($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));

        // not allowed
        $this->assertFalse(Yourdelivery_Helpers_Payment::allowBar($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.01.2011
     */
    public function testServiceNotAllowBar() {
        // amran
        $service = new Yourdelivery_Model_Servicetype_Restaurant(11952);
        $service->setPaymentbar(0);
        $service->save();

        $this->assertTrue(!$service->isPaymentbar());

        // $order = new Yourdelivery_Model_Order_Private_Single_Restaurant();
        $order = new Yourdelivery_Model_Order_Private();
        $order->setService($service);

        $this->assertFalse(Yourdelivery_Helpers_Payment::allowBar($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.01.2011
     */
    public function testServiceAllowBarNoPremium() {
        // amran
        $service = new Yourdelivery_Model_Servicetype_Restaurant(11952);
        $service->setPaymentbar(1);
        $checkPremium = $service->getFranchiseTypeId();
        $service->setFranchiseTypeId(1);

        $this->assertTrue($service->isPaymentbar());
        $this->assertFalse($service->isPremium());

        //$order = new Yourdelivery_Model_Order_Private_Single_Restaurant();
        $order = new Yourdelivery_Model_Order_Private();
        $customer = $this->getRandomCustomer();

        $order->setup($customer);

        $order->setService($service);

        // allowed
        $this->config->payment->bar->enabled == 1 ?
                        $this->assertTrue(Yourdelivery_Helpers_Payment::allowBar($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment')) :
                        $this->assertFalse(Yourdelivery_Helpers_Payment::allowBar($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
        $service->setFranchiseTypeId($checkPremium);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.01.2011
     * @expectedException Yourdelivery_Exception
     */
    public function testServiceNotAllowBarPremium() {

        // YD-1516
        $this->markTestSkipped("");

        $order = new Yourdelivery_Model_Order($this->placeOrder(array('premium' => true, ' payment' => 'bar')));
        $this->assertTrue(is_null($order->getDiscount()));
        $this->assertTrue($order->getService()->isPremium());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.01.2011
     */
    public function testServiceAllowNoBarDiscount() {

        $rabattCode = $this->createDiscount(false, 1, 1200, true, false, false, false, false, null, null, true);
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('discount' => $rabattCode, 'serviceOnlinePayment' => true)));

        $this->assertTrue($order->getDiscount() instanceof Yourdelivery_Model_Rabatt_Code);
        $this->assertGreaterThan(0, $order->getAbsTotal());
        $this->assertFalse(Yourdelivery_Helpers_Payment::allowBar($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
    }

    /**
     * _test, if credit card is usable / allowed if open amount is less then 7,00 EUR
     * but total is at least 7,00 EUR
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.04.2012
     */
    public function testUpperLimitCreditPaymentWithDiscountPrivat() {
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('payment' => 'credit')));

        // create discount that amount is 60 cents below AbsTotal
        $rabattCode = $this->createDiscount(false, 1, ($order->getAbsTotal() - 60));

        $order->setData(array(
            'rabattCodeId' => $rabattCode->getId(),
            'discountAmount' => $rabattCode->getParent()->getRabatt()))->save();

        $order = new Yourdelivery_Model_Order($order->getId());

        $this->assertTrue(Yourdelivery_Helpers_Payment::allowCredit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
    }

    /**
     * check, that PaymentHelper doesn't throw error, when no location object is given in order
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.08.2011
     */
    public function testPaymentGivesNoErrorWhenNoLocationObjectIsGiven() {
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        /**
         * manipulate location (no location could be given, if customer comes via directLink / satellite)
         */
        $order->setLocation(null);

        if ($order->getService()->isOnlycash()) {
            $this->assertFalse(Yourdelivery_Helpers_Payment::allowCredit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
            $this->assertFalse(Yourdelivery_Helpers_Payment::allowPaypal($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
        } else {
            $this->assertTrue(Yourdelivery_Helpers_Payment::allowCredit($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
            $this->assertTrue(Yourdelivery_Helpers_Payment::allowPaypal($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
        }

        if ($order->getService()->isPaymentbar() && !$order->getService()->isPremium()) {
            $this->assertTrue(Yourdelivery_Helpers_Payment::allowBar($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
        } else {
            $this->assertFalse(Yourdelivery_Helpers_Payment::allowBar($order), ' Payment Log: ' . Default_Helpers_Log::getLastLog('payment'));
        }
    }

    private function getRandomOrder(array $fields, $withCompany = false) {
        $db = Zend_Registry::get('dbAdapter');

        $select = $db->select()
                ->from(array('o' => 'orders'))
                ->joinLeft(array('r' => 'restaurants'), "o.restaurantId = r.id", array('r.onlycash', 'r.franchiseTypeId'))
                ->joinleft(array('c' => 'city'), "r.cityId = c.id", array("restCityID" => 'c.id'))
                ->join(array('odb' => 'orders_bucket_meals'), 'odb.orderId = o.id', array('mealId' => 'odb.mealId', 'odb_sizeId' => 'odb.sizeId'))
                ->join(array('ms' => 'meal_sizes_nn'), 'odb.mealId = ms.mealId AND odb.sizeId = ms.sizeId', array('ms_sizeId' => 'ms.sizeId', 'ms_mealId' => 'ms.mealId'))
                ->having('ms_sizeId IS NOT NULL')
                ->having('ms_mealId IS NOT NULL')
                ->where('r.onlycash = 0')
                ->where('r.franchiseTypeId != 3')
                ->where('o.state != -5')
                ->where('o.rabattCodeId IS NULL')
                ->where('o.total > 700')
                ->having('restCityID NOT IN (' . implode(",", $aachenids) . ')');

        if ($fields['o.kind'] == 'comp' || $withCompany) {
            $select->join(array('cc' => 'customer_company'), "cc.customerId = o.customerId", array('ccid' => 'cc.customerId'));
        }

        if (!empty($fields)) {
            foreach ($fields as $key => $value) {
                $select->where($key . " =?", $value);
            }
        }

        $select->order(array('RAND()'));
        $select->limit(1);

        $stmt = $select->query();
        $result = $stmt->fetchAll();

        return $result[0];
    }

    /**
     * Test if New Customer Discount has been Used before with the same PayPal payerId
     * @author Vincent Priem <priem@lieferando.de>
     */
    public function testIsNewCustomerDiscountUsedPaypal() {

        $payerId = uniqid();

        $discount = $this->createDiscount();
        $discountParent = $discount->getParent();
        $discountParent->setType(1);
        $discountParent->save();
        $orderId = $this->placeOrder(array('payment' => 'paypal', 'discount' => $discount, 'finalize' => false));

        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(-5, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "testIsNewCustomerDiscountUsedPaypal"));
        $this->assertTrue($order->hasNewCustomerDiscount());
        $this->assertFalse(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));

        $dbTable = new Yourdelivery_Model_DbTable_Paypal_Transactions();
        $dbTable->createRow(array(
            'orderId' => $orderId,
            'payerId' => $payerId,
        ))->save();

        $this->assertFalse(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));

        $order->setState(1);
        $order->save();

        $this->assertTrue(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));

        $discount = $this->createDiscount();
        $discountParent = $discount->getParent();
        $discountParent->setType(Yourdelivery_Model_Rabatt::TYPE_VERIFCATION_ACTION);
        $discountParent->save();
        $orderId = $this->placeOrder(array('payment' => 'paypal', 'discount' => $discount));

        $order = new Yourdelivery_Model_Order($orderId);
        $order->setState(1);
        $order->save();

        $this->assertFalse(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));

        $dbTable = new Yourdelivery_Model_DbTable_Paypal_Transactions();
        $dbTable->createRow(array(
            'orderId' => $orderId,
            'payerId' => $payerId,
        ))->save();

        $this->assertTrue(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));
    }

    /**
     * Test if New Customer Discount Used function also works without a discount
     * to prevent IsNewCustomerDiscountUsed from crashing
     * 
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 10.05.2012
     */
    public function testIsNewCustomerDiscountUsedGeneral() {

        // Create a new order without discount
        $payerId = uniqid();
        $orderId = $this->placeOrder(array('payment' => 'paypal', 'finalize' => false));
        $order = new Yourdelivery_Model_Order($orderId);

        // assert, that order was created
        $this->assertTrue($order instanceof Yourdelivery_Model_Order);

        // assert, that order has no discount
        $this->assertTrue(!$order->getDiscount() instanceof Yourdelivery_Model_Rabatt_Code);

        // assert, that isNewCustomerDiscountUsed returns false and does not interrupt
        $this->assertFalse(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));

        // Create an order now with discount
        $discount = $this->createDiscount();
        $discountParent = $discount->getParent();
        $discountParent->setType(1);
        $discountParent->save();
        $orderId = $this->placeOrder(array('payment' => 'paypal', 'discount' => $discount, 'finalize' => false));
        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(-5, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "testIsNewCustomerDiscountUsedPaypal"));

        // assert, that order was created
        $this->assertTrue($order instanceof Yourdelivery_Model_Order);

        // assert, that order has a discount
        $this->assertTrue($order->getDiscount() instanceof Yourdelivery_Model_Rabatt_Code);

        // assert, that isNewCustomerDiscountUsed returns false and does not interrupt
        $this->assertFalse(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));

        // create a new paypal transaction
        $dbTable = new Yourdelivery_Model_DbTable_Paypal_Transactions();
        $dbTable->createRow(array(
            'orderId' => $orderId,
            'payerId' => $payerId,
        ))->save();

        $this->assertFalse(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));

        // set order state to 1
        $order->setState(1);
        $order->save();

        // now assert, that the customer discount is recognized as true
        $this->assertTrue(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));
    }

    /**
     * Test if New Customer Discount has been Used before with the same PayPal payerId
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function testIsNewCustomerDiscountUsedAfterNormalDiscount() {

        $payerId = uniqid();

        $discount = $this->createDiscount();
        $discountParent = $discount->getParent();
        $discountParent->setType(0);
        $discountParent->save();
        $orderId = $this->placeOrder(array('payment' => 'paypal', 'discount' => $discount, 'finalize' => false));

        $order = new Yourdelivery_Model_Order($orderId);

        $this->assertFalse($order->hasNewCustomerDiscount());
        $this->assertFalse(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));

        $dbTable = new Yourdelivery_Model_DbTable_Paypal_Transactions();
        $dbTable->createRow(array(
            'orderId' => $orderId,
            'payerId' => $payerId,
        ))->save();

        $this->assertFalse(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));

        $order->setState(1);
        $order->save();

        $this->assertFalse(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));

        $discount = $this->createDiscount();
        $discountParent = $discount->getParent();
        $discountParent->setType(Yourdelivery_Model_Rabatt::TYPE_VERIFCATION_ONCE_PER_THIS_TYPE);
        $discountParent->save();
        $orderId = $this->placeOrder(array('payment' => 'paypal', 'discount' => $discount));

        $order = new Yourdelivery_Model_Order($orderId);
        $order->setState(1);
        $order->save();

        $this->assertTrue(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));
    }

    /**
     * Test if New Customer Discount has been Used before with the same PayPal payerId
     * @author Vincent Priem <priem@lieferando.de>
     */
    public function testIsNewCustomerDiscountUsedEbanking() {

        $payerId = uniqid();

        $discount = $this->createDiscount();
        $discountParent = $discount->getParent();
        $discountParent->setType(1);
        $discountParent->save();
        $orderId = $this->placeOrder(array('payment' => 'ebanking', 'discount' => $discount, 'finalize' => false));

        $order = new Yourdelivery_Model_Order($orderId);
        $order->setStatus(-5, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, "testIsNewCustomerDiscountUsedPaypal"));
        $this->assertTrue($order->hasNewCustomerDiscount());
        $this->assertFalse(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));

        $dbTable = new Yourdelivery_Model_DbTable_Ebanking_Transactions();
        $dbTable->createRow(array(
            'orderId' => $orderId,
            'payerId' => $payerId,
        ))->save();

        if ($order->getStatus() < 0) {
            $this->assertFalse(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));
            $order->setState(1);
            $order->save();
        }

        $this->assertTrue(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));

        $discount = $this->createDiscount();
        $discountParent = $discount->getParent();
        $discountParent->setType(Yourdelivery_Model_Rabatt::TYPE_VERIFCATION_ACTION);
        $discountParent->save();
        $orderId = $this->placeOrder(array('payment' => 'ebanking', 'discount' => $discount));

        $order = new Yourdelivery_Model_Order($orderId);
        $order->setState(1);
        $order->save();

        $this->assertFalse(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));

        $dbTable = new Yourdelivery_Model_DbTable_Ebanking_Transactions();
        $dbTable->createRow(array(
            'orderId' => $orderId,
            'payerId' => $payerId,
        ))->save();

        $this->assertTrue(Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($payerId, $order));
    }
    
    public function createNewCustomerDiscount() {
        $ncdiscounts = Yourdelivery_Model_Rabatt::getNewCustomerDiscounts();

        if (is_array($ncdiscounts)) {

            try {
                $discount = new Yourdelivery_Model_Rabatt($ncdiscounts[array_rand($ncdiscounts, 1)]);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return false;
            }
            $this->assertTrue($discount instanceof Yourdelivery_Model_Rabatt);
            $this->assertTrue(is_numeric($discount->getId()));


            $codes = new Yourdelivery_Model_DbTable_RabattCodes();
            $code = $discount->getId() . Default_Helper::generateRandomString();
            $codes->insert(
                    array(
                        'used' => 0,
                        'code' => $code,
                        'rabattId' =>
                        $discount->getId()
                    )
            );
            return new Yourdelivery_Model_Rabatt_Code($code);
        }

        return false;
    }

}
