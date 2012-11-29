<?php

class Yourdelivery_Form_User_AllSettings extends Default_Forms_Base {
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
        $config = Zend_Registry::get('configuration');
        $minHouseNoLength = $config->locale->housenumber->min;
        $maxHouseNoLength = $config->locale->housenumber->max;
        
        $request = Zend_Controller_Front::getInstance()->getRequest();

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
        
        $this->addElement('text', 'hausnr', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'StringLength', 'options' => array($minHouseNoLength,$maxHouseNoLength))
            )
        ));
        $this->getElement('hausnr')->getValidator('StringLength')
            ->setMessage(__('Die Hausnummer ist zu lang. (max. %d Zeichen)', $minHouseNoLength), Zend_Validate_StringLength::TOO_LONG);
        $this->getElement('hausnr')->getValidator('StringLength')
            ->setMessage(__('Die Hausnummer ist zu kurz. (min. %d Zeichen)', $minHouseNoLength), Zend_Validate_StringLength::TOO_SHORT);
        $this->getElement('hausnr')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib eine Hausnummer ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
        // city id
        $this->addElement('text', 'cityId', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'Db_RecordExists', 'options' => array('city', 'id'))
            )
        ));
        $this->getElement('cityId')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib eine korrekte Postleitzahl ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('cityId')->getValidator('Db_RecordExists')
                ->setMessage(__('Bitte gib eine korrekte Postleitzahl ein.'), Zend_Validate_Db_NoRecordExists::ERROR_NO_RECORD_FOUND);

        // plz
        $this->addElement('text', 'plz', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('plz')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib eine korrekte Postleitzahl ein.'), Zend_Validate_NotEmpty::IS_EMPTY);

        // street
        $this->addElement('text', 'street', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('street')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib eine Straße ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
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