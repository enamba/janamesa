<?php

/**
 * RestaurantBase Controller Class
 *
 * @package core
 * @subpackage controller
 * @author vait
 */
class Default_Controller_RestaurantBase extends Default_Controller_Base {

    /**
     *
     * @var Zend_Auth_Adapter_DbTable
     */
    protected $adminAuth = null;

    /**
     * do some stuff before action is called
     */
    public function preDispatch() {

        parent::preDispatch();
        if(APPLICATION_ENV != 'testing'){
            ini_set('memory_limit', '512M');
        }

        $madmin = $this->session->masterAdmin;
        $admin = $this->session->admin;

        $this->adminAuth = $this->setAdminAuth();
        $this->view->assign("request", $this->getRequest());
        $this->view->assign("loggedIn", (is_null($this->session->admin) && is_null($this->session->masterAdmin) ) ? false : true);

        $restaurant = $this->initRestaurant();
        $this->view->assign("restaurant", $restaurant);

        $request = $this->getRequest();
        if ($request->getActionName() != 'login' && is_null($this->session->admin) && is_null($this->session->masterAdmin)) {
            $this->_redirect('/restaurant/login');
        }
    }

    /**
     * init restaurant
     * @return Yourdelivery_Model_Servicetype_Restaurant
     */
    protected function initRestaurant() {
        
        if (is_null($this->session->currentRestaurant))
            return null;
        return new Yourdelivery_Model_Servicetype_Restaurant($this->session->currentRestaurant->getId());
    }

    /**
     * getter
     * @author vait
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        
        switch ($name) {
            default: 
                return parent::__get($name);

            case 'session': 
                if ($this->_session_restaurant == null) {
                    // start new Session or get previous from namespace
                    $this->_session_restaurant = new Zend_Session_Namespace('Restaurant');
                }
                return $this->_session_restaurant;

            case 'session_front': 
                return parent::__get('session');
        }
    }

    /**
     * Sets the auth of the admin
     * @return Zend_Auth_Adapter_DbTable
     */
    public function setAdminAuth() {
        $registry = Zend_Registry::get('dbAdapter');
        $auth = new Zend_Auth_Adapter_DbTable($registry);

        $auth->setTableName('customers')
                ->setIdentityColumn('email')
                ->setCredentialColumn('password')
                ->setCredentialTreatment('MD5(?)');

        return $auth;
    }

    /**
     * use default session to get notifications
     */
    public function postDispatch() {
        parent::postDispatch();
        $session = new Zend_Session_NameSpace('Default');
        $this->view->assign('notifications', $session->notification);
        $session->notification = null;
    
        $this->setAdminLinks();        
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 06.02.2012
     * define link urls and names for admin backend
     */
    private function setAdminLinks() {
        $menuLinks = array(
            __b('Finanzen') => array(
                'url' => '#',
                'icon_class' => 'icon01',
                'children' => array(
                    __b('Statistik') => '/restaurant/stats',
                    __b('Bestellungen') => '/restaurant/orders',
                    __b('Rechnungen') => '/restaurant/billing'
                )
            ),
            __b('Verwaltung') => array(
                'url' => '#',
                'icon_class' => 'icon02',
                'children' => array(
                    __b('Restaurantverwaltung') => '/restaurant_settings',
                    __b('Liefergebiete') => '/restaurant/locations'
                )
            ),            
            __b('Menü') => array(
                'url' => '#',
                'icon_class' => 'icon03',
                'children' => array(
                    __b('Menüverwaltung') => '/restaurant/menu',
                    __b('Kategorien Übersicht') => '/restaurant/mealcategories',
                    __b('Gerichtgrößen Übersicht') => '/restaurant/mealsizes',
                    __b('Speisen Übersicht') => '/restaurant/meals',
                    __b('Menüvorschau') => '/restaurant/menupreview'
                    
                )
            ),            
            __b('Extras und Optionen') => array(
                'url' => '#',
                'icon_class' => 'icon04',
                'children' => array(
                    __b('Extras Gruppen') => '/restaurant/mealextrasgroups',
                    __b('Extras') => '/restaurant/mealextras',
                    __b('Optionsgruppen') => '/restaurant/mealoptionrows',
                    __b('Optionen') => '/restaurant/mealoptions',
                )
            ),            
            __b('Notizblock') => array(
                'url' => '/restaurant_notepad',
                'icon_class' => 'icon05',
                'children' => array(
                )
            ),
            __b('Jetzt Cache leeren!') => array(
                'url' => '/restaurant/uncache',
                'icon_class' => 'icon06',
                'children' => array(
                )
            )
        );

        $this->view->assign('requestUri', $_SERVER["REQUEST_URI"]);
        foreach ($menuLinks as $parent => $container) {
            if (in_array($_SERVER["REQUEST_URI"], array_values($container['children']))) {
                $this->view->assign('parentMenu', $parent);
            }
        }
        $this->view->assign('menuLinks', $menuLinks);
    }    
}
