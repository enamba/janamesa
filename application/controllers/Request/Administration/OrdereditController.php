<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Refactor of Administration Orderedit
 * Lightbox for order editing options
 * @author Daniel Hahn <hahn@lieferando.de>
 */
class Request_Administration_OrdereditController extends Default_Controller_RequestAdministrationBase {

    protected $request = null;
    protected $id = null;

    /**
     * @var Yourdelivery_Model_Order
     */
    protected $order = null;

    /**
     * @authordaniel
     * @since 21.10.2011
     * @return void
     */
    public function init() {
        parent::init();

        $this->_disableView(true);
        $this->request = $this->getRequest();
        $this->id = (integer) $this->request->getParam('id');

        if ($this->id > 0) {
            try {
                $this->order = new Yourdelivery_Model_Order($this->id);
                $this->view->order = $this->order;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->view->order = null;
                return;
            }
        } else {
            $this->_forward('notfound');
        }
    }

    /**
     * no order found by given id in init method
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 24.07.2012 
     */
    public function notfoundAction() {
        $this->getResponse()->setHttpResponseCode(404);
    }

    /**
     * the index lightbox html action, only one appended to a view
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 24.07.2012
     */
    public function indexAction() {
        $this->_disableView(false);

        //storno
        $stornoForm = new Yourdelivery_Form_Administration_Order_Edit_Storno();
        $stornoForm->setOrder($this->order);
        $this->view->stornoForm = $stornoForm;

        //confrim
        $confirmForm = new Yourdelivery_Form_Administration_Order_Edit_Confirm();
        $confirmForm->setOrder($this->order);
        $this->view->confirmForm = $confirmForm;

        //paypal whitelisting and blacklisting
        $paypalBlackListForm = new Yourdelivery_Form_Administration_Order_Edit_Paypal();
        $paypalBlackListForm->setOrder($this->order);
        $this->view->paypalBlackListForm = $paypalBlackListForm;

        //resend order
        $resendForm = new Yourdelivery_Form_Administration_Order_Edit_Resend();
        $resendForm->setOrder($this->order);
        $this->view->resendForm = $resendForm;
        
        //comment order
        $commentForm = new Yourdelivery_Form_Administration_Order_Edit_Comment();
        $commentForm->setOrder($this->order);
        $this->view->commentForm = $commentForm;
        
        //payment change
        $paymentForm = new Yourdelivery_Form_Administration_Order_Edit_Payment();
        $paymentForm->setOrder($this->order);
        $this->view->paymentForm = $paymentForm;
    }

    /**
     * mark order as storno
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.10.2011, 24.07.2012
     */
    public function stornoAction() {
        $stornoForm = new Yourdelivery_Form_Administration_Order_Edit_Storno();
        $stornoForm->setOrder($this->order);

        if ( $this->order->getState() == Yourdelivery_Model_Order_Abstract::STORNO ){
            echo __b('Bestellung ist bereits storniert');
            return $this->getResponse()->setHttpResponseCode(406);
        }
        
        if ($stornoForm->isValid($this->getRequest()->getParams())) {
            $reasons = Yourdelivery_Model_Order_Abstract::getStornoReasons();
            $oldStatus = $this->order->getState();
            $this->order->setStatus(Yourdelivery_Model_Order_Abstract::STORNO, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_STORNO, $reasons[$stornoForm->getValue('reasonId')], $this->session_admin->admin->getName()), true);

            // Using config-based locale during composing and sending e-mail
            $this->_restoreLocale();
            if ($oldStatus >= 0 && $stornoForm->getValue('informrestaurant')) {
                $this->order->sendStornoNotificationToRestaurant();
            }
            if ($stornoForm->getValue('informcustomer')) {
                $this->order->sendStornoEmailToUser();
            }
            $this->_overrideLocale();

            // refund paypal transaction
            $messages = array();
            if ($stornoForm->getValue('paypal')) {
                Yourdelivery_Helpers_Payment::refundPaypal($this->order, $this->logger, $messages);
                // refund ebanking transaction
            } elseif ($stornoForm->getValue('ebanking')) {
                Yourdelivery_Helpers_Payment::refundEbanking($this->order, $this->logger, $messages, __b("storniert von %s", $this->session_admin->admin->getName()));
                // refund credit transaction
            } elseif ($stornoForm->getValue('credit')) {
                Yourdelivery_Helpers_Payment::refundCredit($this->order, $this->logger, $messages);
            }

            $this->logger->adminInfo(sprintf("Successfully canceled %d", $this->id));
            $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::ORDER_STORNO, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $this->order->getId());
            echo __b("Bestellung wurde erfolgreich storniert");
        } else {
            echo __b('Übermittelte Daten sind nicht korrekt');
            $this->getResponse()->setHttpResponseCode(406);
        }
    }

    /**
     * mark order as fake as block the sender IP for certain time, refund Paypal
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 21.10.2011
     */
    public function fakeAction() {
        
        if ( in_array($this->order->getState(), array(Yourdelivery_Model_Order_Abstract::FAKE_STORNO) )){      
            echo __b('Bestellung wurde bereits als Fake markiert');
            return $this->getResponse()->setHttpResponseCode(406);
        }
        
        try {
            // block the sender IP
            if (!is_null($this->order->getIpAddr())) {
                $blacklist = new Yourdelivery_Model_Support_Blacklist();
                $blacklist->setAdminId($this->session_admin->admin->getId());
                $blacklist->setComment(__b("Bestellung geblacklisted"));
                $blacklist->setOrderId($this->order->getId());
                $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_IP, $this->order->getIpAddr());
                // block the uuid (if available)
                if (!is_null($this->order->getUuid())) {
                    $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_UUID, $this->order->getUuid());
                }
                $blacklist->save();
            }

            //add Order to Paypal Blacklist if paypal
            if ($this->order->getPayment() == "paypal") {
                $pp_transaction = new Yourdelivery_Model_DbTable_Paypal_Transactions();
                $response = $pp_transaction->getUserData($this->order->getId());

                if (strlen($response['EMAIL']) > 0 && strlen($response['PAYERID']) > 0) {
                    $blacklist = new Yourdelivery_Model_Support_Blacklist();
                    $blacklist->setAdminId($this->session_admin->admin->getId());
                    $blacklist->setOrderId($this->order->getId());
                    $blacklist->setComment(__b("Bestellung geblacklisted"));
                    $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_PAYPAL_EMAIL, $response['EMAIL'], Yourdelivery_Model_Support_Blacklist::MATCHING_EXACT, Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_BLACKLIST);
                    $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_PAYPAL_PAYERID, $response['PAYERID'], Yourdelivery_Model_Support_Blacklist::MATCHING_EXACT, Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_BLACKLIST);
                    $blacklist->save();
                }
            }
            sleep(1);
            //set Status to Fake Storno
            $this->order
                    ->setStatus(
                            Yourdelivery_Model_Order_Abstract::FAKE_STORNO, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_BLACKLIST, "", $this->session_admin->admin->getName())
                            , true
            );

            echo __b("Bestellung erfolgreich storniert und als fake markiert. IP Adresse wird bis 3 Nachts blockiert sein");
            $this->logger->adminInfo(sprintf('Successfully marked order %d as fake', $this->id));
            $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::ORDER_FAKE, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $this->order->getId());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->getResponse()->setHttpResponseCode(406);
            $this->logger->adminErr(sprintf("Could not mark order %d as fake", $this->id));
            echo __b("Konnte die Bestellung ") . $this->id . __b(" nicht finden");
        }
    }

    /**
     * resend the order
     * @author Daniel Hahn <hahn@lieferando.de>
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.10.2011. 24.07.2012
     */
    public function resendAction() {
        $toRestaurant = (boolean) $this->request->getParam('torestaurant');
        $toCourier = (boolean) $this->request->getParam('tocourier');

        if ($toRestaurant) {
            $this->logger->adminInfo(sprintf("Resend order #%d again manually to restaurant", $this->id));
        }
        if ($toCourier) {
            $this->logger->adminInfo(sprintf("Resend order #%d again manually to courier", $this->id));
        }

        // Using config-based locale during composing and sending e-mail
        $this->_restoreLocale();
        $this->order->setStatus($this->order->getStatus(), new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_RESENT_BY_SUPPORTER, $this->order->getService()->getNotify(), $this->session_admin->admin->getName()));
        $this->order->send(false, $toRestaurant, $toCourier);
        $this->_overrideLocale();

        $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::ORDER_FAX_RESEND, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $this->order->getId());
        
        echo __b('Bestellung wurde erfolgreich erneut verschickt');
    }

    /**
     * confirm the order and check for valid stati
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.10.2011, 24.07.2012
     */
    public function confirmAction() {
        
        //do not allow to confirm delivered and payment pending orders
        $invalidStatus = array(
            Yourdelivery_Model_Order::DELIVERED,
            Yourdelivery_Model_Order::PAYMENT_NOT_AFFIRMED,
            Yourdelivery_Model_Order::PAYMENT_PENDING
        );
        
        if (!in_array($this->order->getState(), $invalidStatus)) {
            $setStatus = Yourdelivery_Model_Order::AFFIRMED;

            //if this is a great order, the status needs to be set to delivered
            if ($this->order->getMode() != 'great') {
                $setStatus = Yourdelivery_Model_Order::DELIVERED;
            }

            //stati that needs to call finalizeOrderAfterPayment
            $finalizingStati = array(
                Yourdelivery_Model_Order::FAKE,
                Yourdelivery_Model_Order::FAKE_STORNO
            );

            if (in_array($this->order->getState(), $finalizingStati)) {
                if ($this->order->finalizeOrderAfterPayment($this->order->getPayment(), false, false, false, false)) {
                    $this->logger->adminInfo(sprintf("Order %d has been confirmed from fraud, fax send out", $this->id));
                    $this->order->setStatus($setStatus, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_CONFIRM_AFTER_FRAUD, $this->session_admin->admin->getName()), true);
                }
            } else {
                $this->logger->adminInfo(sprintf("Order %d has been manually confirmed", $this->id));
                $this->order->setStatus($setStatus, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_CONFIRM, $this->session_admin->admin->getName()), true);
            }

            /*  track Confirm with different states
             *  0 - Standard
             *  1 - Oraly
             *  2 - Fax went through
             *  @daniel
             *  @since 30.09.2011
             */
            $track = $this->request->getParam('track');
            if ($track == 2) {
                $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::ORDER_CONFIRM_FAX_WENT_THROUGH, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $this->order->getId());
            } elseif ($track == 1) {
                $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::ORDER_CONFIRM_ORALY, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $this->order->getId());
            } else {
                $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::ORDER_CONFIRM, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $this->order->getId());
            }
            
            echo __b('Bestellung wurde erfolgreich bestätigt');
            
        }
        else{
            $this->getResponse()->setHttpResponseCode(406);
            echo __b('Bestellung kann im Status "%s" nicht bestätigt werden', Default_Helpers_Human_Readable_Backend::intToOrderStatus($this->order->getState()));
        }
    }

    /**
     * submit button on change the payment kind page was pressed
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 21.10.2011
     */
    public function changepaymentAction() {
        // change payment here
        $form = new Yourdelivery_Form_Administration_Order_Edit_Payment();
        $form->setOrder($this->order);
        
        if ( $form->isValid($this->getRequest()->getParams()) ){     
            $payment = $form->getValue('payment');
            $this->logger->adminInfo(sprintf("changed payment of order %d to %s", $this->id, $payment));
            $orderRow = $this->order->getRow();
            if (in_array($payment, array('bar', 'bill'))) {
                $orderRow->charge = 0;
            }
            $orderRow->payment = $payment;
            $orderRow->save();
            
            $this->order->setStatus($this->order->getState(), new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, __b("Bezahlart geändert zu %s von %s", $payment, $this->session_admin->admin->getName())), true);
            echo __b('Bezahlart erfolgreich auf %s geändert', $payment);
        }
        else{
            $this->getResponse()->setHttpResponseCode(406);
            echo __b('Bezahlart konnt nicht geändert werden');
        }
    }

    /**
     * comment an order
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.10.2011, 24.07.2012
     */
    public function commentAction() {
        $form = new Yourdelivery_Form_Administration_Order_Edit_Comment();
        $form->setOrder($this->order);
        if ($form->isValid($this->getRequest()->getParams())) {
            $this->logger->adminInfo(sprintf("Order %d has been commented: %s", $this->id, $form->getValue('comment')));
            $this->order->setStatus($this->order->getState(), new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::COMMENT, $form->getValue('comment') . __b(' von %s', $this->session_admin->admin->getName())), true);
            $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::ORDER_COMMENT, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $this->order->getId());
            echo __b('Kommentar erfolgreich gesetzt');
        } else {
            echo __b("Bitte geben sie einen Text für das Kommentar ein");
            $this->getResponse()->setHttpResponseCode(406);
        }
    }

    /**
     * resend rating email
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 21.10.2011
     */
    public function ratingemailAction() {
        $emailAdd = null;

        // send reminder to registered AND unregistered users
        $emailAdd = $this->order->getOrigCustomer()->getEmail();

        if (!is_null($emailAdd)) {
            // send out reminding email
            try {
                // Using config-based locale during composing and sending e-mail
                $this->_restoreLocale();
                $email = new Yourdelivery_Sender_Email_Template('rating');
                $email->setSubject(__('Bewerte Deine Bestellung bei %s', $this->order->getService()->getName()));
                $email->assign('yesadviseorderlink', 'rate/' . $this->order->getHash() . '/' . md5(SALT . 'yes'));
                $email->assign('noadviseorderlink', 'rate/' . $this->order->getHash() . '/' . md5(SALT . 'no'));
                $email->addTo($emailAdd);
                $email->assign('order', $this->order);
                $email->send();
                $this->_overrideLocale();

                echo __b("Die Bewertungsemail für die Bestellung %s wurde erneut verschickt", $this->id);
                $this->logger->adminInfo(sprintf("Rating email for order %d was resend", $this->id));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                echo __b("Die Bewertungsemail für die Bestellung %s konnte nicht erneut verschickt werden. Exception: %s", $this->id, $e->getMessage());
                $this->logger->adminInfo(sprintf("Rating email for order %d could not be resend", $this->id));
            }
        }
    }

    /**
     * resend confirmation email
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 21.10.2011
     */
    public function confirmationemailAction() {
        try {
            // Using config-based locale during composing and sending e-mail
            $this->_restoreLocale();
            $email = new Yourdelivery_Sender_Email_Template('order');
            $email->setSubject(__('%s: Deine Bestellung bei %s.', $this->config->domain->base, $this->order->getService()->getName()));

            // give hash of order number per HTTP-GET in email
            // every customer gets this link to rate an order no matter, if he is logged in or not
            $email->assign('rateorderlink', '?rateorder=' . md5($this->order->getId()));

            $email->addTo($this->order->getCustomer()->getEmail());
            $email->assign('order', $this->order);
            $email->send();
            $this->_overrideLocale();

            echo __b("Die Bestätigungsemail für die Bestellung ") . $this->id . __b(" wurde erneut verschickt");
            $this->logger->adminInfo(sprintf("Confirmation email for order %d was resend", $this->id));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            echo __b("Die Bestätigungsemail für die Bestellung ") . $this->id . __b(" konnte nicht erneut verschickt werden. Exception: ") . $e->getMessage();
            $this->logger->adminInfo(sprintf("Confirmation email for order  %d could not be resend", $this->id));
        }
    }

    /**
     * add to paypal blacklist
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 21.10.2011
     */
    public function blockAction() {
        $payerId = $this->request->getParam('payerId');
        $list = new Yourdelivery_Model_DbTable_Paypal_BlackWhiteList();
        $list->addToBlacklist($payerId);
        echo __b('PayerId %s geblacklistet', $payerId);
    }

    /**
     * add to paypal whitelist
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 21.10.2011
     */
    public function whitelistAction() {
        $payerId = $this->request->getParam('payerId');
        $list = new Yourdelivery_Model_DbTable_Paypal_BlackWhiteList();
        $list->addToWhitelist($payerId);
        echo __b('PayerId %s whitelisted', $payerId);
    }

}

?>
