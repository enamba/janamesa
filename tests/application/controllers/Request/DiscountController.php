<?php

/**
 * Description of Request_DiscountControllerTest
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses
 */
class Request_DiscountControllerTest extends Yourdelivery_Test {

    public function setUp() {
        parent::setUp();
        // init new session for each test
        $session = new Zend_Session_Namespace('Default');
    }

    /**
     * discount ist not usable (not yet + not any more)
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @modified Daniel Hahn <hahn@lieferando.de>
     * @since 02.02.2012, 08.02.2012
     */
    public function testNotActiveDiscount() {


        $discount = $this->createNewCustomerDiscount(array('end' => date('Y-m-d H:i:s', strtotime('-10 minutes'))));

        $this->assertFalse($discount->isActive());

        $this->bootstrap();
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setParam('referer', $discount->getReferer());
        $this->dispatch('request_discount/code');

        $this->assertController('request_discount');
        $this->assertAction('error');

        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status']);
        $this->assertEquals(__("Diese Gutscheinaktion ist beendet."), $data['response']);
    }

    /**
     * put some email in blacklist and try to register should fail
     *
     * @author Allen Frank <frank@lieferando.de>
     * @since 25-01-2012
     */
    public function testTryToRegisterWithBlacklistedEmailShouldFail() {
        /* @depracated BLACKLIST */
        $email = 'my-unique-email@' . time() . '.de';
        $fp = fopen(BLACKLIST, 'w+');
        fputs($fp, $email);
        fclose($fp);

        $discount = $this->createNewCustomerDiscount(array('type' => 2));
        $codes = $discount->getCodes(true);

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'code' => $codes[0]['registrationCode']
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/code');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status']);

        $this->resetRequest();
        $this->resetResponse();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'prename' => 'Samson',
            'name' => 'Tiffy',
            'email' => $email
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/email');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status']);
    }

    /**
     * 1. place an unregistered order
     * 2. try to register with mail from order
     *
     * @author Allen Frank <frank@lieferando.de>
     * @since 25-01-2012
     */
    public function testNewCustomerWithPlacedOrderEmailFail() {

        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);

        $discount = $this->createNewCustomerDiscount(array('type' => 2));
        $codes = $discount->getCodes(true);

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'code' => $codes[0]['registrationCode']
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/code');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status']);

        $this->resetRequest();
        $this->resetResponse();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'prename' => 'Samson',
            'name' => 'Tiffy',
            'email' => $order->getCustomer()->getEmail()
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/email');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status']);
    }

    /**
     * 1. place an unregistered order
     * 2. try to register with telephonenumber from order
     *
     * @author Allen Frank <frank@lieferando.de>
     * @since 25-01-2012
     */
    public function testNewCustomerWithPlacedOrderTelFail() {
        $orderId = $this->placeOrder();
        $order = new Yourdelivery_Model_Order($orderId);

        $discount = $this->createNewCustomerDiscount(array('type' => 2));
        $codes = $discount->getCodes(true);

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'code' => $codes[0]['registrationCode']
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/code');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status'], implode(', ', $data));

        $this->resetRequest();
        $this->resetResponse();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'prename' => 'Samson',
            'name' => 'Tiffy',
            'email' => time() . '-' . $order->getCustomer()->getEmail()
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/email');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status'], implode(', ', $data));


        $this->resetRequest();
        $this->resetResponse();

        $session = new Zend_Session_Namespace('Default');
        $session->emailConfirmed = true;
        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'tel' => $order->getCustomer()->getTel()
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/tel');
        $this->assertController('request_discount');
        $this->assertAction('tel');

        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status'], implode(', ', $data));
    }

    /**
     * 1. get random customer
     * 2. try to register with mail from customer
     *
     * /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.01.2012
     */
    public function testNewCustomerWithExistingAccountEmailFail() {

        $discount = $this->createNewCustomerDiscount(array('type' => 2));
        $codes = $discount->getCodes(true);

        $customer = $this->getRandomCustomer();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'code' => $codes[0]['registrationCode']
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/code');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status']);

        $this->resetRequest();
        $this->resetResponse();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'prename' => 'Samson',
            'name' => 'Tiffy',
            'email' => $customer->getEmail()
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/email');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status']);
        $this->assertEquals(__('Deine E-Mail-Adresse konnte nicht verifiziert werden'), $data['email']);
    }

    /**
     * 1. get random customer
     * 2. try to register with mail from customer
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.01.2012
     */
    public function testNewCustomerWithExistingAccountTelFail() {

        $discount = $this->createNewCustomerDiscount(array('type' => 2));
        $codes = $discount->getCodes(true);

        $customer = $this->getRandomCustomer();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'code' => $codes[0]['registrationCode']
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/code');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status'], implode(', ', $data));

        $this->resetRequest();
        $this->resetResponse();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'prename' => 'Samson',
            'name' => 'Tiffy',
            'email' => time() . '-' . $customer->getEmail()
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/email');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status'], implode(', ', $data));


        $this->resetRequest();
        $this->resetResponse();

        $session = new Zend_Session_Namespace('Default');
        $session->emailConfirmed = true;
        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'tel' => $customer->getTel()
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/tel');
        $this->assertController('request_discount');
        $this->assertAction('tel');

        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status'], implode(', ', $data));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.01.2012
     */
    public function testNewCustomerSuccess() {

        $discount = $this->createNewCustomerDiscount(array('type' => 2));
        $codes = $discount->getCodes(true);

        $customer = $this->getRandomCustomer();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'code' => $codes[0]['registrationCode']
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/code');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status'], implode(', ', $data));

        $this->resetRequest();
        $this->resetResponse();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'prename' => 'Samson',
            'name' => 'Tiffy',
            'email' => time() . '-' . $customer->getEmail()
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/email');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status'], implode(', ', $data));


        $this->resetRequest();
        $this->resetResponse();

        $session = new Zend_Session_Namespace('Default');
        $session->emailConfirmed = true;
        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'tel' => time() . '-uniqueTel-' . Default_Helper::generateRandomString(4)
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/tel');
        $this->assertController('request_discount');
        $this->assertAction('tel');

        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status'], implode(', ', $data));
    }

    /**
     * test register for two different newcustomer discounts with idenitical email
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.01.2012
     */
    public function testNewCustomerEmailVerification() {

        $discount = $this->createNewCustomerDiscount(array('type' => 1));

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'prename' => 'Samson',
            'name' => 'Tiffy',
            'email' => $email = 'any-email-' . time() . '@domain.com'
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/email');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status']);

        $this->resetRequest();
        $this->resetResponse();

        // create other campaign and try again with same email
        $discount = $this->createNewCustomerDiscount(array('type' => 1));
        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'prename' => 'Samson',
            'name' => 'Tiffy',
            'email' => $email,
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/email');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status']);
    }

    /**
     * the link with code in email should never differ
     * no matter how often customer clicked link
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.02.2012
     */
    public function testSendConfirmEmailAgain() {
        $session = new Zend_Session_Namespace('Default');
        $discount = $this->createNewCustomerDiscount(array('type' => 1));

        $check = new Yourdelivery_Model_Rabatt_Check();
        $check->setData(array(
            'referer' => $discount->getReferer(),
            'email' => 'unique-email-' . time() . '@domain.com',
            'name' => 'Haferkorn',
            'prename' => 'Felix',
            'tel' => '0987654321',
            'codeEmail' => 'this-is-my-crypt-testlink'
        ));
        $id = $check->save();

        $session->rabattCheckId = $id;

        $this->dispatch('/request_discount/resendmail');

        $json = $this->getResponse()->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status']);
    }

    /**
     * the link with code in email should never differ
     * no matter how often customer clicked link
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.02.2012
     */
    public function testSendConfirmEmailMoreThan3TimesFail() {
        $session = new Zend_Session_Namespace('Default');
        $discount = $this->createNewCustomerDiscount(array('type' => 1));

        $check = new Yourdelivery_Model_Rabatt_Check();
        $check->setData(array(
            'referer' => $discount->getReferer(),
            'email' => 'unique-email-' . time() . '@domain.com',
            'name' => 'Haferkorn',
            'prename' => 'Felix',
            'tel' => '0987654321',
            'codeEmail' => 'this-is-my-crypt-testlink',
            'emailSendCount' => 1   // is already send once
        ));
        $id = $check->save();

        $session->rabattCheckId = $id;

        $this->dispatch('/request_discount/resendmail');

        $json = $this->getResponse()->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status']);

        // twice
        $this->resetRequest();
        $this->resetResponse();
        $this->dispatch('/request_discount/resendmail');

        $json = $this->getResponse()->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status']);

        // third sending should fail / second resend should fail
        $this->resetRequest();
        $this->resetResponse();
        $this->dispatch('/request_discount/resendmail');

        $json = $this->getResponse()->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status']);
        $this->assertEquals(__('Die Email wurde bereits drei mal verschickt'), $data['response']);
    }

    /**
     * the confirmation code in sms schould never differ
     * no matter how often customer clicked resend
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.02.2012
     */
    public function testResendSmsWithoutPost() {

        // without params should fail
        $this->dispatch('/request_discount/resendsms');

        $json = $this->getResponse()->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status']);
        $this->assertEquals(__('Request Fehler!'), $data['response']);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.02.2012
     */
    public function testResendSmsWitoutDataSetInSession() {
        // without params should fail
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/request_discount/resendsms');

        $json = $this->getResponse()->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status']);
        $this->assertEquals(__('Request Fehler!'), $data['response']);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.02.2012
     */
    public function testResendSmsSuccess() {
        $session = new Zend_Session_Namespace('Default');
        $discount = $this->createNewCustomerDiscount(array('type' => 1));

        $check = new Yourdelivery_Model_Rabatt_Check();
        $check->setData(array(
            'referer' => $discount->getReferer(),
            'email' => 'unique-email-' . time() . '@domain.com',
            'name' => 'Haferkorn',
            'prename' => 'Felix',
            'tel' => '0987654321',
            'codeEmail' => 'this-is-my-crypt-testlink',
            'emailSendCount' => 1, // is already send once
            'smsSendCount' => 1
        ));
        $id = $check->save();

        $session->rabattCheckId = $id;
        $session->emailConfirmed = true;

        $this->getRequest()->setMethod('POST');
        $this->dispatch('/request_discount/resendsms');

        $json = $this->getResponse()->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status']);

        // set send to 3 and check for not sending again
        $check->setSmsSendCount(3);
        $check->save();

        $this->resetRequest();
        $this->resetResponse();

        $this->getRequest()->setMethod('POST');
        $this->dispatch('/request_discount/resendsms');

        $json = $this->getResponse()->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status']);
        $this->assertEquals(__("Die SMS wurde bereits drei mal verschickt!"), $data['response']);
    }

    /**
     * test confirm sms with wrong code
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.02.2012
     */
    public function testWrongSmsCodeShouldFail() {
        $session = new Zend_Session_Namespace('Default');
        $discount = $this->createNewCustomerDiscount(array('type' => 1));

        $check = new Yourdelivery_Model_Rabatt_Check();
        $check->setData(array(
            'referer' => $discount->getReferer(),
            'email' => 'unique-email-' . time() . '@domain.com',
            'name' => 'Haferkorn',
            'prename' => 'Felix',
            'tel' => '0987654321',
            'codeEmail' => 'this-is-my-crypt-testlink'
        ));
        $id = $check->save();

        $session->rabattCheckId = $id;
        $session->emailConfirmed = true;

        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setParam('codetel', 'something');
        $this->dispatch('/request_discount/telcode');

        $json = $this->getResponse()->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status']);
        $this->assertEquals(__("Dieser Code ist ungültig."), $data['response']);
    }

    /**
     * test resend final email with missing params
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.02.2012
     */
    public function testResendCodeWithMissingParams(){
        // without params in session should fail
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/request_discount/resendcode');

        $body = $this->getResponse()->getBody();
        $this->assertTrue(strstr($body, 'NOK') !== false );

        $this->resetRequest();
        $this->resetResponse();

        // without post should fail
        $this->dispatch('/request_discount/resendcode');

        $body = $this->getResponse()->getBody();
        $this->assertTrue(strstr($body, 'NOK') !== false );
    }


    /**
     * test resend final email
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.02.2012
     */
    public function testResendCode() {

        $session = new Zend_Session_Namespace('Default');
        $discount = $this->createNewCustomerDiscount(array('type' => 1));

        $rabattCode = new Yourdelivery_Model_Rabatt_Code($discount->generateCode(null, 13, '0123456789'));
        $cust = $this->getRandomCustomer();

        $check = new Yourdelivery_Model_Rabatt_Check();
        $check->setData(array(
            'referer' => $discount->getReferer(),
            'email' => 'unique-email-' . time() . '@domain.com',
            'name' => 'Haferkorn',
            'prename' => 'Felix',
            'tel' => '0987654321',
            'codeEmail' => 'this-is-my-crypt-testlink',
            'emailSendCount' => 2, // is already send once
            'smsSendCount' => 1,
            'customerId' => $cust->getId(),
            'rabattCodeId' => $rabattCode->getId()
        ));
        $id = $check->save();

        $session->rabattCheckId = $id;
        $session->emailConfirmed = true;

        $this->getRequest()->setMethod('POST');
        $this->dispatch('/request_discount/resendcode');

        $body = $this->getResponse()->getBody();
        $this->assertEquals('OK', $body);

        // set send to 3
        $check->setEmailSendCount(3);
        $check->save();

        $this->resetRequest();
        $this->resetResponse();

        $this->getRequest()->setMethod('POST');
        $this->dispatch('/request_discount/resendcode');
        $body = $this->getResponse()->getBody();
        $this->assertEquals('NOK:REQUESTFAILURE', $body);
    }



    /**
     * It's not allowed to call request with wrong referer
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.02.2012
     */
    public function testNotExistingReferer(){
        $deadlockPreventer = 0;

        do {
            $referer = 'test_' . time();
            $deadlockPreventer++;
        }
        while(!is_null(Yourdelivery_Model_Rabatt::getByReferer($referer)) && ($deadlockPreventer<10));

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $referer,
            'code' => 'somecode'
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/code');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status']);
        $this->assertEquals(__("Gutscheinaktion mit dieser URL existiert nicht!"), $data['response']);
    }

    /**
     * Correct code should be evaluated as valid
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.02.2012
     */
    public function testConfirmCodeInCorrectDiscountCampaign(){
        //avoid creating discounts wiht same referer
        sleep(1);

        // create new discount
        $referer = 'Testdiscount_' . time();
        $discount = new Yourdelivery_Model_Rabatt();
        $discount->setData(
                array(
                    'name' => 'Testdiscount_' . time(),
                    'referer' => $referer,
                    'status' => 1,
                    'rrepeat' => 0,
                    'countUsage' => 0,
                    'kind' => 1,
                    'type' => 2,
                    'rabatt' => 500,
                    'start' => date('Y-m-d H:i:s', time()),
                    'end' => date('Y-m-d H:i:s', time() + 600),
                )
            );
        $discount->save();
        $discount->generateCodes(1);

        //now test the correct code
        $codes = $discount->getCodes(true);
        $regCode = $codes[0];

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $referer,
            'code' => $regCode['registrationCode']
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/code');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status']);
    }

    /**
     * Correct code from wrong campaign should be evaluated as invalid
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.02.2012
     */
    public function testConfirmCodeInWrongDiscountCampaign(){
        //avoid creating discounts with same referer
        sleep(1);

        // create new discount
        $referer = 'Testdiscount_' . time();
        $discount = new Yourdelivery_Model_Rabatt();
        $discount->setData(
                array(
                    'name' => 'Testdiscount_' . time(),
                    'referer' => $referer,
                    'status' => 1,
                    'rrepeat' => 0,
                    'countUsage' => 0,
                    'kind' => 1,
                    'type' => 2,
                    'rabatt' => 500,
                    'start' => date('Y-m-d H:i:s', time()),
                    'end' => date('Y-m-d H:i:s', time() + 600),
                )
            );
        $discount->save();
        $discount->generateCodes(1);

        // create another discount with code for testing
        $referer2 = 'Testdiscount2_' . time();
        $discount2 = new Yourdelivery_Model_Rabatt();
        $discount2->setData(
                array(
                    'name' => 'Testdiscount2_' . time(),
                    'referer' => $referer2,
                    'status' => 1,
                    'rrepeat' => 0,
                    'countUsage' => 0,
                    'kind' => 1,
                    'type' => 2,
                    'rabatt' => 100,
                    'start' => date('Y-m-d H:i:s', time()),
                    'end' => date('Y-m-d H:i:s', time() + 600),
                )
            );
        $discount2->save();
        $discount2->generateCodes(1);

        //now test the code from wrong campaign
        $codes = $discount2->getCodes(true);
        $regCode = $codes[0];

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $referer,
            'code' => $regCode['registrationCode']
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/code');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status']);
    }


    /**
     * code is not usable, the usable case is covered with testConfirmCodeInCorrectDiscountCampaign
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.02.2012
     */
    public function testCodeNotUsable() {
        //avoid creating discounts wiht same referer
        sleep(1);

        // create new discount
        $discount = $this->createNewCustomerDiscount(array('type' => 2));
        $discount->generateCodes(1);

        //now test the correct code
        $codes = $discount->getCodes(true);
        $regCode = $codes[0];

        $rcObj = new Yourdelivery_Model_Rabatt_CodesVerification($regCode['id']);
        $rcObj->setSend(1);
        $rcObj->save();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $post = array(
            'referer' => $discount->getReferer(),
            'code' => $rcObj->getRegistrationCode()
        );

        $request->setPost($post);
        $this->dispatch('/request_discount/code');
        $json = $this->getResponse()->getBody();

        $data = json_decode($json, true);

        $this->assertEquals('NOK', $data['status']);
        $this->assertEquals('Dieser Gutscheincode ist abgelaufen oder wurde schon einmal benutzt.', $data['response']);

    }

    /**
     * Asserts, that hash is correctly generated
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 10.08.2012
     */
    public function testDiscountHash() {
        $discount = $this->createNewCustomerDiscount(array('type' => 1));
        $this->assertEquals($discount->makeHash(), $discount->getHash());
        $discount->delete();
    }

    /**
     * Tests the randomCodeFromDiscount Call with invalid hash
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 10.08.2012
     */
    public function testRandomCodeFromDiscountWithInvalidHash() {
        $request = $this->getRequest();
        $request->setMethod('POST');

        $post = array('rabattHash' => 1);

        $request->setPost($post);
        $this->dispatch('/request_discount/randomcodefromdiscount/');
        $json = $this->getResponse()->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status']);
    }

    /**
     * Tests the randomCodeFromDiscount Call with inactive discount
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 10.08.2012
     */
    public function testRandomCodeFromDiscountWithInactiveDiscount() {
        $discount = $this->createNewCustomerDiscount(array('type' => 1));
        $discount->generateCodes(5);
        $discount->setStatus(0);
        $discount->save();
        $request = $this->getRequest();
        $request->setMethod('POST');

        $post = array('rabattHash' => $discount->getHash());

        $request->setPost($post);
        $this->dispatch('/request_discount/randomcodefromdiscount/');
        $json = $this->getResponse()->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status']);
        $discount->delete();
    }

    /**
     * Tests the randomCodeFromDiscount Call with inactive discount
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 10.08.2012
     */
    public function testRandomCodeFromDiscountWithUsedDiscountCode() {
        $discount = $this->createNewCustomerDiscount(array('type' => 0));
        $discount->save();
        $discount->generateCodes(1);
        $codes = $discount->getCodes()->toArray();
        $code = new Yourdelivery_Model_Rabatt_Code(null, $codes[0]['id']);

        $this->assertTrue($code instanceof Yourdelivery_Model_Rabatt_Code && $code->getId() > 0);
        $code->setCodeUsed();
        $code->save();

        $request = $this->getRequest();
        $request->setMethod('POST');

        $post = array('rabattHash' => $discount->getHash());

        $request->setPost($post);
        $this->dispatch('/request_discount/randomcodefromdiscount/');
        $json = $this->getResponse()->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('NOK', $data['status']);
        $discount->delete();
    }

    /**
     * Tests the randomCodeFromDiscount Call
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 10.08.2012
     */
    public function testRandomCodeFromDiscount() {
        $discount = $this->createNewCustomerDiscount(array('type' => 0));
        $discount->save();
        $discount->generateCodes(1);
        $codes = $discount->getCodes()->toArray();
        $code = new Yourdelivery_Model_Rabatt_Code(null, $codes[0]['id']);

        $this->assertTrue($code instanceof Yourdelivery_Model_Rabatt_Code && $code->getId() > 0);

        $request = $this->getRequest();
        $request->setMethod('POST');

        $post = array('rabattHash' => $discount->getHash());

        $request->setPost($post);
        $this->dispatch('/request_discount/randomcodefromdiscount/');
        $json = $this->getResponse()->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('OK', $data['status']);
        $discount->delete();
    }
}

