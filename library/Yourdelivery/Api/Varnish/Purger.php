<?php

/**
 * @author Matthias Laug 
 */
class Yourdelivery_Api_Varnish_Purger {

    /**
     * @var array 
     */
    protected $_purgeUrls = array();

    /**
     * @var Zend_Config
     */
    protected $_config = null;
    
    /**
     * @var Zend_Log 
     */
    protected $_logger = null;
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012
     */
    public function __construct() {
        $this->_config = Zend_Registry::get('configuration');
        $this->_logger = Zend_Registry::get('logger');
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012
     * @param string $url 
     */
    public function addUrl($url = '') {
        
        // check for http at the beginning
        if (!preg_match('/^http/', $url)) {
            $prepend = 'http://';
            
            if (!strstr($url, $this->_config->domain->base)) {           
                if (IS_PRODUCTION) {
                    if ($this->_config->domain->www_redirect->enabled) {
                        $prepend .= 'www.' . $this->_config->domain->base;
                    }
                    else{
                        $prepend .= $this->_config->domain->base;
                    }
                } else {
                    $prepend .= 'staging.' . $this->_config->domain->base;
                }
                
                // append / if needed
                if (!preg_match('/^\//', $url)) {
                    $url = '/' . $url;
                }
            }
            
        }     
        
        $this->_purgeUrls[] = $prepend . $url;
    }
    
    /**
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012
     * @return array 
     */
    public function getUrlList(){
        return $this->_purgeUrls;
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012
     */
    public function clearUrlList(){
        $this->_purgeUrls = array();
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012
     * @return boolean
     */
    public function executePurge() {
        $n = 0;
        $purgeUrls = array_unique($this->_purgeUrls);
        foreach ($purgeUrls as $url) {
            $this->purgeUrl($url) ? $n++ : null;
        }
        return $n > 0;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012
     */
    protected function purgeUrl($url) {
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'PURGE');
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_exec($c);
        curl_multi_getcontent($c);
        $code = (integer) curl_getinfo($c, CURLINFO_HTTP_CODE);
        curl_close($c);
        $this->_logger->info(sprintf('VARNISH: purged %s with response %s', $url, $code));
        return $code < 400;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.05.2012
     */
    public function purgePost($postId) {
        array_push($this->purgeUrls, get_permalink($postId));
    }

}

