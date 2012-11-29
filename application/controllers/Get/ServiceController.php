<?php

/**
 * @package Yourdelivery
 * @subpackage API
 */

/**
 * <b>Description:</b>
 *
 *  <ul>
 *      <li>
 *          get list of services or single entry of service
 *      </li>
 *  </ul>
 *
 * <b>Available Actions:</b>
 *  <ul>
 *      <li>
 *          index - get a list of all services based on give parameters
 *      </li>
 *      <li>
 *          delete - disallowed - 403
 *      </li>
 *      <li>
 *          get - get all information of one service
 *      </li>
 *      <li>
 *          post - disallowed - 403
 *      </li>
 *      <li>
 *          put - disallowed - 403
 *      </li>
 *  </ul>
 *
 *
 * @author Matthias Laug <laug@lieferando.de>
 * @since 07.09.2010
 *
 * @modified Felix Haferkorn <haferkorn@lieferando.de>, 03.01.2012
 */
class Get_ServiceController extends Default_Controller_RestBase {

    public function preDispatch() {
        $this->_allowCache = false;
        parent::preDispatch();
    }

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>get list of services</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     * <b>2.1. Paremeters:</b>
     *
     *  <code>
     *      type GET
     *      {
     *          one of them:
     *              plz         STRING     (zip code of area to search for services)
     *              cityId      INTEGER    (cityId of area to serach for services)
     *              lat & lng   FLOAT      (GPD coordinates - longitude und latitude)
     *          optional parameters:
     *              limit       INTEGER    (how many services should be loaded)
     *              offset      INTEGER    (where to start from)
     *      }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>3.1. Response:</b>
     *
     *  <code>
     *      <area>
     *          <plz>STRING</plz>
     *          <cityId>INTEGER</cityId>
     *          <city>STRING</city>
     *      </area>
     *      <servicecount>INTEGER</servicecount>            (count of services ignoring limit and offset)
     *      <services>
     *          <service>
     *              <id>Integer (unique)</id>
     *              <qypeId>INTEGER</qypeId>
     *              <name>STRING</name>
     *              <info>STRING</info>
     *              <picture>String</picture>
     *              <plz>String</plz>
     *              <city>Berlin</city>
     *              <telephon>0303309973</telephon>
     *              <fax>String</fax>
     *              <link>lieferservice-aapka-berlin</link>
     *              <onlycash>boolean</onlycash>
     *              <allowcash>boolean</allowcash>
     *              <street>String</street>
     *              <category>String</category>
     *              <premium>boolean</premium>
     *              <open>boolean</open>
     *              <deliversTo>
     *                  <deliverArea>
     *                      <cityId>Integer (unique)</cityId>
     *                      <parent>Integer (unique)</parent>
     *                      <plz>String</plz>
     *                      <deliverCost dimension="cent">Integer</deliverCost>
     *                      <minCost dimension="cent">Integer</minCost>
     *                      <deliverTime dimension="seconds">Integer</deliverTime>
     *                      <noDeliverCostAbove dimension="cent">INTEGER</noDeliverCostAbove>
     *                  </deliverArea>
     *                  ...
     *              </deliversTo>
     *              <tags>
     *                  <tag>Fleischgerichte</tag>
     *                  ...
     *              </tags>
     *              <ratings>
     *                  <advise>INTEGER[0-100]</advise>
     *                  <quality>INTEGER[0-5]</quality>
     *                  <delivery>INTEGER[0-5]</delivery>
     *                  <total>INTEGER</total>
     *                  <votes>INTEGER</votes>
     *                  <title>STRING</title>
     *                  <comment>LONGTEXT</comment>
     *                  <author>STRING</author>
     *                  <created>TIMESTAMP</created>
     *              </ratings>
     *              <openings>
     *                  <day weekday="INTEGER[1-7,10]">
     *                      <from>12:00</from>
     *                      <until>23:00</until>
     *                  </day>
     *                  ...
     *              </openings>
     *          </service>
     *      </services>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Examples:</b>
     *
     * <b>4.1. Example - Request:</b>
     *
     *  <code>
     *      <ul>
     *          <li>http://www.lieferando.de/get_service</li>
     *          <li>http://www.lieferando.de/get_service?plz=10115</li>
     *          <li>http://www.lieferando.de/get_service?cityId=644</li>
     *          <li>http://www.lieferando.de/get_service?lat=50&lng=13</li>
     *          <li>http://www.lieferando.de/get_service?limit=10&offset=20</li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <area>
     *          <plz>10115</plz>
     *          <cityId>644</cityId>
     *          <city>Berlin</city>
     *      </area>
     *      <servicecount>91</servicecount>
     *      <services>
     *          <service>
     *              <id>15563</id>
     *              <qypeId>6899</qypeId>
     *              <name>Aapka</name>
     *              <info>
     *                  Das Angebot des Restaurants Aapka verspricht eine kulinarische Reise durch Indien. Die Grillgerichte werden im original indischen Lehmofen, dem Tandoor gegart. Lassen Sie sich verführen von den exotischen Speisen Indiens!
     *              </info>
     *              <info></info>
     *              <picture>http://image.yourdelivery.de/lieferando.de/service/15563/Aapka-250-0.jpg</picture>
     *              <plz>10119</plz>
     *              <city>Berlin</city>
     *              <telephon>0303309973</telephon>
     *              <fax>030 33 099 755</fax>
     *              <link>lieferservice-aapka-berlin</link>
     *              <onlycash>1</onlycash>
     *              <allowcash>1</allowcash>
     *              <street>Kastanienallee 50</street>
     *              <category>Indisch</category>
     *              <premium>false</premium>
     *              <open>true</open>
     *              <deliversTo>
     *                  <deliverArea>
     *                      <cityId>644</cityId>
     *                      <parent>0</parent>
     *                      <plz>10115</plz>
     *                      <city>10115 Berlin (Mitte)</city>
     *                      <deliverCost dimension="cent">690</deliverCost>
     *                      <minCost dimension="cent">2200</minCost>
     *                      <deliverTime dimension="seconds">2400</deliverTime>
     *                      <noDeliverCostAbove dimension="cent">INTEGER</noDeliverCostAbove>
     *                      </deliverArea>
     *                      ...
     *              </deliversTo>
     *              <tags>
     *                  <tag>Fleischgerichte</tag>
     *                  <tag>Geflügelgerichte</tag>
     *                  ...
     *              </tags>
     *              <ratings>
     *                  <advise>100</advise>
     *                  <quality>5</quality>
     *                  <delivery>3</delivery>
     *                  <total>10</total>
     *                  <votes>5</votes>
     *                  <title>Guter Lieferdienst</title>
     *                  <comment>Super, lecker Esssen</comment>
     *                  <author>Felix</author>
     *                  <created>12345678</created>
     *              </ratings>
     *              <openings>
     *                  <day weekday="1">
     *                      <from>12:00</from>
     *                      <until>23:00</until>
     *                  </day>
     *              </openings>
     *          </service>
     *          ...
     *      </services>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - services found</li>
     *      <li>404 - services not found</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.09.2010
     *
     *
     * ATTENTION (for yourdelivery developers):
     * there is a CRONJOB where this code is copy & pasted
     * be sure, that's identical
     *
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 28.02.2012
     *
     * @return HTTP-RESPONSE-CODE
     */
    public function indexAction() {

        $request = $this->getRequest();

        $sElems = $this->doc->createElement('services');

        //search params
        $lat = $request->getParam('lat', null);
        $lng = $request->getParam('lng', null);
        $cityId = $request->getParam('cityId', null);
        $plz = $request->getParam('plz', null);

        $limit = (integer) $request->getParam('limit', 10000);
        $offset = (integer) $request->getParam('offset', 0);

        $this->logger->info(sprintf('API - SERVICE - INDEX: searching for restaurants (%s, %s, %s, %s)', $lat, $lng, $cityId, $plz));

        //FALLBACK FOR PLZ
        if (!is_null($plz)) {
            $city = Yourdelivery_Model_City::getByPlz($plz);
            if ($city->count() == 0) {
                $this->logger->err('API - SERVICE - INDEX - FALLBACK: could not get cityId by plz ' . $plz);
                $this->getResponse()->setHttpResponseCode(404);
                return;
            }
            $this->logger->debug('API - SERVICE - INDEX - FALLBACK: found cityId by plz ' . $city->current()->id);
            $cityId = $city->current()->id;
        }

        if (is_null($lat) && is_null($lng) && is_null($cityId)) {

            die('please use the ftp upload, contact our it via it@lieferando.de');
        } elseif (!is_null($lat) && !is_null($lng)) {

            /**
             * @todo this case was never used and should be removed
             */
            $this->logger->info(sprintf('API - SERVICE - INDEX: searching for services by lng and lat: %s :: %s', $lng, $lat));

            $geo = new Default_Api_Google_Geocoding();
            $geo->ask(null, $lat, $lng);
            $plz = (integer) $geo->getPlz();

            if ($plz <= 0) {
                $this->message = 'Could not determine current postal code';
                $this->getResponse()->setHttpResponseCode(404);
                return;
            }

            $cityId = (integer) Yourdelivery_Model_City::getByPlz($plz);

            if ($cityId <= 0) {
                $this->message = 'Could not determine current city id from postal code';
                $this->getResponse()->setHttpResponseCode(404);
                return;
            }
        }

        $services = Yourdelivery_Model_DbTable_Restaurant::getListForApi($cityId, $offset, $limit);
        $countServicesWithoutLimit = $services['count'];
        unset($services['count']);

        foreach ($services as $service) {
            if ($service['premium']) {
                continue;
            }

            try {
                $serviceObj = new Yourdelivery_Model_Servicetype_Restaurant($service['serviceId']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }

            $serviceElement = $this->createServiceChild($serviceObj, $cityId);
            if ($serviceElement instanceof DOMElement) {
                $sElems->appendChild($serviceElement);
            }
            unset($serviceObj);
        }


        //finally we add an area to the response so we know everything about the current location
        try {
            $city = new Yourdelivery_Model_City($cityId);
            $aElem = $this->doc->createElement('area');
            $aElem->appendChild(create_node($this->doc, 'plz', $city->getPlz()));
            $aElem->appendChild(create_node($this->doc, 'cityId', $city->getId()));
            $aElem->appendChild(create_node($this->doc, 'city', $city->getFullName()));
            $this->xml->appendChild($aElem);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

        }

        // append total count of services
        $this->xml->appendChild(create_node($this->doc, 'servicecount', $countServicesWithoutLimit));

        //append to response
        $this->xml->appendChild($sElems);
    }

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>get information for one service</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     * <b>2.1. Paremeters:</b>
     *
     *  <code>
     *      type GET
     *      {
     *          <SERVICE-ID>  (INTEGER)
     *      }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>3.1. Response:</b>
     *
     *  <code>
     *      <service>
     *          <id>INTEGER</id>
     *          <qypeId>INTEGER</qypeId>
     *          <name>STRING</name>
     *          <info>STRING</info>
     *          <picture>STRING (URL)</picture>
     *          <plz>STIRNG</plz>
     *          <city>STRING</city>
     *          <telephon>STRING</telephon>
     *          <fax>STRING</fax>
     *          <link>STRING</link>
     *          <onlycash>BOOLEAN</onlycash>
     *          <allowcash>BOOLEAN</allowcash>
     *          <street>STRING</street>
     *          <category>STRING</category>
     *          <premium>BOOLEAN</premium>
     *          <open>BOOLEAN</open>
     *          <deliversTo>
     *              <deliverArea>
     *                  <cityId>INTEGER</cityId>
     *                  <parent>INTEGER</parent>
     *                  <plz>STRING</plz>
     *                  <city>STRING</city>
     *                  <deliverCost dimension="cent">INTEGER</deliverCost>
     *                  <minCost dimension="cent">INTEGER</minCost>
     *                  <deliverTime dimension="seconds">INTEGER</deliverTime>
     *                  <noDeliverCostAbove dimension="cent">INTEGER</noDeliverCostAbove>
     *              </deliverArea>
     *              ...
     *          </deliversTo>
     *          <tags>
     *              <tag>STRING</tag>
     *              ...
     *          </tags>
     *          <ratings>
     *              <advise>INTEGER[0-100]</advise>
     *              <quality>INTEGER[0-5]</quality>
     *              <delivery>INTEGER[0-5]</delivery>
     *              <total>INTEGER</total>
     *              <votes>INTEGER</votes>
     *              <title>STRING</title>
     *              <comment>LONGTEXT</comment>
     *              <author>STRING</author>
     *              <created>TIMESTAMP</created>
     *          </ratings>
     *          <openings>
     *              <day weekday="INTEGER[1-7,10]">
     *                <from>12:00</from>
     *                <until>21:45</until>
     *              </day>
     *              ...
     *          </openings>
     *          <menu>
     *              <dishCategory>
     *                  <id>INTEGER</id>
     *                  <name>STRING</name>
     *                  <picture>STRING (url)</picture>
     *                  <meals>
     *                      <meal>
     *                          <id>INTEGER</id>
     *                          <name>STRING</name>
     *                          <picture>STRING (url)</picture>
     *                          <description>STRING</description>
     *                          <hasspecials>INTEGER</hasspecials>
     *                          <sizes>
     *                              <size>
     *                                  <name>STIRNG</name>
     *                                  <sizeId>INTEGER</sizeId>
     *                                  <cost>INTEGER</cost>
     *                                  <pfand>INTEGER</pfand>
     *                                  <href>STRING (reference to meal)</href>
     *                              </size>
     *                              ...
     *                          </sizes>
     *                          <excludefrommincost>BOOLEAN</excludefrommincost>    (if true meal is not included in minimum order cost)
     *                          <mincount>INTEGER</mincount>                        (meal has to be ordered at least ... times)
     *                      </meal>
     *                      ...
     *                  </meals>
     *              </dishCategory>
     *              ...
     *          </menu>
     *      </service>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Examples:</b>
     *
     * <b>4.1. Example - Request:</b>
     *
     *  <code>
     *      <ul>
     *          <li>http://www.lieferando.local/get_service/15563</li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <service>
     *          <id>15563</id>
     *          <qypeId>6899</qypeId>
     *          <name>Aapka</name>
     *          <info>
     *              Das Angebot des Restaurants Aapka verspricht eine kulinarische Reise durch Indien. Die Grillgerichte werden im original indischen Lehmofen, dem Tandoor gegart. Lassen Sie sich verführen von den exotischen Speisen Indiens!
     *          </info>
     *          <picture>http://image.yourdelivery.de/lieferando.de/service/15563/Aapka-250-0.jpg</picture>
     *          <plz>10119</plz>
     *          <city>Berlin</city>
     *          <telephon>0303309973</telephon>
     *          <fax>030 33 099 755</fax>
     *          <link>lieferservice-aapka-berlin</link>
     *          <onlycash>0</onlycash>
     *          <allowcash>1</allowcash>
     *          <street>Kastanienallee 50</street>
     *          <category>Indisch</category>
     *          <premium>false</premium>
     *          <open>true</open>
     *          <deliversTo>
     *              <deliverArea>
     *                  <cityId>644</cityId>
     *                  <parent>0</parent>
     *                  <plz>10115</plz>
     *                  <city>Berlin (Mitte)</city>
     *                  <deliverCost dimension="cent">690</deliverCost>
     *                  <minCost dimension="cent">2200</minCost>
     *                  <deliverTime dimension="seconds">2400</deliverTime>
     *                  <noDeliverCostAbove dimension="cent">0</noDeliverCostAbove>
     *              </deliverArea>
     *              ...
     *          </deliversTo>
     *          <tags>
     *              <tag>Fleischgerichte</tag>
     *              ...
     *          </tags>
     *          <ratings>
     *              <advise>63</advise>
     *              <quality>4</quality>
     *              <delivery>3</delivery>
     *              <total>7</total>
     *              <votes>345</votes>
     *              <title>Leckeres Essen</title>
     *              <comment>Das Essen hat sehr gut geschmeckt. Ich bin sehr zufrieden.</comment>
     *              <author>Felix</author>
     *              <created>12345678</created>
     *          </ratings>
     *          <openings>
     *              <day weekday="0">
     *                  <from>12:00</from>
     *                  <until>23:00</until>
     *              </day>
     *              ...
     *          </openings>
     *          <menu>
     *              <dishCategory>
     *                  <id>73008</id>
     *                  <name>Suppen</name>
     *                  <picture>http://image.yourdelivery.de/lieferando.de/service/15563/categories/73008/Suppen-700-0.jpg</picture>
     *                  <meals>
     *                      <meal>
     *                          <id>615095</id>
     *                          <name>Dal Shorba</name>
     *                          <picture>http://image.yourdelivery.de/lieferando.de/service/15563/categories/73008/meals/615095/Dal+Shorba-150-150.jpg</picture>
     *                          <description>gehaltvolle Linsensuppe mit frischem Koriander</description>
     *                          <hasspecials>0</hasspecials>
     *                          <sizes>
     *                              <size>
     *                                  <name>Normal</name>
     *                                  <sizeId>94867</sizeId>
     *                                  <cost>350</cost>
     *                                  <pfand>0</pfand>
     *                                  <href>/get_meal/615095?size=94867</href>
     *                              </size>
     *                              ...
     *                          </sizes>
     *                          ...
     *                          <excludefrommincost>0</excludefrommincost>
     *                          <mincount>1</mincount>
     *                          <category>Suppen</category>
     *                      </meal>
     *                      ...
     *                  </meals>
     *              </dishCategory>
     *              ...
     *          </menu>
     *      </service>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - service found</li>
     *      <li>404 - service not found</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.09.2011
     *
     * @return HTTP-RESONSE-CODE
     */
    public function getAction() {

        $serviceId = (integer) $this->getRequest()->getParam('id');
        if ($serviceId <= 0) {
            return $this->getResponse()->setHttpResponseCode(404);
        }

        $service = null;
        try {
            $service = new Yourdelivery_Model_Servicetype_Restaurant($serviceId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return $this->getResponse()->setHttpResponseCode(404);
        }

        //default case, just an id has been provided
        try {

            //create service node with some data
            $sElem = $this->createServiceChild($service);

            //setup menu
            $mElem = $this->doc->createElement('menu');
            $hash = md5('servicemenu' . $service->getId());
            $menu = Default_Helpers_Cache::load($hash);
            if (!$menu) {
                list($menu, $parents) = $service->getMenu();
                Default_Helpers_Cache::store($hash, $menu);
            }

            foreach ($menu as $catId => $category) {
                $cElem = $this->doc->createElement('dishCategory');
                //append category details
                $cElem->appendChild(create_node($this->doc, 'id', $catId));
                $cElem->appendChild(create_node($this->doc, 'name', $category['name']));
                $cElem->appendChild(create_node($this->doc, 'picture', sprintf('http://%s/%s/service/%d/categories/%d/%s-700-0.jpg', $this->config->domain->timthumb, $this->config->domain->base, $serviceId, $catId, urlencode($category['name']))));

                //append meals
                $mealsElem = $this->doc->createElement('meals');
                foreach ($category['meals'] as $mealId => $mealSize) {

                    $mealData = current($mealSize);
                    $mealElem = $this->doc->createElement('meal');
                    $mealElem->appendChild(create_node($this->doc, 'id', $mealId));
                    $mealElem->appendChild(create_node($this->doc, 'name', stripslashes($mealData['name'])));
                    $mealElem->appendChild(create_node($this->doc, 'image', $mealData['hasPicture'] ? sprintf('http://%s/%s/service/%s/categories/%s/meals/%s/%s-%d-%d.jpg', $this->config->domain->timthumb, $this->config->domain->base, $service->getId(), $catId, $mealId, urlencode($mealData['name']), 150, 150) : null));
                    $mealElem->appendChild(create_node($this->doc, 'description', stripslashes(strip_tags($mealData['desc']))));
                    $mealElem->appendChild(create_node($this->doc, 'hasspecials', (integer) stripslashes($mealData['hasSpecials'])));
                    $mealElem->appendChild(create_node($this->doc, 'excludefrommincost', stripslashes((integer) $mealData['excludeFromMinCost'])));
                    $mealElem->appendChild(create_node($this->doc, 'mincount', (integer) $mealData['minAmount']));

                    $mealSizes = $this->doc->createElement('sizes');
                    foreach ($mealSize as $sizeId => $meal) {
                        //check if category size applys to meal size
                        if (!is_null($meal['cost'])) {
                            $mealSize = $this->doc->createElement('size');
                            $mealSize->appendChild(create_node($this->doc, 'name', $meal['sizeName']));
                            $mealSize->appendChild(create_node($this->doc, 'sizeId', $sizeId));
                            $mealSize->appendChild(create_node($this->doc, 'cost', $meal['cost']));
                            $mealSize->appendChild(create_node($this->doc, 'pfand', $meal['pfand']));
                            $mealSize->appendChild(create_node($this->doc, 'href', sprintf('/get_meal/%s?size=%s', $mealId, $sizeId)));
                            $mealSizes->appendChild($mealSize);
                            unset($mealSize);
                        }
                    }

                    $mealElem->appendChild($mealSizes);
                    $mealsElem->appendChild($mealElem);
                }
                $cElem->appendChild($mealsElem);

                //append category to root
                $mElem->appendChild($cElem);
                unset($cElem, $mealsElem);
            }
            $sElem->appendChild($mElem);

            //append to response
            $this->xml->appendChild($sElem);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->err(sprintf('API - SERVICE - GET: %s', $e->getMessage()));
            return $this->getResponse()->setHttpResponseCode(404);
        }
    }

    /**
     * the post method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function postAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the put method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function putAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the delete method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function deleteAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * helper method to create the xml for a service
     *
     * @param array   $service             service to create childs
     * @param integer $cityId              only get information for this cityId
     * @param boolean $verboseOpenings     show all openings
     *
     * @access private
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.10.2010
     *
     * @return DOMElement
     */
    protected function createServiceChild(Yourdelivery_Model_Servicetype_Abstract $serviceObj, $cityId = 0) {

        try {
            // use unix line breaks !!
            $view = new Default_View_Helper_Openings_Format();
            $openings = $serviceObj->getOpening()->getIntervals(strtotime('last sunday'), strtotime('next saturday'));

            $additionalInfo = __('Öffnungszeiten:') . "\n";
            $additionalInfo .= $view->formatOpeningsMerged($openings, 'linebreak') . "\n";


            // accepted payments for service
            $additionalInfo .= __('akzeptierte Bezahlarten:') . "\n";
            if ($serviceObj->isPaymentbar()) {
                $additionalInfo .= '* ' . __('Barzahlung') . "\n";
            }

            if (!$serviceObj->isOnlycash()) {
                $additionalInfo .= '* ' . __('Paypal & Gutschein') . "\n";
            }
            $additionalInfo .= "\n\n" . strip_tags($serviceObj->getDescription());

            //create service node with some data
            $sElem = $this->doc->createElement('service');
            $sElem->appendChild(create_node($this->doc, 'id', $serviceObj->getId()));
            $sElem->appendChild(create_node($this->doc, 'qypeId', $serviceObj->getQypeId()));
            $sElem->appendChild(create_node($this->doc, 'name', $serviceObj->getName()));
            $sElem->appendChild(create_node($this->doc, 'info', $additionalInfo));
            $sElem->appendChild(create_node($this->doc, 'picture', $serviceObj->getImg('api')));
            $sElem->appendChild(create_node($this->doc, 'plz', $serviceObj->getPlz()));
            $sElem->appendChild(create_node($this->doc, 'city', $serviceObj->getCity()->getFullName()));
            $sElem->appendChild(create_node($this->doc, 'telephon', $serviceObj->getTel()));
            $sElem->appendChild(create_node($this->doc, 'fax', $serviceObj->getFax()));
            $sElem->appendChild(create_node($this->doc, 'link', $serviceObj->getRestUrl()));
            $sElem->appendChild(create_node($this->doc, 'onlycash', (integer) $serviceObj->isOnlycash()));
            $sElem->appendChild(create_node($this->doc, 'allowcash', (integer) $serviceObj->isPaymentbar()));
            $sElem->appendChild(create_node($this->doc, 'street', $serviceObj->getStreet() . " " . $serviceObj->getHausnr()));
            $sElem->appendChild(create_node($this->doc, 'category', strlen($serviceObj->getCategory()->name)>0 ? $serviceObj->getCategory()->name : __('keine')));
            $sElem->appendChild(create_node($this->doc, 'premium', (integer) $serviceObj->isPremium()));

            $sElem->appendChild(create_node($this->doc, 'open', $serviceObj->getOpening()->isOpen(time()) ? "true" : "false"));

            //append openings
            $oElems = $this->doc->createElement('openings');
            foreach ($openings as $opening) {
                foreach ($opening as $key => $o) {
                    if (strpos($key, 'next') !== false) {
                        continue;
                    }
                    $oElem = $this->doc->createElement('day');
                    $oElem->setAttribute('weekday', $o['day']);
                    $oElem->appendChild(create_node($this->doc, 'from', date('H:i', $o['timestamp_from'])));
                    $oElem->appendChild(create_node($this->doc, 'until', date('H:i', $o['timestamp_until'])));
                    $oElems->appendChild($oElem);
                    unset($oElem);
                }
            }

            $sElem->appendChild($oElems);

            $plzElems = $this->doc->createElement('deliversTo');
            if ($cityId == 0) {
                $deliversTo = $serviceObj->getRanges();
                foreach ($deliversTo as $range) {
                    $plz = $this->doc->createElement('deliverArea');
                    $plz->appendChild(create_node($this->doc, 'cityId', $range['cityId']));
                    $plz->appendChild(create_node($this->doc, 'parent', $range['parentCityId']));
                    $plz->appendChild(create_node($this->doc, 'plz', $range['plz']));
                    $plz->appendChild(create_node($this->doc, 'city', $range['cityname']));
                    $plz->appendChild(create_node($this->doc, 'deliverCost', (integer) $range['delcost'], 'dimension', 'cent'));
                    $plz->appendChild(create_node($this->doc, 'minCost', (integer) $range['mincost'], 'dimension', 'cent'));
                    $plz->appendChild(create_node($this->doc, 'deliverTime', (integer) $range['deliverTime'], 'dimension', 'seconds'));
                    $plz->appendChild(create_node($this->doc, 'noDeliverCostAbove', (integer) $range['noDeliverCostAbove'], 'dimension', 'cent'));
                    $plzElems->appendChild($plz);
                }
            } else {
                $plz = $this->doc->createElement('deliverArea');
                $plz->appendChild(create_node($this->doc, 'cityId', $cityId));
                $plz->appendChild(create_node($this->doc, 'parent', (integer) $serviceObj->getCity()->getParentCityId()));
                $plz->appendChild(create_node($this->doc, 'plz', $serviceObj->getPlz()));
                $plz->appendChild(create_node($this->doc, 'city', $serviceObj->getCity()->getCity()));
                $plz->appendChild(create_node($this->doc, 'deliverCost', (integer) $serviceObj->getDeliverCost($cityId), 'dimension', 'cent'));
                $plz->appendChild(create_node($this->doc, 'minCost', (integer) $serviceObj->getMinCost($cityId), 'dimension', 'cent'));
                $plz->appendChild(create_node($this->doc, 'deliverTime', (integer) $serviceObj->getDeliverTime($cityId), 'dimension', 'seconds'));
                $plz->appendChild(create_node($this->doc, 'noDeliverCostAbove', (integer) $serviceObj->getTable()->getNoDeliverCostAbove($cityId), 'dimension', 'cent'));
                $plzElems->appendChild($plz);
            }
            $sElem->appendChild($plzElems);
            //append ratings
            $rElems = $this->doc->createElement('ratings');
            $rElems->appendChild(create_node($this->doc, 'advise', (integer) round($serviceObj->getRating()->getAverageAdvise())));
            $rElems->appendChild(create_node($this->doc, 'quality', (integer) round($serviceObj->getRating()->getAverageQuality())));
            $rElems->appendChild(create_node($this->doc, 'delivery', (integer) round($serviceObj->getRating()->getAverageDelivery())));
            $rElems->appendChild(create_node($this->doc, 'total', (integer) ($serviceObj->getRating()->getAverageQuality() + $serviceObj->getRating()->getAverageDelivery())));
            $rElems->appendChild(create_node($this->doc, 'votes', (integer) count($serviceObj->getRating()->getList(null, true))));
            $rElems->appendChild(create_node($this->doc, 'title', ''));
            $rElems->appendChild(create_node($this->doc, 'comment', ''));
            $rElems->appendChild(create_node($this->doc, 'author', ''));
            $rElems->appendChild(create_node($this->doc, 'created', ''));

            $sElem->appendChild($rElems);
            //append tags
            $tagElems = $this->doc->createElement('tags');
            $tags = $serviceObj->getTable()->getTags();
            foreach ($tags as $tag) {
                $tagElems->appendChild(create_node($this->doc, 'tag', $tag['tag']));
            }

            //append tags
            $sElem->appendChild($tagElems);

            return $sElem;
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->err(sprintf('API - SERVICE - createServiceChild: %s', $e->getMessage()));
            return null;
        }
    }

}
