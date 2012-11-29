<?php
/**
 * Optivo Soap API
 * @author vpriem
 * @since 20.06.2011
 */
class Yourdelivery_Api_Optivo_Abstract {
    
    /**
     * Soap client
     * @var Zend_Soap_Client
     */
    private $_soap;
    
    /**
     * Url
     * @var string
     */
    private $_url = "URL";
    
    /**
     * Ssl
     * @var boolean
     */
    private $_ssl = true;
    
    /**
     * Service name
     * @var string
     */
    protected $_serviceName;
    
    /**
     * @author vpriem
     * @since 20.06.2011
     * @return mixed
     */
    protected function _call(){
        
        // get arguments
        $args = func_get_args();
        $method = array_shift($args);
        
        //
        if ($this->_soap === null) {
            $location = "http" . ($this->_ssl ? "s" : "") . "://" . $this->_url . $this->_serviceName;
            $this->_soap = new Zend_Soap_Client();
            $this->_soap
                ->setSoapVersion(SOAP_1_1)
                ->setLocation($location)
                ->setUri($location);
        }
        
        // call
        return $this->_soap->__call($method, $args);
    }
    
    /**
     * @author vpriem
     * @since 22.06.2011
     * @param array|string $value
     * @return array|SoapVar
     */
    protected function _long($value) {
        
        if (is_array($value)) {
             foreach ($value as $k => $v) {
                $value[$k] = $this->_long($v);
             }
        }
        else {
            $value = new SoapVar($value, XSD_STRING, "xsd:long", "http://www.w3.org/2001/XMLSchema");
        }
        
        return $value;
    }
    
    /**
     * @author vpriem
     * @since 22.06.2011
     * @param string $value
     * @return SoapVar
     */
    protected function _byte($value) {
        
        return new SoapVar(base64_encode($value), XSD_STRING, "xsd:base64Binary", "http://www.w3.org/2001/XMLSchema");
    }
}
