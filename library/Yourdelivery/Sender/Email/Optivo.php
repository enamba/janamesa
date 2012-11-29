<?php

/**
 * @author Vincent Priem <priem@lieferando.de> 
 * @since 2012.04.11 
 */
class Yourdelivery_Sender_Email_Optivo {

    /**
     * The configuration
     * @var Zend_Config
     */
    protected $_config;
    
    /**
     * @var array 
     */
    protected $_data = array();
    
    /**
     * @var array 
     */
    protected $_calls = array();

    /**
     * @author Vincent Priem <priem@lieferando.de> 
     * @since 2012.04.11 
     */
    public function __construct() {
        
        $this->_config = Zend_Registry::get('configuration');
        
        $this->_calls = array(
            'RATING_NO_COMMENT' => array(
                'lieferando.de' => "https://api.broadmail.de/http/form/     /sendtransactionmail?bmRecipientId=x&bmMailingId=    &UserPrename=x&LastOrderServiceName=x",
                'lieferando.at' => "https://api.broadmail.de/http/form/    /sendtransactionmail?bmRecipientId=x&bmMailingId=    &UserPrename=x&LastOrderServiceName=x",
                'pyszne.pl' => "https://api.broadmail.de/http/form/     /sendtransactionmail?bmRecipientId=x&bmMailingId=     &UserPrename=x&LastOrderServiceName=x"
            ),
            'RATING_LONG_DELIVERTIME' => array(
                'lieferando.de' => "https://api.broadmail.de/http/form/     /sendtransactionmail?bmRecipientId=x&bmMailingId=     &UserPrename=x&LastOrderServiceName=x",
                'lieferando.at' => "https://api.broadmail.de/http/form/    /sendtransactionmail?bmRecipientId=x&bmMailingId=     &UserPrename=x&LastOrderServiceName=x",
                'pyszne.pl' => "https://api.broadmail.de/http/form/     /sendtransactionmail?bmRecipientId=x&bmMailingId=    &UserPrename=x&LastOrderServiceName=x"
            ),
            'RATING_NO_DELIVERY' => array(
                'lieferando.de' => "https://api.broadmail.de/http/form/      /sendtransactionmail?bmRecipientId=x&bmMailingId=    &UserPrename=x&LastOrderServiceName=x",
                'lieferando.at' => "https://api.broadmail.de/http/form/     /sendtransactionmail?bmRecipientId=x&bmMailingId=    &UserPrename=x&LastOrderServiceName=x",
                'pyszne.pl' => "https://api.broadmail.de/http/form/     /sendtransactionmail?bmRecipientId=x&bmMailingId=    &UserPrename=x&LastOrderServiceName=x"
            ),
            'RATING_BAD_FOOD' => array(
                'lieferando.de' => "https://api.broadmail.de/http/form/     /sendtransactionmail?bmRecipientId=x&bmMailingId=      &UserPrename=x&LastOrderServiceName=x",
                'lieferando.at' => "https://api.broadmail.de/http/form/     /sendtransactionmail?bmRecipientId=x&bmMailingId=           &UserPrename=x&LastOrderServiceName=x",
                'pyszne.pl' => "https://api.broadmail.de/http/form/      /sendtransactionmail?bmRecipientId=x&bmMailingId=          &UserPrename=x&LastOrderServiceName=x"
            ),
            'RATING_SORRY' => array(
                'lieferando.de' => "https://api.broadmail.de/http/form/      /sendtransactionmail?bmRecipientId=x&bmMailingId=         &UserPrename=x&LastOrderServiceName=x",
                'lieferando.at' => "https://api.broadmail.de/http/form/       /sendtransactionmail?bmRecipientId=x&bmMailingId=            &UserPrename=x&LastOrderServiceName=x",
                'pyszne.pl' => "https://api.broadmail.de/http/form/     /sendtransactionmail?bmRecipientId=x&bmMailingId=           &UserPrename=x&LastOrderServiceName=x"
            ),
            'HANNOVER_AKTION' => array(
                'lieferando.de' => "https://api.broadmail.de/http/form/       /sendtransactionmail?bmRecipientId=x&bmMailingId=           &UserPrename=x&LastOrderServiceName=x"
            ),
            'WELCOME_MAILS' => array(
                 'lieferando.de' => "https://api.broadmail.de/http/form/     /sendtransactionmail?bmRecipientId=x&bmMailingId=            x&UserPrename=x&LastOrderServiceName=x"
            )
        );
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de> 
     * @since 2012.04.11 
     * @param string $method
     * @param array $args
     * @return Yourdelivery_Sender_Email_Optivo|mixed
     */
    public function __call($method, $args) {

        $prefix = substr($method, 0, 3);
        $key = substr($method, 3);
        
        switch (substr($prefix, 0, 3)) {
            case "get":
                return $this->_data[$key];
                break;
            
            case "set":
                if (!IS_PRODUCTION && $key == "bmRecipientId") {
                    $args[0] = $this->_config->testing->email;
                }
                
                $this->_data[$key] = $args[0];
                return $this;
                break;
        }
    }

    /**
     * @author Vincent Priem <priem@lieferando.de> 
     * @since 2012.04.11
     * @throws Yourdelivery_Sender_Email_Optivo_Exception
     * @param string $call
     * @param string $domain
     * @return string
     */
    public function getUrl($call, $domain = null) {
        
        if ($domain === null) {
            $domain = $this->_config->domain->base;
        }
        
        if (!array_key_exists($call, $this->_calls)) {
            throw new Yourdelivery_Sender_Email_Optivo_Exception("Unknow transaction call $call");
        }
        
        if (!array_key_exists($domain, $this->_calls[$call])) {
            throw new Yourdelivery_Sender_Email_Optivo_Exception("Unknow transaction call $call for domain $domain");
        }
        
        $url = $this->_calls[$call][$domain];
        $url = @parse_url($url);
        if ($url === false) {
            throw new Yourdelivery_Sender_Email_Optivo_Exception("Cannot parse transaction call $call for domain $domain");
        }
        
        if (isset($url['query'])) {
            parse_str($url['query'], $url['query']);
            $url['query'] = array_merge($url['query'], $this->_data);
            $url['query'] = http_build_query($url['query'], null, "&");
        }
        
        return $url['scheme'] . "://" . $url['host'] . $url['path'] . "?" . $url['query'];
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de> 
     * @since 2012.04.11 
     * @throws Yourdelivery_Sender_Email_Optivo_Exception
     * @param string $call
     * @param string $domain
     * @return boolean 
     */
    public function send($call, $domain = null) {
        
        $url = $this->getUrl($call, $domain);                
        $result = @file_get_contents($url);
        if ($result !== false) {
            return strpos($result, 'enqueued') !== false;
        }
        
        throw new Yourdelivery_Sender_Email_Optivo_Exception("Cannot reach transaction call $call for domain $domain");
    }
}