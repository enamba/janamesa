<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class AdministrationTest extends Yourdelivery_Test {

    public function setUp() {
        parent::setUp();
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        $this->getRequest()->setHeader('Authorization', 'Basic '.  base64_encode('gf:thisishell'));
    }

    /**
     * @mpantar
     * @since 13.3.2011
     */
    public function testLoginWithAdminRights() {


        $admin = $this->createRandomAdministrationUser();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'user' => $admin->getEmail(),
            'pass' => 'admin'
        ));
        $this->dispatch('/administration/login');

        $this->assertRedirectTo('/administration/dashboard');
    }

    /**
     * @author mpantar
     * @since 13.3.2011
     */
    public function testLoginWithSupportRights() {


        $admin = $this->createRandomAdministrationUser();
        $admin->setGroupId('7');
        $admin->save();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'user' => $admin->getEmail(),
            'pass' => 'admin'
        ));
        $this->dispatch('/administration/login');

        $this->assertRedirectTo('/administration/dashboard');

    }

    /**
     * @author mpantar
     * @since 13.3.2011
     */
    public function testLogout() {


        $admin = $this->createRandomAdministrationUser();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'user' => $admin->getEmail(),
            'pass' => 'admin'
        ));
        $this->dispatch('/administration/login');
        $this->assertRedirectTo('/administration/dashboard');

        $this->dispatch('/administration/logout');
        $this->assertRedirectTo('/');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 10.06.2011
     */
    public function testServiceLogin() {
        $admin = $this->createRandomAdministrationUser();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'user' => $admin->getEmail(),
            'pass' => 'admin'
        ));
        $this->dispatch('/administration/login');
        $this->assertRedirectTo('/administration/dashboard');

        $restaurantSession = new Zend_Session_Namespace('Restaurant');
        $service = $this->getRandomService();
        $this->dispatch('/administration/servicelogin/id/'.$service->getId());

        $this->assertRedirectTo('/restaurant');

        $this->assertEquals($restaurantSession->currentRestaurant->getId(), $service->getId());
    }



    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 10.06.2011
     */
    public function testUserLogin() {

        $admin = $this->createRandomAdministrationUser();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'user' => $admin->getEmail(),
            'pass' => 'admin'
        ));
        $this->dispatch('/administration/login');
        $this->assertRedirectTo('/administration/dashboard');

        $customer = $this->getRandomCustomer();
        $session = new Zend_Session_Namespace('Default');
        $this->dispatch('/administration/userlogin/id/'.$customer->getId());

        $this->assertRedirectTo($customer->getStartUrl());

        $this->assertEquals($session->customerId, $customer->getId());

    }

    /**
     * certain domains should redirect to another url(e.g. smakuje -> pyszne, eat-star -> lieferando)
     * @author Allen Frank <frank@lieferando.de>
     * @since 24.05.2012
     */
    public function testAdministrationRedirect() {
        
        $this->assertFalse((bool)$this->config->administration->redirect->enabled);
        
        $admin = $this->createRandomAdministrationUser();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'user' => $admin->getEmail(),
            'pass' => 'admin'
        ));
        $this->dispatch('/administration/login');
        $this->assertRedirectTo('/administration/dashboard');
        
        $this->dispatch('/administration/logout'); 
        $this->resetRequest();
        $this->getRequest()->setHeader('Authorization', 'Basic '.  base64_encode('gf:thisishell'));
                
        $this->config->administration->redirect->enabled = 1;
        $this->config->administration->redirect->url = 'http://www.some-fancy-redirect.test/administration/login';
        $this->assertTrue((bool)$this->config->administration->redirect->enabled);
        $this->dispatch('/administration/login');
        $this->assertRedirectTo("http://www.some-fancy-redirect.test/administration/login");

    }

}
