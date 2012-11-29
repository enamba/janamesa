<?php

/**
 * @package Yourdelivery
 * @subpackage PartnerAPI
 */
/**
 * <b>Description:</b>
 *
 *  <ul>
 *      <li>
 *          
 *      </li>
 *  </ul>
 *
 * <b>Available Actions:</b>
 *  <ul>
 *      <li>
 *          index - get all orders for this service
 *      </li>
 *      <li>
 *          delete - disallowed - 403
 *      </li>
 *      <li>
 *          get - disallowed - 403
 *      </li>
 *      <li>
 *          post - disallowed - 403
 *      </li>
 *      <li>
 *          put - modify state of order(s)
 *      </li>
 *  </ul>
 *
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 13.08.2012
 */
require_once('AbstractController.php');

class Get_Partner_OrderController extends AbstractApiPartnerController {

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>
     *          login partner customer
     *      </li>
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
     *          limit          INTEGER
     *          state          INTEGER
     *          driver         STRING
     *      }
     *  </code>
     *
      -------------------------------------------------------------------------
     *
     * *
     * <b>3. Response:</b>
     *
     *  <code>
     *      <response>
     *          <orders>
     *              <order>
     *                  <id>INTEGER</id>
     *                  <nr>STRING</nr>
     *                  <hash>STRING</hash>
     *                  <time>INTEGER (timestamp)</time>
     *                  <deliverTime>INTEGER (timestamp)</deliverTime>
     *                  <total>INTEGER (cents)</total>
     *                  <taxes>
     *                      <tax type="INTEGER">INTEGER (cents)</tax>
     *                      ...
     *                  </taxes>
     *                  <charge>INTEGER (cents)</charge>
     *                  <deliverCost>INTEGER (cents)</deliverCost>
     *                  <discount>INTEGER (cents)</discount>
     *                  <discountcode>STRING</discountcode>
     *                  <paymentMethod>STRING[bar,paypal,credit,ebanking,bill]</paymentMethod>
     *                  <isRated>BOOLEAN</isRated>
     *                  <isFavorite>BOOLEAN</isFavorite>
     *                  <location>
     *                      <street>STRING</street>
     *                      <hausnr>STRING</hausnr>
     *                      <plz>STRING</plz>
     *                      <cityId>INTEGER (unique)</cityId>
     *                      <company>STRING</company>
     *                      <etage>STIRNG</etage>
     *                      <city>STRING</city>
     *                      <tel>STRING</tel>
     *                  </location>
     *                  <service>
     *                      <id>INTEGER</id>
     *                      <name>STRING</name>
     *                      <picture>STRING</picture>
     *                      <tel>STRING</tel>
     *                      <ratings>
     *                          <advise>INTEGER[0-100]</advise>
     *                          <quality>INTEGER[0-5]</quality>
     *                          <delivery>INTEGER[0-5]</delivery>
     *                          <total>INTEGER</total>
     *                          <votes>INTEGER</votes>
     *                          <title>STRING</title>
     *                          <comment>LONGTEXT</comment>
     *                          <author>STRING</author>
     *                          <created>TIMESTAMP</created>
     *                      </ratings>
     *                  </service>
     *                  <meals>
     *                      <meal>
     *                          <name>STRING</name>
     *                          <cost>INTEGER (cents)</cost>
     *                          <description>STRING</description>
     *                          <count>INTEGER</count>
     *                          <excludefrommincost>BOOLEAN</excludefrommincost>
     *                          <mincount>INTEGER</mincount>
     *                          <extras>
     *                              <extra>
     *                                  <name>STRING</name>
     *                                  <cost>INTEGER (cents)</cost>
     *                              </extra>
     *                              ...
     *                          </<extras>
     *                          <options>
     *                              <option>
     *                                  <name>STRING</name>
     *                                  <cost>INTEGER (cents)</cost>
     *                              </option>
     *                              ...
     *                          </options>
     *                      </meal>
     *                      ...
     *                  </meals>
     *              </order>
     *              ...
     *          </orders>
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
     *              curl "http://www.yourdelivery.local/get_partner_order/index?access=5ad2a6ea5662aa2b2508c29931c123c5&limit=10"
     *          </li>
     *      </ul>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <b>5.1. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - listing result</li>
     *      <li>404 - no accss provided</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     *
     * @return integer HTTP-RESPONSE-CODE
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     */
    public function indexAction() {
        $service = $this->_getService();
        if (!($service instanceof Yourdelivery_Model_Servicetype_Abstract)) {
            return $this->getResponse()->setHttpResponseCode(404);
        }


        $oElems = $this->doc->createElement('orders');

        //open orders are 1
        //delivered orders are 2
        $state = $this->getRequest()->getParam('state', 0);  // initial state
        $lowerThreshold = $this->getRequest()->getParam('threshold', 'today');
        $limit = $this->getRequest()->getParam('limit', null);
        $driver = $this->getRequest()->getParam('driver', null);

        $orders = $service->getTable()->getOrdersFilteredForPartner($limit, $state, $driver, $lowerThreshold);
        foreach ($orders as $orderData) {
            try {
                // append basic data. since uuid and access querys return different id cases, we do a distinction here
                $orderId = (integer) $orderData['orderId'];
                if ($orderId > 0) {
                    $order = new Yourdelivery_Model_Order($orderId);
                    $oElem = createOrderChild($order, 'order', $this->doc);
                    $oElems->appendChild($oElem);
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
        }

        $this->xml->appendChild($oElems);
    }

    /**
     * this method is not used and will be forbidden
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
     *      <li>
     *          update state and position of order(s)
     *      </li>
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
     *          "orders":[{
     *              "orderId": "INTEGER",
     *              "deliveryState": "INTEGER",
     *              "driver" : "STRING",
     *              "position"  : {
     *                  "lat":"LONG",
     *                  "lng":"LONG"
     *              }
     *          }]
     *      }
     *  </code>
     *
      -------------------------------------------------------------------------
     *
     * <b>3. Response:</b>
     *
     *  <code>
     *      <response>
     *          <success>BOOLEAN</success>
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
     *              curl -X PUT -d "parameters={'access':'5ad2a6ea5662aa2b2508c29931c123c5','orders':':[{'orderid':1599881,'position':{'lat':1,'lng':2}}]}" http://www.yourdelivery.local/get_partner_order?trigger=position
     *          </li>
     *          <li>
     *              curl -X PUT -d "parameters={'access':'5ad2a6ea5662aa2b2508c29931c123c5','orders':':[{'orderid':1599881,'driver':'samson','deliveryState':'1'}" http://www.yourdelivery.local/get_partner_order?trigger=state
     *          </li>
     *          <li>
     *              curl -X PUT -d "parameters={'access':'5ad2a6ea5662aa2b2508c29931c123c5','orders':':[{'orderid':1599881,'driver':'samson'}" http://www.yourdelivery.local/get_partner_order?trigger=pickorder
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <response>
     *          
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <b>5.1. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>404 - order not found or no access provided</li>
     *      <li>406 - parameters or access not provided (or not all data provided)</li>
     *      <li>409 - order already taken by another driver</li>
     *      <li>201 - data received and stored</li>
     *      <li>200 - action performed</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     *
     * @return integer HTTP-RESPONSE-CODE
     *
     * @author 
     * @since 
     */
    public function putAction() {
        $post = $this->_getPut();

        if (!isset($post['parameters'])) {
            $parameters = $this->getRequest()->getParam('parameters', null);
            if ($parameters === null) {
                $this->logger->warn('API - PARTNER - ORDER - PUT: did not get any parameters');
                $this->message = "didn't get parameters";
                return $this->getResponse()->setHttpResponseCode(406);
            }
            $post['parameters'] = $parameters; //overwrite from get parameter, if put is not successful
        }

        $json = json_decode($post['parameters']);
        try {
            $service = $this->_getService($json);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->warn(sprintf('API - PARTNER - ORDER - PUT: could not find service by json'));
            return $this->getResponse()->setHttpResponseCode(404);
        }

        if (!($json instanceof stdClass)) {
            $this->message = 'could not decode json';
            $this->logger->warn(sprintf('API - PARTNER - ORDER - PUT: json could not be decoded "%s"', $post['parameters']));
            return $this->getResponse()->setHttpResponseCode(406);
        }

        foreach ($json->orders as $orderJson) {
            $orderId = (integer) $orderJson->orderid;
            try {
                $orderObj = new Yourdelivery_Model_Order($orderId);
                if ($orderObj->getService()->getId() != $service->getId()) {
                    /**
                     * shouldn't we cancel response here
                     * because somthings going deeply wrong?? 
                     */
                    $this->logger->warn(sprintf('API - PARTNER - ORDER - PUT: order #%s belongs to #%s but #%s tried to change status', $orderId, $orderJson->getService()->getId(), $service->getId()));
                    continue;
                }

                $action = $this->getRequest()->getParam('trigger');

                $driver = $orderJson->driver;
                if (strlen($driver) > 0) {
                    $driverTable = new Yourdelivery_Model_DbTable_Restaurant_Partner_Drivers();
                    $subAccountId = $driverTable->findDriver($driver, $service->getId());
                } else {
                    if ($action == 'state' || $action == 'pickorder') {
                        $this->message = __p('Es wurde kein Fahrer für diese Bestellung angegeben');
                        $this->logger->warn(sprintf('API - PARTNER - ORDER - PUT: did not provide a driver for action %s', $action));
                        return $this->getResponse()->setHttpResponseCode(406);
                    }
                }

                if ($action == 'position') {
                    $positionTable = new Yourdelivery_Model_DbTable_Order_Geolocation_PositionLog();
                    $positionTable->createRow(array(
                        'orderId' => $orderObj->getId(),
                        'lng' => (float) $orderJson->position->lng,
                        'lat' => (float) $orderJson->position->lat
                    ))->save();
                    return $this->getResponse()->setHttpResponseCode(201);
                }

                if ($action == 'state') {
                    $statusTable = new Yourdelivery_Model_DbTable_Order_Geolocation_StatusLog();
                    $statusTable->createRow(array(
                        'orderId' => $orderObj->getId(),
                        'statusId' => (integer) $orderJson->deliveryState
                    ))->save();
                    return $this->getResponse()->setHttpResponseCode(201);
                }

                if ($action == 'pickorder' && $subAccountId > 0) {
                    $orderTable = new Yourdelivery_Model_DbTable_Restaurant_Partner_Drivers_Orders();
                    try {
                        $rowId = $orderTable->createRow(array(
                                    'orderId' => $orderObj->getId(),
                                    'subAccountId' => $subAccountId
                                ))->save();
                        $this->logger->debug(sprintf('API - PARTNER - ORDER - PUT: successfully created row #%s in driver orders', $rowId));
                        return $this->getResponse()->setHttpResponseCode(200);
                    } catch (Exception $e) {
                        $this->logger->warn(sprintf('API - PARTNER - ORDER - PUT: conflict while picking order %s for driver %s: %s', $orderId, $driver, $e->getMessage()));
                        $this->message = __p('Diese Bestellung ist schon vom Fahrer "%s" in der Auslieferung', $driver);
                        return $this->getResponse()->setHttpResponseCode(409);
                    }
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->message = __p('Konnte Bestellung nicht finden');
                $this->logger->warn(sprintf('API - PARTNER - ORDER - PUT: could not find order by id #%s', $orderId));
                continue;
            }
        }
    }

    /**
     * this method is not used and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function postAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * this method is not used and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function deleteAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

}
