<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 14.06.2012 
 */
class Administration_Request_BlacklistController extends Default_Controller_RequestAdministrationBase {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.06.2012 
     */
    public function keywordAction() {

        $request = $this->getRequest();
        $ip = $request->getParam('ip');
        $ip_newcustomer_discount = $request->getParam('ip_newcustomer_discount');
        $tel = $request->getParam('tel');
        $address = $request->getParam('address');
        $customer = $request->getParam('customer');
        $company = $request->getParam('company');
        $uuid = $request->getParam('uuid');
        $orderId = $request->getParam('orderId');
        
        $defaults = array(
            'matching' => Yourdelivery_Model_Support_Blacklist::MATCHING_EXACT,
        );
        if ($ip) {
            $defaults['type'] = Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_IP;
            $defaults['value'] = $ip;
        }
        else if ( $ip_newcustomer_discount ){
            $defaults['type'] = Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_IP_NEWCUSTOMER_DISCOUNT;
            $defaults['value'] = $ip_newcustomer_discount;
        }
        else if ($uuid) {
            $defaults['type'] = Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_UUID;
            $defaults['value'] = $uuid;
        }
        else if ($tel) {
            $defaults['type'] = Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_TEL;
            $defaults['value'] = $tel;
        }
        else if ($address) {
            $defaults['type'] = Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_ADDRESS;
            $defaults['value'] = $address;
        }
        else if ($customer) {
            $defaults['type'] = Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_CUSTOMER;
            $defaults['value'] = $customer;
        }
        else if ($company) {
            $defaults['type'] = Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_COMPANY;
            $defaults['value'] = $company;
        }
        
        if ($orderId) {
            $defaults['orderId'] = $orderId;
        }
        
        $form = new Yourdelivery_Form_Administration_Blacklist_Keyword();
        $this->view->form = $form->populate($defaults);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.06.2012
     */
    public function emailAction(){
        
        $request = $this->getRequest();
        $email = $request->getParam('email');
        $orderId = $request->getParam('orderId');
        
        $form = new Yourdelivery_Form_Administration_Blacklist_Email();
        $this->view->form = $form->populate(array(
            'bl_email' => $email, 
            'bl_orderId' => $orderId, 
            'bl_cancelorder' => 1));
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.06.2012 
     */
    public function matchingsAction() {
        
        $request = $this->getRequest();
        $id = $request->getParam('id');
        
        try {
            $value = new Yourdelivery_Model_Support_Blacklist_Values($id);
            $this->view->value = $value;
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }
    }
}
