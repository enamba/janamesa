<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * users managment
 *
 * @author mlaug
 */
class Administration_UserController extends Default_Controller_AdministrationBase {

    /**
     * create new user
     */
    public function createAction() {
        $this->view->assign('navusers', 'active');
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration/users');
        }

        if ($this->getRequest()->isPost()) {
            $form = new Yourdelivery_Form_Administration_User_Create();
            $post = $this->getRequest()->getPost();
            
            // if we are in Brasil, create city based on plz
            if (strpos($this->config->domain->base, "janamesa") !== false) {
                $cityByPLz = Yourdelivery_Model_City::getByPlz($post['plz']);
                // we take the first one, beacuse we have only one city entry per plz in Brazil
                $c = $cityByPLz[0];

                if (is_null($c)) {
                    $this->error(__b("Diese PLZ existiert nicht!"));
                    $this->_redirect('/administration_user/create');
                }

                $post['cityId'] = $c['id'];
            }            
            
            if ($form->isValid($post)) {
                $values = $form->getValues();
                
                $alreadyRegistred = Yourdelivery_Model_DbTable_Customer::findByEmail($values['email'], false);
                if (is_array($alreadyRegistred)) {
                    $this->error(__b("Die E-Mail Adresse %s wird bereits verwendet bei dem Benutzer %d", $email, $alreadyRegistred['id']));
                    $this->_redirect('/administration_user/create');
                }

                $city = new Yourdelivery_Model_City($values['cityId']);
                $values['plz'] = $city->getPlz();

                $cid = Yourdelivery_Model_Customer::add($values);
                
                if (is_null($cid)) {
                    $this->error(__b("Der benutzer konnte nicht erstellt werden"));                    
                    $this->_redirect('/administration_user/create');
                }
                
                $customer = new Yourdelivery_Model_Customer($cid);
                $customer->setNewsletter(true);

                if ((strlen($values['street']) != 0) && (strlen($values['hausnr']) != 0) && (strlen($values['plz']) != 0)) {
                    // save first user location
                    $location = new Yourdelivery_Model_Location();
                    $location->setData($values);
                    $location->setCustomerId($cid);
                    $location->save();
                } else {
                    $this->error(__b("Adressdaten sind unvollstänig. Adresse wurde nicht angelegt."));
                }

                //assign user to the company
                if (isset($values['company']) && ($values['company'] != -1) && (strlen($values['company']) > 0)) {
                    $compId = $values['company'];
                    Yourdelivery_Model_DbTable_Customer_Company::add(array('email' => $customer->getEmail(), 'budget' => $values['budgetId']), $compId);

                    //make user company admin
                    if ($values['company_admin']) {
                        try {
                            $company = new Yourdelivery_Model_Company($compId);
                            if (!is_null($company)) {
                                if ($customer->makeAdmin($company)) {
                                    $this->success(__b("Der Benutzer wurde als Admin für die Firma ") . $company->getName() . __b(" gesetzt."));
                                } else {
                                    $this->error(__b("Dem Benutzer konnte nicht als Admin für die Firma ") . $company->getName() . __b(" gesetzt werden!"));
                                }
                            }
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->error(__b('Diese Firma existiert nicht!'));
                        }
                    }
                }

                //set user as admin of the restaurant
                if (isset($values['service_admin']) && ($values['service_admin'] != -1)) {
                    $restId = $values['service_admin'];

                    try {
                        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restId);
                        if (!is_null($restaurant)) {
                            if ($customer->makeAdmin($restaurant)) {
                                $this->success(__b("Der Benutzer wurde als Admin für das Restaurant ") . $restaurant->getName() . __b(" gesetzt."));
                            } else {
                                $this->error(__b("Dem Benutzer konnte nicht als Admin für das Restaurant ") . $restaurant->getName() . __b(" gesetzt werden."));
                            }
                        }
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->error(__b("Dieses Restaurant existiert nicht!"));
                    }
                }

                //assign discount to the user
                if (isset($values['discount']) && ($values['discount'] != -1)) {
                    $rabattId = $values['discount'];

                    try {
                        $rabatt = new Yourdelivery_Model_Rabatt_Code(null, $rabattId);
                        if ($customer->setDiscount($rabatt)) {
                            $this->success(__b("Der Rabattcode wurde dem Benutzer zugewiesen."));
                        } else {
                            $this->error(__b("Der Rabattcode konnte dem Benutzer nicht zugewiesen werden!"));
                        }
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->error(__b("Diese Rabattaktion existiert nicht!"));
                    }
                }

                $this->logger->adminInfo(sprintf("Successfully created user %s %s (#%d)", $customer->getPrename(), $customer->getName(), $customer->getId()));

                $this->success(__b("Der Benutzer wurde erfolgreich erstellt!"));
                $this->_redirect('/administration/users');
            } else {
                $this->error($form->getMessages());
            }
        }

        $this->view->assign('p', $post);

        $rabattTable = new Yourdelivery_Model_DbTable_RabattCodes();
        $this->view->assign('rabattIds', $rabattTable->getOnlyCustomersIds());

        $compTable = new Yourdelivery_Model_DbTable_Company();
        $this->view->assign('compIds', $compTable->getDistinctNameId());

        $restTable = new Yourdelivery_Model_DbTable_Restaurant();
        $this->view->assign('restIds', $restTable->getDistinctNameId());
    }

    /**
     * delete user
     * @author alex
     * @todo User Rechte für Restaurant wegnehmen?
     * @modified mlaug 26.10.2011
     */
    public function deleteAction() {
        $request = $this->getRequest();
        $cid = (integer) $request->getParam('id');

        if ($cid > 0) {

            try {
                $customer = new Yourdelivery_Model_Customer($cid);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__b("Could not find customer"));
                return $this->_redirect('/administration/users');
            }

            if ($customer->delete()) {
                $this->success(__b("the customer %s has been removed", $customer->getFullname()));
                $this->logger->adminInfo(sprintf("Successfully deleted user %s %s (#%d)", $customer->getPrename(), $customer->getName(), $customer->getId()));
            } else {
                $this->error(__b("the customer %s could not be removed", $customer->getFullname()));
                $this->logger->adminErr(sprintf("Could not delete user %s %s (#%d)", $customer->getPrename(), $customer->getName(), $customer->getId()));
            }
        } else {
            $this->error(__b("could not find customer with the id %s", $cid));
        }
        
        //redirect to overview
        return $this->_redirect('/administration/users');
    }

}

?>
