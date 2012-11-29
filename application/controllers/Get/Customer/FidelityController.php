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
 *          a customer can get fidelity points for several actions all over the system
 *      </li>
 *      <li>
 *          for example a customer gets fidelity points for successfully processing an order,
 *          rating an order or uploading a profile image
 *      </li>
 *      <li>
 *          every response of API will return the structure
 *          <code>
 *              <fidelity>
 *                  <points>INTEGER</points>    (points the customer got for that action)
 *                  <message>STRING</message>   (translated message for customer)
 *              </fidelity>
 *          </code>
 *      </li>
 *      <li>
 *          if the amount of points is greater than 0 there will be a message for customer
 *      </li>
 *      <li>
 *          if amount of points is 0 customer didn't get any points for this action
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
 *          get - disallowed - 403
 *      </li>
 *      <li>
 *          post - get several information about customer fidelity points
 *      </li>
 *      <li>
 *          put - disallowed - 403
 *      </li>
 *  </ul>
 *
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 22.11.2011
 */
class Get_Customer_FidelityController extends Default_Controller_RestBase {

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>Get information about fidelity points for customer</li>
     *      <li>you can get the done fidelity transactions by providing json <b>"type":"transactions"</b></li>
     *      <li>you can get the open actions to get fidelity points for given customer by providing json <b>"type":"openactions"</b></li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     * <b>2.1. Paremeters - Fidelity History / Transactions:</b>
     *
     *  <code>
     *      type JSON
     *      {
     *          "type":"transactions",
     *          "access":STRING,
     *          "limit":INTEGER *       (count of transactions to recieve - NOT IMPLEMENTED YET)
     *      }
     *  </code>
     *  * = optional parameter
     *
     *
     *
     * <b>3.1. Response:</b>
     *  <code>
     *      <response>
     *          <customer>
     *              <customerid>INTEGER</customerid>
     *              <transactions>
     *                  <transaction>
     *                      <tid>INTEGER</tid>                                       (id of transaction)
     *                      <time>TIMESTAMP</time>                                   (time of transaction is created - similar to e.g. order)
     *                      <status>INTEGER</status>                                 (state of on transaction - if state < 0 the transaction is canceled; the points are not in count of total points)
     *                      <action>STRING</action>                                  (name / type of transaction - e.g. "order", "register", "accountimage", "manual", "registeraftersale", "rate_high", "rate_low")
     *                      <points>INTEGER</points>                                 (count of points for this single transaction)
     *                      <pointsuntiltransaction>INTEGER</pointsuntiltransaction> (count of points user had before that transaction)
     *                      <message>STRING</message>                                (message for customer to know something about this transaction)
     *                  </transaction>
     *                  ...
     *              </transactions>
     *              <fidelityfooter>STRING</fidelityfooter>                          (text to provide information in footer)
     *          </customer>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2.2. Paremeters - Fidelity open / possible actions:</b>
     *
     *  <code>
     *      {
     *          "type":"openactions",
     *          "access":STRING
     *      }
     *  </code>
     *
     *
     * <b>3.2. Response:</b>
     *
     * <code>
     *      <response>
     *          <customer>
     *              <customerid>INTEGER</customerid>
     *              <openactionpoints>INTEGER</openactionpoints>                (maximum amount of points customer can get by doing all these actions)
     *              <fidelityfooter>STRING</fidelityfooter>                          (text to provide information in footer)
     *              <openactions>
     *                  <openaction>
     *                      <type>STRING</type>                                 (name / type of transaction - e.g. "order", "ac")
     *                      <info>STRING</info>                                 (description of action)
     *                      <points>INTEGER</points>                            (count of points the customer can get by doing this action)
     *                      <call2action>STRING</call2action>                   (URL to call this action on website)
     *                      <orderId>INTEGER</orderId>                          (if <type> = 'orders' you will get the orderId here)
     *                  </openaction>
     *                  ...
     *              </openactions>
     *          </customer>
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
     *              curl -X POST -d parameters='{"access":"dc9aeffb7ef67068d1d19fb3d246060a","type":"openactions"}' "http://www.lieferando.de/get_customer_fidelity"
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *          <customer>
     *              <customerid>1231</customerid>
     *              <openactionpoints>23</openactionpoints>
     *              <fidelityfooter>STRING</fidelityfooter>
     *              <openactions>
     *                  <openaction>
     *                      <type>orders</type>
     *                      <info>Bestellung bei  bewerten und bis zu 5 Punkte bekommen</info>
     *                      <points>5</points>
     *                      <call2action>/user/rate/3d1a9947a92f2e19e0e4795f8d52d353</call2action>
     *                      <orderId>446666</orderId>
     *                  </openaction>
     *                  <openaction>
     *                      <type>orders</type>
     *                      <info>Bestellung bei  bewerten und bis zu 5 Punkte bekommen</info>
     *                      <points>5</points>
     *                      <call2action>/user/rate/1ce40a817da60a5d80d2bda96a1e3969</call2action>
     *                      <orderId>414378</orderId>
     *                  </openaction>
     *                  <openaction>
     *                      <type>ac</type>
     *                      <info>Lade jetzt ein Profilbild von Dir hoch und bekomme 8 Treuepunkte</info>
     *                      <points>8</points>
     *                      <call2action>/user/index</call2action>
     *                      <orderId></orderId>
     *                  </openaction>
     *              </openactions>
     *          </customer>
     *          <success>true</success>
     *              <message></message>
     *              <fidelity>
     *                  <points>0</points>
     *                  <message></message>
     *              </fidelity>
     *              <memory>6</memory>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>403 - no valid access key provided</li>
     *      <li>404 - invalid type given</li>
     *      <li>405 - invalid json provided</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.11.2011
     *
     * @todo implement limit for openActions
     * @see http://ticket.yourdelivery.local/browse/YD-736
     *
     * @return HTTP-RESONSE-CODE
     */
    public function postAction() {
        $post = $this->getRequest()->getPost();
        $json = json_decode($post['parameters']);

        if (!is_object($json) || !isset($json->type)) {
            $this->logger->debug(sprintf('API - CUSTOMER - FIDELITY - GET: Did not valid get json'));
            $this->success = "false";
            return $this->getResponse()->setHttpResponseCode(405);
        }
        $customer = null;
        try {
            $this->logger->debug(sprintf('API - CUSTOMER - FIDELITY - GET: Looking for customer by access %s', $json->access));
            $customer = $this->_getCustomer($json);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->warn(sprintf('API - CUSTOMER - FIDELITY - GET: could not find any customer by given access %s', $json->access));
            $this->success = "false";
            return $this->getResponse()->setHttpResponseCode(403);
        }

        $cElem = $this->doc->createElement('customer');
        $cElem->appendChild(create_node($this->doc, 'customerid', $customer->getId()));
        $cElem->appendChild(create_node($this->doc, 'openactionpoints', $customer->getFidelity() ? (integer) $customer->getFidelity()->getOpenActionPoints(true) : 0));
        $fidelityConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini', APPLICATION_ENV);
        $fidelityfooter = __('Wenn du %d Treuepunkte gesammelt hast, kannst du eine Speise bis %s € gratis bekommen. Einlösen kannst du die Punkte auf unserer Webseite.', (integer) $fidelityConfig->fidelity->cashin->need, intToPrice($fidelityConfig->fidelity->cashin->maxcost));
        $cElem->appendChild(create_node($this->doc, 'fidelityfooter', $fidelityfooter));

        $fidelity = $customer->getFidelity();

        switch ($json->type) {
            case 'transactions': {
                    $this->logger->debug(sprintf('API - CUSTOMER - FIDELITY - GET: looking for transactions for customer #%s %s (%s)', $customer->getId(), $customer->getFullname(), $json->access));
                    $transactions = $fidelity->getTransactions(null, !is_null($json->limit) ? $json->limit : null);
                    $tElems = $this->doc->createElement('transactions');
                    foreach ($transactions as $transaction) {
                        $tElem = $this->doc->createElement('transaction');
                        $tElem->appendChild(create_node($this->doc, 'tid', $transaction['id']));
                        $tElem->appendChild(create_node($this->doc, 'time', $transaction['created']));
                        $tElem->appendChild(create_node($this->doc, 'status', $transaction['status']));
                        $tElem->appendChild(create_node($this->doc, 'action', $transaction['action']));
                        $tElem->appendChild(create_node($this->doc, 'points', $transaction['points']));
                        $transactionObject = new Yourdelivery_Model_Customer_FidelityTransaction($transaction['id']);
                        $tElem->appendChild(create_node($this->doc, 'pointsuntiltransaction', $transactionObject->getPointsUntil()));
                        $tElem->appendChild(create_node($this->doc, 'message', $transactionObject->getDescription()));
                        $tElems->appendChild($tElem);
                        unset($tElem);
                    }

                    $cElem->appendChild($tElems);
                    $this->xml->appendChild($cElem);
                    return;
                }
            case 'openactions': {
                    /**
                     * @todo implement limit
                     * http://ticket.yourdelivery.local/browse/YD-736
                     */
                    $this->logger->debug(sprintf('API - CUSTOMER - FIDELITY - GET: looking for openactions for customer #%s %s (%s)', $customer->getId(), $customer->getFullname(), $json->access));
                    $openActions = $fidelity->getOpenActions(true);
                    $aElems = $this->doc->createElement('openactions');
                    foreach ($openActions as $type => $openAction) {
                        foreach ($openAction as $a) {
                            $aElem = $this->doc->createElement('openaction');
                            $aElem->appendChild(create_node($this->doc, 'type', $type));
                            $aElem->appendChild(create_node($this->doc, 'info', $a['info']));
                            $aElem->appendChild(create_node($this->doc, 'points', $a['points']));
                            $aElem->appendChild(create_node($this->doc, 'call2action', $a['call2action']));
                            $aElem->appendChild(create_node($this->doc, 'orderId', isset($a['id']) ? $a['id'] : null));
                            $aElems->appendChild($aElem);
                            unset($aElem);
                        }
                    }

                    $cElem->appendChild($aElems);
                    $this->xml->appendChild($cElem);
                    return;
                }
            default: {
                    $this->logger->debug(sprintf('API - CUSTOMER - FIDELITY - GET: Did not get param "transactions" or "openactions" for type'));
                    $this->success = "false";
                    return $this->getResponse()->setHttpResponseCode(404);
                }
        }
    }

    /**
     * the method is not in use, and will be forbidden
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.11.2011
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function indexAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the method is not in use, and will be forbidden
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.11.2011
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function getAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the method is not in use, and will be forbidden
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.11.2011
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function putAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the method is not in use, and will be forbidden
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 22.11.2011
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function deleteAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

}