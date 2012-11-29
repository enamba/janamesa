<?php

/**
 * @package Yourdelivery
 * @subpackage API
 */

/**
 * <b>Location API</b>
 *  <ul>
 *      <li>
 *          this API is for create, update and delete locations / addresses for one customer
 *      </li>
 *  </ul>
 * 
 *
 * @since 07.09.2010
 * @author Matthias Laug <laug@lieferando.de>
 */
class Get_LocationController extends Default_Controller_RestBase {

    /**
     * list all available locations, based on the unique uuid of the iphone
     * or any other mobile device. Since we would get a location for each order
     * we are trying to hash each location and filter the duplicates
     *
     * @return integer HTTP-RESPONSE-CODE 200
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 28.03.2012
     *
     * @deprecated this function will always return response code 200
     *
     * to search locations for customer use logged-in /get_customer
     */
    public function indexAction() {
        $this->message = 'this method is deprecated';
        return $this->getResponse()->setHttpResponseCode(200);
    }

    /**
     * the get method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function getAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>create a new location for a customer</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     * <b>2.1. Paremeters:</b>
     *
     *  <code>
     *      type JSON
     *      parameters =
     *      {
     *          "access":"STRING",
     *          "street":"STRING",
     *          "hausnr":"STRING",
     *          "plz":"STRING",
     *          "cityId":"INTEGER",
     *          "tel":"STRING"
     *          "company":"STRING" *,
     *          "etage":"STRING" *,
     *          "comment":"STRING" *,
     *          "primary":"INTEGER" (0 or 1) *
     *      }
     * </code>
     *
     * * = optional parameters
     * 
     * -------------------------------------------------------------------------
     *
     * <b>4. Examples:</b>
     *
     * <b>4.1. Example - Request:</b>
     *
     *  <code>
     *      <ul>
     *          <li>
     *              curl -X POST -d parameters='{"access":"f28a353195eafca74edcc9d9e7270ead","street":"Test Street","hausnr":"4","plz":"01234","cityId":"644", "primary":"1"}'  http://www.yourdelivery.local/get_location
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *              <id>276110</id>
     *              <success>true</success>
     *              <message>Adresse erfolgreich angelegt</message>
     *              <url></url>
     *              <anchortext></anchortext>
     *              <fidelity>
     *                  <points>0</points>
     *                  <message></message>
     *              </fidelity>
     *              <errorkey></errorkey>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>201 location successfully created</li>
     *      <li>406 no parameters provided or data invalid</li>
     *      <li>403 no access provided for customer</li>
     *      <li>500 failed to create location, application error</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @return integer HTTP-RESPONSE-CODE
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.09.2010
     */
    public function postAction() {
        $post = $this->getRequest()->getPost();

        if (!isset($post['parameters'])) {
            $this->logger->err('API - LOCATION - POST: no parameters provided');
            $this->message = 'no params provided';
            return $this->getResponse()->setHttpResponseCode(406);
        }

        $json = json_decode($post['parameters']);
        $data = json_decode($post['parameters'], true);
        if (is_object($json)) {
            try {
                $customer = $this->_getCustomer($json);

                $form = new Yourdelivery_Form_Api_Location();

                if (!$form->isValid($data)) {
                    $this->returnFormErrors($form->getMessages());
                    return $this->getResponse()->setHttpResponseCode(406);
                }

                $location = new Yourdelivery_Model_Location();
                $location->setStreet($json->street);
                $location->setHausnr($json->hausnr);
                $location->setPlz($json->plz);
                $location->setCityId($json->cityId);
                $location->setCustomerId($customer->getId());
                $location->setComment($json->comment);
                $location->setTel($json->tel);
                $location->setCompanyName($json->company);
                $location->setEtage($json->etage);
                $location->setPrimary((boolean) $json->primary);
                $id = $location->save();
                if ($id === false) {
                    $this->logger->err('API - LOCATION - POST: could not save location');
                    return $this->getResponse()->setHttpResponseCode(500);
                }

                $this->logger->info(sprintf('API - LOCATION - POST: location #%s succesfully created by customer #%s %s', $id, $customer->getId(), $customer->getFullname()));
                $this->xml->appendChild(create_node($this->doc, 'id', $id));
                $this->message = __('Adresse erfolgreich angelegt');
                return $this->getResponse()->setHttpResponseCode(201);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return $this->getResponse()->setHttpResponseCode(403);
            }
        }
        $this->logger->err('API - LOCATION - POST: could not encode json');
        $this->message = 'could not encode json';
        return $this->getResponse()->setHttpResponseCode(406);
    }

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>update an existing location for a customer</li>
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
     *          <LOCATION-ID>   (INTEGER)
     *      }
     * 
     *      type JSON
     *      parameters =
     *      {
     *          "access":"STRING",
     *          "street":"STRING",
     *          "hausnr":"STRING",
     *          "plz":"STRING",
     *          "cityId":"INTEGER",
     *          "tel":"STRING"
     *          "company":"STRING" *,
     *          "etage":"STRING" *,
     *          "comment":"STRING" *,
     *          "primary":"INTEGER" (0 or 1) *
     *      }
     * </code>
     *
     * * = optional parameters
     * 
     * -------------------------------------------------------------------------
     *
     * <b>4. Examples:</b>
     *
     * <b>4.1. Example - Request:</b>
     *
     *  <code>
     *      <ul>
     *          <li>
     *              curl -X PUT -d parameters='{"access":"f28a353195eafca74edcc9d9e7270ead","street":"Test Street","hausnr":"4","plz":"01234","cityId":"644", "primary":"1"}'  http://www.yourdelivery.local/get_location/276110
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *              <id>276110</id>
     *              <success>true</success>
     *              <message>Adresse erfolgreich angelegt</message>
     *              <url></url>
     *              <anchortext></anchortext>
     *              <fidelity>
     *                  <points>0</points>
     *                  <message></message>
     *              </fidelity>
     *              <errorkey></errorkey>
     *      </response>
     *  </code>
     * 
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes</b>
     *      
     *  <ul>
     *      <li>200 - location successfully updated</li>
     *      <li>406 - no parameters provided or data invalid</li>
     *      <li>403 - no access provided for customer</li>
     *      <li>404 - location not found</li>
     *      <li>406 - could not validate data</li>
     *  </ul>
     *
     * @return integer HTTP-RESPONSE-CODE
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.09.2010
     */
    public function putAction() {
        $post = $this->_getPut();

        if (!isset($post['parameters'])) {
            $parameters = $this->getRequest()->getParam('parameters', null);
            if ($parameters === null) {
                $this->logger->err('API - LOCATION - PUT: did not get any parameters');
                return $this->getResponse()->setHttpResponseCode(406);
            }
            $post['parameters'] = $parameters; //overwrite from get parameter, if put is not successful
        }

        $json = json_decode($post['parameters']);
        if (is_object($json)) {
            try {
                $customer = $this->_getCustomer($json);

                $locationId = (integer) $this->getRequest()->getParam('id');
                if ($locationId <= 0) {
                    $this->logger->warn(sprintf('API - LOCATION - PUT: locationId #%s not valid', $locationId));
                    $this->message = 'locationId not valid';
                    return $this->getResponse()->setHttpResponseCode(404);
                }

                $params = (array) json_decode($post['parameters']);
                $form = new Yourdelivery_Form_Api_Location();
                if (!$form->isValid($params)) {
                    $this->returnFormErrors($form->getMessages());
                    return $this->getResponse()->setHttpResponseCode(406);
                }

                $location = new Yourdelivery_Model_Location($locationId);
                $location->setStreet($json->street);
                $location->setHausnr($json->hausnr);
                $location->setPlz($json->plz);
                $location->setCityId($json->cityId);
                $location->setTel($json->tel);
                $location->setCustomerId($customer->getId());
                $location->setComment($json->comment);
                $location->setCompanyName($json->company);
                $location->setEtage($json->etage);
                $location->setPrimary((boolean)$json->primary);
                $location->save();
                $this->logger->info(sprintf('API - LOCATION - PUT: location #%s successfully updated by customer #%s %s', $locationId, $customer->getId(), $customer->getFullname()));
                $this->message = __('Adresse erfolgreich bearbeitet');
                return;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->message = 'could not find location by id';
                $this->logger->err(sprintf('API - LOCATION - PUT: could not find location by given id %d', $locationId));
                return $this->getResponse()->setHttpResponseCode(403);
            }
        }

        $this->logger->err('API - LOCATION - PUT: could not encode json');
        $this->message = 'no valid json';
        return $this->getResponse()->setHttpResponseCode(406);
    }

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>delete an existing location for a customer</li>
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
     *          <LOCATION-ID>    (INTEGER)
     *      }
     *
     *      one of:
     *          type JSON
     *              parameters =
     *              {
     *                  access    : STRING         (access key of customer)
     *              }
     *          type GET
     *              {
     *                  <ACCESS>    (STRING)        (access key of customer)
     *              }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>3.1. Response:</b>
     *
     *  <code>
     *      <response>
     *          <success>BOOLEAN</success>
     *          <message>STRING</message>
     *      </response>
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
     *          <li>
     *              curl -d parameters='{"access":"f28a353195eafca74edcc9d9e7270ead"}' -H "X-HTTP-Method-Override: DELETE" -X POST http://staging.lieferando.de/get_location/257619
     *          </li>
     *          <li>
     *              curl -X DELETE http://staging.lieferando.de/get_location/257619?access=f28a353195eafca74edcc9d9e7270ead
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *          <success>true</success>
     *          <message></message>
     *          <fidelity>
     *              <points>0</points>
     *              <message></message>
     *          </fidelity>
     *          <memory>10</memory>
     *      </response>
     *  </code>
     *
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - location successfully deleted</li>
     *      <li>404 - no locationId provided</li>
     *      <li>403 - failed deleting location</li>
     *      <li>406 - no / invalid params provided</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @return integer HTTP-RESPONSE-CODE
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 14.09.2010
     */
    public function deleteAction() {
        $post = $this->getRequest()->getPost();
        $getAccess = $this->getRequest()->getParam('access', null);

        if (!isset($post['parameters']) && is_null($getAccess)) {
            $this->logger->err('API - LOCATION - DELETE: no parameters provided');
            $this->message = 'no params provided';
            return $this->getResponse()->setHttpResponseCode(406);
        }

        $json = json_decode($post['parameters']);
        if (is_null($json) && !is_null($getAccess)) {
            // fallback for access via GET param
            $this->logger->info(sprintf('API - LOCATION - DELETE: Fallback with access-key "%s" via GET param', $getAccess));
            $json = new stdClass();
            $json->access = $getAccess;
        }

        if (is_object($json)) {
            try {
                $customer = $this->_getCustomer($json);
                $locationId = (integer) $this->getRequest()->getParam('id');
                if ($locationId <= 0) {
                    $this->logger->err(sprintf('API - LOCATION - DELETE: try deleting location without providing a locationId by customer #%s %s', $customer->getId(), $customer->getFullname()));
                    $this->message = 'locationId not valid';
                    return $this->getResponse()->setHttpResponseCode(404);
                }
                $location = new Yourdelivery_Model_Location($locationId);
                if ($location->getCustomer()->getId() != $customer->getId()) {
                    $this->logger->err(sprintf('API - LOCATION - DELETE: try deleting location #%s which doesnt belong to customer #%s %s', $location->getId(), $customer->getId(), $customer->getFullname()));
                    $this->message = 'location and customer do not match';
                    return $this->getResponse()->setHttpResponseCode(403);
                }

                if ($location->isDeleted()) {
                    $this->logger->err(sprintf('API - LOCATION - DELETE: try deleting location #%s which is deleted already (customer #%s %s)', $location->getId(), $customer->getId(), $customer->getFullname()));
                    $this->message = 'location is deleted already';
                    return $this->getResponse()->setHttpResponseCode(403);
                }

                // remove location here
                $location->remove();
                $this->message = __('Adresse erfolgreich entfernt.');
                $this->logger->info(sprintf('API - LOCATION - DELETE: succesfully deleted location #%s by customer #%s %s', $locationId, $customer->getId(), $customer->getFullname()));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->message = 'location not found';
                $this->logger->err(sprintf('API - LOCATION - DELETE: failed with exception %s', $e->getMessage()));
                return $this->getResponse()->setHttpResponseCode(403);
            }
        } else {
            $this->logger->err(sprintf('API - LOCATION - DELETE: no json object'));
            $this->message = 'invalid access format';
            return $this->getResponse()->setHttpResponseCode(406);
        }
    }

}
