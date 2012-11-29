<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class TagesspiegelFrameworkControllerTest extends Yourdelivery_Test {

    /**
     * @author mlaug
     * @since 02.03.2011
     */
    public function testStart() {
        if ($this->config->domain->base != 'lieferando.de') {
            $this->markTestSkipped("Test should only run in lieferando.de");
        }

        $request = $this->getRequest();
        //call the start page
        $this->dispatch('/if/tagesspiegel/2/start/');
        $this->assertController('iframe_framework');
        $this->assertAction('start');
    }

    public function testServiceWithPLZ() {
        if ($this->config->domain->base != 'lieferando.de') {
            $this->markTestSkipped("Test should only run in lieferando.de");
        }

        //provide just a plz, should work, if he finds a cityId
        $request = $this->getRequest();
        $plz = $this->getRandomPlz();

        $request->setMethod('POST');
        $request->setPost(array('plz' => $plz['plz']));
        $this->dispatch('/if/tagesspiegel/2/service/');
        $this->assertController('iframe_framework');
        $this->assertAction('service');
    }

    public function testServiceWithCityId() {
        if ($this->config->domain->base != 'lieferando.de') {
            $this->markTestSkipped("Test should only run in lieferando.de");
        }

        //provide just a plz, should work, if he finds a cityId
        $request = $this->getRequest();
        $plz = $this->getRandomPlz();

        $request->setMethod('POST');
        $request->setPost(array('plz' => $plz['plz'], 'cityId' => $plz['cityId']));
        $this->dispatch('/if/tagesspiegel/2/service/');
        $this->assertController('iframe_framework');
        $this->assertAction('service');
    }

}
