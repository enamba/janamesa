<?php

/**
 * @package Yourdelivery
 * @subpackage API
 */

/**
 * Discount API
 * @since 07.09.2010
 * @author mlaug
 */
class Get_DiscountController extends Default_Controller_RestBase {

    private $_excludedRabattTypes = array();

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>
     *          Validate a discount and get additional informations
     *      </li>
     *      <li>
     *          If you provide an optional amount parameter, this call will do the calculation for you
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
     *          <DISCOUNT-CODE>  (STRING)               (discount code to be validated)
     *          <SERVICE-ID>     (INTEGER)              (* optional ID of service from order)
     *          <AMOUNT>         (INTEGER)              (* optional total amount of order)
     *      }
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>3.1. Response:</b>
     *
     *  <code>
     *      <response>
     *          <message>STRING</message>
     *          <discount>
     *              <name>STRING</name>                 (name of discount)
     *              <minamount>INTEGER</minamount>      (minimum order amount of discount)
     *              <info>STRING</info>                 (description of discount)
     *              <amount>STRING</amount>             (amount of discount to be substracted from order total)
     *              <percent>INTEGER[0-100]</percent>   (percentage of discount)
     *              <diff>INTEGER</diff>                (* difference between order with and without discount is only calculated, if AMOUNT was provided in request )
     *              <newamount>INTEGER</newamount>      (* new total amount of order is only calculated, if AMOUNT was provided in request )
     *          </discount>
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
     *              http://www.lieferando.local/get_discount/yd-testing2011?amount=1000
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *          <discount>
     *              <name>CMS</name>
     *              <minamount>1000</minamount>
     *              <info>Only for test purposes</info>
     *              <amount>10</amount>
     *              <percent>0</percent>
     *              <diff>10</diff>
     *              <newamount>990</newamount>
     *          </discount>
     *          <success>true</success>
     *          <message></message>
     *          <url></url>
     *          <anchortext></anchortext>
     *          <fidelity>
     *              <points>0</points>
     *              <message></message>
     *          </fidelity>
     *          <memory>20</memory>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - discount found and valid</li>
     *      <li>404 - discount not found</li>
     *      <li>406 - discount not usable, reason readably in message tag of xml response</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.09.2010
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 27.03.2012
     *
     * @return integer HTTP-RESPONSE-CODE
     */
    public function getAction() {
        $code = $this->getRequest()->getParam('id', null);
        $serviceId = (integer) $this->getRequest()->getParam('serviceId', null);

        if (is_string($code) && !empty($code)) {
            try {

                $firstLetters = strtolower(substr($code, 0, 2));
                if ($firstLetters === 'cb') {

                    //$result = strtolower(file_get_contents(sprintf('https://lieferando1.cbapps.de/%s', $code)));
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, sprintf('https://lieferando2.cbapps.de/%s', $code));
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
                    $result = strtolower(curl_exec($ch));

                    if ($result == 'valid') {
                        $this->logger->info(sprintf('ComputerBILD.DE API: successfully validated code for cumputerbild.de, will now put this code %s in our database', $code));
                        /**
                         * ID am Freitag 16.06.2011 von Oli Pawliczek bekommen
                         *
                         * Testcodes von Computerbild:
                         * cb-aaaa-aaax - Valid
                         * cb-aaaa-aaaa - Invalid
                         *
                         * @author Felix Haferkorn <haferkorn@lieferando.de>
                         *
                         */
                        $rabattAction = new Yourdelivery_Model_Rabatt(36536);
                        $rabattAction->generateCode($code);
                    } else {
                        $this->logger->warn(sprintf('ComputerBILD.DE API: failed to validate code %s, but going on, trying to find a valid in house code', $code));
                    }
                }


                $discount = new Yourdelivery_Model_Rabatt_Code($code);

                if ($discount->isUsable(true)) {

                    // don't allow company discounts in API
                    if ($discount->getParent()->isOnlyCompany()) {
                        $this->message = __('Dieser Gutschein ist nur für Firmenkunden.');
                        $this->logger->info(sprintf('API - DISCOUNT - GET: code (%s) is only for company use', $code));
                        return $this->getResponse()->setHttpResponseCode(406);
                    }

                    // disallow several discount types and show info to user
                    if (in_array($discount->getParent()->getType(), $this->_excludedRabattTypes)) {
                        $this->message = __('Dein Neukunden-Gutschein kann hier leider nicht verifiziert werden. Bitte löse ihn auf unserer Internetseite ein.');
                        if (!is_null($serviceId) && $serviceId > 0) {
                            try {
                                $service = new Yourdelivery_Model_Servicetype_Restaurant($serviceId);
                                $this->url = sprintf("http://www.%s/%s", $this->config->domain->base, $service->getDirectLink());
                                $this->anchortext = __('direkt zu %s', $service->getName());
                            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                                $this->url = sprintf("http://www.%s/", $this->config->domain->base);
                                $this->anchortext = __('zu %s', $this->config->domain->base);
                            }
                        } else {
                            $this->url = sprintf("http://www.%s/", $this->config->domain->base);
                            $this->anchortext = __('zu %s', $this->config->domain->base);
                        }

                        $this->logger->info(sprintf('API - DISCOUNT - GET: code (%s) is only for new customer only', $code));
                        return $this->getResponse()->setHttpResponseCode(406);
                    }

                    $dElem = $this->doc->createElement('discount');

                    $parent = $discount->getParent();

                    $amount = 0;
                    $percent = 0;
                    if ($parent->getKind() == 0) {
                        $percent = $parent->getRabatt();
                    } else {
                        $amount = $parent->getRabatt();
                    }

                    $rabattMinAmount = (integer) $parent->getMinAmount();
                    
                    $dElem->appendChild(create_node($this->doc, 'name', $parent->getName()));
                    $dElem->appendChild(create_node($this->doc, 'minamount', $rabattMinAmount));
                    $dElem->appendChild(create_node($this->doc, 'info', $parent->getInfo()));
                    $dElem->appendChild(create_node($this->doc, 'amount', $amount));
                    $dElem->appendChild(create_node($this->doc, 'percent', $percent));

                    $currentAmount = (integer) $this->getRequest()->getParam('amount', 0);
                    if ($currentAmount > 0) {
                        list($diff, $newamount) = $discount->calcDiff($currentAmount);
                        $dElem->appendChild(create_node($this->doc, 'diff', $diff));
                        $dElem->appendChild(create_node($this->doc, 'newamount', $newamount));
                        
                        /**
                         * minamount for discount not reached
                         * we can only check that, if we get an amount
                         */
                        if($currentAmount < $rabattMinAmount){
                            $this->logger->warn(sprintf('API - DISCOUNT - GET: submitted order amount %s is below minamount of rabatt %s', intToPrice($currentAmount), intToPrice($rabattMinAmount)));
                            $this->message = __('Der Mindestbestellwert von %s%s für den Gutschein wurde noch nicht erreicht.', intToPrice($rabattMinAmount), '€');
                            return $this->getResponse()->setHttpResponseCode(406);
                        }
                        
                    }

                    $this->xml->appendChild($dElem);
                    $this->logger->info(sprintf('API - DISCOUNT - GET: valid code (%s) was added', $code));
                    return;
                } else {
                    $this->message = __('Dieser Gutschein ist leider nicht mehr gültig.');
                    $this->logger->info(sprintf('API - DISCOUNT - GET: code (%s) is not usable anymore', $code));
                    return $this->getResponse()->setHttpResponseCode(406);
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

            }
        }

        $this->message = __('Dieser Gutschein wurde nicht gefunden.');
        $this->logger->info(sprintf('API - DISCOUNT - GET: could not verify code (%s)', $code));
        return $this->getResponse()->setHttpResponseCode(404);
    }

    /**
     * the index method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function indexAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the post method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function postAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the put method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function putAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the delete method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function deleteAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

}

?>
