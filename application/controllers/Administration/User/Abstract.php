<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Abstract
 *
 * @author matthiaslaug
 */
abstract class Administration_User_Abstract extends Default_Controller_AdministrationBase {

    protected $customer = null;
    
    //put your code here
    public function init() {
        parent::init();
        $request = $this->getRequest();
        $customerId = (integer) $request->getParam('userid');
        try {
            if ( $customerId <= 0 ){
                throw new Yourdelivery_Exception_Database_Inconsistency('');
            }
            $customer = new Yourdelivery_Model_Customer($customerId);
            $this->view->customer = $this->customer = $customer;
            $this->view->assign('navusers', 'active');
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->_redirect('/administration/users');
        }
    }
}

?>
