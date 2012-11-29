<?php

/**
 * @author Felix Haferkorn
 * @since 05.07.2012
 */

/**
 * @runTestsInSeparateProcesses 
 */
class BestServiceApiTest extends Yourdelivery_Test {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.07.2012
     * 
     * @dataProvider dataProviderCacheNoCache
     */
    public function testGetSuccess($cache) {
        $this->setUsingCache($cache);

        $request = $this->getRequest();
        $request->setMethod('GET');
        $location = $this->getRandomLocation();
        $this->dispatch('/get_best_service/' . $location->getCity()->getId() . '?limit=100');
        $this->assertController('get_best_service');
        $this->assertAction('get');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $data = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($data);

        $this->assertEquals(1, $doc->getElementsByTagName("services")->length);
        $this->assertEquals(count($location->getBestServices(100, true)), $doc->getElementsByTagName("service")->length);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.07.2012
     */
    public function testIndexFail() {
        $request = $this->getRequest();
        $this->dispatch('/get_best_service');
        $this->assertController('get_best_service');
        $this->assertAction('index');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.07.2012
     */
    public function testPostFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_best_service');
        $this->assertController('get_best_service');
        $this->assertAction('post');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.07.2012
     */
    public function testPutFail() {
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $this->dispatch('/get_best_service');
        $this->assertController('get_best_service');
        $this->assertAction('put');
        $this->assertResponseCode(403);
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.07.2012
     */
    public function testDeleteFail() {
        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $this->dispatch('/get_best_service');
        $this->assertController('get_best_service');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }

}
