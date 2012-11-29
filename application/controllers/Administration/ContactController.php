<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * contacts management
 *
 * @author alex
 */
class Administration_ContactController extends Default_Controller_AdministrationBase{

    /**
     * create new contact
     * @author alex
     */
    public function createAction(){
        $this->view->assign('navcontacts', 'active');
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration/contacts');
        }

        if ( $request->isPost() ){
            $post = $this->getRequest()->getPost();

            // if we are in Brasil, create city based on plz
            if (strpos($this->config->domain->base, "janamesa") !== false) {
                $cityByPLz = Yourdelivery_Model_City::getByPlz($post['plz']);
                // we take the first one, beacuse we have only one city entry per plz in Brazil
                $c = $cityByPLz[0];

                if (is_null($c)) {
                    $this->error(__b("Diese PLZ existiert nicht!"));
                    $this->_redirect('/administration_contact/create');
                }

                $post['cityId'] = $c['id'];
            }
            
            //create new contact
            $form = new Yourdelivery_Form_Administration_Contact_Edit();
            if($form->isValid($post)) {
                $contact = new Yourdelivery_Model_Contact();

                $values = $form->getValues();
                
                $city = new Yourdelivery_Model_City($values['cityId']);
                $values['plz'] = $city->getPlz();

                $contact->setData($values);
                $contact->save();
                $this->success(__b("Contact was succesfully created"));
                $this->logger->adminInfo(sprintf("Created contact #%d", $contact->getId()));
                $this->_redirect('/administration/contacts');
            }
            else {
                $this->error($form->getMessages());
                $this->view->assign('p', $post);
            }
        }
    }

    /**
     * edit contact
     * @author alex
     */
    public function editAction(){
        $this->view->assign('navcontacts', 'active');
        $request = $this->getRequest();

        $path = $this->session->contactspath;
        if (is_null($path)) {
            $path = '/administration/contacts';
        }

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect($path);
        }

        if (is_null($request->getParam('id'))) {
            $this->error(__b("This contact ist non-existant"));
            $this->_redirect($path);
        }

        //create contact object
        try {
            $contact = new Yourdelivery_Model_Contact($request->getParam('id'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error(__b("This contact ist non-existant"));
            $this->_redirect($path);
        }

        if ( $request->isPost() ){
            $post = $this->getRequest()->getPost();

            $form = new Yourdelivery_Form_Administration_Contact_Edit();
            if ( $form->isValid($post) ){
                $values = $form->getValues();

                // if we are in Brasil, create city based on plz
                if (strpos($this->config->domain->base, "janamesa") !== false) {
                    $cityByPLz = Yourdelivery_Model_City::getByPlz($values['plz']);
                    // we take the first one, beacuse we have only one city entry per plz in Brazil
                    $c = $cityByPLz[0];
                    
                    if (is_null($c)) {
                        $this->error(__b("Diese PLZ existiert nicht!"));
                        $this->_redirect('/administration_contact/edit/id/' . $contact->getId());
                    }
                    
                    $values['cityId'] = $c['id'];
                }
                else {
                    $city = new Yourdelivery_Model_City($values['cityId']);
                    $values['plz'] = $city->getPlz();                    
                }
                //save new data
                $contact->setData($values);
                $contact->save();
                $this->success(__b("Changes successfully saved"));
                $this->logger->adminInfo(sprintf("Contact #%d was edited", $contact->getId()));
                $this->_redirect('/administration_contact/edit/id/' . $contact->getId());
            }
            else{
                $this->error($form->getMessages());
                $this->_redirect('/administration_contact/edit/id/' . $contact->getId());                
            }
        }

        $this->view->assign('contact', $contact);
    }

    /**
     * information about the contact
     * @author alex
     */
    public function infoAction() {
        $request = $this->getRequest();
        $cid = $request->getParam('id', null);

        if(!is_null($cid)) {
            $contact = new Yourdelivery_Model_Contact($cid);
            $this->view->assign('contact', $contact);

            $restaurants_db = $contact->getServices();
            $restaurants = new SplObjectStorage();
            
            foreach($restaurants_db as $r){
                $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($r['id']);
                $restaurants->attach($restaurant);
            }
            $this->view->assign('restaurants', $restaurants);

            $companys_db = $contact->getCompanys();
            $companys = new SplObjectStorage();

            foreach($companys_db as $c){
                $company = new Yourdelivery_Model_Company($c['id']);
                $companys->attach($company);
            }
            $this->view->assign('companys', $companys);
        }
        else {
            $path = $this->session->contactspath;
            if (is_null($path)) {
                $path = '/administration/contacts';
            }
            $this->error(__b("This contact ist non-existant"));
            $this->_redirect($path);
        }
    }

    /**
     * delete contact
     * @author alex
     */
     public function deleteAction() {
        $request = $this->getRequest();
        $cid = $request->getParam('id');

        $contact = new Yourdelivery_Model_Contact($cid);

        if(is_null($contact->getId())) {
            $this->error(__b("This contact ist non-existant"));
        } else {
            $contact->getTable()->remove($contact->getId());
        }

        $this->logger->adminInfo(sprintf("Contact #%d was deleted", $cid));

        $path = $this->session->contactspath;
        if (is_null($path)) {
            $path = '/administration/contacts';
        }

        $this->_redirect($path);
    }
}
?>
