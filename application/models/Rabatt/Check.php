<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Check
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 19.01.2012
 */
class Yourdelivery_Model_Rabatt_Check extends Default_Model_Base {

    //put your code here

    protected $_table = null;

    /**
     * Broadmail Url for Confirm Mail
     * @var string
     */
    protected $_confirmMailUrl = null;

    /**
     * Broadmail Url for Final Mail with Code
     * @var string
     */
    protected $_finalMailUrl = null;

    public function __construct($id = null, $current = null) {
        parent::__construct($id, $current);

        // Initialize
        switch ($this->config->domain->base) {
            case 'lieferando.at':
                $this->_confirmMailUrl = 'https://api.broadmail.de/http/form/1V46UA0-1V4WQ3J-188POR/sendtransactionmail?bmRecipientId=%s&bmMailingId=4059473218&Link=%s';
                $this->_finalMailUrl = "https://api.broadmail.de/http/form/1V46UA0-1V4WQ3J-188POR/sendtransactionmail?bmRecipientId=%s&bmMailingId=4059473235&UserPrename=%s&Code=%s&UserEmail=%s&Passwort=%s";
                break;
            case 'smakuje.pl':
            case 'pyszne.pl':
                $this->_confirmMailUrl = 'https://api.broadmail.de/http/form/1V6BH2X-1V6MC4J-F4EOBR/sendtransactionmail?bmRecipientId=%s&bmMailingId=4062450210&Link=%s';
                $this->_finalMailUrl = "https://api.broadmail.de/http/form/1V6BH2X-1V6MC4J-F4EOBR/sendtransactionmail?bmRecipientId=%s&bmMailingId=4062362340&UserPrename=%s&Code=%s&UserEmail=%s&Passwort=%s";
                break;
            case 'lieferando.ch':
            case 'lieferando.de':           
            case 'eat-star.de':
            case 'appetitos.it':
            case 'elpedido.es':
                $this->_confirmMailUrl = 'https://api.broadmail.de/http/form/1UXR6ED-1V46OJK-XEH1BFX/sendtransactionmail?bmRecipientId=%s&bmMailingId=4058264052&Link=%s';
                $this->_finalMailUrl = "https://api.broadmail.de/http/form/1UXR6ED-1V46OJK-XEH1BFX/sendtransactionmail?bmRecipientId=%s&bmMailingId=4059471422&UserPrename=%s&Code=%s&UserEmail=%s&Passwort=%s";
                break;
            case 'taxiresto.fr':
                $this->_confirmMailUrl = 'https://api.broadmail.de/http/form/1V472L4-1V4V5ST-US66QZ/sendtransactionmail?bmRecipientId=%s&bmMailingId=4059407405&Link=%s';
                $this->_finalMailUrl = "https://api.broadmail.de/http/form/1V472L4-1V4V5ST-US66QZ/sendtransactionmail?bmRecipientId=%s&bmMailingId=4059472300&UserPrename=%s&Code=%s&UserEmail=%s&Passwort=%s";
                break;
            default:
                break;
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     * @param Yourdelivery_Model_Rabatt $discount
     * @return type
     */
    public function finalize(Yourdelivery_Model_Rabatt $discount) {

        if ($this->getRabattCodeId() == null) {

            $db = Zend_Registry::get('dbAdapter');
            $db->beginTransaction();
            try {
                // generate new Rabattcode
                $rabatt = new Yourdelivery_Model_Rabatt_Code($discount->generateCode(null, 13, '0123456789'));

                $this->logger->info(sprintf('DISCOUNT CHECK: created code %s for discount ', $rabatt->getCode(), $discount->getId()));

                //add User with RabattCheck Values and generate password
                $values = array();
                $values['email'] = $this->getEmail();
                $values['name'] = $this->getName();
                $values['prename'] = $this->getPrename();
                $values['tel'] = $this->getTel();
                $values['password'] = Default_Helper::generateRandomString(8);

                // check if user has registered before
                try {
                    $customer = new Yourdelivery_Model_Customer(null, $this->getEmail());
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $customer = null;
                }

                if (is_null($customer) || $customer->getId() == null) {
                    $customerId = Yourdelivery_Model_Customer::add($values);
                    $customer = new Yourdelivery_Model_Customer($customerId);
                }

                $customer->setNewsletter(true);


                //set values and save object
                $this->setRabattCodeId($rabatt->getId());
                $this->setCustomerId($customerId);
                $this->save();

                //set Code used when  type = 2
                if ($this->getRabattVerificationId() != NULL &&
                        $discount->getType() == Yourdelivery_Model_Rabatt::TYPE_VERIFICATION_MANY) {
                    $table = new Yourdelivery_Model_DbTable_RabattCodesVerification();
                    $rows = $table->find($this->getRabattVerificationId());
                    $row = $rows->current();
                    $row->send = 1;
                    $row->save();
                }
            } catch (Exception $e) {
                $db->rollback();
                $this->logger->err(sprintf('DISCOUNT CHECK: save failed with Message %s', $e->getMessage()));

                throw new Exception($e->getMessage(), $e->getCode());
            }

            $db->commit();

            //send out mails
            $this->send($customer, $rabatt->getCode(), $values['password']);
            $this->sendExternalMailing($discount, $customer);
        } else {
            $rabatt = new Yourdelivery_Model_Rabatt_Code(null, $this->getRabattCodeId());
            $customer = new Yourdelivery_Model_Customer($this->getCustomerId());
        }

        return compact('rabatt', 'customer');
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     * @param Yourdelivery_Model_Rabatt $discount
     * @param type $customer
     */
    public function sendExternalMailing(Yourdelivery_Model_Rabatt $discount, $customer) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $select = $db->select()->distinct()
                ->from('rabatt_codes_external_mailing')
                ->where('rabattId=?', $discount->getId())
                ->where('customerId IS NULL')
                ->where('email IS NULL')
                ->limit(1);

        $results = $db->fetchAll($select);
        
        if ($results && count($results) > 0) {

            $row = $results[0];
            $email = $customer->getEmail();
            $data = array(
                'email' => $email,
                'customerId' => $customer->getId()
            );
            $where = $db->quoteInto('id = ?', $row['id']);
            $db->update('rabatt_codes_external_mailing', $data, $where);


            // send external-email with discount code
            $emailTemplate = new Yourdelivery_Sender_Email_Template($row['campaign']);
            $emailTemplate->setSubject(__('Ihr Gutscheincode'));
            $emailTemplate->assign('code', $row['code']);
            $emailTemplate->assign('customer', $customer);
            $emailTemplate->addTo($email);
            if ($emailTemplate->send()) {
                $this->logger->info(sprintf('DISCOUNT CHECK: sent %s #%s %s to email %s', $row['campaign'], $row['id'], $row['code'], $email));
            } else {
                $this->logger->err(sprintf('DISCOUNT CHECK: could not sent %s #%s %s to email %s', $row['campaign'], $row['id'], $row['code'], $email));
            }
        } elseif (is_array($results) && count($results) == 0) {
            //mail an Marketing
        } else {
            //error
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     * @param Yourdelivery_Model_Customer  $customer
     * @param string $code
     * @param string $password
     * @return boolean
     *
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 31.01.2012
     */
    protected function send(Yourdelivery_Model_Customer $customer, $code, $password) {

        //do not use optivo for janamesa
        if ($this->config->domain->base == 'janamesa.com.br') {
            $to = IS_PRODUCTION ? $this->getEmail() : $this->config->testing->email;
            $email = new Yourdelivery_Sender_Email();
            return $email->setSubject('Jánamesa - seu voucher e o acesso a sua conta')
                            ->setBodyText(
                                    "Bem vindo no Jánamesa! \nAgora você já pode pedir sua comida preferida online.\n\n" .
                                    'Número do voucher: ' . $code . "\n" .
                                    'Seu email: ' . $customer->getEmail() . "\n" .
                                    'Sua senha: ' . $password . "\n" .
                                    'Acesse sua conta agora: www.janamesa.com.br' . "\n\n" .
                                    'Seu time Jánamesa')
                            ->addTo($to)
                            ->send();
        }

        $broadmailUrl = $this->_finalMailUrl;

        if (is_null($broadmailUrl)) {
            $this->logger->err(sprintf('DISCOUNT CHECK - SEND: broadmailUrl is NULL'));
        }

        // prepare url for testing or live emails
        if (IS_PRODUCTION) {
            $mailstring = sprintf($broadmailUrl, $customer->getEmail(), $customer->getPrename(), $code, $customer->getEmail(), $password);
        } else {
            $mailstring = sprintf($broadmailUrl, $this->config->testing->email, $customer->getPrename(), $code, $customer->getEmail(), $password);
        }

        // call the broadmail API
        $result = file_get_contents($mailstring);

        // check result of mailing
        if (strpos($result, 'enqueued') !== false) {
            // only save email for us, when sending via optivo
            $this->logger->info(sprintf('DISCOUNT CHECK - SEND: successfully sent code %s to email %s via broadmail', $code, $customer->getEmail()));
            return true;
        } else {
            $this->logger->err(sprintf('DISCOUNT CHECK - SEND: could not send code %s to email %s via broadmail. mailstring: %s - result: %s', $code, $customer->getEmail(), $mailstring, $result));
            return false;
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return boolean 
     */
    public function resend() {

        if (!$this->allowResend('email')) {
            return false;
        }

        try {
            $customer = new Yourdelivery_Model_Customer($this->getCustomerId());
            $rabatt = new Yourdelivery_Model_Rabatt_Code(null, $this->getRabattCodeId());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return false;
        }
        $password = Default_Helper::generateRandomString(8);
        $customer->setPassword(md5($password));
        $customer->save();

        $this->logger->info(sprintf("DISCOUNT CHECK - RESEND: try to resend final mail to %s", $customer->getEmail()));

        return $this->send($customer, $rabatt->getCode(), $password);
    }

    /**
     * @author Daniel Hahn
     * @since 07.02.2012
     * @param Yourdelivery_Model_Rabatt $discount
     * @param mixed $config
     * @return boolean
     */
    public function sendConfirmMail(Yourdelivery_Model_Rabatt $discount, $config) {

        //do not use optivo for janamesa
        if ($this->config->domain->base == 'janamesa.com.br') {
            $mailString = 'http://www.' . $config->domain->base . '/' . $discount->getReferer() . '/confirm/code/' . $this->getCodeEmail();
            $to = IS_PRODUCTION ? $this->getEmail() : $this->config->testing->email;
            $email = new Yourdelivery_Sender_Email();
            return $email->setSubject('Jánamesa - verifique seu email')
                            ->setBodyText('Por favor, clique no link para verificar seu email: ' . $mailString)
                            ->addTo($to)
                            ->send();
        }

        $broadmailUrl = $this->_confirmMailUrl;

        if (is_null($broadmailUrl)) {
            $this->logger->err(sprintf('DISCOUNT CHECK - SEND: broadmailUrl is NULL'));
        }

        // prepare url for testing or live emails
        if (IS_PRODUCTION) {
            $mailString = sprintf($broadmailUrl, $this->getEmail(), urlencode('http://www.' . $config->domain->base . '/' . $discount->getReferer() . '/confirm/code/' . $this->getCodeEmail()));
        } else {
            $mailString = sprintf($broadmailUrl, $this->config->testing->email, urlencode('http://www.' . $config->domain->base . '/' . $discount->getReferer() . '/confirm/code/' . $this->getCodeEmail()));
        }

        $result = @file_get_contents($mailString);
        // check result of mailing
        if (strpos($result, 'enqueued') !== false) {
            $this->logger->info(sprintf('DISCOUNT CHECK: successfully sent email with verification code %s to %s via broadmail', $this->getCodeEmail(), $this->getEmail()));
            $this->saveEmail($this->getEmail(), 'http://www.' . $config->domain->base . '/' . $discount->getReferer() . '/confirm/code/' . $this->getCodeEmail(), $result);
        } else {
            $this->logger->err(sprintf('DISCOUNT CHECK: could not send email with verification code %s to %s. mailstring %s - result: %s', $this->getCodeEmail(), $this->getEmail(), $mailString, $result));
            return false;
        }

        return true;
    }

    public function sendSms($tel) {
        $sms = new Yourdelivery_Sender_Sms();
        $state = $sms->send($tel, __('Dein Bestätigungscode lautet: %s', $this->getCodeTel()));

        if ($state) {
            $this->setTel($tel);
            $this->setTelSend(date(DATETIME_DB));
            $this->increaseSmsSendCount();
            $this->save();
            $this->logger->info(sprintf("DISCOUNT CHECK: successfully sent out sms with code %s to %s", $this->getCodeTel(), $tel));
            return true;
        }

        return false;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 02.02.2012
     * @param int $count
     */
    public function increaseSmsSendCount($count = 1) {

        $this->setSmsSendCount(((integer) $this->getSmsSendCount()) + ((integer) $count));
    }

    /**
     * Check if we still allow resend
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $type
     * @return boolean 
     */
    public function allowResend($type) {

        switch ($type) {
            case 'sms':
                $countSendSms = (integer) $this->getSmsSendCount();
                if ($countSendSms >= 3) {
                    return false;
                }
                return true;

            case 'email':
                $countSendEmail = (integer) $this->getEmailSendCount();
                if ($countSendEmail >= 3) {
                    return false;
                }
                return true;
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     * @param Yourdelivery_Model_Rabatt $discount
     * @param string $email
     * @param string $name
     * @param string $prename
     * @param int $rabattVerificationId
     *
     */
    public function saveStep2(Yourdelivery_Model_Rabatt $discount, $email, $name, $prename, $rabattVerificationId) {
        $this->setEmail($email);
        $this->setRabattVerificationId($rabattVerificationId);
        $this->setName($name);
        $this->setPrename($prename);
        $this->setReferer($discount->getReferer());
        $code = Default_Helper::generateRandomString(6, '1234567890');
        $this->setCodeTel($code);
        $this->setCodeEmail(md5($code . SALT . $discount->getId()));
        $this->save();
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     * @param string $code
     * @return boolean
     */
    public function codeIsValid($code) {
        return $code === $this->getCodeTel();
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param type $tel
     * @return boolean 
     */
    public function checkTel($tel) {
        if ($tel === $this->getTel()) {
            return true;
        }

        $test = new Default_Forms_Validate_TelefonNumber();
        return $test->isValid($tel);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     * @param type $code
     * @param type $rabattId
     * @return type
     */
    public static function getValidVerificationCode($code, $rabattId) {
        $table = new Yourdelivery_Model_DbTable_RabattCodesVerification();

        $row = $table->findByUqRabattVerification($code, $rabattId);

        if ($row && !empty($row['id'])) {
            return $row;
        }

        return false;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     * @param int $id
     * @return boolean
     */
    public static function isVerificationCodeById($id) {
        $table = new Yourdelivery_Model_DbTable_RabattCodesVerification();
        $row = $table->findById($id);

        if ($row && !empty($row['id'])) {
            return true;
        }

        return false;
    }

    /**
     * @author  Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     * get table class
     * @return Yourdelivery_Model_DbTable_RabattCheck
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_RabattCheck();
        }
        return $this->_table;
    }

    /**
     * find enty by tel or email or customer id
     * @author Alex Vait <vait@lieferando.de>
     * @since 18.01.2012
     * @param string $email
     * @param string $tel
     * @param string $customerId
     * @return array
     */
    public static function findByEmailOrTelOrCustomerOrVerificationcode($email = null, $tel = null, $customerId = null, $verificationCode = null) {
        $row = Yourdelivery_Model_DbTable_RabattCheck::findByEmailOrTelOrCustomerOrVerificationcode($email, $tel, $customerId, $verificationCode);

        if (intval($row) != 0) {
            try {
                $rabattCheck = new Yourdelivery_Model_Rabatt_Check($row['id']);
                return $rabattCheck;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }

        return null;
    }

    /**
     * get corresponding discount code
     * @author Alex Vait <vait@lieferando.de>
     * @since 18.01.2012
     * @return Yourdelivery_Model_Rabatt_Code
     */
    public function getRabattcode() {
        if ($this->getRabattCodeId() == 0) {
            return null;
        }

        try {
            $rabattcode = new Yourdelivery_Model_Rabatt_Code(null, $this->getRabattCodeId());
            return $rabattcode;
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            
        }

        return null;
    }

    /**
     * get customer who was registered with this code
     * @author Alex Vait <vait@lieferando.de>
     * @since 19.01.2012
     * @return Yourdelivery_Model_Customer
     */
    public function getCustomer() {
        if ($this->getCustomerId() == 0) {
            return null;
        }

        try {
            $customer = new Yourdelivery_Model_Customer($this->getCustomerId());
            return $customer;
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            
        }

        return null;
    }

    /**
     * Save email in db and in storage
     * @author Alex Vait <vait@lieferando.de>
     * @since 26.01.2012
     */
    protected function saveEmail($receiver, $link, $transactionResult) {
        //$email = new Yourdelivery_Sender_Email_Template('discountemail');
        $email = new Yourdelivery_Sender_Email();
        $email->addTo($receiver)
                ->setSubject(__('Verifizierung der Email Adresse'))
                ->setBodyText(__('Email Adresse %s wurde bestätigt mit dem Link "%s". Transaktionsantwort: "%s"', $receiver, $link, $transactionResult));
        $email->save();
    }

}
