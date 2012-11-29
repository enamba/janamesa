<?php

/**
 * @package Yourdelivery
 * @subpackage SMS
 * @author mlaug
 * @since 02.11.2011
 */
class Yourdelivery_Sender_Sms_Kannel {
    
    /**
     * @var Zend_Config 
     */
    private $_config;
    
    /**
     * @var Zend_Log
     */
    private $_logger;
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 09.08.2011
     */
    public function __construct() {
        
        $this->_config = Zend_Registry::get('configuration');
        $this->_logger = Zend_Registry::get('logger');
    }
    
    /**
     * Send a message via a kannel server
     * @author Matthias Laug <laug@lieferando.de>
     * @since 02.11.2011
     * @param string $to
     * @param string $msg
     * @return boolean
     */
    public function sendSmsMessage($to, $msg) {
        
        IS_PRODUCTION ? null : $to = $this->_config->testing->sms;
        
        if (empty($to)) {
            $this->_logger->info("KANNEL: send to nobody");
            
            if (!IS_PRODUCTION) {
                return true;
            }
            
            return false;
        }
        
        // get kannel config
        // TODO: refector to sender->sms->kannel
        $config = $this->_config->sender->kannel->toArray();
        
        $url = '/cgi-bin/sendsms?username=' . $config['username']
            . '&password=' . $config['password']
            . '&charset=UCS-2&coding=2'
            . "&to={$to}"
            . "&from=015114534409"
            . '&text=' . urlencode(iconv('utf-8', 'ucs-2', $msg));

        $result = file_get_contents('http://' . $config['host'] . ':' . $config['port'] . $url);
        if ($result == '0: Accepted for delivery') {
            $this->_logger->info("KANNEL: successfully send to " . $to);
            return true;
        }
        
        $this->_logger->err(sprintf("KANNEL: cannot send to %s because: %s", $to, $result));
        return false;
    }

}