<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * restaurants management
 *
 * @author alex
 */
class Administration_ServiceController extends Default_Controller_AdministrationBase {

    public function preDispatch() {
        parent::preDispatch();
        $this->view->assign('navservices', 'active');
    }

    /**
     * stores a filter array in a session
     * @param array $array
     * @return array
     */
    private function _setFilter($array) {
        $action = $this->getRequest()->getActionName();

        if (!isset($this->_session->filter))
            $this->_session->filter = new stdClass();

        $this->_session->filter->$action = $array;

        return $this->_session->filter->$action;
    }

    /**
     * create new service
     * @author alex
     * @modified daniel
     */
    public function createAction() {
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration/services');
        }

        if ($request->isPost()) {
            $form = new Yourdelivery_Form_Administration_Service_Create();
            $post = $request->getPost();

            // if we are in Brasil, create city based on plz
            if (strpos($this->config->domain->base, "janamesa") !== false) {
                $cityByPLz = Yourdelivery_Model_City::getByPlz($post['plz']);
                // we take the first one, beacuse we have only one city entry per plz in Brazil
                $c = $cityByPLz[0];

                if (is_null($c)) {
                    $this->error(__b("Diese PLZ existiert nicht!"));
                    $this->_redirect('/administration_service/create');
                }

                $post['cityId'] = $c['id'];
            }

            try {
                $city = new Yourdelivery_Model_City($post['cityId']);
                $post['plz'] = $city->getPlz();
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__b("City with this id couldn't be created!"));
                $this->_redirect('/administration_service/create');
            }

            $post['restUrl'] = Default_Helpers_Web::urlify(__('lieferservice-%s-%s', $post['name'], $city->getFullName()));
            $post['caterUrl'] = Default_Helpers_Web::urlify(__('catering-%s-%s', $post['name'], $city->getFullName()));
            $post['greatUrl'] = Default_Helpers_Web::urlify(__('grosshandel-%s-%s', $post['name'], $city->getFullName()));

            if (( (strcmp($post['notify'], 'email') == 0) || (strcmp($post['notify'], 'all') == 0) ) && (strlen(trim($post['email'])) == 0)) {
                $this->error(__b("Bitte geben Sie eine Email Adresse, wenn der Dienstleister über Email benachrichtigt werden soll"));
                $this->view->assign('p', $post);
            } else if ($form->isValid($post)) {
                $service = new Yourdelivery_Model_Servicetype_Restaurant();

                $values = $form->getValues();

                if ($values['isOnline'] == 0) {
                    $values['status'] = 3;
                } else {
                    $values['status'] = 0;
                }


                //log new Entry in Status History
                Yourdelivery_Model_DbTable_Restaurant_StatusHistory::logStatusChange((int) $values['status']);

                // if restaurant accepts only cash, set paymentbar field manually, because in this case
                // the checkbox is inactive and the value can't be read
                if ($values['onlycash'] == 1) {
                    $values['paymentbar'] = 1;
                }

                $custNr = Yourdelivery_Model_DbTable_Restaurant::getMaxCustNr() + 1;
                if ($custNr == 1) {
                    $config = Zend_Registry::get('configuration');
                    $custNr = $config->customerNr->restaurant->initialval;
                }

                $service->setCustomerNr($custNr);
                $service->setData($values);

                $id = (integer) $service->save();
                
                if ($id <= 0) {
                    $this->error(__b("Konnte Dienstleister nicht erstellen"));
                    return;
                }

                //upload new image
                if ($form->img->isUploaded()) {
                    $service->setImg($form->img->getFileName());
                }

                $franchiseTypeId = $values['franchiseTypeId'];

                if ($franchiseTypeId < 0) {
                    if (!$values['franchiseName']) {
                        $this->error(__b("Name für das neue Franchise fehlt, Franchise wurde nicht erstellt"));
                    } else {
                        try {
                            $franchise = new Yourdelivery_Model_Servicetype_Franchise();
                            $service->setFranchiseTypeId($franchise->setFranchise($values['franchiseName']));
                            $service->save();
                            $this->success(__b("Franchise wurde erstellt"));
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->logger->crit($e->getMessage());
                            $this->error(__b("Franchise konnte nicht angelegt werden"));
                            $this->_redirect('/administration/services');
                        }
                    }
                }

                $selContactId = $values['selContactId'];

                if ($selContactId == -1) {
                    $contactCity = new Yourdelivery_Model_City($values['contact_cityId']);

                    $contactData = array(
                        'name' => $values['contact_name'],
                        'prename' => $values['contact_prename'],
                        'position' => $values['contact_position'],
                        'street' => $values['contact_street'],
                        'hausnr' => $values['contact_hausnr'],
                        'plz' => $contactCity->getPlz(),
                        'cityId' => $contactCity->getId(),
                        'email' => $values['contact_email'],
                        'tel' => $values['contact_tel'],
                        'fax' => $values['contact_fax']
                    );

                    if (!$contactData['name']) {
                        $this->error(__b("Name für die Kontaktperson fehlt, Kontaktperson wurde nicht erstellt"));
                    } else if (!$contactData['prename']) {
                        $this->error(__b("Vorname für die Kontaktperson fehlt, Kontaktperson wurde nicht erstellt"));
                    } else {
                        $service_contact = new Yourdelivery_Model_Contact();
                        $service_contact->setData($contactData);
                        $contactId = $service_contact->save();
                        $this->success(__b("Kontaktperson wurde erstellt"));
                    }
                } else {
                    $contactId = $selContactId;
                }

                if ($contactId) {
                    //set contact for the service
                    $service->setContactId($contactId);
                    $service->save();

                    //the new contact shall also be an admin
                    if ($form->getValue('use_as_admin') == 1) {
                        $contact = null;
                        try {
                            $contact = new Yourdelivery_Model_Contact($contactId);
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->logger->crit($e->getMessage());
                            $this->error(__b("Kontakt konnte nicht erstellt werden"));
                            $this->_redirect('/administration/services');
                        }

                        //create new customer from contact data
                        $customer = Yourdelivery_Model_Customer::createFromContact($contact);
                        if ($customer) {
                            $customer->makeAdmin($service);
                            $this->success(__b("Kontaktperson wurde als Administrator markiert"));
                        }
                    }
                }

                if ($form->getValue('bill_as_contact') == 1) {
                    if (!is_null($contactId)) {
                        $service->setBillingContactId($contactId);
                        $service->save();
                        $this->success(__b("Kontaktperson wurde als Rechnungskontakt gespeichert"));
                    } else {
                        $this->error(__b("Kontaktperson fehlt, Rechnungskontakt wurde nicht erstellt"));
                    }
                } else {
                    $billingContactId = $values['selBillingContactId'];

                    if ($billingContactId == -1) {
                        $billContactCity = new Yourdelivery_Model_City($values['bill_cityId']);

                        $billing_contact = array(
                            'name' => $form->getValue('bill_name'),
                            'prename' => $form->getValue('bill_prename'),
                            'position' => $form->getValue('bill_position'),
                            'email' => $form->getValue('bill_email'),
                            'tel' => $form->getValue('bill_tel'),
                            'fax' => $form->getValue('bill_fax'),
                            'street' => $form->getValue('bill_street'),
                            'hausnr' => $form->getValue('bill_hausnr'),
                            'plz' => $billContactCity->getPlz(),
                            'cityId' => $billContactCity->getId(),
                            'comment' => $form->getValue('bill_comment')
                        );
                        $service_billing_contact = new Yourdelivery_Model_Contact();
                        $service_billing_contact->setData($billing_contact);
                        $service_billing_contact->save();
                    } else {
                        $service_billing_contact = null;
                        try {
                            $service_billing_contact = new Yourdelivery_Model_Contact($billingContactId);
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->logger->crit($e->getMessage());
                            $this->error(__b("Kontakt wurde nicht erstellt"));
                            $this->_redirect('/administration/services');
                        }
                    }
                    //append to company
                    $service->setBillingContactId($service_billing_contact->getId());
                    $service->save();
                    $this->success(__b("Rechnungskontakt wurde erstellt"));
                }

                //assign courier to this restaurant
                if ($values['service_courier'] != -1) {
                    $courier = null;
                    try {
                        $courier = new Yourdelivery_Model_Courier($values['service_courier']);
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->logger->crit($e->getMessage());
                        $this->error(__b("Kurier konnte nicht erstellt werden"));
                        $this->_redirect('/administration/services');
                    }


                    //create curier relationship
                    if (!is_null($courier)) {
                        $courierRelTable = new Yourdelivery_Model_DbTable_Courier_Restaurant();

                        //if courier is not registered for this restaurnat, add him
                        if ($courierRelTable->isCourierBy($courier->getId()) != $service->getId()) {
                            $courierRelation = new Yourdelivery_Model_Courier_Restaurant();
                            $courierRelation->add($courier->getId(), $service->getId());
                            $this->success(__b("Kurierdienst wurde dem Restaurant zugewiesen"));
                        }
                    }
                }

                //assign salesperson to this restaurant
                if ($values['service_salesperson'] != -1) {
                    $salesperson = null;
                    try {
                        $salesperson = new Yourdelivery_Model_Salesperson($values['service_salesperson']);
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->logger->crit($e->getMessage());
                        $this->error(__b("Salesperson konnte nicht erstellt werden"));
                        $this->logger->crit($e->getMessage());
                        $this->_redirect('/administration/services');
                    }


                    //create salesperson relationship
                    if (!is_null($salesperson)) {
                        $salespersonRelTable = new Yourdelivery_Model_DbTable_Salesperson_Restaurant();

                        //if salesperson is not registered for this restaurnat, add him
                        if (!$salespersonRelTable->isSalespersonFor($salesperson->getId(), $service->getId())) {
                            $salespersonRelation = new Yourdelivery_Model_Salesperson_Restaurant();

                            if (strlen($values['signed']) > 0) {
                                $signed = substr($values['signed'], 6, 4) . "-" . substr($values['signed'], 3, 2) . "-" . substr($values['signed'], 0, 2);
                            } else {
                                $signed = null;
                            }

                            $salespersonRelation->add($salesperson->getId(), $service->getId(), $signed);
                            $this->success(__b("Vertriebler wurde dem Restaurant zugewiesen"));
                        }
                    }
                }

                //set company restriction to this company.
                //all possible errors, e.g. wrong or missing id, are handled in setCompanyRestriction
                if ($values['service_company'] != -1) {
                    $service->setCompanyRestriction($values['service_company']);
                }


                // generate Urls for Restaurant, in case it already exists they have to made manually
                try {
                    $service->save();
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error(__b("Urls konnten nicht erzeugt werden und müssen manuell nachgebaut werden!"));
                    $this->_redirect('/administration_service/create');
                }

                $admin = $this->session->admin;

                if (is_null($admin)) {
                    $this->error(__b("Kein Admin wurde in der Sitzung gefunden, kann keinen Kommentar anlegen"));
                } else {

                    $comment = new Yourdelivery_Model_Servicetype_RestaurantNotepad();
                    $comment->setMasterAdmin(1);
                    $comment->setAdminId($admin->getId());

                    $comment->setRestaurantId($service->getId());
                    $comment->setComment(__b("Dienstleister angelegt."));
                    $comment->setTime(date("Y-m-d H:i:s", time()));
                    $comment->save();
                }


                $this->logger->adminInfo(sprintf("Successfully created service %s (%s)", $service->getName(), $service->getId()));
                //tracking create service
                $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::SERVICE_CREATE, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_SERVICE, $service->getId());

                $this->success(__b("Das Restaurant wurde erfolgreich erstellt!"));
                $this->_redirect('/administration_service_edit/index/id/' . $service->getId());
            } else {
                $this->error($form->getMessages());
                $this->view->assign('p', $post);
            }
        }

        $this->view->assign('categories', Yourdelivery_Model_Servicetype_Categories::all());
        $this->view->assign('franchisetypes', Yourdelivery_Model_Servicetype_Franchise::all());

        $compTable = new Yourdelivery_Model_DbTable_Company();
        $this->view->assign('compIds', $compTable->getDistinctNameId());

        $courierTable = new Yourdelivery_Model_DbTable_Courier();
        $this->view->assign('courierIds', $courierTable->getDistinctNameId());

        $contTable = new Yourdelivery_Model_DbTable_Contact();
        $this->view->assign('contacts', $contTable->getDistinctNameId());

        $salersTable = new Yourdelivery_Model_DbTable_Salesperson();
        $this->view->assign('salespersons', $salersTable->getDistinctNameId());

        $this->view->assign('robots', array(
            'noindex,follow' => "noindex,follow",
            'index,follow' => "index,follow",
            'index,nofollow' => "index,nofollow",
            'noindex,nofollow' => "noindex,nofollow"
        ));
    }

    /**
     * edit contact for this service
     * @author alex
     */
    public function contactAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            //create restaurant object
            $service = null;
            try {
                $service = new Yourdelivery_Model_Servicetype_Restaurant($request->getParam('id'));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__b("Dieses Restaurant existiert nicht!"));
                $this->_redirect('/administration/services');
            }

            // the same contact as the actual was chosen, do nothing
            if (!is_null($service->getContact()) && $request->getParam('contactId') == $service->getContact()->getId()) {
                $this->success(__b("Kontakt ist der gleiche, keine Änderungen wurden vorgenommen"));
            }
            //new contact was selected from drop-down menu
            else if (($request->getParam('contactId') != -1)) {
                $service->setContactId($request->getParam('contactId'));
                $service->save();
                $this->logger->adminInfo(sprintf("Set new contact #%d for restaurant %s (#%d)", $service->getContactId(), $service->getName(), $service->getId()));
                $this->success(__b("Neuer Kontakt wurde gesetzt"));
            }
            //new contact must be created
            else {
                //create contact object
                $contact = new Yourdelivery_Model_Contact();

                $form = new Yourdelivery_Form_Administration_Service_ContactEdit();

                if ($form->isValid($post)) {
                    $values = $form->getValues();

                    //if email is entered, test if some contact is already registered with this email
                    if ($values['email']) {
                        $id = Yourdelivery_Model_Contact::getByEmail($values['email']);
                        if (!is_null($id) && ($id != 0)) {
                            $this->error(__b("Unter dieser E-mail Adresse ist bereits ein Kontakt registriert. id: ") . $id);
                            $this->_redirect('/administration_service_edit/contacts/id/' . $service->getId());
                        }
                    }

                    $city = new Yourdelivery_Model_City($values['cityId']);
                    $values['plz'] = $city->getPlz();

                    //save new contact data
                    $contact->setData($values);
                    $contact->save();
                    $service->setContact($contact);
                    $service->save();
                    $this->logger->adminInfo(sprintf("Created new contact #%d for restaurant %s (#%d)", $contact->getId(), $service->getName(), $service->getId()));
                    $this->success(__b("Neuer Kontakt wurde erstellt"));
                } else {
                    $this->error($form->getMessages());
                }
            }

            $this->logger->adminInfo(sprintf("Successfully changed contact for service %s (#%d)", $service->getName(), $service->getId()));
        }

        $this->_redirect('/administration_service_edit/contacts/id/' . $service->getId());
    }

    /**
     * edit billing contact for this service
     * @author alex
     */
    public function billingcontactAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            //create restaurant object
            $service = null;
            try {
                $service = new Yourdelivery_Model_Servicetype_Restaurant($request->getParam('id'));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__b("Dieses Restaurant existiert nicht!"));
                $this->_redirect('/administration/services');
            }

            // the same billing contact as the actual was chosen, do nothing
            if (!is_null($service->getBillingContact()) && $request->getParam('billingContactId') == $service->getBillingContact()->getId()) {
                $this->success(__b("Rechnungskontakt ist der gleiche, keine Änderungen wurden vorgenommen"));
            }
            //new contact was selected from drop-down menu
            else if (($request->getParam('billingContactId') != -1)) {
                $service->setBillingContactId($request->getParam('billingContactId'));
                $service->save();
                $this->logger->adminInfo(sprintf("Set new billing contact #%d for restaurant %s (#%d)", $service->getBillingContactId(), $service->getName(), $service->getId()));
                $this->success(__b("Neuer Rechnungskontakt wurde gesetzt"));
            }
            //new contact must be created
            else {
                //create contact object
                $contact = new Yourdelivery_Model_Contact();

                $form = new Yourdelivery_Form_Administration_Service_ContactEdit();

                if ($form->isValid($post)) {
                    $values = $request->getPost();

                    //if email is entered, test if some contact is already registered with this email
                    if ($values['email']) {
                        $id = Yourdelivery_Model_Contact::getByEmail($values['email']);
                        if (!is_null($id) && ($id != 0)) {
                            $this->error(__b("Unter dieser E-mail Adresse ist bereits ein Kontakt registriert. id: ") . $id);
                            $this->_redirect('/administration_service_edit/contacts/id/' . $service->getId());
                        }
                    }

                    $city = new Yourdelivery_Model_City($values['cityId']);
                    $values['plz'] = $city->getPlz();

                    //save new contact data
                    $contact->setData($values);
                    $contact->save();
                    $service->setBillingContactId($contact->getId());
                    $service->save();
                    $this->logger->adminInfo(sprintf("Created new billing contact #%d for restaurant %s (#%d)", $service->getContactId(), $service->getName(), $service->getId()));
                    $this->success(__b("Neuer Rechnungskontakt wurde erstellt"));
                } else {
                    $this->error($form->getMessages());
                }
            }
            $this->logger->adminInfo(sprintf("Successfully changed billing contact for service %s (%d)", $service->getName(), $service->getId()));
        }
        $this->_redirect('/administration_service_edit/contacts/id/' . $service->getId());
    }

    /**
     * manage courier relationship
     * @author alex
     */
    public function courierAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();

        //create restaurant object
        $service = null;
        try {
            $service = new Yourdelivery_Model_Servicetype_Restaurant($request->getParam('serviceId'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Dieses Restaurant existiert nicht!"));
            $this->_redirect('/administration/services');
        }

        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();

            if ($post['service_courier'] == -1) {
                $this->error(__b("Keine Kurier Id wurde angegeben!"));
            }
            //add courier to the service
            else if (!is_null($post['add_courier'])) {
                $courier = null;
                try {
                    $courier = new Yourdelivery_Model_Courier($post['service_courier']);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error(__b("Kurier konnte nicht erstellt werden"));
                    $this->_redirect('/administration/services');
                }

                //create curier relationship
                if (!is_null($courier)) {
                    $courierRelTable = new Yourdelivery_Model_DbTable_Courier_Restaurant();

                    //if courier is not registered for this restaurant, add him
                    if ($courierRelTable->isCourierBy($courier->getId()) != $service->getId()) {
                        $courierRelation = new Yourdelivery_Model_Courier_Restaurant();
                        $courierRelation->add($courier->getId(), $service->getId());
                    }
                }

                $this->logger->adminInfo(sprintf("Successfully set courier %s (%d) for service %s (#%d)", $courier->getName(), $courier->getId(), $service->getName(), $service->getId()));

                $this->success(__b("Zuordnung zum Kurierdient wurde gesetzt"));
            }
        }
        // GET request, so we deleting courier relationship
        else {
            if ($request->getParam('delcourier', false)) {
                $curierId = $request->getParam('delcourier');
                Yourdelivery_Model_Courier_Restaurant::delete($curierId, $service->getId());
                $this->success(__b("Zuordnung zum Kurierdient wurde gelöscht"));

                $this->logger->adminInfo(sprintf("Successfully deleted association of courier #%d with service %s (#%d)", $curierId, $service->getName(), $service->getId()));
            }
        }
        $this->_redirect('/administration_service_edit/assoc/id/' . $service->getId());
    }

    /**
     * manage salesperson relationship
     * @author alex
     */
    public function salespersonAction() {
        $request = $this->getRequest();

        //create restaurant object
        $service = null;
        try {
            $service = new Yourdelivery_Model_Servicetype_Restaurant($request->getParam('serviceId'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Dieses Restaurant existiert nicht!"));
            $this->_redirect('/administration/services');
        }

        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();

            //assotiate salesperson with the service
            if (!is_null($post['add_salesperson']) && ($post['service_salesperson'] != -1)) {
                $salesperson = null;
                try {
                    $salesperson = new Yourdelivery_Model_Salesperson($post['service_salesperson']);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error(__b("Diesen Vertriebler gibt es nicht!"));
                    $this->_redirect('/administration_service_edit/assoc/id/' . $service->getId());
                }

                //create salesperson relationship
                if (!is_null($salesperson)) {
                    $salespersonRelTable = new Yourdelivery_Model_DbTable_Salesperson_Restaurant();

                    //if salesperson is not registered for this restaurant, add him
                    if (!$salespersonRelTable->isSalespersonFor($salesperson->getId(), $service->getId())) {
                        $signed = substr($post['signed'], 6, 4) . "-" . substr($post['signed'], 3, 2) . "-" . substr($post['signed'], 0, 2);
                        $salespersonRelation = new Yourdelivery_Model_Salesperson_Restaurant();
                        $salespersonRelation->add($salesperson->getId(), $service->getId(), $signed);

                        $this->logger->adminInfo(sprintf("Successfully set salesperson %s %s (#%d) for service %s (#%d)", $salesperson->getPrename(), $salesperson->getName(), $salesperson->getId(), $service->getName(), $service->getId()));
                    }
                }
            }
            $this->_redirect('/administration_service_edit/assoc/id/' . $service->getId());
        }
        // GET request, so we deleting salesperson relationship
        else {
            if ($request->getParam('delsalesperson', false)) {
                $salespersonId = $request->getParam('delsalesperson');
                Yourdelivery_Model_Salesperson_Restaurant::delete($salespersonId, $service->getId());
                $this->logger->adminInfo(sprintf("Successfully deleted association of salesperson #%d with service %s (#%d)", $salespersonId, $service->getName(), $service->getId()));
                $this->_redirect('/administration_service_edit/assoc/id/' . $service->getId());
            }
        }

        $contTable = new Yourdelivery_Model_DbTable_Contact();
        $this->view->assign('contacts', $contTable->getDistinctNameId());
    }

    /**
     * load and send a bradcast fax
     * @author alex
     */
    public function broadcastfaxAction() {
        // broadcast fax blocks the system, so wil be deactivated until decision if it stay or will be deleted forever
        return;


        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 1200);

        $form = new Yourdelivery_Form_Administration_Service_Broadcastfax();
        $post = $this->getRequest()->getPost();

        //load fax from pdf file
        if ($this->getRequest()->isPost() && $this->getRequest()->getParam('load_pdf')) {
            if ($form->isValid($post)) {
                $val = $form->getValues();
                if ($form->pdf->isUploaded()) {
                    $dataTable = new Yourdelivery_Model_DbTable_Restaurant();
                    $filter = $this->_setFilter(array("status = " . $val['sendto']));

                    try {
                        $c = $dataTable->fetchAll(implode(' AND ', $filter));
                    } catch (Zend_Exception $e) {
                        $this->error($e->getMessage());
                        $this->_redirect('/admin/broadcastfax');
                    }

                    foreach ($c AS $rest) {
                        // Using config-based locale during composing and sending fax
                        $this->_restoreLocale();
                        $fax = new Yourdelivery_Sender_Fax();
                        $isFaxSent = $fax->send($rest->fax, $form->pdf->getFileName(), 'broadcastfax');
                        $this->_overrideLocale();

                        if ($isFaxSent) {
                            $this->success(__b("Fax wurde an <strong>%s</strong> (id#%s) erfolgreich verschickt", $rest->name, $rest->id));
                        } else {
                            $this->error(__b("Fax konnte nicht an <strong>%s</strong> (id#%s) verschickt werden", $rest->name, $rest->id));
                        }
                    }
                }
            } else {
                $errors = array();
                $errors = $form->getErrors();
                foreach ($errors as $err) {
                    $this->error($err);
                }
            }
        }
    }

    /**
     * manage the list of company restrictions
     * @author alex
     */
    public function companyAction() {
        $request = $this->getRequest();

        //create restaurant object
        $service = null;
        try {
            $service = new Yourdelivery_Model_Servicetype_Restaurant($request->getParam('serviceId'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Dieses Restaurant existiert nicht!"));
            $this->_redirect('/administration/services');
        }

        if ($request->isPost()) {
            $compId = $request->getParam('service_company');
            $exclusive = $this->getRequest()->getParam('exclusive');
            $service->setCompanyRestriction($compId, $exclusive);
            $this->logger->adminInfo(sprintf("Successfully set association of service %s (%d) to company #%d", $service->getName(), $service->getId(), $compId));
        }
        //GET request, we came from the link
        else {
            if ($request->getParam('delcomp', false)) {
                $service->removeCompanyRestriction($request->getParam('delcomp'));
                $this->logger->adminInfo(sprintf("Successfully deleted association of service %s (%d) with company %d", $service->getName(), $service->getId(), $request->getParam('delcomp')));
            }
        }
        $this->_redirect('/administration_service_edit/assoc/id/' . $service->getId());
    }

    /**
     * manage the list of company restrictions
     * @author alex
     * @since 28.09.2010
     */
    public function billingmergeAction() {
        $request = $this->getRequest();

        //create restaurant object
        $service = null;
        try {
            $service = new Yourdelivery_Model_Servicetype_Restaurant($request->getParam('serviceId'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Dieses Restaurant existiert nicht!"));
            $this->_redirect('/administration/services');
        }

        if ($request->isPost()) {
            $post = $request->getPost();
            $billingChildren = $post['billingchildren'];
            if (is_array($billingChildren)) {
                foreach ($billingChildren as $childId) {
                    $service->addBillingChild(new Yourdelivery_Model_Servicetype_Restaurant($childId));
                }
            }
        }
        //GET request, we came from the delete link
        else {
            if ($request->getParam('deletechild', false)) {
                Yourdelivery_Model_DbTable_Restaurant_BillingMerge::removeByParentAndChild($service->getId(), $request->getParam('deletechild'));
            } else if ($request->getParam('deleteparent', false)) {
                Yourdelivery_Model_DbTable_Restaurant_BillingMerge::removeByParentAndChild($request->getParam('deleteparent'), $service->getId());
            }
        }
        $this->_redirect('/administration_service_edit/assoc/id/' . $service->getId());
    }

    /**
     * manage special openings
     * @author alex
     */
    public function specialopeningsAction() {
        $request = $this->getRequest();

        //create restaurant object
        $service = null;
        try {
            $service = new Yourdelivery_Model_Servicetype_Restaurant($request->getParam('serviceId'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Dieses Restaurant existiert nicht!"));
            $this->_redirect('/administration/services');
        }

        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();

            if (!is_null($post['spec_date']) && (strlen(trim($post['spec_date'])) > 0)) {
                $date = $post['spec_date'];

                //restaurant is closed
                if ($post['closed'] == 1) {
                    $openings = new Yourdelivery_Model_Servicetype_OpeningsSpecial();
                    $values = array(
                        'restaurantId' => $service->getId(),
                        'specialDate' => substr($date, 6) . substr($date, 3, 2) . substr($date, 0, 2),
                        'closed' => 1);

                    $openings->setData($values);
                    $openings->save();
                }
                //restaurnat has special opening times
                else if (!is_null($post['spectimeFrom']) && !is_null($post['spectimeUntil'])) {
                    $openings = new Yourdelivery_Model_Servicetype_OpeningsSpecial();
                    $values = array(
                        'restaurantId' => $service->getId(),
                        'specialDate' => substr($date, 6) . substr($date, 3, 2) . substr($date, 0, 2),
                        'ffrom' => $post['spectimeFrom'],
                        'until' => $post['spectimeUntil']);

                    $openings->setData($values);
                    $openings->save();
                }
            }
        }
        // GET request, we are probably deleting special opening times
        else {
            $openingId = $request->getParam('openingid');

            if (!is_null($openingId)) {
                Yourdelivery_Model_DbTable_Restaurant_Openings_Special::remove($openingId);
            }
        }
        $this->_redirect('/administration_service_edit/index/id/' . $service->getId());
    }

    /**
     * delete restarurant
     * @author alex
     */
    public function deleteAction() {
        $request = $this->getRequest();
        $sid = $request->getParam('id');

        if (is_null($sid)) {
            $this->error(__b("Diesen Dienstleister gibt es nicht!"));
            $this->_redirect('/administration/services');
        }

        //create company object to test if it exists
        if (!is_null($sid)) {
            $service = null;
            try {
                $service = new Yourdelivery_Model_Servicetype_Restaurant($sid);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__b("Diesen Dienstleister gibt es nicht!"));
                $this->_redirect('/administration/services');
            }


            if (is_null($service->getId())) {
                $this->error(__b("Diesen Dienstleister gibt es nicht!"));
            } else {
                $service->delete();

                $admin = $this->session->admin;

                if (is_null($admin)) {
                    $this->error(__b("Kein Admin wurde in der Sitzung gefunden, kann die Begründung für die Statusänderung nicht eintragen"));
                } else {
                    $comment = new Yourdelivery_Model_Servicetype_RestaurantNotepad();
                    $comment->setMasterAdmin(1);
                    $comment->setAdminId($admin->getId());

                    $comment->setRestaurantId($service->getId());
                    $comment->setComment("Gelöscht");
                    $comment->setTime(date("Y-m-d H:i:s", time()));
                    $comment->save();
                }

                $this->logger->adminInfo(sprintf("Successfully deleted service %s (%d)", $service->getName(), $service->getId()));
                $this->success(__b("Dienstleister wurde gelöscht"));
            }
        }
        $this->_redirect('/administration/services');
    }

    /**
     * copy menu, deliver ranges or/and openings from one restaurant to another
     * @author Alex Vait <vait@lieferando.de>
     */
    public function copyAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $srcId = $request->getParam('srcId', null);
            
            $post = $request->getPost();
            
            if (is_null($srcId) || strlen($srcId)==0) {
                $this->error(__b("Bitte spezifizieren sie den Dienstleister, von dem die Speisekarte kopiert werden soll!"));
                $this->_redirect('/administration_service/copy');
            }
            
            $dstId = $request->getParam('dst', null);
            $restaurantIds = $request->getParam('restaurantIds');

            if (!is_array($restaurantIds) || (count($restaurantIds)==0) ) {
                $this->error(__b("Bitte spezifizieren sie den Dienstleister, in den die Speisekarte kopiert werden soll!"));
                $this->_redirect('/administration_service/copy');
            }
            
            // don't copy menu to same restaurants moe than once
            $restaurantIds = array_unique($restaurantIds);
            
            $messages = array();
            
            foreach ($restaurantIds as $dstId) {
                $messages[$dstId] = array('errors' => array(), 'success' => array());
                
                if ($srcId == $dstId) {
                    $messages[$dstId]['errors'][] = __b("Ziel und Quelle sind identisch bei dem Dienstleister #") . $dstId;
                    continue;
                } 
                else {
                    try {
                        $srcRestaurant = new Yourdelivery_Model_Servicetype_Restaurant($srcId);
                        $dstRestaurant = new Yourdelivery_Model_Servicetype_Restaurant($dstId);
                    } 
                    catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $messages[$dstId]['errors'][] = __b("Dienstleister #" . $dstId . __b(" existiert nicht!"));
                        continue;
                    }

                    // copy menu
                    if ($request->getParam('menu', false)) {
                        $menu = new Yourdelivery_Model_Menu();
                        $menu->copy($dstRestaurant, $srcRestaurant);

                        $messages[$dstId]['success'][] = __b("Speisekarte von %s (%d) wurde nach %s (%d) kopiert", $srcRestaurant->getName(), $srcRestaurant->getId(), $dstRestaurant->getName(), $dstRestaurant->getId());
                        $this->logger->adminInfo(sprintf("Successfully copied menu from service %s (%d) to %s (%d)", $srcRestaurant->getName(), $srcRestaurant->getId(), $dstRestaurant->getName(), $dstRestaurant->getId()));
                    }

                    // copy deliver ranges
                    if ($request->getParam('deliverranges', false)) {
                        $countErrors = 0;
                        $countDuplicates = 0;
                        $countSuccess = 0;
                        
                        $srcRestaurant->copyDeliverRanges($dstId, $countErrors, $countDuplicates, $countSuccess);                        
                        
                        if ($countErrors > 0) {
                            $messages[$dstId]['errors'][] = __b("%d Liefergebiete von %s (%d) konnten nicht nach %s (%d) kopiert.", $countErrors, $srcRestaurant->getName(), $srcRestaurant->getId(), $dstRestaurant->getName(), $dstRestaurant->getId());
                        }

                        if ($countDuplicates > 0) {
                            $messages[$dstId]['errors'][] = __b("%d Liefergebiete von %s (%d) wurden nicht nach %s (%d) kopiert, da diese Liefergebiete bereits existieren. Die neuen Daten wurden übernommen.", $countDuplicates, $srcRestaurant->getName(), $srcRestaurant->getId(), $dstRestaurant->getName(), $dstRestaurant->getId());
                        }

                        if ($countSuccess > 0) {
                            $messages[$dstId]['success'][] = __b("%d Liefergebiete von %s (%d) wurden nach %s (%d) kopiert", $countSuccess, $srcRestaurant->getName(), $srcRestaurant->getId(), $dstRestaurant->getName(), $dstRestaurant->getId());                            
                        }
                        
                        $this->logger->adminInfo(sprintf("Successfully copied deliver ranges from service %s (%d) to %s (%d)", $srcRestaurant->getName(), $srcRestaurant->getId(), $dstRestaurant->getName(), $dstRestaurant->getId()));
                    }

                    // copy opening times
                    if ($request->getParam('openings', false)) {
                        if (count($dstRestaurant->getRegularOpenings()) > 0) {
                            $messages[$dstId]['errors'][] = __b("Für das Restaurant %s (%d) wurden bereits Öffnungszeiten für mindestens einen Tag eingetragen, Öffnungszeiten wurden nicht kopiert", $dstRestaurant->getName(), $dstRestaurant->getId());
                        } 
                        else {
                            $srcRestaurant->copyOpenings($dstId);
                            // copy special opening times too
                            $srcRestaurant->copySpecialOpenings($dstId);

                            $messages[$dstId]['success'][] = __b("Öffnungszeiten von %s (%d) wurde nach %s (%d) kopiert", $srcRestaurant->getName(), $srcRestaurant->getId(), $dstRestaurant->getName(), $dstRestaurant->getId());
                            $this->logger->adminInfo(sprintf("Successfully copied opening times from service %s (%d) to %s (%d)", $srcRestaurant->getName(), $srcRestaurant->getId(), $dstRestaurant->getName(), $dstRestaurant->getId()));
                        }
                    }
                }                
            }
        }
        $this->view->messages = $messages;
    }

    public function picturecategoryAction() {
        $request = $this->getRequest();
        $id = $request->getParam('id', null);
        if (!is_null($id) && $id != '') {
            try {
                $service = new Yourdelivery_Model_Servicetype_Restaurant($id);
                $this->view->service = $service;
                $this->view->picCat = Yourdelivery_Model_DbTable_Category_Picture::all();
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->_redirect('/administration_service_category_picture/list');
            }
        } else {
            $this->warn(__b("Kein Restaurant gewählt"));
            $this->_redirect('/administration_service_category_picture/list');
        }

        // save assignment for each meal_categorie
        if ($request->isPost() && !is_null($request->getParam('submitassign', null))) {
            $pcats = $request->getParam('pcat', null);
            $errors = null;
            foreach ($pcats as $catId => $pcat) {
                try {
                    $cat = new Yourdelivery_Model_Meal_Category($catId);
                    $cat->setCategoryPictureId($pcat[0]);
                    $cat->save();
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    continue;
                }
            }

            $this->success(__b("Kategorien erfolgreich bearbeitet"));
            $this->_redirect('/administration_service/picturecategory/id/' . $id);
        }
    }

    /**
     * Search and replace
     * @author mlaug
     * @since 31.08.2010
     */
    public function searchreplaceAction() {
        
    }

    /**
     * Search and replace overview
     * @author vpriem
     * @since 01.09.2010
     */
    public function searchreplaceoverviewAction() {

        // get parameters
        $request = $this->getRequest();
        $id = $request->getParam('id');

        // delete
        if ($id !== null) {
            // load table
            $filterTable = new Yourdelivery_Model_DbTable_Filters();

            // create and delete
            if ($filter = $filterTable->findRow($id)) {
                $filter->delete();
                $this->success(__b("Das Filter wurde erfolgreich gelöscht"));
            } else {
                $this->error(__b("Das Filter wurde nicht gefunden"));
            }

            // redirect
            $this->_redirect('/administration_service/searchreplaceoverview');
        }

        // create gid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setSource(new Bvb_Grid_Source_Zend_Select(Yourdelivery_Model_Filter::getGrid()));
        $grid->updateColumn('id', array('decorator' => "#{{id}}"));

        // add extra columns
        $col = new Bvb_Grid_Extra_Column();
        $col->position('right')
                ->name('Optionen')
                ->decorator(
                        '<div>
                        <a href="/administration_service/searchreplaceoverview/id/{{id}}" class="yd-are-you-sure">' . __b('Löschen') . '</a>
                    </div>'
        );
        $grid->addExtraColumns($col);

        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * Search and replace now
     * @author vpriem
     * @since 04.10.2010
     */
    public function searchreplacenowAction() {

        set_time_limit(0);
        $db = Zend_Registry::get('dbAdapter');

        $rows = $db->fetchAll(
                "SELECT `search`, `replace`
            FROM `filters`
            WHERE `name` = 'mealNameDescription'"
        );
        $search = $replace = array();
        foreach ($rows as $row) {
            $search[] = $row['search'];
            $replace[] = $row['replace'];
        }

        // meals
        $stmt = $db->query(
                "SELECT `id`, `name`, `description`
            FROM `meals`"
        );
        while ($row = $stmt->fetch()) {
            $db->query(
                    "UPDATE `meals`
                SET `name` = ?, `description` = ?
                WHERE `id` = ?", array(
                str_replace($search, $replace, $row['name']),
                str_replace($search, $replace, $row['description']),
                $row['id']
            ));
        }

        // categories
        $stmt = $db->query(
                "SELECT `id`, `name`, `description`
            FROM `meal_categories`"
        );
        while ($row = $stmt->fetch()) {
            $db->query(
                    "UPDATE `meal_categories`
                SET `name` = ?, `description` = ?
                WHERE `id` = ?", array(
                str_replace($search, $replace, $row['name']),
                str_replace($search, $replace, $row['description']),
                $row['id']
            ));
        }

        // move filters
        $db->query(
                "INSERT INTO `filters_applied`
            SELECT *
            FROM `filters`
            WHERE `name` = 'mealNameDescription'
                OR `name` = 'mealCategoryNameDescription'"
        );
        $db->query(
                "DELETE FROM `filters`
            WHERE `name` = 'mealNameDescription'
                OR `name` = 'mealCategoryNameDescription'"
        );

        $this->success(__b("Alles klar"));
        $this->_redirect('administration_service/searchreplace');
    }

    /**
     * upload new document for this restaurant
     * @since 21.12.2010
     * @author alex
     */
    public function uploaddocumentAction() {
        $request = $this->getRequest();

        //load document
        if ($request->isPost()) {
            $post = $request->getPost();

            //create restaurant object
            try {
                $service = new Yourdelivery_Model_Servicetype_Restaurant($post['serviceId']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__b("Das Restaurant konnte nicht initialisiert werden!"));
                $this->_redirect('/administration/services');
            }

            $form = new Yourdelivery_Form_Administration_Service_Upload();

            if ($form->isValid($post)) {
                $val = $form->getValues();
                if ($form->document->isUploaded()) {
                    $documentName = $form->document->getFileName();
                    $fileExtension = end(explode(".", basename($documentName)));

                    if (strlen(trim($val['alternativeName'])) > 0) {
                        // replace only the name, keep correct file extension
                        $documentBasename = current(explode(".", trim($val['alternativeName']))) . "." . $fileExtension;
                    } else {
                        $documentBasename = basename($documentName);
                    }

                    //replace whitespaces with underscore so when deleting the document we don't have to mess with whitespaces and similar stuff in URL
                    $documentBasename = preg_replace("/[^a-zA-Z0-9_.]/i", "_", $documentBasename);

                    $data = file_get_contents($documentName);
                    // if file_get_contents failed $data is 'false'
                    if ($data !== false) {
                        $service->getDocumentsStorage()->store($documentBasename, $data);

                        $this->logger->adminInfo(sprintf("Successfully saved document %s for service %s (%d)", $documentBasename, $service->getName(), $service->getId()));
                    }
                }
            } else {
                $this->error($form->getMessages());
            }

            $this->_redirect('/administration_service_edit/documents/id/' . $service->getId());
        }
    }

    /**
     * delete document associated with this restaurant
     * @since 21.12.2010
     * @author alex
     */
    public function deletedocumentAction() {
        $request = $this->getRequest();

        if (is_null($request->getParam('serviceId', null)) || is_null($request->getParam('document', null))) {
            $this->error(__b("Falsche Parameter!"));
            $this->_redirect('/administration/services');
        }

        //create restaurant object
        try {
            $service = new Yourdelivery_Model_Servicetype_Restaurant($request->getParam('serviceId'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Das Restaurant konnte nicht initialisiert werden!"));
            $this->_redirect('/administration/services');
        }

        $service->removeDocument($request->getParam('document'));

        $this->logger->adminInfo(sprintf("Successfully deleted document %s for service %s (%d)", $request->getParam('document'), $service->getName(), $service->getId()));

        $this->_redirect('/administration_service_edit/documents/id/' . $service->getId());
    }

    /**
     * define new data for additional comission
     * @since 22.12.2010
     * @author alex
     */
    public function addcommissionAction() {
        $request = $this->getRequest();

        //create restaurant object
        try {
            $service = new Yourdelivery_Model_Servicetype_Restaurant($request->getParam('restaurantId'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Das Restaurant konnte nicht initialisiert werden!"));
            $this->_redirect('/administration/services');
        }

        if ($service->getId() == 0) {
            $this->error(__b("Das Restaurant konnte nicht initialisiert werden!"));
            $this->_redirect('/administration/services');
        }

        if ($request->isPost()) {
            $post = $request->getPost();
            $form = new Yourdelivery_Form_Administration_Service_AdditionalCommission();

            if ($form->isValid($post)) {
                $values = $form->getValues();
                $values['komm'] = str_replace(',', '.', $values['komm']);
                $additionalCommission = new Yourdelivery_Model_Servicetype_Commission();

                if (intval($values['komm']) > 100) {
                    $this->error(sprintf(__b("Die Kommission kann nicht größer als 100%% sein!"))); // use sprintf to escape %
                    $this->_redirect('/administration_service_edit/comissions/id/' . $service->getId());
                }

                $newFrom = substr($values['startTimeD'], 6, 4) . "-" . substr($values['startTimeD'], 3, 2) . "-" . substr($values['startTimeD'], 0, 2);
                $newUntil = substr($values['endTimeD'], 6, 4) . "-" . substr($values['endTimeD'], 3, 2) . "-" . substr($values['endTimeD'], 0, 2);

                if ($newFrom > $newUntil) {
                    $this->error(__b("Die Anfangszeit kann nicht größer als Endzeit sein!"));
                    $this->_redirect('/administration_service_edit/comissions/id/' . $service->getId());
                }

                $savedComissions = Yourdelivery_Model_DbTable_Restaurant_Commission::getAdditionalCommissions($service->getId());
                foreach ($savedComissions as $sc) {
                    $scFrom = substr($sc['from'], 0, 10);
                    $scUntil = substr($sc['until'], 0, 10);

                    if (( ($newFrom <= $scFrom) && ($newUntil >= $scFrom)) ||
                            ( ($newFrom < $scUntil) && ($newUntil > $scUntil)) ||
                            ( ($newFrom > $scFrom) && ($newUntil <= $scUntil))
                    ) {
                        $this->error(__b("Dieser Zeitabschnitt überschneidet sich mit einem anderen!"));
                        $this->_redirect('/administration_service_edit/comissions/id/' . $service->getId());
                    }
                }

                $values['from'] = $newFrom . " 00:00:00";
                $values['until'] = $newUntil . " 23:59:59";

                $additionalCommission->setData($values);
                $additionalCommission->save();

                $this->logger->adminInfo(sprintf("Added new comission data for restaurant %s (#%d)", $service->getName(), $service->getId()));

                $this->success(__b("Zusätzliche Provisionsdaten wurden hinzugefügt"));
            } else {
                $this->error($form->getMessages());
            }

            $this->_redirect('/administration_service_edit/comissions/id/' . $service->getId());
        }
    }

    /**
     * delete additional comission
     * @since 22.12.2010
     * @author alex
     */
    public function deletecomissionAction() {
        $request = $this->getRequest();

        $commissionId = $request->getParam('commissionId', null);
        $restaurantId = $request->getParam('serviceId', null);

        if (is_null($restaurantId) || is_null($commissionId)) {
            $this->error(__b("Falsche Parameter!"));
            $this->_redirect('/administration/services');
        }

        //create restaurant object
        try {
            $service = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Das Restaurant konnte nicht initialisiert werden!"));
            $this->_redirect('/administration/services');
        }
        $commission = null;
        try {
            $commission = new Yourdelivery_Model_Servicetype_Commission($commissionId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Diesen Dienstleister gibt es nicht!"));
            $this->_redirect('/administration/services');
        }


        if ($commission->getRestaurantId() != $service->getId()) {
            $this->error(__b("Diese Provisionsdaten gehören nicht zu diesem Dienstleister!"));
        } else {
            Yourdelivery_Model_DbTable_Restaurant_Commission::remove($commissionId);
            $this->logger->adminInfo(sprintf("Deleted comission data #%d for restaurant %s (#%d)", $commissionId, $service->getName(), $service->getId()));
            $this->success(__b("Provisionsdaten wurden gelöscht!"));
        }

        $this->_redirect('/administration_service_edit/comissions/id/' . $service->getId());
    }

}
