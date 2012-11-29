<?php

/**
 * @author mlaug
 */
/**
 * @runTestsInSeparateProcesses
 */
class ServiceApiTest extends Yourdelivery_Test{

    /**
     * @author mlaug
     * @since 03.11.2010
     */
    public function testGetByServiceId(){


        $service = $this->getRandomService();

        $this->dispatch('/get_service/' . $service->getId());
        $response = $this->getResponse();
        $xml = $response->getBody();

        $this->assertController('get_service');
        $this->assertAction('get');

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->assertEquals('true',$doc->getElementsByTagName("success")->item(0)->nodeValue);
        $this->assertGreaterThan(0,$doc->getElementsByTagName("service")->length);

        $serviceNode = $doc->getElementsByTagName("service")->item(0);
        $this->assertGreaterThan(0,$serviceNode->getElementsByTagName('id')->length);
        $this->assertGreaterThan(0,$serviceNode->getElementsByTagName('plz')->length);
        $this->assertEquals($serviceNode->getElementsByTagName('telephon')->length,1);
        $this->assertEquals($serviceNode->getElementsByTagName('fax')->length,1);
        $this->assertEquals($serviceNode->getElementsByTagName('link')->length,1);
        $this->assertEquals($serviceNode->getElementsByTagName('street')->length,1);
        $this->assertGreaterThanOrEqual(1, $serviceNode->getElementsByTagName('category')->length);
        $this->assertEquals($serviceNode->getElementsByTagName('premium')->length,1);
        $this->assertEquals($serviceNode->getElementsByTagName('open')->length,1);
        $this->assertEquals($serviceNode->getElementsByTagName('openings')->length,1);
        $this->assertEquals($serviceNode->getElementsByTagName('ratings')->length,1);
        $this->assertEquals($serviceNode->getElementsByTagName('menu')->length,1);
        $this->assertGreaterThan(1,$serviceNode->getElementsByTagName('meals')->length);
        $this->assertNotNull($serviceNode->getElementsByTagName('hasspecials')->item(0)->nodeValue);
        $this->assertEquals($serviceNode->getElementsByTagName('id')->item(0)->nodeValue,$service->getId());
        $this->assertEquals($serviceNode->getElementsByTagName('name')->item(0)->nodeValue,$service->getName());
        $this->assertEquals($serviceNode->getElementsByTagName('plz')->item(0)->nodeValue,$service->getPlz());
        $this->assertGreaterThan(1,$serviceNode->getElementsByTagName('noDeliverCostAbove')->length);

        $info = $serviceNode->getElementsByTagName('info')->item(0)->nodeValue;
        $this->assertFalse(strpos($info, '&nbsp;'), sprintf('found html-tag "&nbsp;" in info for restaurant - info is "%s"', $info));
    }

    /**
     * @author mlaug
     * @since 03.11.2010
     */
    public function testIndexByCityId(){


        $this->dispatch('/get_service?cityId=' . $this->getRandomCityId());
        $response = $this->getResponse();
        $xml = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->assertEquals('true',$doc->getElementsByTagName("success")->item(0)->nodeValue);
        $this->assertGreaterThan(0,$doc->getElementsByTagName("service")->length);

        $service = $doc->getElementsByTagName("service")->item(0);
        $this->assertEquals($service->getElementsByTagName('id')->length,1);
        $this->assertEquals($service->getElementsByTagName('name')->length,1);
        $this->assertEquals($service->getElementsByTagName('picture')->length,1);
        $this->assertGreaterThan(0,$service->getElementsByTagName('plz')->length);
        $this->assertEquals($service->getElementsByTagName('telephon')->length,1);
        $this->assertEquals($service->getElementsByTagName('fax')->length,1);
        $this->assertEquals($service->getElementsByTagName('link')->length,1);
        $this->assertEquals($service->getElementsByTagName('street')->length,1);
        $this->assertEquals($service->getElementsByTagName('category')->length,1);
        $this->assertEquals($service->getElementsByTagName('premium')->length,1);
        $this->assertEquals($service->getElementsByTagName('open')->length,1);
        $this->assertEquals($service->getElementsByTagName('openings')->length,1);
        $this->assertEquals($service->getElementsByTagName('ratings')->length,1);

    }

    /**
     * @author mlaug
     * @since 03.11.2010
     */
    public function testIndexByPlz(){


        $plz = $this->getRandomPlz();
        $this->dispatch('/get_service?plz=' . $plz['plz']);
        $response = $this->getResponse();
        $xml = $response->getBody();
        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->assertEquals('true',$doc->getElementsByTagName("success")->item(0)->nodeValue);
        $this->assertGreaterThan(0,$doc->getElementsByTagName("service")->length);

        $service = $doc->getElementsByTagName("service")->item(0);
        $this->assertEquals($service->getElementsByTagName('id')->length,1);
        $this->assertEquals($service->getElementsByTagName('name')->length,1);
        $this->assertEquals($service->getElementsByTagName('picture')->length,1);
        $this->assertGreaterThan(0,$service->getElementsByTagName('plz')->length);
        $this->assertEquals($service->getElementsByTagName('telephon')->length,1);
        $this->assertEquals($service->getElementsByTagName('fax')->length,1);
        $this->assertEquals($service->getElementsByTagName('link')->length,1);
        $this->assertEquals($service->getElementsByTagName('street')->length,1);
        $this->assertEquals($service->getElementsByTagName('category')->length,1);
        $this->assertEquals($service->getElementsByTagName('premium')->length,1);
        $this->assertEquals($service->getElementsByTagName('open')->length,1);
        $this->assertEquals($service->getElementsByTagName('openings')->length,1);
        $this->assertEquals($service->getElementsByTagName('ratings')->length,1);

    }


    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.02.2012
     */
    public function testGetWithoutServiceIdFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_service/-1');
        $this->assertController('get_service');
        $this->assertAction('get');
        $this->assertResponseCode(404);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.02.2012
     */
    public function testGetWithNotExistingServiceIdFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_service/'.Default_Helper::generateRandomString());
        $this->assertController('get_service');
        $this->assertAction('get');
        $this->assertResponseCode(404);
    }


    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPostFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_service');
        $this->assertController('get_service');
        $this->assertAction('post');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPutFail() {
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $this->dispatch('/get_service');
        $this->assertController('get_service');
        $this->assertAction('put');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testDeleteFail() {
        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $this->dispatch('/get_service');
        $this->assertController('get_service');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }

}
