<?php

/**
 * @package Yourdelivery
 * @subpackage API
 */

/**
 * PLZ Api
 * @since 07.09.2010
 * @author mlaug
 */
class Get_PlzController extends Default_Controller_RestBase {

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>
     *          Use this api call for autocompletion. You can either provide the first
     *          letters of a postal code or a latitude and longitude to get the according
     *          postal code with cityId
     *      </li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     *  <code>
     *      type GET
     *      {
     *          one of them:
     *              plz       STRING      (zip code)
     *              lng & lat FLOATS      (gps coordinates)
     *      }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>3. Response:</b>
     *
     *  <code>
     *      <response>
     *          <suggestions>
     *              <suggestion>
     *                  <plz>STRING</plz>
     *                  <city>STRING</city>
     *                  <cityId>INTEGER (unique)</cityId>
     *                  <parentId>INTEGER (unique)</parentId>
     *                  <restUrl>STRING</restUrl>
     *              </suggestion>
     *              ...
     *          </suggestions>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Example:</b>
     *
     *  <code>
     *      <ul>
     *          <li>http://www.lieferando.de/get_plz?plz=10115</li>
     *          <li>http://www.lieferando.de/get_plz?plz=10115&city=Berlin</li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <suggestions>
     *          <suggestion>
     *              <plz>10115</plz>
     *              <city>10115 Berlin</city>
     *              <cityId>644</cityId>
     *              <parentId></parentId>
     *              <restUrl>lieferservice-berlin-10115</restUrl>
     *          </suggestion>
     *          ...
     *      </suggestions>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - valid call, responding with postal codes</li>
     *      <li>400 - no parameters provided</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.09.2010
     *
     * @return HTTP-RESPONSE-CODE
     */
    public function indexAction() {
        $lat = $this->getRequest()->getParam('lat', null);
        $lng = $this->getRequest()->getParam('lng', null);
        $plz = $this->getRequest()->getParam('plz', null);
        $city = $this->getRequest()->getParam('city', null);

        if ($lng !== null && $lat !== null) {
            $geo = new Default_Api_Google_Geocoding();
            $geo->ask(null, $lat, $lng);
            $plz = $geo->getPlz();
            $this->logger->info(sprintf('API - PLZ - INDEX: searching for zip-code with lat and lng %s :: %s and found %s', $lat, $lng, $plz));
            $cities = Yourdelivery_Model_Autocomplete::plzLimited($plz, 20);
            $areas = $this->doc->createElement('areas');
            foreach ($cities as $city) {
                $areas->appendChild(create_node($this->doc, 'cityId', $city['id'], 'plz', $city['plz']));
                $areas->appendChild(create_node($this->doc, 'plz', $city['plz']));
                $areas->appendChild(create_node($this->doc, 'city', $city['value']));
            }
            $this->xml->appendChild($areas);
            return;
        } elseif ($plz !== null && $city !== null) {
            // search by plz and city
            $this->logger->info(sprintf('API - PLZ - INDEX: searching for cityId by plz %s and city %s', $plz, $city));
            $cityTable = new Yourdelivery_Model_DbTable_City();
            $cityRows = $cityTable->findByPlzAndCity($plz, $city);

            if (!$cityRows) {
                $this->message = __('Es konnte keine Stadt gefunden werden. Bitte erstelle Deine Adresse manuell.');
            }

            $suggestions = $this->doc->createElement('suggestions');
            foreach ($cityRows as $cityRow) {
                $suggestion = $this->doc->createElement('suggestion');
                $suggestion->appendChild(create_node($this->doc, 'plz', $cityRow['plz']));
                $suggestion->appendChild(create_node($this->doc, 'city', $cityRow['city']));
                $suggestion->appendChild(create_node($this->doc, 'cityId', $cityRow['id']));
                $suggestion->appendChild(create_node($this->doc, 'parentId', $cityRow['parentCityId']));
                $suggestion->appendChild(create_node($this->doc, 'restUrl', urlencode($cityRow['restUrl'])));
                $suggestions->appendChild($suggestion);
                unset($suggestion);
            }
            $this->xml->appendChild($suggestions);
            return;
        } elseif ($plz !== null) {
            $result = Yourdelivery_Model_Autocomplete::plzLimited($plz, 20);
            $suggestions = $this->doc->createElement('suggestions');
            foreach ($result as $p) {
                $suggestion = $this->doc->createElement('suggestion');
                $suggestion->appendChild(create_node($this->doc, 'plz', $p['plz']));
                $suggestion->appendChild(create_node($this->doc, 'city', $p['value']));
                $suggestion->appendChild(create_node($this->doc, 'cityId', $p['id']));
                $suggestion->appendChild(create_node($this->doc, 'parentId', $p['parentCityId']));
                $suggestion->appendChild(create_node($this->doc, 'restUrl', urlencode($p['restUrl'])));
                $suggestions->appendChild($suggestion);
                unset($suggestion);
            }
            $this->xml->appendChild($suggestions);
            return;
        }

        $this->message = 'no parameters provided';
        $this->logger->err('API - PLZ - INDEX: no lat/lng/plz has been provided');
        return $this->getResponse()->setHttpResponseCode(400);
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
     * the get method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function getAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

}
