<?php

/**
 *
 * @package controller
 * @subpackage iframe
 * @author mlaug
 */
class Iframe_FrameworkController extends Default_Controller_Base {

    protected $_partner = null;
    protected $_cssVersion = null;

    public function init() {
        parent::init();

        $request = $this->getRequest();
        $this->_partner = $request->getParam('partner', 'default');
        $this->_cssVersion = (integer) $request->getParam('css', 1);

        //def2ine layout to choose from
        $this->view->setLayout('iframe', true);
        $this->view->setDir($this->_partner);
        $this->view->setName('base.htm');

        $this->view->partner = $this->session->partner = $this->_partner;
        $this->view->cssversion = $this->_cssVersion;

        try {
            $method = $request->getParam('method', 'error');
            $this->_forward($method); //forward (NOT REDIRECT) to destined action
        } catch (Exception $e) {
            $this->_redirect('/iframe_framework/error');
        }
    }

    public function startAction() {
        $this->view->enableCache();
        $this->view->setName('start.htm');
    }

    /**
     * load the the service page into an iframe
     * we need parameters to define the template
     * @author mlaug
     */
    public function serviceAction() {
        $this->view->enableCache();

        //define the service page
        $this->view->setName('service.htm');

        $location = $this->_initLocation();
        if ( $location === null ){
            /**
             * if we found no valid location, we redirect to start plz
             */
            $request = $this->getRequest();
            $plz = (integer) $request->getParam('plz');
            $cityId = (integer) $request->getParam('cityId');
            $this->logger->warn(sprintf('FRAMEWORK CONTROLLER: could not initLocation with given params - cityId %s, plz %s', $cityId, $plz));
            return $this->_redirect('/if/'.$this->_partner.'/'.$this->_cssVersion.'/start');
            #die('waaaaah');
        }

        //append data to view
        $this->view->cityId = $location->getCity()->getId();
        $this->view->location = $location;
        $this->view->ydcategories = Yourdelivery_Model_Servicetype_Categories::getCategoriesByCityId($location->getCity()->getId(), 1, true);
        $this->view->services = Yourdelivery_Model_Order::getServicesByCityId($location->getCity()->getId(), 'rest');
    }

    /**
     * just load the menu from given service
     * @author mlaug
     */
    public function menuAction() {

        $this->view->enableCache();

        /**
         * this little hack is for including the header of express
         */
        $this->view->setName('menu.htm');

        $sid = (integer) $this->getRequest()->getParam('sid', null);

        if (($sid === null || $sid <= 0)) {
            die(__("Es tut uns leid, bei diesem Lieferservice sind zur Zeit keine Onlinebestellungen möglich"));
        }

        try{
            $service = new Yourdelivery_Model_Servicetype_Restaurant($sid);
            $rangeFirst = current($service->getRanges());
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            die(__("Es tut uns leid, bei diesem Lieferservice sind zur Zeit keine Onlinebestellungen möglich"));
        }

        $location = $this->_initLocation($rangeFirst['cityId']);
        if ( $location === null ){
            die('waaaaah');
        }

        /**
         * do some qype stuff here, but we may think
         * about refactoring this to a new controller
         * to keep this clean
         */
        $search_url = urldecode($this->getRequest()->getParam('search_url', null));
        $discount_code = $this->getRequest()->getParam('discount', null);

        if ($discount_code !== null && strlen($discount_code) > 0) {
            try {

                $discount = new Yourdelivery_Model_Rabatt_Code($discount_code);
                if ( !$service->isOnlycash() && $discount->isUsable() ) {
                    $this->view->discount = $discount;
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

            }
        }

        $this->view->search_url = $search_url;

        switch ($this->_partner) {
            default:
                $width = 0;
                $height = 0;
                break;
            case 'qype':
                $width = 460;
                $height = 85;
                break;
            case 'express':
                $width = 380;
                $height = 85;
                break;
        }

        $this->view->category_image_height = $height;
        $this->view->category_image_width = $width;

        // create menu
        $this->view->location = $location;
        $this->view->minAmount = $service->getMinCost($location->getCityId());
        $this->view->deliverCost = $service->getDeliverCost($location->getCityId());
        $this->view->service = $service;
        list($menu, $parents) = $service->getMenu();
        $this->view->menu = $menu;
        $this->view->parents = $parents;

        if($this->_partner != 'speisekarte'){
            $this->view->menu = $this->view->fetch('default/menu.htm', $cacheId);
        }
    }

    public function errorAction() {

    }

    /**
     * @author mlaug
     * @since 21.06.2011
     * @return Yourdelivery_Model_Location
     */
    protected function _initLocation($backupCityId = null) {
        $request = $this->getRequest();
        $plz = $request->getParam('plz');
        $cityId = (integer) $request->getParam('cityId',$backupCityId);

        if ($cityId <= 0 && $plz <= 0) {
            if($backupCityId <= 0 || is_null($backupCityId)){
                return null;
            }else{
                $cityId = $backupCityId;
            }
        }


        //just a plz give, try to get a cityId
        if ($cityId <= 0) {
            $city = Yourdelivery_Model_City::getByPlz($plz);
            if (!$city || $city->count() == 0) {
                    return null;
                }
            $cityId = $city->current()->id;
        }

        $location = new Yourdelivery_Model_Location();
        $location->setCityId($cityId);

        $state = Yourdelivery_Cookie::factory('yd-state');
        $state->set('city', $cityId);
        $state->set('mode','rest');
        $state->save();

        try {
            $location->getCity();
            return $location;
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
    }

}
