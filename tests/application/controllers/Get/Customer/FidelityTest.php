<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 22.11.2011
 */
/**
 * @runTestsInSeparateProcesses 
 */
class CustomerStatsFidelityTest extends Yourdelivery_Test {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 28.11.2011
     */
    public function testCustomerFidelityPostEmptyParams() {

        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = '{
                "type":"transactions",
                "access":""
            }';

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));

        $this->dispatch('/get_customer_fidelity/');
        $this->assertResponseCode(403);

        $response = $this->getResponse();
        $data = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($data);
        $success = $doc->getElementsByTagName("success");
        $this->assertEquals('false', $success->item(0)->nodeValue);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 24.05.2012
     */
    public function testCustomerFidelityPostEmptyParamsJSON() {

        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = '{
                "type":"transactions",
                "access":""
            }';

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));

        $this->dispatch('/get_customer_fidelity?format=json');
        $this->assertResponseCode(403);

        $response = $this->getResponse();
        $data = $response->getBody();

        $this->assertIsArray(json_decode($data, true));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function testPostWithWrongTypeFail() {
        $customer = $this->getRandomCustomer();
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = '{
                "type":"foobar",
                "access":"' . $customer->getSalt() . '"
            }';

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));

        $this->dispatch('/get_customer_fidelity/');
        $this->assertController('get_customer_fidelity');
        $this->assertAction('post');
        $this->assertResponseCode(404);
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 24.05.2012
     */
    public function testPostWithWrongTypeFailJSON() {
        $customer = $this->getRandomCustomer();
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = '{
                "type":"foobar",
                "access":"' . $customer->getSalt() . '"
            }';

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));

        $this->dispatch('/get_customer_fidelity?format=json');
        $this->assertController('get_customer_fidelity');
        $this->assertAction('post');
        $this->assertResponseCode(404);
        
        $this->assertIsArray(json_decode($this->getResponse()->getBody(), true));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function testPostWithInvalidJson() {
        $customer = $this->getRandomCustomer();
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = '{
                "type":"openactions",
                "access":' . $customer->getSalt() . '"
            }';

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));

        $this->dispatch('/get_customer_fidelity/');
        $this->assertController('get_customer_fidelity');
        $this->assertAction('post');
        $this->assertResponseCode(405);
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 24.05.2012
     */
    public function testPostWithInvalidJsonJSON() {
        $customer = $this->getRandomCustomer();
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = '{
                "type":"openactions",
                "access":' . $customer->getSalt() . '"
            }';

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));

        $this->dispatch('/get_customer_fidelity?format=json');
        $this->assertController('get_customer_fidelity');
        $this->assertAction('post');
        $this->assertResponseCode(405);
        
        $this->assertIsArray(json_decode($this->getResponse()->getBody(), true));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.11.2011
     */
    public function testCustomerFidelityPost() {

        // new customer should not have any orders
        $customer = $this->createCustomer();
        $access = $customer->getSalt();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = '{
                "type":"transactions",
                "access":"' . $access . '"
            }';

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));

        $this->dispatch('/get_customer_fidelity/');
        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $customerId = $doc->getElementsByTagName("customerid");
        $this->assertEquals($customer->getId(), $customerId->item(0)->nodeValue);
        $this->assertEquals(0, $doc->getElementsByTagName("openactions")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("transactions")->length);
        $this->assertEquals(0, $doc->getElementsByTagName("transaction")->length);

        $this->resetRequest();
        $this->resetResponse();

        $customer = $this->createCustomer();
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('customer' => $customer)));
        $order->setState(Yourdelivery_Model_Order::AFFIRMED);
        $order->save();

        $access = $customer->getSalt();
        $countOpenActions = count($customer->getFidelity()->getOpenActions());

        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = '{
                "type":"openactions",
                "access":"' . $access . '"
            }';

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));

        $this->dispatch('/get_customer_fidelity/');
        $response = $this->getResponse();
        $data = $response->getBody();

        $doc = null;
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $customerId = $doc->getElementsByTagName("customerid");
        $this->assertEquals($customer->getId(), $customerId->item(0)->nodeValue);
        $this->assertEquals(0, $doc->getElementsByTagName("transactions")->length);
        $this->assertEquals(1, $doc->getElementsByTagName("openactions")->length);
        $this->assertEquals($countOpenActions, $doc->getElementsByTagName("openaction")->length);

        $this->resetRequest();
        $this->resetResponse();

        // create some fidelity transactions for customer
        $customer->addFidelityPoint('testcase', 'testdata', 15);

        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = '{
                "type":"transactions",
                "access":"' . $access . '"
            }';

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));

        $this->dispatch('/get_customer_fidelity/');
        $response = $this->getResponse();
        $data = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($data);

        $customerId = $doc->getElementsByTagName("customerid");
        $this->assertEquals($customer->getId(), $customerId->item(0)->nodeValue);
        $this->assertEquals(1, $doc->getElementsByTagName("transactions")->length);

        // there should be 2 transactions (testdata, order)
        $this->assertEquals(2, $doc->getElementsByTagName("transaction")->length);
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 24.05.2012
     */
    public function testCustomerFidelityPostJSON() {

        // new customer should not have any orders
        $customer = $this->createCustomer();
        $access = $customer->getSalt();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = '{
                "type":"transactions",
                "access":"' . $access . '"
            }';

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));

        $this->dispatch('/get_customer_fidelity?format=json');
        $response = $this->getResponse();
        $data = $response->getBody();
        
        $jsonArray = json_decode($data, true);
        $this->assertIsArray($jsonArray);

        $this->assertEquals($customer->getId(), $jsonArray['customer']['customerid']);
        $this->assertEquals(0, count($jsonArray['customer']['openactions']));
        $this->assertArrayHasKey('transactions', $jsonArray['customer']);
        $this->assertEquals(0, count($jsonArray['customer']['transaction']));

        $this->resetRequest();
        $this->resetResponse();

        $customer = $this->createCustomer();
        $order = new Yourdelivery_Model_Order($this->placeOrder(array('customer' => $customer)));
        $order->setState(Yourdelivery_Model_Order::AFFIRMED);
        $order->save();

        $access = $customer->getSalt();
        $countOpenActions = count($customer->getFidelity()->getOpenActions());

        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = '{
                "type":"openactions",
                "access":"' . $access . '"
            }';

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));

        $this->dispatch('/get_customer_fidelity?format=json');
        $response = $this->getResponse();
        $data = $response->getBody();
        
        $jsonArray = json_decode($data, true);
        $this->assertIsArray($jsonArray);
#var_dump($jsonArray);die;
        $this->assertEquals($customer->getId(), $jsonArray['customer']['customerid']);
        $this->assertEquals(1, count($jsonArray['customer']['openactions']));
        $this->assertArrayHasKey('openactions', $jsonArray['customer']);
        
        $this->assertEquals($countOpenActions, count($jsonArray['customer']['openactions']));

        $this->resetRequest();
        $this->resetResponse();

        // create some fidelity transactions for customer
        $customer->addFidelityPoint('testcase', 'testdata', 15);

        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = '{
                "type":"transactions",
                "access":"' . $access . '"
            }';

        $request->setParam('parameters', $params);
        $request->setPost(array('parameters' => $params));

        $this->dispatch('/get_customer_fidelity?format=json');
        $response = $this->getResponse();
        $data = $response->getBody();
        
         $jsonArray = json_decode($data, true);
        $this->assertIsArray($jsonArray);
        
        $this->assertEquals($customer->getId(), $jsonArray['customer']['customerid']);
        $this->assertArrayHasKey('transactions', $jsonArray['customer']);

        // there should be 2 transactions (testdata, order)
        $this->assertEquals(2, count($jsonArray['customer']['transactions']['transaction']));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testDeleteFail() {
        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $this->dispatch('/get_customer_fidelity/some-stuff');
        $this->assertController('get_customer_fidelity');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 24.05.2012
     */
    public function testDeleteFailJSON() {
        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $this->dispatch('/get_customer_fidelity/some-stuff?format=json');
        $this->assertController('get_customer_fidelity');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
        
        $this->assertIsArray(json_decode($this->getResponse()->getBody(), true));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testPutFail() {
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $this->dispatch('/get_customer_fidelity');
        $this->assertController('get_customer_fidelity');
        $this->assertAction('put');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 24.05.2012
     */
    public function testPutFailJSON() {
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $this->dispatch('/get_customer_fidelity?format=json');
        $this->assertController('get_customer_fidelity');
        $this->assertAction('put');
        $this->assertResponseCode(403);

        $this->assertIsArray(json_decode($this->getResponse()->getBody(), true));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testGetFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_customer_fidelity/khgjhg');
        $this->assertController('get_customer_fidelity');
        $this->assertAction('get');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testGetFailJSON() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_customer_fidelity/khgjhg?format=json');
        $this->assertController('get_customer_fidelity');
        $this->assertAction('get');
        $this->assertResponseCode(403);

        $this->assertIsArray(json_decode($this->getResponse()->getBody(), true));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.01.2012
     */
    public function testIndexFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_customer_fidelity?bla=blub');
        $this->assertController('get_customer_fidelity');
        $this->assertAction('index');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 24.05.2012
     */
    public function testIndexFailJSON() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_customer_fidelity?bla=blub&format=json');
        $this->assertController('get_customer_fidelity');
        $this->assertAction('index');
        $this->assertResponseCode(403);

        $this->assertIsArray(json_decode($this->getResponse()->getBody(), true));
    }

}
