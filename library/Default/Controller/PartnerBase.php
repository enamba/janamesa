<?php

/**
 * Description of partnerBase
 *
 * @author daniel
 */
class Default_Controller_PartnerBase extends Default_Controller_Base {

    /**
     *
     * @var Zend_Auth_Adapter_DbTable
     */
    protected $adminAuth = null;

    /**
     *
     * @var Yourdelivery_Model_Servicetype_Abstract
     */
    protected $restaurant = null;
    // for the case if password was forgotten and temporary password was send to the partner
    protected $temporaryAdminAuth = null;
    protected $_firstSaleMonth = null;
    protected $_firstSaleYear = null;

    /**
     * do some stuff before action is called
     */
    public function preDispatch() {

        parent::preDispatch();

        $request = $this->getRequest();

        if (APPLICATION_ENV != 'testing') {
            ini_set('memory_limit', '512M');
        }

        $this->adminAuth = $this->setAdminAuth();
        $this->temporaryAdminAuth = $this->setTemporaryAdminAuth();

        $this->view->assign("request", $this->getRequest());
        $this->view->assign("loggedIn", (is_null($this->session->partnerRestaurantId)) ? false : true);

        $this->restaurant = $this->initRestaurant();
        $this->view->assign("restaurant", $this->restaurant);
        $this->view->action = $this->getRequest()->getActionName();
        $request = $this->getRequest();

        if (!is_null($this->restaurant)) {
            $firstSale = Yourdelivery_Model_DbTable_Restaurant::getDateOfFirstSale($this->restaurant->getId());

            if (is_array($firstSale) && count($firstSale) > 0) {
                $this->view->firstMonth = $this->_firstSaleMonth = date('m', strtotime($firstSale['time']));
                $this->view->firstYear = $this->_firstSaleYear = date('Y', strtotime($firstSale['time']));

                $years = array();
                for ($i = $this->_firstSaleYear; $i <= date('Y'); $i++) {
                    $years[] = $i;
                }
                $this->view->years = array_reverse($years);
            } else {
                $this->view->firstMonth = null;
                $this->view->firstYear = null;
            }
        }


        if ($request->getActionName() != 'login' && $request->getActionName() != 'requestpassword' && is_null($this->session->partnerRestaurantId)) {
            $this->_helper->redirector->gotoRoute(array('action' => 'login'), 'partnerRoute', true);
        }

        if ($this->session->temporaryAuthentication && ($request->getActionName() != 'resetpassword') && ($request->getActionName() != 'logout')) {
            $this->_helper->redirector->gotoRoute(array('action' => 'resetpassword'), 'partnerRoute', true);
        }
    }

    /**
     * init restaurant
     * @return Yourdelivery_Model_Servicetype_Restaurant
     */
    protected function initRestaurant() {
        if (is_null($this->session->partnerRestaurantId)) {
            return null;
        }
        $restaurantId = $this->session->partnerRestaurantId;
        $this->view->statistics = true;
        $this->view->statisticsOrderCountToday = Yourdelivery_Statistics_Restaurant::getOrderCount($restaurantId, 'today');
        $this->view->statisticsOrderCountLastseven = Yourdelivery_Statistics_Restaurant::getOrderCount($restaurantId, 'lastseven');
        $this->view->statisticsOrderCountWeek = Yourdelivery_Statistics_Restaurant::getOrderCount($restaurantId, 'week');
        $this->view->statisticsOrderCountMonth = Yourdelivery_Statistics_Restaurant::getOrderCount($restaurantId, 'month');
        $this->view->statisticsOrderCountLastmonth = Yourdelivery_Statistics_Restaurant::getOrderCount($restaurantId, 'lastmonth');
        $this->view->assign('loggedIn', true);
        return new Yourdelivery_Model_Servicetype_Restaurant($this->session->partnerRestaurantId);
    }

    /**
     * Sets the auth of the admin
     * @return Zend_Auth_Adapter_DbTable
     */
    public function setAdminAuth() {
        $form = new Yourdelivery_Form_Partner_Login();
        return $form->getAuthAdapter();
    }

    /**
     * Sets the authentication data of the partner backend for the temporary password
     * @author Alex Vait <vait@lieferando.de>
     * @return Zend_Auth_Adapter_DbTable
     */
    public function setTemporaryAdminAuth() {
        $registry = Zend_Registry::get('dbAdapter');
        $auth = new Zend_Auth_Adapter_DbTable($registry);

        $auth->setTableName('partner_restaurants')
                ->setIdentityColumn('restaurantId')
                ->setCredentialColumn('temporarypassword')
                ->setCredentialTreatment('MD5(?)');

        return $auth;
    }

    /**
     * use default session to get notifications
     */
    public function postDispatch() {
        parent::postDispatch();
    }
}
