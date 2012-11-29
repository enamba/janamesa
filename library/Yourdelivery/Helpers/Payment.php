<?php

/**
 * Description of Yourdelivery_Helpers_Payment
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
class Yourdelivery_Helpers_Payment {

    protected static $_logging = null;
    protected static $_reason = null;

    /**
     * log to system logger
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.01.2011
     * @param Yourdelivery_Model_Order $order
     * @param string $message
     */
    private static function log($order = null, $message = null, $setAsLastReason = false) {

        $config = Zend_Registry::get('configuration');
        $logger = new Yourdelivery_Log();
        $file_logger = new Zend_Log_Writer_Stream(
                        sprintf($config->logging->payment, date('d-m-Y'))
        );

        if (APPLICATION_ENV == "production") {
            $filter = new Zend_Log_Filter_Priority(Zend_Log::INFO);
            $logger->addFilter($filter);
        }

        $logger->addWriter($file_logger);

        if ($order === null) {
            $text = __('PaymentHelper: Order object has gone away :(');
        } else {
            $text = __(sprintf('PaymentHelper: OrderNr %s, ServiceId %d, CustomerId %d, Nachricht: %s', $order->getNr(), $order->getService()->getId(), $order->getCustomer()->getId(), $message));
        }
        $logger->info($text);

        if ($setAsLastReason) {
            $session = new Zend_Session_Namespace('Default');
            $session->lastReason = $message;
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.01.2011
     * @return string 'PaymentHelper: OrderNr %s, ServiceId %d, CustomerId %d, Nachricht: %s'
     */
    public static function getLastReason() {
        $session = new Zend_Session_Namespace('Default');
        return $session->lastReason;
    }

    /**
     * check, if given value is valid object
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.11.2010
     * @param Yourdelivery_Model_Order_Abstract | Yourdelivery_Model_Order_Company_Single_Restaurant_Canteen
     * @return boolean
     */
    public static function checkOrderObject($order) {

        if (is_null($order) || !is_object($order)) {
            return false;
        }

        if (!($order instanceof Yourdelivery_Model_Order_Abstract)) {
            return false;
        }
    }

    /**
     * gives string for the preselected payment
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.11.2010
     * @param Yourdelivery_Model_Order_Abstract | Yourdelivery_Model_Order_Company_Single_Restaurant_Canteen
     * @return string
     */
    public static function preselected($order) {

        self::checkOrderObject($order);

        $kind = $order->getKind();
        $mode = $order->getMode();
        $payment = $order->getPayment();
        $service = $order->getService();
        $config = Zend_Registry::get('configuration');

        /**
         * this service only allows bar payment
         * we ignore onlyCash, if service is premium
         */
        if ($service->isNoContract() || ($service->isOnlycash() && !$service->isPremium())) {
            return 'bar';
        }

        // if already set, we preselect it
        if (!empty($payment) && in_array($payment, array('bar', 'credit', 'paypal', 'bill', 'debit', 'ebanking'))) {
            // check if payment is allowed, otherwise go on
            if (self::allowPayment($order, $payment)) {
                return $payment;
            }
        }

        $allowedPayments = array();


        switch ($kind) {
            case 'priv': {
                    switch ($mode) {
                        case 'rest': {

                                if (self::allowBar($order)) {
                                    $allowedPayments[] = 'bar';
                                }

                                if (self::allowEbanking($order)) {
                                    $allowedPayments[] = 'ebanking';
                                }

                                if (self::allowPaypal($order)) {
                                    $allowedPayments[] = 'paypal';
                                }

                                if (self::allowCredit($order)) {
                                    $allowedPayments[] = 'credit';
                                }
                                break;
                            }
                        case 'cater':
                        case 'great':
                        case 'fruit': {

                                if (self::allowEbanking($order)) {
                                    $allowedPayments[] = 'ebanking';
                                }

                                if (self::allowPaypal($order)) {
                                    $allowedPayments[] = 'paypal';
                                }
                                if (self::allowCredit($order)) {
                                    $allowedPayments[] = 'credit';
                                }
                                break;
                            }
                        default:
                            $allowedPayments[] = 'none';
                            break;
                    }

                    // end private
                    break;
                }

            case 'comp': {
                    switch ($mode) {
                        case 'rest': {
                                // company restaurant
                                if (self::allowBar($order)) {
                                    $allowedPayments[] = 'bar';
                                }

                                if (self::allowEbanking($order)) {
                                    $allowedPayments[] = 'ebanking';
                                }

                                if (self::allowPaypal($order)) {
                                    $allowedPayments[] = 'paypal';
                                }

                                if (self::allowCredit($order)) {

                                    $allowedPayments[] = 'credit';
                                }

                                break;
                            }
                        case 'cater':
                        case 'great':
                        case 'fuit': {
                                /**
                                 * nothing to do, because nothing to pay for customer
                                 * company will get bill for order
                                 */
                                break;
                            }
                        default:
                            break;
                    }

                    // end company
                    break;
                }

            default:
                //return 'credit';
                $allowedPayments[] = 'none';
                break;
        }


        if (in_array($config->payment->default, $allowedPayments)) {
            return $config->payment->default;
        } else if ($config->payment->default == 'none') {
            return "none";
        } else {
            return $allowedPayments[0];
        }
    }

    /**
     * show payment div if something is to pay
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.11.2010
     * @param Yourdelivery_Model_Order_Abstract | Yourdelivery_Model_Order_Company_Single_Restaurant_Canteen
     * @return boolean
     */
    public static function showPayment($order) {

        self::checkOrderObject($order);

        if ($order->getAbsTotal() <= 0) {
            return false;
        }

        return true;
    }

    /**
     * get all payments, that are activated in config file
     * 
     * @author Felix Haferkorn <haferkron@lieferando.de>
     * @since 29.12.2011
     * 
     * @return array 
     */
    public static function getAllowedPayments() {
        $allowed = array();

        $config = Zend_Registry::get('configuration');

        if ($config->payment->bar->enabled == 1) {
            $allowed[] = 'bar';
        }

        if ($config->payment->bill->enabled == 1) {
            $allowed[] = 'bill';
        }

        if ($config->payment->debit->enabled == 1) {
            $allowed[] = 'debit';
        }

        if ($config->payment->credit->enabled == 1) {
            $allowed[] = 'credit';
        }

        if ($config->payment->paypal->enabled == 1) {
            $allowed[] = 'paypal';
        }

        if ($config->payment->ebanking->enabled == 1) {
            $allowed[] = 'ebanking';
        }

        return $allowed;
    }

    /**
     * DON'T allow bar if:
     * - service is Premium
     * - order is cater, fruit, great, canteen => mode != rest
     * - if service allows no bar payment
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.11.2010
     * @param Yourdelivery_Model_Order_Abstract | Yourdelivery_Model_Order_Company_Single_Restaurant_Canteen
     * @return boolean
     */
    public static function allowBar($order, $checkAmount = false) {

        $config = Zend_Registry::get('configuration');

        if ($config->payment->bar->enabled == 0) {
            self::log($order, __('Barzahlung abgelehnt, weil systemweit deaktiviert'), true);
            return false;
        }

        self::checkOrderObject($order);

        if ($checkAmount && $order->getAbsTotal() <= 0) {
            self::log($order, __('Barzahlung zugestimmt, weil abstotal <= 0 und checkAmount == true'), true);
            return true;
        }

        // little hack for charleys - they want to accept bar-payment when discount order
        if (is_object($order->getDiscount()) && $order->getDiscount()->getParent()->isNoCash() && !in_array($order->getService()->getId(), array(12931)) && !$order->getService()->isAvanti() && $order->getAbsTotal() > 0) {
            self::log($order, __('Barzahlung abgelehnt, weil Rabatt "%s" keine Barzahlung erlaubt', $order->getDiscount()->getParent()->getName()), true);
            return false;
        }

        if ($order->getService()->isPremium() && $order->getAbsTotal() > 0) {
            self::log($order, __('Barzahlung abgelehnt, weil Lieferservice "%s" premium ist (AbsTotal = %d)', $order->getService()->getName(), $order->getAbsTotal()), true);
            return false;
        }

        if ($order->getMode() != 'rest') {
            self::log($order, __('Barzahlung abgelehnt, weil Bestelltyp nicht Restaurant ist, Bestelltyp ist "%s"', $order->getMode()), true);
            return false;
        }

        if (!$order->getService()->isPaymentbar() && $order->getAbsTotal() > 0) {
            self::log($order, __('Barzahlung abgelehnt, weil Restaurant keine Barzahlung akzeptiert'), true);
            return false;
        }

        if ($order->getService()->isAvanti() && is_object($order->getDiscount()) && $order->getDiscount()->getParent()->getId() != 22674) {
            if ($order->getCurrentPayment() == 'bar') {
                self::log($order, __('Barzahlung abgelehnt, weil Restaurant Avanti ist und diesen Gutschein nicht mit Bar akzeptiert'), true);
                return false;
            }
        }

        self::log($order, __('Barzahlung zugestimmt'));
        return true;
    }

    /**
     * we DON'T allow credit if
     * - total is under X cent (defined in config)
     * - but if this is a canteen order, we allow
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.11.2010
     * @param Yourdelivery_Model_Order_Abstract | Yourdelivery_Model_Order_Company_Single_Restaurant_Canteen
     * @return boolean
     */
    public static function allowCredit($order) {

        $config = Zend_Registry::get('configuration');

        /**
         * Get objects
         */
        $location = $order->getLocation();
        $service = $order->getService();
        $customer = $order->getCustomer();

        if ($config->payment->credit->enabled == 0) {
            self::log($order, __('Kreditkartenzahlung abgelehnt, weil systemweit deaktiviert'), true);
            return false;
        }

        self::checkOrderObject($order);

        $parentOrt = null;
        try {
            if (is_object($location) && !is_null($location->getCityId()) && is_object($location->getCity())) {
                $parentCityId = $location->getCity()->getParentCityId();
                if ($parentCityId > 0) {
                    try {
                        $parentCity = new Yourdelivery_Model_City($parentCityId);
                        $parentOrt = $parentCity->getCity();
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        
                    }
                }
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            // nothing
        }

        /**
         * this service only allows bar payment
         * we ignore onlyCash, if service is premium
         */
        if ($service->isNoContract() || ($service->isOnlycash() && !$service->isPremium())) {
            self::log($order, __('Kreditkartenzahlung abgelehnt, weil Restaurant "%s" nur Barzahlung akzeptiert, Restaurant ist nicht Premium', $service->getName()), true);
            return false;
        }


        /**
         * No Credit Card for New Customer  Discounts
         */
        if ($order->hasNewCustomerDiscount()) {
            self::log($order, __('Kreditkartenzahlung abgelehnt, weil Neukundengutschein benutzt wurde'), true);
            return false;
        }

        /**
         * if discount order, we allow credit payment, if total of order is greater or equal defined limit
         * we don't refer on absTotal (open amount)
         */
        if (is_object($order->getDiscount()) && $order->getAbsTotal() > 0) {
            $total = $order->getBucketTotal() + $service->getDeliverCost() + $service->getCurierCost();

            if ($total >= $config->payment->credit->min) {
                self::log($order, __('Kreditkartenzahlung zugestimmt, weil Gutscheinverwendung und Bestellwert (nicht offener Wert) von %s über Grenze von %s', inttoprice($total), intToPrice($config->payment->credit->min)));
                return true;
            }
        }

        if ($order->getAbsTotal() <= 0) {
            self::log($order, __('Kreditkartenzahlung abgelehnt, weil offener Betrag von %s <= 0', intToPrice($order->getAbsTotal())), true);
            return false;
        }

        if ($order->getAbsTotal() < $config->payment->credit->min && $order->getKind() != 'comp') {
            self::log($order, __('Kreditkartenzahlung abgelehnt, weil offener Betrag der Privatbestellung von %s kleiner als Grenze von %s', intToPrice($order->getAbsTotal()), $config->payment->credit->min), true);
            return false;
        }

        self::log($order, __('Kreditkartenzahlung zugestimmt'));
        return true;
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.11.2010
     * @param Yourdelivery_Model_Order_Abstract
     * @return boolean
     */
    public static function allowPaypal($order) {


        $config = Zend_Registry::get('configuration');

        if ($config->payment->paypal->enabled == 0) {
            self::log($order, __('Paypalbezahlung abgelehnt, weil systemweit deaktiviert'), true);
            return false;
        }

        self::checkOrderObject($order);

        if ($order->getAbsTotal() <= 0) {
            self::log($order, __('Paypalbezahlung abgelehnt, weil offener Betrag <= 0'), true);
            return false;
        }

        /**
         * this service only allows bar payment
         * we ignore onlyCash, if service is premium
         */
        if ($order->getService()->isNoContract() || ($order->getService()->isOnlycash() && !$order->getService()->isPremium())) {
            self::log($order, __('Paypalbezahlung abgelehnt, weil Restaurant nur Barzahlung akzeptiert, Restaurant ist nicht Premium'), true);
            return false;
        }

        if ($order->getMode() == 'canteen') {
            self::log($order, __('Paypalbezahlung abgelehnt, weil Bestelltyp Online Lunch'), true);
            return false;
        }

        self::log($order, 'Paypalbezahlung zugestimmt');
        return true;
    }

    /**
     * we allow debit if
     * - order is canteen
     * - OR SPECIAL HACK VRCOM_ONLY
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.11.2010
     * @param Yourdelivery_Model_Order_Abstract | Yourdelivery_Model_Order_Company_Single_Restaurant_Canteen
     * @return boolean
     */
    public static function allowDebit($order) {

        $config = Zend_Registry::get('configuration');

        if ($config->payment->debit->enabled == 0) {
            self::log($order, __('Lastschrift abgelehnt, weil Lastschrift systemweit deaktiviert'), true);
            return false;
        }

        self::checkOrderObject($order);

        /**
         * this service only allows bar payment
         * we ignore onlyCash, if service is premium
         */
        $service = $order->getService();
        if ($service->isNoContract() || ($service->isOnlycash() && !$service->isPremium())) {
            self::log($order, __('Lastschrift abgelehnt, weil Restaurant nur Barzahlung akzeptiert, Restaurant ist nicht Premium'), true);
            return false;
        }

//        if ($order->getState() == Yourdelivery_Model_Order::PAYMENT_NOT_AFFIRMED) {
//            self::log($order, 'Lastschrift abgelehnt, weil im prepayment');
//            return false;
//        }

        if ($order->getAbsTotal() <= 0) {
            self::log($order, __('Lastschrift abgelehnt, weil offener Betrag <= 0'), true);
            return false;
        }

        /**
         * HACK for VRCOM
         */
//        $customer = $order->getCustomer();
//        if ($customer->isEmployee() && $customer->getCompany()->getId() == VRCOM) {
//            self::log($order, 'Lastschrift zugestimmt, weil Mitarbeiter VRCOM');
//            return true;
//        }

        self::log($order, __('Lastschrift standardmäßig zugestimmt'), true);
        return true;
    }

    /**
     * we DON'T allow PRIVATE bill, if:
     * - order is company order
     * - if time is not during support times (900 - 2400)
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.11.2010
     * @param Yourdelivery_Model_Order_Abstract | Yourdelivery_Model_Order_Company_Single_Restaurant_Canteen
     * @return boolean
     */
    public static function allowBill($order) {

        $config = Zend_Registry::get('configuration');

        if ($order->getKind() == 'comp') {
            return true;
        }

        if ($config->payment->bill->enabled == 0) {
            self::log($order, __('Rechnung abgelehnt, weil systemweit deaktiviert'), true);
            return false;
        }

        self::checkOrderObject($order);

        if ($order->getState() == Yourdelivery_Model_Order::PAYMENT_NOT_AFFIRMED) {
            self::log($order, 'Rechnung abgelehnt, weil im prepayment');
            return false;
        }

        if ($order->getAbsTotal() <= 0) {
            self::log($order, __('Rechnung abgelehnt, weil offener Betrag <= 0'), true);
            return false;
        }

        /**
         * this service only allows bar payment
         * we ignore onlyCash, if service is premium
         */
        if ($order->getService()->isNoContract() || ($order->getService()->isOnlycash() && !$order->getService()->isPremium())) {
            self::log($order, __('Rechnung abgelehnt, weil Restaurant nur Barzahlung aktiviert, Restaurant ist nicht Premium'), true);
            return false;
        }

        $kind = $order->getKind();
        $mode = $order->getMode();

        switch ($kind) {

            case 'comp': {
                    self::log($order, sprintf(__('Rechnung abgelehnt, weil Firmenbestellung (%s)', $order->getCustomer()->getCompany()->getName())), true);
                    return false;
                }

            case 'priv': {

                    if ($order->getCustomer()->isEmployee()) {
                        self::log($order, sprintf(__('Rechnung abgelehnt, weil Kunde Mitarbeiter einer Firma (%s)', $order->getCustomer()->getCompany()->getName())), true);
                        return false;
                    }

                    /**
                     * check time - we only allow bill at support times
                     * we don't need to set upper limit
                     */
                    if ((time() < mktime(9, 00)) || (time() > mktime(23, 59))) {
                        self::log($order, sprintf(__('Rechnung abgelehnt, weil Uhrzeit zwischen %s', date('H:i', mktime(9, 00)), date('H:i', mktime(23, 9)))), true);
                        return false;
                    }
                    self::log($order, __('Rechnung zugestimmt, weil privat'));
                    return true;
                }
            default: {
                    self::log($order, __('Rechnung abgelehnt, weil weder Firmen- noch Privatbestellung *WTF* '), true);
                    return false;
                }
        }

        self::log($order, __('Rechnung standardmäßig abgelehnt'), true);
        return false;
    }

    /**
     * We allow eBanking for everybody
     * @author vpriem
     * @since 10.01.2011
     * @param Yourdelivery_Model_Order_Abstract | Yourdelivery_Model_Order_Company_Single_Restaurant_Canteen
     * @return boolean
     */
    public static function allowEbanking($order) {

        $config = Zend_Registry::get('configuration');

        if ($config->payment->ebanking->enabled == 0) {
            self::log($order, __('Sofortüberweisung abgelehnt, weil systemweit deaktiviert'), true);
            return false;
        }

        self::checkOrderObject($order);

        if ($order->getAbsTotal() <= 0) {
            self::log($order, __('Sofortüberweisung abgelehnt, weil offeneer Betrag <= 0'), true);
            return false;
        }

        if ($order->getAbsTotal() <= 10) {
            self::log($order, __('Sofortüberweisung abgelehnt, weil Betrag <= 10 cents'), true);
            return false;
        }

        /**
         * this service only allows bar payment
         * we ignore onlyCash, if service is premium
         */
        if ($order->getService()->isNoContract() || ($order->getService()->isOnlycash() && !$order->getService()->isPremium())) {
            self::log($order, __('Sofortüberweisung abgelehnt, weil Restaurant nur Barzahlung akzeptiert, Restaurant ist nicht Premium'), true);
            return false;
        }

        /**
         * No Ebanking for New Customer  Discounts
         */
//        if ($order->hasNewCustomerDiscount()) {
//            self::log($order, __('Sofortüberweisung abgelehnt, weil Neukundengutschein benutzt wurde'), true);
//            return false;
//        }

        self::log($order, __('Sofortüberweisung zugestimmt'));
        return true;
    }

    /**
     * this is a helper only. returns already implemented functions-values
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 25.11.2010
     *
     * @param string $payment
     * @return boolean
     */
    public static function allowPayment($order, $payment) {

        self::checkOrderObject($order);

        if (!in_array($payment, array('bar', 'credit', 'paypal', 'bill', 'debit', 'ebanking'))) {
            return false;
        }

        switch ($payment) {
            case 'bar':
                return self::allowBar($order);

            case 'credit':
                return self::allowCredit($order);

            case 'paypal':
                return self::allowPaypal($order);

            case 'ebanking':
                return self::allowEbanking($order);

            case 'bill':
                return self::allowBill($order);

            case 'debit':
                return self::allowDebit($order);

            default:
                return false;
        }
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @return boolean
     */
    public static function refundEbanking(Yourdelivery_Model_Order_Abstract $order, Zend_Log $logger, array &$messages = array(), $comment = null) {

        // check if already refunded
        if ($order->isRefunded()) {
            $messages[] = __("Sofortüberweisung wurde bereits vorgemerkt.");
            $logger->info(sprintf("Ebanking refund: order #%s already refunded", $order->getId()));
            return;
        }

        $refund = new Yourdelivery_Payment_Ebanking_Refund();
        try {
            $response = $refund->refund($order, $comment);
            if ($response->getStatus() == "ok") {
                $messages[] = __("Sofortüberweisung wurde erfolgreich vorgemerkt");
                $log =  new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_REFUND_EBANKING, $order->getId());                 
                $logger->info($log->getRawMessage());
                $order->setStatus($order->getState(), $log);
                return true;
            } else {
                $messages[] = __("Sofortüberweisung konnte nicht vorgemerkt werden: " . $response->getErrorMessage());
                $log =  new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_REFUND_FAIL_EBANKING, $order->getId(),$response->getErrorCode(), $response->getErrorMessage());                                 
                $logger->warn($log->getRawMessage());
                $order->setStatus($order->getState(), $log);
                return false;
            }
        } catch (Yourdelivery_Payment_Ebanking_Exception $e) {
            $messages[] = __("Sofortüberweisung konnte nicht vorgemerkt werden: " . $e->getMessage());
            $log =  new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_REFUND_FAIL_EBANKING, $order->getId(), $e->getCode(), $e->getMessage());                                           
            $logger->crit($log->getRawMessage());
            $order->setStatus($order->getState(), $log);
            return false;
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 20.09.2011
     * @return boolean
     */
    public static function refundPaypal(Yourdelivery_Model_Order_Abstract $order, Zend_Log $logger, array &$messages = array()) {

        // check if already refunded
        if ($order->isRefunded()) {
            $messages[] = sprintf("Paypal wurde schon zurückgebucht.");
            $logger->info(sprintf("Paypal refund: order #%s already refunded", $order->getId()));
            return false;
        }

        try {
            $paypalAPI = new Yourdelivery_Payment_Paypal();
            $response = $paypalAPI->refundTransaction($order);
            if ($response['ACK'] == "Success") {
                $messages[] = __("Paypal wurde erfolgreich zurückgebucht");
                $log =  new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_REFUND_PAYPAL, $order->getId());                            
                $logger->info($log->getRawMessage());
                $order->setStatus($order->getState(), $log);
                return true;
            } else {
                $messages[] = __("Paypal konnte nicht storniert werden");
                $log =  new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_REFUND_FAIL_PAYPAL, $order->getId(), $response['L_LONGMESSAGE0']);                    
                $logger->warn($log->getRawMessage());
                $order->setStatus($order->getState(), $log);
                return false;
            }
        } catch (Yourdelivery_Payment_Paypal_Exception $e) {
            $messages[] = __("Paypal konnte nicht zurückgebucht werden: " . $e->getMessage());
            $log =  new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_REFUND_FAIL_PAYPAL, $order->getId(), $e->getMessage());                                
            $logger->crit($log->getRawMessage());
            $order->setStatus($order->getState(), $log);
            return false;
        }
    }

    /**
     * a wrapper for all credit payments
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.03.2012
     * @param Yourdelivery_Model_Order_Abstract $order
     * @param Zend_Log $logger
     * @param array $messages 
     * @return boolean
     */
    public static function refundCredit(Yourdelivery_Model_Order_Abstract $order, Zend_Log $logger, array &$messages = array()) {
        // check if already refunded
        if ($order->isRefunded()) {
            $messages[] = __("Kreditkartenzahlung wurde schon zurückgebucht.", $order->getId());
            $logger->info(sprintf("Credit refund: order #%s already refunded", $order->getId()));
            return false;
        }

        return self::refundHeidelpay($order, $logger, $messages) || self::refundAdyen($order, $logger, $messages);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.03.2012
     * @param Yourdelivery_Model_Order_Abstract $order
     * @param Zend_Log $logger
     * @param array $messages 
     * @return boolean
     */
    public static function refundAdyen(Yourdelivery_Model_Order_Abstract $order, Zend_Log $logger, array &$messages = array()) {
        //check if there is a transaction
        if ($order->getTable()->getAdyenTransaction()->count() == 0) {
            $logger->info(sprintf("Credit refund: order #%s not payed with adyen, no transactions found", $order->getId()));
            return false;
        }

        $adyen = new Yourdelivery_Payment_Adyen();
        $result = $adyen->refund($order);
        if ($result == '[cancelOrRefund-received]') {
            $messages[] = __("Kreditkartenzahlung wurde erfolgreich zurückgebucht");
            $log =  new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_REFUND_ADYEN, $order->getId());                                       
            $logger->info($log->getRawMessage());
            $order->setStatus($order->getState(), $log);
            return true;
        } else {
            $messages[] = __('Kreditkartenzahlung konnte nicht zurückgebucht werden: %s', $result);
            $log =  new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_REFUND_FAIL_ADYEN, $order->getId(), $result);                                            
            $logger->info($log->getRawMessage());
            $order->setStatus($order->getState(), $log);
            return false;
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 15.11.2011
     * @param Yourdelivery_Model_Order_Abstract $order
     * @param Zend_Log $logger
     * @param array $messages 
     * @return boolean
     */
    public static function refundHeidelpay(Yourdelivery_Model_Order_Abstract $order, Zend_Log $logger, array &$messages = array()) {

        //check if there is a transaction
        if ($order->getTable()->getHeidelpayWpfTransactions()->count() == 0) {
            $logger->info(sprintf("Credit refund: order #%s not payed with heidelpay, no transactions found", $order->getId()));
            return false;
        }

        try {
            $heidelpay = new Yourdelivery_Payment_Heidelpay_Wpf();
            $result = $heidelpay->refundOrder($order);
            if ($result['POST_VALIDATION'] == "ACK") {
                $messages[] = __("Kreditkartenzahlung wurde erfolgreich zurückgebucht");
                $log =  new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_REFUND_HEIDELPAY, $order->getId());                                                     
                $logger->info($log->getRawMessage());
                $order->setStatus($order->getState(), $log);
                return true;
            } else {
                $messages[] = __('Kreditkartenzahlung konnte nicht zurückgebucht werden: %s : %s', $result['PROCESSING_REASON'], $result['PROCESSING_RETURN']);
                $log =  new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::PAYMENT_REFUND_HEIDELPAY, $order->getId(), $result['PROCESSING_REASON'], $result['PROCESSING_RETURN']);                                                                     
                $logger->warn($log->getRawMessage());
                $order->setStatus($order->getState(), $log);
            }
        } catch (Yourdelivery_Payment_Heidelpay_Exception $e) {
            $messages[] = __('Kreditkartenzahlung konnte nicht zurückgebucht werden: %s', $e->getMessage());
            $logger->crit(sprintf("Credit refund: cannot refund order with heidelpay #%s because %s", $order->getId(), $e->getMessage()));
        }

        return false;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 21.12.2011
     * @param string $payerId 
     * @param Yourdelivery_Model_Order_Abstract $order
     * @return boolean
     */
    public static function isNewCustomerDiscountUsed($payerId, Yourdelivery_Model_Order_Abstract $order) {

        $config = Zend_Registry::get('configuration');

        if (!$config->ordering->discount->newcustomercheck || 
            !$order->getDiscount() || 
            $order->getDiscount()->getParent()->getNewCustomerCheck() != 1) {
            return false;
        }


        $logger = Zend_Registry::get('logger');
        if ($order->hasNewCustomerDiscount()) {

            $db = Zend_Registry::get('dbAdapterReadOnly');

            switch ($order->getPayment()) {
                case "paypal":
                    // white list for the pm team
                    if (!IS_PRODUCTION && $payerId == "7B6XW4WXT75P8") {
                        return false;
                    }
                    
                    $select = $db->select()->distinct()
                            ->from(array('pt' => 'paypal_transactions'), array())
                            ->join(array('o' => 'orders'), "o.id = pt.orderId", array())
                            ->join(array('rc' => 'rabatt_codes'), "o.rabattCodeId = rc.id", array('rabattId'))
                            ->join(array('r' => 'rabatt'), "r.id = rc.rabattId", array('type'))
                            ->where('o.state >= 0')
                            ->where('pt.payerId = ?', $payerId);
                    break;

                case "ebanking":
                    $select = $db->select()->distinct()
                            ->from(array('et' => 'ebanking_transactions'), array())
                            ->join(array('o' => 'orders'), "o.id = et.orderId", array())
                            ->join(array('rc' => 'rabatt_codes'), "o.rabattCodeId = rc.id", array('rabattId'))
                            ->join(array('r' => 'rabatt'), "r.id = rc.rabattId", array('type'))
                            ->where('o.state >= 0')
                            ->where('et.payerId = ?', $payerId);
                    break;

                default:
                    $logger->debug(sprintf('NEW CUSTOMER VERIFICATION: wrong payment %s for order %s, not searching for payerId %s', $order->getPayment(), $order->getId(), $payerId));
                    return false;
            }

            $discount = $order->getDiscount()->getParent();

            // check if this payerId has not been used before
            $rows = $db->fetchAll($select);
            foreach ($rows as $row) {

                // for this type payerId can be used one time per discout
                if ($discount->getType() == Yourdelivery_Model_Rabatt::TYPE_VERIFCATION_ACTION || $discount->getType() == Yourdelivery_Model_Rabatt::TYPE_VERIFCATION_SINGLE_ACTION) {
                    if ($discount->getId() == $row['rabattId']) {
                        $logger->warn(sprintf('NEW CUSTOMER VERIFICATION: dismissed new customer discount, because of already used PayerId %s for the discount %d ', $payerId, $row['rabattId']));
                        return true;
                    }
                }
                // otherwise on time for all discount
                else {
                    $logger->warn(sprintf('NEW CUSTOMER VERIFICATION: Dismissed new customer discount, because of already used PayerId %s for the discount %d', $payerId, $row['rabattId']));
                    return true;
                }
            }
            $logger->debug(sprintf('NEW CUSTOMER VERIFICATION: no entry found for payerId %s via %s', $payerId, $select->__toString()));
            return false;
        }
        $logger->debug(sprintf('NEW CUSTOMER VERIFICATION: order %s is not assigned with a new customer discount, not searching for payerId %s', $order->getId(), $payerId));

        return false;
    }

}
