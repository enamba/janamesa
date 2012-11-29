<?php

/**
 * Description of EditController
 *
 * @author matthiaslaug
 */
require_once(APPLICATION_PATH . '/controllers/Administration/User/Abstract.php');

class Administration_User_EditController extends Administration_User_Abstract {

    /**
     * edit user data
     * @author alex
     * 
     * @modified Felix Haferkorn <haferkorn@lieferando.de>
     * @since 04.07.2012
     */
    public function indexAction() {
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            $this->_redirect('/administration/users');
        }

        // saving new user data
        if ($request->isPost()) {
            $form = new Yourdelivery_Form_Administration_User_Edit();
            $form->initEmail(true, true, true, false, true, $this->customer->getId());
            
            $post = $request->getPost();

            if (!$form->isValid($post)) {
                // invalid form - showing errors and return without saving
                $this->error($form->getMessages());
                return;
            }

            $origEmail = $this->customer->getEmail();

            $values = $form->getValues();

            // was a new password provided?
            if (isset($values['newpass']) && !empty($values['newpass'])) {
                $values['password'] = md5($values['newpass']);
            }

            // if new email differs from existing, we have to migrate existing fidelity transactions
            if (strtolower($origEmail) != strtolower($values['email'])) {
                // migrate fidelity points / transactions
                $check = $this->customer->getFidelity()->migrateToEmail($values['email']);
                $this->customer->setNewsletter(false);
                if ($check) {
                    $fidelityMigrateMessage = __b('Treuepunkte des Benutzers wurden übertragen.');
                }
            }

            $this->customer->setData($values);
            $this->customer->save();

            $this->success(__b("Änderungen erfolgreich gespeichert.") . $fidelityMigrateMessage);
            $this->logger->adminInfo(sprintf("Succesfully edited user #%d %s", $this->customer->getId(), $this->customer->getFullname()));

            return $this->_redirect('/administration_user_edit/index/userid/' . $this->customer->getId());
        }
        $this->view->assign('orders', Yourdelivery_Model_Order::latestFromCustomer($this->customer->getId()));

        $this->view->assign('customer', $this->customer);
    }

    /**
     * show user data for editing associations
     * @author alex
     */
    public function assocAction() {
        $request = $this->getRequest();

        // data for the view
        //if already an employeer, send only the company, so we don't have to supply everything for the view
        if ($this->customer->isEmployee()) {
            $this->view->assign('employeeIn', $this->customer->getCompany());
        } else {
            $compTable = new Yourdelivery_Model_DbTable_Company();
            $this->view->assign('compIds', $compTable->getDistinctNameId());
        }

        // we need the list of all restaurants
        $restTable = new Yourdelivery_Model_DbTable_Restaurant();
        $this->view->assign('restIds', $restTable->getDistinctNameId());

        $this->view->assign('customer', $this->customer);
    }

    /**
     * manage the fidelity stuff
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.12.2011
     */
    public function fidelityAction() {
        // if the user already has a discount, send only the discount, so we don't have to supply everything for the view
        if (!is_null($this->customer->getDiscount())) {
            $this->view->assign('hasDiscount', $this->customer->getDiscount()->getCode());
        } else {
            $rabattTable = new Yourdelivery_Model_DbTable_RabattCodes();
            $this->view->assign('rabattIds', $rabattTable->getOnlyCustomersIds());
        }

        // assign fidelity transactions
        $this->view->assign('transactions', Yourdelivery_Model_DbTable_Customer_FidelityTransaction::findAllByEmail($this->customer->getEmail()));
        $this->view->assign('customer', $this->customer);
    }

    /**
     * assign new company to the user, add or remove company admin rights
     * @author alex
     */
    public function companyAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            // assign new company to the user, can be only one company
            if ($request->getParam('adddcompany', false)) {
                $post = $request->getPost();

                if (isset($post['company']) && (($post['company']) != -1)) {
                    $compId = $post['company'];
                    try {
                        $company = new Yourdelivery_Model_Company($compId);
                        Yourdelivery_Model_DbTable_Customer_Company::add(array('email' => $this->customer->getEmail(), 'budget' => $post['budgetId']), $compId);

                        $this->logger->adminInfo(sprintf("Successfully associated user %s %s (#%d) with company %s (#%d)", $this->customer->getPrename(), $this->customer->getName(), $this->customer->getId(), $company->getName(), $company->getId()));
                        //make user company admin
                        if ($post['company_admin']) {
                            if (!$this->customer->makeAdmin($company)) {
                                $this->error(__b("Der Benutzer konnte nicht als Admin für die Firma ") . $company->getName() . __b(" gesetzt werden!"));
                            } else {
                                $this->logger->adminInfo(sprintf("Successfully added administration rights for user %s %s (#%d) on company %s (#%d)", $this->customer->getPrename(), $this->customer->getName(), $this->customer->getId(), $company->getName(), $company->getId()));
                            }
                        }
                    }
                    // only Yourdelivery_Model_Company constructor can throw an exception
                    catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->error(__b("Diese Firma existiert nicht!"));
                    }
                }
            }
        }
        // GET request
        else {
            if ($request->getParam('company') != null) {
                try {
                    $company = new Yourdelivery_Model_Company($request->getParam('company'));

                    // delete admin rights
                    if ($request->getParam('delcompadmin', false)) {
                        $this->customer->removeAdmin($company);
                        $this->success(__b("Administratorrechte wurden entfernt!"));
                        $this->logger->adminInfo(sprintf("Successfully removed administration rights for user %s %s (#%d) on company %s (#%d)", $this->customer->getPrename(), $this->customer->getName(), $this->customer->getId(), $company->getName(), $company->getId()));
                    }
                    // add admin rights
                    else if ($request->getParam('addcompadmin', false)) {
                        $this->customer->makeAdmin($company);
                        $this->logger->adminInfo(sprintf("Successfully added administration rights for user %s %s (#%d) on company %s (#%d)", $this->customer->getPrename(), $this->customer->getName(), $this->customer->getId(), $company->getName(), $company->getId()));
                        $this->success(__b("Administratorrechte wurden zugewiesen!"));
                    }
                    // delete the association of the user with this company
                    else if ($request->getParam('delcomp', false)) {
                        $assoc = Yourdelivery_Model_DbTable_Customer_Company::findByCustomerId($this->customer->getId());
                        if (is_array($assoc)) {
                            Yourdelivery_Model_DbTable_Customer_Company::remove($assoc['id']);
                            $this->customer->removeAdmin($company);
                            $this->logger->adminInfo(sprintf('Successfully removed association of user %s %s (#%d) with company %s (#%d)', $this->customer->getPrename(), $this->customer->getName(), $this->customer->getId(), $company->getName(), $company->getId()));
                        }
                    }
                }
                // only Yourdelivery_Model_Company constructor can throw an exception
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error(__b("Diese Firma existiert nicht!"));
                }
            }
        }

        return $this->_redirect('/administration_user_edit/assoc/userid/' . $this->customer->getId());
    }

    /**
     * add or remove discount for the customer
     * @author alex
     */
    public function discountAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();
            $discountID = $post['discountId'];

            // assign discount to the user, can be only one discount for user
            if (!is_null($discountID) && ($discountID != -1)) {
                $post = $request->getPost();

                try {
                    $rabatt = new Yourdelivery_Model_Rabatt_Code(null, $discountID);
                    if (!$this->customer->setDiscount($rabatt)) {
                        $this->error(__b("Der Rabattcode konnte dem Benutzer nicht zugewiesen werden!"));
                    } else {
                        $this->logger->adminInfo(sprintf("Successfully added discount %s (#%d) for user %s %s (#%d)", $rabatt->getName(), $rabatt->getId(), $this->customer->getPrename(), $this->customer->getName(), $this->customer->getId()));
                    }
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error(__b("Diese Rabattaktion existiert nicht!"));
                }
            } else {
                $this->error(__b("Bitte wählen Sie eine Rabattaktion!"));
            }
        }
        //send request, so it can only be remove discount link
        else {
            if ($request->getParam('deldiscount', false)) {
                $this->customer->removeDiscount();
                $this->logger->adminInfo(sprintf("Successfully deleted discount for user %s %s (#%d)", $this->customer->getPrename(), $this->customer->getName(), $this->customer->getId()));
            }
        }

        return $this->_redirect('/administration_user_edit/fidelity/userid/' . $this->customer->getId());
    }

    /**
     * add or remove admin rights for the restaurant
     * @author alex
     */
    public function restaurantAction() {
        $request = $this->getRequest();

        if ($request->isPost() && $this->getRequest()->getParam('assignadmin', false)) {
            $post = $request->getPost();

            $ids = $post['restCheckbox'];

            if (sizeof($ids) > 0) {
                foreach ($ids as $restaurantID => $val) {
                    $restaurantTable = new Yourdelivery_Model_DbTable_Restaurant();

                    if (count($restaurantTable->findById($restaurantID)) < 1) {
                        $this->error(__b("Restaurant mit id %d existiert nicht!", $restaurantID));
                    } else {
                        if (!$this->customer->addRight('r', $restaurantID)) {
                            $this->error(__b("Fehler beim Setzen der Berechtigung fürs Restaurant mit id %d!", $restaurantID));
                        } else {
                            $this->logger->adminInfo(sprintf("Successfully added administration right for user %s %s (#%d) at restaurant #%d", $this->customer->getPrename(), $this->customer->getName(), $this->customer->getId(), $restaurantID));
                            $this->success(__b("Berechtigung für das Restaurant mit id %d wurde gesetzt!", $restaurantID));
                        }
                    }
                }
            }

            return $this->_redirect('/administration_user_edit/assoc/userid/' . $request->getParam('userId') . '');
        }
        // delete admin rights for the restaurant
        else if ($request->getParam('delrest', false)) {
            $restaurantID = $request->getParam('delrest');

            $refTable = new Yourdelivery_Model_DbTable_Restaurant();
            if (count($refTable->findById($restaurantID)) < 1) {
                $this->error(__b("Dieses Restaurant existiert nicht!"));
            } else {
                if (!$this->customer->delRight('r', $restaurantID)) {
                    $this->error(__b("Fehler beim Löschen der Berechtigung!"));
                } else {
                    $this->logger->adminInfo(sprintf("Successfully deleted administration right for user %s %s (#%d) at restaurant #%d", $this->customer->getPrename(), $this->customer->getName(), $this->customer->getId(), $restaurantID));
                }
            }
            return $this->_redirect('/administration_user_edit/assoc/userid/' . $request->getParam('userId') . '/#restaurant');
        }
    }

    /**
     * manage user addresses
     * @author alex
     */
    public function locationAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            // add new address to the list on user locations
            if ($request->getParam('addadress', false)) {
                $form = new Yourdelivery_Form_Administration_User_Addlocation();

                if ($form->isValid($post)) {
                    $values = $form->getValues();

                    // if we are in Brasil, create city based on plz
                    if (strpos($this->config->domain->base, "janamesa") !== false) {
                        $cityByPLz = Yourdelivery_Model_City::getByPlz($values['plz']);
                        // we take the first one, beacuse we have only one city entry per plz in Brazil
                        $c = $cityByPLz[0];

                        if (is_null($c)) {
                            $this->error(__b("Diese PLZ existiert nicht!"));
                            $this->_redirect('/administration_user_edit/location/userid/' . $this->customer->getId());
                        }

                        $values['cityId'] = $c['id'];
                    } else {
                        $city = new Yourdelivery_Model_City($values['cityId']);
                        $values['plz'] = $city->getPlz();
                    }

                    $values['company'] = '';
                    $location = new Yourdelivery_Model_Location();
                    $location->setData($values);
                    $location->setCustomerId($this->customer->getId());
                    $location->save();
                    $this->success(__b("Neue Adresse wurde gespeichert"));
                    $this->logger->adminInfo(sprintf("Successfully created new address (%s %s) for user %s %s (#%d)", $values['street'], $values['hausnr'], $this->customer->getPrename(), $this->customer->getName(), $this->customer->getId()));
                } else {
                    $this->error($form->getMessages());
                }
            }
            // edit address
            else if ($request->getParam('editaddress', false)) {

                try {
                    $location = new Yourdelivery_Model_Location((integer) $request->getParam('locationid'));
                    $city = new Yourdelivery_Model_City((integer) $post['cityId']);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error(__b("location or city could not be found"));
                    return $this->_redirect('/administration_user_edit/location/userid/' . $this->customer->getId());
                }

                // if we are in Brasil, create city based on plz
                if (strpos($this->config->domain->base, "janamesa") !== false) {
                    $cityByPLz = Yourdelivery_Model_City::getByPlz($post['plz']);
                    // we take the first one, beacuse we have only one city entry per plz in Brazil
                    $c = $cityByPLz[0];

                    if (is_null($c)) {
                        $this->error(__b("Diese PLZ existiert nicht!"));
                        $this->_redirect('/administration_user_edit/location/userid/' . $this->customer->getId());
                    }

                    $post['cityId'] = $c['id'];
                } else {
                    $city = new Yourdelivery_Model_City($post['cityId']);
                    $post['plz'] = $city->getPlz();
                }

                $location->setData($post);
                $location->save();
                $this->logger->adminInfo(sprintf("Successfully edited address (%s %s) for user %s %s (#%d)", $location->getStreet(), $location->getHausnr(), $this->customer->getPrename(), $this->customer->getName(), $this->customer->getId()));
                $this->success(__b("Die Adresse wurde bearbeitet"));
            }
            // delete addess from the list on user locations
            else if ($request->getParam('deleteaddress', false)) {
                $lid = $post['locationId'];
                if (Yourdelivery_Model_DbTable_Locations::remove($lid) == 0) {
                    $this->success(__b("Keine Adresse wurde gelöscht"));
                } else {
                    $this->logger->adminInfo(sprintf("uccessfully deleted address #%d for user %s %s (#%d)", $lid, $this->customer->getPrename(), $this->customer->getName(), $this->customer->getId()));
                    $this->success(__b("Adresse wurde gelöscht"));
                }
            }
        }
    }

    /**
     * test if this mail already exists in the customers table
     * @author alex
     */
    public function testmailAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            $email = $request->getParam('email', null);

            if (strlen(trim($email)) == 0) {
                echo 0;
                return;
            }

            try {
                $user = new Yourdelivery_Model_Customer(null, $email);
                // this email is already registered in the customers table
                if ($user->getId() != 0) {
                    echo "<a href=\"/administration_user_edit/userid/" . $user->getId() . "\">" . $user->getPrename() . " " . $user->getName() . "</a>";
                }
                // this email is not registered in contacts table
                else {
                    echo 0;
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                echo 0;
            }
        }
    }

    /**
     * manage the crm tickets
     * @author alex
     * @since 12.07.2011
     */
    public function crmAction() {
        // get grid and deploy it to view
        $this->view->grid = Yourdelivery_Model_Crm_Ticket::getGrid('customer', $this->customer->getId());
    }

}

?>
