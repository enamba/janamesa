<?php

require_once APPLICATION_PATH . '/controllers/Get/OrderController.php';

/**
 * @package Yourdelivery
 * @subpackage API
 */

/**
 * <b>Description:</b>
 *
 *  <ul>
 *      <li>
 *          It's possible to mark an order as favorite
 *      </li>
 *  </ul>
 *
 * <b>Available Actions:</b>
 *  <ul>
 *      <li>
 *          index - disallowed - 403
 *      </li>
 *      <li>
 *          delete - unmark an order from favorite
 *      </li>
 *      <li>
 *          get - get all orders of one customer marked as favorites
 *      </li>
 *      <li>
 *          post - mark an order of customer as favorite
 *      </li>
 *      <li>
 *          put - disallowed - 403
 *      </li>
 *  </ul>
 *
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 2011
 */
class Get_Order_FavoriteController extends Get_OrderController {

    /**
     * <b>1. Description:</b>
     *  <ul>
     *      <li>
     *          get all orders of one customer marked as favorites
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
     *          <ACCESS>    (STRING)
     *      }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>3. Response:</b>
     *
     *  <code>
     *      <response>
     *          <favorites>
     *              <favorite>
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
     *                          <total>INTEGER[0-10]</total>
     *                          <votes>INTEGER</votes>
     *                      </ratings>
     *                  </service>
     *                  <meals>
     *                      <meal>
     *                          <name>STRING</name>
     *                          <cost>INTEGER (cents)</cost>
     *                          <description>STRING</description>
     *                          <count>INTEGER</count>
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
     *          </favorites>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Example:</b>
     *
     * <b>4.1. Example - Request:</b>
     *
     *  <code>
     *      <ul>
     *          <li>http://www.lieferano.de/get_order_favorite/960b467ef6c6e21a478107f319d20398
     *          <li>curl "http://www.lieferando.de/get_order_favorite/960b467ef6c6e21a478107f319d20398"</li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     * <code>
     *      <response>
     *          <version>1.0</version>
     *          <favorites>
     *              <favorite>
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
     *                  <isFavorite>1<isFavorite>
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
     *              </favorite>
     *          </favorites>
     *          <success>true</success>
     *          <message/>
     *          <fidelity>
     *              <points>0</points>
     *              <message/>
     *          </fidelity>
     *          <memory>28</memory>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - found favorites</li>
     *      <li>403 - no valid customer access provided</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 24.10.2011
     *
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 07.01.2012
     *
     * @return HTTP-RESONSE-CODE
     */
    public function getAction() {
        $access = $this->getRequest()->getParam('id');
        try {
            $customer = new Yourdelivery_Model_Customer(null, null, $access);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->debug(sprintf('API - FAVORITE - GET: did not find customer by given access %s', $access));
            return $this->getResponse()->setHttpResponseCode(403);
        }

        $this->logger->debug(sprintf('API - FAVORITE - GET: searching for favorites for customer #%s %s', $customer->getId(), $customer->getFullname()));

        $favsElement = $this->doc->createElement('favorites');
        $favs = $customer->getFavourites();
        foreach ($favs as $fav) {
            $favElement = createOrderChild($fav, 'favorite', $this->doc);
            $favsElement->appendChild($favElement);
        }

        $this->xml->appendChild($favsElement);
    }

    /**
     * <b>1. Description:</b>
     *  <ul>
     *      <li>
     *          mark an order of customer as favorite
     *      </li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     * <code>
     *      type JSON
     *      parameters =
     *      {
     *          access : STRING,        (customer access)
     *          hash   : STRING         (hash of order)
     *      }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Example:</b>
     *
     * <b>4.1. Example - Request:</b>
     *
     *  <code>
     *      <ul>
     *          <li>
     *              curl -X POST -d parameters='{"access":"960b467ef6c6e21a478107f319d20398","hash":"751e5f0f1648bf9178952ccc54d3b62b"}' "http://www.lieferando.de/get_order_favorite"
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     * <code>
     *      <response>
     *          <version>1.0</version>
     *          <success>true</success>
     *          <message>order is marked as favorite already</message>
     *          <fidelity>
     *              <points>0</points>
     *              <message></message>
     *          </fidelity>
     *          <memory>27</memory>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - order is already marked as favorite</li>
     *      <li>201 - successfully created favorite</li>
     *      <li>403 - user is not allowed to create a favorite for that order</li>
     *      <li>404 - order could not be found</li>
     *      <li>406 - no valid json provided</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 24.10.2011
     *
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 07.01.2012
     *
     * @return HTTP-RESONSE-CODE
     */
    public function postAction() {
        $post = $this->getRequest()->getPost();
        $json = json_decode($post['parameters']);

        if (!is_object($json)) {
            $this->logger->err('API - FAVORITE - POST: could not decode json');
            $this->message = 'json not valid';
            return $this->getResponse()->setHttpResponseCode(406);
        }

        try {
            $customer = $this->_getCustomer($json);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->warn('API - FAVORITE - POST: could not authenticate customer using access token ' . $json->access);
            $this->message = 'no access';
            return $this->getResponse()->setHttpResponseCode(403);
        }

        $order = Yourdelivery_Model_Order::createFromHash($json->hash);
        if (!$order) {
            $this->logger->warn(sprintf('API - FAVORITE - POST: could not find order by hash %s', $json->hash));
            return $this->getResponse()->setHttpResponseCode(404);
        }

        // get sure, current customer == order customer
        if ($order->getCustomer()->getId() != $customer->getId()) {
            $this->logger->warn('API - FAVORITE - POST: order and customer do not match');
            return $this->getResponse()->setHttpResponseCode(403);
        }

        // get sure, order isn't favorite already
        if ($order->isFavourite()) {
            $this->logger->warn('API - FAVORITE - POST: order already a favorite');
            $this->message = 'order is marked as favorite already';
            return $this->getResponse()->setHttpResponseCode(200);
        }

        //add favorite to table
        $order->addToFavorite($customer);
        $this->logger->info(sprintf('API - FAVORITE - POST: successfully marked order #%s to favorite by customer #%s %s', $order->getId(), $customer->getId(), $customer->getFullname()));
        return $this->getResponse()->setHttpResponseCode(201);
    }

    /**
     * <b>1. Description:</b>
     *  <ul>
     *      <li>
     *          unmark an order from favorite
     *      </li>
     *      <li>
     *          the given orderId must match the associated customer
     *      </li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     * <code>
     *      type GET
     *      {
     *          <ORDER-ID>   (INTEGER)
     *      }
     * 
     *      one of them:
     *          type JSON
     *          parameters =
     *          {
     *              "access" : "STRING"        (customer access)
     *          }
     *          
     *          type GET
     *          {
     *              access=<ACCESS>     (access key of customer)
     *          }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Example:</b>
     *
     * <b>4.1. Example - Request:</b>
     *
     *  <code>
     *      <ul>
     *          <li>
     *              curl -d 'parameters={"access":"960b467ef6c6e21a478107f319d20398"}' -H "X-HTTP-Method-Override: DELETE" -X POST http://www.lieferando.de/get_order_favorite/1721
     *          </li>
     *          <li>
     *              curl -X DELETE http://www.lieferando.de/get_order_favorite/1721?access=960b467ef6c6e21a478107f319d20398
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
     *          <memory>18</memory>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - successfully deleted favorite</li>
     *      <li>403 - no access</li>
     *      <li>406 - could not find favorite</li>
     *      <li>404 - customer does not match customer of favorite</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 24.10.2011
     *
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 07.01.2012
     *
     * @return HTTP-RESONSE-CODE
     */
    public function deleteAction() {
        $post = $this->getRequest()->getPost();
        $json = json_decode($post['parameters']);

        try {
            $customer = $this->_getCustomer($json);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->message = 'no access';
            return $this->getResponse()->setHttpResponseCode(403);
        }

        /**
         * @todo: maybe refactor that inside the model/dbTable
         */
        $orderId = (integer) $this->getRequest()->getParam('id');
        $table = new Yourdelivery_Model_DbTable_Favourites();
        $row = $table->fetchRow(sprintf('orderId = %d' , $orderId));
        if ($orderId == 0 || !$row) {
            $this->logger->warn(sprintf('API FAVORITE - DELETE: could not find favorite by given orderId #%s', $orderId));
            $this->message = 'could not find favorite';
            return $this->getResponse()->setHttpResponseCode(406);
        }

        if ($row->customerId != $customer->getId()) {
            $this->logger->warn(sprintf('API FAVORITE - DELETE: favorite-customer #%s does not match given customer #%s', $row->customerId, $customer->getId()));
            $this->message = 'customer is not associated with order';
            return $this->getResponse()->setHttpResponseCode(404);
        }

        $favRow = Yourdelivery_Model_DbTable_Favourites::findByOrderId($orderId);
        Yourdelivery_Model_DbTable_Favourites::remove($favRow['id']);
        $this->logger->info(sprintf('API FAVORITE - DELETE: successfully deleted favorite with orderId #%s (favId %s) by custommer #%s %s ', $orderId, $favRow['id'], $customer->getId(), $customer->getFullname()));
    }

    /**
     * the method is not in use, and will be forbidden
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 07.01.2012
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function indexAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function putAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

}