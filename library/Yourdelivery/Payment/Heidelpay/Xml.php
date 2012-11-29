<?php

/**
 * Yourdelivery_Payment_Heidelpay_Xml
 * @author vpriem
 * @since 15.11.2010
 */
class Yourdelivery_Payment_Heidelpay_Xml extends Yourdelivery_Payment_Abstract {

    /**
     * The url
     * @var string
     */
    private $_url;

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
     * @todo put this method in an abstract class
     * @author vpriem
     * @since 18.05.2011
     */
    public function __construct() {

        $config = Zend_Registry::get('configuration');

        // is enabled?
        if (!$config->payment->credit->enabled) {
            return;
        }

        $this->_url = $config->payment->credit->xmlurl;
        $this->_security_sender = $config->payment->credit->security_sender;
        $this->_user_login = $config->payment->credit->user_login;
        $this->_user_pwd = $config->payment->credit->user_pwd;
        $this->_transaction_channel = $config->payment->credit->transaction_channel;
        $this->_transaction_mode = IS_PRODUCTION ? "LIVE" : "CONNECTOR_TEST";
        
        // prevent shits
        if (IS_PRODUCTION && strpos($this->_url, "test") !== false) {
            new Yourdelivery_Exception("Try to use the CTPE XML Test API in a production environment");
        }
    }

    /**
     * Generate a valid xml request for heidelpay
     * @author vpriem
     * @since 15.11.2010
     * @param string $uniqueId
     * @param Yourdelivery_Model_Order $order
     * @return array
     */
    public function request($uniqueId, $order, $isNew = false) {

        // create document
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        // create Request
        $Request = $doc->createElement('Request');
        $Request->setAttribute('version', '1.0');
        $doc->appendChild($Request);

        // create Request Header
        $Header = $doc->createElement('Header');
        $Request->appendChild($Header);

        // create Request Header Security
        $Security = $doc->createElement('Security');
        $Security->setAttribute('sender', $this->_security_sender);
        $Header->appendChild($Security);

        // create Request Transaction
        $Transaction = $doc->createElement('Transaction');
        $Transaction->setAttribute('mode', $this->_transaction_mode);
        $Transaction->setAttribute('response', 'SYNC');
        $Transaction->setAttribute('channel', $this->_transaction_channel);
        $Request->appendChild($Transaction);

        // create Request Transaction User
        $User = $doc->createElement('User');
        $User->setAttribute('login', $this->_user_login);
        $User->setAttribute('pwd', $this->_user_pwd);
        $Transaction->appendChild($User);

        // create Request Transaction Identification
        $Identification = $doc->createElement('Identification');
        $Transaction->appendChild($Identification);

        // create Request Transaction Identification TransactionID
        $TransactionID = $doc->createElement('TransactionID');
        $TransactionID->appendChild($doc->createTextNode($order->getId() . "-" . time()));
        $Identification->appendChild($TransactionID);

        // create Request Transaction Payment
        $Payment = $doc->createElement('Payment');
        $Payment->setAttribute('code', 'CC.DB');
        $Transaction->appendChild($Payment);

        // create Request Transaction Payment Presentation
        $Presentation = $doc->createElement('Presentation');
        $Payment->appendChild($Presentation);

        // create Request Transaction Payment Presentation Amount
        $Amount = $doc->createElement('Amount');
        $Amount->appendChild($doc->createTextNode(inttoprice($order->getAbsTotal(), 2, '.')));
        $Presentation->appendChild($Amount);

        // create Request Transaction Payment Presentation Currency
        $Currency = $doc->createElement('Currency');
        $Currency->appendChild($doc->createTextNode($this->getCurrencyCode()));
        $Presentation->appendChild($Currency);

        // create Request Transaction Payment Presentation Currency
        $Usage = $doc->createElement('Usage');
        $Usage->appendChild($doc->createTextNode(__("Bestellung-Nr. %s", $order->getId())));
        $Presentation->appendChild($Usage);

        // create Request Transaction Account
        $Account = $doc->createElement('Account');
        $Account->setAttribute('registration', $uniqueId);
        $Transaction->appendChild($Account);
        
        
        $Frontend = $doc->createElement("Frontend");
        $ResponseUrl = $doc->createElement('ResponseUrl');
        $ResponseUrl->appendChild($doc->createTextNode("http://" . HOSTNAME . "/payment_heidelpay/finalize/secret/" . sha1($order->getNr() . SALT) . ($isNew ? "/uniqueId/" . $uniqueId : "")));
        $Frontend->appendChild($ResponseUrl);
        $Transaction->appendChild($Frontend);
        
        // get xml document resp
        $doc = $this->_process($order, $doc);

        // get results
        $Result = $this->_getNodeValue($doc, 'Result');
        $Status = $this->_getNodeValue($doc, 'Status');
        $Reason = $this->_getNodeValue($doc, 'Reason');
        $Return = $this->_getNodeValue($doc, 'Return', 'code');
        $Brand  = $this->_getNodeValue($doc, 'Brand');
        $Number = $this->_getNodeValue($doc, 'Number');
        $RedirectUrl = $this->_getNodeValue($doc, "Redirect", "url"); 
        $RedirectParams  =$this->_getRedirectParams($doc);
        
        return compact('Result', 'Status', 'Reason', 'Return', 'Brand', 'Number', 'RedirectUrl', 'RedirectParams');
    }

    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param int $orderId 
     */
    public function finalize($response) {
        
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($response);
        
        // todo check failures
        $TransactionID = $this->_getNodeValue($doc, "TransactionID");                       
        $Result = $this->_getNodeValue($doc, "Result");
        $Status = $this->_getNodeValue($doc, 'Status');
        $Reason = $this->_getNodeValue($doc, 'Reason');
        $Return = $this->_getNodeValue($doc, 'Return', 'code');
        
        return compact('Result', 'TransactionID', 'Status', 'Reason', 'Return');
    }
    
    /**
     * @author vpriem
     * @since 14.11.2011
     * @param DOMDocument $doc
     * @param string $nodeName
     * @param string $attrName
     * @return string 
     */
    private function _getNodeValue(DOMDocument $doc, $nodeName, $attrName = null) {
        
        $nodes = $doc->getElementsByTagName($nodeName);
        for ($i = 0; $i < $nodes->length; $i++) {
            if ($attrName !== null) {
                return $nodes->item($i)->getAttribute($attrName);
            }
            return $nodes->item($i)->nodeValue;
        }
        
        return "";
    }
    
    /**
     * 
     * Get Values for Redirect Post Params
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param DOMDocument $doc
     * @return array
     */
    private function _getRedirectParams(DOMDocument $doc) {
        
        $return = array();
        
        $xpath = new DOMXPath($doc);
        $nodes = $xpath->query("//Redirect/Parameter");
        for ($i = 0; $i < $nodes->length; $i++) {
            $return[$nodes->item($i)->getAttribute("name")] = str_replace(" ", "+", $nodes->item($i)->nodeValue);                        
        }
        
        return $return;
                      
    }
    
    /**
     * @author vpriem
     * @since 03.11.2011
     * @throws Yourdelivery_Payment_Heidelpay_Exception
     * @param string $xml
     * @return DOMDocument 
     */
    private function _process(Yourdelivery_Model_Order $order, DOMDocument $doc) {
        
        // get xml
        $xml = $doc->saveXML();
        
        // send request
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->_url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, "php ctpepost");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "load=" . urlencode($xml));
        $curlExec = curl_exec($curl);
        $curlError = curl_error($curl);
        curl_close($curl);

        // log
        $dbTable = new Yourdelivery_Model_DbTable_Heidelpay_Xml_Transactions();
        $dbRow = $dbTable->createRow(array(
            'orderId' => $order->getId(),
            'params' => $xml,
            'response' => $curlExec === false ? $curlError : urldecode($curlExec),
        ))->save();

        if ($curlExec === false) {
            throw new Yourdelivery_Payment_Heidelpay_Exception($curlError);
        }
        
        // parse response
        $doc = new DOMDocument();
        if ($doc->loadXML(urldecode($curlExec))) {
            return $doc;
        }
        
        throw new Yourdelivery_Payment_Heidelpay_Exception("Could not parse XML response: " . $curlExec);
    }
}
