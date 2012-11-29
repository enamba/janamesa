<?php

/**
 * Description of OrderController
 * @todo use correct http response codes
 * @author Matthias Laug <laug@lieferando.de>
 */
class Request_OrderController extends Default_Controller_RequestBase {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     *
     * @param Yourdelivery_Model_Rabatt_Code $rabattCode
     * @param boolean $result
     * @param string $msg
     * @return json
     */
    private function discountState($result, $msg = '', $log = array(), $rabattCode = null, $allowBar = false, $newCustomer = false) {
        if (is_array($log) &&
                array_key_exists('type', $log) && !empty($log['type']) &&
                array_key_exists('msg', $log) && !empty($log['msg'])) {
            $this->logger->$log['type']('ADDDISCOUNT - ' . $log['msg']);
        }

        if ($newCustomer) {
            if (substr($this->config->domain->base, -3) == '.pl') {
                $allowCredit = true;
            } else {
                $allowCredit = false;
            }
            $allowEbanking = true;
            $this->view->newCustomer = true;
            in_array($rabattCode->getParent()->getType(), array(4, 6)) ?
                            $this->view->oldCustomer = true : null;
        } else {
            $allowCredit = true;
            $allowEbanking = true;
        }

        $this->view->discount = $rabattCode;

        $kindHtml = null;
        if ($result && $rabattCode instanceof Yourdelivery_Model_Rabatt_Code && $rabattCode->getParent()->getRabatt() > 0) {
            $f = $rabattCode->getParent()->getRabatt();
            if ($rabattCode->getParent()->getKind() == 0) {
                $kindHtml = $rabattCode->getParent()->getRabatt() . __('% - Gutschein') . '<br/>';
            } elseif ($rabattCode->getParent()->getKind() == 1) {
                $kindHtml = intToPrice($rabattCode->getParent()->getRabatt()) . __('€ - Gutschein') . '<br/>';
            }
        }

        echo json_encode(array(
            'result' => $result,
            'msg' => $msg,
            'allowCash' => $allowBar,
            'allowCredit' => $allowCredit,
            'allowEbanking' => $allowEbanking,
            'newCustomerCode' => $rabattCode instanceof Yourdelivery_Model_Rabatt_Code ? $rabattCode->getParent()->isNewCustomerDiscount() && (boolean) $rabattCode->getParent()->getNewCustomerDiscountCheck() : false,
            'html' => $this->view->fetch('order/_includes/finish/discount.htm'),
            'data' => $result && $rabattCode instanceof Yourdelivery_Model_Rabatt_Code ? array(
                'minAmount' => (integer) $rabattCode->getParent()->getMinAmount(),
                'minAmountHtml' => (integer) $rabattCode->getParent()->getMinAmount() > 0 ? __('Mindestbestellwert: %s €', intToPrice($rabattCode->getParent()->getMinAmount())) : null,
                'minAmountMsg' => __('Der Mindestbestellwert des Gutscheins von %s€ ist noch nicht erreicht.', intToPrice($rabattCode->getParent()->getMinAmount())),
                'kind' => $rabattCode->getParent()->getKind(),
                'value' => (integer) $rabattCode->getParent()->getRabatt(),
                'info' => $rabattCode->getParent()->getInfo(),
                'kindHtml' => $kindHtml
                    ) : null
        ));
    }

    /**
     * Check for affiliprint discount
     * @author Matthias Laug <laug@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @param string $code
     * @return null|Yourdelivery_Model_Rabatt_Code
     */
    protected function _checkAffiliPrint($code) {
        if (!IS_PRODUCTION) {
            return null;
        }

        if ($this->config->domain->base != 'lieferando.de') {
            return null;
        }

        require_once(APPLICATION_PATH . '/../library/Extern/Affiliprint/AffiliPrintConfig.php');
        require_once(APPLICATION_PATH . '/../library/Extern/Affiliprint/AffiliPrintCom.php');

        $talkToAffiliprint = new AffiliPrintCom();
        if ($talkToAffiliprint->localValidateBonuscode($code) && $talkToAffiliprint->remoteValidateBonuscode($code)) {
            // add the affiliprint discount code
            $rabattAction = new Yourdelivery_Model_Rabatt(31546);
            // if we get false here, the discount exists, but not in the cashback field
            if ($rabattAction->generateCode($code) !== false) {
                $this->logger->info(sprintf('AFFILIPRINT: use code %s', $code));
                $rabattCode = new Yourdelivery_Model_Rabatt_Code($code);
                return $rabattCode;
            } else {
                $rabattCode = new Yourdelivery_Model_Rabatt_Code();
                $rabattCode->setCode($code);
                $rabattCode->setRabattId(31546);
                $rabattCode->save();
                return $rabattCode;
            }
            $this->logger->warn(sprintf('AFFILIPRINT: cannot use code %s', $code));
            return null;
        }
        $this->logger->debug(sprintf('AFFILIPRINT: unknow code %s response: %s', $code, $talkToAffiliprint->getStatus()));
        return null;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 27.07.2012
     * @param string $code code to verify
     * @return null|Yourdelivery_Model_Rabatt_Code
     */
    protected function _checkGoolive($code) {
         if (!IS_PRODUCTION) {
            return null;
        }

        if ($this->config->domain->base !== 'lieferando.de') {
            return null;
        }

        require_once(APPLICATION_PATH . '/../library/Extern/Goolive/ApiCall.php');

        $goolive = new Goolive_ApiCall();

        if($goolive->validate($code)) {
             // add the goolive discount code
            $rabattAction = new Yourdelivery_Model_Rabatt(57496);
            // if we get false here, the discount exists, but not in the cashback field
            if ($rabattAction->generateCode($code) !== false) {
                $this->logger->info(sprintf('GOOLIVE: use code %s', $code));
                $rabattCode = new Yourdelivery_Model_Rabatt_Code($code);
                return $rabattCode;
            } else {
                $rabattCode = new Yourdelivery_Model_Rabatt_Code();
                $rabattCode->setCode($code);
                $rabattCode->setRabattId(57496);
                $rabattCode->save();
                return $rabattCode;
            }
            $this->logger->warn(sprintf('GOOLIVE: cannot use code %s', $code));
            return null;

        }

        $this->logger->debug(sprintf('GOOLIVE: unknown code %s response: %s', $code, $goolive->getResultCode()));
        return null;

    }



    /**
     * add a discount to the current order
     * make sure this discount is usable
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.09.2011
     *
     * @todo refactor check for new customers and duplicate code
     * @return json
     */
    public function adddiscountAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        // get params
        $request = $this->getRequest();
        $code = htmlentities($request->getParam('code', null));
        $code = trim($code);
        $serviceId = (integer) $request->getParam('service', 0);
        $isLoggedIn = (boolean) $request->getParam('customer', false);
        $kind = htmlentities($request->getParam('kind', 'priv'));
        $cityId = htmlentities($request->getParam('city', null));


        if ($serviceId <= 0) {
            return $this->discountState(
                            false, __("Leider kein gültiger Gutschein. Vielleicht hast Du dich vertippt."), array(
                        'type' => 'err',
                        'msg' => 'no serviceId provided for discount check'), $code
            );
        }

        try {
            $service = new Yourdelivery_Model_Servicetype_Restaurant($serviceId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return $this->discountState(
                            false, __("Leider kein gültiger Gutschein. Vielleicht hast Du dich vertippt."), array(
                        'type' => 'err',
                        'msg' => 'no service found with id #' . $serviceId), $code
            );
        }

        //check for this code
        if (is_null($code) || empty($code)) {
            //thats strange, and we won't allow it!
            return $this->discountState(
                            false, __("Leider kein gültiger Gutschein. Vielleicht hast Du dich vertippt."), array(
                        'type' => 'err',
                        'msg' => 'tried to add discount, but no code has been provided'), $code
            );
        }

        //check on cashback, and generate if not exists
        $firstLetters = substr($code, 0, 6);
        if ($firstLetters === 'L13F3D' && strlen($code) == 15) {
            $rabattAction = new Yourdelivery_Model_Rabatt(4119);
            //if we get false here, the discount exists, but not in the cashback field
            if ($rabattAction->generateCode($code) !== false) {
                $rabattObj = new Yourdelivery_Model_Rabatt_Code($code);
            } else {
                throw new Yourdelivery_Exception_Database_Inconsistency();
            }
        } else {
            $rabattObj = $this->_checkAffiliPrint($code);

            if(is_null($rabattObj)) {
                $rabattObj  = $this->_checkGoolive($code);
            }

            if (is_null($rabattObj)) {
                try {
                    $rabattObj = new Yourdelivery_Model_Rabatt_Code($code);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

                    if (!$rabattObj instanceof Yourdelivery_Model_Rabatt_Code) {
                        return $this->discountState(
                                        false, __("Leider kein gültiger Gutschein. Vielleicht haben Sie sich vertippt."), array(
                                    'type' => 'warn',
                                    'msg' => sprintf('Customer #%s %s tried to add code %s which does not exists', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $code)
                                        ), $code
                        );
                    }
                }
            }
        }

        //check for fraud
        if ( in_array($rabattObj->getParent()->getType(), array(1,2,3,5)) && Default_Helpers_Fraud_Customer::detect(Default_Helpers_Web::getClientIp(), Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_IP_NEWCUSTOMER_DISCOUNT) ){
            //thats strange, and we won't allow it!
            return $this->discountState(
                            false, __("Leider kein gültiger Gutschein. Vielleicht hast Du dich vertippt."), array(
                        'type' => 'err',
                        'msg' => 'tried to add discount of type 1,2,3 or 5 but ip is blocked'), $code);
        }

        //check if only allowed for not logged in users #9742 Rebuy; #9743 meinfoto.de
        if (in_array($rabattObj->getParent()->getId(), array(3583, 5855, 9742, 9743)) && $isLoggedIn) {
            return $this->discountState(
                            false, __("Dieser Gutschein ist nur für Neukunden"), array(
                        'type' => 'warn',
                        'msg' => sprintf('Customer #%s %s tried to add code #%s %s assigned only to new customers', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code)
                            ), $rabattObj
            );
        }

        // check, wheather discount is allowed for order kind
        if ($rabattObj->getParent()->isOnlyPrivate() && $kind == 'comp') {
            return $this->discountState(
                            false, __("Dieser Gutschein ist nur für Privatkunden"), array(
                        'type' => 'warn',
                        'msg' => sprintf('Customer #%s %s tried to add code #%s %s assigned only to private customers', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code)
                            ), $rabattObj
            );
        }

        if ($rabattObj->getParent()->isOnlyCompany() && $kind == 'priv') {
            return $this->discountState(
                            false, __("Dieser Gutschein ist nur für Firmenkunden"), array(
                        'type' => 'warn',
                        'msg' => sprintf('Customer #%s %s tried to add code #%s %s assigned only to companies', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code)
                            ), $rabattObj
            );
        }

        if ($rabattObj->getParent()->isOnlyPremium() && !$service->isPremium()) {
            // tested
            return $this->discountState(
                            false, __("Dieser Gutschein ist nur für Premium-Restaurants"), array(
                        'type' => 'warn',
                        'msg' => sprintf('Customer #%s %s tried to add code #%s %s assigned only to premium, but not to #%s %s', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code, $service->getId(), $service->getName())
                            ), $rabattObj
            );
        }

        if ($rabattObj->getParent()->isOnlyRestaurant() && $service->isPremium()) {
            // tested
            return $this->discountState(
                            false, __("Dieser Gutschein ist nur für Lieferdienste"), array(
                        'type' => 'warn',
                        'msg' => sprintf('Customer #%s %s tried to add code #%s %s assigned only to deliver services, but not to premium-service #%s %s', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code, $service->getId(), $service->getName())
                            ), $rabattObj
            );
        }

        if ($rabattObj->getParent()->isOnlyIphone()) {
            // tested
            return $this->discountState(
                            false, __("Dieser Gutschein ist nur in der Android- oder iPhone-App von Lieferando.de einlösbar."), array(
                        'type' => 'warn',
                        'msg' => sprintf('Customer #%s %s tried to add code #%s %s assigned only to iphone', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code)
                            ), $rabattObj
            );
        }

        // lieferando11 / On Broadway
        if ($rabattObj->getParent()->getId() == 6472 && !in_array($service->getId(), Yourdelivery_Model_Rabatt::getLieferando11Restaurants())) {
            // tested
            return $this->discountState(
                            false, __('Dieser Gutschein ist bei diesem Restaurant nicht einlösbar'), array(
                        'type' => 'warn',
                        'msg' => sprintf('Customer #%s %s tried to add code #%s %s not assigned to #%s %s', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code, $service->getId(), $service->getName())
                            ), $rabattObj
            );
        }

        // pulcinella
        if ($rabattObj->getParent()->getId() == 6473 && $service->getId() != 13439) {
            // tested
            return $this->discountState(
                            false, 'Der Gutschein ist nur bei "Pulcinella" einlösbar.', array(
                        'type' => 'warn',
                        'msg' => sprintf('Customer #%s %s tried to add code #%s %s assigned only to Pulcinella', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code)
                            ), $rabattObj
            );
        }

        // charleys
        if ($rabattObj->getParent()->getId() == 7465 && $service->getId() != 12931) {
            // tested
            return $this->discountState(
                            false, 'Der Gutschein ist nur bei "Charleys Pizza Profis" einlösbar.', array(
                        'type' => 'warn',
                        'msg' => sprintf('Customer #%s %s tried to add code #%s %s assigned only to Charleys Pizza Profis', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code)
                            ), $rabattObj
            );
        }

        // Chinaland
        if ($rabattObj->getParent()->getId() == 9861 && $service->getId() != 15000) {
            // tested
            return $this->discountState(
                            false, 'Der Gutschein ist nur bei "Chinaland" einlösbar.', array(
                        'type' => 'warn',
                        'msg' => sprintf('Customer #%s %s tried to add code #%s %s assigned only to Chinaland', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code)
                            ), $rabattObj
            );
        }

        // Onkel Lee
        if ($rabattObj->getParent()->getId() == 9864 && $service->getId() != 13967) {
            // tested
            return $this->discountState(
                            false, 'Der Gutschein ist nur bei "Onkel Lee" einlösbar.', array(
                        'type' => 'warn',
                        'msg' => sprintf('Customer #%s %s tried to add code #%s %s assigned only to Onkel Lee', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code)
                            ), $rabattObj
            );
        }

        // AVANTI99
        if ($rabattObj->getParent()->getId() == 22674 && !$service->isAvanti()) {
            // tested
            return $this->discountState(
                            false, __('Dieser Gutschein ist bei diesem Restaurant nicht einlösbar'), array(
                        'type' => 'warn',
                        'msg' => sprintf('Customer #%s %s tried to add code #%s %s not assigned to #%s %s', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code, $service->getId(), $service->getName())
                            ), $rabattObj
            );
        }


        //check for specified Restaurants
        if(in_array($rabattObj->getParent()->getType(),array(4,5,6,7))  &&  $rabattObj->getParent()->getRestaurants() && !$rabattObj->getParent()->isDiscountRestaurant($service->getId())) {
             return $this->discountState(
                            false, __('Dieser Lieferservice nimmt nicht an dieser Aktion teil. Bitte wähle einen anderen oder löse den Gutschein bei einer anderen Bestellung ein.'), array(
                        'type' => 'warn',
                        'msg' => sprintf('customer #%s %s tried to add code to servive "%s #%d" that does not have this discount action', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $service->getName(), $service->getId())
                            ), $rabattObj
            );
        }

        //check for specified Cities
        if(in_array($rabattObj->getParent()->getType(),array(4,5,6,7))  &&  $rabattObj->getParent()->getCitys() && !$rabattObj->getParent()->isDiscountCity($cityId)) {
             return $this->discountState(
                            false, __('Diese PLZ nimmt nicht an dieser Aktion teil. Bitte ändere Deine Lieferadresse oder löse Deinen Gutschein bei Deiner nächsten Bestellung ein.'), array(
                        'type' => 'warn',
                        'msg' => sprintf('customer #%s %s tried to add code to servive "%s #%d" that does not have this discount action', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $service->getName(), $service->getId())
                            ), $rabattObj
            );
        }


        if ($service->isOnlycash() || $service->isNoContract()) {
            // tested
            return $this->discountState(
                            false, __('Bei diesem Restaurant können keine Gutscheine eingelöst werden, da keine Online- oder Teil-Online-Zahlung akzeptiert wird.'), array(
                        'type' => 'warn',
                        'msg' => sprintf('customer #%s %s tried to add code to servive "%s #%d" that does not accept online payment', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $service->getName(), $service->getId())
                            ), $rabattObj
            );
        }


        //NewCustomerDiscount, should be last before the rest
        if ($rabattObj->getParent()->isNewCustomerDiscount() && (boolean) $rabattObj->getParent()->getNewCustomerCheck() && $rabattObj->isUsable()) {      
            return $this->discountState(
                            true, '', array(
                        'type' => 'info',
                        'msg' => sprintf('customer #%s %s successfully added newcustomer code #%s %s', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code)
                            ), $rabattObj, false
            );
        }





        /**
         * YEAHA - customer passed lots of checks
         * and now check if the code is still usable
         */
        if (!$rabattObj->isUsable()) {

            // check if code is not usable yet or not usable any more
            $start = $rabattObj->getParent()->getStart();
            if ($start > time()) {
                // tested
                $notStartedInfo = $rabattObj->getParent()->getNotStartedInfo();
                return $this->discountState(
                                false, empty($notStartedInfo) ? __("Dieser Gutschein ist noch nicht gültig") : $notStartedInfo, array(
                            'type' => 'warn',
                            'msg' => sprintf('customer #%s %s tried to add code #%s %s that is not started yet (startTime %s - now %s)', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code, date('d.m.Y H:i:s'), time())
                                ), $rabattObj
                );
            } else {
                // tested
                $expInfo = $rabattObj->getParent()->getExpirationInfo();
                if ($rabattObj->isUsed()) {
                    return $this->discountState(
                                    false, __('Der Gutschein wurde schon benutzt oder wurde noch nicht aktiviert. Zum Aktivieren des Gutscheins bitte auf die genannte Aktionsseite gehen.'), array(
                                'type' => 'warn',
                                'msg' => sprintf('customer #%s %s tried to add code #%s %s which is already used', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code)
                                    ), $rabattObj
                    );
                } elseif (!$rabattObj->getParent()->isStatus()) {
                    return $this->discountState(
                                    false, __('Der Gutschein wurde deaktiviert.'), array(
                                'type' => 'warn',
                                'msg' => sprintf('customer #%s %s tried to add code #%s %s which is already used', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code)
                                    ), $rabattObj
                    );
                } else {
                    return $this->discountState(
                                    false, empty($expInfo) || is_null($expInfo) ? __('Der Gutschein war nur für einen bestimmten Zeitraum gültig und ist mittlerweile abgelaufen.') : $expInfo, array(
                                'type' => 'info',
                                'msg' => sprintf('customer #%s %s tried to add code #%s %s (Rabatt #%s) which is too old', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code, $rabattObj->getParent()->getId())
                                    ), $rabattObj
                    );
                }
            }
        }

        // SUCCESS
        // tested
        return $this->discountState(
                        true, '', array(
                    'type' => 'info',
                    'msg' => sprintf('customer #%s %s successfully added code #%s %s', $this->getCustomer()->getId(), $this->getCustomer()->getFullname(), $rabattObj->getId(), $code)
                        ), $rabattObj, $service->isAvanti() && $rabattObj->getParent()->getId() == 22674
        );
    }

    /**
     * search through menu and match with service name
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function searchAction() {

        $this->_disableView();
        $table = new Yourdelivery_Model_DbTable_Restaurant();

        $request = $this->getRequest();
        $search_string = addslashes($request->getParam('search'));

        $search = explode(' ', strtolower($search_string));

        $ids = $request->getParam('ids');
        $result = array(
            'meals' => array(),
            'services' => array(
                'match' => array(),
                'nomatch' => array()
            )
        );
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $table->setId((integer) $id);
                $_result = $table->getSmallMenu($search);
                if (count($_result) > 0) {
                    foreach ($_result as $key => $data) {
                        foreach ($data as $k => $v) {
                            $data[$k] = stripslashes($v);
                        }
                        $min = $data['min'];
                        $max = $data['max'];
                        if ($min == $max) {
                            $data['cost'] = __('%s €', intToPrice($min));
                        } else {
                            $data['cost'] = __('von %s bis %s €', intToPrice($min), intToPrice($max));
                        }
                        $_result[$key] = $data;
                    }
                    $result['meals'][] = $_result;
                    $result['services']['match'][] = $id;
                } else {
                    $name = strtolower($table->getCurrent()->name);
                    foreach ($search as $s) {
                        if ($s && strstr($name, $s)) {
                            $result['services']['match'][] = $id;
                            break;
                        }
                    }
                }
            }

            $result['services']['nomatch'] = array_values(array_diff($ids, $result['services']['match']));
        }
        echo json_encode($result);
    }

    /**
     * alter current size and display extras
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function updateextrasAction() {
        $this->view->enableCache();
        $request = $this->getRequest();
        $mealId = (integer) $request->getParam('mid', null);
        $sizeId = (integer) $request->getParam('sid', null);
        $extras = $request->getParam('extras', array());

        if (!is_array($extras)) {
            $extras = array($extras);
        }

        if ($sizeId == 0) {
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        }

        if (!is_null($mealId)) {
            try {
                $meal = new Yourdelivery_Model_Meals($mealId);
                if (is_null($sizeId)) {
                    $sizes = $meal->getSizes();
                    if (is_array($sizes)) {
                        $size = current($sizes);
                        $sizeId = $size['id'];
                    } else {
                        return;
                    }
                }
                $meal->setCurrentSize($sizeId);
                $this->view->meal = $meal;
                $this->view->extras = $extras;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return;
            }
        }
    }

    /**
     * open lightbox to display meal options and extras
     * @todo: add cache here!
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function callmealAction() {

        $this->view->enableCache();

        $request = $this->getRequest();
        $mealId = (integer) $request->getParam('id');
        $sizeId = (integer) $request->getParam('size');
        $updateMeal = (integer) $request->getParam('update', 0);

        $this->view->count = 1;
        $this->view->update = $updateMeal;

        if ($sizeId == 0) {
            $sizeId = null;
        }

        if ($mealId) {
            try {
                $meal = new Yourdelivery_Model_Meals($mealId);
                if (!$sizeId) {
                    $sizes = $meal->getSizes();
                    if (is_array($sizes)) {
                        $size = current($sizes);
                        $sizeId = $size['id'];
                    } else {
                        return;
                    }
                }
                $meal->setCurrentSize($sizeId);
                $this->view->meal = $meal;
                $this->view->count = $meal->getMinAmount();
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return;
            }
        }
    }

    /**
     * add a budget to the current order
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function addbudgetAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();
        if ($request->isPost()) {

            $email = filter_var($request->getParam('email', null), FILTER_SANITIZE_EMAIL);
            $amount = (integer) $request->getParam('amount', 0);
            $customerId = (integer) $request->getParam('customer', 0);

            if (empty($email) || $amount <= 0 || $customerId <= 0) {
                $this->logger->warn(sprintf('invalid parameters provided (%s,%s,%s)', $email, $amount, $customerId));
                $this->getResponse()->setHttpResponseCode(406);
                return;
            }

            try {
                $customer = new Yourdelivery_Model_Customer($customerId);
                $customer_to_add = new Yourdelivery_Model_Customer(null, $email);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->logger->warn(sprintf('could not find either customer %s or %s', $email, $customerId));
                $this->getResponse()->setHttpResponseCode(404);
                return;
            }

            if (!$customer->isEmployee() || !$customer_to_add->isEmployee() || is_null($customer->getCompany()) || is_null($customer_to_add->getCompany())) {
                $this->logger->warn(sprintf('either %s or %s is not employeed in a company', $email, $customerId));
                $this->getResponse()->setHttpResponseCode(404);
                return;
            }

            if ($customer->getCompany()->getId() != $customer_to_add->getCompany()->getId()) {
                $this->logger->warn(sprintf('%s and %s are not in the same company', $email, $customerId));
                $this->getResponse()->setHttpResponseCode(404);
                return;
            }

            $customer_to_add = new Yourdelivery_Model_Customer_Company($customer_to_add->getId(), $customer_to_add->getCompany()->getId());
            if ($customer_to_add->getCurrentBudget() < $amount) {
                $this->logger->warn(sprintf('could not add %s from customer %s, because amount is higher available %d', $amount, $customer_to_add->getId(), $customer_to_add->getCurrentBudget()));
                $this->getResponse()->setHttpResponseCode(406);
                return;
            }
        }
    }

    /**
     * @todo: is this a duplicated method from the user request controller?
     */
    public function feedbackAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();
        $text = $request->getParam('text', null);
        if (!is_null($text)) {
            $this->getCustomer()->sendFeedback($text);
        }
    }

    public function repeatAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();
        $hash = htmlentities($request->getParam('hash', 0));
        $locationId = (integer) $request->getParam('location', 0);

        //try to load order
        try {
            if (strlen($hash) <= 0) {
                throw new Yourdelivery_Exception_Database_Inconsistency();
            }
            $order = Yourdelivery_Model_Order::createFromHash($hash);
            if (!is_object($order)) {
                throw new Yourdelivery_Exception_Database_Inconsistency();
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->getResponse()->setHttpResponseCode(404);
            echo json_encode(array(
                'result' => false,
                'msg' => __('Die Bestellung wurde nicht gefunden.')));
            return;
        }

        if (!is_object($order->getService()) || $order->getService()->isDeleted() || !$order->getService()->isOnline()) {
            $this->getResponse()->setHttpResponseCode(406);
            //service is currently not available
            echo json_encode(array(
                'result' => false,
                'msg' => __('Der Lieferservice "%s" steht zur Zeit leider nicht zur Verfügung.', $order->getService()->getName())));
            return;
        }

        //step 1, select a location and setup ydState object
        $ydState = Yourdelivery_Cookie::factory('yd-state');
        $cityId = $order->getLocation()->getCity()->getId();
        if ($locationId > 0) {
            try {
                $location = new Yourdelivery_Model_Location($locationId);
                $cityId = $location->getCity()->getId();
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $locationId = 0;
            }
        } elseif ($order->getKind() == 'comp') {
            $locations = $order->getCustomer()->getCompanyLocations();
            foreach ($locations as $loc) {
                if ($loc->getCityId() == $order->getLocation()->getCityId()) {
                    $locationId = $loc->getId();
                    break;
                }
            }
        }

        $ydState->set('city', $cityId);
        $ydState->set('kind', $order->getKind());
        $ydState->set('mode', $order->getMode());
        $ydState->set('location', $locationId);
        $ydState->save();

        $data = array(
            'kind' => $order->getKind(),
            'mode' => $order->getMode(),
            'cityId' => $order->getLocation()->getCityId(),
            'serviceId' => $order->getService()->getId(),
            'comment' => $order->getLocation()->getComment()
        );

        $card = $order->getCard(false, true, true);
        foreach ($card['bucket'] as $celem) {
            foreach ($celem as $hash => $elem) {
                $meal = $elem['meal'];
                $options = array();
                $extras = array();
                foreach ($meal->getCurrentOptions() as $opt) {
                    $options[] = $opt->getId();
                }
                foreach ($meal->getCurrentExtras() as $ext) {
                    $extras[$ext->getId()]['id'] = $ext->getId();
                    $extras[$ext->getId()]['count'] = $ext->getCount();
                }
                $data['meal'][$hash] = array(
                    'id' => $meal->getId(),
                    'size' => $elem['size'],
                    'special' => $meal->getSpecial(),
                    'count' => $elem['count'],
                    'options' => $options,
                    'extras' => $extras
                );
            }
        }

        //generate post to finish page
        echo json_encode(array_merge($data, $order->getLocation()->getData()));
    }

    /**
     * get the last order and cache if json is requested
     *
     * @since 25.08.2011
     * @author Matthias Laug <laug@lieferando.de>
     * @todo migrate to hash
     */
    public function lastorderAction() {
        $request = $this->getRequest();
        $type = $request->getParam('type', 'html');
        $hash = htmlentities($request->getParam('hash'));
        $cache_hash = md5($hash);

        if ($type == 'json' && strlen($cache_hash) > 0) {
            $data = Default_Helpers_Cache::load($cache_hash);
            if ($data != null) {
                echo $data;
                $this->getResponse()->setHttpResponseCode(304);
                return;
            }
        }

        try {
            if (strlen($hash) <= 0) {
                throw new Yourdelivery_Exception_Database_Inconsistency();
            }
            $order = Yourdelivery_Model_Order::createFromHash($hash);
            if (!is_object($order)) {
                throw new Yourdelivery_Exception_Database_Inconsistency();
            }

            $kind = $request->getParam('kind');
            if (strlen($kind) <= 0 || $order->getKind() != $kind) {
                throw new Yourdelivery_Exception_Database_Inconsistency();
            }

            $mode = $request->getParam('mode');
            if (strlen($mode) <= 0 || $order->getMode() != $mode) {
                throw new Yourdelivery_Exception_Database_Inconsistency();
            }

            $service = $order->getService();
            if (!$service->isOnline()) {
                throw new Yourdelivery_Exception_Database_Inconsistency();
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->getResponse()
                    ->setHttpResponseCode(204);
            $this->_disableView();
            return;
        }

        switch ($type) {
            default:
            case 'html':
                $this->view->enableCache();
                $this->view->service = $service;
                $this->view->mode = $request->getParam('mode', 'rest');
                $this->view->order = $order;
                break;

            case 'json':
                $this->_disableView();
                $customer = $order->getCustomer();
                $location = $order->getLocation();
                $data = json_encode(array(
                    'prename' => $customer->getPrename(),
                    'name' => $customer->getName(),
                    'email' => $customer->getEmail(),
                    'tel' => $location->getTel(),
                    'plz' => $location->getPlz(),
                    'cityId' => $location->getCity()->getId(),
                    'street' => $location->getStreet(),
                    'number' => $location->getHausnr(),
                    'company' => $location->getCompanyName(),
                    'etage' => $location->getEtage(),
                    'comment' => $location->getComment()
                        ));
                Default_Helpers_Cache::store($cache_hash, $value);
                echo $data;
                break;
        }
    }

    /**
     * get more information about a service
     * @author Matthias Laug <laug@lieferando.de>
     * @since 20.11.2011
     */
    public function serviceinfoAction() {
        $this->view->enableCache();
        $id = (integer) $this->getRequest()->getParam('id');
        try {
            if ($id <= 0) {
                throw new Yourdelivery_Exception_Database_Inconsistency();
            }

            $service = new Yourdelivery_Model_Servicetype_Restaurant($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return $this->getResponse()->setHttpResponseCode(404);
        }
        $this->view->service = $service;
    }

    /**
     * Checks order status and delivery time, sends these data as JSON
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>, Vincent Priem <priem@lieferando.de>
     * @since 16.05.2012
     *
     * @return void
     */
    public function checknotifyAction() {

        $request = $this->getRequest();
        $hash = $request->getParam('hash', "");
        if (!strlen($hash)) {
            return $this->_json(array());
        }
        
        $this->view->order = Yourdelivery_Model_Order::createFromHash($hash);
    }

    /**
     * Sends fidelity cash in limit value, as JSON
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 18.06.2012
     *
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 20.06.2012
     *
     * @return json
     */
    public function getfidelitymaxcostAction() {
        $this->_disableView();
        $fidelityConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini', APPLICATION_ENV);
        // Outputting response as dummy JSON (setting valid content-type make some browsers crazy, therefore skipped)
        echo json_encode(array(
            'maxCost' => (integer) $fidelityConfig->fidelity->cashin->maxcost
        ));
    }
}
