<?php

//define api version
!defined('API_VERSION') ? define('API_VERSION', '1.0') : null;

/**
 *
 * @param DOMDocument $doc
 * @param string $name
 * @param mixed $value
 * @return DOMElement
 */
function create_node($doc, $name, $value, $attributeName = null, $attributeValue = null) {
    $elem = $doc->createElement($name);

    if (!is_null($attributeName) && !is_null($attributeValue)) {
        $elem->setAttribute($attributeName, $attributeValue);
    }

    $elem->appendChild($doc->createTextNode($value));
    return $elem;
}

/**
 * create order data set
 *
 * @param Yourdelivery_Model_Order $order
 *
 * @return DOM_Element
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
function createOrderChild($order, $elementName = 'order', $doc) {
    $config = Zend_Registry::get('configuration');
    $oElem = $doc->createElement($elementName);
    $oElem->appendChild(create_node($doc, 'id', (integer) $order->getId()));
    $oElem->appendChild(create_node($doc, 'nr', $order->getNr()));
    $oElem->appendChild(create_node($doc, 'hash', $order->getHash()));
    $oElem->appendChild(create_node($doc, 'time', $order->getTime()));
    $oElem->appendChild(create_node($doc, 'deliverTime', $order->getDeliverTime()));
    $oElem->appendChild(create_node($doc, 'total', (integer) $order->getTotal()));
    $taxesElement = $doc->createElement('taxes');
    foreach ($config->tax->types->toArray() as $tax) {
        $taxesElement->appendChild(create_node($doc, 'tax', (integer) $order->getTax($tax), 'type', $tax));
    }
    $oElem->appendChild($taxesElement);
    $oElem->appendChild(create_node($doc, 'charge', 0));
    $oElem->appendChild(create_node($doc, 'deliverCost', (integer) $order->getServiceDeliverCost() + $order->getCourierCost() - $order->getCourierDiscount()));
    $oElem->appendChild(create_node($doc, 'discount', (integer) $order->getDiscountAmount()));
    $oElem->appendChild(create_node($doc, 'discountcode', $order->getDiscount() ? $order->getDiscount()->getCode() : null ));
    $oElem->appendChild(create_node($doc, 'paymentMethod', $order->getPayment()));
    $oElem->appendChild(create_node($doc, 'isRated', (integer) !$order->isRateable()));
    $oElem->appendChild(create_node($doc, 'isFavorite', $order->isFavourite()));
    $oElem->appendChild(create_node($doc, 'isRepeatable', (integer) $order->isRepeatable()));

    //append customer
    $userElement = $doc->createElement('customer');
    $userElement->appendChild(create_node($doc, 'prename', $order->getCustomer()->getPrename()));
    $userElement->appendChild(create_node($doc, 'name', $order->getCustomer()->getName()));
    $oElem->appendChild($userElement);
    
    //append location
    $locationElement = $doc->createElement('location');
    $location = $order->getLocation();
    $locationElement->appendChild(create_node($doc, 'street', $location->getStreet()));
    $locationElement->appendChild(create_node($doc, 'hausnr', $location->getHausnr()));
    $locationElement->appendChild(create_node($doc, 'plz', $location->getPlz()));
    $locationElement->appendChild(create_node($doc, 'cityId', $location->getCityId()));
    $locationElement->appendChild(create_node($doc, 'company', $location->getCompanyName()));
    $locationElement->appendChild(create_node($doc, 'etage', $location->getEtage()));
    if (is_null($location->getOrt())) {
        $locationElement->appendChild(create_node($doc, 'city', __('Unbekannt')));
    } else {
        $locationElement->appendChild(create_node($doc, 'city', $location->getOrt()->getOrt()));
    }
    $locationElement->appendChild(create_node($doc, 'tel', $location->getTel()));
    $locationElement->appendChild(create_node($doc, 'comment', $location->getComment()));
    $oElem->appendChild($locationElement);

    $serviceElement = $doc->createElement('service');
    $service = $order->getService();
    $service->setCurrentCityId($location->getCity()->getId());
    $serviceElement->appendChild(create_node($doc, 'id', $service->getId()));
    $serviceElement->appendChild(create_node($doc, 'name', $service->getName()));
    $serviceElement->appendChild(create_node($doc, 'picture', $service->getImg('api')));
    $serviceElement->appendChild(create_node($doc, 'tel', $service->getTel()));
    $serviceElement->appendChild(create_node($doc, 'onlycash', (integer) $service->isOnlycash()));
    $serviceElement->appendChild(create_node($doc, 'allowcash', (integer) $service->isPaymentbar()));

    // current deliver area of this order
    $plzElems = $doc->createElement('deliversTo');
    $plz = $doc->createElement('deliverArea');
    $plz->appendChild(create_node($doc, 'cityId', $cityId));
    $plz->appendChild(create_node($doc, 'parent', (integer) $service->getCity()->getParentCityId()));
    $plz->appendChild(create_node($doc, 'plz', $service->getPlz()));
    $plz->appendChild(create_node($doc, 'city', $service->getCity()->getCity()));
    $plz->appendChild(create_node($doc, 'deliverCost', (integer) $service->getDeliverCost($cityId), 'dimension', 'cent'));
    $plz->appendChild(create_node($doc, 'minCost', (integer) $service->getMinCost($cityId), 'dimension', 'cent'));
    $plz->appendChild(create_node($doc, 'deliverTime', (integer) $service->getDeliverTime($cityId), 'dimension', 'seconds'));
    $plzElems->appendChild($plz);
    $serviceElement->appendChild($plzElems);

    // openings for today
    $openingElements = $doc->createElement('openings');
    $openings = $service->getOpening()->getIntervalOfDay();
    foreach ($openings as $opening) {
        foreach ($opening as $key => $o) {
            if (strpos($key, 'next') !== false) {
                continue;
            }
            $openingElement = $doc->createElement('day');
            $openingElement->setAttribute('weekday', $o['day']);
            $openingElement->appendChild(create_node($doc, 'from', date('H:i', $o['timestamp_from'])));
            $openingElement->appendChild(create_node($doc, 'until', date('H:i', $o['timestamp_until'])));
            $openingElements->appendChild($openingElement);
            unset($openingElement);
        }
    }

    $serviceElement->appendChild($openingElements);

    $ratingElement = $doc->createElement('ratings');
    $ratingElement->appendChild(create_node($doc, 'advise', (integer) round($service->getRating()->getAverageAdvise())));
    $ratingElement->appendChild(create_node($doc, 'quality', (integer) $service->getRating()->getAverageQuality()));
    $ratingElement->appendChild(create_node($doc, 'delivery', (integer) $service->getRating()->getAverageDelivery()));
    $ratingElement->appendChild(create_node($doc, 'total', (integer) $service->getRating()->getAverage()));
    $ratingElement->appendChild(create_node($doc, 'votes', (integer) $service->getRating()->count()));
    $ratingElement->appendChild(create_node($doc, 'title', ''));
    $ratingElement->appendChild(create_node($doc, 'comment', ''));
    $ratingElement->appendChild(create_node($doc, 'author', ''));
    $ratingElement->appendChild(create_node($doc, 'created', ''));

    $serviceElement->appendChild($ratingElement);
    $oElem->appendChild($serviceElement);

    //append order card
    $card = $doc->createElement('meals');
    foreach ($order->getCard() as $customerBucket) {
        foreach ($customerBucket as $bucket) {
            foreach ($bucket as $item) {
                $mealElement = $doc->createElement('meal');
                $meal = $item['meal'];
                $mealElement->appendChild(create_node($doc, 'id', $meal->getId()));
                $mealElement->appendChild(create_node($doc, 'name', $meal->getName()));
                $mealElement->appendChild(create_node($doc, 'cost', $meal->getCost()));
                $mealElement->appendChild(create_node($doc, 'description', $meal->getDescription()));
                $mealElement->appendChild(create_node($doc, 'count', $item['count']));
                $mealElement->appendChild(create_node($doc, 'excludefrommincost', stripslashes((integer) $meal->getExcludeFromMinCost() || $meal->getCategory()->getExcludeFromMinCost())));
                $mealElement->appendChild(create_node($doc, 'mincount', (integer) $meal->getMinAmount()));
                $mealElement->appendChild(create_node($doc, 'sizeId', (integer) $meal->getCurrentSize()));

                $extrasElement = $doc->createElement('extras');
                $optionsElement = $doc->createElement('options');

                foreach ($meal->getCurrentExtras() as $extra) {
                    $extraElement = $doc->createElement('extra');
                    $extraElement->appendChild(create_node($doc, 'id', $extra->getId()));
                    $extraElement->appendChild(create_node($doc, 'name', $extra->getName()));
                    $extraElement->appendChild(create_node($doc, 'cost', $extra->getCost()));
                    $extrasElement->appendChild($extraElement);
                }

                foreach ($meal->getCurrentOptions() as $option) {
                    $optionElement = $doc->createElement('option');
                    $optionElement->appendChild(create_node($doc, 'id', $option->getId()));
                    $optionElement->appendChild(create_node($doc, 'name', $option->getName()));
                    $optionElement->appendChild(create_node($doc, 'cost', $option->getCost()));
                    $optionsElement->appendChild($optionElement);
                }

                $mealElement->appendChild($extrasElement);
                $mealElement->appendChild($optionsElement);
                $card->appendChild($mealElement);
            }
        }
    }

    $oElem->appendChild($card);
    return $oElem;
}

/**
 * Base Controller Class
 * @package core
 * @subpackage controller
 * @abstract
 * @author mlaug
 */
abstract class Default_Controller_RestBase extends Zend_Rest_Controller {

    protected $_customer = null;

    /**
     * list of valid api keys
     * @var array
     */
    protected $apiKeys = array(
        'iphone' => 'Snjke983A2##dM87uibkjneS##wO8732iujkN',
        'qype' => 'Tuz82hoIo243tohF#asf+Fknj23jlkY',
        'mobile' => 'jlkfadihj387fdkjh#adsuzg23187t6',
        'speisekarte' => 'fhjkkjhdkjh#327dhn2ns,,',
        'woopla' => 'letstryittheeasyway',
        'card4you' => 'ksfki3o09f###edjurtz3uzdbg--+',
        'allmax' => 'khgewrihu34278fdskjdsf##+zo32rleiug',
        'econa' => 'g30fhedn,mf+++1239hafdh9uaf--'
    );

    /**
     * create valid xml for respond
     * @var DOMElement
     */
    protected $xml = null;

    /**
     *
     * @var DOMDocument
     */
    protected $doc = null;

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
     * mark if this request shall be cached or not
     * @var boolean
     */
    protected $_allowCache = false;

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
             * get current configuration of application
             */
            case "config": {
                    if ($this->_config == null) {
                        $this->_config = Zend_Registry::get('configuration');
                    }
                    return $this->_config;
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
                        $auth->getDbSelect()->where('deleted=0');

                        /**
                         * @var Zend_Auth
                         */
                        $this->_auth = $auth;
                    }
                    return $this->_auth;
                }
        }
    }

    /**
     * work some stuff before action is called
     * @since 13.09.2010
     * @author mlaug
     */
    public function preDispatch() {

        $request = $this->getRequest();

        $apiKey = $request->getHeader('apikey');

        if (!in_array($apiKey, $this->apiKeys) && IS_PRODUCTION) {
            die(); //DIEEEEEEEEE
        }

        $this->version = API_VERSION;
        $this->_helper->ViewRenderer->setNoRender(true);

        //create basic doc element
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;
        $root_element = $doc->createElement("response");
        $doc->appendChild($root_element);

        $versionElement = $doc->createElement("version");
        $versionElement->appendChild($doc->createTextNode($this->version));
        $root_element->appendChild($versionElement);

        $this->xml = $root_element;
        $this->doc = $doc;
        $this->message = "";
        $this->url = "";
        $this->anchortext = "";
        $this->success = "true";
        $this->show_fidelity = true;
        $this->fidelity_points = 0;
        $this->fidelity_message = "";
        $this->errorkey = "";
    }

    /**
     * For each and every request we need to do some things
     * <ul>
     *  <li>Append success or failure message. This can be triggered by the success flag or a httpResponseCode greater or equal 400</li>
     *  <li>Append fidelity points with its message</li>
     *  <li>Append some debugging messages like memory usage</li>
     * </ul>
     *
     * @since 13.09.2010
     * @author mlaug
     */
    public function postDispatch() {

        if ($this->_cache === true) {
            return;
        }

        $statusElement = $this->doc->createElement("success");
        if ($this->getResponse()->getHttpResponseCode() >= 400 || $this->success === false) {
            $statusElement->appendChild($this->doc->createTextNode('false'));
        } else {
            $statusElement->appendChild($this->doc->createTextNode('true'));
        }
        $this->xml->appendChild($statusElement);

        //append message
        $messageElement = $this->doc->createElement("message");
        $messageElement->appendChild($this->doc->createTextNode($this->message));
        $this->xml->appendChild($messageElement);

        // append optional url
        $urlElement = $this->doc->createElement("url");
        $urlElement->appendChild($this->doc->createTextNode($this->url));
        $this->xml->appendChild($urlElement);

        //append message
        $anchortextElement = $this->doc->createElement("anchortext");
        $anchortextElement->appendChild($this->doc->createTextNode($this->anchortext));
        $this->xml->appendChild($anchortextElement);

        if ($this->show_fidelity === true) {
            //add fidelity points to each and every response
            $fidelityElement = $this->doc->createElement('fidelity');

            //check for any present fidelity points, otherwise check, if we
            $customer = null;
            try {
                $customer = $this->_getCustomer();
                if ($this->fidelity_points == 0 && $customer instanceof Yourdelivery_Model_Customer_Abstract) {
                    $transactionId = (integer) $customer->getFidelity()->getLastTransactionId();
                    if ($transactionId > 0 && !$customer->getFidelity()->isOldTransaction()) {
                        try {
                            $transaction = new Yourdelivery_Model_Customer_FidelityTransaction($transactionId);
                            $this->fidelity_points = $transaction->getPoints();
                            $this->fidelity_message = __('fidelity_' . $transaction->getAction() . ' %s', $this->fidelity_points);
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            
                        }
                    }
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }


            $fidelityElement->appendChild(create_node($this->doc, 'points', $this->fidelity_points));
            $fidelityElement->appendChild(create_node($this->doc, 'message', $this->fidelity_message));
            $this->xml->appendChild($fidelityElement);
        }

        /**
         * append information from which field error of form came from 
         */
        if (is_array($this->errorkey)) {
            array_unique($this->errorkey);
            $this->errorkey = implode(',', $this->errorkey);
        }
        $errorKeyElement = $this->doc->createElement('errorkey');
        $errorKeyElement->appendChild($this->doc->createTextNode($this->errorkey));
        $this->xml->appendChild($errorKeyElement);

        //append some debugging messages
        if (APPLICATION_ENV == "development") {
            $memoryElement = $this->doc->createElement("memory");
            $memoryElement->appendChild($this->doc->createTextNode(round(memory_get_peak_usage() / 1024 / 1024)));
            $this->xml->appendChild($memoryElement);
        }

        /*
         * user may choose between xml and json output
         * HINT: if we respond with a list of elemets, you may face one problem
         * json_encode will result in an object, if only one element is in the list, but
         * with a list (as expected in both scenarios) if the list inherits more
         * than one element
         */
        $request = $this->getRequest();
        $type = $request->getParam('format', 'xml');
        switch ($type) {
            default:
            case 'xml':
                $this->getResponse()->setHeader('Content-Type', 'text/xml');
                $ret = $this->doc->saveXML();
                break;
            case 'json':
                $this->getResponse()->setHeader('Content-Type', 'application/json');
                $ret = json_encode(new SimpleXMLElement(@$this->doc->saveXML(), LIBXML_NOCDATA));
                break;
        }

        if ($this->_allowCache) {
            $cache = Default_Helpers_Cache::store($this->getRequestHash(), $ret);
        }

        //remove all cookies for this calls
        foreach ($_COOKIE as $key => $v) {
            unset($_COOKIE[$key]);
        }

        echo $ret;
    }

    /**
     * set all basic functions to "Access denied" by default
     */
    public function indexAction() {
        $this->getResponse()->setHttpResponseCode(403);
    }

    public function getAction() {
        $this->getResponse()->setHttpResponseCode(403);
    }

    public function postAction() {
        $this->getResponse()->setHttpResponseCode(403);
    }

    public function putAction() {
        $this->getResponse()->setHttpResponseCode(403);
    }

    public function deleteAction() {
        $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * caching method, which will prevent the postDispatch action
     * and just returns the cached item
     */
    public function cacheAction() {
        $this->getRequest()->setDispatched(true);
        $this->getResponse()->setHeader('Content-Type', 'text/xml');
        $this->_helper->ViewRenderer->setNoRender(true);
    }

    /**
     * get the http put data from standard input
     * @author mlaug
     * @since 18.02.2011
     */
    protected function _getPut() {
        $put = array();
        parse_str(file_get_contents('php://input'), $put);
        return $put;
    }

    /**
     * enable caching functions for this request
     * @author mlaug
     * @since 18.02.2011
     */
    protected function enableCache() {
        $this->_allowCache = true;
    }

    /**
     * disable caching functions for this request
     * @author mlaug
     * @since 18.02.2011
     */
    protected function disableCache() {
        $this->_allowCache = false;
    }

    /**
     * @author mlaug
     * @since 05.02.2011
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 28.11.2011
     *
     * @param stdClass $json
     * @throws Yourdelivery_Exception_Database_Inconsistency
     * @return Yourdelivery_Model_Customer
     */
    protected function _getCustomer(stdClass $json = null) {
        if ($this->_customer === null) {
            if ($json === null || $json->access === null) {
                $request = $this->getRequest();
                if ($request->getParam('access')) {
                    $this->logger->debug(sprintf('API - BASE: get access for customer via POST with access %s', $request->getParam('access')));
                    try {
                        return new Yourdelivery_Model_Customer(null, null, $request->getParam('access'));
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->logger->debug(sprintf("API - BASE: didn't get access for customer via POST with access %s", $request->getParam('access')));
                        throw new Yourdelivery_Exception_Database_Inconsistency();
                    }
                }
                $this->logger->debug(sprintf("API - BASE: didn't get access for customer via POST with access %s", $request->getParam('access')));
                throw new Yourdelivery_Exception_Database_Inconsistency();
            }

            try {
                $this->_customer = new Yourdelivery_Model_Customer(null, null, $json->access);
                $this->logger->debug(sprintf('API - BASE: get access for customer via json with access %s', $json->access));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->logger->debug(sprintf("API - BASE: didn't get access for customer via JSON with access %s", $json->access));
                throw new Yourdelivery_Exception_Database_Inconsistency();
            }
        } elseif ($this->_customer === null && $json === null) {
            $this->logger->debug(sprintf("API - BASE: didn't get access for customer with json is null"));
            throw new Yourdelivery_Exception_Database_Inconsistency();
        }
        return $this->_customer;
    }

    protected function getRequestHash() {
        $_request = $this->getRequest();
        $request = array_merge(array(
            $_request->getActionKey(),
            $_request->getActionName(),
            $_request->getControllerKey(),
            $_request->getControllerName(),
                ), $_request->getParams());

        $hash = sha1(implode('', $request));
        return $hash;
    }

    /**
     *
     * @param array $errorMessages
     */
    public function returnFormErrors(array $errorMessages) {
        $this->message = __('Bitte überprüfe Deine Eingaben:') . "\n";
        foreach ($errorMessages as $key => $msg) {
            $m = array_pop($msg);
            $this->errorkey[] = $key;
            $this->message .= '* ' . $m . "\n";
        }
    }

}
