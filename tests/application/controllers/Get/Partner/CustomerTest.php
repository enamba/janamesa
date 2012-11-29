<?php

/**
 * @author MAtthias Laug <laug@lieferando.de>
 */

/**
 * @runTestsInSeparateProcesses 
 */
class GetPartnerCustomerTest extends Yourdelivery_Test {

    /**
     * test login with invalid parameters and expect a 406
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     */
    public function testLoginInvalidParameters() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_partner_customer');
        $this->assertResponseCode(406);
    }

    /**
     * test login with invalid customerNr expecting 404
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.08.2012
     */
    public function testLoginWithNotExistingCustomerNrFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'nr' => 'this-is-my-not-existing-customerNr',
            'pass' => 'testen'
        ));
        $this->dispatch('/get_partner_customer');
        $this->assertAction('post');
        $this->assertResponseCode(403);
    }

    /**
     * test login with valid parameters and expect a 201
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     */
    public function testLoginSuccess() {
        $request = $this->getRequest();
        $request->setMethod('POST');

        $service = $this->getRandomService();
        $service->setPassword(md5('testen'));
        $service->save();

        //clear password if available
        if ($service->getPartnerData() !== null) {
            $service->getPartnerData()->setTemporaryPassword('');
            $service->getPartnerData()->save();
        }

        $request->setPost(array(
            'nr' => $service->getCustomerNr(),
            'pass' => 'testen'
        ));

        $this->dispatch('/get_partner_customer');
        $this->assertResponseCode(201);
    }

    /**
     * test login with valid parameters and expect a 201
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     */
    public function testLoginWithIncorrectPassword() {
        $service = $this->getRandomService();
        $request = $this->getRequest();

        $request->setMethod('POST');
        $request->setPost(array(
            'nr' => $service->getCustomerNr(),
            'pass' => 'this-is-my-not-existing-password'
        ));

        $this->dispatch('/get_partner_customer');
        $this->assertAction('post');
        $this->assertResponseCode(403);

        $data = $this->getResponse()->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($data);

        $message = $doc->getElementsByTagName("message");
        $this->assertEquals(__p('Das Passwort ist falsch!'), $message->item(0)->nodeValue);
    }

    /**
     * test login with valid parameters but temporary password set
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     */
    public function testLoginFailsDueToTemporaryPassword() {
        $request = $this->getRequest();
        $request->setMethod('POST');

        $service = $this->getRandomService();
        $service->setPassword(md5('testen'));
        $service->save();

        //clear password if available
        if ($service->getPartnerData() !== null) {
            $service->getPartnerData()->setTemporarypassword('asdad');
            $service->getPartnerData()->save();
        }

        $request->setPost(array(
            'nr' => $service->getCustomerNr(),
            'pass' => 'testen'
        ));

        $this->dispatch('/get_partner_customer');
        $this->assertResponseCode(403);
    }

    /**
     * test login with valid parameters and expect a 201
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     */
    public function testLoginSuccessAndGetOrdersOfService() {
        $request = $this->getRequest();
        $request->setMethod('POST');

        $service = $this->getRandomService();
        $service->setPassword(md5('testen'));
        $service->save();

        $countOrders = $service->getOrders()->count();

        $order = new Yourdelivery_Model_Order($this->placeOrder(array('service' => $service)));
        $order->setStatus(Yourdelivery_Model_Order::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'testcase API Partner testLoginSuccessAndGetOrdersOfService'));
        // should be affirmed and ready for picking by driver

        $serviceNew = new Yourdelivery_Model_Servicetype_Restaurant($service->getId());
        $countOrdersNew = $serviceNew->getOrders()->count();

        $this->assertEquals($countOrders + 1, $countOrdersNew);


        //clear password if available
        if ($service->getPartnerData() !== null) {
            $service->getPartnerData()->setTemporaryPassword('');
            $service->getPartnerData()->save();
        }

        $request->setPost(array(
            'nr' => $service->getCustomerNr(),
            'pass' => 'testen'
        ));

        $this->dispatch('/get_partner_customer');
        $this->assertResponseCode(201);

        $data = $this->getResponse()->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($data);

        $success = $doc->getElementsByTagName("success");
        $this->assertEquals('true', $success->item(0)->nodeValue);

        $access = $doc->getElementsByTagName("access")->item(0)->nodeValue;
        $this->assertEquals($service->getSalt(), $access);

        // now try to get orders from this service by using this access
        $this->resetRequest();
        $this->resetResponse();

        $this->dispatch('/get_partner_order?access=' . $access);
        $this->assertAction('index');
        $this->assertResponseCode(200);

        $data = $this->getResponse()->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($data);
        $this->assertGreaterThanOrEqual(1, $doc->getElementsByTagName("order")->length);

        // place another order and check count again
        $order2 = new Yourdelivery_Model_Order($this->placeOrder(array('service' => $service)));
        $order2->setStatus(Yourdelivery_Model_Order::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, 'testcase API Partner testLoginSuccessAndGetOrdersOfService'));
        // should be affirmed and ready for picking by driver
        // now try to get orders from this service by using this access
        $this->resetRequest();
        $this->resetResponse();

        $this->dispatch('/get_partner_order?access=' . $access);
        $this->assertAction('index');
        $this->assertResponseCode(200);

        $data = $this->getResponse()->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($data);
        $this->assertGreaterThanOrEqual(2, $doc->getElementsByTagName("order")->length);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.08.2012 
     */
    public function testPutFail() {
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $request->setPost(array(
            'foo' => 'bar'
        ));

        $this->dispatch('/get_partner_customer');
        $this->assertAction('put');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.08.2012 
     */
    public function testGetFail() {
        $request = $this->getRequest();
        $this->dispatch('/get_partner_customer/foo');
        $this->assertAction('get');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.08.2012 
     */
    public function testIndexFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_partner_customer');
        $this->assertAction('index');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 31.08.2012 
     */
    public function testDeleteFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setHeader("X-HTTP-Method-Override", "DELETE");
        $request->setPost(array(
            'foo' => 'bar'
        ));
        $this->dispatch('/get_partner_customer');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }

}
