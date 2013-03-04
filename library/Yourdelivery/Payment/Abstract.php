<?php
/**
 * Payment Abstract
 * @package payment
 * @subpackage abstract
 * @author vpriem
 * @since 24.03.2011
 */
abstract class Yourdelivery_Payment_Abstract {

    /**
     * The country code
     * @var string
     */
    protected $_country;
    
    /**
     * The currency code
     * @var string
     */
    protected $_currency;
    
    /**
     * The system logger
     * @var Yourdelivery_log
     */
    protected $_logger;
    
    /**
     * the sysem config
     * @var Zend_Config_Ini 
     */
    protected $_config;

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.03.2012
     * @return Yourdelivery_log 
     */
    public function getLogger() {
        
        if ($this->_logger === null) {            
            $this->_logger = Zend_Registry::get('logger');
        }
        return $this->_logger;
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.03.2012
     * @return Zend_Config_Ini
     */
    public function getConfig() {
        
        if ($this->_config === null) {         
            $this->_config = $config = Zend_Registry::get('configuration');
        }
        return $this->_config;
    }
    
    /**
     * Get country code
     * @author vpriem
     * @since 24.03.2011
     * @return string
     */
    public function getCountryCode(){

        if ($this->_country !== null) {
            return $this->_country;
        }

        // get hostname from configuration
        $config = Zend_Registry::get('configuration');
        $hostname = $config->domain->base;
        
        return $this->_country = strtoupper(substr($hostname, -2));

    }

    /**
     * Get currency code
     * @author vpriem
     * @since 07.06.2011
     * @return string
     */
    public function getCurrencyCode(){

        if ($this->_currency !== null) {
            return $this->_currency;
        }
        
        $country = $this->getCountryCode();
        
        switch ($country) {
            case "CH":
                return $this->_currency = "CHF";
                
            case "PL":
                return $this->_currency = "PLN";
                
            case "BR":
                return $this->_currency = "BRL";
                
            default:
                return $this->_currency = "EUR";
        }

    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.08.2012
     * @return array
     */
    public static function getPayments() {

        return array(
            'bar' => __("Barzahlung"),
            'paypal' => __("PayPal"),
            'credit' => __("Kreditkarte"),
            'ebanking' => __("Sofortüberweisung"),
            'debit' => __("Lastschrift"),
            'bill' => __("Rechnung"),
        );
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.08.2012
     * @return array
     */
    public static function getAdditions() {

        return array(
            'ec' => "Cartão de Débito (Aparelho em domicílio)",
            'creditCardAtHome' => "Cartão de Crédito (Aparelho em domicílio)",
            'vr' => __("Vale Refeição"),
            'cheque' => __("Cheque"),
            'ticketRestaurant' => __("Ticket Restaurante"),
        );
    }

}
