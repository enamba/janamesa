<?php


/**
 * Description of InfoController
 *
 * @author matthiaslaug
 */
class Administration_Service_EditController extends Default_Controller_AdministrationBase {

    protected $id = null;
    protected $service = null;

    public function init() {

        parent::init();

        $this->id = (integer) $this->getRequest()->getParam('id');
        if ($this->id <= 0) {
            $this->error(__b("Keine ID übergeben"));
            $this->logger->adminWarn(sprintf("calling edit action for service without valid id"));
            $this->_redirect('/administration/services');
        }

        //create restaurant object
        try {
            $this->service = new Yourdelivery_Model_Servicetype_Restaurant($this->id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Dieses Restaurant existiert nicht!"));
            $this->_redirect('/administration/services');
        }

        //test cityId
        if (intval($this->service->getCityId()) <= 0) {
            $this->error(__b("Keine PLZ wurde für das Restaurant angegeben, bitte reden Sie mit der IT"));
            $this->_redirect('/administration/services');
        }

        // to avoid known php bug in loading jpg images
        // http://bugs.php.net/29878
        // 0 is the default behavior
        ini_set('gd.jpeg_ignore_warning', 1);

        $this->view->assign('restaurant', $this->service);
        $this->view->assign('service', $this->service);
        $this->view->assign('comments', Yourdelivery_Model_DbTable_Restaurant_Notepad::getComments($this->id));
        $this->view->assign('balance', $this->service->getBalanceOfBillings());
    }

    /**
     * show forms for editing service and edit service on post request
     * @author alex
     * @modified daniel
     * @since 13.01.2011
     */
    public function indexAction() {
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect(sprintf('/administration_service_edit/index/id/%s', $this->_request->id));
        }

        // ugly hack to avoid users in certain admin group from editing online restaurants
        $admin = $this->session->admin;

        if ($admin->hasGroup('Dienstleister')) {
            if ($this->service->getIsOnline() == 1) {
                $this->error(__b("Sie haben leider keine Berechtigung, online Dienstleister zu bearbeiten"));
                $this->_redirect('/administration/services');
            }
        }

        if ($request->isPost()) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Administration_Service_Edit();
            if (( (strcmp($post['notify'], 'email') == 0) || (strcmp($post['notify'], 'all') == 0) ) && (strlen(trim($post['email'])) == 0)) {
                $this->logger->error(sprintf('no email given for service #%d, but orders should be send out via email. we do not allow that', $this->service->getId()));
                $this->error(__b("Bitte geben Sie eine Email Adresse, wenn der Dienstleister über Email benachrichtigt werden soll"));
                $this->view->assign('p', $post);
            } else if (count(array_unique(array(trim($post['restUrl']), trim($post['caterUrl']), trim($post['greatUrl'])))) < 3) {
                $this->logger->error(sprintf('no unique url given for service #%d', $this->service->getId()));
                $this->error(__b("Alle URL müssen eindeutig sein, keine URL darf mehrfach eingetragen werden"));
                $this->view->assign('p', $post);
            } else if ($form->isValid($post)) {
                //save new restaurant data
                $values = $form->getValues();
                $values['chargeStart'] = strtotime($values['chargeStart']) ? date(DATE_DB, strtotime($values['chargeStart'])) : null;

                // if we are in Brasil, create city based on plz
                if (strpos($this->config->domain->base, "janamesa") !== false) {
                    $cityByPLz = Yourdelivery_Model_City::getByPlz($values['plz']);
                    // we take the first one, beacuse we have only one city entry per plz in Brazil
                    $c = $cityByPLz[0];

                    if (is_null($c)) {
                        $this->error(__b("Diese PLZ existiert nicht!"));
                        $this->_redirect('/administration_service_edit/index/id/' . $this->service->getId());
                    }

                    $values['cityId'] = $c['id'];
                } else {
                    $city = new Yourdelivery_Model_City($values['cityId']);
                    $values['plz'] = $city->getPlz();
                }

                if ($values['noNotification'] == 1) {
                    $values['notifyPayed'] = -1;
                }

                if ($values['isOnline'] == 1) {
                    $values['status'] = 0;
                }

                // if restaurant accepts only cash, set paymentbar field manually, because in this case
                // the checkbox is inactive and the value can't be read
                if ($values['onlycash'] == 1) {
                    $values['paymentbar'] = 1;
                }

                // log if some relevant data was changed
                if (strcmp($values['name'], $this->service->getName()) != 0) {
                    $this->logger->adminInfo(sprintf("Name was changed for restaurant %s (%d)", $this->service->getName(), $this->service->getId()));
                }

                if (strcmp($values['description'], $this->service->getDescription()) != 0) {
                    $this->logger->adminInfo(sprintf("Description was changed for restaurant %s (%d)", $this->service->getName(), $this->service->getId()));
                }

                if (strcmp($values['specialComment'], $this->service->getSpecialComment()) != 0) {
                    $this->logger->adminInfo(sprintf("Special comment (public comment) was changed for restaurant %s (%d)", $this->service->getName(), $this->service->getId()));
                }

                if (strcmp($values['statecomment'], $this->service->getStatecomment()) != 0) {
                    $this->logger->adminInfo(sprintf("State comment (intern comment) was changed for restaurant %s (%d)", $this->service->getName(), $this->service->getId()));
                }

                // make nice url
                $values['restUrl'] = Default_Helpers_Web::urlify($values['restUrl']);
                $values['caterUrl'] = Default_Helpers_Web::urlify($values['caterUrl']);
                $values['greatUrl'] = Default_Helpers_Web::urlify($values['greatUrl']);

                // set this restaurant to the top of the list until $topUntil
                if ($this->session->admin->isAdmin()) {
                    $values['topUntil'] = empty($values['topUntil']) ? null : date('Y-m-d', strtotime($values['topUntil']));
                } else {
                    if (!empty($values['topUntil'])) {
                        $this->error(__b("Leider haben Sie nicht die Berechtigung die Dienstleister hoch zu stellen"));
                    }
                    unset($values['topUntil']);
                }



                $currentStatus = $this->service->isOnline();
                $currentOfflineStatus = $this->service->getStatus();


                if (!$this->service->checkOldUrls($values['restUrl'], $values['caterUrl'], $values['greatUrl'])) {
                    unset($values['restUrl']);
                    unset($values['caterUrl']);
                    unset($values['greatUrl']);
                    $this->error(__b('Restaurant Url konnte nicht gespeichert werden, da ein Konflikt mit der History existiert.  Bitte kontaktieren Sie die IT.'));
                };



                $this->service->setData($values);

                $franchiseTypeId = $values['franchiseTypeId'];

                if ($franchiseTypeId < 0) {
                    if (!$values['franchiseName']) {
                        $this->error(__b("Name für das neue Franchise fehlt, Franchise wurde nicht erstellt"));
                    } else {
                        try {
                            $franchise = new Yourdelivery_Model_Servicetype_Franchise();
                            $this->service->setFranchiseTypeId($franchise->setFranchise($values['franchiseName']));
                            $this->success(__b("Franchise wurde erstellt"));
                            $this->logger->adminInfo(sprintf("New franchise %s created", $values['franchiseName']));
                        } catch (Exception $e) {
                            $this->logger->crit($e->getMessage());
                            $this->error(__b("Franchise konnte nicht angelegt werden"));
                            return $this->_redirect('/administration/services');
                        }
                    }
                }
                // uncache must always be called before save() to delete the correct link-files.
                // If restaurant stays offline, no file are there and uncache has no effect
                $this->service->uncache();
                $this->service->save();

                $offlineStati = Yourdelivery_Model_Servicetype_Abstract::getStati();

                $newStatus = $this->service->isOnline();
                $newOfflineStatus = $this->service->getStatus();

                //if some reason for status change was given, write it to the restaurant notepad
                if ((intval($values['isOnline']) == 0) && ($currentOfflineStatus != $newOfflineStatus)) {
                    $admin = $this->session->admin;

                    if (is_null($admin)) {
                        $this->error(__b("Kein Admin wurde in der Sitzung gefunden, kann die Begründung für die Statusänderung nicht eintragen"));
                    } else {

                        $comment = new Yourdelivery_Model_Servicetype_RestaurantNotepad();
                        $comment->setMasterAdmin(1);
                        $comment->setAdminId($admin->getId());

                        $comment->setRestaurantId($this->service->getId());
                        $comment->setComment(__b("[offline status gesetzt: '%s']. Begründung: %s", $offlineStati[$values['status']], trim($values['offline-change-reason-text'])));
                        $comment->setTime(date("Y-m-d H:i:s", time()));
                        $comment->save();
                    }
                } else if (($currentStatus == 0) && (intval($values['isOnline']) == 1)) {
                    $admin = $this->session->admin;

                    if (is_null($admin)) {
                        $this->error(__b("Kein Admin wurde in der Sitzung gefunden, kann die Begründung für die Statusänderung nicht eintragen"));
                    } else {
                        $comment = new Yourdelivery_Model_Servicetype_RestaurantNotepad();
                        $comment->setMasterAdmin(1);
                        $comment->setAdminId($admin->getId());

                        $comment->setRestaurantId($this->service->getId());
                        $comment->setComment(__b("Online gestellt"));
                        $comment->setTime(date("Y-m-d H:i:s", time()));
                        $comment->save();
                    }
                }

                //log if admin changed the status
                if ($currentStatus != $newStatus) {
                    // if setting restaurant online, clear cache for all deliver ranges of this restaurant
                    if ($newStatus == 1) {
                        $this->service->uncacheRanges();
                    }

                    // if restaurant is set offline or online
                    $this->logger->adminInfo(sprintf("Status of %s (#%d) changed from %s to %s", $this->service->getName(), $this->service->getId(), $currentStatus == 1 ? "online" : "offline", $newStatus == 1 ? "online" : "offline"));
                    //tracking status change
                    $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::SERVICE_STATUS_CHANGE, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_SERVICE, $this->service->getId());
                }

                // state changed
                if ((intval($newStatus) == 0) && ($currentOfflineStatus != $newOfflineStatus)) {
                    $this->logger->adminInfo(sprintf("Offline status of %s (%s) changed from %s to %s. Reason: %s", $this->service->getName(), $this->service->getId(), $offlineStati[$currentOfflineStatus], $offlineStati[$newOfflineStatus], trim($values['offline-change-reason-text'])));
                }

                //log status change
                if ($currentOfflineStatus != $newOfflineStatus) {
                    Yourdelivery_Model_DbTable_Restaurant_StatusHistory::logStatusChange((int) $newOfflineStatus, (int) $currentOfflineStatus);
                }

                $this->logger->adminInfo(sprintf("Successfully edited service %s (%s)", $this->service->getName(), $this->service->getId()));
                //tracking edit
                $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::SERVICE_EDIT, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_SERVICE, $this->service->getId());


                $this->success(__b("Dienstleister erfolgreich bearbeitet"));
                $this->_redirect('/administration_service_edit/index/id/' . $this->service->getId());
            } else {
                $this->error($form->getMessages());
            }
        }
        $this->view->assign('robots', array(
            'index,follow' => "index,follow",
            'index,nofollow' => "index,nofollow",
            'noindex,nofollow' => "noindex,nofollow",
            'noindex,follow' => "noindex,follow",
        ));

        $openings = array();
        foreach ($this->service->getRegularOpenings() as $opening) {
            $openings[$opening->day][] = array('from' => $opening->from, 'until' => $opening->until);
        }

        $this->view->assign('franchisetypes', Yourdelivery_Model_Servicetype_Franchise::all());
        $this->view->assign('categories', Yourdelivery_Model_Servicetype_Categories::All());
        $this->view->openings = $openings;
    }

    /**
     * forms for editing contacts
     * @author alex
     * @since 13.11.2011
     */
    public function contactsAction() {
        $request = $this->getRequest();

        $contTable = new Yourdelivery_Model_DbTable_Contact();
        $this->view->assign('contacts', $contTable->getDistinctNameId());
        $this->view->assign('restaurant', $this->service);
    }

    /**
     * forms for editing service associations
     * @author alex
     * @since 13.01.2011
     */
    public function assocAction() {
        $request = $this->getRequest();

        $billingParentId = $this->service->getBillingParentId();
        if (!is_null($billingParentId)) {
            try {
                $billingParent = new Yourdelivery_Model_Servicetype_Restaurant($billingParentId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__b("Service-Typ konnte nicht erstellt werden"));
                $this->_redirect('/administration/services');
            }

            $this->view->assign('billingParent', $billingParent);
        }

        $compTable = new Yourdelivery_Model_DbTable_Company();
        $this->view->assign('compIds', $compTable->getDistinctNameId());

        $courierTable = new Yourdelivery_Model_DbTable_Courier();
        $this->view->assign('courierIds', $courierTable->getDistinctNameId());

        $salersTable = new Yourdelivery_Model_DbTable_Salesperson();
        $this->view->assign('salespersons', $salersTable->getDistinctNameId());

        $this->view->assign('restaurant', $this->service);

        $companiesAssoc = array();
        foreach ($this->service->getCompanyRestrictions() as $assoc) {
            $c = new Yourdelivery_Model_Company($assoc['companyId']);
            $cdata = $c->getData();
            $cdata['exclusive'] = $assoc['exclusive'];
            $companiesAssoc[] = $cdata;
        }

        $this->view->assign('compAssoc', $companiesAssoc);
        $this->view->assign('billing_children', $this->service->getBillingChildren());
        $this->view->assign('courier', $this->service->getCourier());

        $salesperson = $this->service->getSalesperson();
        if (!is_null($salesperson)) {
            $contract = $salesperson->getContractForRestaurant($this->service->getId());
            $this->view->assign('contract', $contract);
        }
        $this->view->assign('salesperson', $salesperson);

        $restTable = new Yourdelivery_Model_DbTable_Restaurant();
        $this->view->assign('restIds', $restTable->getDistinctNameIdForMerge());

        $this->view->assign('printer', $this->service->getSmsPrinter());
    }

    /**
     * forms for editing service categories pictures
     * @author alex
     */
    public function piccategoriesAction() {
        $request = $this->getRequest();

        $this->view->assign('restaurant', $this->service);

        $piccatTable = new Yourdelivery_Model_DbTable_Category_Picture();
        $this->view->assign('picCat', $piccatTable->getIdsNames());
    }

    /**
     * load and remove documents for this restaurant
     * @author alex
     * @since 13.01.2011
     */
    public function documentsAction() {

    }

    /**
     * set password for this restaurant and send email with pasword on his address
     * @author Alex Vait <vait@lieferando.de>
     * @since 04.04.2012
     */
    public function setpasswordAction() {
        $request = $this->getRequest();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $form = new Yourdelivery_Form_Administration_Service_Password();
            if ($form->isValid($data)) {
                $values = $form->getValues();

                $this->service->setPassword(md5($values['password']));
                $this->service->save();

                if (strlen(trim($this->service->getEmail()))>0) {
                    // Using config-based locale during composing and sending e-mail
                    $this->_restoreLocale();
                    $email = new Yourdelivery_Sender_Email();
                    $email->setSubject(__b("Ihr neues Passwort für das Lieferando-Konto"))
                        ->setBodyHtml(__b("Ihr neues Passwort für das Lieferando-Konto lautet: %s<br /><br /><br />Ihr Lieferando-Team.", $values['password']))
                        ->addTo($this->service->getEmail());
                    $isEmailSent = $email->send('system');
                    $this->_overrideLocale();

                    if ($isEmailSent) {
                        $this->success(__b("Email mit Passwort wurde an den Dienstleister verschickt"));
                        $this->logger->adminInfo(sprintf("Password was send to restaurant #%d per email", $this->service->getId()));
                    }
                    else{
                        $this->error(__b("Email mit Passwort konnte nicht an den Dienstleister verschickt werden"));
                        $this->logger->adminInfo(sprintf("Password could not be send to restaurant #%d per email", $this->service->getId()));
                    }
                }
                else {
                    $this->error(__b("Email mit Passwort konnte nicht an den Dienstleister verschickt werden, weil der Dienstleister keine Email Adresse hat! Bitte das Passwort dem Dienstleister auf anderen Wegen zukommen lassen."));
                }

                $this->success(__b("Passwort erfolgreich gespeichert"));
                $this->logger->adminInfo(sprintf("Password for restaurant #%d was set", $this->service->getId()));
                $this->_redirect('/administration_service_edit/index/id/' . $this->service->getId());
            }
            else {
                $this->error($form->getMessages());
                $this->_redirect('/administration_service_edit/setpassword/id/' . $this->service->getId());
            }
        }
    }

    /**
     * show forms for editing service additional comissions
     * @author alex
     * @since 13.01.2011
     */
    public function comissionsAction() {
        $this->view->assign('commissions', Yourdelivery_Model_DbTable_Restaurant_Commission::getAdditionalCommissions($this->service->getId()));
    }

    /**
     * @author mlaug
     * @since 07.08.2010
     */
    public function billingAction() {
        $request = $this->getRequest();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $form = new Yourdelivery_Form_Administration_Billing_Customized();
            if ($form->isValid($data)) {
                $cleanData = $form->getValues();
                $this->service->getBillingCustomized()
                        ->setData($cleanData)
                        ->save();
                $this->success(__b("Daten erfolgreich gespeichert"));
                $this->logger->adminInfo(sprintf("Billing data for restaurant #%d was edited", $this->service->getId()));
            }
            else {
                $this->error($form->getMessages());
            }
        }

        $this->view->customized = $this->service->getBillingCustomizedData();
    }

    /**
     * @author mlaug
     * @since 10.06.2011
     */
    public function balanceAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();
            $form = new Yourdelivery_Form_Administration_Service_Balance();
            if ($form->isValid($post)) {
                $balance = new Yourdelivery_Model_Billing_Balance();
                $balance->setObject($this->service);
                $sign = (integer) $form->getValue('sign');

                if ($sign == '0') {
                    $this->error(__b("please select a type"));
                } else {
                    $balance->addBalance($sign * $form->getValue('amount'), $form->getValue('comment'));
                    $this->logger->adminDebug(sprintf("adding balance %s to %s", $form->getValue('amount'), $this->service->getId()));
                    $this->success(__b("balance succesfully added"));
                }
            } else {
                $this->error($form->getMessages());
            }
        }

        //redirect and mark balance-div to be open
        return $this->_redirect('/administration_service_edit/index/id/' . $this->service->getId() . '#balance-div');
    }

    /**
     * create os tickets according to the new offline status
     * @since 23.02.2011
     * @author alex
     */
    protected function manageOsTickets($service, $newOfflineStatus, $reason, $offlineStatusUntil) {
        // Using config-based locale during composing and sending e-mail
        $this->_restoreLocale();
        switch ($newOfflineStatus) {

            // "momentan kein Fahrer"
            case 5:
                Yourdelivery_Sender_Email::osTicket(
                        "Sebastian Ohrmann", __b("Dienstleister %s (%d) wurde offline gesetzt: momentan kein Fahrer bis voraussichtlich %s", $service->getName(), $service->getId(), $offlineStatusUntil), __b("Dienstleister %s (%d) wurde offline gesetzt: momentan kein Fahrer bis voraussichtlich %s. Notiz: %s", $service->getName(), $service->getId(), $offlineStatusUntil, $reason));
                break;


            // "akzeptiert keine Bargeldlose Bezahlung"
            case 6:
                Yourdelivery_Sender_Email::osTicket(
                        "Jean-Pierre Giannakoulopoulos", __b("Dienstleister %s (%d) wurde offline gesetzt: akzeptiert keine Bargeldlose Bezahlung", $service->getName(), $service->getId()), __b("Dienstleister %s (%d) wurde offline gesetzt: akzeptiert keine Bargeldlose Bezahlung. Notiz: %s", $service->getName(), $service->getId(), $reason));
                break;



            // "defizientes Faxgerät"
            case 7:
                Yourdelivery_Sender_Email::osTicket(
                        "Sebastian Ohrmann", __b("Dienstleister %s (%d) wurde offline gesetzt: defizientes Faxgerät bis voraussichtlich %s", $service->getName(), $service->getId(), $offlineStatusUntil), __b("Dienstleister %s (%d) wurde offline gesetzt: defizientes Faxgerät bis voraussichtlich %s. Notiz: %s", $service->getName(), $service->getId(), $offlineStatusUntil, $reason));
                break;


            // "ready to check"
            case 9:
                Yourdelivery_Sender_Email::osTicket(
                        "Sebastian Ohrmann", __b("Dienstleister %s (%d) ist fertig, bitte checken", $service->getName(), $service->getId()), __b("Dienstleister %s (%d) ist im Status 'ready to check'. Notiz: %s", $service->getName(), $service->getId(), $reason));
                break;


            // "gekündigt"
            case 11:
                Yourdelivery_Sender_Email::osTicket(
                        "Jean-Pierre Giannakoulopoulos", __b("Dienstleister %s (%d) wurde offline gesetzt: gekündigt", $service->getName(), $service->getId()), __b("Dienstleister %s (%d) wurde offline gesetzt: gekündigt. Notiz: %s", $service->getName(), $service->getId(), $reason));
                break;

            // "Urlaub"
            case 12:
                Yourdelivery_Sender_Email::osTicket(
                        "Sebastian Ohrmann", __b("Dienstleister %s (%d) wurde offline gesetzt: Urlaub bis %s", $service->getName(), $service->getId(), $offlineStatusUntil), __b("Dienstleister %s (%d) wurde offline gesetzt: Urlaub bis %s. Notiz: %s", $service->getName(), $service->getId(), $offlineStatusUntil, $reason));
                break;


            // "warten mit Freischaltung"
            case 13:
                Yourdelivery_Sender_Email::osTicket(
                        "Sebastian Ohrmann", __b("Dienstleister %s (%d) wurde offline gesetzt: warten mit Freischaltung", $service->getName(), $service->getId()), __b("Dienstleister %s (%d) wurde offline gesetzt: warten mit Freischaltung. Notiz: %s", $service->getName(), $service->getId(), $reason));
                break;


            // "Inhaberwechsel"
            case 14:
                Yourdelivery_Sender_Email::osTicket(
                        "Jean-Pierre Giannakoulopoulos", __b("Dienstleister %s (%d) wurde offline gesetzt: Inhaberwechsel bis voraussichtlich %s", $service->getName(), $service->getId(), $offlineStatusUntil), __b("Dienstleister %s (%d) wurde offline gesetzt: Inhaberwechsel bis voraussichtlich %s. Notiz: %s", $service->getName(), $service->getId(), $offlineStatusUntil, $reason));
                break;

            // "Karten update"
            case 20:
                Yourdelivery_Sender_Email::osTicket(
                        "Sebastian Ohrmann", __b("Dienstleister %s (%d) wurde offline gesetzt: Karten update", $service->getName(), $service->getId()), __b("Dienstleister %s (%d) wurde offline gesetzt: Karten update. Notiz: %s", $service->getName(), $service->getId(), $reason));
                break;


            default:
                break;
        }
        $this->_overrideLocale();
    }

    /**
     * manage the crm tickets
     * @author alex
     * @since 12.07.2011
     */
    public function crmAction() {
        // get grid and deploy it to view
        $this->view->grid = Yourdelivery_Model_Crm_Ticket::getGrid('service', $this->service->getId());
    }

    /**
     * show forms for editing service logo
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.12.2011
     */
    public function logoAction() {
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration/services');
        }

        if ($request->isPost()) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Administration_Service_EditLogo();
            //// need to do it to check the logo data
            $form->getValues();

            if (!$form->img->isUploaded()) {
                $this->error(__b("Bitte gib ein Bild an"));
                return;
            }

            if ($form->isValid($post) && $form->img->isUploaded()) {
                $this->service->setImg($form->img->getFileName());
                $this->success('Logo wurde hochgeladen');
                return $this->_redirect('/administration_service_edit/logo/id/' . $this->service->getId());
            }
        }
    }


    /**
     * select the available payments for this service
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 20.03.2012
     */
    public function paymentsAction() {
        
        $form = new Yourdelivery_Form_Administration_Service_Payments();
        $form->setAction(sprintf('/administration_service_edit/payments/id/%d', $this->service->getId()));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $values = $form->getValues();
                
                $this->service->removePayments();
                foreach ($values['payments'] as $paymentName => $paymentValue) {
                    if (!in_array($paymentValue, array(0, 1))) {
                        continue;
                    }
                    
                    $payment = new Yourdelivery_Model_Servicetype_Payment();
                    $payment->setPayment($paymentName);
                    $payment->setStatus($paymentValue);
                    
                    if ($values['default'] == $paymentName && $paymentValue) {
                        $payment->setDefault(1);
                    }
                    
                    $this->service->addPayment($payment);
                }
            }
        }
        else {
            $defaults = array(
                'payments' => array()
            );

            $payments = $this->service->getPayments();
            foreach ($payments as $payment) {
                $defaults['payments'][$payment->getPayment()] = $payment->getStatus();
                
                if ($payment->getDefault()) {
                    $defaults['default'] = $payment->getPayment();
                }
            }

            $form->populate($defaults);
        }

        $this->view->form = $form;
    }


    /**
     * show all notes about the restaurant and add new
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 20.06.2012
     */
    public function notepadAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            if (strlen(trim($post['comment'])) > 0) {
                $admin = $this->session->admin;

                if ( is_null($admin) ) {
                    $this->error(__b("Kein Admin wurde in der Session gefunden"));
                    $this->_redirect('/administration_service_edit/notepad/id/' . $this->service->getId());
                }

                $comment = new Yourdelivery_Model_Servicetype_RestaurantNotepad();
                $comment->setMasterAdmin(1);
                $comment->setAdminId($admin->getId());
                $comment->setRestaurantId($this->service->getId());
                $comment->setComment(htmlspecialchars($post['comment']));
                $comment->setTime(date("Y-m-d H:i:s", time()));

                try {
                    $comment->save();
                    $this->success(__b('Kommentar wurde gespeichert.'));
                }
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error(__b("Kommentar konnte nicht gespeichert werden."));
                }
            }
            else {
                $this->error(__b("Bitte schreiben sie ein Kommentar"));
            }

            $this->_redirect('/administration_service_edit/notepad/id/' . $this->service->getId());
        }
    }

}

?>
