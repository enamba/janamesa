<?php

/*
 * @author Felix Haferkorn
 */
/**
 * @runTestsInSeparateProcesses 
 */
class DiscountControllerTest extends Yourdelivery_Test {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 02.02.2012
     */
    public function testDontAllowLoggedInUser() {
        $discount = $this->createNewCustomerDiscount();
        $this->bootstrap();

        $cust = $this->getRandomCustomer();
        $pass = time();
        $cust->setPassword(md5($pass));
        $cust->save();
        $this->login($cust->getEmail(), $pass);
        $this->assertTrue($cust->isLoggedIn());

        // try to enter new-customer-discount-registration
        $this->dispatch($discount->getReferer());
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/');
    }

    /**
     * Trying to hack the discoutn controller entering confirm url directly
     * @author Alex Vait <vait@lieferando.de>     
     * @since 01.02.2012
     */
    public function testConfirmWithoutCodeFail() {
        //avoid creating discounts with same referer and let the discount action be set before we test it
        sleep(1);

        // create new discount of type 2
        $discount = $this->createNewCustomerDiscount(array('type' => 2));

        $this->dispatch('/discount/confirm/referer/' . $referer);
        $this->assertController('discount');
        $this->assertAction('confirm');

        //test redirection, so no action shall be done on the discount page
        $this->assertResponseCode(302);
    }

    /**
     * Test valid discount
     * @author Alex Vait <vait@lieferando.de>     
     * @since 01.02.2012
     */
    public function testExistingDiscount() {

        // create new discount
        $discount = $this->createNewCustomerDiscount(array('type' => 1));
        $this->bootstrap();

        $request = $this->getRequest();
        $this->dispatch($discount->getReferer());
        $this->assertController('discount');
        $this->assertAction('index');

        $htmlBody = $this->getResponse()->getBody();

        //must be on the discount page and referer must be in the html code
        $this->assertNotEquals(strpos($htmlBody, 'name="referer" value="' . $discount->getReferer() . '"'), false);
    }

    /**
     * Test discount, that is no longer valid
     * @author Alex Vait <vait@lieferando.de>     
     * @since 01.02.2012
     */
    public function testInvalidDiscount() {

        // create new discount and set end time to now - 10 minutes, so it's not valid
        $discount = $this->createNewCustomerDiscount(array('type' => 1, 'end' => date('Y-m-d H:i:s', strtotime('-10 minutes'))));
        $this->bootstrap();

        $request = $this->getRequest();
        $this->dispatch($discount->getReferer());
        $this->assertController('discount');
        $this->assertAction('index');

        //test redirection, so no action shall be done on the discount page
        $this->assertResponseCode(302);

        $htmlBody = $this->getResponse()->getBody();

        //must not be on the discount page
        $this->assertEquals(strpos($htmlBody, 'name="referer" value="' . $discount->getReferer() . '"'), false);
    }

}

