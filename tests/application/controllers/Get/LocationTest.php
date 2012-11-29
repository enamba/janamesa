<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 29.09.2011
 */

/**
 * @runTestsInSeparateProcesses
 */
class LocationApiTest extends Yourdelivery_Test {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 29.09.2011
     */
    public function testDeleteLocation() {
        $customer = $this->getRandomCustomer();
        $location = $this->getRandomLocation($customer->getId());

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setHeader("X-HTTP-Method-Override", "DELETE");
        $request->setPost(array(
            'parameters' => json_encode(array(
                'access' => $customer->getSalt()
            ))
        ));
        $this->dispatch('/get_location?id=' . $location->getId());

        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->assertResponseCode(200);
        $this->assertEquals('true', $doc->getElementsByTagName("success")->item(0)->nodeValue, Default_Helpers_Log::getLastLog());

        $locationCheck = new Yourdelivery_Model_Location($location->getId());
        $this->assertTrue($locationCheck->isPersistent());
        $this->assertTrue($locationCheck->isDeleted());

        $this->resetRequest();
        $this->resetResponse();

        // test fail delete location
        $customer = $this->getRandomCustomer();

        // get location which doesn't belong to customer
        do {
            $location = $this->getRandomLocation();
        } while ($i++ <= MAX_LOOPS && $location->getCustomerId() == $customer->getId());

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setHeader("X-HTTP-Method-Override", "DELETE");
        $request->setPost(array(
            'parameters' => json_encode(array(
                'access' => $customer->getSalt()
            ))
        ));
        $this->dispatch('/get_location?id=' . $location->getId());

        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->assertResponseCode(403);
        $this->assertEquals('false', $doc->getElementsByTagName("success")->item(0)->nodeValue, Default_Helpers_Log::getLastLog());

        $locationCheck = new Yourdelivery_Model_Location($location->getId());
        $this->assertTrue($locationCheck->isPersistent());
        $this->assertFalse($locationCheck->isDeleted());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 29.09.2011
     */
    public function testDeleteLocationWithGetParamFallback() {
        $customer = $this->getRandomCustomer();
        $location = $this->getRandomLocation($customer->getId());

        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $this->dispatch(sprintf('/get_location/%s?access=%s', $location->getId(), $customer->getSalt()));
        $this->assertController('get_location');
        $this->assertAction('delete');

        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->assertResponseCode(200, $response->getBody());
        $this->assertEquals('true', $doc->getElementsByTagName("success")->item(0)->nodeValue, Default_Helpers_Log::getLastLog());

        $locationCheck = new Yourdelivery_Model_Location($location->getId());
        $this->assertTrue($locationCheck->isPersistent());
        $this->assertTrue($locationCheck->isDeleted());
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testDeleteLocationWithoutParams() {
        $customer = $this->getRandomCustomer();
        $location = $this->getRandomLocation($customer->getId());

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setHeader("X-HTTP-Method-Override", "DELETE");

        $this->dispatch('/get_location?id=' . $location->getId());

        $this->assertResponseCode(406);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testDeleteLocationWithInvalidId() {
        $customer = $this->getRandomCustomer();
        $location = $this->getRandomLocation($customer->getId());

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setHeader("X-HTTP-Method-Override", "DELETE");
        $request->setPost(array(
            'parameters' => json_encode(array(
                'access' => $customer->getSalt()
            ))
        ));
        $this->dispatch('/get_location');
        $this->assertResponseCode(404);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testDeleteLocationWithNonExistingId() {
        $customer = $this->getRandomCustomer();
        $location = $this->getRandomLocation($customer->getId());

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setHeader("X-HTTP-Method-Override", "DELETE");
        $request->setPost(array(
            'parameters' => json_encode(array(
                'access' => $customer->getSalt()
            ))
        ));
        $this->dispatch('/get_location/999999999');
        $this->assertResponseCode(403);
    }

    /**
     * testcase for updating a location
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.12.2011
     */
    public function testPutLocation() {
        $customer = $this->getRandomCustomer(null, true);
        $location = $this->getRandomLocation($customer->getId());
        $city = new Yourdelivery_Model_City($this->getRandomCityId());
        $locationId = $location->getId();

        $this->assertTrue($location->isPersistent());
        $this->assertGreaterThan(0, strlen($location->getStreet()));
        $this->assertNotEquals("mein neue Straße", $location->getStreet());

        $street = "mein neue Straße";
        $hausnr = rand(1, 999);
        $plz = $city->getPlz();
        $cityId = $city->getId();
        $tel = rand(10, 9999999999);
        $company = "this is my world";
        $etage = "sky";
        $comment = "this is my comment to you: #'*+-.,:;'`´?=)(/&%§@€üäö ÜÄÖ ß";

        $parameters = sprintf('{
            "access":"%s",
            "street":"%s",
            "hausnr":"%s",
            "plz":"%s",
            "cityId":"%s",
            "tel":"%s",
            "company":"%s",
            "etage":"%s" ,
            "comment":"%s"}', $customer->getSalt(), $street, $hausnr, $plz, $cityId, $tel, $company, $etage, $comment);

        $request = $this->getRequest();
        $request->setMethod('PUT');
        $request->setParam('parameters', $parameters);
        $this->dispatch('/get_location/' . $location->getId());

        $this->assertController('get_location');
        $this->assertAction('put');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->assertEquals('true', $doc->getElementsByTagName("success")->item(0)->nodeValue);

        $location = null;
        $location = new Yourdelivery_Model_Location($locationId);
        $this->assertTrue($location->isPersistent());

        $this->assertEquals($street, $location->getStreet());
        $this->assertEquals($hausnr, $location->getHausnr());
        $this->assertEquals($plz, $location->getPlz());
        $this->assertEquals($cityId, $location->getCityId());
        $this->assertEquals($tel, $location->getTel());
        $this->assertEquals($company, $location->getCompanyName());
        $this->assertEquals($etage, $location->getEtage());
        $this->assertEquals($comment, $location->getComment());

        // wrong / invalid params
        $parameters = sprintf('{
            "access":"%s",
            "street":"",
            "hausnr":"%s",
            "plz":"%s",
            "cityId":"%s",
            "tel":"%s",
            "company":"%s",
            "etage":"%s" ,
            "comment":"%s"}', $customer->getSalt(), $hausnr, $plz, $cityId, $tel, $company, $etage, $comment);

        $this->resetRequest();
        $this->resetResponse();

        $request = $this->getRequest();
        $request->setMethod('PUT');
        $request->setParam('parameters', $parameters);
        $this->dispatch('/get_location/' . $location->getId());

        $this->assertController('get_location');
        $this->assertAction('put');
        $this->assertResponseCode(406);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPutLocationWithoutParams() {
        $customer = $this->getRandomCustomer();
        $location = $this->getRandomLocation($customer->getId());

        $request = $this->getRequest();
        $request->setMethod('PUT');
        $this->dispatch('/get_location/' . $location->getId());

        $this->assertController('get_location');
        $this->assertAction('put');
        $this->assertResponseCode(406);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPutLocationWithoutIdFail() {
        $cust = $this->getRandomCustomer();
        $request = $this->getRequest();
        $range = $this->getRandomPlz();
        $request->setMethod('PUT');
        $params = array(
            'access' => $cust->getSalt(),
            "street" => "mein neue Straße",
            "hausnr" => rand(1, 999),
            "plz" => $range['plz'],
            "cityId" => $range['cityId'],
            "tel" => rand(1000, 9999999999),
            "company" => "this is my world",
            "etage" => "sky",
            "comment" => "this is my comment to you: #'*+-.,:;'`´?=)(/&%§@€üäö ÜÄÖ ß",
        );
        $request->setPost(array('parameters' => json_encode($params)));
        $this->dispatch('/get_location/');

        $this->assertController('get_location');
        $this->assertAction('put');
        $this->assertResponseCode(404);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPutLocationWithoutNonExistingIdFail() {
        $cust = $this->getRandomCustomer();
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $range = $this->getRandomPlz();
        $params = array(
            'access' => $cust->getSalt(),
            "street" => "mein neue Straße",
            "hausnr" => rand(1, 999),
            "plz" => $range['plz'],
            "cityId" => $range['cityId'],
            "tel" => rand(1000, 9999999999),
            "company" => "this is my world",
            "etage" => "sky",
            "comment" => "this is my comment to you: #'*+-.,:;'`´?=)(/&%§@€üäö ÜÄÖ ß",
        );
        $request->setPost(array('parameters' => json_encode($params)));
        $this->dispatch('/get_location/999999999');

        $this->assertController('get_location');
        $this->assertAction('put');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPutLocationWithoutInvalidJsonFail() {
        $cust = $this->getRandomCustomer();
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $invalidJson = '{"access":"}';

        $request->setPost(array('parameters' => $invalidJson));
        $this->dispatch('/get_location/999999999');

        $this->assertController('get_location');
        $this->assertAction('put');
        $this->assertResponseCode(406);
    }

    /**
     * A collection of not acceptable primary values
     *
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 07.08.2012
     *
     * @return array of invalid primary values
     */
    public static function invalidPrimaryProvider() {
        return array(
            array('abcde'),
            array(123),
            array(-22),
        );
    }

    /**
     * Tests, if the location form validator works properly
     *
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 07.08.2012
     *
     * @param mixed $invalidPrimary Invalid values for primary
     * @dataProvider invalidPrimaryProvider
     */
    public function testPutLocationWithInvalidPrimaryFail($invalidPrimary) {
        $request = $this->getRequest();
        $request->setMethod('PUT');

        $customer = $this->getRandomCustomer();
        $location = $this->getRandomLocation($customerId = $customer->getId());

        $params = array(
            'primary' => $invalidPrimary,
            'access' => $customer->getSalt(),
            'street' => $location->getStreet(),
            'hausnr' => $location->getHausnr(),
            'plz' => $location->getPlz(),
            'cityId' => $location->getCity()->getId()
        );

        $json = json_encode($params);
        $request->setPost(array('parameters' => $json));
        $this->dispatch(sprintf('/get_location/%s', $location->getId()));

        $this->assertController('get_location');
        $this->assertAction('put');
        $this->assertResponseCode(406);
    }

    /**
     * testcase for creating a new location
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.12.2011
     */
    public function testPostLocation() {
        $customer = $this->getRandomCustomer();
        $access = $customer->getSalt();

        $city = new Yourdelivery_Model_City($this->getRandomCityId());
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = array(
            'access' => $customer->getSalt(),
            "street" => $street = "mein neue Straße",
            "hausnr" => $hausnr = rand(1, 999),
            "plz" => $plz = $city->getPlz(),
            "cityId" => $cityId = $city->getId(),
            "tel" => $tel = rand(10, 9999999999),
            "company" => $company = "this is my world",
            "etage" => $etage = "sky",
            "comment" => $comment = "this is my comment to you: #'*+-.,:;'`´?=)(/&%§@€üäö ÜÄÖ ß",
        );
        $request->setPost(array('parameters' => json_encode($params)));
        $this->dispatch('/get_location');
        $this->assertResponseCode(201);

        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->assertEquals('true', $doc->getElementsByTagName("success")->item(0)->nodeValue);
        $id = $doc->getElementsByTagName("id")->item(0)->nodeValue;

        $location = new Yourdelivery_Model_Location($id);
        $this->assertTrue($location->isPersistent());
        $this->assertEquals($street, $location->getStreet());
        $this->assertEquals($hausnr, $location->getHausnr());
        $this->assertEquals($plz, $location->getPlz());
        $this->assertEquals($cityId, $location->getCityId());
        $this->assertEquals($tel, $location->getTel());
        $this->assertEquals($company, $location->getCompanyName());
        $this->assertEquals($etage, $location->getEtage());
        $this->assertEquals($comment, $location->getComment());
    }

    public function testPostLocationWithoutTelefon() {
        $customer = $this->getRandomCustomer();
        $access = $customer->getSalt();

        $city = new Yourdelivery_Model_City($this->getRandomCityId());
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = array(
            'access' => $customer->getSalt(),
            "street" => $street = "mein neue Straße",
            "hausnr" => $hausnr = rand(1, 999),
            "plz" => $plz = $city->getPlz(),
            "cityId" => $cityId = $city->getId(),
            "company" => $company = "this is my world",
            "etage" => $etage = "sky",
            "comment" => $comment = "this is my comment to you: #'*+-.,:;'`´?=)(/&%§@€üäö ÜÄÖ ß",
        );
        $request->setPost(array('parameters' => json_encode($params)));
        $this->dispatch('/get_location');
        $this->assertResponseCode(201);

        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->assertEquals('true', $doc->getElementsByTagName("success")->item(0)->nodeValue);
        $id = $doc->getElementsByTagName("id")->item(0)->nodeValue;

        $location = new Yourdelivery_Model_Location($id);
        $this->assertTrue($location->isPersistent());
        $this->assertEquals($street, $location->getStreet());
        $this->assertEquals($hausnr, $location->getHausnr());
        $this->assertEquals($plz, $location->getPlz());
        $this->assertEquals($cityId, $location->getCityId());
        $this->assertEquals(null, $location->getTel());
        $this->assertEquals($company, $location->getCompanyName());
        $this->assertEquals($etage, $location->getEtage());
        $this->assertEquals($comment, $location->getComment());
    }

    public function testPostLoactionWithoutParamsFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_location');
        $this->assertController('get_location');
        $this->assertAction('post');
        $this->assertResponseCode(406);
    }

    public function testPostLocationWithInvalidJson() {
        $customer = $this->getRandomCustomer();
        $city = new Yourdelivery_Model_City($this->getRandomCityId());
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = array(
            'access' => $customer->getSalt(),
            "street" => "my street",
            "hausnr" => $hausnr = rand(1, 999),
            "plz" => $plz = $city->getPlz(),
            "cityId" => $cityId = $city->getId(),
            "tel" => $tel = rand(10, 9999999999),
            "company" => $company = "this is my world",
            "etage" => $etage = "sky",
            "comment" => "this is my comment to you: #'*+-.,:;'`´?=)(/&%§@€üäö ÜÄÖ ß",
        );
        $request->setPost(array('parameters' => $params));
        $this->dispatch('/get_location');
        $this->assertResponseCode(406);

        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->assertEquals('false', $doc->getElementsByTagName("success")->item(0)->nodeValue);
    }

    public function testPostLocationWithInvalidData() {
        $customer = $this->getRandomCustomer();

        $city = new Yourdelivery_Model_City($this->getRandomCityId());
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = array(
            'access' => $customer->getSalt(),
            "street" => "",
            "hausnr" => rand(1, 999),
            "plz" => $city->getPlz(),
            "cityId" => $city->getId(),
            "tel" => rand(1234567, 87654321),
            "company" => "this is my world",
            "etage" => "sky",
            "comment" => "this is my comment to you: #'*+-.,:;'`´?=)(/&%§@€üäö ÜÄÖ ß",
        );
        $request->setPost(array('parameters' => json_encode($params)));
        $this->dispatch('/get_location');
        $this->assertResponseCode(406);
    }

    public function testPostLocationWithInvalidAccess() {

        $city = new Yourdelivery_Model_City($this->getRandomCityId());
        $request = $this->getRequest();
        $request->setMethod('POST');
        $params = array(
            'access' => "invalid-access",
            "street" => "",
            "hausnr" => $hausnr = rand(1, 999),
            "plz" => $plz = $city->getPlz(),
            "cityId" => $cityId = $city->getId(),
            "tel" => $tel = rand(10, 9999999999),
            "company" => $company = "this is my world",
            "etage" => $etage = "sky",
            "comment" => $comment = "this is my comment to you: #'*+-.,:;'`´?=)(/&%§@€üäö ÜÄÖ ß",
        );
        $request->setPost(array('parameters' => json_encode($params)));
        $this->dispatch('/get_location');
        $this->assertResponseCode(403);

        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->assertEquals('false', $doc->getElementsByTagName("success")->item(0)->nodeValue);
    }

    /**
     * test for disallowing get_location - getAction()
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.12.2011
     */
    public function testGetLocation() {
        $this->dispatch('/get_location/foobar');
        $this->assertController('get_location');
        $this->assertAction('get');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 07.01.2012
     */
    public function testIndexSuccess() {
        // get Random order with uuid
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $row = $db->fetchRow('SELECT uuid FROM orders where uuid IS NOT NULL AND state > 0 ORDER BY RAND() LIMIT 1');
        $uuid = $row['uuid'];

        $request = $this->getRequest();

        $this->dispatch('get_location?uuid=' . $uuid);
        $this->assertController('get_location');
        $this->assertAction('index');
        $this->assertResponseCode(200);
    }

}
