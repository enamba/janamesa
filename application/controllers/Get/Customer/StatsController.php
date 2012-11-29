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
 *          get some statistic data of a customer
 *      </li>
 *      <li>
 *          for example you get the time of first and last order, the total count of orders
 *      </li>
 *      <li>
 *          only affirmed orders will be considered
 *      </li>
 *  </ul>
 *
 * <b>Available Actions:</b>
 *  <ul>
 *      <li>
 *          index - disallowed - 403
 *      </li>
 *      <li>
 *          delete - disallowed - 403
 *      </li>
 *      <li>
 *          get - get statistic information about customer orders
 *      </li>
 *      <li>
 *          post - disallowed - 403
 *      </li>
 *      <li>
 *          put - disallowed - 403
 *      </li>
 *  </ul>
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 18.11.2011
 */
class Get_Customer_StatsController extends Default_Controller_RestBase {

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>
     *          get statistic information about customer orders
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
     *          <customerid>INTEGER</id>
     *          <firstorder>TIMESTAMP</firstorder>  (time of first order of customer)
     *          <lastorder>TIMESTAMP</lastorder>    (time of last order of customer)
     *          <countorders>INTEGER</countorders>  (total count of orders of customer)
     *          <unratedorders>                     (orders that are not rated by customer yet)
     *              <maxavailablefidelitypoints>
     *                  INTEGER                     (maximum count of points the customer can get for rate this orders)
     *              </maxavailablefidelitypoints>
     *              <customerorders>
     *                  <order>
     *                      <id>INTEGER</id>                        (orderId)
     *                      <hash>STRING</hash>                     (hash of order)
     *                      <time>TIMESTAMP</time>                  (order time)
     *                      <deliverTime>TIMESTAMP</deliverTime>    (deliver time)
     *                      <total>INTEGER</total>                  (total amount of order in cent)
     *                      <service>
     *                          <id>INTEGER</id>                    (serviceId)
     *                          <name>STRING</name>                 (restaurant name)
     *                          <picture>STRING/picture>            (url of logo of restaurant)
     *                          <ratings>
     *                              <advise>INTEGER[0-100]</advise>
     *                              <total>INTEGER[0-10]</total>
     *                              <votes>INTEGER</votes>
     *                          </ratings>
     *                      </service>
     *                  </order>
     *                  ...
     *              <customerorders>
     *          </unratedorders>
     *          <ratedorders>                       (orders that are rated by customer already)
     *              <earnedfidelitypoints>
     *                  INTEGER                     (count of points the customer did get for rating this orders)
     *              </earnedfidelitypoints>
     *              <customerorders>
     *                  <order>
     *                      <id>INTEGER</id>
     *                      <hash>STRING</hash>
     *                      <time>TIMESTAMP</time>
     *                      <deliverTime>TIMESTAMP</deliverTime>
     *                      <ratingTime>TIMESTAMP</ratingTime>
     *                      <total>INTEGER</total>
     *                      <service>
     *                          <id>INTEGER</id>
     *                          <name>STRING</name>
     *                          <picture>STRING/picture>
     *                          <ratings>
     *                              <advise>INTEGER[0-100]</advise>
     *                              <total>INTEGER[0-10]</total>
     *                              <votes>INTEGER</votes>
     *                          </ratings>
     *                      </service>
     *                  </order>
     *                  ...
     *              </customerorders>
     *          </ratedorders>
     *      </response>
     * </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Example:</b>
     *
     *  <code>
     *      <ul>
     *          <li>curl http://www.lieferando.local/get_customer_stats/dc9aeffb7ef67068d1d19fb3d246060a</li>
     *          <li>http://www.lieferando.local/get_customer_stats/dc9aeffb7ef67068d1d19fb3d246060a</li>
     *      </ul>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Code:</b>
     *
     *  <ul>
     *      <li>200 - success</li>
     *      <li>404 - customer not found</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 18.11.2011
     *
     * @return HTTP-RESPONSE-CODE
     */
    public function getAction() {
        try {
            $customer = new Yourdelivery_Model_Customer(null, null, $this->getRequest()->getParam('id'));

            $orderStats = $customer->getFirstAndLastAndCountOrders();

            $cElem = $this->doc->createElement('customer');
            $cElem->appendChild(create_node($this->doc, 'customerid', $customer->getId()));
            $cElem->appendChild(create_node($this->doc, 'firstorder', isset($orderStats['deliverTimeFirstOrder']) ? $orderStats['deliverTimeFirstOrder'] : null));
            $cElem->appendChild(create_node($this->doc, 'lastorder', isset($orderStats['deliverTimeLastOrder']) ? $orderStats['deliverTimeLastOrder'] : null));
            $cElem->appendChild(create_node($this->doc, 'countorders', isset($orderStats['countOrders']) ? $orderStats['countOrders'] : 0));

            $unratedOrdersElem = $this->doc->createElement('unratedorders');


            $unratedOrders = array();
            $unratedOrders = $customer->getUnratedOrders(25, 0);
            $unratedOrdersElem->appendChild(create_node($this->doc, 'maxavailablefidelitypoints', count($unratedOrders) * $customer->getFidelity()->getPointsForAction('rate_high')));

            $orderElements = $this->doc->createElement('customerorders');
            foreach ($unratedOrders as $order) {
                $orderElem = $this->createShortOrderChild($order);
                $orderElements->appendChild($orderElem);
            }
            $unratedOrdersElem->appendChild($orderElements);
            $cElem->appendChild($unratedOrdersElem);

            $ratedOrdersElem = $this->doc->createElement('ratedorders');

            $ratedOrders = array();
            $ratedOrders = $customer->getRatedOrders(25, 0);
            $ratedOrdersElem->appendChild(create_node($this->doc, 'earnedfidelitypoints', $customer->getFidelity()->getPoints('rate_low') + $customer->getFidelity()->getPoints('rate_high')));

            $orderElements = $this->doc->createElement('customerorders');
            foreach ($ratedOrders as $order) {
                $orderElem = $this->createShortOrderChild($order, true);
                $orderElements->appendChild($orderElem);
            }
            $ratedOrdersElem->appendChild($orderElements);

            $cElem->appendChild($ratedOrdersElem);

            $this->xml->appendChild($cElem);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->err(sprintf('API - CUSTOMER - STATS - GET: could not find any customer by given access %s', $this->getRequest()->getParam('id')));
            $this->message = 'no access';
            $this->success = "false";
            return $this->getResponse()->setHttpResponseCode(404);
        }
    }

    /**
     * create an order element with few tags
     *
     * @param Yourdelivery_Model_Order_Abstract $order the orderelement
     *
     * @author Felix Haferkorn
     * @since 27.12.2011
     *
     * @return DOMElement order
     */
    private function createShortOrderChild($order, $rated = false) {

        $orderElem = $this->doc->createElement('order');
        $orderElem->appendChild(create_node($this->doc, 'id', $order['order_id']));
        $orderElem->appendChild(create_node($this->doc, 'time', strtotime($order['time'])));
        $orderElem->appendChild(create_node($this->doc, 'delivertime', strtotime($order['delivertime'])));
        $orderElem->appendChild(create_node($this->doc, 'ratingTime', $rated ? strtotime($order['ratingTime']) : ''));       
        $orderElem->appendChild(create_node($this->doc, 'total', (integer) $order['total']));

        $serviceElement = $this->doc->createElement('service');

        try {
            $service = new Yourdelivery_Model_Servicetype_Restaurant($order['restaurantId']);
            $serviceElement->appendChild(create_node($this->doc, 'id', $service->getId()));
            $serviceElement->appendChild(create_node($this->doc, 'name', $service->getName()));
            $serviceElement->appendChild(create_node($this->doc, 'picture', $service->getImg('api')));
            $ratingElement = $this->doc->createElement('ratings');
            $ratingElement->appendChild(create_node($this->doc, 'advise', (integer) $service->getRating()->getAverageAdvise()));
            $ratingElement->appendChild(create_node($this->doc, 'total', (integer) ($service->getRating()->getAverageQuality() + $service->getRating()->getAverageDelivery())));
            $ratingElement->appendChild(create_node($this->doc, 'votes', (integer) count($service->getRating()->getList(null, true))));
            $serviceElement->appendChild($ratingElement);
            $orderElem->appendChild($serviceElement);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

        }
        return $orderElem;
    }

    /**
     * the method is not in use, and will be forbidden
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 18.11.2011
     *
     * @return HTTP-RESPONSE-CODE 403
     */
    public function postAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the method is not in use, and will be forbidden
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 18.11.2011
     *
     * @return HTTP-RESPONSE-CODE 403
     */
    public function putAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the method is not in use, and will be forbidden
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 18.11.2011
     *
     * @return HTTP-RESPONSE-CODE
     */
    public function deleteAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the method is not in use, and will be forbidden
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 18.11.2011
     *
     * @return HTTP-RESPONSE-CODE 403
     */
    public function indexAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

}