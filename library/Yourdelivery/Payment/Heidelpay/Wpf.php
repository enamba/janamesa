<?php

/**
 * Yourdelivery_Payment_Heidelpay_Wpf
 * @author vpriem
 * @since 18.05.2011
 */
class Yourdelivery_Payment_Heidelpay_Wpf extends Yourdelivery_Payment_Abstract {

    /**
     * The url
     * @var string
     */
    private $_url;

    /**
     * The secret
     * @var string
     */
    private $_secret;

    /**
     * The login data
     * @var string
     */
    private $_security_sender;
    private $_user_login;
    private $_user_pwd;
    private $_transaction_channel;
    private $_transaction_mode;

    /**
     * Constructor
     * @author vpriem
     * @since 18.05.2011
     */
    public function __construct() {

        $config = Zend_Registry::get('configuration');

        // is enabled?
        if (!$config->payment->credit->enabled) {
            return;
        }

        $this->_url = $config->payment->credit->wpfurl;
        $this->_security_sender = $config->payment->credit->security_sender;
        $this->_user_login = $config->payment->credit->user_login;
        $this->_user_pwd = $config->payment->credit->user_pwd;
        $this->_transaction_channel = $config->payment->credit->transaction_channel;
        $this->_transaction_mode = IS_PRODUCTION ? "LIVE" : "CONNECTOR_TEST";
        
        // prevent shits
        if (IS_PRODUCTION && strpos($this->_url, "test") !== false) {
            new Yourdelivery_Exception("Try to use the CTPE WPF Test API in a production environment");
        }
    }

    /**
     * Get redirect url
     * @author vpriem
     * @since 18.05.2011
     * @param Yourdelivery_Model_Order $order
     * @param boolean $register
     * @return string|boolean
     */
    public function redirectUser(Yourdelivery_Model_Order $order, $register = false) {

        $customer = $order->getCustomer();
        $location = $order->getLocation();

        $cssName = strtolower($this->getCountryCode());
        if (strpos(HOSTNAME, ".eat-star") !== false) {
            $cssName = "eatstar";
        }

        $config = Zend_Registry::get('configuration');
        $data = array(
            'TRANSACTION.CHANNEL' => $this->_transaction_channel,
            'TRANSACTION.MODE' => $this->_transaction_mode,
            'REQUEST.VERSION' => "1.0",
            'PAYMENT.CODE' => 'CC.' . ($register ? "RG" : "DB"),
            'ACCOUNT.COUNTRY' => $this->getCountryCode(),
            'FRONTEND.RESPONSE_URL' => "http://" . HOSTNAME . "/payment_heidelpay/" . ($register ? "register" : "callback") . "/secret/" . sha1($order->getNr() . SALT),
            'FRONTEND.REDIRECT_TIME' => 0,
            'FRONTEND.CSS_PATH' => "https://cs.hosteurope.de/yd-css/yourdelivery-webpayment-" . $cssName . ".css",
            'PRESENTATION.AMOUNT' => inttoprice($order->getAbsTotal(), 2, "."),
            'PRESENTATION.CURRENCY' => $this->getCurrencyCode(),
            'PRESENTATION.USAGE' => __("Bestellung-Nr. %s", $order->getNr()),
            'IDENTIFICATION.TRANSACTIONID' => $order->getId() . "-" . time(),
            'FRONTEND.MODE' => "DEFAULT",
            'FRONTEND.ENABLED' => "true",
            'FRONTEND.POPUP' => "false",
            'FRONTEND.LANGUAGE_SELECTOR' => "true",
            'FRONTEND.LANGUAGE' => strtolower($this->getCountryCode()),
            'NAME.GIVEN' => $customer->getPrename(),
            'NAME.FAMILY' => $customer->getName(),
            'ADDRESS.STREET' => $location->getStreet() . " " . $location->getHausnr(),
            'ADDRESS.ZIP' => $location->getOrt()->getPlz(),
            'ADDRESS.CITY' => $location->getOrt()->getOrt(),
            'ADDRESS.COUNTRY' => $this->getCountryCode(),
            'CONTACT.EMAIL' => $customer->getEmail(),
            'CONTACT.PHONE' => $location->getTel(),
        );

        if ( $config->domain->base == 'janamesa.com.br' ){
            $data['ADDRESS.STATE'] = 'SP';
        }
        
        // send
        return $this->_process($order, $data);
    }

    /**
     * Get hash
     * @author vpriem
     * @since 18.05.2011
     * @param array $data
     * @return string
     */
    public function getHash(array $data) {

        $h = array(
            $data['PAYMENT_CODE'],
            $data['IDENTIFICATION_TRANSACTIONID'],
            $data['IDENTIFICATION_UNIQUEID'],
            $data['PROCESSING_RETURN_CODE'],
            $data['CLEARING_AMOUNT'],
            $data['CLEARING_CURRENCY'],
            $data['PROCESSING_RISK_SCORE'],
            $data['TRANSACTION_MODE'],
            $this->_secret,
        );

        return md5(implode("|", $h));
    }
    
    /**
     * return URL for testcase
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 10.11.2011
     * @return string
     */
    public function getRedirectUrl() {
         
        return $this->_url;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @throws Yourdelivery_Payment_Heidelpay_Exception
     * @param Yourdelivery_Model_Order $order
     * @return boolean
     */
    public function refundOrder(Yourdelivery_Model_Order $order) {

        $uniqueId = null;
        
        $transactions = $order->getTable()
                              ->getHeidelpayWpfTransactions();
        foreach ($transactions as $transaction) {
            $response = $transaction->getResponse();
            if ($transaction->isResponseSuccessful()) {
                $uniqueId = $response['IDENTIFICATION_UNIQUEID'];
                break;
            }
        }
        
        if ($uniqueId === null) {
            $transactions = $order->getTable()
                                  ->getHeidelpayXmlTransactions();
            foreach ($transactions as $transaction) {
                if ($transaction->isResponseSuccessful()) {
                    $uniqueId = $transaction->getResponseUniqueId();
                    break;
                }
            }
        }
        
        if ($uniqueId === null) {
            throw new Yourdelivery_Payment_Heidelpay_Exception("No UNIQUEID could be found");
        }
        
        // check if is Company Order over Budget with privAmount
        $amount = $order->getAbsTotal();
        if ($order->getMode() == "comp" && $order->isCompanyCredit()) {
            $amount = $order->getPayedAmount();
        }

        // In Brasil the customers want a direct reversal which only works on the same day
        if ($this->config->payment->credit->tryreversal
            && $order->getTime() > date('Y-m-d 00:00:00')) {
            // build transaction
            $data = array(
                'TRANSACTION.CHANNEL' => $this->_transaction_channel,
                'TRANSACTION.MODE' => $this->_transaction_mode,
                'TRANSACTION.RESPONSE' => 'SYNC',
                'IDENTIFICATION.TRANSACTIONID' => $order->getId() . "-" . time() - "rf",
                'IDENTIFICATION.REFERENCEID' => $uniqueId,
                'REQUEST.VERSION' => "1.0",
                'PAYMENT.CODE' => 'CC.RV',
            );

            // send
            $result = $this->_process($order, $data);
            if ($result['POST_VALIDATION'] == "ACK") {
                return $result;
            }
            $log = sprintf('Credit refund: cannot revers order #%s with heidelpay, trying refund now. Reason of revers problem: %s, %s', $order->getId(), $result['PROCESSING_REASON'], $result['PROCESSING_RETURN']);
            $logger->warn($log);
        }

        // build transaction
        $data = array(
            'TRANSACTION.CHANNEL' => $this->_transaction_channel,
            'TRANSACTION.MODE' => $this->_transaction_mode,
            'TRANSACTION.RESPONSE' => 'SYNC',
            'IDENTIFICATION.TRANSACTIONID' => $order->getId() . "-" . time() - "rf",
            'IDENTIFICATION.REFERENCEID' => $uniqueId,
            'REQUEST.VERSION' => "1.0",
            'PAYMENT.CODE' => 'CC.RF',
            'PRESENTATION.AMOUNT' => inttoprice($amount, 2, "."),
            'PRESENTATION.CURRENCY' => $this->getCurrencyCode(),
            'PRESENTATION.USAGE' => __("Rückbuchung Bestellung-Nr. %s", $order->getNr()),
        );

        // send
        return $this->_process($order, $data);
        
    }

    /**
     * @author vpriem
     * @since 03.11.2011
     * @throws Yourdelivery_Payment_Heidelpay_Exception
     * @param array $data
     * @return array
     */
    private function _process(Yourdelivery_Model_Order $order, array $data) {

        $dbTable = new Yourdelivery_Model_DbTable_Heidelpay_Wpf_Transactions();
        $dbRow = $dbTable->createRow(array(
            'orderId' => $order->getId(),
            'params' => http_build_query($data),
        ));

        $data = array_merge(array(
            'SECURITY.SENDER' => $this->_security_sender,
            'USER.LOGIN' => $this->_user_login,
            'USER.PWD' => $this->_user_pwd,
        ), $data);

        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $this->_url);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($c, CURLOPT_USERAGENT, "php ctpepost");
        curl_setopt($c, CURLINFO_HEADER_OUT, true);
        curl_setopt($c, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8"));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($data));
        $curlExec = curl_exec($c);
        $curlError = curl_error($c);
        curl_close($c);

        $dbRow->response = $curlExec === false ? $curlError : $curlExec;
        $dbRow->save();

        if ($curlExec === false) {
            throw new Yourdelivery_Payment_Heidelpay_Exception($curlError);
        }

        parse_str($curlExec, $curlExec);
        return $curlExec;
    }

}
