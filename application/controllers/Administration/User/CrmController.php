<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CrmController
 *
 * @author oliverknoblich
 */
require_once(APPLICATION_PATH . '/controllers/Administration/Crm/Abstract.php');
class Administration_User_CrmController extends CrmController_Abstract{
    
    public function init() {
        parent::init();
        $request = $this->getRequest();
        $customerId = (integer) $request->getParam('id');
        try {
            if ( $customerId <= 0 ){
                throw new Yourdelivery_Exception_Database_Inconsistency('');
            }
            $customer = new Yourdelivery_Model_Customer($customerId);
            $this->view->object = $this->object = $customer;
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->_redirect('/administration/users');
        }
    }
    
    public function getLink(){
        return "user";
    }
    
    public function callAction() {
        
    }

    public function taskAction() {
        
    }

}

?>
