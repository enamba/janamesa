<?php

/**
 * Description of CachingTest
 *
 * @author matthiaslaug
 */
/**
 * @runTestsInSeparateProcesses 
 */
class CachingTest extends Yourdelivery_Test {

    public function testCachingIndex() {
      //  $this->markTestSkipped('schlägt im jenkins fehl');
        
        global $_SERVER;
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->application->getBootstrap()->testHelper('routes');
        $this->dispatch('/');
        $this->assertTrue(file_exists(APPLICATION_PATH . '/../public/cache/html/' . HOSTNAME . '/index.html'));
    }

    /**
     * test caching system for services
     * @author mlaug
     * @global array $_SERVER
     */
    public function testCachingService() {
        $cityId = $this->getRandomCityId();
        $city = new Yourdelivery_Model_City($cityId);

        global $_SERVER;
        $_SERVER['REQUEST_URI'] = '/' . $city->getRestUrl();
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->application->getBootstrap()->testHelper('routes');
        $this->dispatch('/' . $city->getRestUrl());
        $this->assertTrue(file_exists(APPLICATION_PATH . '/../public/cache/html/' . HOSTNAME .'/' . $city->getRestUrl() . '.html'), 
                'could not find cached html file '.APPLICATION_PATH . '/../public/cache/html/' . HOSTNAME .'/' . $city->getRestUrl() . '.html');
    }

    /**
     * test caching system for menu
     * @author mlaug
     * @global array $_SERVER
     */
    public function testCachingMenu() {
        $service = $this->getRandomService();

        global $_SERVER;
        $_SERVER['REQUEST_URI'] = '/' . $service->getRestUrl();
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->application->getBootstrap()->testHelper('routes');
        $this->dispatch('/' . $service->getRestUrl());
        $this->assertTrue(file_exists(APPLICATION_PATH . '/../public/cache/html/' . HOSTNAME . '/' . $service->getRestUrl() . '.html'));
    }

}

?>
