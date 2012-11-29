<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EditController
 *
 * @author matthiaslaug
 */
class Administration_Company_EditController extends Default_Controller_AdministrationBase {
    
    protected $company = null;
    
    public function init(){
        
        parent::init();
        
        $request = $this->getRequest();
        $companyId = (integer) $request->getParam('companyid');
        try {
            if ( $companyId <= 0 ){
                throw new Yourdelivery_Exception_Database_Inconsistency('no companyId provided');
            }
            $company = new Yourdelivery_Model_Company($companyId);
            $this->view->company = $this->company = $company;
            $this->view->assign('navcompanys', 'active');
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return $this->_redirect('/administration/users');
        }
    }
    
    /**
     * edit company
     */
    public function indexAction(){
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration/companys');
        }
        
        //test cityId
        if ( intval($this->company->getCityId())<=0) {
            $this->error(__b("Keine PLZ wurde für die Firma angegeben, bitte reden Sie mit der IT"));
            return $this->_redirect('/administration/companys');
        }

        if ( $request->isPost() ){
            $form = new Yourdelivery_Form_Administration_Company_Edit();
            $post = $request->getPost();
            if ( $form->isValid($post) ){
                $values = $form->getValues();
                        
                // if we are in Brasil, create city based on plz
                if (strpos($this->config->domain->base, "janamesa") !== false) {      
                    $cityByPLz = Yourdelivery_Model_City::getByPlz($values['plz']);
                    // we take the first one, beacuse we have only one city entry per plz in Brazil
                    $c = $cityByPLz[0];
                    
                    if (is_null($c)) {
                        $this->error(__b("Diese PLZ existiert nicht!"));
                        $this->_redirect('/administration_company_edit/index/companyid/' . $this->company->getId());
                    }
                    
                    $values['cityId'] = $c['id'];
                }
                else {
                    $city = new Yourdelivery_Model_City($values['cityId']);
                    $values['plz'] = $city->getPlz();                    
                }
                
                //save new company data
                $this->company->setData($values);
                $this->company->save();
                $this->logger->adminInfo(sprintf("Company #%d was edited", $this->company->getId()));
                $this->success(__b("Changes successfully saved"));
                return $this->_redirect('/administration_company_edit/index/companyid/' . $this->company->getId());
            }
            else{
                $this->error($form->getMessages());
            }
        }

        if (!is_null($this->company->getContactId())) {
            try {
                $contact = new Yourdelivery_Model_Contact($this->company->getContactId());
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                $contact = null;
            }
        }

        if (!is_null($this->company->getBillingContactId())) {
            try {
                $billingContact = new Yourdelivery_Model_Contact($this->company->getBillingContactId());
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                $billingContact = null;
            }
        }

        $restTable = new Yourdelivery_Model_DbTable_Restaurant();
        $this->view->assign('restIds', $restTable->getDistinctNameId());

        $contTable = new Yourdelivery_Model_DbTable_Contact();
        $this->view->assign('contacts', $contTable->getDistinctNameId());

        $rabattTable = new Yourdelivery_Model_DbTable_RabattCodes();
        $this->view->assign('rabattIds', $rabattTable->getOnlyCompanyIds());

        $this->view->assign('contact', $contact);
        $this->view->assign('billingContact', $billingContact);  
    }

    /**
     * edit company contacts
     * @author alex
     * @since 13.01.2010
     */
    public function contactsAction(){
        $request = $this->getRequest();
     
        if (!is_null($this->company->getContactId())) {
            try {
                $contact = new Yourdelivery_Model_Contact($this->company->getContactId());
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                $contact = null;
            }
        }

        if (!is_null($this->company->getBillingContactId())) {
            try {
                $billingContact = new Yourdelivery_Model_Contact($this->company->getBillingContactId());
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                $billingContact = null;
            }
        }

        $contTable = new Yourdelivery_Model_DbTable_Contact();
        $this->view->assign('contacts', $contTable->getDistinctNameId());

        $this->view->assign('contact', $contact);
        $this->view->assign('billingContact', $billingContact);       
    }

    /**
     * edit company associations
     * @author alex
     * @since 13.01.2010
     */
    public function assocAction(){
        $request = $this->getRequest();

        $restTable = new Yourdelivery_Model_DbTable_Restaurant();
        $this->view->assign('restIds', $restTable->getDistinctNameId());

        $rabattTable = new Yourdelivery_Model_DbTable_RabattCodes();
        $this->view->assign('rabattIds', $rabattTable->getOnlyCompanyIds());
    }
    
    /**
     * @author mlaug
     * @since 07.08.2010
     */
    public function billingAction(){
        $request = $this->getRequest();

        if ( $this->getRequest()->isPost() ){
            $data = $this->getRequest()->getPost();
            $form = new Yourdelivery_Form_Administration_Billing_Customized();
            if ( $form->isValid($data) ){
                $cleanData = $form->getValues();
                $this->company->getBillingCustomized()
                        ->setData($cleanData)
                        ->save();
                $this->logger->adminInfo(sprintf("Data for billing of company #%d edited", $this->company->getId()));

                $this->success(__b("Data successfully saved"));
            }
            else{
                $this->error($form->getMessage());
            }
        }

        $this->view->customized = $this->company->getBillingCustomizedData();
    }/**
     * edit company contact or billing contact
     */
     public function contactAction() {
        $request = $this->getRequest();

        if ( $request->isPost() ){
            $post = $request->getPost();

            // remove billing contact
            if ($request->getParam('removeBillingContact', false)) {
                $this->company->setBillingContactId('0');
                $this->company->save();
                $this->logger->adminInfo(sprintf("Billing contact for company #%d was deleted", $this->company->getId()));
                $this->success(__b("Billing contact successfully removed. Contact person now is billing contact too"));
            }
            //new contact was selected from drop-down menu
            else if ( $request->getParam('selContactId') > 0) {
                if ($request->getParam('selContactId') != $this->company->getContactId()) {
                    $this->company->setContactId($request->getParam('selContactId'));
                    $this->company->save();
                    $this->logger->adminInfo(sprintf("Set new contact for company #%d", $this->company->getId()));
                    $this->success(__b("New contact was set"));
                }
                else {
                    $this->success(__b("Contact is the same. No changes."));
                }
            }
            //new billing contact was selected from drop-down menu
            else if ($request->getParam('selBillingContactId') > 0 ) {
                if ($request->getParam('selBillingContactId') != $this->company->getBillingContactId()) {
                    $this->company->setBillingContactId($request->getParam('selBillingContactId'));
                    $this->company->save();
                    $this->logger->adminInfo(sprintf("Set new billing contact for company #%d", $this->company->getId()));
                    $this->success(__b("New Billing Contact was set"));
                }
                else {
                    $this->success(__b("Billing contact is the same. No changes."));
                }
            }
            //create new contact
            else {
                $contact = new Yourdelivery_Model_Contact();

                $form = new Yourdelivery_Form_Administration_Company_ContactEdit();

                $id = Yourdelivery_Model_Contact::getByEmail($values['email']);
                if (strlen($values['email'])>0 && !is_null($id) && ($id!=0)) {
                    $this->error(__b("There is already a contact with this email. id: ") . $id);
                    $this->logger->adminInfo(sprintf("contact could not be created for company #%d - email already in use", $this->company->getId()));
                    return $this->_redirect('/administration_company_edit/contacts/companyid/' . $this->company->getId());
                }
                
                if ( $form->isValid($post) ){
                    $values = $form->getValues();
                    
                    $id = Yourdelivery_Model_Contact::getByEmail($values['email']);
                    if (strlen($values['email'])>0 && !is_null($id) && ($id!=0)) {
                        $this->logger->adminInfo(sprintf("contact could not be created for company #%d - email already in use", $this->company->getId()));
                        $this->error(__b("Unter dieser E-mail Adresse ist bereits ein Kontakt registriert. id: ") . $id);
                        return $this->_redirect('/administration_company_edit/contacts/companyid/' . $this->company->getId());
                    }

                    $city = new Yourdelivery_Model_City($values['cityId']);
                    $values['plz'] = $city->getPlz();

                    //edit contact object
                    $contact->setData($values);
                    $contact->save();

                    // we are assigning new billing contact
                    if ( $request->getParam('saveBillingContact', false)  ) {
                        $this->company->setBillingContact($contact);
                        $this->company->save();
                        $this->logger->adminInfo(sprintf("New billing contact was created for company #%d", $this->company->getId()));
                        $this->success(__b("New billing contact was created"));
                    }
                    // we are assigning new contact
                    else {
                        $this->company->setContact($contact);
                        $this->company->save();
                        $this->logger->adminInfo(sprintf("New contact was created for company #%d", $this->company->getId()));
                        $this->success(__b("New contact was created"));
                    }
                }
                else{
                    $this->error($form->getMessages());
                }
            }
        }
        return $this->_redirect('/administration_company_edit/contacts/companyid/' . $this->company->getId() . $link);
    }

    /**
     * add restaurant association
     */
    public function addrestaurantAction() {
        $request = $this->getRequest();
        $restId = $this->getRequest()->getParam('restaurantId');
        $exclusive = $this->getRequest()->getParam('exclusive');

        if(!is_null($restId)) {
            $this->company->setRestaurantRestriction($restId,$exclusive);
            $this->logger->adminInfo(sprintf("Association of restaurant #%d and company #%d created", $restId, $this->company->getId()));
            $this->success(__b("Association to restaurant was saved"));
        }
        return $this->_redirect('/administration_company_edit/assoc/companyid/' . $this->company->getId());
    }

    /**
     * remove restaurant association
     */
    public function removerestaurantAction() {
        $request = $this->getRequest();
        $restId = $this->getRequest()->getParam('restaurantId');

        if(!is_null($restId)) {
            $this->company->removeRestaurantRestriction($restId);
            $this->logger->adminInfo(sprintf("Association of restaurant #%d and company #%d deleted", $restId, $this->company->getId()));
            $this->success(__b("Association to restaurant was deleted"));
        }
        return $this->_redirect('/administration_company_edit/assoc/companyid/' . $this->company->getId());
    }

    /**
     * add permanent discount to all employeers of the company
     */
    public function adddiscountAction() {
        $request = $this->getRequest();

        if ( $request->isPost() ){
            $post = $request->getPost();

            $discountCodeId = $post['discountId'];

            if(!is_null($discountCodeId)) {

                //create discount object
                try {
                    $rabatt = new Yourdelivery_Model_Rabatt_Code(null, $discountCodeId);

                    $count = 0;
                    foreach ($this->company->getEmployees() as $employeer) {
                        $t = $employeer;

                        //create customer object
                        try {
                            //$customer = new Yourdelivery_Model_Customer(1123);

                            //add discount to the customer
                            if (!$employeer->setDiscount($rabatt)) {
                                $this->error(__b("Discount code could not associated to customer ") . $customer->getPrename() . ' ' . $customer->getName() . '(#' . $customer->getId());
                            }

                        }
                        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                            $this->error(__b("Customer %s %s (#%s) is non-existant", $customer->getPrename(), $customer->getName(), $customer->getId()));
                        }
                        
                        $count++;
                    }

                    if ($count == 0) {
                        $this->success(__b("Company doesn't have employees. Discount code could not be associated to anyone"));
                    }
                    else {
                        $this->success(__b("Discount code %s was successfully associated to %s employees", $rabatt->getName(), $count));
                        $this->logger->adminInfo(sprintf("Discount code %s (#%d) was assigned to %d employeers of company #%d",
                                $rabatt->getName(), $rabatt->getId(), $count, $this->company->getId()));
                    }
                }
                catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                    $this->error(__b("This discount code is non-existant"));
                }

            }
        }

        return $this->_redirect('/administration_company_edit/assoc/companyid/' . $this->company->getId());
    }
    
    /**
     * manage crm tickets
     * @author alex
     * @since 12.07.2011
     */
    public function crmAction() {
        // get grid and deploy it to view
        $this->view->grid = Yourdelivery_Model_Crm_Ticket::getGrid('company', $this->company->getId());        
    }
    
}
