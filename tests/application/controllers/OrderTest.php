<?php

/**
 * @author mlaug
 */
/**
 * @runTestsInSeparateProcesses 
 */
class OrderControllerTest extends Yourdelivery_Test {

    protected static $uri;

    public function testPrivateStart() {
        //if no customer is logged in, we redirect to frontpage
        $this->dispatch('/order_basis/start');
        $this->assertRedirect('/');

        $this->resetRequest();
        $this->resetResponse();

        //login customer
        $customer = $this->getRandomCustomer();
        $this->assertTrue($customer->isLoggedIn());
        $session = new Zend_Session_Namespace('Default');
        $session->customerId = $customer->getId();
        $this->dispatch('/order_basis/start');
        $this->assertNotRedirect();

        $ydState = Yourdelivery_Cookie::factory('yd-state');
        $this->assertEquals($ydState->get('kind'), 'priv');
    }

    public function testServiceRest() {
        $cityId = $this->getRandomCityId();
        $city = new Yourdelivery_Model_City($cityId);
        self::$uri = "/" . $city->getRestUrl();
        $_SERVER['REQUEST_URI'] = self::$uri;
        parent::setUp();
        $request = $this->getRequest();
        $request->setMethod('GET');

        $this->dispatch(self::$uri);
        $this->assertEquals($this->getResponse()->getHttpResponseCode(), "200");
    }

    public function testMenu() {
        $service = $this->getRandomService();
        self::$uri = "/" . $service->getRestUrl();
        $_SERVER['REQUEST_URI'] = self::$uri;
        parent::setUp();
        $request = $this->getRequest();
        $request->setMethod('GET');

        $this->dispatch(self::$uri);
        $this->assertEquals($this->getResponse()->getHttpResponseCode(), "200");
    }

    public function testFinishPrivate() {
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    public function testFinishCompany() {
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

}
