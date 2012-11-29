<?php

/**
 * Description of CustomerController
 *
 * @author mlaug
 */
class Administration_Request_Grid_CustomerController extends Default_Controller_RequestAdministrationBase {

    /**
     * create info box for grid to display verbose customer information
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.06.2012 
     */
    public function infoboxAction() {

        $request = $this->getRequest();
        $customerId = (integer) $request->getParam('customerId', 0);
        $email = $this->view->email = $request->getParam('email', null);      
        $this->view->orderId = $request->getParam('orderId');
        $this->view->name = $request->getParam('name', null);
        
        $customer = new Yourdelivery_Model_Customer_Anonym();
        //try to find according to customerId
        if ($customerId > 0) {
            try {
                $customer = new Yourdelivery_Model_Customer($customerId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }
        //try to find according to email address
        elseif (strlen($email) > 0) {
            try {
                $customer = new Yourdelivery_Model_Customer(null, $email);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }

        $this->view->customer = $customer;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.06.2012 
     */
    public function emailinfoAction() {
        
        $request = $this->getRequest();
        $email = $request->getParam('email');
        $orderId = $request->getParam('orderId');
        
        $anonym = new Yourdelivery_Model_Anonym();
        $anonym->setEmail($email);
        
        $this->view->email = $email;
        $this->view->orderId = $orderId;
        $this->view->status = $anonym->getNewsletter();
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.06.2012 
     */
    public function tooglenewsletterAction() {
        
        $this->_disableView();
        
        $request = $this->getRequest();
        $email = $request->getParam('email');
        $status = $request->getParam('status') == 'true';

        $anonym = new Yourdelivery_Model_Anonym();
        $anonym->setEmail($email);
        $anonym->setNewsletter($status, $status, 'changes made through backend');
        
        echo "OK";
    }

}
