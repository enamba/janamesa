<?php
/**
 * Description of RequestBase
 * @package core
 * @subpackage controller
 * @author mlaug
 */
class Default_Controller_RequestBase extends Default_Controller_Base{

    public function preDispatch() {

        if ( $_SERVER['HTTP_USER_AGENT'] === 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)' ){
            die();
        }
        
        parent::preDispatch();

        if (APPLICATION_ENV == "testing") {
            return true;
        }

        // check for secure cookie
        $secure = Yourdelivery_Security_Request::getInstance();
        if (!$secure->verifyPassword()) {

            $error = sprintf("Could not verify cookie for %s Action of %s Controller<br /><br />Info: %s",
                            $this->_request->getActionName(),
                            $this->_request->getControllerName(),
                            $_SERVER['HTTP_USER_AGENT']);

            if (APPLICATION_ENV == "production") {
                // Yourdelivery_Sender_Email::error($error, true);
                //$this->logger->err($error);
                die();
            }
            
            // in devel just kill him
            die($error);
        }

        // check for secure header
        if ($this->getRequest()->getHeader('YourdeliveryDoormen') != "kjfsdkjhdfsalkhjfdsalkhjfdas") {

            $error = sprintf("Could not verify header for %s Action of %s Controller<br /><br />Info: %s",
                            $this->_request->getActionName(),
                            $this->_request->getControllerName(),
                            $_SERVER['HTTP_USER_AGENT']);

            if (APPLICATION_ENV == "production") {
                // Yourdelivery_Sender_Email::error($error, true);
                //$this->logger->err($error);
                die();
            }
            
            // in devel just kill him
            die($error);
        }
    }

    /**
     * init restaurant
     * @return Yourdelivery_Model_Servicetype_Restaurant
     */
    protected function initRestaurant(){

        return new Yourdelivery_Model_Servicetype_Restaurant($this->session->currentRestaurant->getId());
        
    }

    /**
     * Print a json object form an array
     * @author vpriem
     * @since 16.06.2011
     * @param $arr array
     * @return void
     */
    protected function _json(array $arr) {

        echo json_encode($arr);
        
    }
    
}
