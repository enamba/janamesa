<?php

/**
 * companys management
 * @package controller
 * @subpackage company
 */

class Administration_CompanyController extends Default_Controller_AdministrationBase {

    public function indexAction(){
        $this->_redirect('/administration_company/create');
    }

    /**
     * create new company
     */
    public function createAction(){
        $this->view->assign('navcompanys', 'active');
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration/companys');
        }
        
        if ( $request->isPost() ){
            $post = $request->getPost();
            
            // if we are in Brasil, create city based on plz
            if (strpos($this->config->domain->base, "janamesa") !== false) {
                $cityByPLz = Yourdelivery_Model_City::getByPlz($post['plz']);
                // we take the first one, beacuse we have only one city entry per plz in Brazil
                $c = $cityByPLz[0];

                if (is_null($c)) {
                    $this->error(__b("Diese PLZ existiert nicht!"));
                    $this->_redirect('/administration_company/create');
                }

                $post['cityId'] = $c['id'];
            }            
            
            $form = new Yourdelivery_Form_Administration_Company_Create();
            if ( $form->isValid($post) ){
                $values = $form->getValues();
                
                //create company object                 
                try{
                    $city = new Yourdelivery_Model_City($values['cityId']);
                    $values['plz'] = $city->getPlz();

                    $company = new Yourdelivery_Model_Company();
                    $company->setData($values);
                    $company->setStatus(1);
                    $company->save();
                }
                catch ( Exception $e ){                    
                    $this->error($e->getMessage());
                    return;
                }
                
                $custNr = Yourdelivery_Model_DbTable_Company::getActualCustNr() + 1;
                if ($custNr == 1) {
                    $config = Zend_Registry::get('configuration');
                    $custNr = $config->customerNr->company->initialval;
                }

                $company->setCustomerNr($custNr);
                $company->save();
                
                if (strlen($values['contact_cityId'])>0) {
                    try{
                        $contactCity = new Yourdelivery_Model_City($values['contact_cityId']);

                        //create contact address as first company address
                        $location = new Yourdelivery_Model_Location();
                        $location->setData(array(
                            'street'    => $values['contact_street'],
                            'hausnr'    => $values['contact_hausnr'],
                            'plz'       => $contactCity->getPlz(),
                            'cityId'    => $contactCity->getId(),
                            'comment'   => $values['contact_comment']
                        ));
                        $location->setCompany($company);
                        $location->save();
                    }
                    catch ( Exception $e ){
                        $this->error($e->getMessage());
                    }
                }

                //create basic budget
                $budget = new Yourdelivery_Model_Budget();
                $budget->setName('Ansprechpersonen');
                $budget->setCompany($company);
                $budget->setStatus(0);
                $budget->save();
                //append budget to company and location
                $company->addBudget($budget, $location);

                $contactId = $values['selContactId'];

                if ($contactId == -1){
                    if ( strlen(trim($values['contact_email']))>0 ) {
                        //create contact person
                        $contact = array(
                            'name'      => $form->getValue('contact_name'),
                            'prename'   => $form->getValue('contact_prename'),
                            'position'  => $form->getValue('contact_position'),
                            'email'     => $form->getValue('contact_email'),
                            'tel'       => $form->getValue('contact_tel'),
                            'fax'       => $form->getValue('contact_fax')
                        );
                        if (!is_null($location)) {
                            $contact = array_merge($contact, $location->getData());
                        }
                         
                        $company_contact = new Yourdelivery_Model_Contact();
                        $company_contact->setData($contact);
                        $company_contact->save();
                    }
                    else {
                        $this->error(__b("No email given. Contact could NOT be created"));
                    }
                }
                else {
                    //TODO: catch invalid $contactId                   
                    $company_contact = new Yourdelivery_Model_Contact($contactId);
                }
                //append contact to company
                $company->setContact($company_contact);
                $company->save();

                if ( $form->getValue('bill_as_contact') == 1) {
                    if (!is_null($company_contact)) {
                        $company->setBillingContact($company_contact);
                        $company->save();
                    }
                }
                else{
                    $billingContactId = $values['selBillingContactId'];

                    if ($billingContactId == -1){
                        $billcontactCity = new Yourdelivery_Model_City($form->getValue('bill_cityId'));

                        $billing_contact = array(
                            'name'          => $form->getValue('bill_name'),
                            'prename'       => $form->getValue('bill_prename'),
                            'position'      => $form->getValue('bill_position'),
                            'email'         => $form->getValue('bill_email'),
                            'tel'           => $form->getValue('bill_tel'),
                            'fax'           => $form->getValue('bill_fax'),
                            'street'        => $form->getValue('bill_street'),
                            'hausnr'        => $form->getValue('bill_hausnr'),
                            'plz'           => $billcontactCity->getPlz(),
                            'cityId'        => $billcontactCity->getId(),
                            'comment'       => $form->getValue('bill_comment')
                        );
                        $company_billing_contact = new Yourdelivery_Model_Contact();
                        $company_billing_contact->setData($billing_contact);
                        $company_billing_contact->save();
                    }
                    else {
                        $company_billing_contact = new Yourdelivery_Model_Contact($billingContactId);
                    }
                    //append to company
                    $company->setBillingContact($company_billing_contact);
                    $company->save();
                }
             
                //create admin if the mail was given
                if ( !is_null($company_contact)) {
                    $employee = Yourdelivery_Model_Customer_Company::add(
                                    $company_contact->getData(),
                                    $company->getId(),
                                    false
                                );
                }
                
                if ( is_object($employee) ){
                    $employee->makeAdmin($company);
                    $budget->addMember($employee->getId());
                    $this->success(__b("Company successfully created"));
                }
                else{
                    $this->success(__b("Could NOT creat admin. Company was created successfully"));
                }
                $this->logger->adminInfo(sprintf("Created company #%d", $company->getId()));

                $this->_redirect('/administration_company_edit/index/companyid/' . $company->getId());

            }
            else{
                $this->error($form->getMessages());
            }
            $this->view->assign('p', $post);            
        }

        $contTable = new Yourdelivery_Model_DbTable_Contact();
        $this->view->assign('contacts', $contTable->getDistinctNameId());
    }
    
    /**
     * delete company
     */
     public function deleteAction() {
        $request = $this->getRequest();
        $cid = $request->getParam('id');
        
        //create company object to test if it exists
        if(!is_null($cid)) {
            $company = new Yourdelivery_Model_Company($cid);
            
            if(is_null($company->getId())) {
                $this->error(__b("This company is non-existant"));
            } else {
                $company->getTable()->remove($company->getId());
                $this->logger->adminInfo(sprintf("Company #%d was deleted", $company->getId()));
                $this->success(__b("Company successfully deleted"));
            }
        }
        else {
            $this->error(__b("This company is non-existant"));
        }

        $this->_redirect('/administration/companys');
    }
    
}
