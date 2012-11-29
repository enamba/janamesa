<?php

require_once APPLICATION_PATH . '/controllers/Get/ServiceController.php';

/**
 * @package Yourdelivery
 * @subpackage API
 */

/**
 * <b>Description:</b>
 *
 *  <ul>
 *      <li>
 *          get list of best services
 *      </li>
 *  </ul>
 *
 * <b>Available Actions:</b>
 *  <ul>
 *      <li>
 *          get - get a list of best services based on given location
 *      </li>
 *      <li>
 *          delete - disallowed - 403
 *      </li>
 *      <li>
 *          post - disallowed - 403
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
 * @authorFelix Haferkorn <haferkorn@lieferando.de>
 * @since 05.07.2012
 * 
 * @see http://ticket/browse/YD-2698
 */
class Get_Best_ServiceController extends Get_ServiceController {

    public function preDispatch() {
        $this->_allowCache = false;
        parent::preDispatch();
    }

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>get list of best services</li>
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
     *          cityId      INTEGER    (cityId of area to serach for best services)
     *          
     *          optional parameters:
     *              limit       INTEGER    (how many best services should be loaded)
     *      }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>3.1. Response:</b>
     *
     *  <code>
     *      
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
     *          <li>http://www.lieferando.de/get_best_service/644</li>
     *          <li>http://www.lieferando.de/get_best_service/644?limit=5</li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
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
     *      <li>403 - cityId missing</li>
     *      <li>404 - cityId not found</li>
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
    public function getAction() {
        $request = $this->getRequest();

        $cityId = $request->getParam('id', null);
        if (is_null($cityId)) {
            $this->message = 'cityId missing';
            return $this->getResponse()->setHttpResponseCode(403);
        }

        $limit = (integer) $request->getParam('limit', 10);
        $this->logger->info(sprintf('API - BEST - SERVICE - GET: searching for best services for city #%s - limit %s', $cityId, $limit));


        // build up new location object without saving
        try {
            $city = new Yourdelivery_Model_City($cityId);
            $location = new Yourdelivery_Model_Location();
            $location->setData(array('cityId' => $city->getId(), 'plz' => $city->getPlz()));
            $bestServices = $location->getBestServices($limit);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->message = 'cityId not found';
            return $this->getResponse()->setHttpResponseCode(404);
        }

        $sElems = $this->doc->createElement('services');
        foreach ($bestServices as $bestService) {

            $serviceElement = $this->createServiceChild($bestService, $cityId);
            if ($serviceElement instanceof DOMElement) {
                $sElems->appendChild($serviceElement);
            }
        }

        $this->xml->appendChild($sElems);
    }

    /**
     * the post method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function indexAction() {
        return $this->getResponse()->setHttpResponseCode(403);
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

}