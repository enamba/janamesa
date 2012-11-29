<?php

class Yourdelivery_Form_User_Settings extends Default_Forms_Base {
    public function isValid($data) {
        $check = parent::isValid($data);
        if ($check) {
            // checking whether e-mail address is blacklisted or it is a 10 minute mail
            $checkedFields = array(
                // TODO: extend if necessary
                'email'
            );
            $blacklistKey = Default_Helpers_Fraud_Customer::multidetect(
                array_intersect_key($data, array_flip($checkedFields))
            );
            if ($blacklistKey == Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL) {
                // entire e-mail blacklisted
                $this->setErrors(array(
                    __('Deine E-Mail-Adresse konnte nicht verifiziert werden.')
                ));
                return false;
            } elseif ($blacklistKey == Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL_MINUTEMAILER) {
                // minutemailer domain
                $this->setErrors(array(
                    __('Bitte gib eine gültige E-Mail-Adresse an. Wegwerf-E-Mail-Adressen dürfen nicht verwendet werden.')
                ));
                return false;
            }
        }
        return $check;
    }

    public function init() {
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $this->addElement('password', 'newpw', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'StringLength', 'options' => array(5, 20)),
                array(
                    'validator' => 'Identical',
                    'options' => array($request->getParam('newpwagain', ''))
                )
            )
        ));

        $this->addElement('password', 'newpwagain', array(
            'filters' => array('StringTrim'),
        ));

        $this->getElement('newpw')->getValidator('StringLength')
                ->setMessage('<br />Ihr Passwort ist zu lang. (max. 20 Zeichen)', Zend_Validate_StringLength::TOO_LONG);
        $this->getElement('newpw')->getValidator('StringLength')
                ->setMessage('<br />Ihr Passwort ist zu kurz. (mind. 5 Zeichen)', Zend_Validate_StringLength::TOO_SHORT);
        $this->getElement('newpw')->getValidator('NotEmpty')
                ->setMessage('<br />Passwort darf nicht leer sein.', Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('newpw')->getValidator('Identical')
                ->setMessage('<br />Die Passwörter stimmen nicht überein.', Zend_Validate_Identical::NOT_SAME);


        $this->addElement('text', 'tel', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
            )
        ));

        $this->addElement('select', 'sex', array(
            'registerInArrayValidator' => false
        ));

        $this->addElement('checkbox', 'newsletter', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('text', 'prename', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                ))
        );
        $this->getElement('prename')->getValidator('NotEmpty')
                ->setMessage('<br />Feld Vorname darf nicht leer sein', Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                ))
        );
        $this->getElement('name')->getValidator('NotEmpty')
                ->setMessage('<br />Feld Nachname darf nicht leer sein', Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'birthday_day', array(
            'required' => false,
            'registerInArrayValidator' => false
        ));
        $this->addElement('text', 'birthday_month', array(
            'required' => false,
            'registerInArrayValidator' => false
        ));
        $this->addElement('text', 'birthday_year', array(
            'required' => false,
            'registerInArrayValidator' => false
        ));
    }

    public function initEmail($customerId) {

        $db = Zend_Registry::get('dbAdapter');

        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                'EmailAddress',
                array('validator' => 'Db_NoRecordExists', 'options' => array(
                        'table' => 'customers',
                        'field' => 'email',
                        'exclude' => $db->quoteInto("deleted = 0 AND id != ?", $customerId)
                    )
                )
            )
        ));


        $this->getElement('email')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('email')->getValidator('Db_NoRecordExists')
                ->setMessage(__('Diese E-Mail-Adresse existiert bereits.'), Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Bitte geben Sie eine gültige E-Mail-Adresse ein.'), Zend_Validate_EmailAddress::INVALID);
    }

}