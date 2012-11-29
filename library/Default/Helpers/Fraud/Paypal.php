<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Checks if paypal payerId is in black or white, or if there is more than one Order with the same PayerId in one day
 * important functions:
 * 
 * payerIdInBlackList <- checks if black
 * payerIdInWhiteList <- checks if white
 * customerIsWhitelist <- checks if customer is white
 * moreThenOnOrderPerDay <- checks if more than one order per day 
 * 
 * basic functions:
 * 
 * __inWhitelist <- all whitelist functions are called here
 * __inBlacklist <- all blacklist functions are called here 
 * isLegit <- returns true if in whitelist or no match, return false if blacklist
 * 
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 9.9.2011
 */
class Default_Helpers_Fraud_Paypal {

    protected static $order;
    protected static $payerId;
    protected static $paypal_email;
    protected static $logger;
    protected static $list;
    protected static $message;
    protected static $status = Yourdelivery_Model_Order_Abstract::FAKE_STORNO;

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param Yourdelivery_Model_Order_Abstract $order
     * @param string $payerId
     * @return boolean
     */
    public static function isLegit($order, $payerId, $paypal_email) {
        $logger = Zend_Registry::get('logger');

        self::$order = $order;
        self::$payerId = $payerId;
        self::$paypal_email = $paypal_email;
        self::$logger = $logger;
        self::$list = Yourdelivery_Model_Support_Blacklist::getList(array('paypal'));
        self::$message = "";

        self::$logger->debug('PayerId: ' . self::$payerId);
        if (self::__inWhiteList()) {
            return true;
        }
        self::$logger->debug('After Whitelist...');
        if (self::__inBlackList()) {
            return false;
        }
        self::$logger->debug('After Blacklist...');
        return true;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return string
     */
    public static function getMessage() {
        return self::$message;
    }

    public static function getStatus() {
        return self::$status;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return boolean
     */
    protected static final function __inWhiteList() {

        $whiteListFuncs = array('payerIdinWhitelist', 'customerIsWhitelist');

        foreach ($whiteListFuncs as $function) {

            if (call_user_func(array('Default_Helpers_Fraud_Paypal', $function))) {

                self::$logger->debug('In Whitelist...');
                return true;
            }
        }

        return false;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return boolean
     */
    protected static final function __inBlackList() {

        $blacklistFuncs = array(
            'payerIdInBlackList',
            'moreThenOnOrderPerDay'
        );

        foreach ($blacklistFuncs as $function) {

            if (call_user_func(array('Default_Helpers_Fraud_Paypal', $function))) {
                self::$logger->debug('In Blacklist...');
                return true;
            }
        }

        return false;
    }

    //Whitelist Functions

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return boolean
     */
    protected static function payerIdinWhitelist() {

        foreach (self::$list as $entry) {

            if ($entry['type'] == Yourdelivery_Model_Support_Blacklist::TYPE_PAYPAL_PAYERID &&
                    $entry['value'] == self::$payerId &&
                    $entry['behaviour'] == Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_WHITELIST) {
                self::$message = __b("User in Whitelist, PayerId: %s" , self::$payerId);
                self::$logger->info(self::$message);
                $entry->addMatching(self::$order->getId());
                return true;
            }

            if ($entry['type'] == Yourdelivery_Model_Support_Blacklist::TYPE_PAYPAL_EMAIL &&
                    $entry['value'] == self::$paypal_email &&
                    $entry['behaviour'] == Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_WHITELIST) {
                self::$message = __b("User in Whitelist, PayerId: %s" , self::$paypal_email);
                self::$logger->info(self::$message);
                $entry->addMatching(self::$order->getId());
                return true;
            }
        }

        return false;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return boolean
     */
    protected static function customerIsWhitelist() {

        if (self::$order->getCustomer()->isWhitelist()) {
            self::$message = __b("Logged in User %s is Whitelist", self::$order->getCustomer()->getEmail());
            self::$logger->info(self::$message);

            return true;
        }

        return false;
    }

    //Blacklist Functions

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return boolean
     */
    protected static function payerIdInBlackList() {

        foreach (self::$list as $entry) {

            if ($entry['type'] == Yourdelivery_Model_Support_Blacklist::TYPE_PAYPAL_PAYERID &&
                    $entry['value'] == self::$payerId &&
                    $entry['behaviour'] == Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_BLACKLIST) {
                self::$message = __b("User in Blacklist, Paypal Email: %s" , self::$payerId);
                self::$logger->info(self::$message);
                $entry->addMatching(self::$order->getId());
                return true;
            }

            if ($entry['type'] == Yourdelivery_Model_Support_Blacklist::TYPE_PAYPAL_EMAIL &&
                    $entry['value'] == self::$paypal_email &&
                    $entry['behaviour'] == Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_BLACKLIST) {
                self::$message = __b("User in Blacklist, Paypal Email: " , self::$paypal_email);
                self::$logger->info(self::$message);
                $entry->addMatching(self::$order->getId());
                return true;
            }
        }

        return false;
    }

    /**
     * 
     * Filter all Orders where paypal is used more than once a day, with exception of stornos
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return boolean
     */
    protected static function moreThenOnOrderPerDay() {

        $db = Zend_Registry::get('dbAdapterReadOnly');

        $select = $db->select()->from('paypal_transactions')->joinLeft('orders', "orders.id=paypal_transactions.orderId", array('orders.id', 'orders.state'))
                ->where('orders.state != -2  OR orders.state IS NULL')
                ->where('payerId=?', self::$payerId)
                ->where('TIMESTAMPDIFF(DAY,paypal_transactions.created, NOW()) <1')
                ->where('WEEKDAY(paypal_transactions.created) = WEEKDAY(NOW())');



        $result = $db->fetchAll($select);
        self::$logger->debug(__FUNCTION__ . ' Count: ' . count($result));

        //Filter out Failures to prevent false Alarms
        $count = count($result);
        $orderIds = array();
        if ($count > 1) {
           
            foreach ($result as $entry) {
                $orderIds[] = $entry['orderId'];
                $response = unserialize($entry['response']);
                if ($response['ACK'] == 'Failure' || isset($response['L_ERRORCODE0'])) {
                    $count--;
                }
            }
        }

        if ($count > 1) {
            self::$status = Yourdelivery_Model_Order_Abstract::FAKE;
            self::$message = sprintf('User orderd more than one time this day, PayerId: %s , order #%s' , self::$payerId , implode(", order #", $orderIds));
            self::$logger->info(self::$message);
            return true;
        }

        return false;
    }

    protected static function addToBlacklist() {
        
    }

    public static function reset() {
        self::$status = Yourdelivery_Model_Order_Abstract::FAKE_STORNO;
    }

}

?>
