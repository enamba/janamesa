<?php

/**
 * some helper classes for adyen soap request
 */
class ModificationRequest {

    public $merchantAccount;
    public $originalReference;

}

/**
 * some helper classes for adyen soap request
 */
class ModificationResult {

    public $pspReference;
    public $response;

}

/**
 * @author Matthias Laug <laug@lieferando.de>
 */
class Yourdelivery_Payment_Adyen extends Yourdelivery_Payment_Abstract {

    /**
     * @var string 
     */
    protected $_initUrl = '';

    /**
     * @var string 
     */
    protected $_sharedSecret = '';

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 20.03.2012
     */
    public function __construct() {

        $this->_initUrl = $this->getConfig()->adyen->initurl;
        $this->_sharedSecret = $this->getConfig()->adyen->sharedkey;
    }

    /**
     * get the shared secret key
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 20.03.2012
     * @return string 
     */
    public function getSharedSecret() {

        return $this->_sharedSecret;
    }

    /**
     * get from data for this order
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 20.03.2012
     * @param Yourdelivery_Model_Order_Abstract $order
     * @return array 
     */
    public function initPayment(Yourdelivery_Model_Order_Abstract $order, $unique = null) {

        if ($unique === null) {
            $unique = time();
        }

        $data = array();
        $data['skinCode'] = $this->getConfig()->adyen->skin;
        $data['merchantReference'] = $order->getNr() . '-' . $unique; // The transaction reference you assign to the payment
        $data['paymentAmount'] = $order->getAbsTotal(false);  // Amount in minor units (10000 for 100.00 EUR)
        $data['currencyCode'] = $this->getConfig()->adyen->currencycode;  // 3 Digit ISO Currency Code  (e.g. GBP, USD)
        $data['shipBeforeDate'] = $order->getDeliverTimestamp(); // example: ship in 5 days
        $data['shopperLocale'] = $this->getConfig()->locale->name; // Locale (language) to present to shopper (e.g. en_US, nl, fr, fr_BE)
        $data['orderData'] = $this->prepareCart($order); // A description of the payment which is displayed to shoppers
        $data['sessionValidity'] = $order->getDeliverTimestamp() + (60 * 60); // example: shopper has one hour to complete
        $data['shopperReference'] = $order->getCustomer()->getId(); // the shopper id in our system 
        $data['shopperEmail'] = $order->getCustomer()->getEmail(); // the shopper's email address   
        $data['merchantAccount'] = $this->getConfig()->adyen->merchant;
        
        if(!IS_PRODUCTION && $this->getConfig()->adyen->merchant == 'PysznePL'){
            $data['countryCode'] = 'PL';
        }
        
        if(strpos($order->getDomain(), $this->getConfig()->domain->base) === false){ // this case sets the resultURL/redirectURl for satellites
            $data['resURL'] = sprintf('http://%s/payment_adyen/process', HOSTNAME);
        }
        
        extract($data);
        // concatenate all the data needed to calculate the HMAC-string in the correct order
        // (please refer to Appendix B in the Integration Manual for more details)
        $hmacData = $paymentAmount .
                $currencyCode .
                $shipBeforeDate .
                $merchantReference .
                $skinCode .
                $merchantAccount .
                $sessionValidity .
                $shopperEmail .
                $shopperReference;

        // base64 encode the binary result of the HMAC computation. If you use a PHP version < 5.0.12 you
        // you may need to use a different HMAC implementation. Please refer to "Computing the HMAC in PHP"
        // example from the downloads section on https://support.adyen.com/ 
        $data['merchantSignature'] = base64_encode(hash_hmac('sha1', $hmacData, $this->getSharedSecret(), true));

        //create entry in database
        $table = new Yourdelivery_Model_DbTable_Adyen_Transactions();
        $table->createRow(array(
            'orderId' => $order->getId(),
            'transactionId' => $merchantReference
        ))->save();

        return $data;
    }

    /**
     * process response from adyen and check validaty
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @param Yourdelivery_Model_Order_Abstract $order
     * @since 19.03.2012
     * @return boolean
     */
    public function processPayment(Yourdelivery_Model_Order_Abstract $order, Zend_Controller_Request_Abstract $request) {

        $authResult = urldecode($request->getParam('authResult'));
        $pspReference = urldecode($request->getParam('pspReference'));
        $merchantReference = urldecode($request->getParam('merchantReference'));
        $skinCode = urldecode($request->getParam('skinCode'));
        $returnData = urldecode($request->getParam('merchantReturnData'));

        $table = new Yourdelivery_Model_DbTable_Adyen_Transactions();
        $transaction = $table->getByTransactionId($merchantReference);
        if (!$transaction) {
            $this->getLogger()->warn(sprintf('ADYEN: could not find any transaction by given merchantReference %s', $merchantReference));
            return false;
        }

        //set returning time
        $transaction->return = date(DATETIME_DB);
        $transaction->responseCode = $authResult;

        $signatureGivenFromAdyen = $request->getParam('merchantSig');
        $hmacData = $authResult .
                $pspReference .
                $merchantReference .
                $skinCode .
                $returnData;
        $calculatedSignature = base64_encode(hash_hmac('sha1', $hmacData, $this->getSharedSecret(), true));
        if ($calculatedSignature != $signatureGivenFromAdyen) {
            $this->getLogger()->warn(sprintf('ADYEN: signature %s did not match expteced signature %s on response for order %s', $signatureGivenFromAdyen, $calculatedSignature, $order->getId()));
            $transaction->valid = false;
            $transaction->save();
            return false;
        }

        //save reference number
        $transaction->reference = $pspReference;
        $transaction->save();
        return true;
    }

    /**
     * cancel an transaction and refund money
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 22.03.2012
     * @return boolean
     */
    public function refund(Yourdelivery_Model_Order $order) {

        if ($order->isRefunded()) {
            return true;
        }

        $table = new Yourdelivery_Model_DbTable_Adyen_Transactions();
        $transactions = $table->getByOrder($order->getId());
        foreach ($transactions as $transaction) {

            //create soap request and refund all transaction associated with this order
            ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache 

            $rr = new ModificationRequest();
            $rr->merchantAccount = $this->getConfig()->adyen->merchant;
            $rr->originalReference = $transaction->reference;

            $ro = new ModificationResult();

            $classmap = array(
                'cancelOrRefund' => 'cancelOrRefund',
                'ModificationRequest' => 'ModificationRequest',
                'ModificationResult' => 'ModificationResult'
            );

            $soapClient = new SoapClient(APPLICATION_PATH . '/templates/adyen/Payment.wsdl', array(
                        'login' => "USER",
                        'password' => "PASS",
                        'soap_version' => SOAP_1_1,
                        'style' => SOAP_DOCUMENT,
                        'encoding' => SOAP_LITERAL,
                        'location' => IS_PRODUCTION ? "https://pal-live.adyen.com/pal/servlet/soap/Payment" : "https://pal-test.adyen.com/pal/servlet/soap/Payment",
                        'trace' => false,
                        'classmap' => $classmap));

            try {
                $response = $soapClient->cancelOrRefund(array('modificationRequest' => $rr, 'captureResponse' => $ro));
                if ($response->cancelOrRefundResult->response == '[cancelOrRefund-received]') {
                    $transaction->refunded = true;
                    $transaction->refundedOn = date(DATETIME_DB);
                } 
                $transaction->refundResponse = $response->cancelOrRefundResult->response;
                $transaction->save();
                return $response->cancelOrRefundResult->response;
            } catch (SoapFault $e) {
                $this->getLogger()->info(sprintf('ADYEN: Soaperror: %s for order(#%s)', $e->getMessage(), $order->getId()));
                return $e->getMessage();
            }
        }
    }

    /**
     * prepare html code for the cart
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 19.03.2012
     * @param Yourdelivery_Model_Order_Abstract $order
     * @return string 
     */
    private function prepareCart(Yourdelivery_Model_Order_Abstract $order) {
        // uncommented this part because of YD-2597. When the issue wiht images over https is somehow solved, this part can be uncommented
        
//        $restaurantLogo = sprintf('%s/%s/service/%s/%s-110-71.jpg',
//            $this->getConfig()->domain->timthumb, 
//            $this->getConfig()->domain->base, 
//            $order->getService()->getId(),
//            urlencode($order->getService()->getName())
//        );

        
        // '<div class="cart"><div id=yd-rest-name>%s</div><div id=yd-rest-logo><img src="https://%s" /></div></div>' // old syntax, if we want to switch back
        $data = sprintf('<div class="cart"><div id=yd-rest-name>%s</div></div>',
            $order->getService()->getName() 
        );
        return base64_encode(gzencode($data));
    }

}
