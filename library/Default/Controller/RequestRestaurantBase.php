<?php

/**
 * Description of Default_Controller_RequestRestaurantBase
 * @package core
 * @subpackage controller
 * @author alex
 * @since 10.11.2010
 */
class Default_Controller_RequestRestaurantBase extends Default_Controller_RequestBase{

    public function preDispatch() {

        if (APPLICATION_ENV == "testing") {
            return;
        }

        // check if user logged in
        if ( is_null($this->session_restaurant) || (($this->session_restaurant->admin === null) && ($this->session_restaurant->masterAdmin === null)) ) {
            //throw new Yourdelivery_Exception_Insecure('Admin session has expired, administrator object has gone away :(');
            $this->getResponse()->setHttpResponseCode(501);
            $this->_disableView();
            $this->getRequest()->setDispatched(true);
            return;   
        }

        parent::preDispatch();

    }

}
