<?php

/**
 * @author mlaug
 * @package core
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    /**
     * @author mlaug
     * @return Zend_Application_Module_Autoloader
     */
    protected function _initAutoload() {
        $autoloader = new Zend_Application_Module_Autoloader(array(
                    'namespace' => 'Yourdelivery_',
                    'basePath' => dirname(__FILE__),
                ));
        return $autoloader;
    }

    /**
     * @author mlaug
     */
    protected function _initSecureRequests() {
        $this->bootstrap('registry');
        // init singleton
        $secure = Yourdelivery_Security_Request::getInstance();
        $secure->createPassword();
    }

    /**
     * @author mlaug
     * @since 07.09.2010
     */
    protected function _initConstants() {
        defined('ALL_TAX') ? null : define('ALL_TAX', 1000);
        defined('ONE_DAY') ? null : define('ONE_DAY', 60 * 60 * 24);
        defined('FLAG_NOT_DELETED') ? null : define('FLAG_NOT_DELETED', "(deleted IS NULL OR deleted = 0)");
        /* @deprecated BLACKLIST */
        defined('BLACKLIST') ? null : define('BLACKLIST', APPLICATION_PATH . '/../storage/blacklist.txt');
        defined('SALT') ? null : define('SALT', 'SALLLLLT');
        defined('LF') ? null : define('LF', "\n");
        defined('CRLF') ? null : define('CRLF', "\r\n");
        defined('DATE_DB') ? null : define('DATE_DB', "Y-m-d");
        defined('DATETIME_DB') ? null : define('DATETIME_DB', "Y-m-d H:i:s");
        defined('FRAUD_DISCOUNT_COOKIE') ? null : define('FRAUD_DISCOUNT_COOKIE', 'yd-discount');
    }

    /**
     * generate registry and bootstrap database. We use a mulit db environment
     * to scale perfomance. We have a read only database (slave) and a write
     * database (master)
     * @author mlaug
     * @return Zend_Registry
     */
    protected function _initRegistry() {
        $this->bootstrap('multidb');
        $multiDb = $this->getPluginResource('multidb');
        $multiDb->init();

        $registry = Zend_Registry::getInstance();
        $config = null;
        if (APPLICATION_ENV == 'testing') {
            $config = new Zend_Config($this->getOptions(), true);
        } else {
            $config = new Zend_Config($this->getOptions());
        }
        $registry->configuration = $config;

        try{
            // load write db.. if this fails that is really bad :(
            $registry->dbAdapter = $multiDb->getDb('write');
            $registry->dbAdapter->query("SET NAMES 'utf8'");
            $registry->dbAdapter->query(sprintf("SET lc_time_names = '%s'", $config->locale->name));
            
            //check for maintenance mode
            $maintenance = $registry->dbAdapter->fetchOne('select value from settings where setting="maintenance"');
            if ( $maintenance == 'on' ){
                throw new Exception('page currently in maintenance mode');
            }
            
        }
        catch (Exception $e){
            header('HTTP/1.1 500 Internal Server Error'); //should not be 200, so no caching is hit
            header('location:/maintenance.html');
            die();
        }

        if (!IS_PRODUCTION) {
            $profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
            $profiler->setEnabled(true);
            $registry->dbAdapter->setProfiler($profiler);
        }

        // load read db only if slave running
        try {
            $state = $multiDb->getDb('read')->fetchRow("SHOW SLAVE STATUS");
            if ($state !== null && $state['Slave_IO_Running'] == 'Yes' &&
                    $state['Slave_SQL_Running'] == 'Yes' && intval($state['Seconds_Behind_Master']) < 10) {

                $registry->dbAdapterReadOnly = $multiDb->getDb('read');
                $registry->dbAdapterReadOnly->query("SET NAMES 'utf8'");
                $registry->dbAdapterReadOnly->query(sprintf("SETSELECT DAYNAME('2010-01-01'), MONTHNAME('2010-01-01'); lc_time_names = '%s'", $config->locale->name));
            }
        } catch (Exception $e) {

        }

        if (!isset($registry->dbAdapterReadOnly)) {
            $registry->dbAdapterReadOnly = $registry->dbAdapter;
        }

        //if a cookie is set, we store it in here, so no duplicate headers are sent
        $registry->setCookies = array();

        $registry->node = @exec('/bin/hostname', $output); //get current node, to check load balancing

        $registry->redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        return $registry;
    }

    /**
     * @author mlaug
     */
    protected function _initPhp() {

        if (APPLICATION_ENV == "testing") {
            #Default_Helpers_Date::setTimezoneByDesiredTime('12:00');
            gc_enable();
            //testing is default round a about 12 o'clock
            ini_set('memory_limit', '14000M');
            ini_set('max_execution_time', 0);

            $_SERVER['HTTP_X_FORWARDED_HOST'] = '66.66.66.66';
            $_SERVER['HTTP_HOST'] = '66.66.66.66';

            $_SERVER['SERVER_NAME'] = '66.66.66.66';

            $_SERVER['HTTP_X_FORWARDED_FOR'] = '66.66.66.66';
            $_SERVER['HTTP_CLIENT_IP'] = '66.66.66.66';
            $_SERVER['REMOTE_ADDR'] = '66.66.66.66';
        } else {
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', 60);
            ini_set('zlib.output_compression', 'Off'); //this will be done by apache
        }
    }

    /**
     * Init translation
     *
     * @author Vincent Priem <priem@lieferando.de>
     * @since 09.12.2010
     */
    protected function _initLocale() {

        $this->bootstrap('registry');
        $registry = $this->getResource('registry');

        $config = $registry->configuration;
        if ($config->locale !== null) {
            $locale = $config->locale->toArray();

            // init locale
            setlocale(LC_ALL, $locale['name'] . ".UTF-8");
            date_default_timezone_set($locale['timezone']);

            // init gettext
            if (function_exists("bindtextdomain")) {
                // sets the path for the backend domain
                bindtextdomain("yd-backend", APPLICATION_PATH . "/locales");
                bind_textdomain_codeset("yd-backend", "UTF-8");

                // sets the path for the partner domain
                bindtextdomain("yd-partner", APPLICATION_PATH . "/locales");
                bind_textdomain_codeset("yd-partner", "UTF-8");

                // sets the path for the frontend domain
                bindtextdomain("yd", APPLICATION_PATH . "/locales");
                bind_textdomain_codeset("yd", "UTF-8");
                // and uses it as the default domain
                textdomain("yd");
            }
        }
    }

    /**
     * Custom Routes
     * @author mlaug
     * @return Zend_Router
     */
    public function _initRoutes() {
        $this->bootstrap('caching');
        $registry = $this->getResource('registry');

        $ctrl = Zend_Controller_Front::getInstance();
        $router = $ctrl->getRouter();

        $this->__initFrameworkRoutes($router);

        $this->__initApiRoutes($router, $ctrl);

        $this->__initSatelliteRoutes($router);

        $this->__initRatingRoutes($router);

        $this->__initDownloadRoutes($router);

        $this->__initDirectLinkRoutes($router);

        $this->__initNewsletterRoutes($router);

        $this->__initDiscountRoutes($router);

        $this->__initPartnerRoutes($router);

        /**
         * used for getting credit for invited customers
         */
        $router->addRoute(
                'inviteRoute', new Zend_Controller_Router_Route('i/:customerId',
                        array('controller' => 'tracking',
                            'action' => 'i'))
        );


        return $router;
    }

    /**
     * @author mlaug
     * @since 01.04.2011
     * @param Zend_Controller_Router_Interface $router
     */
    private function __initNewsletterRoutes(Zend_Controller_Router_Interface $router) {
        // routes to unsubscribe from newsletter
        $router->addRoute(
                'abmeldenRoute', new Zend_Controller_Router_Route('unsubscribe',
                        array('controller' => 'user',
                            'action' => 'abmelden'))
        );
        // routes to unsubscribe from newsletter directly per link
        $router->addRoute(
                'unsubscribeRoute', new Zend_Controller_Router_Route('unsubscribe/:email',
                        array('controller' => 'user',
                            'action' => 'abmelden'))
        );
    }

    /**
     * @author mlaug
     * @since 01.04.2011
     * @param Zend_Controller_Router_Interface $router
     */
    private function __initDownloadRoutes(Zend_Controller_Router_Interface $router) {
        // add download route
        $router->addRoute(
                'downloadRoute1', new Zend_Controller_Router_Route('download/:action/:hash/:ext',
                        array('controller' => 'download'))
        );
        $router->addRoute(
                'downloadRoute2', new Zend_Controller_Router_Route('download/:action/:hash',
                        array('controller' => 'download'))
        );
        $router->addRoute(
                'bestellzettelRoute', new Zend_Controller_Router_Route('ordercoupon/:hash',
                        array('controller' => 'order',
                            'action' => 'bestellzettel'
                ))
        );
    }

    /**
     * @author vpriem
     * @since 27.05.2011
     * @param Zend_Controller_Router_Interface $router
     */
    private function __initDirectLinkRoutes(Zend_Controller_Router_Abstract $router) {

        $uri = @parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($uri === false) {
            return;
        }
        $uri = substr($uri, 1); //remove leading slash
        // must match a certian regular expression to trigger database
        if (preg_match('/request|administration|autocomplete|^user\/|^order_/', $uri)) {
            return;
        }

        $uri = urldecode($uri);

        // exclude satellites
        if (!isBaseUrl()) {
            return;
        }

        $row = null;
        $mode = null;

        //current service url
        list ($mode, $row) = Yourdelivery_Model_DbTable_Restaurant::findByDirectLink($uri);
        if ($row !== null) {
            return $router->addRoute('showMenu', new Zend_Controller_Router_Route($uri, array(
                                'controller' => 'order_basis',
                                'action' => 'menu',
                                'mode' => $mode,
                                'serviceId' => $row['id'],
                            )));
        }

        //plz url: for all services delivering to one plz
        $row = Yourdelivery_Model_DbTable_City::findByDirectLink($uri);
        if ($row !== null) {
            return $router->addRoute('listPlzServices', new Zend_Controller_Router_Route($uri, array(
                                'controller' => 'order_basis',
                                'action' => 'service',
                                'cityId' => $row['id'],
                            )));
        }

        $domainBase = Zend_Registry::get('configuration')->domain->base;
        $cityLimit = $domainBase == 'lieferando.de'? 350 : null;
        
        //district url: for all services delivering to one district (limited to 350 cityIds)
        $rows = Yourdelivery_Model_DbTable_Districts::findByDirectLink($uri, true, null, $cityLimit);
        if (is_array($rows)) {
            return $router->addRoute('listDistrictServices', new Zend_Controller_Router_Route($uri, array(
                                'controller' => 'order_basis',
                                'action' => 'service',
                                'cityId' => array_map(function($item) {
                                                return $item['id'];
                                            }, $rows)
                            )));
        }

        //region url: for all services delivering to one region/city (limited to 350 cityIds)
        $rows = Yourdelivery_Model_DbTable_Regions::findByDirectLink($uri, true, null, $cityLimit);
        if (is_array($rows)) {
            return $router->addRoute('listRegionServices', new Zend_Controller_Router_Route($uri, array(
                                'controller' => 'order_basis',
                                'action' => 'service',
                                'cityId' => array_map(function($item) {
                                                return $item['id'];
                                            }, $rows)
                            )));
        } elseif(Yourdelivery_Model_DbTable_Regions::findByDirectLink($uri, false, null, 1)) {
            // redirecting to home because of SEO issues
            // not using ZF because of performance
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: /');
            exit;
        }

        //history url, those may be changed but will be redirected to the current service url (top)
        $row = Yourdelivery_Model_DbTable_Restaurant_UrlHistory::findByDirectLink($uri);
        if ($row !== null) {
            return $router->addRoute('redirectOldUrl', new Zend_Controller_Router_Route($uri, array(
                                'controller' => 'order_basis',
                                'action' => 'redirect',
                                'mode' => $row['mode'],
                                'serviceId' => $row['restaurantId'],
                            )));
        }
    }

    /**
     * @author mlaug
     * @since 01.04.2011
     * @param Zend_Controller_Router_Interface $router
     */
    private function __initRatingRoutes(Zend_Controller_Router_Interface $router) {
        // rate an order
        $router->addRoute(
                'bewerten', new Zend_Controller_Router_Route('rate/:hash',
                        array('controller' => 'user',
                            'action' => 'bewerten'))
        );

        // rate an order with preselect from email
        $router->addRoute(
                'bewertenPreselect', new Zend_Controller_Router_Route('rate/:hash/:adviselink',
                        array('controller' => 'user',
                            'action' => 'bewerten'))
        );

        $router->addRoute(
                'danke', new Zend_Controller_Router_Route('thankyou',
                        array('controller' => 'user',
                            'action' => 'danke'))
        );
    }

    /**
     * @author mlaug
     * @since 01.04.2011
     * @param Zend_Controller_Router_Interface $router
     */
    private function __initSatelliteRoutes(Zend_Controller_Router_Interface $router) {
        // build up satellite route

        if (!isBaseUrl()) {

            //check for satellites
            $satelliteTable = new Yourdelivery_Model_DbTable_Satellite();
            $satellites = $satelliteTable->findAllByDomain(Default_Helpers_Web::getHostname());

            //routes for multiple domains
            if ($satellites->count() > 1) {

                // route
                $hostnameRoute = new Zend_Controller_Router_Route_Hostname(
                                HOSTNAME,
                                array(
                                    'controller' => 'satellite_multiple')
                );
                $pathRoute = new Zend_Controller_Router_Route_Module(array());
                $router->addRoute('satelliteRoute', $hostnameRoute->chain($pathRoute));

                //create directories
                foreach ($satellites as $satellite) {
                    //url based routes
                    
                    if ( !strstr($_SERVER['REQUEST_URI'], $satellite->url) ){
                        continue;
                    }           

                    $router->addRoute(
                            'sat-menu-index-' . $satellite->id, new Zend_Controller_Router_Route($satellite->url,
                                    array('controller' => 'satellite_multiple',
                                        'action' => 'menu',
                                        'satelliteId' => $satellite->id))
                    );

                    $router->addRoute(
                            'sat-menu-' . $satellite->id, new Zend_Controller_Router_Route($satellite->url . '/menu',
                                    array('controller' => 'satellite_multiple',
                                        'action' => 'menu',
                                        'satelliteId' => $satellite->id))
                    );

                    $router->addRoute(
                            'sat-finish-' . $satellite->id, new Zend_Controller_Router_Route($satellite->url . '/finish',
                                    array('controller' => 'satellite_multiple',
                                        'action' => 'finish',
                                        'satelliteId' => $satellite->id))
                    );

                    $router->addRoute(
                            'sat-success-' . $satellite->id, new Zend_Controller_Router_Route($satellite->url . '/success',
                                    array('controller' => 'satellite_multiple',
                                        'action' => 'success',
                                        'satelliteId' => $satellite->id))
                    );
                }

                $router->addRoute(
                        'sat-payment', new Zend_Controller_Router_Route('/order_basis/payment',
                                array('controller' => 'satellite_multiple',
                                    'action' => 'payment'))
                );
                
                $router->addRoute(
                        'sat-success-priv', new Zend_Controller_Router_Route('/order_private/success',
                                array('controller' => 'satellite_multiple',
                                    'action' => 'success'))
                );

                $router->addRoute(
                        'sat-success-comp', new Zend_Controller_Router_Route('/order_company/success',
                                array('controller' => 'satellite_multiple',
                                    'action' => 'success'))
                );

                $router->addRoute(
                        'sat-list-with-url', new Zend_Controller_Router_Route('/shops',
                                array(
                                    'controller' => 'satellite_multiple',
                                    'action' => 'list'))
                );

                $router->addRoute(
                        'sat-list-with-url-avanti', new Zend_Controller_Router_Route('/bestellen/deutschland',
                                array(
                                    'controller' => 'satellite_multiple',
                                    'action' => 'list'))
                );

                $router->addRoute(
                        'sat-list-with-url-impressum', new Zend_Controller_Router_Route('/impr',
                                array(
                                    'controller' => 'satellite_multiple',
                                    'action' => 'impressum'))
                );
                $router->addRoute(
                        'sat-list-with-url-bewerten', new Zend_Controller_Router_Route('/bewerten',
                                array(
                                    'controller' => 'satellite_multiple',
                                    'action' => 'bewerten'))
                );
            }
            //routes for single domains
            else {

                // route
                $hostnameRoute = new Zend_Controller_Router_Route_Hostname(
                                HOSTNAME,
                                array(
                                    'controller' => 'satellite')
                );
                $pathRoute = new Zend_Controller_Router_Route_Module(array());
                $router->addRoute('satelliteRoute', $hostnameRoute->chain($pathRoute));

                $router->addRoute(
                        'sat-menu', new Zend_Controller_Router_Route('/menu',
                                array('controller' => 'satellite',
                                    'action' => 'menu'))
                );

                $router->addRoute(
                        'sat-finish', new Zend_Controller_Router_Route('/finish',
                                array('controller' => 'satellite',
                                    'action' => 'finish'))
                );

                $router->addRoute(
                        'sat-payment', new Zend_Controller_Router_Route('/order_basis/payment',
                                array('controller' => 'satellite',
                                    'action' => 'payment'))
                );

                $router->addRoute(
                        'sat-success', new Zend_Controller_Router_Route('/order_basis/success',
                                array('controller' => 'satellite',
                                    'action' => 'success'))
                );

                $router->addRoute(
                        'sat-success-priv', new Zend_Controller_Router_Route('/order_private/success',
                                array('controller' => 'satellite',
                                    'action' => 'success'))
                );

                $router->addRoute(
                        'sat-success-comp', new Zend_Controller_Router_Route('/order_company/success',
                                array('controller' => 'satellite',
                                    'action' => 'success'))
                );

                $router->addRoute(
                        'sat-about', new Zend_Controller_Router_Route('/about',
                                array('controller' => 'satellite',
                                    'action' => 'about'))
                );

                $router->addRoute(
                        'sat-notfound', new Zend_Controller_Router_Route('/notfound',
                                array('controller' => 'satellite',
                                    'action' => 'notfound'))
                );

                $router->addRoute(
                        'sat-jobs', new Zend_Controller_Router_Route('/jobs',
                                array('controller' => 'satellite',
                                    'action' => 'jobs'))
                );

                $router->addRoute(
                        'sat-opinion', new Zend_Controller_Router_Route('/opinion',
                                array('controller' => 'satellite',
                                    'action' => 'opinion'))
                );
            }
        }
    }

    /**
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 03.07.2012
     * @param Zend_Controller_Router_Interface $router
     *
     * domain-dependant routes to partner backend
     */
    private function __initPartnerRoutes(Zend_Controller_Router_Interface $router) {

        $domainBase = Zend_Registry::get('configuration')->domain->base;

        // Just add domain here and the path it should use for partner backend
        // This is not needed for de,ch,fr since we have a fallback route
        $partnerRoutings = array(
            'pyszne.pl' => 'konto',
            'taxiresto.fr' => 'acces'
        );

        // adds dynamic routes
        foreach ($partnerRoutings as $domain => $partnerRoute) {
            if ($domainBase === $domain) {
                $router->addRoute('partnerRoute', new Zend_Controller_Router_Route(
                    $partnerRoute . '/:action/*',
                    array(
                        'controller' => 'partner',
                        'action' => 'index'
                    )
                ));
            }
        }

        // adds a default fallback route used for countries not in array
        if (!$router->hasRoute('partnerRoute')) {
                $router->addRoute('partnerRoute', new Zend_Controller_Router_Route(
                    'partner/:action/*',
                    array(
                        'controller' => 'partner',
                        'action' => 'index'
                    )
            ));
        }

    }

    /**
     * @author mlaug
     * @since 01.04.2011
     * @param Zend_Controller_Router_Interface $router
     */
    private function __initApiRoutes(Zend_Controller_Router_Interface $router, $ctrl) {
        // build up api route
        $rest = new Zend_Rest_Route($ctrl, array(), array(
                    'default' => array(
                        'get_service', 'get_best_service',
                        'get_order', 'get_order_favorite',
                        'get_location',
                        'get_customer', 'get_image', 'get_customer_stats', 'get_customer_fidelity',
                        'get_meal',
                        'get_ratings',
                        'get_plz',
                        'get_discount',
                        'get_call',
                        'get_settings',
                        'get_suggestion',
                        'get_test',
                        'get_feedback',
                        'get_partner_customer', 'get_partner_order'
                    )
                ));
        $router->addRoute('API_REST', $rest);
    }

    /**
     * @author mlaug
     * @since 01.04.2011
     * @param Zend_Controller_Router_Interface $router
     */
    private function __initFrameworkRoutes(Zend_Controller_Router_Interface $router) {
        //route for iframe framework (menu)
        $router->addRoute(
                'IframeFrameworkRouteMenu', new Zend_Controller_Router_Route(
                        '/if/:partner/:css/:method/:cityId/:sid',
                        array(
                            'controller' => 'iframe_framework',
                            'action' => 'error', // there will be an redirect in the init method
                        // of the controller, but if this won't work, show error
                        )
                )
        );

        //the qype route
        $router->addRoute(
                'IframeFrameworkRouteMenuQype', new Zend_Controller_Router_Route(
                        '/if/qype/:css/menu/:plz/:sid',
                        array(
                            'partner' => 'qype',
                            'method' => 'menu',
                            'controller' => 'iframe_framework',
                            'action' => 'error', // there will be an redirect in the init method
                        // of the controller, but if this won't work, show error
                        )
                )
        );

        //route for iframe framework (service)
        $router->addRoute(
                'IframeFrameworkRouteServicesPlz', new Zend_Controller_Router_Route(
                        '/if/:partner/:css/:method/:cityId/',
                        array(
                            'controller' => 'iframe_framework',
                            'action' => 'error', // there will be an redirect in the init method
                        // of the controller, but if this won't work, show error
                        )
                )
        );

        $router->addRoute(
                'IframeFrameworkRouteServices', new Zend_Controller_Router_Route(
                        '/if/:partner/:css/:method/',
                        array(
                            'controller' => 'iframe_framework',
                            'action' => 'error', // there will be an redirect in the init method
                        // of the controller, but if this won't work, show error
                        )
                )
        );

        //blank and session page for safari workaround with qype
        $router->addRoute(
                'blankRoute', new Zend_Controller_Router_Route('blank',
                        array('controller' => 'index',
                            'action' => 'blank'))
        );

        //blank and session page for safari workaround with qype
        $router->addRoute(
                'sessionRoute', new Zend_Controller_Router_Route('startsession',
                        array('controller' => 'index',
                            'action' => 'startsession'))
        );
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 14.02.2012
     * @param Zend_Controller_Router_Interface $router
     */
    public function __initDiscountRoutes(Zend_Controller_Router_Interface $router) {

        $uri = @parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($uri === false) {
            return;
        }
        $uri = substr($uri, 1);

        //must match a certian regular expression to trigger database
        if (preg_match('/request|administration|autocomplete|^user\/|^order_/', $uri)) {
            return;
        }

        $coops = Yourdelivery_Model_Rabatt::getRabattRoutes();
        foreach ($coops as $coop) {


            $router->addRoute(
                    sprintf('discount-%s-1', $coop), new Zend_Controller_Router_Route(sprintf('%s/:action/code/:code', $coop),
                            array(
                                'controller' => 'discount',
                                'referer' => $coop
                            )
                    )
            );

            $router->addRoute(
                    sprintf('discount-%s-2', $coop), new Zend_Controller_Router_Route(sprintf('%s/:action', $coop),
                            array(
                                'controller' => 'discount',
                                'referer' => $coop
                            )
                    )
            );

            $router->addRoute(
                    sprintf('discount-%s-3', $coop), new Zend_Controller_Router_Route(sprintf('%s/', $coop),
                            array(
                                'controller' => 'discount',
                                'action' => 'index',
                                'referer' => $coop
                            )
                    )
            );
        }
    }

    /**
     * @author mlaug
     */
    public function _initSession() {
        $this->bootstrap('registry');
        $config = Zend_Registry::get('configuration');
        $session_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/session.ini', APPLICATION_ENV);

        switch ($config->sessionstorage->method) {
            default:
            case 'mysql':
                Zend_Session::setOptions($session_config->toArray());
                Zend_Session::setSaveHandler(new Yourdelivery_Session_Handler(array(
                            'name' => 'session',
                            'primary' => 'id',
                            'modifiedColumn' => 'modified',
                            'dataColumn' => 'data',
                            'lifetimeColumn' => 'lifetime'
                                )
                        )
                );
                break;

            case 'couchdb':
                Zend_Session::setOptions($session_config->toArray());
                Zend_Session::setSaveHandler(new Yourdelivery_Session_CouchDb());
                break;
        }
    }

    /**
     * alter view to use smarty instead of Zend_Layout
     * @author mlaug
     */
    protected function _initView() {

        $registry = null;
        $session = null;

        $this->bootstrap('registry');
        $this->bootstrap('session');
        $this->bootstrap('caching');
        $this->bootstrap('logging');
        $registry = $this->getResource('registry');
        $config = $registry->configuration;

        Zend_Loader::loadClass('Smarty', array(APPLICATION_PATH . '/../library/Smarty/'));
        $view = new Default_View_Smarty($config);

        /**
         * assign some default variable to our smarty view
         */
        $session = new Zend_Session_Namespace('Default');

        // assign some smarty stuff
        $view->assign('root', $config->hostname . '/');
        $view->assign('domain_base', $config->domain->base);

        $subdomain = Default_Helpers_Web::getSubdomain();
        $view->assign('SUBDOMAIN', $subdomain);

        $view->assign('canonical', '');
        $view->assign('domain_js', $config->domain->js);
        $view->assign('domain_css', $config->domain->css);
        $view->assign('domain_static', $config->domain->static);

        if (isset($config->domain->timthumb)) {
            if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] == 443) {
                $timthumb = 'https://' . $config->domain->timthumb;
                $view->assign('ssl', true);
            } else {
                $timthumb = 'http://' . $config->domain->timthumb;
                $view->assign('ssl', false);
            }
        } else {
            $view->assign('ssl', false);
            $timthumb = '';
        }
        $view->assign('timthumb', $timthumb);

        $view->assign('config', $config);
        $view->assign('bits', '../../../scripts/');
        $view->assign('host', $config->hostname);
        $view->assign('randomNumber', rand(1, 9));
        $view->assign('APPLICATION_ENV', APPLICATION_ENV);
        $view->assign('HOSTNAME', HOSTNAME);

        //if we got this customer from any cooperations, we mark the finish page
        $view->assign('PARTNER', $session->partner);

        // bad weather info
        $view->assign('BADWEATHERINFO', $config->ordering->info->badweather);
        $build = realpath($_SERVER["SCRIPT_FILENAME"]);

        $view->assign('NODE', $registry->node);
        $view->assign('BUILD', $build);
        $view->assign('REVISION', sha1($build));

        /**
         * add configured taxes
         */
        $view->assign('taxes', $config->tax->types->toArray());
        $view->assign('googleAccounts', $config->google->ua->toArray());

        /**
         * init some values, which are used on each and every template
         */
        $view->assign('order', null);
        $view->assign('service', null);
        $view->assign('mode', 'rest');
        $view->assign('grid', null);
        $view->assign('city', null);

        /**
         * init helpers
         */
        $openingsFormat = new Default_View_Helper_Openings_Format();
        $view->registerHelper($openingsFormat, 'formatOpenings');
        $view->registerHelper($openingsFormat, 'formatOpeningsAsJson');
        $view->registerHelper($openingsFormat, 'formatOpeningsAsSelect');
        $view->registerHelper($openingsFormat, 'formatOpeningsMerged');
        /**
         * assign smarty view to rendering engine
         * set as default
         */
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setView($view)
                ->setViewBasePathSpec($config->smarty->template_dir)
                ->setViewScriptPathSpec(':controller/:action.:suffix')
                ->setViewScriptPathNoControllerSpec(':action.:suffix')
                ->setViewSuffix('htm');
        Zend_Registry::set('view', $view);
        return $view;
    }

    /**
     * create our logging facility
     * @author mlaug
     */
    protected function _initLogging() {
        $this->bootstrap('registry');
        $this->bootstrap('session');

        $registry = $this->getResource('registry');
        $config = $registry->configuration;
        $logger = new Yourdelivery_Log();
        $file_logger = new Zend_Log_Writer_Stream(
                        sprintf($config->logging->file, date('d-m-Y'))
        );

        $logger->addWriter($file_logger);

        if (!IS_PRODUCTION) {
            $firebug_logger = new Zend_Log_Writer_Firebug();
            $logger->addWriter($firebug_logger);
        }


        if (IS_PRODUCTION) {
            $filter = new Zend_Log_Filter_Priority(Zend_Log::INFO);
            $logger->addFilter($filter);
        }

        $registry->logger = $logger;
    }

    /**
     * create caching interface
     * @author mlaug
     */
    protected function _initCaching() {

        if (php_sapi_name() == 'cli' && APPLICATION_ENV != "testing") {
            return null;
        }

        $this->bootstrap('registry');
        $this->bootstrap('logging');
        $registry = $this->getResource('registry');
        $config = $registry->configuration;

        if (!$config->cache->use) {
            $registry->cache = null;
            return null;
        }

        //even if file exists, retry after 10 minutes
        $blockfile = APPLICATION_PATH . '/configs/block_memcache';
        if (file_exists($blockfile)) {
            $filemtime = filemtime($blockfile);
            if (time() > ($filemtime + 3600)) {
                unlink($blockfile);
            }
        }

        //SETUP CACHING
        $oBackend = new Zend_Cache_Backend_Memcached(
                        array(
                            'servers' => array(array(
                                    'host' => $config->memcached->host,
                                    'port' => $config->memcached->port
                            )),
                            'compression' => true
                ));

        // configure caching logger
        if ($config->cache->logging) {
            $oCacheLog = new Zend_Log();
            $oCacheLog->addWriter(new Zend_Log_Writer_Stream('/tmp/memcache-' . APPLICATION_ENV . '.log'));
        } else {
            $oCacheLog = null;
        }

        // configure caching frontend strategy
        $oFrontend = new Zend_Cache_Core(
                        array(
                            'caching' => true,
                            'lifetime' => 60 * 60, //one hour
                            'cache_id_prefix' => str_replace('-', '', str_replace('.', '', $config->domain->base)),
                            'logging' => $config->cache->logging,
                            'logger' => $oCacheLog,
                            'write_control' => true,
                            'automatic_serialization' => true,
                            'ignore_user_abort' => true
                ));

        // build a caching object
        $cache = Zend_Cache::factory($oFrontend, $oBackend);

        try {
            $m = new Memcache();
            $m->addServer($config->memcached->host, $config->memcached->port);
            $version = $m->getversion();
            if ($version === false) {
                throw new Exception('could not connect to memcache server');
            }

            Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
            $registry->cache = $cache;
        } catch (Exception $e) {
            $registry->logger->crit(sprintf('could not find out memcache version on server %s, so this server will not use memcache for the next 10 minutes', $registry->node));
            touch(APPLICATION_PATH . '/configs/block_memcache');
            return null;
        }
    }

    /**
     * @author mlaug
     */
    protected function _initAccess() {
        $restrictedResources = array(
            'company' => array(),
            'user' => array('login', 'loginfailed')
        );
        $registry = $this->getResource('registry');
        $registry->restricted = $restrictedResources;
    }

    /**
     * Set up mail transport
     * @author vpriem
     * @since 16.11.2010
     */
    protected function _initTransport() {
        $this->bootstrap('registry');
        $registry = $this->getResource('registry');
        $config = $registry->configuration;

        // set default sender
        Zend_Mail::setDefaultFrom($config->sender->email->from, $config->sender->email->name);
    }

    /**
     * make sure all pages are set to max-age=0
     *
     * @author Matthias Laug
     * @since 19.06.2012
     */
    public function _initSetHttpCaching(){
        header('Cache-Control: no-cache', true);
        header('Pragma: no-cache', true);
        header(sprintf('Date: %s',date('D, d M Y H:i:s \G\M\T',0)),true);
        header(sprintf('Expires: %s', date('D, d M Y H:i:s \G\M\T',0)), true);
    }

    /**
     * helper to bootstrap again, but only in testing
     * @author mlaug
     * @since 12.07.2011
     * @param string $bootstrap
     */
    public function testHelper($bootstrap) {
        if (APPLICATION_ENV == 'testing') {
            $method = '_init' . ucfirst(strtolower($bootstrap));
            if (method_exists($this, $method)) {
                $this->$method();
            }
        }
    }

}

/**
 * @author mlaug
 * @since 19.08.2010
 * @param string $string
 */
function __con($string, $linebreak = true) {
    if (APPLICATION_ENV == "console") {
        $stdout = fopen('php://stdout', 'w');
        fwrite($stdout, $string . $linebreak ? "\n" : "");
        fclose($stdout);
    }
}

/**
 * Define some hooks
 * @author mlaug
 * @since 29.09.2010 (vpriem)
 * @param Yourdelivery_Model_Order_Abstract $order
 * @param int $pickupTimestamp
 * @return boolean
 */
function hook_after_fax_is_ok(Yourdelivery_Model_Order_Abstract $order) {

    $service = $order->getService();

    $courier = $service->getCourier();
    if (!$courier instanceof Yourdelivery_Model_Courier) {
        return false;
    }

    switch ($courier->getApi()) {
        case "prompt":
            // check if a trackingId exists
            $tableTracking = new Yourdelivery_Model_DbTable_Prompt_Tracking();
            $trackings = $tableTracking->getByOrder($order->getId());
            if (count($trackings)) {
                return true;
            }

            // book order through api of prompt
            $api = new Yourdelivery_Model_Api_Prompt($order);
            $rateId = $api->rates();
            if ($rateId !== false) {
                if ($api->book($rateId) !== false) {
                    return true;
                }
            }

            break;

        case "interkep":
            $api = new Yourdelivery_Api_Interkep($order);
            return $api->send();

            break;
    }

    return false;
}
