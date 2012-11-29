<?php

/**
 * @author mlaug
 */

/**
 * @runTestsInSeparateProcesses 
 */
class CustomerApiTest extends Yourdelivery_Test {

    /**
     * @author mlaug
     * @modified fhaferkorn, 30.09.2011
     */
    public function testCustomerPostAndGet() {

        $customer = $this->getRandomCustomer(null, true);
        $points = $customer->getFidelity()->getPoints();

        $customer->setPassword(md5('testen'));
        $customer->save();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'email' => $customer->getEmail(),
            'password' => 'testen'
        ));

        $this->dispatch('/get_customer');
        $response = $this->getResponse();
        $data = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($data);

        $success = $doc->getElementsByTagName("success");
        $access = $doc->getElementsByTagName("access");
        $this->assertEquals('true', $success->item(0)->nodeValue, Default_Helpers_Log::getLastLog());
        $this->assertEquals($customer->getSalt(), $access->item(0)->nodeValue, Default_Helpers_Log::getLastLog());

        //use provided login url to gain access
        unset($doc);
        $this->resetResponse(); //remove xml data of last request
        $request->setMethod('GET');
        $this->dispatch('/get_customer/' . $access->item(0)->nodeValue);
        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $id = $doc->getElementsByTagName("id");
        $this->assertEquals($customer->getId(), $id->item(0)->nodeValue);

        //test for nodes
        $this->assertTrue($doc->getElementsByTagName("location")->length > 0, 'Did not get location tag in response. complete response was: ' . implode(',', $data));
        $this->assertTrue($doc->getElementsByTagName("locations")->length > 0);
        $this->assertGreaterThan(0, $doc->getElementsByTagName("name")->length);
        $this->assertGreaterThan(0, $doc->getElementsByTagName("prename")->length);
        $this->assertGreaterThan(0, $doc->getElementsByTagName("email")->length);
        $this->assertGreaterThan(0, $doc->getElementsByTagName("picture")->length);
        $this->assertGreaterThan(0, $doc->getElementsByTagName("gender")->length);
        $this->assertGreaterThan(0, $doc->getElementsByTagName("birthday")->length);
        $this->assertGreaterThan(0, $doc->getElementsByTagName("nickname")->length);
        $this->assertGreaterThan(0, $doc->getElementsByTagName("fidelitypoints")->length);

        $fidelity = $doc->getElementsByTagName("fidelitypoints");
        $this->assertEquals($points, $fidelity->item(0)->nodeValue);

        $success = $doc->getElementsByTagName("success");
        $this->assertEquals('true', $success->item(0)->nodeValue);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testGetWithLocations() {
        $customer = null;
        while (true) {
            $customer = $this->getRandomCustomer();
            if ($customer->getLocations()->count() > 0) {
                break;
            }
        }

        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_customer/' . $customer->getSalt());
        $this->assertResponseCode(200);
        $this->assertController('get_customer');
        $this->assertAction('get');
        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $this->assertGreaterThan(0, $doc->getElementsByTagName("location")->length);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPostWithoutParams() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_customer/');
        $this->assertResponseCode(403);
        $this->assertController('get_customer');
        $this->assertAction('post');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPostWithNonExistingEmailFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('email' => 'non-existant-email@' . time() . '.de', 'password' => 'any-password'));
        $this->dispatch('/get_customer/');
        $this->assertResponseCode(403);
        $this->assertController('get_customer');
        $this->assertAction('post');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPostWithWrongPasswordFail() {
        $customer = $this->getRandomCustomer();
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('email' => $customer->getEmail(), 'password' => 'invalid-password' . time()));
        $this->dispatch('/get_customer/');
        $this->assertResponseCode(403);
        $this->assertController('get_customer');
        $this->assertAction('post');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.12.2011
     */
    public function testCustomerPostToRegister() {
        $email = 'this-is-my-unique-email@' . time() . '.com';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = sprintf('{
            "email":"' . $email . '",
            "password":"testen",
            "prename":"Felix",
            "name":"Haferkorn",
            "tel":"0987654321"}');
        $request->setPost('parameters', $params);
        $request->setParam('register', true);
        $request->setParam('parameters', $params);

        $this->dispatch('/get_customer');

        $this->assertController('get_customer');
        $this->assertAction('post');
        $this->assertResponseCode(201);
        $response = $this->getResponse();
        $data = $response->getBody();



        $doc = new DOMDocument();
        $doc->loadXML($data);

        $success = $doc->getElementsByTagName("success");
        $access = $doc->getElementsByTagName("access");
        $this->assertEquals('true', $success->item(0)->nodeValue);
        $access = $access->item(0)->nodeValue;

        $customer = new Yourdelivery_Model_Customer(null, null, $access);
        $this->assertInstanceof(Yourdelivery_Model_Customer, $customer);
        $this->assertTrue($customer->isPersistent());
        if ($this->config->newsletter->method == 'doubleoptin') {
            $this->assertFalse($customer->getNewsletter(), 'NewsletterFlag IS set but double Opt In is set ... WTF');
        } else {
            $this->assertTrue($customer->getNewsletter(), 'NewsletterFlag is NOT set ... WTF');
        }

        $this->resetResponse();

        $request = $this->getRequest();
        $request->setMethod('GET');
        // check some values after register
        $this->dispatch('/get_customer_stats/' . $access);
        $this->assertController('get_customer_stats');
        $this->assertAction('get');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $this->assertEquals(0, $doc->getElementsByTagName('earnedfidelitypoints')->nodeValue);
        $this->assertEquals(0, $doc->getElementsByTagName('maxavailablefidelitypoints')->nodeValue);
        $this->assertEquals(0, $doc->getElementsByTagName('countorders')->nodeValue);

        // check login with new created account
        list($success, $access) = $this->_login($email, 'testen');

        $this->assertTrue($success);
        $this->assertEquals($customer->getSalt(), $access);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 15.06.2012
     */
    public function testCustomerPostRegisterWithTooShortTelFail() {
        $email = 'this-is-my-unique-email@' . time() . '.com';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = sprintf('{
            "email":"' . $email . '",
            "password":"testen",
            "prename":"Felix",
            "name":"Haferkorn",
            "tel":"123456"}');
        $request->setPost('parameters', $params);
        $request->setParam('register', true);
        $request->setParam('parameters', $params);

        $this->dispatch('/get_customer');

        $this->assertController('get_customer');
        $this->assertAction('post');
        $this->assertResponseCode(403);

        $response = $this->getResponse();
        $data = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($data);

        $this->assertTrue(strpos($data, '* '. __('Die Telefonnummer ist zu kurz. (mind. %d Zeichen)', 7)) !== false);
    }
    

    public function testCustomerPostAndPut() {

        $customer = $this->getRandomCustomer();
        $customer->setPassword(md5('testen'));
        $customer->save();

        $oldEmail = $customer->getEmail();

        $customerId = $customer->getId();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'email' => $customer->getEmail(),
            'password' => 'testen'
        ));

        $this->dispatch('/get_customer');
        $response = $this->getResponse();
        $data = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($data);

        $success = $doc->getElementsByTagName("success");
        $access = $doc->getElementsByTagName("access");
        $this->assertEquals('true', $success->item(0)->nodeValue, Default_Helpers_Log::getLastLog());
        $this->assertEquals($customer->getSalt(), $access->item(0)->nodeValue, Default_Helpers_Log::getLastLog());

        $passMd5 = $customer->getPassword();
        //use provided login url to gain access
        unset($doc);
        $this->resetResponse(); //remove xml data of last request
        //create params
        $uniqueEmail = "eliego@" . time() . ".se";

        $points = $customer->getFidelity()->getPoints();
        // add 2 fidelity transactions to test migration of points
        $randomPoints1 = rand(12, 127);
        $customer->addFidelityPoint('testSetSettingsSuccess', 'we want to test the migration of an email to another', $randomPoints1);
        $randomPoints2 = rand(12, 127);
        $customer->addFidelityPoint('testSetSettingsSuccess', 'we want to test the migration of an email to another', $randomPoints2);

        $paramsCorrect = sprintf('{
                    "name":"Kaas",
                    "prename":"Eli23",
                    "access":"%s",
                    "email":"%s",
                    "gender":"m",
                    "birthday":"5.12.1980",
                    "nickname":"samson tiffy",
                    "password":"fubar89",
                    "tel":"1234567"}', $access->item(0)->nodeValue, $uniqueEmail);


        $paramsWrong = sprintf('{
                    "access":"282828282",
                    "name":"Kaas",
                    "prename":"Eli23",
                    "email":"%s",
                    "tel":"1234567"}', $uniqueEmail);

        //do not provide parameters, should fail
        $request->setMethod('PUT');
        $this->dispatch('/get_customer');
        $this->assertResponseCode(406);

        //do not provide access, should fail
        $this->resetResponse();
        $request->setMethod('PUT');
        $this->dispatch('/get_customer');
        $request->setParam('parameters', $paramsWrong);
        $request->setPost(array('parameters' => $paramsWrong));
        $this->dispatch('/get_customer');
        $this->assertResponseCode(403);
        
        $this->resetResponse();
        $request->setMethod('PUT');

        $request->setParam('parameters', $paramsCorrect);
        $request->setPost(array('parameters' => $paramsCorrect));

        $this->dispatch('/get_customer');
        $this->assertController('get_customer');
        $this->assertAction('put');
        $this->assertResponseCode(200);

        $customer = new Yourdelivery_Model_Customer($customerId);
        $this->assertEquals($customer->getName(), 'Kaas');
        $this->assertEquals($customer->getPrename(), 'Eli23');
        $this->assertEquals($customer->getEmail(), $uniqueEmail);
        $this->assertEquals('m', $customer->getSex());
        $this->assertEquals('samson tiffy', $customer->getNickname());
        $this->assertEquals('1980-12-05', $customer->getBirthday());
        $this->assertNotEquals($passMd5, $customer->getPassword());
        $this->assertEquals(md5('fubar89'), $customer->getPassword());

        // check fidelity migration
        $this->assertEquals($points + $randomPoints1 + $randomPoints2, $customer->getFidelity()->getPoints());


        $paramsIncomplete = sprintf('{
                    "name":"Kaas",
                    "prename":"Eli23",
                    "access":"%s",
                    "email":"%s",
                    "gender":"",
                    "birthday":"",
                    "nickname":"",
                    "tel":"1234567"}', $access->item(0)->nodeValue, $uniqueEmail);

        $this->resetResponse();
        $request->setMethod('PUT');

        $request->setParam('parameters', $paramsIncomplete);
        $request->setPost(array('parameters' => $paramsIncomplete));

        $this->dispatch('/get_customer');
        $this->assertController('get_customer');
        $this->assertAction('put');
        $this->assertResponseCode(200);

        $customer = new Yourdelivery_Model_Customer($customerId);
        $this->assertEquals($customer->getName(), 'Kaas');
        $this->assertEquals($customer->getPrename(), 'Eli23');
        $this->assertEquals($customer->getEmail(), $uniqueEmail);
        $this->assertEquals('m', $customer->getSex());
        $this->assertEquals('', $customer->getNickname(), $customer->getId());
        $this->assertEquals('1980-12-05', $customer->getBirthday());
        $this->assertEquals(md5('fubar89'), $customer->getPassword());
    }

    /**
     * test post with invalid email
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testPostWithInvalidDataFail() {

        // invalid email
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = sprintf('{
            "email":"this-is-an-invalid-email.com",
            "password":"testen",
            "prename":"Felix",
            "name":"Haferkorn",
            "tel":"0987654321"}');
        $request->setPost('parameters', $params);
        $request->setParam('register', true);
        $request->setParam('parameters', $params);

        $this->dispatch('/get_customer');

        $this->assertController('get_customer');
        $this->assertAction('post');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testRegisterWithExistingEmailFail() {
        $customer = $this->getRandomCustomer();
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = sprintf('{
            "email":"' . $customer->getEmail() . '",
            "password":"testen",
            "prename":"Felix",
            "name":"Haferkorn",
            "tel":"0987654321"}');
        $request->setPost('parameters', $params);
        $request->setParam('register', true);
        $request->setParam('parameters', $params);

        $this->dispatch('/get_customer');

        $this->assertController('get_customer');
        $this->assertAction('post');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testRegisterWithExistingDeletedEmailSuccess() {
        $customer = $this->getRandomCustomer();
        $customer->delete();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = sprintf('{
            "email":"' . $customer->getEmail() . '",
            "password":"testen",
            "prename":"Felix",
            "name":"Haferkorn",
            "tel":"0987654321"}');
        $request->setPost('parameters', $params);
        $request->setParam('register', true);
        $request->setParam('parameters', $params);

        $this->dispatch('/get_customer');

        $this->assertController('get_customer');
        $this->assertAction('post');
        $this->assertResponseCode(201);

        $response = $this->getResponse();
        $data = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($data);

        $this->assertEquals('true', $doc->getElementsByTagName("success")->item(0)->nodeValue);

        $fidelityConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini', APPLICATION_ENV);
        $this->assertEquals($fidelityConfig->fidelity->points->register, $doc->getElementsByTagName("points")->item(0)->nodeValue);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 15.11.2011
     */
    public function testCustomerPutWithInvalidData() {

        $customer = $this->getRandomCustomer();
        $customer->setPassword(md5('testen'));
        $customer->save();

        $customerId = $customer->getId();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'email' => $customer->getEmail(),
            'password' => 'testen'
        ));

        $this->dispatch('/get_customer');
        $response = $this->getResponse();
        $data = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($data);

        $success = $doc->getElementsByTagName("success");
        $access = $doc->getElementsByTagName("access");
        $this->assertEquals('true', $success->item(0)->nodeValue, Default_Helpers_Log::getLastLog());
        $this->assertEquals($customer->getSalt(), $access->item(0)->nodeValue, Default_Helpers_Log::getLastLog());

        $paramsIncorrect = sprintf('{
                    "name":"Felix",
                    "prename":"Hafer",
                    "access":"%s",
                    "email":"this-is-no-valid-email-' . time() . '",
                    "tel":"123123"}', $access->item(0)->nodeValue);

        $this->resetResponse();
        $request->setMethod('PUT');
        $request->setParam('parameters', $paramsIncorrect);
        $request->setPost(array('parameters' => $paramsIncorrect));
        $this->dispatch('/get_customer');

        $this->assertResponseCode(403);
    }

    /**
     * test reset pass via api
     *
     * @author Feli Haferkorn <haferkorn@lieferando.de>
     * @since 18.11.2011
     *
     */
    public function testResetPasswordViaApi() {

        $customer = $this->getRandomCustomer();
        $customer->setPassword(md5('testen'));
        $customer->save();
        $customerId = $customer->getId();

        $customer = new Yourdelivery_Model_Customer($customerId);
        $this->assertEquals($customer->getPassword(), md5('testen'));

        $request = $this->getRequest();
        $request->setMethod('PUT');

        $params = sprintf('{"resetpassword":"%s"}', $customer->getEmail());

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));
        $this->dispatch('/get_customer');
        $this->assertResponseCode(200);

        $customer = null;
        $customer = new Yourdelivery_Model_Customer($customerId);
        $newpass = $customer->getPassword();
        $this->assertNotEquals($newpass, md5('testen'));

        // test invalid email - should not reset pass
        $request = $this->getRequest();
        $request->setMethod('PUT');

        $params = sprintf('{"resetpassword":"%s"}', 'this-is-an-invalid-email');

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));
        $this->dispatch('/get_customer');
        $this->assertResponseCode(404);

        $customer = null;
        $customer = new Yourdelivery_Model_Customer($customerId);
        $this->assertEquals($newpass, $customer->getPassword());

        // test deleted customer
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $row = $db->fetchRow('SELECT id, email, password FROM customers where deleted > 0 ORDER BY RAND() LIMIT 1');
        $params = sprintf('{"resetpassword":"%s"}', $row['email']);

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));
        $this->dispatch('/get_customer');
        $this->assertResponseCode(404);

        $customer = null;
        $customer = new Yourdelivery_Model_Customer($row['id']);
        $this->assertEquals($row['password'], $customer->getPassword());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.12.2011
     *
     * @param string $email
     * @param string $pass
     *
     * @return array(BOOLEAN $success, STRING $access)
     */
    private function _login($email, $pass) {
        $this->resetRequest();
        $this->resetResponse();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'email' => $email,
            'password' => $pass
        ));

        $this->dispatch('/get_customer');
        $response = $this->getResponse();
        $data = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($data);

        $success = $doc->getElementsByTagName("success");
        $access = $doc->getElementsByTagName("access");

        return array($success->item(0)->nodeValue == 'true' ? true : false, $access->item(0)->nodeValue);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testGetWithoutAccessFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('get_customer/invalid-access');
        $this->assertController('get_customer');
        $this->assertAction('get');
        $this->assertResponseCode(404);

        $response = $this->getResponse();
        $data = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($data);

        $this->assertEquals('false', $doc->getElementsByTagName("success")->item(0)->nodeValue);
        $this->assertEquals('no access', $doc->getElementsByTagName("message")->item(0)->nodeValue);
    }

    /**
     * test incorrect json
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testPutFail() {
        $request = $this->getRequest();
        $paramsIncorrect = sprintf('{
                    "name":"Felix",
                    "prename":"Hafer",
                    "access":"98765rghughjuhg",
                    "email":this-is-no-valid-email-' . time() . '",
                    "tel":"123123"}');

        $request->setMethod('PUT');
        $request->setParam('parameters', $paramsIncorrect);
        $request->setPost(array('parameters' => $paramsIncorrect));
        $this->dispatch('/get_customer');

        $this->assertController('get_customer');
        $this->assertAction('put');
        $this->assertResponseCode(406);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testDeleteFail() {
        $request = $this->getRequest();
        $request->setMethod('DELETE');

        $this->dispatch('/get_customer');
        $this->assertController('get_customer');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testIndexFail() {
        $this->dispatch('/get_customer?blub=bla');
        $this->assertController('get_customer');
        $this->assertAction('index');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.01.2012
     */
    public function testPutWithoutChangingPassword() {
        $request = $this->getRequest();
        $customer = $this->getRandomCustomer();
        $paramsIncorrect = '{"name":"Felix",
                    "prename":"Hafer",
                    "access":"' . $customer->getSalt() . '",
                    "email":"' . $customer->getEmail() . '",
                    "tel":"1234567"}';

        $request->setMethod('PUT');
        $request->setPost(array('parameters' => $paramsIncorrect));
        $this->dispatch('/get_customer');
        $this->assertResponseCode(200);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.01.2012
     */
    public function testPutWithInvalidData() {

        // password too short
        $request = $this->getRequest();
        $customer = $this->getRandomCustomer();
        $paramsIncorrect = '{
                    "name":"Hafer",
                    "prename":"Felix",
                    "access":"' . $customer->getSalt() . '",
                    "email":"' . $customer->getEmail() . '",
                    "password":"1234",
                    "tel":"1234567"}';

        $request->setMethod('PUT');
        $request->setPost(array('parameters' => $paramsIncorrect));
        $this->dispatch('/get_customer');

        $this->assertResponseCode(403);

        $this->resetRequest();
        $this->resetResponse();

        // name too short
        $request = $this->getRequest();
        $customer = $this->getRandomCustomer();
        $paramsIncorrect = '{
                    "name":"Ha",
                    "prename":"Felix",
                    "access":"' . $customer->getSalt() . '",
                    "email":"' . $customer->getEmail() . '",
                    "password":"12345",
                    "tel":"1234567"}';

        $request->setMethod('PUT');
        $request->setPost(array('parameters' => $paramsIncorrect));
        $this->dispatch('/get_customer');

        $this->assertResponseCode(403);

        $this->resetRequest();
        $this->resetResponse();

        // prename too short
        $request = $this->getRequest();
        $customer = $this->getRandomCustomer();
        $paramsIncorrect = '{
                    "name":"Hafer",
                    "prename":"Fe",
                    "access":"' . $customer->getSalt() . '",
                    "email":"' . $customer->getEmail() . '",
                    "password":"12345",
                    "tel":"1234567"}';

        $request->setMethod('PUT');
        $request->setPost(array('parameters' => $paramsIncorrect));
        $this->dispatch('/get_customer');

        $this->assertResponseCode(403);

        $this->resetRequest();
        $this->resetResponse();

        // telefon too short
        $request = $this->getRequest();
        $customer = $this->getRandomCustomer();
        $paramsIncorrect = '{
                    "name":"Hafer",
                    "prename":"Felix",
                    "access":"' . $customer->getSalt() . '",
                    "email":"' . $customer->getEmail() . '",
                    "password":"12345",
                    "tel":"123456"}';

        $request->setMethod('PUT');
        $request->setPost(array('parameters' => $paramsIncorrect));
        $this->dispatch('/get_customer');

        $this->assertResponseCode(403);

        $this->resetRequest();
        $this->resetResponse();

        // email of other customer
        $request = $this->getRequest();
        $customer = $this->getRandomCustomer();
        $otherCustomer = $this->getRandomCustomer();
        $paramsIncorrect = '{
                    "name":"Hafer",
                    "prename":"Felix",
                    "access":"' . $customer->getSalt() . '",
                    "email":"' . $otherCustomer->getEmail() . '",
                    "password":"12345",
                    "tel":"1234567"}';

        $request->setMethod('PUT');
        $request->setPost(array('parameters' => $paramsIncorrect));
        $this->dispatch('/get_customer');

        $this->assertResponseCode(403);
    }

    /**
     * this is a complex test to check fidelity points
     * - register customer
     * - check open actions
     * - check stats
     * - place order
     * - check open actions
     * - check stats
     * - rate order
     * - check open actions
     * - check stats
     *
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.04.2012
     */
    public function testCorrectValuesInStatsForANewCustomer() {
        // register user
        $email = 'this-is-my-unique-email@' . time() . rand(1, 9) . '.com';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = sprintf('{
            "email":"' . $email . '",
            "password":"testen",
            "prename":"Felix",
            "name":"Haferkorn",
            "tel":"0987654321"}');
        $request->setPost('parameters', $params);
        $request->setParam('register', true);
        $request->setParam('parameters', $params);

        $this->dispatch('/get_customer');

        $this->assertResponseCode(201);

        $doc = new DOMDocument();
        $doc->loadXML($this->getResponse()->getBody());
        $access = $doc->getElementsByTagName("access")->item(0)->nodeValue;

        // reset
        $this->resetRequest();
        $this->resetResponse();
        

        // check max available fidelity points

        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = '{
                "type":"openactions",
                "access":"' . $access . '"
            }';

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));

        $this->dispatch('/get_customer_fidelity/');
        $this->assertResponseCode(200);
        $doc = new DOMDocument();
        $doc->loadXML($this->getResponse()->getBody());
        $this->assertEquals(8, $doc->getElementsByTagName("openactionpoints")->item(0)->nodeValue);

        // reset
        $this->resetRequest();
        $this->resetResponse();


        // check fidelity points
        $request->setMethod('GET');
        $this->dispatch('/get_customer/' . $access);
        $doc = new DOMDocument();
        $doc->loadXML($this->getResponse()->getBody());
        $this->assertEquals(20, $doc->getElementsByTagName("fidelitypoints")->item(0)->nodeValue);


        // reset
        $this->resetRequest();
        $this->resetResponse();

        // get customer stats
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_customer_stats/' . $access);
        $this->assertResponseCode(200);
        $doc = new DOMDocument();
        $doc->loadXML($this->getResponse()->getBody());

        $this->assertEquals(0, $doc->getElementsByTagName("maxavailablefidelitypoints")->item(0)->nodeValue);

        // reset
        $this->resetRequest();
        $this->resetResponse();

        // do an order
        $customer = new Yourdelivery_Model_Customer(null, null, $access);
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('customer' => $customer)));

        // manipulate order - deliverTime
        $db = Zend_Registry::get('dbAdapter');
        $db->query(sprintf('Update orders SET time = "%s", deliverTime = "%s" WHERE id = %d', date('Y-m-d H:i:s', strtotime('-2 hours')), date('Y-m-d H:i:s', strtotime('-2 hours')), $order->getId()));
        $order->setStatus(Yourdelivery_Model_Order::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'testcase'));

        // check stats again
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_customer_stats/' . $access);
        $this->assertResponseCode(200);
        $doc = new DOMDocument();
        $doc->loadXML($this->getResponse()->getBody());

        $this->assertEquals(5, $doc->getElementsByTagName("maxavailablefidelitypoints")->item(0)->nodeValue);

        // reset
        $this->resetRequest();
        $this->resetResponse();

        // check max available fidelity points again
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = '{
                "type":"openactions",
                "access":"' . $access . '"
            }';

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));

        $this->dispatch('/get_customer_fidelity/');
        $this->assertResponseCode(200);
        $doc = new DOMDocument();
        $doc->loadXML($this->getResponse()->getBody());
        $this->assertEquals(13, $doc->getElementsByTagName("openactionpoints")->item(0)->nodeValue);

        // reset
        $this->resetRequest();
        $this->resetResponse();

        // rate order
        $json = '{"hash":"' . $order->getHash() . '", "advise":"1", "title":"this is a test title", "quality":"5", "delivery":"5", "comment":"this is a SHORT test comment","author":"Felix"}';
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array('parameters' => $json));
        $this->dispatch('/get_ratings');
        $this->assertResponseCode(201);

        // reset
        $this->resetRequest();
        $this->resetResponse();

        // set rating online
        $result = $db->fetchRow(sprintf('SELECT * FROM restaurant_ratings WHERE orderId = %d', $order->getId()));
        $this->assertGreaterThan(0, $result['id']);
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'ratingId' => $result['id']
        ));
        $this->dispatch('/request_administration/toggleratingstatus');
        $this->assertResponseCode(200);


        // reset
        $this->resetRequest();
        $this->resetResponse();

        // check max available fidelity points again
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = '{
                "type":"openactions",
                "access":"' . $access . '"
            }';

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));

        $this->dispatch('/get_customer_fidelity/');
        $this->assertResponseCode(200);
        $doc = new DOMDocument();
        $doc->loadXML($this->getResponse()->getBody());
        $this->assertEquals(8, $doc->getElementsByTagName("openactionpoints")->item(0)->nodeValue, $customer->getId() . ' ' . $customer->getEmail());

        // reset
        $this->resetRequest();
        $this->resetResponse();

        // get customer stats
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_customer_stats/' . $access);
        $this->assertResponseCode(200);
        $doc = new DOMDocument();
        $doc->loadXML($this->getResponse()->getBody());

        $this->assertEquals(0, $doc->getElementsByTagName("maxavailablefidelitypoints")->item(0)->nodeValue);
        $this->assertEquals(2, $doc->getElementsByTagName("earnedfidelitypoints")->item(0)->nodeValue);
    }

}
