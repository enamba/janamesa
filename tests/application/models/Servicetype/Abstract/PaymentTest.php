<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 14.08.2012
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Servicetype_Abstract_PaymentTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.08.2012
     */
    public function testPayment() {
        
        $service = $this->getRandomService();
        
        $service->removePayments();
        $this->assertEquals(count($service->getPayments()), 0);
        
        $payment = new Yourdelivery_Model_Servicetype_Payment();
        $payment->setPayment("bar");
        $payment->setStatus(1);
        $service->addPayment($payment);
        $this->assertEquals(count($service->getPayments()), 1);
        
        $payment = new Yourdelivery_Model_Servicetype_Payment();
        $payment->setPayment("paypal");
        $payment->setStatus(1);
        $payment->setDefault(1);
        $service->addPayment($payment);
        $this->assertEquals(count($service->getPayments()), 2);
        
        $payment = new Yourdelivery_Model_Servicetype_Payment();
        $payment->setPayment("ebanking");
        $payment->setStatus(0);
        $service->addPayment($payment);
        $this->assertEquals(count($service->getPayments()), 3);
        
        $this->assertEquals($service->getDefaultPayment(), "paypal");
        
        $this->config->payment->bar->enabled = 1;
        $this->config->payment->paypal->enabled = 1;
        $this->config->payment->ebanking->enabled = 1;
        $this->assertTrue($service->isPaymentAllowed('bar'));
        $this->assertTrue($service->isPaymentAllowed('paypal'));
        $this->assertFalse($service->isPaymentAllowed('ebanking'));
        
        $this->config->payment->bar->enabled = 0;
        $this->config->payment->paypal->enabled = 0;
        $this->assertFalse($service->isPaymentAllowed('bar'));
        $this->assertFalse($service->isPaymentAllowed('paypal'));
        
        $this->config->payment->credit->enabled = 1;
        $this->config->payment->credit->allowed = 1;
        $this->assertTrue($service->isPaymentAllowed('credit'));
        $this->config->payment->credit->allowed = 0;
        $this->assertFalse($service->isPaymentAllowed('credit'));
    }
}
