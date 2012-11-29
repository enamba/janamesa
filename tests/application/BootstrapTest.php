<?php
/**
 * @runTestsInSeparateProcesses
 */
class BootstrapTest extends Yourdelivery_Test {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 08.02.2012
     * @return Zend_Db_Table_Row_Abstract
     */
    protected function _getRandomCity() {

        $dbTable = new Yourdelivery_Model_DbTable_City();
        return $dbTable->fetchRow(
            $dbTable->select()
                    ->order("RAND()")
                    ->limit(1)
        );
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 08.02.2012
     * @return Zend_Db_Table_Row_Abstract
     */
    protected function _getRandomRestaurant($deleted = 0) {

        $dbTable = new Yourdelivery_Model_DbTable_Restaurant();
        return $dbTable->fetchRow(
            $dbTable->select()
                    ->where("`deleted` = ?", $deleted)
                    ->where("`restUrl` <> ''")
                    ->where("`caterUrl` <> ''")
                    ->where("`greatUrl` <> ''")
                    ->order("RAND()")
                    ->limit(1)
        );
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 08.02.2012
     */
    public function testRouteCityRest() {
        if (!preg_match("/\.pl$/", HOSTNAME)) {
            $this->markTestSkipped("only in PL");
        }


        $row = $this->_getRandomCity();

        $_SERVER['REQUEST_URI'] = "/" . $row->restUrl;
        $this->application->getBootstrap()->_initRoutes();
        $this->dispatch("/" . $row->restUrl);
        $this->assertRoute('listPlzServices');
        $this->assertController('order_basis');
        $this->assertAction('service');
        $this->assertNotRedirect();
    }

   
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 08.02.2012
     * @expectedException Zend_Controller_Dispatcher_Exception
     */
    public function testRouteCityCater() {

        $row = $this->_getRandomCity();

        $_SERVER['REQUEST_URI'] = "/" . $row->caterUrl;
        $this->application->getBootstrap()->_initRoutes();
        $this->dispatch("/" . $row->restUrl, true);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 08.02.2012
     * @expectedException Zend_Controller_Dispatcher_Exception
     */
    public function testRouteCityGreat() {

        $row = $this->_getRandomCity();

        $_SERVER['REQUEST_URI'] = "/" . $row->greatUrl;
        $this->application->getBootstrap()->_initRoutes();
        $this->dispatch("/" . $row->greatUrl, true);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 08.02.2012
     */
    public function testRouteRestaurant() {

        $row = $this->_getRandomRestaurant();

        $modes = array("restUrl", "caterUrl", "greatUrl");
        foreach ($modes as $mode) {
            $_SERVER['REQUEST_URI'] = "/" . $row->$mode;
            $this->application->getBootstrap()->_initRoutes();
            $this->dispatch("/" . $row->$mode);
            $this->assertRoute('showMenu', sprintf('Request_Uri: %s',  $_SERVER['REQUEST_URI']));
            $this->assertController('order_basis');
            $this->assertAction('menu');
            $this->assertNotRedirect();
        }
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 08.02.2012
     * @expectedException Zend_Controller_Dispatcher_Exception
     */
    public function testRouteRestaurantDeleted() {

        $row = $this->_getRandomRestaurant(1);

        $_SERVER['REQUEST_URI'] = "/" . $row->restUrl;
        $this->application->getBootstrap()->_initRoutes();
        $this->dispatch("/" . $row->restUrl, true);
    }


    public function testRouteDiscount() {

        $discount = $this->createNewCustomerDiscount();

        $_SERVER['REQUEST_URI'] = "/" . $discount->getReferer() ;
        $this->application->getBootstrap()->_initRoutes();
        $this->dispatch("/" .$discount->getReferer() );
        $this->assertRoute(sprintf('discount-%s-3', $discount->getReferer() ));
        $this->assertController('discount');
        $this->assertAction('index');
        $this->assertNotRedirect();

    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 19.06.2012
     */
    public function testHeadersIndex(){
        //index page
        $this->dispatch('/');
        $this->assertHeaderContains('Cache-Control', 'max-age=86400, public, must-revalidate');
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 19.06.2012
     */
    public function testHeadersCity(){
        //service page
        $city = new Yourdelivery_Model_City($this->getRandomCityId());
        $_SERVER['REQUEST_URI'] = "/" . $city->getUrl();
        $this->application->getBootstrap()->_initRoutes();
        $this->dispatch('/' . $city->getUrl());
        $this->assertHeaderContains('Cache-Control', 'max-age=28800, public, must-revalidate');
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 19.06.2012
     */
    public function testHeadersMenu(){
        //menu page
        $service = $this->getRandomService();
        $_SERVER['REQUEST_URI'] = "/" . $service->getRestUrl();
        $this->application->getBootstrap()->_initRoutes();
        $this->dispatch('/' . $service->getRestUrl());
        $this->assertHeaderContains('Cache-Control', 'max-age=28800, public, must-revalidate');
    }

    /**
     * Tests the _initPartnerRoute method
     *
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 12.07.2012
     */
    public function testInitPartnerRoutes() {
        $fc = Zend_Controller_Front::getInstance();
        $router = $fc->getRouter();
        $this->assertTrue($router->hasRoute('partnerRoute'));

        $partnerRoute = $router->getRoute('partnerRoute');
        $this->assertTrue($partnerRoute instanceof Zend_Controller_Router_Route);

        $defaultValues = $partnerRoute->getDefaults();
        $this->assertEquals($defaultValues, array('controller'=>'partner','action'=>'index'));

        $this->dispatch("/partner/login");
        $this->assertRoute('partnerRoute');
        $this->assertController('partner');
        $this->assertAction('login');
    }

}
