<?php

/**
 * @package Yourdelivery
 * @subpackage API
 */

/**
 * Description of OrderController
 * @since 09.07.2010
 * @author mlaug
 */
class Get_OrderController extends Default_Controller_RestBase {

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>get all my orders based on the given access key</li>
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
     *              <ACCESS> (STRING)
     *
     *          optional:
     *              <LIMIT>     (INTEGER)   (* optional limit of result)
     *              <OFFSET>    (INTEGER)   (* optional offset)
     *      }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
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
     * <b>4.1. - Example - Request</b>
     *
     *  <code>
     *      <ul>
     *          <li>http://www.yourdelivery.local/get_order?access=dc9aeffb7ef67068d1d19fb3d246060a</li>
     *          <li>http://www.yourdelivery.local/get_order?access=dc9aeffb7ef67068d1d19fb3d246060a&limit=10</li>
     *          <li>http://www.yourdelivery.local/get_order?access=dc9aeffb7ef67068d1d19fb3d246060a&limit=10&offset=10</li>
     *      </ul>
     *  </code>

     * <b>4.2. - Example - Response</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *              <orders>
     *                  <order>
     *                      <id>446666</id>
     *                      <nr>cuR5kIbx</nr>
     *                      <hash>ed6521b0cb73312f29b1e377a62badbb</hash>
     *                      <time>1320081766</time>
     *                      <deliverTime>1320081766</deliverTime>
     *                      <total>1350</total>
     *                      <taxes>
     *                          <tax type="7">88</tax>
     *                          <tax type="19">0</tax>
     *                      </taxes>
     *                      <charge>27</charge>
     *                      <deliverCost>0</deliverCost>
     *                      <discount>700</discount>
     *                      <discountcode>mEuaW9oWoKibgZx</discountcode>
     *                      <paymentMethod>paypal</paymentMethod>
     *                      <isRated>1</isRated>
     *                      <isFavorite/>
     *                      <location>
     *                          <street>Hammer Straße</street>
     *                          <hausnr>32</hausnr>
     *                          <plz>16515</plz>
     *                          <cityId>959</cityId>
     *                          <company/>
     *                          <etage>Erdgeschoss</etage>
     *                          <city>Oranienburg</city>
     *                          <tel>015140031777</tel>
     *                          <onlycash>0</onlycash>
     *                          <allowcash>1</allowcash>
     *                          <deliversTo>
     *                              <deliverArea>
     *                                  <cityId>26217</cityId>
     *                                  <parent>0</parent>
     *                                  <plz>50-000</plz>
     *                                  <city>50-000 Wrocław</city>
     *                                  <deliverCost dimension="cent">0</deliverCost>
     *                                  <minCost dimension="cent">1500</minCost>
     *                                  <deliverTime dimension="seconds">3600</deliverTime>
     *                              </deliverArea>
     *                              ...
     *                          </deliversTo>
     *                          <openings>
     *                              <day weekday="0">
     *                                  <from>11:00</from>
     *                                  <until>22:00</until>
     *                              </day>
     *                              ...
     *                          </openings>
     *                      </location>
     *                  <service>
     *                      <id>14610</id>
     *                      <name>Pizzeria Toscana</name>
     *                      <picture>http://image.yourdelivery.de/lieferando.de/service/14610/Pizzeria+Toscana-250-0.jpg</picture>
     *                      <tel>03304 31751</tel>
     *                      <ratings>
     *                          <advise>100</advise>
     *                          <quality>1</quality>
     *                          <delivery>4</delivery>
     *                          <total>5</total>
     *                          <votes>1</votes>
     *                          <title>this is a test title</title>
     *                          <comment>blub</comment>
     *                          <author>Felix</author>
     *                          <created>1325980222</created>
     *                      </ratings>
     *                  </service>
     *                  <meals>
     *                      <meal>
     *                          <name>Insalata Toscana</name>
     *                          <cost>700</cost>
     *                          <description>gemischter Salat mit Thunfisch, Schafskäse, Vorderschinken und Ei</description>
     *                          <count>1</count>
     *                          <excludefrommincost>0</excludefrommincost>
     *                          <mincount>1</mincount>
     *                          <extras/>
     *                          <options/>
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
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - found orders</li>
     *      <li>404 - no uuid provided</li>
     *      <li>406 - could not find customer</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 15.10.2011
     *
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 28.12.2011
     *
     * @return HTTP-RESPONSE-CODE
     */
    public function indexAction() {

        $uuid = $this->_request->uuid;
        $access = $this->_request->access;
        $limit = $this->_request->limit;
        $offset = $this->_request->offset;

        if (($uuid !== null && strlen($uuid) > 0) || ($access !== null && strlen($access) > 0)) {

            $oElems = $this->doc->createElement('orders');
            $orders = array();
            if (strlen($uuid) > 0) {
                
                // We do not allow searching for UUIDs in the API
                $this->logger->warn(sprintf('API - ORDER - INDEX: someone still searches for orders by uuid %s although it is not supported any longer' , $uuid));
                $orders = array();
            } else {
                $this->logger->debug('searching for orders by access ' . $access);
                try {
                    $customer = $this->_getCustomer();
                    if ($customer) {
                        list($orders, $countOrders) = $customer->getTable()->getOrdersSelectWithCountRows(true, $limit, $offset);
                        $oElems->appendChild(create_node($this->doc, 'ordersCount', (integer) $countOrders));
                        $this->logger->debug(sprintf('found %d order by access %s', count($orders), $access));
                    }
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->logger->warn('API - ORDER - INDEX: could not find customer by given access ' . $access);
                    $this->message = 'could not find customer';
                    $this->getResponse()->setHttpResponseCode(406);
                }
            }
            
            foreach ($orders as $orderData) {
                try {
                    // append basic data. since uuid and access querys return different id cases, we do a distinction here
                    $orderId = (integer) $orderData['ID'];
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
            return;
        }

        $this->logger->err('API - ORDER - INDEX: no uuid provided');
        $this->getResponse()->setHttpResponseCode(404);
    }

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>get information for single order by given orderId</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     *  <code>
     *      type GET
     *      {
     *          <ORDERID>  (INTEGER)
     *      }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>3. Response:</b>
     *
     *  <code>
     *      <response>
     *          <order>
     *              <id>INTEGER</id>
     *              <nr>STRING</nr>
     *              <hash>STRING</hash>
     *              <time>INTEGER (timestamp)</time>
     *              <deliverTime>INTEGER (timestamp)</deliverTime>
     *              <total>INTEGER (cents)</total>
     *              <taxes>
     *                  <tax type="INTEGER">INTEGER (cents)</tax>
     *                  ...
     *              </taxes>
     *              <charge>INTEGER (cents)</charge>
     *              <deliverCost>INTEGER (cents)</deliverCost>
     *              <discount>INTEGER (cents)</discount>
     *              <discountcode>STRING</discountcode>
     *              <paymentMethod>STRING[bar,paypal,credit,ebanking,bill]</paymentMethod>
     *              <isRated>BOOLEAN</isRated>
     *              <isFavorite>BOOLEAN</isFavorite>
     *              <location>
     *                  <street>STRING</street>
     *                  <hausnr>STRING</hausnr>
     *                  <plz>STRING</plz>
     *                  <cityId>INTEGER (unique)</cityId>
     *                  <company>STRING</company>
     *                  <etage>STIRNG</etage>
     *                  <city>STRING</city>
     *                  <tel>STRING</tel>
     *              </location>
     *              <service>
     *                  <id>INTEGER</id>
     *                  <name>STRING</name>
     *                  <picture>STRING</picture>
     *                  <tel>STRING</tel>
     *                  <ratings>
     *                      <advise>INTEGER[0-100]</advise>
     *                      <total>INTEGER[0-10]</total>
     *                      <votes>INTEGER</votes>
     *                  </ratings>
     *              </service>
     *              <meals>
     *                  <meal>
     *                      <name>STRING</name>
     *                      <cost>INTEGER (cents)</cost>
     *                      <description>STRING</description>
     *                      <count>INTEGER</count>
     *                      <extras>
     *                          <extra>
     *                              <name>STRING</name>
     *                              <cost>INTEGER (cents)</cost>
     *                          </extra>
     *                          ...
     *                      </<extras>
     *                      <options>
     *                          <option>
     *                              <name>STRING</name>
     *                              <cost>INTEGER (cents)</cost>
     *                          </option>
     *                          ...
     *                      </options>
     *                  </meal>
     *                  ...
     *              </meals>
     *          </order>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Example:</b>
     *
     *  <code>
     *      <ul>
     *          <li>http://www.lieferando.de/get_order/446666</li>
     *      </ul>
     *  </code>
     *
     * <b>4.1. Example - Response</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *              <order>
     *                  <id>446666</id>
     *                  <nr>cuR5kIbx</nr>
     *                  <hash>ed6521b0cb73312f29b1e377a62badbb</hash>
     *                  <time>1320052966</time>
     *                  <deliverTime>1320052966</deliverTime>
     *                  <total>1350</total>
     *                  <taxes>
     *                      <tax type="7">88</tax>
     *                      <tax type="19">0</tax>
     *                  </taxes>
     *                  <charge>27</charge>
     *                  <deliverCost>0</deliverCost>
     *                  <discount>700</discount>
     *                  <discountcode>mEuaW9oWoKibgZx</discountcode>
     *                  <paymentMethod>paypal</paymentMethod>
     *                  <isRated>0</isRated>
     *                  <isFavorite>0<isFavorite>
     *                  <location>
     *                      <street>Meine Straße</street>
     *                      <hausnr>32</hausnr>
     *                      <plz>16515</plz>
     *                      <cityId>959</cityId>
     *                      <company></company>
     *                      <etage>Erdgeschoss</etage>
     *                      <city>Oranienburg</city>
     *                      <tel>015140031777</tel>
     *                  </location>
     *                  <service>
     *                      <id>14610</id>
     *                      <name>Pizzeria Toscana</name>
     *                      <picture>http://image.yourdelivery.de/lieferando.de/service/14610/Pizzeria+Toscana-250-0.jpg</picture>
     *                      <tel>03304 31751</tel>
     *                      <ratings>
     *                          <advise>0</advise>
     *                          <total>5</total>
     *                          <votes>0</votes>
     *                      </ratings>
     *                  </service>
     *                  <meals>
     *                      <meal>
     *                          <name>Insalata Toscana</name>
     *                          <cost>700</cost>
     *                          <description>gemischter Salat mit Thunfisch, Schafskäse, Vorderschinken und Ei</description>
     *                          <count>1</count>
     *                          <extras></extras>
     *                          <options></options>
     *                      </meal>
     *                      ...
     *                  </meals>
     *              </order>
     *              <success>true</success>
     *              <message></message>
     *              <fidelity>
     *                  <points>0</points>
     *                  <message></message>
     *              </fidelity>
     *          <memory>25</memory>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - found order</li>
     *      <li>406 - no orderId provided</li>
     *      <li>404 - order not found</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.02.2012
     *
     * @return HTTP-RESPONSE-CODE
     */
    public function getAction() {
        $orderId = $this->getRequest()->getParam('id', null);

        if (is_null($orderId) || strlen($orderId) < 1) {
            $this->message = 'no orderId provided';
            return $this->getResponse()->setHttpResponseCode(404);
        }

        try {
            $order = new Yourdelivery_Model_Order((integer) $orderId);
            $oElem = createOrderChild($order, 'order', $this->doc);
            $this->xml->appendChild($oElem);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->message = 'could not find order by given id';
            return $this->getResponse()->setHttpResponseCode(404);
        }
    }

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>
     *          Place an order using the post request. Depending on the payment choice
     *          this will trigger the order or just place a bookmark in the database
     *          which must be confirmed using the put request.
     *      </li>
     *      <li>
     *          Inside the fidelity tag we mark wether this action resulted in some points
     *      </li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     *  <code>
     *      type JSON
     *      parameters =
     *      {
     *          "customer":
     *          {
     *              "access" :  "STRING",               (* optional param, you have to provide if customer is logged in while order)
     *              "name"   :  "STRING",
     *              "prename":  "STRING",
     *              "email"  :  "STRING",
     *              "tel"    :  "STRING"
     *          },
     *          "payment": ENUM["bar","paypal","credit"]
     *          "deliverTime":
     *          {
     *              "day" : DATE,
     *              "time": STRING|INTEGER
     *          },
     *          "location":
     *          {
     *              "street" :  "STRING",
     *              "hausnr" :  "STRING",
     *              "plz"    :  "STRING",
     *              "cityId" :  INTEGER
     *              "city"   :  "STRING",
     *              "comment":  "STRING",
     *              "tel"    :  "STRING",
     *          },
     *          "discountCode": "STRING",
     *          "serviceId"   : INTEGER,
     *          "meals":[{
     *              "id"     : INTEGER,
     *              "options": [LIST OF INTEGERS],
     *              "count"  : INTEGER,
     *              "sizeId" : INTEGER,
     *              "extras" : [LIST OF INTEGERS],
     *              "special": STRING
     *          }]
     *      }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>3. Response:</b>
     *
     *  <code>
     *      <response>
     *          <message>STRING</message>
     *          <success>BOOLEAN</success>
     *          <fidelity>
     *              <success>BOOLEAN</success>
     *              <points>INTEGER</points>
     *          </fidelty>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Example:</b>
     *
     *  <b>4.1. Example - Request:</b>
     *
     *      <code>
     *          <ul>
     *              <li>
     *                  curl -X POST -d parameters='{"uuid":"my-udid","customer":{"access":"f28a353195eafca74edcc9d9e7270ead","name":"Felix","prename":"Haferkorn","email":"haferkron@lieferando.de","tel":""},"payment":"bar","deliverTime":{"day":"20.12.2012","time":"20:00"},"location":{"hausnr":"52","street":"my-street","cityId":"3260","city":"46242 Bottrop","comment":"no comment","tel":"1325170352"},"serviceId": "12445","meals":[{"id":116934,"sizeId":20300,"count":1,"options":[],"extras":[]},{"id":117415,"sizeId":20687,"count":3,"options":[],"extras":[21633,21633]}]}' "http://www.yourdelivery.local/get_order"
     *              </li>
     *          </ul>
     *      </code>
     *
     *  <b>4.1. Example - Response:</b>
     *
     *      <code>
     *          <response>
     *              <version>1.0</version>
     *              <id>544050</id>
     *              <success>true</success>
     *              <message></message>
     *              <fidelity>
     *                  <points>0</points>
     *                  <message></message>
     *              </fidelity>
     *              <memory>41</memory>
     *          </response>
     *      </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - Order succesfully created, waiting for payment response</li>
     *      <li>201 - Order succesfully created (bar payment)</li>
     *      <li>400 - Invalid parameters</li>
     *      <li>404 - Could not find cityId by plz</li>
     *      <li>406 - Data could not be validated - read detailed message</li>
     *      <li>407 - deliver range is not online OR does not belong to ranges of service</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 11.01.2011
     *
     * @return HTTP-RESONSE-CODE
     */
    public function postAction() {
        $post = $this->getRequest()->getPost();

        if (!isset($post['parameters'])) {
            $this->logger->err('API - ORDER - POST: no parameters provided');
            $this->getResponse()->setHttpResponseCode(406);
            return;
        }


        $json = json_decode($post['parameters']);

        // log post params in debug mode
        $this->logger->debug(sprintf('API - ORDER - POST params: %s', print_r($json, true)));

        if ($json === null) {
            $this->logger->err(sprintf('API - ORDER - POST: could not encode json: %s', $post['parameters']));
            $this->message = __('Die Anfrage konnte nicht verarbeitet werden.');
            return $this->getResponse()->setHttpResponseCode(406);
        }

        $fallback = false;
        //this is a fallback for the old api usage
        if (!isset($json->location->cityId)) {
            $fallback = true;
            $cityId = Yourdelivery_Model_City::getByPlz($json->location->plz);
            if (!$cityId || $cityId->count() == 0) {
                $this->logger->err('API - ORDER - POST - FALLBACK: could not get cityId by plz ' . $json->location->plz);
                $this->errorkey = 'city';
                $this->message = __('Die PLZ %s wurde nicht gefunden.', $json->location->plz);
                return $this->getResponse()->setHttpResponseCode(404);
            }
            $this->logger->debug('API - ORDER - POST - FALLBACK: found cityId by plz ' . $cityId->current()->id);
            $json->location->cityId = $cityId->current()->id;
        }

        if (!is_object($json)) {
            $this->logger->err('API - ORDER - POST: could not decode json');
            $this->message = __('Daten konnten nicht verarbeitet werden.');
            return $this->getResponse()->setHttpResponseCode(400);
        }

        if (!$this->validateData($json)) {
            // return message is already set in validateData()
            $this->logger->err('API - ORDER - INDEX: could not validate data');
            return $this->getResponse()->setHttpResponseCode(406);
        }

        if (count($json->meals) == 0) {
            $this->message = __('Der Einkaufswagen ist leer.');
            $this->errorkey = 'cart';
            $this->logger->err('API - ORDER - INDEX: no meals were provided');
            return $this->getResponse()->setHttpResponseCode(406);
        }

        /**
         * if access is provided we serach for customer
         * otherwise create model anonym
         */
        $customer = null;
        if (isset($json->customer->access)) {
            try {
                $customer = new Yourdelivery_Model_Customer(null, null, $json->customer->access);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                // customer could not be found by access - fallback - use model anonym
            }
        }

        if (is_null($customer)) {
            $customer = new Yourdelivery_Model_Customer_Anonym();
        }

        $customer->setName($json->customer->name);
        $customer->setPrename($json->customer->prename);
        $customer->setEmail($json->customer->email);
        $customer->setTel($json->location->tel);

        $order = new Yourdelivery_Model_Order_Private();
        $order->setup($customer, 'rest', 'priv');

        //set current deliver time
        $order->setDeliverTime($json->deliverTime->time, $json->deliverTime->day);

        //load service and check if open and online
        $service = new Yourdelivery_Model_Servicetype_Restaurant();
        $service->load($json->serviceId);
        $order->setService($service);

        if (!$service->getOpening()->isOpen($order->getDeliverTime()) || !$service->isOnline($order->getCustomer(), $order->getKind())) {
            $this->message = __('Der Lieferdienst %s ist zur gewünschten Lieferzeit %s geschlossen.', $service->getName(), date('d.m.Y H:i', $order->getDeliverTime()));
            $this->errorkey = 'delivertime';
            $this->logger->warn(sprintf('API - ORDER - POST: service #%s is not open at given deliverTime %s, do not allow order', $order->getService()->getId(), date('d.m.Y H:i', $order->getDeliverTime())));
            return $this->getResponse()->setHttpResponseCode(406);
        }

        //create location object
        $location = new Yourdelivery_Model_Location();
        $location->setStreet($json->location->street);
        $location->setHausnr($json->location->hausnr);
        $location->setCityId($json->location->cityId);
        $location->setTel($json->location->tel);
        $location->setCompanyName($json->location->company);
        $location->setEtage($json->location->etage);
        $location->setComment($json->location->comment);

        if (!is_object($location->getOrt())) {
            $this->logger->err(sprintf('API - ORDER - POST: failed to get location by cityId %d', (integer) $json->location->cityId));
            $this->message = __('Dieser Ort wurde nicht gefunden.');
            $this->errorkey = 'city';
            return $this->getResponse()->setHttpResponseCode(406);
        }

        $order->setLocation($location);
        $service->setCurrentCityId($location->getCity()->getId());


        $uuid = $json->uuid;
        if (strlen($uuid) == 0) {
            $uuid = md5(time());
        }
        $order->setUuid($uuid);

        //check for the discount and add it
        if (isset($json->discountCode) && $json->discountCode) {
            try {

                /**
                 * we except this is an pre-order with discount 
                 * if choosen deliver time is more than 30 minutes in future
                 */
                if ($order->getDeliverTime() > strtotime('+30 minutes')) {
                    $this->message = __('Bei der Verwendung von Gutscheinen ist kein Vorbestellen möglich.');
                    return $this->getResponse()->setHttpResponseCode(406);
                }

                $code = new Yourdelivery_Model_Rabatt_Code($json->discountCode);

                // do the UUID-check .... but ONLY FOR RABATT TYPES != 0
                if ($code->getParent()->getType() != 0 && $code->getParent()->hasAlreadyBeenUsedForThatUuid($uuid)) {
                    $this->message = __('Dieser Gutscheincode kann nicht eingelöst werden, da Du schon einen Gutschein dieser Aktion eingelöst hast.');
                    return $this->getResponse()->setHttpResponseCode(406);
                }

                if (!$code->isUsable(true)) {
                    $this->logger->warn(sprintf('API - ORDER - POST: discount %s is not valid any more, will not add to order', $json->discountCode));
                } else {
                    $this->logger->info(sprintf('API - ORDER - POST: adding discount %s to current order', $json->discountCode));
                    $order->setDiscount($code);
                    $order->getAbsTotal(); //recalculate
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                // inform user and cancel order process
                $this->logger->err(sprintf('API - ORDER - POST: could not find discount %s', $json->discountCode));
                $this->message = __('Dieser Gutscheincode ist nicht gültig.');
                return $this->getResponse()->setHttpResponseCode(406);
            }
        }

        foreach ($json->meals as $mealData) {
            try {

                $meal = new Yourdelivery_Model_Meals($mealData->id);
                $meal->setCurrentSize($mealData->sizeId);

                /**
                 * http://ticket/browse/YD-1546
                 * warkaround to be able to use apps, that only support 1 extra
                 */
                $extras = array();
                foreach ($mealData->extras as $ext) {
                    if ($ext instanceof stdClass) {
                        $extras[] = array(
                            'id' => $ext->id,
                            'count' => $ext->count
                        );
                        continue;
                    }

                    $extras[] = array(
                        'id' => $ext,
                        'count' => 1
                    );
                }

                $opt_ext = array(
                    'options' => $mealData->options,
                    'extras' => $extras,
                    'special' => $mealData->special
                );


                $order->addMeal($meal, $opt_ext, $mealData->count, $customer);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->logger->err(sprintf('API - ORDER - POST: provided meal %s does not exist', $mealData->id));
                continue;
            }
        }

        $minamount = $order->getService()->getMinCost();
        if ($minamount > $order->getBucketTotal(null, true)) {
            $this->logger->warn(sprintf('User tried to finish an order below minamount - minamount: %s - bucketTotal: %s', $minamount, $order->getBucketTotal(null, true)));
            $this->success = 'false';
            $this->message = __('Der Mindestbestellwert von %s%s wurde nicht erreicht', intToPrice($minamount), '€');
            return $this->getResponse()->setHttpResponseCode(406);
        }

        $payment = $json->payment;
        //currently only bar and paypal
        if (!in_array($payment, array('bar', 'paypal')) || $order->getAbsTotal() <= 0) {
            $this->logger->warn(sprintf('API - ORDER - POST: getting %s as payment but switching to bar, %d ', $payment, $order->getAbsTotal()));
            $payment = 'bar';
        }

        $order->setPayment($payment);
        $order->setCurrentPayment($payment);
        $order->getAbsTotal();

        if ($payment == 'paypal' && !Yourdelivery_Helpers_Payment::allowPaypal($order)) {
            $this->logger->warn(sprintf('API - ORDER - POST: service #%s %s does not allow online payment', $order->getService()->getId(), $order->getService()->getName()));
            $this->message = __('Der Lieferdienst erlaubt derzeit keine Onlinezahlung.');
            return $this->getResponse()->setHttpResponseCode(406);
        }

        if ($payment == 'bar' && !Yourdelivery_Helpers_Payment::allowBar($order) && $order->getAbsTotal() > 0) {
            $this->logger->warn(sprintf('API - ORDER - POST: service #%s %s does not allow bar payment', $order->getService()->getId(), $order->getService()->getName()));
            $this->message = __('Der Lieferdienst erlaubt derzeit keine Barzahlung.');
            return $this->getResponse()->setHttpResponseCode(406);
        }

        $result = $order->finish();
        if ($result === true) {
            switch ($payment) {
                default:
                case 'bar':
                    $this->logger->info(sprintf('API - ORDER - POST: finishing order %s', $order->getId()));
                    $this->getResponse()->setHttpResponseCode(201);
                    $order->finalizeOrderAfterPayment('bar');
                    $this->_customer = $customer;
                    break;
                case 'paypal':

                    $paypal = new Yourdelivery_Payment_Paypal();

                    $successURL = "http://success";
                    $errorURL = "http://cancel";
                    if (isset($json->paypal)) {
                        $successURL = $json->paypal->successUrl;
                        $errorURL = $json->paypal->errorUrl;
                    }

                    // setExpressCheckout
                    $currentOrder = new Yourdelivery_Model_Order($order->getId());

                    $currentOrder
                            ->setStatus(
                                    Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_PROCESS_PAYPAL, 'API')
                    );

                    $resp = $paypal->setExpressCheckout($currentOrder, $successURL, $errorURL);
                    if ($resp['ACK'] == "Success" && array_key_exists('TOKEN', $resp)) {
                        $paymentElement = $this->doc->createElement('payment');
                        $paymentElement->appendChild(create_node($this->doc, 'method', 'paypal'));
                        $paymentElement->appendChild(create_node($this->doc, 'token', $resp['TOKEN']));
                        $this->xml->appendChild($paymentElement);
                        $this->getResponse()->setHttpResponseCode(200);
                        $this->logger->info(sprintf('API - ORDER - POST: finishing order #%s, awaiting paypal response', $order->getId()));
                    } else {
                        $this->logger->err(sprintf('API - ORDER - POST: finishing order #%s, but could not init paypal: %s', $order->getId(), print_r($resp, true)));
                        $this->message = __('Die Paypal-Transaktion konnte nicht erstellt werden.');
                        return $this->getResponse()->setHttpResponseCode(400);
                    }
                    break;
            }

            //append the new created order id
            $this->xml->appendChild(create_node($this->doc, 'id', $order->getId()));
            return;
        } else {
            $this->logger->err('API - ORDER - POST: could not finish this order');
            $this->message = __('Die Bestellung konnte nicht abgeschlossen werden.');
            return $this->getResponse()->setHttpResponseCode(500);
        }
    }

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>
     *          if the order has been created using online payment, this must be confirmed
     *          via the put request, which will trigger the delivery to the service.
     *      </li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     *  <code>
     *      type GET
     *      orderId
     *
     *      type JSON
     *      parameters =
     *      {
     *          "token"   : STRING,
     *          "payerId" : STRING,
     *          "id"      : INTEGER
     *      }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>3. Response:</b>
     *
     *  <code>
     *      <response>
     *          <message>STRING</message>
     *          <success>BOOLEAN</success>
     *          <fidelity>
     *              <success>BOOLEAN</success>
     *              <points>INTEGER</points>
     *          </fidelty>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Example:</b>
     *
     *  <b>4.1. Example - Request:</b>
     *
     *      <code>
     *          <ul>
     *              <li>
     *                  curl -X PUT -d parameters='{"token":"khvjkhjgkjhghjkg","payerId":"zgu7876867867"}' "http://www.lieferando.de/get_order/765432"
     *              </li>
     *          </ul>
     *      </code>
     *
     *  <b>4.1. Example - Response:</b>
     *
     *      <code>
     *          <response>
     *              <success>true</success>
     *              <message></message>
     *              <fidelity>
     *                  <points>0</points>
     *                  <message></message>
     *              </fidelity>
     *              <memory>41</memory>
     *          </response>
     *      </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - payment successfully proccesed</li>
     *      <li>400 - json could not be decoded</li>
     *      <li>404 - id could not be found</li>
     *      <li>406 - invalid parameters</li>
     *      <li>406 - cannot validate voucher</li>
     *      <li>500 - payment not successfully</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.09.2010
     *
     * @return HTTP-RESONSE-CODE
     */
    public function putAction() {

        $post = $this->_getPut();
        $orderId = (integer) $this->getRequest()->getParam('id');

        if ($orderId <= 0) {
            $this->logger->err('API - ORDER - PUT: no orderid provided');
            return $this->getResponse()->setHttpResponseCode(406);
        }

        if (!isset($post['parameters'])) {
            $parameters = $this->getRequest()->getParam('parameters', null);
            if ($parameters === null) {
                $this->logger->err('API - LOCATION - PUT: did not get any parameters');
                return $this->getResponse()->setHttpResponseCode(406);
            }
            $post['parameters'] = $parameters; //overwrite from get parameter, if put is not successful
        }

        try {
            $order = new Yourdelivery_Model_Order($orderId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->err(sprintf('API - ORDER - PUT: could not find order by id %d', $orderId));
            return $this->getResponse()->setHttpResponseCode(404);
        }

        /**
         * @var stdClass
         */
        $json = json_decode($post['parameters']);

        if ($json instanceof stdClass) {

            if ($json->token === null || $json->payerId === null) {
                $this->logger->err(sprintf('API - ORDER - PUT: no token or payerid provided for order %d', $orderId));
                $this->message = __('Keinen Token oder keine PayerId von PayPal bekommen');
                return $this->getResponse()->setHttpResponseCode(406);
            }

            // finalize the transaction
            $paypal = new Yourdelivery_Payment_Paypal();

            // add paypal details
            $details = false;
            try {
                $details = $paypal->getExpressCheckoutDetails($order, $json->token);
            } catch (Yourdelivery_Payment_Paypal_Exception $e) {
                
            }
            if ($details) {
                $order->setStatus($order->getStatus(), new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_PAYPAL_PAYER_DETAILS, $details['FIRSTNAME'], $details['LASTNAME'], $details['EMAIL'], $details['ADDRESSSTATUS'], $details['PAYERSTATUS']));
            }

            // new customer discount check
            $discount = $order->getDiscount();
            if (Yourdelivery_Helpers_Payment::isNewCustomerDiscountUsed($json->payerId, $order)) {
                $json->payerId = $json->payerId === null ? '' : $json->payerId;
                $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_FAIL_PAYPAL_NC_DISCOUNT, $discount->getCode(), $json->payerId));
                $this->logger->warn(sprintf('API - ORDER - PUT: payment with discount not possible, account %s already used for order #%s', $json->payerId, $orderId));
                $this->message = __('Dieser Gutschein ist nur für Neukunden einlösbar. Dein Paypalkonto wurde nicht belastet.');
                // TODO: remove discount form order ?
                return $this->getResponse()->setHttpResponseCode(406);
            }

            if ($order->hasNewCustomerDiscount() && $details['PAYERSTATUS'] == 'unverified') {
                $order->setStatus(Yourdelivery_Model_Order_Abstract::PAYMENT_NOT_AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_FAIL_PAYPAL_NOT_VERIFIED, $discount->getCode(), $json->payerId));
                $this->logger->warn(sprintf('API - ORDER - PUT: payment with discount not possible for order %s, account %s is not yet verified', $orderId, $json->payerId));
                $this->message = __('Dieser Gutschein ist nur mit einem verifizierten PayPal Account einlösbar');
                // TODO: remove discount form order ?
                return $this->getResponse()->setHttpResponseCode(406);
            }

            $resp = false;
            try {
                $resp = $paypal->doExpressCheckoutPayment($order, $json->token, $json->payerId);
            } catch (Yourdelivery_Payment_Paypal_Exception $e) {
                
            }

            // success, we got it
            if ($resp && $resp['ACK'] == "Success") {
                $this->logger->info(sprintf('API - ORDER - PUT: paypal successful processed, sending out order %s', $orderId));
                $order->finalizeOrderAfterPayment('paypal');
                
                if(!$order->getDiscount()){
                    $fidelityConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini', APPLICATION_ENV);
                    $this->fidelity_points = $fidelityConfig->fidelity->points->order;
                    $this->fidelity_message = __('fidelity_order %s', $this->fidelity_points);
                }
            } else {
                $this->logger->err(sprintf('API - ORDER - PUT: could not finally process paypal: %s %s', print_r($resp, true), print_r($json, true)));
                $this->message = 'Could not finish paypal process';
                $this->getResponse()->setHttpResponseCode(500);
            }
            return;
        }

        $this->logger->err(sprintf('API - ORDER - POST: could not decode json for order %s', $orderId));
        $this->getResponse()->setHttpResponseCode(400);
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
     * check if the given json is valid
     *
     * @param stdClass $json provided json to validate
     *
     * @access private
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 29.12.2011
     *
     * @return boolean
     */
    private function validateData($json) {

        /**
         * prepare json-data for Zend Form
         */
        $data = array(
            'name' => $json->customer->name,
            'prename' => $json->customer->prename,
            'email' => $json->customer->email,
            'telefon' => $json->location->tel,
            'tel' => $json->location->tel,
            'street' => $json->location->street,
            'hausnr' => $json->location->hausnr,
            'comment' => $json->location->comment,
            'cityId' => $json->location->cityId,
            'city' => $json->location->city,
            'payment' => $json->payment,
            'serviceId' => $json->serviceId,
            'meals' => $json->meals
        );

        $form = new Yourdelivery_Form_Api_Order_FinishPrivate();

        if (!$form->isValid($data)) {
            $this->returnFormErrors($form->getMessages());
            return false;
        }

        return true;
    }

}
