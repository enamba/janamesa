<?php

/**
 * @author mlaug
 */

/**
 * @runTestsInSeparateProcesses 
 */
class BillingCourierTest extends Yourdelivery_Test {

    /**
     * @author Jens Naie <naie@lieferando.de>
     * @since 08.05.2012
     */
    public function testCreateBill() {
        $start = strtotime('-2 month');
        $end = time() - 1;
        
        /**
         * @author Felix
         * @todo get random courier with confirmed orders in interval
         */
        $this->markTestSkipped('refactor it !');
        
        
        $courier = $this->getRandomCourier();
        $bill = new Yourdelivery_Model_Billing_Courier($courier, $start, $end, Yourdelivery_Model_Billing_Courier::BILL_PER_MONTH, 1);

        //add orders to bill, so that they are not regenerated
        $this->assertTrue($bill->create(true), sprintf('could not create bill for courier #%d', $courier->getId()));
    }

}
