<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StatusMessage
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 12.07.2012
 */
class Yourdelivery_Model_Order_StatusMessage {
    
    /**
     * Constants for messages
     */
    const NO_REASON = 0;
    const COMMENT = 100;

    /**
     * Order Sent Messages 
     */
    const ORDER_SENT_BY_FAX = 200;
    const ORDER_FAX_ERROR = 201;
    const ORDER_SENT_BY_EMAIL = 202;
    const ORDER_SENT_BY_SMS = 203;
    const ORDER_SENT_BY_SMS_ERROR_1 = 204;
    const ORDER_SENT_BY_SMS_ERROR_2 = 205;
    const ORDER_SENT_BY_MOBILE = 206;
    const ORDER_SENT_BY_MOBILE_ERROR = 207;
    const ORDER_SENT_BY_ECCLECTICA = 208;
    const ORDER_SENT_BY_ECCLECTICA_ERROR = 209;
    const ORDER_SENT_BY_PHONE = 210;
    const ORDER_SENT_OUT_BY = 211;
    const WOOPLA_OK = 212;
    const WOOPLA_ERROR = 213;
    const ORDER_ACOM_INVALID = 214;
    const ORDER_ACOM_PROGRESS = 215;
    const ORDER_ACOM_CONFIRM = 216;
    const ORDER_ACOM_ERROR = 217;
    const ORDER_ACOM_DELIVERING = 218;
    const ORDER_ACOM_DELIVERED = 219;
    const RETARUS_OK = 220;
    const RETARUS_OK_GREAT = 221;
    const RETARUS_ERROR_NO_TRAIN = 222;
    const RETARUS_ERROR = 223;
    const TOPUP_OK = 225;
    const TOPUP_ERROR = 226;
    const WIERCIK_RECEIVED = 227;
    const WIERCIK_LOST = 228;
    const WIERCIK_CONFIRM = 229;
    const WIERCIK_REJECTED = 230;
    const ECLECTICA_SUCCESS = 231;
    const ECLECTICA_FAIL = 232;
    const ORDER_RESENT_BY_SUPPORTER = 233;
    
    const CHARISMA_SUCCESS = 234;
    const CHARISMA_FAIL = 235;

    /**
     * Discount Messages 
     */
    const DISCOUNT_VALID = 300;
    const DISCOUNT_INVALID = 301;

    /**
     * Fax Messages 
     */
    const INTERFAX_OK = 400;
    const INTERFAX_OK_GREAT = 401;
    const INTERFAX_ERROR = 402;
    const INTERFAX_ERROR_NO_CREDIT = 403;
    const INTERFAX_ERROR_NO_TRAIN = 404;
    const ORDER_FAX_ERROR_NO_CONNECTION = 405;

    /**
     * Order Status Messages
     */
    const ORDER_FRAUD_MESSAGE = 500;
    const ORDER_BLACKLIST = 501;
    const ORDER_STORNO_MASSSTORNO = 502;
    const ORDER_STORNO = 503;
    const ORDER_CONFIRM_AFTER_FRAUD = 504;
    const ORDER_CONFIRM = 505;
    const ORDER_STORNO_SIMPLE = 506;


    /**
     * Payment Messages 
     */
    const PAYMENT_CHANGE_CREDIT = 600;
    const PAYMENT_CHANGE_EBANKING = 601;
    const PAYMENT_CHANGE_PAYPAL = 602;
    const PAYMENT_PROCESS_CREDIT = 603;
    const PAYMENT_PROCESS_EBANKING = 604;
    const PAYMENT_PROCESS_PAYPAL = 605;
    const PAYMENT_SUCCESS_CREDIT = 606;
    const PAYMENT_FAKE_CREDIT = 607;
    const PAYMENT_ERROR_CREDIT = 608;
    const PAYMENT_SUCCESS_ADYEN = 609;
    const PAYMENT_PENDING_ADYEN = 610;
    const PAYMENT_FAIL_EBANKING = 611;
    const PAYMENT_DISCOUNT_FAIL_EBANKING = 612;
    const PAYMENT_SUCCESS_EBANKING = 613;
    const PAYMENT_FAIL_EBANKING_NOK = 614;
    const PAYMENT_SUCCESS_PAYPAL_IPN = 615;
    const PAYMENT_FAIL_PAYPAL_IPN = 616;
    const PAYMENT_FAIL_PAYPAL_NC_DISCOUNT = 617;
    const PAYMENT_PAYPAL_PAYER_DETAILS = 618;
    const PAYMENT_FAKE_PAYPAL = 619;
    const PAYMENT_SUCCESS_PAYPAL = 620;
    const PAYMENT_FAIL_PAYPAL = 621;
    const PAYMENT_REFUND_EBANKING = 622;
    const PAYMENT_REFUND_FAIL_EBANKING = 623;
    const PAYMENT_REFUND_PAYPAL = 624;
    const PAYMENT_REFUND_FAIL_PAYPAL = 625;
    const PAYMENT_REFUND_ADYEN = 626;
    const PAYMENT_REFUND_FAIL_ADYEN = 627;
    const PAYMENT_REFUND_HEIDELPAY = 628;
    const PAYMENT_REFUND_FAIL_HEIDELPAY = 629;
    const PAYMENT_FAIL_ADYEN = 630;
    const PAYMENT_FAIL_PAYPAL_NOT_VERIFIED = 631;
    
    /**
     * Backend Messages 
     */
    const BACKEND_SET_DEPOSIT = 700;

    /**
     * All Messages as they will be inserted in the database for history 
     * @var array 
     */
    protected static $_messages = array(self::NO_REASON => 'No reason added',
        self::COMMENT => 'Comment: %s',
        self::ORDER_SENT_BY_FAX => 'Send out order to service via %s (%s) to %s',
        self::ORDER_FAX_ERROR => 'Could not send out fax by fax service, error occured',
        self::ORDER_SENT_BY_EMAIL => 'Sending out order via email to %s, no confirmation expected',
        self::ORDER_SENT_BY_SMS => 'Assigned to %s printer #%s',
        self::ORDER_SENT_BY_SMS_ERROR_1 => 'Could not assign to %s printer #%s',
        self::ORDER_SENT_BY_SMS_ERROR_2 => 'Could not send out via sms printer, no printer available',
        self::ORDER_SENT_BY_MOBILE => 'sms send out to %s',
        self::ORDER_SENT_BY_MOBILE_ERROR => 'failed to send out sms to %s',
        self::ORDER_SENT_BY_ECCLECTICA => 'order prepared for delivery via ecletica api',
        self::ORDER_SENT_BY_ECCLECTICA_ERROR => 'failed to prepare order for delivery via ecletica api',
        self::ORDER_SENT_BY_PHONE => 'order prepared for delivery via phone',
        self::ORDER_SENT_OUT_BY => 'Send out order to service via %s %s',
        self::DISCOUNT_INVALID => 'discout #%s is already used, cannot finalize order, payment has been refunded',
        self::DISCOUNT_VALID => 'successfully revalidated discount #%s, which now is not usable anymore',
        self::INTERFAX_OK => 'Interfax - Fax report came in: OK',
        self::INTERFAX_OK_GREAT => 'Interfax - Fax report came in for great order',
        self::INTERFAX_ERROR => 'Failed reception of fax: %s',
        self::INTERFAX_ERROR_NO_CREDIT => 'Interfax - Failed reception of fax: %s - Out of credit, awaiting refill',
        self::INTERFAX_ERROR_NO_TRAIN => 'Interfax - Maybe reception of fax: %s - Fax is being handled:Timeout',
        self::ORDER_FAX_ERROR_NO_CONNECTION => 'Could not send out fax by fax service, no connection',
        self::ORDER_FRAUD_MESSAGE => 'marked as %s because %s',
        self::ORDER_BLACKLIST => "BLACKLIST: %s by supporter %s",
        self::WOOPLA_OK => 'Service acknoledged recieval of fax via woopla',
        self::WOOPLA_ERROR => 'Service declined recieval of fax via woopla',
        self::PAYMENT_CHANGE_CREDIT => 'payment is not affirmed currently after payment change, waiting for credit payment response',
        self::PAYMENT_CHANGE_EBANKING => 'payment is not affirmed currently after payment change, waiting for ebanking payment response',
        self::PAYMENT_CHANGE_PAYPAL => 'payment is not affirmed currently after payment change, waiting for paypal payment response',
        self::PAYMENT_PROCESS_CREDIT => 'payment is not affirmed currently, waiting for credit payment response',
        self::PAYMENT_PROCESS_EBANKING => 'payment is not affirmed currently, waiting for ebanking payment response',
        self::PAYMENT_PROCESS_PAYPAL => 'payment is not affirmed currently, waiting for paypal payment response',
        self::PAYMENT_SUCCESS_CREDIT => 'successfully process credit payment',
        self::PAYMENT_FAKE_CREDIT => 'Heidelpay XML: order #%s tagged as FAKE because: %s: %s',
        self::PAYMENT_ERROR_CREDIT => 'Heidelpay XML: transaction failed for order #%s because: %s: %s',
        self::PAYMENT_SUCCESS_ADYEN => 'response from adyen has been authorised',
        self::PAYMENT_PENDING_ADYEN => 'response from adyen, order is pending',
        self::PAYMENT_FAIL_EBANKING => 'Ebanking: bad key for order #%s',
        self::PAYMENT_DISCOUNT_FAIL_EBANKING => 'Ebanking: receive ACK for order #%s but payment with discount %s not possible, account %s already used',
        self::PAYMENT_SUCCESS_EBANKING => 'Ebanking: receive ACK for order #%s',
        self::PAYMENT_FAIL_EBANKING_NOK => 'Ebanking: receive NOK for order #%s',
        self::PAYMENT_SUCCESS_PAYPAL_IPN => 'PayPal IPN: succesfully processed paypal payment',
        self::PAYMENT_FAIL_PAYPAL_IPN => 'PayPal IPN: Payment could not be verified',
        self::PAYMENT_FAIL_PAYPAL_NC_DISCOUNT => 'payment with discount %s not possible, account %s already used',
        self::PAYMENT_PAYPAL_PAYER_DETAILS => 'Paypal Details: Name: %s %s, Email: %s, Adresstatus: %s, Payer Status: %s',
        self::PAYMENT_FAKE_PAYPAL => '%s not paid yet!',
        self::PAYMENT_SUCCESS_PAYPAL => 'successfully process paypal response',
        self::PAYMENT_FAIL_PAYPAL => 'Paypal: transaction failed for order #%s because %s',
        self::PAYMENT_FAIL_PAYPAL_NOT_VERIFIED => 'payment with discount %s failed because paypal account %s is not yet verified',
        self::ORDER_STORNO_MASSSTORNO => 'Mass Storno by %s',
        self::ORDER_STORNO => 'Order Storno %s by %s',
        self::ORDER_CONFIRM_AFTER_FRAUD => 'order confirmed after Fraud by %s',
        self::ORDER_CONFIRM => 'order confirmed by %s',
        self::ORDER_ACOM_INVALID => 'INVALID ACOM RESPONSE: %s',
        self::ORDER_ACOM_PROGRESS => 'ACOM RESPONSE - order is in progress: %s',
        self::ORDER_ACOM_CONFIRM => 'ACOM RESPONSE - order has been confirmed: %s',
        self::ORDER_ACOM_ERROR => 'ACOM ERROR RESPONSE: %s',
        self::ORDER_ACOM_DELIVERING => 'ACOM RESPONSE - order is currently being delivered: %s',
        self::ORDER_ACOM_DELIVERED => 'ACOM RESPONSE - order has been delivered: %s',
        self::PAYMENT_FAIL_ADYEN => 'response from adyen,order has been denied. Cause: %s',
        self::RETARUS_OK => 'Retarus - Fax report came in: OK',
        self::RETARUS_OK_GREAT => 'Retarus - Fax report came in for great order',
        self::RETARUS_ERROR_NO_TRAIN => 'Retarus - Maybe reception of fax: %s',
        self::RETARUS_ERROR => 'Retarus - Failed reception of fax: %s',
        self::TOPUP_OK => 'Successfully send out via topup printer with %s mins',
        self::TOPUP_ERROR => 'Could not send out via topup printer, printer is offline',
        self::WIERCIK_RECEIVED => "order has been received, but not affirmed yet",
        self::WIERCIK_LOST => "Order has been lost on the way",
        self::WIERCIK_CONFIRM => "Order has been confirmed",
        self::WIERCIK_REJECTED => "order has been rejected by restaurant",
        self::PAYMENT_REFUND_EBANKING => "Ebanking refund: successfully refunded order #%s",
        self::PAYMENT_REFUND_FAIL_EBANKING => "Ebanking refund: cannot refund order #%s because %s:%s",
        self::PAYMENT_REFUND_PAYPAL => "Paypal refund: successfully refunded order #%s",
        self::PAYMENT_REFUND_FAIL_PAYPAL => 'Paypal refund: cannot refund order #%s. Reason: %s',
        self::PAYMENT_REFUND_ADYEN => "Credit refund: successfully refunded order #%s with adyen",
        self::PAYMENT_REFUND_FAIL_ADYEN => 'Credit refund: cannot refund order with adyen #%s with adyen because %s',
        self::PAYMENT_REFUND_HEIDELPAY => "Credit refund: successfully refunded order #%s with heidelpay",
        self::PAYMENT_REFUND_FAIL_HEIDELPAY => 'Credit refund: cannot refund order with heidelpay #%s because %s, %s',
        self::ECLECTICA_SUCCESS => 'eclectica processes order sucessfully',
        self::ECLECTICA_FAIL => 'eclectica processing order failed',
        self::ORDER_STORNO_SIMPLE => "Order has been cancelled",
        self::BACKEND_SET_DEPOSIT => "Pfand was set to %s",
        self::ORDER_RESENT_BY_SUPPORTER => 'resend to service via %s by %s',
        self::CHARISMA_SUCCESS => 'send out order to charisma api',
        self::CHARISMA_FAIL => 'could not send out order to charisma api: %s'
    );

    /**
     * Message Id
     * @var int
     */
    protected $_id = null;

    /**
     * Params for placeholders
     * @var array
     */
    protected $_params = array();

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.07.2012
     * @param int $id
     * @throws Yourdelivery_Exception_InvalidMessage
     */
    public function __construct($id) {
        if (empty(self::$_messages[$id])) {
            throw new Yourdelivery_Exception_InvalidMessage();
        }

        $this->_id = (integer) $id;
        $params = func_get_args();
        $this->_params = array_slice($params, 1);        
    }

    /**
     *  @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.07.2012 
     * @param array $params
     */
    public function setParams(array $params) {
        $this->_params = $params;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.07.2012
     * @return boolean
     */
    public function getTranslateMessage() {

        $this->_checkParams();
        
        if ($this->_id && !empty(self::$_messages[$this->_id])) {
            return call_user_func_array("__b", array_merge(array(self::$_messages[$this->_id]), $this->_params));
        } else {
            return false;
        }
    }

    /**
     *  @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.07.2012
     * @return boolean
     */
    public function getRawMessage() {
        
        $this->_checkParams();
        
        if ($this->_id && !empty(self::$_messages[$this->_id])) {
            return vsprintf(self::$_messages[$this->_id], $this->_params);
        } else {
            return false;
        }
    }

    /**
     *  @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.07.2012
     * @return type
     */
    public static function getMessages() {
        return self::$_messages;
    }

    /**
     *  @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.07.2012
     * @return type
     */
    public function getId() {
        return $this->_id;
    }

    /**
     *  @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.07.2012
     * @return string
     */
    public function __toString() {
        return serialize(array('id' => $this->_id, 'params' => $this->_params));
    }

    /**
     *  @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.07.2012
     * @param string $messageString
     * @return \Yourdelivery_Model_Order_StatusMessage
     */
    public static function createFromString($messageString) {
        $messageArray = unserialize($messageString);

        $id = (int) $messageArray['id'];

        $model = new Yourdelivery_Model_Order_StatusMessage($id);
        $model->setParams($messageArray['params']);

        return $model;
    }

    /**
     * check if params are missing
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 25.07.2012     
     */
    protected function _checkParams() {
        
        $check = str_replace("%%", "", self::$_messages[$this->_id]);

        $required_param_count = substr_count($check, "%");

        if (count($this->_params) < $required_param_count) {


            $traces = debug_backtrace();
            foreach ($traces as $i => $trace) {
                $error[] = " " . $i . ". " .
                        (isset($trace['class']) ? $trace['class'] . $trace['type'] : "") .
                        (isset($trace['function']) ? $trace['function'] . "() " : "") .
                        (isset($trace['file']) ? $trace['file'] . ":" . $trace['line'] : "");
            }
            $error = implode("\n", $error);

            Yourdelivery_Sender_Email::error("Parameteranzahl in Status Message ist inkorrekt:  Message: ".self::$_messages[$this->_id]. ", Parameter: ".print_r($this->_params)."\nStacktrace:\n ".$error);

            while (count($this->_params) < $required_param_count) {
                $this->_params[] = "Param missing in Status";
            }
        }
    }
    
}

