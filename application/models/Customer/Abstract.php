<?php

/**
 * Description of Abstract
 * put in factory classes in
 * @package customer
 * @author mlaug
 */
abstract class Yourdelivery_Model_Customer_Abstract extends Default_Model_Base {

    const DEFAULT_IMG = 'http://cdn.yourdelivery.de/images/yd-profile/default_user.png';

    /**
     *
     * @var Yourdelivery_Model_Rabatt
     */
    protected $_rabatt = null;

    /**
     * get all information about the fidelity points
     * @var Yourdelivery_Model_Customer_Fidelity
     */
    protected $_fidelity = null;

    /**
     * create one of customer models
     * @author mlaug
     * @param int $id
     * @return Yourdelivery_Model_Customer_Abstract
     */
    public static function factory($id) {
        
    }

    /**
     * standard full name of an anonymous user
     * @author mlaug
     * @return string
     */
    public function getFullname() {
        $fullname = $this->getPrename() . ' ' . $this->getName();
        if (empty($fullname) || $fullname == " ") {
            return __("Unbekannt");
        }
        return $fullname;
    }

    /**
     * Get usr shorted name
     * @author vpriem
     * @since 18.10.2010
     * @return string
     */
    public function getShortedName() {
        $prename = $this->getPrename();
        $name = $this->getName();
        if (strlen($name) > 3) {
            $name = substr($name, 0, 3) . ".";
        }
        return trim($prename . " " . $name);
    }

    abstract function isLoggedIn();

    /**
     * get salutation of ananymous user
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 14.04.2011
     * @return string Salutation with Fullname
     */
    public function getEmailSalutation() {
        switch ($this->getSex()) {
            case 'm': return __('Sehr geehrter Herr %s', $this->getFullname());
                break;
            case 'w': return __('Sehr geehrte Frau %s', $this->getFullname());
                break;
            default: return __('Sehr geehrte(r) %s', $this->getFullname());
        }
    }

    /**
     * get salutation of ananymous user
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 14.04.2011
     * @return string Salutation with fullname
     */
    public function getPersonalEmailSalutation() {
        switch ($this->getSex()) {
            case 'm': return __('Lieber Herr %s', $this->getFullname());
                break;
            case 'w': return __('Liebe Frau %s', $this->getFullname());
                break;
            default: return __('Liebe(r) %s', $this->getFullname());
        }
    }

    /**
     * get customer nr
     * @author mlaug
     * @since 14.03.2011
     * @return string
     */
    public function getCustomerNr() {
        $name = $this->getName();
        if (empty($name) || $name === null) {
            return 0;
        } else {
            return 10000 + ord(strtolower(substr($name, 0, 1))) - 97;
        }
    }

    /**
     * @author mlaug
     * @since 04.11.2011
     * @return Yourdelivery_Model_Customer_Fidelity
     */
    public function getFidelity() {
        if ($this->_fidelity === null) {
            $email = $this->getEmail();
            if ($email === null) {
                $this->logger->crit('failed to load fidelity object, no email given?');
                return null;
            }
            $this->_fidelity = new Yourdelivery_Model_Customer_Fidelity($email);
        }
        return $this->_fidelity;
    }

    /**
     * add fidelity point to customer
     * @author mlaug
     * @since 04.11.2011
     * @param string $action
     * @return integer new count of points
     */
    public function addFidelityPoint($action, $data, $points = 0) {
        if ($this->getFidelity() === null) {
            return 0;
        }
        return $this->getFidelity()->addTransaction($action, $data, $points);
    }

    /**
     * removes fidelitypoint(s) from customer
     * @author mlaug
     * @since 04.11.2011
     * @param integer $transactionId
     * @return integer new count of points
     */
    public function removeFidelityPoint($transactionId) {
        if ($this->getFidelity() === null) {
            return 0;
        }
        return $this->getFidelity()->modifyTransaction($transactionId, -1);
    }

    /**
     * set fidelity points count to given count
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 04.08.2010
     * @param int $count
     * @param int $orderId
     * @param string $comment
     * @return int new count of points
     * @tested models/FidelityTest.php
     */
    public function setFidelityPoint($points, $data = 'manually added fidelity points') {
        if ($this->getFidelity() === null) {
            return 0;
        }
        return $this->getFidelity()->addTransaction('manual', $data, $points);
    }

    /**
     * @deprecated remove once refactored
     * @author mlaug
     * @since 04.11.2011
     * @return Yourdelivery_Model_Customer_Fidelity 
     */
    public function getFidelityPoints() {
        return $this->getFidelity();
    }

    /**
     * send newsletter to user
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @param string $html
     * @param int $type (template type)
     * @return Yourdelivery_Sender_Email_Newsletter
     */
    public function sendNewsletter($template, $orderTime) {
        if (is_null($template) || is_null($this->getEmail())) {
            return false;
        }
        try {
            new Yourdelivery_Sender_Email_Newsletter($template, $this, $orderTime);
        } catch (Exception $e) {
            // unable to send newsletter
        }
    }

    /**
     * gives always true, because we want to show premium services to all
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 15.09.2010
     * @return boolean
     */
    public function isPremium() {
        return true;
    }

    /**
     * Checks whether Email is valid and sends Email with new Pass to User
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @return int
     * 0 = success
     * 1 = email not valid
     * 2 = Email not in DB
     */
    public function forgottenPass($email) {
        if (is_null($email) || !Default_Helper::email_validate($email)) {
            // Email not valid or null
            return 1;
        } else {
            try {
                // try to create new customer with given email
                $cu = new Yourdelivery_Model_Customer(null, $email);
                $genPass = Default_Helper::generateRandomString();

                if ($cu->isDeleted()) {
                    return 2;
                }

                $cu->setPassword(md5(trim($genPass)));
                $cu->save();

                $config = Zend_Registry::get('configuration');

                $email = new Yourdelivery_Sender_Email_Template('forgotpw');
                $email->assign('pass', $genPass);
                $email->assign('cust', $cu);
                $email->addTo($cu->getEmail());
                $email->setSubject(__('%s: Dein Passwort wurde zurückgesetzt', $config->domain->base));
                $email->send();
                // Success
                return 0;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                // Email not in DB
                return 2;
            }
        }
    }

    /**
     * Checks whether Email is valid and sends Email with new Pass to User
     * Uses template to inform user, that admin has reseted his pass
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @return int
     * 0 = success
     * 1 = email not valid
     * 2 = Email not in DB
     */
    public function newPassAdmin() {
        $email = $this->getEmail();

        if (is_null($email) || !Default_Helper::email_validate($email)) {
            // Email not valid or null
            return 1;
        } else {
            try {
                // try to create new customer with given email
                $cu = new Yourdelivery_Model_Customer(null, $email);
                $genPass = Default_Helper::generateRandomString();
                $cu->setPassword(md5(trim($genPass)));
                $cu->save();

                $config = Zend_Registry::get('configuration');

                $email = null;
                $email = new Yourdelivery_Sender_Email_Template('newpwadmin');
                $email->assign('pass', $genPass);
                $email->assign('cust', $cu);
                $email->addTo($cu->getEmail());
                $email->setSubject(__('%s: Dein Passwort wurde vom Administrator zurückgesetzt', $config->domain->base));
                $email->send();
                // Success
                return 0;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                // Email not in DB
                return 2;
            }
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.03.2012
     * @return boolean 
     */
    public function isInNewsletterRecipients() {

        if (!$this->getEmail()) {
            return false;
        }

        $row = Yourdelivery_Model_DbTable_Newsletterrecipients::findByEmail($this->getEmail());
        if (is_array($row)) {
            return true;
        }
        return false;
    }

    /**
     * check, if customer gets newsletter of specified type
     * 
     * @param string $type
     * @return boolen
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 21.10.2010
     * 
     * @modified Matthias Laug <laug@lieferando.de>
     */
    public function getNewsletter() {

        if (!$this->getEmail()) {
            return false;
        }

        $row = Yourdelivery_Model_DbTable_Newsletterrecipients::findByEmail($this->getEmail());

        if (!is_array($row)) {
            return false;
        }

        $config = Zend_Registry::get('configuration');
        if ($config->newsletter->method == 'doubleoptin') {
            return (boolean) ($row['status'] && $row['affirmed']); //double opt in
        }
        return (boolean) ($row['status']);
    }

    /**
     * set newsletter types in related table
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 21-10-2010
     * @param boolean $status
     * @param boolean $special
     * @param boolean $fideltiy
     * @return boolean
     */
    public function setNewsletter($status = true, $affirmed = false, $reason = false) {
        $email = trim($this->getEmail());
        if (empty($email) || !Default_Helper::email_validate($email)) {
            return false;
        }

        $row = Yourdelivery_Model_DbTable_Newsletterrecipients::findByEmail($email);
        $id = null;
        if (is_array($row)) {
            $id = (integer) $row['id'];
        }

        $n = new Yourdelivery_Model_Newsletterrecipients($id);
        $n->setEmail($email);
        $n->setStatus($status);

        if ($reason) {
            $n->setOptOutReason($reason);
        }

        //double opt in
        $config = Zend_Registry::get('configuration');
        if ($config->newsletter->method == 'doubleoptin') {
            if ($status === true && $affirmed === false && (boolean) $n->getAffirmed() === false) {
                $this->logger->info(sprintf('sending out double opt in email to %s', $email));
                //send out email to confirm email adress
                $emailConfirm = new Yourdelivery_Sender_Email_Template('newsletter_ask_for_confirm');
                $emailConfirm->assign('cust', $this);
                $emailConfirm->assign('hash', md5(SALT . $email . SALT));
                $emailConfirm->addTo($email)
                        ->setSubject(__('Bitte ihre eMail bestätigen'))
                        ->send();
            }
        } else {
            //if we do not use the double opt in proces, we affirm this email right away
            $this->logger->debug(sprintf('no double opt in activated, setting affirmed true for %s', $email));
            $affirmed = true;
        }

        //do not overwrite once set to true
        $n->setAffirmed((integer) $affirmed || $n->getAffirmed());
        return count($n->save()) > 0 ? true : false;
    }

    /**
     * @author vpriem
     * @since 03.11.2011
     * @return SplObjectStorage
     */
    public function getCreditcards() {
        $dbTable = new Yourdelivery_Model_DbTable_Customer_Creditcard();
        $rows = $dbTable->findFromCustomerId($this->getId());

        $spl = new SplObjectStorage();
        foreach ($rows as $row) {
            try {
                $spl->attach(new Yourdelivery_Model_Customer_Creditcard($row->id));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }
        return $spl;
    }

    /**
     * get nickname or prename as default
     * 
     * @since 28.11.2011
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getNickname() {
        $nick = $this->_data['nickname'];
        if ($nick === null) {
            return $this->getPrename();
        }
        return $nick;
    }

    /**
     * get date of first order
     * get date of last order
     * get count of all orders
     * 
     * @return array
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 23.12.2011
     */
    public function getFirstAndLastAndCountOrders() {
        $hash = $this->getCacheTag('firstandlastandcountorders');
        $data = Default_Helpers_Cache::load($hash);
        if (is_null($data)) {
            $table = new Yourdelivery_Model_DbTable_Customer();
            $data = $table->getFirstAndLastAndCountOrders($this->getEmail());
            Default_Helpers_Cache::store($hash, $data);
        }
        return $data;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 06.12.2011
     * @param string $tag
     * @return string
     */
    public function getCacheTag($tag) {
        return sprintf("%scustomer%s", $tag, $this->getId());
    }

}
