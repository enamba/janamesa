<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class SettingsApiTest extends Yourdelivery_Test{

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testIndexFail() {
        $this->dispatch('/get_settings');
        $this->assertController('get_settings');
        $this->assertAction('index');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $xml = $response->getBody();

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $this->assertEquals('true',$doc->getElementsByTagName("success")->item(0)->nodeValue);

        $config = Zend_Registry::get('configuration');
        $fidelityConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini', APPLICATION_ENV);
        
        $this->assertEquals((integer) (boolean) $config->payment->paypal->enabled, (integer) (boolean) $doc->getElementsByTagName("paypal")->item(0)->nodeValue);
        $this->assertEquals((integer) (boolean) $config->payment->credit->enabled, (integer) (boolean) $doc->getElementsByTagName("credit")->item(0)->nodeValue);
        $this->assertEquals((integer) (boolean) $config->payment->ebanking->enabled, (integer) (boolean) $doc->getElementsByTagName("ebanking")->item(0)->nodeValue);
        $this->assertEquals((integer) (boolean) $config->payment->bar->enabled, (integer) (boolean) $doc->getElementsByTagName("bar")->item(0)->nodeValue);
        $this->assertEquals((integer) (boolean) $fidelityConfig->fidelity->enabled, (integer) (boolean) $doc->getElementsByTagName("enabled")->item(0)->nodeValue);
        $this->assertEquals((integer) $fidelityConfig->fidelity->cashin->need, (integer) $doc->getElementsByTagName("cashinneed")->item(0)->nodeValue);
        $this->assertEquals((integer) $fidelityConfig->fidelity->cashin->maxcost, (integer) $doc->getElementsByTagName("cashinlimit")->item(0)->nodeValue);
        $this->assertGreaterThan(600, strlen($doc->getElementsByTagName("sitenotice")->item(0)->nodeValue));
        $this->assertGreaterThan(50, strlen($doc->getElementsByTagName("dynamicStartUpHTML")->item(0)->nodeValue));
        $this->assertGreaterThan(300, strlen($doc->getElementsByTagName("dynamicFaqHTML")->item(0)->nodeValue));
        $this->assertGreaterThan(300, strlen($doc->getElementsByTagName("dynamicManualHTML")->item(0)->nodeValue));
        $this->assertGreaterThan(300, strlen($doc->getElementsByTagName("dynamicNewsHTML")->item(0)->nodeValue));
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testGetFail() {
        $request = $this->getRequest();
        $request->setMethod('GET');
        $this->dispatch('/get_settings/foo');
        $this->assertController('get_settings');
        $this->assertAction('get');
        $this->assertResponseCode(403);
    }


    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 08.01.2012
     */
    public function testPostFail() {
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch('/get_settings');
        $this->assertController('get_settings');
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
        $this->dispatch('/get_settings');
        $this->assertController('get_settings');
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
        $this->dispatch('/get_settings');
        $this->assertController('get_settings');
        $this->assertAction('delete');
        $this->assertResponseCode(403);
    }

}
