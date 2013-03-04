<?php

/**
 * Description of UserController
 *
 * @author mlaug
 */
class Request_UserController extends Default_Controller_RequestBase {

    /**
     * @todo rework, that we do not need session here
     * @author vpriem
     * @since 14.02.2011
     * @modified afrank 11.11.11
     */
    public function addfavouriteAction() {
        $customer = $this->getCustomer();

        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        
        $hash = $this->getRequest()->getParam('id');
        $order = Yourdelivery_Model_Order::createFromHash($hash);
        if (Yourdelivery_Model_DbTable_Favourites::findByOrderId($order->getId())) {
            echo json_encode(array('error' => __('Diese Bestellung ist bereits als Favorit markiert')));
            return;
        }

        if ($order->addToFavorite($customer)) {
            $this->logger->info(sprintf('customer #%s %s successfully added order #%s to favourite', $customer->getId(), $customer->getFullname(), $order->getId()));
            echo json_encode(array('success' => __('Favorit erfolgreich angelegt')));
            return;
        }

        $this->logger->warn(sprintf('could not create favourite for order #%s', $order->getId()));
        echo json_encode(array('error' => __('Favorit konnte nicht angelegt werden')));
        return;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 18.11.2011
     */
    public function delfavouriteAction() {
        $request = $this->getRequest();
        $hash = $request->getParam('id');
        $restaurantId = $request->getParam('restId');
        $customer = $this->getCustomer();

        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        if ($hash !== null) {
            $order = Yourdelivery_Model_Order::createFromHash($hash);
            $favourite = Yourdelivery_Model_DbTable_Favourites::findByOrderAndCustomerId($order->getId(), $customer->getId());

            // if favourite exists we delete it
            if ($favourite) {
                try {
                    $order = new Yourdelivery_Model_Order($favourite['orderId']);
                    if (($order->getCustomerId() == $customer->getId()) || (strcmp($order->getCustomer()->getEmail(), $customer->getEmail()) == 0)) {
                        $order->deleteFromFavorite();
                        $this->logger->info(sprintf('customer #%s %s successfully deleted favourite #%s by hash', $customer->getId(), $customer->getFullname(), $favourite['id']));
                        echo json_encode(array('success' => __('Favorit erfolgreich gelöscht')));
                        return;
                    } else {

                        $this->logger->warn(sprintf('favourite #%s (orderId #$s) could not be deleted by hash because customers (actual: %s - order: %s) do not match', $order->getId(), $customer->getFullname(), $order->getCustomer()->getFullname(), $favourite['id']));
                        echo json_encode(array('error' => __('Favorit konnte nicht gelöscht werden')));
                        return;
                    }
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->logger->err(sprintf('favorite could not be deleted  by hash, because %s', $e->getMessage()));
                    echo json_encode(array('error' => __('Favorit konnte nicht gelöscht werden')));
                    return;
                }
            }
        } elseif ($restaurantId !== null && $customer->getId() !== null) {

            try {
                $rows = Yourdelivery_Model_DbTable_Favourites::removeAll($restaurantId, $customer->getId());

                if( $rows == 1){
                    $this->logger->info(sprintf('customer #%s %s successfully deleted favourite by restaurantId #%s', $customer->getId(), $customer->getFullname(), $restaurantId));
                    echo json_encode(array('success' => __('Favorit erfolgreich gelöscht')));
                    return;
                }elseif ($rows > 1) {
                    $this->logger->info(sprintf('customer #%s %s successfully deleted %d favourites #%s by restaurantId #%s', $customer->getId(), $customer->getFullname(), $rows, $favourite['id'], $restaurantId));
                    echo json_encode(array('success' => __('%d Favoriten erfolgreich gelöscht', $rows)));
                    return;
                } else {
                    $this->logger->warn(sprintf('no favorite of customer #%s %s was deleted by restaurantId #%s', $customer->getId(), $customer->getFullname(), $restaurantId));
                    echo json_encode(array('success' => __('Favorit erfolgreich gelöscht')));
                    return;
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->logger->err(sprintf('favorite could not be deleted by restaurantId #%s, because %s', $restaurantId, $e->getMessage()));
                echo json_encode(array('error' => __('Favorit konnte nicht gelöscht werden')));
                return;
            }
        }
    }

    /**
     * send feedback-thanks to customer and info to team
     * @todo rework, that we do not need session here
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function feedbackAction() {
        /**
         * @todo view feedbacks in admin-backend
         */
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $msg = $this->getRequest()->getParam('text', null);

        if (is_null($msg)) {
            return false;
        }

        try {
            $feedback = new Yourdelivery_Model_Feedback();
            $feedback->setCustomer($this->getCustomer());
            $feedback->setFeedback($msg);
            $feedback->save();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->err($e->getMessage() . $e->getTraceAsString());
            Yourdelivery_Sender_Email::error($e->getMessage() . $e->getTraceAsString(), true);
        }

        try {
            $email = new Yourdelivery_Sender_Email_Template('feedback');
            $email->setSubject('Yourdelivery: Feedback');
            $email->addTo($this->getCustomer()->getEmail());
            $email->assign('cust', $this->getCustomer());
            $email->assign('msg', $msg);
            $email->send();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->err($e->getMessage() . $e->getTraceAsString());
            Yourdelivery_Sender_Email::error($e->getMessage() . $e->getTraceAsString(), true);
        }
        Yourdelivery_Sender_Email::notify('neues Feedback von ' . $this->getCustomer()->getFullname() . ' (' . $this->getCustomer()->getEmail() . ') : ' . $msg);
    }

    /**
     * @todo rework, that we do not need session here
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function registerfidelityAction() {
        $fidelityConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/fidelity.ini', APPLICATION_ENV);
        $this->view->pointsforregister = $fidelityConfig->fidelity->points->order + 
                $fidelityConfig->fidelity->points->register +
                $fidelityConfig->fidelity->points->registeraftersale;
        $request = $this->getRequest();

        // post, print json
        if ($request->isPost()) {
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

            $post = $request->getPost();
            $orderId = (integer) $post['orderId'];

            $order = null;
            try {
                if ($orderId <= 0) {
                    throw new Yourdelivery_Exception_Database_Inconsistency('Could not find order by invalid id');
                }
                $order = new Yourdelivery_Model_Order($orderId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->logger->err('could not find order by invalid id');
                echo json_encode(array(
                    'error' => __('Bestellung nicht gefunden')
                ));
                return;
            }

            $customer = $order->getCustomer();
            $location = $order->getLocation();

            try {
                $alreadyRegistredCustomer = new Yourdelivery_Model_Customer(null, $customer->getEmail());
                if (!is_null($alreadyRegistredCustomer) && ($alreadyRegistredCustomer->getId() > 0) && ($alreadyRegistredCustomer->getDeleted() == 0)) {
                    echo json_encode(array(
                        'error' => __("Deine E-Mail-Adresse %s ist bereits bei uns registriert. Du erhältst für diese Bestellung %d Treuepunkte.", $customer->getEmail(), $fidelityConfig->fidelity->points->order)
                    ));
                    return;
                }
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }

            // register
            $values = array(
                'sex' => 'n',
                'prename' => $customer->getPrename(),
                'name' => $customer->getName(),
                'street' => $location->getStreet(),
                'hausnr' => $location->getHausnr(),
                'plz' => $location->getPlz(),
                'cityId' => $location->getCityId(),
                'email' => $customer->getEmail(),
                'tel' => $location->getTel(),
                'etage' => $location->getEtage(),
                'companyName' => $location->getCompanyName(),
                'comment' => $location->getComment(),
                'password' => $post['password'],
                'agb' => '1',
            );

            $form = new Yourdelivery_Form_Register();
            if ($post['registerLightbox'] && (strcmp($post['password'], $post['password2']) != 0)) {
                echo json_encode(array(
                    'error' => Default_View_Notification::array2html('Die Passwörter stimmen nicht überein')
                ));
                return;
            }

            if (!$form->isValid($values)) {
                $this->logger->warn('could not validate form for register fidelity');
                echo json_encode(array(
                    'error' => Default_View_Notification::array2html($form->getMessages())
                ));
                return;
            } else {
                $values = $form->getValues();
                $id = Yourdelivery_Model_Customer::add($values);

                if (is_null($id) || $id <= 0) {
                    $this->logger->warn('could not create customer');
                    echo json_encode(array(
                        'error' => __('Der Benutzer konnte leider nicht angelegt werden')
                    ));
                    return;
                }

                // create first location for customer
                try {
                    $customer = new Yourdelivery_Model_Customer($id);
                    $this->session->customerId = $id;
                    $customer->login();
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->logger->crit(sprintf('could not find customer by id %d', $id));
                    echo json_encode(array(
                        'error' => __('Der Benutzer konnte leider nicht angelegt werden')
                    ));
                    return;
                }

                $customer->addAddress($values);

                //add customer to this order
                $order->updateCustomer($customer);

                // send out registration email
                $config = Zend_Registry::get('configuration');
                $email = new Yourdelivery_Sender_Email_Template('register');
                $email->setSubject(__('Registrierung auf %s', $config->domain->base));
                $email->addTo($customer->getEmail(), $customer->getFullname());
                $email->assign('cust', $customer);
                $email->assign('password', $values['password']);
                $email->send();

                if (is_array($alreadyRegistred) && ($alreadyRegistred['deleted'] != 0)) {
                    echo json_encode(array(
                        'success' => __('Du hast Dich erfolgreich registriert. Willkommen bei %s', $config->domain->base)
                    ));
                } else {
                    echo json_encode(array(
                        'success' => __('Du hast Dich erfolgreich registriert und %d Treuepunkte erhalten. Willkommen bei %s', $fidelityConfig->fidelity->points->register, $config->domain->base)
                    ));
                }
                return;
            }
        }
        $this->view->orderId = $request->getParam('id');
    }

    /**
     * @todo rework, that we do not need session here
     * @todo refactor html code into view
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @return 
     */
    public function debitregisterAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

            $html_errorr = '
                    <h1 class="yd-lbicon">' . __("Kontodaten - zur Teilnahme am Lastschriftverfahren") . '</h1>
                    <form class="_autovalidate">
                        <div class="yd-lightbox-content-container">
                            <div class="yd-form">
                                <strong class="yd-form-tease">' . __("Geben Sie Ihre Kontodaten ein:") . '</strong>
                                <div class="yd-form-wrapper">
                                    <ul class="yd-clearfix">
                                        <li class="yd-form-left">
                                            <span>' . __("Kontoinhaber") . ' <b class="red">*</b></span>
                                        </li>
                                        <li class="yd-form-right">
                                            <input type="text" name="ktoName" id="ktoName" class="yd-form-input yd-form-invalid" value="" />
                                            <em class="yd-form-info hidden" id="debit-reg-name-info" >' . __("Bitte geben Sie den Kontoinhaber an") . '</em>
                                        </li>
                                    </ul>
                                    <ul class="yd-clearfix">
                                        <li class="yd-form-left">
                                            <span>' . __("Kontonummer") . ' <b class="red">*</b></span>
                                        </li>
                                        <li class="yd-form-right">
                                            <input type="text" name="ktoNr" id="ktoNr" class="yd-form-input yd-form-invalid yd-only-nr" value="" />
                                            <em class="yd-form-info hidden" id="debit-reg-nr-info" >' . __("Bitte geben Sie die Kontonummer ein") . '</em>
                                        </li>
                                    </ul>
                                    <ul class="yd-clearfix">
                                        <li class="yd-form-left">
                                            <span>Bankleitzahl <b class="red">*</b></span>
                                        </li>
                                        <li class="yd-form-right">
                                            <input type="text" name="ktoBlz" id="ktoBlz" class="yd-form-input yd-form-invalid yd-only-nr" value="" />
                                            <em class="yd-form-info hidden" id="debit-reg-blz-info" >' . __("Bitte geben Sie die Bankleitzahl ein") . '</em>
                                        </li>
                                    </ul>
                                </div>
                                <br /><br />
                                <input type="button" id="yd-finish-payment-debit-register-form-submit" value="' . __("Speichern") . '" class="button" />&nbsp;
                            </div>
                        </div>
                    </form>';
            $html_success = '
                    <em class="hidden">SUCCESS</em>
                    <h1 class="yd-lbicon">' . __("Bitte warten ...") . '</h1>
                    <form class="_autovalidate">
                        <div class="yd-lightbox-content-container">
                            <div class="yd-form">
                                <br /><br />
                            </div>
                        </div>
                    </form>';

            // check kto-data
            $ktoBlz = $request->getParam('ktoBlz', null);
            $ktoNr = $request->getParam('ktoNr', null);
            $ktoName = $request->getParam('ktoName', null);

            if (is_null($ktoBlz) || is_null($ktoNr) || is_null($ktoName)) {
                echo $html_errorr;
                return;
            }
            //load checker :)
            require_once APPLICATION_PATH . "/../library/Bav/classes/autoloader/BAV_Autoloader.php";
            BAV_Autoloader::add(APPLICATION_PATH . '/../library/Bav/classes/dataBackend/exception/BAV_DataBackendException.php');
            BAV_Autoloader::add(APPLICATION_PATH . '/../library/Bav/classes/dataBackend/exception/BAV_DataBackendException_BankNotFound.php');

            BAV_Autoloader::add(APPLICATION_PATH . '/../library/Bav/classes/dataBackend/BAV_DataBackend_File.php');
            $databack = new BAV_DataBackend_File();
            $databack->install();

            $result = false;
            try {
                if ($databack->bankExists($ktoBlz)) {
                    $bank = $databack->getBank($ktoBlz);
                    if ($bank->isValid($ktoNr)) {
                        $result = true;
                    }
                }
            } catch (Exception $e) {
                $result = false;
            }

            if ($result) {
                $this->getCustomer()->setDebit(true);
                $this->getCustomer()->setKtoName($ktoName);
                $this->getCustomer()->setKtoNr($ktoNr);
                $this->getCustomer()->setKtoBlz($ktoBlz);
                $this->getCustomer()->save();
                echo $html_success;
            } else {
                $this->getCustomer()->setDebit(false);
                echo $html_errorr;
            }
        }
    }

    /**
     * Register for newsletter at index
     * @author vpriem, fhaferkorn (15.7.2011)
     * @since 02.12.2010
     */
    public function registernewsletterAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $email = $this->getRequest()->getParam('email');
        if ($email === null || !Default_Helper::email_validate($email)) {
            echo json_encode(array(
                'error' => __("Diese Email-Adresse ist nicht korrekt.")
            ));
            return;
        }

        $row = Yourdelivery_Model_DbTable_Newsletterrecipients::findByEmail($email);
        if ($row) {
            echo json_encode(array(
                'error' => __("Diese Email-Adresse ist bereits für unseren Newsletter angemeldet.")
            ));
            return;
        }

        $n = new Yourdelivery_Model_Newsletterrecipients();
        $n->setData(array(
            'email' => $email,
            'status' => 1
        ));

        if ($n->save()) {
            echo json_encode(array(
                'success' => __("Du hast dich erfolgreich für unseren Newsletter eingetragen.")
            ));
            return;
        }

        echo json_encode(array(
            'error' => __("Deine E-Mail-Adresse konnte nicht eingetragen werden.")
        ));
    }

    public function continueAction() {
        $serviceId = (integer) $this->getRequest()->getParam('serviceId');
        try {
            $service = new Yourdelivery_Model_Servicetype_Restaurant($serviceId);
            $this->view->service = $service;
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            $this->getResponse()->setHttpResponseCode(404);
        }
    }

    /**
     * view to open ligthbox for openid selection
     * @author mlaug
     * @since 19.10.2011
     */
    public function openidAction() {
        
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 25.11.2011
     * @return void
     */
    public function newsletterAction() {
        $this->_disableView();
        $request = $this->getRequest();
        if ($this->getRequest()->isPost()) {
            $customerId = base64_decode($request->getParam('customerId'));

            $customer = new Yourdelivery_Model_Customer($customerId);
        } else {
            $customer = $this->getCustomer();
            if (!$customer->isLoggedIn()) {
                return $this->_redirect('/');
            }
        }

        try {
            $customer->setNewsletter((boolean) (int) $request->getParam('newsletter'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            echo __('Newsletter konnte nicht gespeichert werden');
            return;
        }
        if ($customer->getNewsletter() == 1) {
            echo __('Newsletter wurde abonniert');
        } else {
            echo __('Newsletter wurde abbestellt');
        }
    }

    public function ratingdeletedAction() {
        
    }

}
