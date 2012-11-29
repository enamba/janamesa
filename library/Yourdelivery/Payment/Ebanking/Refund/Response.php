<?php

/**
 * eBanking payment refund response
 * Build the request
 * @author Vincent Priem <priem@lieferando.de>
 * @since 18.01.2012
 */
class Yourdelivery_Payment_Ebanking_Refund_Response {
    
    /**
     * @var DOMDocument 
     */
    private $_doc;
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @throws Yourdelivery_Payment_Ebanking_Exception 
     * @param string $xml
     */
    public function __construct($xml) {
        
        $doc = new DOMDocument();
        if (!$doc->loadXML($xml)) {
            throw new Yourdelivery_Payment_Ebanking_Exception("Could not parse XML response: " . $xml);
        }
        
        $this->_doc = $doc;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @return string|boolean
     */
    public function getStatus() {
        
        return $this->_getNodeValue("status");
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @return string|boolean
     */
    public function getErrorCode() {
        
        return $this->_getNodeValue("code");
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @return string|boolean
     */
    public function getErrorMessage() {
        
        return $this->_getNodeValue("message");
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @param string $nodeName
     * @return string|boolean
     */
    private function _getNodeValue($nodeName) {
        
        $nodes = $this->_doc->getElementsByTagName($nodeName);
        for ($i = 0; $i < $nodes->length; $i++) {
            return $nodes->item($i)->nodeValue;
        }
        
        return false;
    }
    
}
