<?php

/**
 * @runTestsInSeparateProcesses
 */
class SatelliteMultipleControllerTest extends Yourdelivery_Test {

    public function setUp() {
        define('HOSTNAME', 'www.avanti.de');
        parent::setUp();
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 03.06.2012
     *
     * @dataProvider dataProviderCacheNoCache
     */
    public function testImpressum($cache) {
        $this->setUsingCache($cache);

        $this->getRequest();
        $this->dispatch('/impr');
        $this->assertRoute('sat-list-with-url-impressum');
        $this->assertController('satellite_multiple');
        $this->assertAction('impressum');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 03.06.2012
     *
     * @dataProvider dataProviderCacheNoCache
     */
    public function testBewerten($cache) {
        $this->setUsingCache($cache);

        $this->getRequest();
        $this->dispatch('/bewerten');
        $this->assertRoute('sat-list-with-url-bewerten');
        $this->assertController('satellite_multiple');
        $this->assertAction('bewerten');
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 03.06.2012
     *
     * @dataProvider dataProviderCacheNoCache
     */
    public function testList($cache) {
        $this->setUsingCache($cache);

        $this->getRequest();
        $this->dispatch('/bestellen/deutschland');
        $this->assertRoute('sat-list-with-url-avanti');
        $this->assertController('satellite_multiple');
        $this->assertAction('list');
    }

    /**
     * @author Toni Meuschke <meuschke@lieferando.de>
     * @since 01.06.2012
     *
     * @dataProvider dataProviderCacheNoCache
     */
    public function testSendmailtopremiumSuccess($cache) {
        $this->setUsingCache($cache);
        
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(array(
            'tomail' => "testing@lieferando.de",
            'Test' => "Test",
            'Test2' => "Test2"
        ));
        $this->dispatch('/satellite_multiple/sendmailtopremium');
        $this->assertController('satellite_multiple');
        $this->assertAction('sendmailtopremium');
        $this->assertRedirectTo('/bewerten_success');  
    }

}
?>
