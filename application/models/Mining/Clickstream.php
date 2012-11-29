<?php

/**
 * Description of Clickstream
 *
 * @author mlaug
 */
class Yourdelivery_Model_Mining_Clickstream extends Default_Model_Base {

    /**
     * leave a breadcrumb like Haensel in the forest and 
     * fuck the witch aha aha aha :) just get all the informationen needed
     * to check where everybode came from
     * @author mlaug
     * @since 07.01.2011
     * @param string $referer
     * @param string $path
     * @param Yourdelivery_Model_Order_Abstract $order
     * @param Yourdelivery_Model_Customer_Abstract $customer 
     */
    public static function leaveBreadcrumb($referer = null, $path = '', $order = null, $customer = null, array $data = array()) {
        
        //build up default values
        $clickstreamState = array();
        
        //set path
        $clickstreamState['path'] = $path;
        if ( empty($path) ){
            $clickstreamState['path'] = 'unknown';
        }
        
        //check for referer if not given
        $clickstreamState['referer'] = $referer;
        if ( $referer === null ){
            $clickstreamState['referer'] = Default_Helpers_Web::getReferer();
        }
        
        //set some order defaults, if order object has been provided
        if ( is_object($order) ){
            $clickstreamState['ident'] = $order->getSecret();
            $clickstreamState['orderMode'] = $order->getMode();
        }
        
        //set some customer defaults, if customer object has been provided
        if ( is_object($customer) && $customer->isLoggedIn() ){
            $clickstreamState['customerId'] = $customer->getId();
        }
        
        //set a cookie and collect the entire clickstream
        $currentCookieState = Default_Helpers_Web::getCookie('clickstream');
        if ( is_array($currentCookieState) ){
            foreach($currentCookieState as $state){
                /**
                 * @todo: create clickstreams from cookie and reset cookie
                 * this should be impltemented in the jsRefactor branch
                 */
            }
            Default_Helpers_Web::setCookie('clickstream',null);
        }
        
        //append data given
        $clickstreamState['data'] = serialize($data);
        
        //store clickstream in database
        $clickstream = new Yourdelivery_Model_DbTable_Mining_Clickstream();
        $clickstream->createRow($clickstreamState)
                    ->save();
    }

    //put your code here
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Mining_Clickstream();
        }
        return $this->_table;
    }

}

?>
