<?php

/**
 * @package Controller
 * @subpackage User
 */

/**
 * @author mlaug
 * @since 21.10.2011
 */
class User_OpenidController extends Default_Controller_Base {

    /**
     * callback for ajax request after retrival of facebook 
     * information with regisration
     * @author mlaug
     * @since 21.10.2011
     */
    public function connectAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        //if customer is already logged in we just redirect to start page
        if ( $this->getCustomer()->isLoggedIn() ){
            $this->getResponse()->setHttpResponseCode(302);
            return $this->_redirect('/order_private/start');
        }
        
        try{
            $facebook = new Yourdelivery_Connect_Facebook();
            $facebookId = $facebook->getUser();
        }catch(Yourdelivery_Exception_NoConnection $e){
            $this->logger->error($e->getMessage());
            return $this->_redirect('/');
        }
        
        
        //user is no logged in via php sdk, so lets call this page again
        if (!$facebookId) {
            $loginUrl = $facebook->getLoginUrl();
            $this->logger->info(sprintf('redirecting user to %s for final login', $loginUrl));
            return $this->_redirect($loginUrl);
        }
        //user is logged in and authenticated, try to find in customer table
        else {
            try{
                $customer = $facebook->getYourdeliveryUser();
            }catch(FacebookApiException $e){
                $this->logger->error($e->getMessage());
                return $this->_redirect('/');
            }      
            
            if(is_null($customer)){
                $this->logger->error('could not login facebookuser');
                return $this->_redirect('/user/logout');
            }
            //set our cookies and log in user
            $customer->login();
            $this->session->customerId = $customer->getId();
            return $this->_redirect('/order_private/start');
        }
    }

}

