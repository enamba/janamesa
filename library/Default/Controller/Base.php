<?php

/**
 * Base Controller Class
 * @package core
 * @subpackage controller
 * @abstract
 * @author mlaug
 */
abstract class Default_Controller_Base extends Zend_Controller_Action {

    /**
     * store the current session
     * @var Zend_Session_Namespace
     */
    protected $_session = null;
    /**
     * logging facility
     * @var Zend_Log
     */
    protected $_logger = null;
    /**
     * Cache nearly everything to provide a rapid interface
     * @var Zend_Cache
     */
    protected $_cache = null;
    /**
     * Get DB Adapter
     * @var Zend_Db
     */
    protected $_db = null;
    
    /**
     * get the current customer
     * @var Yourdelivery_Model_Customer_Abstract
     */
    protected $_customer = null;

    /**
     * initialize each controller action
     * @author mlaug
     */
    public function init() {
        /**
         * HACK: in testing the view seems to be missing, this restores
         * this issue, but there should be a proper fix
         */
        if ($this->view == null) {
            $rend = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
            $this->view = $rend->view;
        }
        //use this cookie based security to ensure calls can be done only from within the domain
        $secure = Yourdelivery_Security_Request::getInstance();
        $secure->createPassword();
        
        $this->view->assign('cust', $this->getCustomer());
        $this->view->assign('request', $this->getRequest());
    }

    public function __get($name) {
        switch ($name) {

            /**
             * get our logger facility
             */
            case "logger": {
                    if ($this->_logger == null) {
                        //set up logger
                        $logger = Zend_Registry::get('logger');
                        $this->_logger = $logger;
                    }
                    return $this->_logger;
                }

            /**
             * get our cache system
             */
            case "cache": {
                    if ($this->_cache == null && Zend_Registry::isRegistered('cache')) {
                        //set up cache
                        $cache = Zend_Registry::get('cache');
                        $this->_cache = $cache;
                    }
                    return $this->_cache;
                }

            /**
             * get our session storage
             */
            case "session": {
                    if ($this->_session == null) {
                        // start new Session or get previous from namespace
                        $this->_session = new Zend_Session_Namespace('Default');
                    }
                    return $this->_session;
                }


            case 'session_admin': {
                    if ($this->_session_admin == null) {
                        // start new Session or get previous from namespace
                        $this->_session_admin = new Zend_Session_Namespace('Administration');
                    }
                    return $this->_session_admin;
                }

            case 'session_restaurant': {
                    if ($this->_session_restaurant == null) {
                        // start new Session or get previous from namespace
                        $this->_session_restaurant = new Zend_Session_Namespace('Restaurant');
                    }
                    return $this->_session_restaurant;
                }

            /**
             * get db adapter
             */
            case "database": {
                    if ($this->_db == null) {
                        /**
                         * @see Zend_Db_Adapter
                         */
                        $this->_db = Zend_Registry::get('dbAdapter');
                    }
                    return $this->_db;
                }

            /**
             * get auth adapter
             */
            case "auth": {
                    if ($this->_auth == null) {

                        $auth = new Zend_Auth_Adapter_DbTable(Zend_Registry::get('dbAdapterReadOnly'));

                        $auth->setTableName('customers')
                                ->setIdentityColumn('email')
                                ->setCredentialColumn('password')
                                ->setCredentialTreatment('MD5(?)');

                        //add filter that we do not check for deleted users
                        $auth->getDbSelect()
                                ->where('deleted=0')
                                ->where('password IS NOT null');

                        /**
                         * @var Zend_Auth
                         */
                        $this->_auth = $auth;
                    }
                    return $this->_auth;
                }

            /**
             * get facebook auth adapter
             */
            case "auth_fb": {
                    if ($this->_auth == null) {

                        $auth = new Zend_Auth_Adapter_DbTable(Zend_Registry::get('dbAdapterReadOnly'));

                        $auth->setTableName('customers')
                                ->setIdentityColumn('email')
                                ->setCredentialColumn('facebookId')
                                ->setCredentialTreatment(null);

                        //add filter that we do not check for deleted users
                        $auth->getDbSelect()
                                ->where('facebookId IS NOT null')
                                ->where('deleted=0');

                        /**
                         * @var Zend_Auth
                         */
                        $this->_auth = $auth;
                    }
                    return $this->_auth;
                }

            /**
             * get current configuration of application
             */
            case "config": {
                    if ($this->_config == null) {
                        $this->_config = Zend_Registry::get('configuration');
                    }
                    return $this->_config;
                }
        }
    }

    /**
     * work some stuff before action is called
     * @author mlaug
     */
    public function preDispatch() {

        $cust = $this->session->customer;
        $request = $this->getRequest();
        $this->view->assign("request", $this->getRequest());
    }

    /**
     * display a success message using session
     * @author mlaug
     * @param string $msg
     */
    public function success($msg) {
        Default_View_Notification::success($msg);
    }

    /**
     * display a warning message using session
     * @author mlaug
     * @param string $msg
     */
    public function warn($msg) {
        Default_View_Notification::warn($msg);
    }

    /**
     * display a info message using session
     * @author mlaug
     * @param string $msg
     */
    public function info($msg) {
        $this->success($msg);
    }

    /**
     * display a error message using session
     * @author mlaug
     * @param string $msg
     */
    public function error($msg) {
        Default_View_Notification::error($msg);
    }

    /**
     * do some stuff after view has been called
     * @author mlaug
     */
    public function postDispatch() {
        try {
            $params = $this->getRequest()->getParams();
            if (is_object($this->session->customer) && $this->session->customer->isLoggedIn()) {
                $this->session->customer->setPersistentNotfication();
            }
            $this->view->assign('notifications', $this->session->notification);
            $this->view->assign('additionalJs', $this->_additionalJs);
            $this->view->assign('additionalExternJs', $this->_additionalExternJs);
            $this->session->notification = null;
            if (!is_null($this->session_front)) {
                $this->session_front = null;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (APPLICATION_ENV == "production") {
                Yourdelivery_Sender_Email::error($error);
            } else {
                echo $error;
            }
        }
    }

    /**
     * set the correct meta tags
     * seo relevant
     * @author mlaug
     * @since 12.05.2011
     */
    public function seoRelevant() {
        $meta[] = '<meta name="robots" content="index,follow" />';
        $this->view->assign('additionalMetatags', $meta);
    }

    /**
     * set the correct meta tags
     * seo irrelavant
     * @author mlaug
     * @since 12.05.2011
     */
    public function notSeoRelevant() {
        $meta[] = '<meta name="robots" content="noindex,follow" />';
        $this->view->assign('additionalMetatags', $meta);
    }

    /**
     * get the current session customer
     * @author mlaug
     * @since 19.07.2011
     * @return Yourdelivery_Model_Customer_Abstract
     */
    protected function getCustomer() {
        
        if ( $this->_customer instanceof Yourdelivery_Model_Customer_Abstract ){
            return $this->_customer;
        }
        
        $customer = new Yourdelivery_Model_Customer_Anonym();
        try{
            if (isset($this->session->customerId) && $this->session->customerId > 0) {
                $customer = new Yourdelivery_Model_Customer($this->session->customerId);
                if ($customer->isEmployee()) {
                    $customer = new Yourdelivery_Model_Customer_Company($this->session->customerId, $customer->getCompany()->getId());
                }
            }
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            
        }
        $customer->isLoggedIn(true);
        return $this->_customer = $this->view->customer = $customer;
    }
    
    /**
     * reset the current customer
     * @author mlaug
     * @since 17.08.2011
     */
    protected function resetCustomer() {
        $this->_customer = null;
    }

    /**
     * Disable zend view
     * @author vpriem
     * @since 16.06.2011
     */
    protected function _disableView($disabled = true) {      
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', $disabled);
    }
    
    /**
     * set the cache for a page
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @param integer $maxAge
     * @param string $cacheExtra
     * @param string $pragma 
     */
    protected function setCache($maxAge = 0, $cacheExtra = 'public, must-revalidate', $pragma = 'no-cache'){
        $response = $this->getResponse();
        $response->setHeader('Cache-Control', sprintf('max-age=%s, %s', $maxAge, $cacheExtra), true);
        $response->setHeader('Pragma', $pragma, true);
        $response->setHeader('Date', date('D, d M Y H:i:s \G\M\T',time()), true);
        $response->setHeader('Expires', date('D, d M Y H:i:s \G\M\T',time() + $maxAge), true);
    }

    /**
     * Overrides locale setting, storing the newly chosen one also in the cookie
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>, Vincent Priem <priem@lieferando.de>
     * @since 12.06.2012
     *
     * @param string $cookieName
     * @return string
     */
    final protected function _overrideLocale($cookieName = null, $locale = null) {
        if ($this->_isLocaleFrozen()) {
            // Locale overriding not allowed
            return null;
        }
        if (is_null($cookieName)) {
            if (is_null($cookieName = $this->_getLocaleCookieName())) {
                // Cookie name unknown - giving up
                return null;
            }
        }

        if (is_null($locale)) {
            // retrieving custom locale from the cookie and checking, whether is supported
            $locale = Default_Helpers_Web::getCookie($cookieName);
            if (is_null($locale)) {
                // cookie locale not set - using browser suggestion
                $locale = Default_Helpers_Locale::detect();
            }
            if (!Default_Helpers_Locale::isAvailable($locale)) {
                $locale = null;
            }
        } else {
            // checking whether given locale is supported, then storing it in the cookie
            if (Default_Helpers_Locale::isAvailable($locale)) {
                Default_Helpers_Web::cancelCookie($cookieName);
                Default_Helpers_Web::setCookie($cookieName, $locale, Default_Helpers_Locale::getExpireTime());
            } else {
                $locale = null;
            }
        }

        if (!is_null($locale)) {
            // using just validated locale
            if (!Default_Helpers_Locale::apply($locale)) {
                $locale = null;
            }
        }

        if (is_null($locale)) {
            // deleting locale cookie for not supported case
            Default_Helpers_Web::deleteCookie($cookieName);
        }

        return $locale;
    }

    /**
     * Temporarily (no cookie) switches locale to passed one (or config-based)
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 6.07.2012
     *
     * @param string $locale
     * @return string
     */
    final protected function _restoreLocale($locale = null) {
        if ($this->_isLocaleFrozen()) {
            // Locale overriding not allowed
            return null;
        }

        if ($locale === null) {
            $locale = $this->config->locale->name;
        }
        
        if (!Default_Helpers_Locale::isAvailable($locale)) {
            return null;
        }
        
        if (!Default_Helpers_Locale::apply($locale)) {
            $locale = null;
        }
        
        return $locale;
    }

    /**
     * Returns default cookie name for locale overriding
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 6.07.2012
     *
     * @return string
     */
    protected function _getLocaleCookieName() {
        // Globally default locale cookie name is unset to prevent from locale overriding
        // It will be available only in some controllers
        return null;
    }

    /**
     * Tells whether any locale overriding case is allowed
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 6.07.2012
     *
     * @return boolean
     */
    protected function _isLocaleFrozen() {
        // Globally locale changing is forbidden
        // It will be available only in some controllers
        return true;
    }
}
