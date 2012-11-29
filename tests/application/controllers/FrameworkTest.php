<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class FrameworkControllerTest extends Yourdelivery_Test {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 24.11.2010
     */
    public function testIframeofQype() {
        if($this->config->domain->base != 'lieferando.de'){
            $this->markTestSkipped("Test should only run in lieferando.de");
        }

        // call coorp for qype with service "Kreuzburger"
        $request = $this->getRequest();
        $this->dispatch('/if/qype/2/menu/10115/1371');
        $this->assertController('iframe_framework');
        $this->assertAction('menu');
    }

    /**
     * @author mlaug
     * @since 02.03.2011
     */
    public function testIframeofExpress() {
        if($this->config->domain->base != 'lieferando.de'){
            $this->markTestSkipped("Test should only run in lieferando.de");
        }

        // call coorp for qype with service "Kreuzburger"
        $request = $this->getRequest();
        $this->dispatch('/if/express/2/start/');
        $this->assertController('iframe_framework');
        $this->assertAction('start');
    }

    /**
     * @author mlaug
     * @since 02.03.2011
     */
    public function testIframeofTagesspiegel() {
        if($this->config->domain->base != 'lieferando.de'){
            $this->markTestSkipped("Test should only run in lieferando.de");
        }

        // call coorp for qype with service "Kreuzburger"
        $request = $this->getRequest();
        $this->dispatch('/if/tagesspiegel/2/start/');
        $this->assertController('iframe_framework');
        $this->assertAction('start');
    }

    /**
     * @author mlaug
     * @since 02.03.2011
     */
    public function testIframeofFronline() {
        if($this->config->domain->base != 'lieferando.de'){
            $this->markTestSkipped("Test should only run in lieferando.de");
        }

        // call coorp for qype with service "Kreuzburger"
        $request = $this->getRequest();
        $this->dispatch('/if/fronline/2/start/');
        $this->assertController('iframe_framework');
        $this->assertAction('start');
    }

}
