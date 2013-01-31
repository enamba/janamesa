<?php

/**
 * Abstract class for any order process
 *
 * @author mlaug
 */
class Order_BasisController extends Default_Controller_Base {

    protected $_orderClass = "Yourdelivery_Model_Order_Private";

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 17.04.2012
     * @return redirect
     */
    public function citystreetAction() {
        $this->setCache(28800); //8 hours
        $this->_disableView();
        $request = $this->getRequest();

        // rest url is now fix
        $col = "restUrl";
        $paramString = $this->getAppendableParams(array(
            'city' => $request->getParam('city'),
            'street' => $request->getParam('street')
                ));

        //the match will be the cityId
        $cityId = (integer) $request->getParam('cityId', 0);

        if ($cityId <= 0) {
            $form = new Yourdelivery_Form_Order_Start_Citystreet();
            //validates that street, number and city match upxx (must be strict)
            if ($form->isValid($request->getPost())) {
                $cityVerbose = new Yourdelivery_Model_City_Verbose();
                $matches = $cityVerbose->findmatch(
                        $form->getValue('city'), $form->getValue('street'), $form->getValue('hausnr', null)
                );
                if (count($matches) == 1) {
                    $data = array_pop($matches);
                    $cityId = (integer) $data['cityId'];
                    $verboseId = (integer) $data['vId'];

                    //store that verbose information in cookie
                    $state = Yourdelivery_Cookie::factory('yd-state');
                    $state->set('verbose', $verboseId);
                    $state->save();
                } else {
                    //nothing or more than one found, we need the number
                }
            }
        }

        //at this point, we should have gathered a match
        if ($cityId > 0) {
            $cache = md5(sprintf('redirectCity%s%s%s', $col, $cityId, md5($paramString)));
            $redirect = Default_Helpers_Cache::load($cache);
            if ($redirect) {
                $this->logger->debug(sprintf('ORDER - BASIS - cityStreet: loaded from cache with params %s - will redirect to %s', $paramString, $redirect));
                return $this->_redirect($redirect);
            }

            try {
                $city = new Yourdelivery_Model_City($cityId);
                Default_Helpers_Cache::store($cache, $city->getUrl('rest') . $paramString);

                $paramString = preg_replace('/\?(city|street|hausnr)=(.*)/', '', $paramString);

                return $this->_redirect($city->getUrl('rest') . $paramString);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

            }
        }
        //no match found, indicate plz error on index page
        return $this->_redirect('/' . $paramString . '#plzerror');
    }

    /**
     * @author mlaug
     * @since 29.09.2011
     * @modifier vpriem, 29.09.2011
     */
    public function plzAction() {
        $this->setCache(28800); //8 hours
        $this->_disableView();
        $request = $this->getRequest();

        // get plz and remove everything which is no a number
        $plz = preg_replace('/[^0-9\-]/', '', $request->getParam('plz'));
        if ($plz === null || $plz <= 0) {
            $this->logger->warn('SEM_CEP_PARA: ' . $plz);
            return $this->_redirect('/#plzerror');
        }

        //store last area in cookie
        $cookie = Yourdelivery_Cookie::factory('yd-recurring');
        $cookie->set('lastarea', $plz);
        $cookie->save();

        // rest url is now fix
        $col = "restUrl";
        $comida = $request->getParam('comida', null);
        $paramString = $this->getAppendableParams(array("comida"=>$comida));

        if ($comida !== null){
            $paramString = '/comida/' . $comida;
        } else {
            $paramString = '';
        }
        
        $cache = md5(sprintf('redirect%s%s%s', $col, $plz, md5($paramString)));
        $redirect = Default_Helpers_Cache::load($cache);
        if ($redirect) {
            return $this->_redirect($redirect);
        }

        $rows = Yourdelivery_Model_City::getByPlz($plz);
        foreach ($rows as $row) {
            if (!$row->parentCityId) {
                Default_Helpers_Cache::store($cache, $row->$col . $paramString);
                return $this->_redirect($row->$col . $paramString);
            }
        }
        $this->logger->warn('SEM_CEP_PARA: ' . $plz);
        
        return $this->_redirect('/' . $paramString . '#plzerror');
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    protected function getAppendableParams($additional = array()) {
        //remove those, which should not be appended
        $params = array_flip(array_filter(array_flip($_GET), function($key) {
                            return !in_array($key, array('mode', 'plz', 'cityId', 'empty'));
                        }));

        $params = array_merge($additional, $params);

        //do the appendix :)
        $paramString = '';
        if (count($params) > 0) {
            $paramString = '?';
            foreach ($params as $key => $value) {
                $paramString .= sprintf('%s=%s&', $key, $value);
            }
            $paramString = substr($paramString, 0, -1);
        }
        return $paramString;
    }

    /**
     * @author vpriem
     * @since 05.09.2011
     */
    public function startAction() {
        $request = $this->getRequest();

        if (!$this->getCustomer()->isLoggedIn()) {
            return $this->_redirect('/');
        }
        $this->view->extra_css = 'step1';

        // get given mode
        $mode = $request->getParam("mode");
        if ($mode === null || !in_array($mode, array("rest", "cater", "great"))) {
            $mode = "rest";
        }
        $this->view->mode = $mode;
    }

    /**
     * get all services located in one city area
     * @author mlaug
     * @since 07.07.2011
     */
    public function serviceAction() {
        $this->view->extra_css = 'step2';
        $this->view->GADocumentReady = true;
        $this->view->enableCache();

        $request = $this->getRequest();
        

        $this->view->comida = $request->getParam('filterTag')?$request->getParam('filterTag') : $request->getParam('comida', '');

        //this element maybe an integer or a list of cityIds
        $cityIds = $request->getParam('cityId', 0);

        //wrap an array around that
        if (!is_array($cityIds)) {
            $cityIds = array((integer) $cityIds);
        } else {
            $this->logger->info(sprintf('getting %s cityIds for url %s', count($cityIds), $_SERVER['REQUEST_URI']));
        }

        if (count($cityIds) == 0) {
            $this->logger->warn('no cityIds given in service action');
            return $this->_redirect('/');
        }

        /**
         * @author Matthias Laug <laug@lieferando.de>
         * @since 17.04.2012
         *
         * we use the first cityId as the reference
         */
        $location = null;
        $this->view->cityIds = $cityIds;
        $cityId = (integer) current($cityIds); //get the first element as reference
        try {
            if ($cityId <= 0) {
                $this->logger->debug('got no city id, searching for plz parameter');
                $plz = $request->getParam('plz', null);
                if ($plz === null) {
                    //check if we got any plz
                    throw new Yourdelivery_Exception_Database_Inconsistency();
                } else {
                    $this->logger->debug(sprintf('got a plz %s, trying to find cityIds', $plz));
                    $cityIds = (array) Yourdelivery_Model_City::getByPlz($plz);
                    if (count($cityIds) == 1) {
                        $this->logger->debug('found just one city id, going on');
                        $cityId = (integer) array_pop($cityIds);
                    } elseif (count($cityIds) > 1) {
                        $this->logger->debug(sprintf('found %d cityIds, redirecting so that customer can select', count($cityIds)));
                        return $this->_redirect(sprintf('order_basis/city?plz=%s', $plz));
                    } else {
                        throw new Yourdelivery_Exception_Database_Inconsistency('found no city id by plz ' . $plz);
                    }
                }
            }
            $city = new Yourdelivery_Model_City($cityId);
            $location = new Yourdelivery_Model_Location();
            $location->setCityId($cityId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->warn(sprintf('the given cityId %d could not be found, redirecting to start page', $cityId));
            return $this->_redirect('/');
        }

        //add reference element to view
        $this->view->location = $location;
        $this->view->city = $city;

        //meta tags
        $meta = array();
        $city_plz = null;
        if ($location->getDepth() == 'plz') {
            $city_plz = $location->getPlz();
        }

        $city_name = $location->getCity()->getCity();
        $city_parent_name = $city_name;
        if (($parentCityId = $location->getCity()->getParentCityId())) {
            $parentCity = new Yourdelivery_Model_City($parentCityId);
            $city_parent_name = $parentCity->getCity();
        }
        $city_complete = trim($location->getTitle());
        switch ($this->config->domain->base) {
            default:
                break;
            case 'eat-star.de':
                $this->view->customTitle = sprintf('In %s Essen bestellen beim Lieferservice!', $city_complete);
                $meta[] = sprintf('<meta name="description" content="Für %s Lieferservice und Pizzaservice finden! Essen bestellen nach Wunsch in %s! Superschnell und superlecker - direkt an ihre Haustür" />', $city_complete, $city_name);
                break;
            case 'lieferando.ch':
            case 'lieferando.at':
            case 'lieferando.de':
                if ($city_plz) {
                    $this->view->customTitle = sprintf('Lieferservice %s in %s - Essen online bestellen in %s', $city_parent_name, $city_plz, $city_name);
                    $meta[] = sprintf('<meta name="description" content="Beim Lieferservice %s in %s Essen online bestellen. Zahlreiche Lieferservices in %s finden! %s eingeben & bargeldlos zahlen!" />', $city_parent_name, $city_plz, $city_name, $city_plz);
                } else {
                    $this->view->customTitle = sprintf('Lieferservice %s - günstig Essen online bestellen in %s', $city_parent_name, $city_complete);
                    $meta[] = sprintf('<meta name="description" content="Beim Lieferservice %s schnell & günstig Essen online bestellen. Finden Sie Lieferservices in %s ✓ Essen bestellen ✓ bargeldlos zahlen!" />', $city_parent_name, $city_complete);
                }
                break;
            case 'taxiresto.fr':
                $this->view->customTitle = sprintf('Trouvez un restaurant à %s et passez commande en ligne!', $city_complete);
                if ($city_plz) {
                    $meta[] = sprintf('<meta name="description" content="Commandez dès à présent vos repas à %s et découvrez notre sélection de restaurants livrant à %s. Faites-vous livrer à %s et payez en ligne si vous le souhaitez !" />', $city_name, $city_complete, $city_plz);
                } else {
                    $meta[] = sprintf('<meta name="description" content="Commandez dès à présent vos repas à %s et découvrez notre sélection de restaurants livrant à %s !" />', $city_name, $city_complete);
                }
                break;
            case 'smakuje.pl':
                $this->view->customTitle = sprintf('Jedzenie z dowozem %s – zamów jedzenie na telefon lub online w restauracji!', $city_name);
                $meta[] = sprintf('<meta name="description" content="Znajdź restauracje lub pizzerie w %s! Zamów ulubione jedzenie w %s! Szybka dostawa i smaczne jedzenie – prosto do drzwi!" />', $city_name, $city_name);
                break;
            case 'pyszne.pl':
                $this->view->customTitle = sprintf('Jedzenie z dowozem %s – zamów jedzenie na telefon lub online w restauracji!', $city_name);
                $meta[] = sprintf('<meta name="description" content="Znajdź restauracje lub pizzerie w %s! Zamów ulubione jedzenie w %s! Szybka dostawa i smaczne jedzenie – prosto do drzwi!" />', $city_name, $city_name);
                break;
        }

        $meta[] = '<meta name="robots" content="index,follow" />';

        $this->view->assign('additionalMetatags', $meta);

        $this->view->services = $services = Yourdelivery_Model_Order_Abstract::getServicesByCityId($cityIds, null, 150);
        $this->view->offlineServices = $offlineServices = Yourdelivery_Model_Order_Abstract::getOfflineServicesByCityId($cityIds, null, 30);
        $this->view->ydcategories = Yourdelivery_Model_Servicetype_Categories::getCategoriesByCityId($cityIds);

        $this->logger->info(sprintf('found %d online and %d offline services in #%s', count($services), count($offlineServices), implode(',', $cityIds)));

        $this->setCache(28800); //8 hours
    }

    /**
     * @authr vpriem
     * @since 07.07.2011
     */
    public function menuAction() {
        $this->view->extra_css = 'step3';
        $this->view->enableCache();
        $request = $this->getRequest();

        $serviceId = (integer) $request->getParam('serviceId', 0);
        if (!$serviceId) {
            throw new Yourdelivery_Exception("No serviceId provided");
        }

        $mode = $this->view->mode = $request->getParam('mode');
        if ($mode === null) {
            throw new Yourdelivery_Exception("No mode provided");
        }

        // try to create service
        try {
            switch ($mode) {
                default:
                case Yourdelivery_Model_Servicetype_Abstract::RESTAURANT:
                    $service = new Yourdelivery_Model_Servicetype_Restaurant($serviceId);
                    break;

                case Yourdelivery_Model_Servicetype_Abstract::CATER:
                    $service = new Yourdelivery_Model_Servicetype_Cater($serviceId);
                    break;

                case Yourdelivery_Model_Servicetype_Abstract::GREAT:
                    $service = new Yourdelivery_Model_Servicetype_Great($serviceId);
                    break;

                default:
                    throw new Yourdelivery_Exception("Wrong mode provided");
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->warn(sprintf('no valid serviceId given in service action %s', $serviceId));
            return $this->_redirect('/');
        }

        // assign service to view
        $this->view->mode = $mode;
        $this->view->service = $this->view->currentservice = $service;
        list($menu, $parents) = $service->getMenu();
        $this->view->menu = $menu;
        $this->view->parents = $parents;

        // try to find plz if it was provided
        $plz = $request->getParam('plz');
        if (!empty($plz)) {
            $cityIds = array();
            $ranges = $service->getRanges(1000, true);
            foreach ($ranges as $range) {
                if (strpos($range['cityname'], $plz) !== false) {
                    $cityIds[] = $range['cityId'];
                }
            }
            $this->view->cityIds = $cityIds;
        }

        // add topseller
        if ($service->useTopseller() && $mode == 'rest') {
            $maxSizes = 1;
            $bestSeller = $service->getBestSeller(10);
            foreach ($bestSeller as $best) {
                $maxSizes = count($best->getSizes()) > $maxSizes ? count($best->getSizes()) : $maxSizes;
            }
            $this->view->topSellerCountSizes = $maxSizes;
        }

        // assign meta tags to view
        if ($service->getMetaTitle() != null) {
            $title = $service->getMetaTitle();
        } else {
            $title = __("%s Lieferservice %s %s, %s bestellen", $service->getName(), $service->getCategory()->name, $service->getOrt()->getOrt(), $service->getCategory()->name);
        }

        // meta tags
        $meta = array();

        //if this is a service, we only use this as a canonical
        if ($service->isRestaurant()) {
            $meta[] = sprintf('<link rel="canonical" href="http://www.%s/%s">', $this->config->domain->base, $service->getRestUrl());
        }

        // eatstar is connected to the lieferando.de database, so we need to hack that!
        if ($this->config->domain->base == 'eat-star.de') {
            $this->view->customTitle = sprintf('Bei %s lecker %s Essen bestellen in %s', $service->getName(), $service->getCategory()->name, $service->getCity()->getCity());
            $meta[] = sprintf('<meta name="description" content="Jezt in %s lecker %s Essen Bestellen! Bei %s auf Eat-Star.de bestellen und bargeldlos zahlen - Lieferservice direkt an die Haustür in %s" />', $service->getCity()->getCity(), $service->getCategory()->name, $service->getName(), $service->getCity()->getCity());
            $meta[] = '<meta name="robots" content="noindex,follow" />';
        } else {
            if ($service->getMetaDescription() !== null) {
                $meta[] = '<meta name="description" content="' . $service->getMetaDescription() . '" />';
            } else {
                $meta[] = '<meta name="description" content="' . __("%s %s Lieferservice %s im Überblick. Alle Informationen auf einen Blick. Bequem %s bestellen, bargeldlos zahlen bei %s.", $service->getOrt()->getOrt(), $service->getCategory()->name, $service->getName(), $service->getCategory()->name, $service->getName()) . '" />';
            }
            if ($service->getMetaKeywords() !== null) {
                $meta[] = '<meta name="keywords" content="' . $service->getMetaKeywords() . '" />';
            } else {
                $meta[] = '<meta name="keywords" content="' . __("%s Lieferservice %s %s essen bestellen Kreditkarte bargeldlos Heimservice Bringdienst", $service->getName(), $service->getCategory()->name, $service->getOrt()->getOrt()) . '" />';
            }
            if ($service->getMetaRobots() !== null) {
                $meta[] = '<meta name="robots" content="' . $service->getMetaRobots() . '" />';
            } else {
                $meta[] = '<meta name="robots" content="index,follow" />';
            }
            $this->view->customTitle = $title;
        }

        $this->view->additionalMetatags = $meta;
        $service->buildRedirectCache();
        $this->setCache(28800); //8 hours
    }

    /**
     * Pre-finish logic
     * @author vpriem
     * @since 14.07.2011
     * @param Yourdelivery_Model_Order_Abstract $order
     * @return boolean
     */
    protected function _preFinish(Yourdelivery_Model_Order_Abstract $order, array &$post) {
        return true;
    }

    /**
     * Finish logic
     * @author vpriem
     * @since 14.07.2011
     * @param Yourdelivery_Model_Order_Abstract $order
     * @return boolean
     */
    protected function _finish(Yourdelivery_Model_Order_Abstract $order) {
        return true;
    }

    /**
     * Post-finish logic
     * @author vpriem
     * @since 14.07.2011
     * @param Yourdelivery_Model_Order_Abstract $order
     * @return boolean
     */
    protected function _postFinish(Yourdelivery_Model_Order_Abstract $order) {
        return true;
    }

    /**
     * if the order process is not enabled, we stop the customer on
     * this page and just display all the information he need to order via
     * telephone
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.02.2012
     */
    public function finishstopAction() {
        //define template, to work with company if anyone ever hits this page
        //from a company account

        $this->view->order = $this->getRequest()->getParam('order');
        $this->view->service = $this->getRequest()->getParam('service');
        $this->view->setDir('order/basis');
        $this->view->setName('finishstop.htm');
        $this->setCache(0);
    }

    /**
     * @author vpriem
     * @since 14.07.2011
     */
    public function finishAction() {
        $this->setCache(0);

        $meta[] = '<meta name="robots" content="noindex,follow" />';
        $this->view->assign('additionalMetatags', $meta);
        $this->view->extra_css = 'step4';

        // get request
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $this->view->post = $request->getPost();
        } else {
            $this->logger->warn('hit finish page without posting, redirect to start page');
            return $this->_redirect('/');
        }

        /**
         * We recieve the order data from the menu page
         */
        // if this order is not to be finished, we delete it from session
        try {
            // get state
            $state = Yourdelivery_Cookie::factory('yd-state');

            // setup order
            $order = new $this->_orderClass();
            $order->setup(
                    $this->getCustomer(), $post['mode']
            );

            //in the form of a satellite, we have a hidden filed "satellite"
            //which indicated that this order has been placed from a satellite
            //this field is needed to mark this order for the invoices
            if (isset($post['satellite'])) {
                $order->setSatellite(htmlentities($post['satellite']));
            }
            $this->view->order = $order;
            $this->view->mode = $order->getMode();

            /**
             * @todo: we should check here what is the best workflow
             * to determine if location is set or not. For instance, if the
             * locationId differs from the given cityId in the cookie? Or if the
             * locationId for a company order is not valid!
             */
            // setup location
            $cityId = (integer) $post['cityId'];
            $locationId = (integer) $state->get('location');
            try {
                $location = new Yourdelivery_Model_Location($locationId); //if the locationId is 0, we do not mind
                $oldCityId = $location->getCityId();
                if ($oldCityId != $cityId) {
                    $location->setCityId($cityId); // we overwrite the cityId to make sure, the service is in range
                    $location->setStreet('');      // we overwrite street and hausnr because it can't fit to the cityId
                    $location->setHausnr('');
                }
                $city = $location->getCity(); // this should throw a exception if the cityId was not correct
                //get additional information if any available
                $info = $this->view->verbose = $city->getVerboseInformation($cityId);
                if (is_array($info) && count($info) > 0) {
                    $verboseId = (integer) $state->get('verbose');
                    $location->setNumber($state->get('number'));
                    try {
                        if ($verboseId <= 0) {
                            throw new Yourdelivery_Exception_Database_Inconsistency();
                        }
                        $verbose = new Yourdelivery_Model_City_Verbose($verboseId);
                        $newStreet = $verbose->getStreet();
                        if (!empty($newStreet)) {
                            $location->setStreet($newStreet);
                            $location->setHausnr('');
                        }
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        //use first selection as fallback
                        $this->logger->info('appending fallback verbose information to order via cityId ' . $cityId);
                        if (!empty($newStreet)) {
                            $location->setStreet($info['street']);
                            $location->setHausnr('');
                        }
                    }
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->logger->crit('post data did not provide a valid city Id: ' . $cityId);
                return $this->_redirect('/');
            }
            $order->setLocation($location);
            if ($this->config->domain->base == 'taxiresto.fr' && isset($post['digicode'])) {
                $post['comment'] = $post['comment'] . "\nDigicode: " . $post['digicode'];
            }
            if (strlen($post['comment']) > 0) {
                $order->getLocation()->setComment($post['comment']);
            }
            if (strlen($post['etage']) > 0) {
                $order->getLocation()->setEtage($post['etage']);
            }

            //add cpf for janamesa
            $order->setCpf(htmlentities($post['cpf']));

            // setup service
            $serviceId = (integer) $post['serviceId'];
            if ($serviceId <= 0) {
                throw new Yourdelivery_Exception_MissingOrderData('service id is missing');
            }
            $service = $order->getServiceClass();
            try {
                $service->load($serviceId);
                $service->setCurrentCityId($cityId);
                if (!$service->isOnline($order->getCustomer(), $order->getKind())) {
                    //you should have never been able to select this one!!!
                    $this->error(__('Dieser Dienstleister steht leider nicht mehr zur Verfügung'));
                    $this->logger->err(sprintf('user selected a service which should not be available to him, customer #%s %s, service #%s %s - redirecting to /', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $serviceId, $service->getName()));
                    return $this->_redirect('/');
                }
                $order->setService($service);
                $this->view->service = $service;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                throw new Yourdelivery_Exception_MissingOrderData('could not find service by id ' . $serviceId);
            }

            // build up cart
            if (is_array($post['meal'])) {
                foreach ($post['meal'] as $mealData) {
                    try {
                        $sizeId = (integer) $mealData['size'];
                        $mealId = (integer) $mealData['id'];
                        if ($mealId <= 0 || $sizeId <= 0) {
                            throw new Yourdelivery_Exception_Database_Inconsistency();
                        }
                        $meal = new Yourdelivery_Model_Meals($mealId);
                        $meal->setCurrentSize($sizeId);

                        $options = array();
                        $mealoptions = array();
                        $extras = array();

                        //build up options
                        if (isset($mealData['options']) && is_array($mealData['options'])) {
                            foreach ($mealData['options'] as $opt) {
                                array_push($options, $opt); //we append a list a ids here
                            }
                        }

                        //build up mealoptions
                        if (isset($mealData['mealoptions']) && is_array($mealData['mealoptions'])) {
                            foreach ($mealData['mealoptions'] as $opt) {
                                array_push($mealoptions, $opt); //we append a list a ids here
                            }
                        }

                        //build up extras
                        if (isset($mealData['extras']) && is_array($mealData['extras'])) {
                            foreach ($mealData['extras'] as $ext) {
                                array_push($extras, $ext); //we append a list of arrays(id, count) here
                            }
                        }

                        $opt_ext = array(
                            'options' => $options,
                            'mealoptions' => $mealoptions,
                            'extras' => $extras,
                            'special' => $mealData['special']
                        );
                        $order->addMeal($meal, $opt_ext, (integer) $mealData['count']);
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->logger->warn(sprintf('failed adding meal with id %d with size %d', $mealId, $sizeId));
                    }
                }
            }

            //order process is not enabled to be finalized, forwarding to static
            //finish page
            if ($this->config->order->finish == 0) {
                //add order and service to request so it is usable in finishstop
                $this->getRequest()->setParam('order', $order);
                $this->getRequest()->setParam('service', $service);
                return $this->_forward('finishstop');
            }


            $discountEnteredDirectly = null;
            $discountCode = isset($post['discount']) && strlen($post['discount']) > 0 ? $post['discount'] : null;
            //dectivate until neccessary checks are in place

            if ($discountCode && $post['fidelity'] == 0) {
                try {
                    $code = new Yourdelivery_Model_Rabatt_Code(htmlentities($discountCode));
                    if ($code->isUsable()) {
                        $order->setDiscount($code);

                        //check for the minamount
                        $minamount = $order->getDiscount()
                                ->getParent()
                                ->getMinAmount();
                        if ($minamount > $order->getBucketTotal(null, true)) {
                            $order->setDiscount(null);
                            $this->logger->warn(sprintf('User tried to finish an order with discount below min amount'));
                            $this->error(__(sprintf('Der Mindestbestellwert bei dem eingelösten Gutschein ist %s €', intToPrice($minamount))));
                            return false;
                        }
                    } else {
                        $this->logger->warn(sprintf('submited a discount %s in form, which is not usable', $code->getCode()));
                        if ($discountEnteredDirectly !== null) {
                            throw new Yourdelivery_Exception_Database_Inconsistency();
                        }
                    }
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->logger->warn(sprintf('trying to add discount %s, but failed to find this one', $post['discount']));
                    if ($discountEnteredDirectly !== null) {
                        $this->logger->info(sprintf('user entered code without validation, give him the chance to revalidate'));
                        $this->warn(__('Dein Gutscheincode %s konnte nicht validiert werden', $discountEnteredDirectly));
                        return false;
                    }
                }
            } else {
                if (!isset($post['finish']) && $post['fidelity'] == 0 && $this->getCustomer()->isLoggedIn() && $this->getCustomer()->getDiscount() instanceof Yourdelivery_Model_Rabatt_Code && !$order->getService()->isOnlycash()) {
                    /**
                     * @author Felix Haferkorn <haferkorn@lieferando.de>
                     * @since 15.11.2011
                     *
                     * this additional "!isset($post['finish'])" is for only adding permanent discount to order view on finish-page
                     * customer should have the oppertunity to remove permanent discount and order bar
                     */
                    $order->setDiscount($this->getCustomer()->getDiscount());
                    $this->view->discount = $this->getCustomer()->getDiscount();
                }
            }

            /**
             * check the minamount. If this is an company order and the company is one within a certian id range, we will accept any amount
             * of the current order. this is an exception made for some companys like JVM
             * @author mlaug
             */
            if ($order->getKind() == 'priv' || !$this->getCustomer()->isEmployee() || !in_array($this->getCustomer()->getCompany()->getId(), array(1443, 1097))) {
                $minamount = $order->getService()->getMinCost();
                if ($minamount > $order->getBucketTotal(null, true)) {
                    $this->logger->warn(sprintf('User tried to finish an order below min amount %s > %s', $minamount, $order->getBucketTotal(null, true)));
                    $this->error(__(sprintf('Der Mindestbestellwert von %s € wurde leider nicht erreicht', intToPrice($minamount))));
                    return false;
                }
            } else {
                $this->logger->info(sprintf('Ignoring minamount due to exception of company %s', $this->getCustomer()->getCompany()->getId()));
            }

            // floorfee / etagenzuschlag
            if ($post['floor'] == 'lift') {
                $order->setLift(true);
            } else {
                $order->setFloor((integer) $post['floor']);
                $order->setLift(false);
            }

            //check if in range
            if (!$order->addressInRange($cityId)) {
                $this->error(__('Dieser Dienstleister liefert hierhin leider nicht'));
                $this->logger->err(sprintf('tried to access delivers %s on cityId %s, which is not in the delivery range', $serviceId, $cityId));
                return false;
            }
        } catch (Yourdelivery_Exception_MissingOrderData $e) {
            $this->logger->warn(sprintf('missing order data ' . $e->getMessage()));
            return false;
        }

        /**
         * We recieve the last data from the finish page
         */
        if (isset($post['finish'])) {
            // call the pre-finish logic
            $pre = (boolean) $this->_preFinish($order, $post);

            // call the finish logic
            $fin = (boolean) $this->_finish($order);

            if (!$pre || !$fin) {
                return false;
            }

            // check location for premium restaurant with prompt
            if ($order->getService()->hasPromptCourier()) {
                $pomptAPI = new Yourdelivery_Model_Api_Prompt($order);
                $resp = $pomptAPI->geocode();

                if ($resp === false) {
                    $this->error(__('Ihre Lieferadresse ist nicht korrekt. Bitte überprüfen Sie Ihr Angabe.'));

                    if ($order->getKind() == 'comp') {
                        $this->error(__('Bitte setzen Sie sich mit unserem Support im Verbindung.'));
                    }
                    return false;
                }

                if ($resp['stage'] == 1 || $resp['stage'] == 2) {
                    $this->error(__('Ihre Lieferadresse ist nicht korrekt.'));

                    if (is_array($resp['data']) && count($resp['data'])) {
                        $this->error(_n('Vorschlag:', 'Vorschläge:', count($resp['data'])));
                        foreach ($resp['data'] as $address) {
                            $this->error($address['street'] . " " . $address['nr'] . ", " . $address['postcode'] . " " . $address['city']);
                        }
                    }

                    if ($order->getKind() == 'comp') {
                        $this->error(__('Bitte setzen Sie sich mit unserem Support im Verbindung.'));
                    }
                    return false;
                }
            }

            //add ec to payment
            $order->setPaymentAddition($this->getRequest()->getParam('paymentAddition'));
            $order->setChange((integer) preg_replace("/[^0-9]/", "", $this->getRequest()->getParam('change')));

            // save order
            try {
                $order->finish();
            } catch (Yourdelivery_Exception_FailedFinishingOrder $e) {
                $this->logger->crit('Could not finish order: ' . $e->getMessage());
                $this->error(__('Die Bestellung konnte leider nicht ausgeführt werden. Bitte setzen sie sich mit unserem Support in Kontakt'));
                return $this->_redirect('/error/throw');
            }

            $this->logger->info(sprintf('customer #%d %s sucessfully finished order #%d', $order->getCustomer()->getId(), $order->getCustomer()->getFullname(), $order->getId()));

            // call the post-finish logic
            $this->_postFinish($order);

            // process payment
            $this->session->currentOrderId = $order->getId();
            return $this->_processPayment($order->getId(), $order->getCurrentPayment());
        }
    }

    /**
     * Success logic
     * @author vpriem
     * @since 14.07.2011
     * @param Yourdelivery_Model_Order_Abstract $order
     * @return boolean
     */
    protected function _success(Yourdelivery_Model_Order_Abstract $order) {
        return true;
    }

    /**
     * @author vpriem
     * @modified daniel
     * @since 14.07.2011
     */
    public function successAction() {
        $this->setCache(0);
        $this->view->extra_css = 'step5';

        $order = $this->_getCurrentOrder();

        //store last cookie
        $cookie = Yourdelivery_Cookie::factory('yd-recurring');
        $cookie->set('lastorder', $order->getHash());
        $cookie->set('lastorderarea', $order->getLocation()->getPlz());
        $cookie->save();

        Default_Helpers_Web::deleteCookie('yd-track');
        Default_Helpers_Web::deleteCookie('yd-referer');

        if ($this->config->domain->base == 'lieferando.de') {
            //lottoland code
            $this->view->customerCode = urlencode(Default_Helpers_Crypt::encryptAES(json_encode(array('email' => $order->getCustomer()->getEmail())), '@a6Q6aTCQMkY'));
            $this->view->orderCode = urlencode($order->getNumber() . ',' . Default_Helpers_Crypt::encryptHMAC_SHA1($order->getNumber(), '@a6Q6aTCQMkY'));
        }

        // set mode
        $this->view->mode = $order->getMode();
        $this->view->order = $order;

        // check for order discount (SP-6708)
        $discount = $order->getDiscount();
        $this->view->vouid = $discount instanceof Yourdelivery_Model_Rabatt_Code ? $discount->getRabattId() : 0;

        // call the success logic
        $this->_success($order);
        if (IS_PRODUCTION) {
            unset($this->session->currentOrderId);
        }
    }

    /**
     *
     * @author vpriem
     * @modified mlaug 04.10.2011 http://ticket.yourdelivery.local/browse/YD-217
     */
    public function paymentAction() {
        $this->setCache(0);
        $this->view->extra_css = 'step4';

        $order = $this->_getCurrentOrder();

        if ($this->session->newCustomerDiscountError) {
            $this->view->assign('newCustomerDiscountError', $this->session->newCustomerDiscountError);
            $this->view->assign('newCustomerDiscountAbsTotal', $this->session->newCustomerDiscountAbsTotal);
            try {
                $discount = new Yourdelivery_Model_Rabatt_Code(null, $this->session->newCustomerDiscountId);
                $this->view->assign('discount', $discount);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__("Ein Fehler ist aufgetreten. Bitte starten Sie die Bestellung erneut."));
                return;
            }
        }

        // get request
        $request = $this->getRequest();

        // errors from callbacks
        $crediterror = $request->getParam('crediterror');
        if ($crediterror == 1) {
            $this->error(__('Ihre Kreditkartenzahlung konnte nicht durchgeführt werden. Versuchen Sie es bitte erneut oder wählen Sie ein andere Zahlungsmethode.'));
        } elseif ($crediterror == 2) {
            $this->error(__('Ihre Registrierung ist leider Fehlgeschlagen.'));
        } elseif ($crediterror == 3) {
            $this->error(__('Die 3DSecure Validierung ist fehlgeschlagen. Versuchen Sie es bitte erneut oder wählen Sie ein andere Zahlungsmethode.'));
        }

        //get the selected payment
        $post = $request->getPost();
        $payment = $post['payment'];

        // post
        if ($request->isPost()) {
            $post = $request->getPost();

            //change the current payment
            $this->logger->info(sprintf('changing payment from %s to %s for order %s', $order->getPayment(), $payment, $order->getId()));
            $order->setPayment($payment);

            //add Discount if possible
            if ($this->session->newCustomerDiscountError && $post['discount'] && $payment === "paypal") {
                $discountCode = $post['discount'];
                $discountNew = new Yourdelivery_Model_Rabatt_Code($post['discount']);
                $order->addDiscount($discountNew);
            }

            //add Fidelity if possible
            if ($this->session->newCustomerDiscountError && $order->getDiscount() == null) {
                $order->getCustomer()->addFidelityPoint('order', $order->getId());
            }

            unset($this->session->newCustomerDiscountError);
            unset($this->session->newCustomerDiscountId);
            unset($this->session->newCustomerDiscountAbsTotal);
            switch ($payment) {

                case 'bill':
                    if (!Yourdelivery_Helpers_Payment::allowBill($order)) {
                        $this->error(__('Rechnung ist bei dieser Bestellung leider nicht möglich'));
                        return;
                    }
                    $order->finalizeOrderAfterPayment('bill');

                    if ($order->getKind() == 'comp') {
                        return $this->_redirect('/order_company/success');
                    }
                    return $this->_redirect('/order_private/success');

                case 'bar':
                    if (!Yourdelivery_Helpers_Payment::allowBar($order)) {
                        $this->error(__('Barzahlung ist bei dieser Bestellung leider nicht möglich'));
                        return;
                    }
                    $order->finalizeOrderAfterPayment('bar');

                    if ($order->getKind() == 'comp') {
                        return $this->_redirect('/order_company/success');
                    }

                    return $this->_redirect('/order_private/success');

                case 'credit': // this might trigger a redirect
                    if (!Yourdelivery_Helpers_Payment::allowCredit($order)) {
                        $this->error(__('Kreditkartenzahlung ist bei dieser Bestellung leider nicht möglich'));
                        return;
                    }

                    $order->setStatus(
                            Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_CHANGE_CREDIT)
                    );
                    return $this->_processCredit($order);

                case 'ebanking': // this might trigger a redirect
                    if (!Yourdelivery_Helpers_Payment::allowEbanking($order)) {
                        $this->error(__('√úberweisung ist bei dieser Bestellung leider nicht möglich'));
                        return;
                    }

                    $order->setStatus(
                            Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_CHANGE_EBANKING)
                    );
                    return $this->_processEbanking($order);

                case 'paypal': // this might trigger a redirect
                    if (!Yourdelivery_Helpers_Payment::allowPaypal($order)) {
                        $this->error(__('PayPal ist bei dieser Bestellung leider nicht möglich'));
                        return;
                    }

                    $order->setStatus(
                            Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_CHANGE_PAYPAL)
                    );
                    return $this->_processPaypal($order);

                case 'debit': // this might trigger a redirect
                    if (!Yourdelivery_Helpers_Payment::allowDebit($order)) {
                        $this->error(__('Lastschrift ist bei dieser Bestellung leider nicht möglich'));
                        return;
                    }
                    return $this->_processDebit($order);

                default:
                    $this->warn(__('%s ist kein gültiges Bezahlverfahren', paymentToReadable($type)));
                    return $this->_redirect('/order_basis/payment');
            }
        }
    }

    public function redirectAction() {
        $request = $this->getRequest();

        $serviceId = (integer) $request->getParam('serviceId', 0);
        if (!$serviceId) {
            throw new Yourdelivery_Exception("No serviceId provided");
        }

        $mode = $this->view->mode = $request->getParam('mode');
        if ($mode === null) {
            throw new Yourdelivery_Exception("No mode provided");
        }

        // try to create service
        try {
            switch ($mode) {
                default:
                case Yourdelivery_Model_Servicetype_Abstract::RESTAURANT:
                    $service = new Yourdelivery_Model_Servicetype_Restaurant($serviceId);
                    break;

                case Yourdelivery_Model_Servicetype_Abstract::CATER:
                    $service = new Yourdelivery_Model_Servicetype_Cater($serviceId);
                    break;

                case Yourdelivery_Model_Servicetype_Abstract::GREAT:
                    $service = new Yourdelivery_Model_Servicetype_Great($serviceId);
                    break;

                default:
                    throw new Yourdelivery_Exception("Wrong mode provided");
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->warn(sprintf('no valid serviceId given in service action %s', $serviceId));
            return $this->_redirect('/');
        }

        $service->buildRedirectCache();
        $this->_redirect($service->getDirectLink(), array('code' => 301));
    }

    /**
     * Finish order
     * @author vpriem
     * @since 19.04.2011
     * @param Yourdelivery_Model_Order_Abstract $order
     * @param string $payment
     * @return void
     */
    protected function _processPayment($orderId, $payment = 'bar') {

        /**
         * @author mlaug
         * to avoid failures with payment system, we store the order
         * at this point and redirect the customer to the payment page
         * this order will be stored in state -5 (awaiting payment)
         */
        try {
            //initialize a read only order object to provide for payment
            $order = new Yourdelivery_Model_Order($orderId);
            $order->setCurrentPayment($payment);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->crit('Order could not be found before going to payment');
            $this->error(__('Die Bestellung konnte leider nicht ausgeführt werden. Bitte setzen sie sich mit unserem Support in Kontakt'));
            return $this->_redirect('/error/throw');
        }

        // order with no open amount - don't check payment
        $absTotal = $order->getAbsTotal();
        if ($absTotal <= 0) {
            $this->logger->info(sprintf('processing payment for order #%s with absTotal() = %s', $order->getId(), $absTotal));

            $order->setPayment('bar');
            $order->setCurrentPayment('bar');
            $order->finalizeOrderAfterPayment('bar');

            if ($order->getKind() == 'comp') {
                return $this->_redirect('/order_company/success');
            }

            return $this->_redirect('/order_private/success');
        }

        switch ($payment) {
            case 'bill':
                if (!Yourdelivery_Helpers_Payment::allowBill($order)) {
                    $this->error(__('Rechnung ist bei dieser Bestellung leider nicht möglich'));
                    return;
                }
                $order->finalizeOrderAfterPayment('bill');

                if ($order->getKind() == 'comp') {
                    return $this->_redirect('/order_company/success');
                }
                return $this->_redirect('/order_private/success');

            case 'bar':
                if (!Yourdelivery_Helpers_Payment::allowBar($order, true)) {
                    $this->error(__('Barzahlung ist bei dieser Bestellung leider nicht möglich'));
                    return $this->_redirect('/order_basis/payment');
                }
                $order->finalizeOrderAfterPayment('bar');

                if ($order->getKind() == 'comp') {
                    return $this->_redirect('/order_company/success');
                }
                return $this->_redirect('/order_private/success');

            case 'credit': // this MIGHT trigger a redirect
                if (!Yourdelivery_Helpers_Payment::allowCredit($order)) {
                    $this->error(__('Barzahlung ist bei dieser Bestellung leider nicht möglich'));
                    return $this->_redirect('/order_basis/payment');
                }

                $order->setStatus(
                        Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_PROCESS_CREDIT)
                );
                return $this->_processCredit($order);

            case 'ebanking': // this MIGHT trigger a redirect
                if (!Yourdelivery_Helpers_Payment::allowEbanking($order)) {
                    $this->error(__('Überweisung ist bei dieser Bestellung leider nicht möglich'));
                    return $this->_redirect('/order_basis/payment');
                }

                $order->setStatus(
                        Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_PROCESS_EBANKING)
                );
                return $this->_processEbanking($order);

            case 'paypal': // this MIGHT trigger a redirect
                if (!Yourdelivery_Helpers_Payment::allowPaypal($order)) {
                    $this->error(__('PayPal ist bei dieser Bestellung leider nicht möglich'));
                    return $this->_redirect('/order_basis/payment');
                }

                $order->setStatus(
                        Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_PROCESS_PAYPAL)
                );
                return $this->_processPaypal($order);

            case 'debit': // this MIGHT trigger a redirect
                $this->error(__('Lastschrift ist bei dieser Bestellung leider nicht möglich'));
                return $this->_redirect('/order_basis/payment');
                return $this->_processDebit($order);

            default:
                $this->warn(__('%s ist kein gültiges Bezahlverfahren', paymentToReadable($type)));
                return $this->_redirect('/error/throw');
        }
    }

    /**
     * Redirect the user to the Heidelpay site
     * @author vpriem
     * @since 02.03.2011
     * @param Yourdelivery_Model_Order_Abstract $order
     * @return void
     */
    protected function _processCredit(Yourdelivery_Model_Order_Abstract $order) {
        if ($this->config->payment->credit->gateway == 'adyen') {
            return $this->_redirect('/payment_adyen/initialize');
        }

        $request = $this->getRequest();
        $register = $request->getParam('savecc', false) && $this->getCustomer()->isLoggedIn();
        $creditcard = $request->getParam('creditcard');

        // load creditcard
        if ($creditcard !== null) {
            try {
                $creditcard = new Yourdelivery_Model_Customer_Creditcard($creditcard);
                if ($creditcard->isOwner($this->getCustomer()->getId())) {

                    // answer xmp api
                    $heidelpay = new Yourdelivery_Payment_Heidelpay_Xml();

                    $resp = false;
                    try {
                        $resp = $heidelpay->request($creditcard->getUniqueId(), $order);
                    } catch (Yourdelivery_Payment_Heidelpay_Exception $e) {

                    }
                    // ack
                    if (isset($resp['Result']) && strstr($resp['Result'], "ACK") && $resp['Status'] == "NEW") {
                        $this->logger->info('Heidelpay XML: receive ACK with status NEW for order #' . $order->getId());

                        $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_SUCCESS_CREDIT)
                        );
                        $order->finalizeOrderAfterPayment('credit');

                        if ($order->getKind() == 'comp') {
                            return $this->_redirect("/order_company/success");
                        }
                        return $this->_redirect("/order_private/success");
                    }
                    // case where 3DSecure is enabled
                    elseif ($resp['Result'] && strstr($resp['Result'], "ACK") && $resp['Status'] == "WAITING") {
                        $this->logger->info('Heidelpay XML: receive ACK with status WAITING for order #' . $order->getId());

                        // save values in session
                        if ($resp['RedirectUrl'] && $resp['RedirectParams']) {
                            $this->session->CreditRedirectUrl = $resp['RedirectUrl'];
                            $this->session->CreditRedirectParams = $resp['RedirectParams'];
                        }
                        return $this->_redirect("/payment_heidelpay/redirect/");
                    }
                    // fake ?
                    elseif (isset($resp['Return']) && Yourdelivery_Payment_Heidelpay::isFake($resp['Return'])) {
                        $msg = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_FAKE_CREDIT, $order->getId(), $resp['Reason'], $resp['Return']);
                        $this->logger->warn($msg->getRawMessage());
                        $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, $msg);
                        $order->finalizeOrderAfterPayment('credit', true);

                        if ($order->getKind() == 'comp') {
                            return $this->_redirect("/order_company/success");
                        }
                        return $this->_redirect("/order_private/success");
                    }
                    // inform user
                    else {
                        $msg = new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_FAIL_CREDIT, $order->getId(), $resp['Reason'], $resp['Return']);
                        $this->logger->err($msg->getRawMessage());
                        $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, $msg);

                        return $this->_redirect('/order_basis/payment?crediterror=1');
                    }
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

            }

            // creditcard was not found
            $this->error(__('Unbekannte Kreditkarte'));
            return $this->_redirect('/order_basis/payment');
        }

        // Heidelpay gateway
        $heidelpay = new Yourdelivery_Payment_Heidelpay_Wpf();
        $resp = false;
        try {
            $resp = $heidelpay->redirectUser($order, $register);
        } catch (Yourdelivery_Payment_Heidelpay_Exception $e) {
            $this->logger->err('Heidelpay WPF: could not redirect user for order #' . $order->getId() . ' because: ' . $e->getMessage());
        }

        if ($resp && $resp['POST_VALIDATION'] == "ACK") {
            $this->logger->info('Heidelpay WPF: redirect user for order #' . $order->getId());
            return $this->_redirect($resp['FRONTEND_REDIRECT_URL']);
        }

        if ($resp) {
            $this->logger->err('Heidelpay WPF: could not redirect user for order #' . $order->getId() . ' because: ' . $resp['PROCESSING_REASON'] . ": " . $resp['PROCESSING_RETURN']);
        }

        $this->error(__('Die Weiterleitung konnte nicht hergestellt werden, bitte versuchen Sie erneut oder wählen Sie ein andere Zahlungsmöglichkeit'));
        return $this->_redirect('/order_basis/payment');
    }

    /**
     * Redirect the user to the MasterPayment site
     * @author vpriem
     * @since 04.04.2011
     * @param Yourdelivery_Model_Order_Abstract $order
     * @return boolean
     * @deprecated
     */
    protected function _processDebit(Yourdelivery_Model_Order_Abstract $order) {
        // intialize the transaction
        // and redirect the user
        $master = new Yourdelivery_Payment_Master();
        return $master->redirectUser($order, "elv");
    }

    /**
     * Redirect the user to the eBanking site
     * @author vpriem
     * @since 02.03.2011
     * @param Yourdelivery_Model_Order_Abstract $order
     * @return void
     */
    protected function _processEbanking(Yourdelivery_Model_Order_Abstract $order) {
        // intialize the transaction
        // and redirect the user
        $eBanking = new Yourdelivery_Payment_Ebanking();
        return $this->_redirect($eBanking->redirectUser($order));
    }

    /**
     * Redirect the user to the PayPal site
     * @author vpriem
     * @since 02.03.2011
     * @param Yourdelivery_Model_Order_Abstract $order
     * @param boolean $redirect
     * @return void
     */
    protected function _processPaypal(Yourdelivery_Model_Order_Abstract $order) {
        $paypal = new Yourdelivery_Payment_Paypal();

        $resp = false;
        try {
            $resp = $paypal->setExpressCheckout($order, "/payment_paypal/finish", "/payment_paypal/cancel", "/payment_paypal_giropay/finish", "/payment_paypal_giropay/cancel");
        } catch (Yourdelivery_Payment_Paypal_Exception $e) {
            $this->logger->err('Paypal: could not redirect user for order #' . $order->getId() . ' because: ' . $e->getMessage());
        }

        // redirect if successful
        if ($resp && $resp['ACK'] == "Success") {
            $this->logger->info('Paypal: redirect user for order #' . $order->getId());
            return $this->_redirect($paypal->redirectUser($resp['TOKEN']));
        }

        // failed to initialize transaction
        if ($resp) {
            $this->logger->err('Paypal: could not redirect user for order #' . $order->getId() . ' because ' . $resp['L_LONGMESSAGE0']);
        }

        $this->error(__('Eine sichere Verbindung zu Paypal konnte leider nicht hergestellt werden, bitte versuchen Sie erneut oder wählen Sie ein andere Zahlungsmöglichkeit'));
        return $this->_redirect('/order_basis/payment');
    }

    /**
     * this will return current order and add to view,
     * if not present will redirect
     * @author mlaug
     * @since 19.07.2011
     * @return Yourdelivery_Model_Order
     */
    protected function _getCurrentOrder() {
        if ($this->session->currentOrderId <= 0) {
            $this->logger->warn('tryed to access payment page without any orderId in session');
            unset($this->session->currentOrderId);
            return $this->_redirect('/');
        }

        try {
            $order = new Yourdelivery_Model_Order($this->session->currentOrderId);
            $this->view->order = $order;
            return $order;
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->crit(sprintf('could not find order by id %d on payment page', $this->session->currentOrderId));
            return $this->_redirect('/');
        }
    }

}
