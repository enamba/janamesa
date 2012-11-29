<?php

class Yourdelivery_Form_Register extends Default_Forms_Base {

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
        // sex
        $this->addElement('select', 'sex', array(
            'registerInArrayValidator' => false
        ));

        // prename
        $this->addElement('text', 'prename', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('prename')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib Deinen Vornamen ein.'), Zend_Validate_NotEmpty::IS_EMPTY);

        // name
        $this->addElement('text', 'name', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('name')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib Deinen Namen ein.'), Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'birthday', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('birthday')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib Dein Geburtsdatum ein.'), Zend_Validate_NotEmpty::IS_EMPTY);

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

        // tel
        $this->addElement('text', 'tel', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('tel')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib Deine Telefonnummer ein. Sie wird für evtl. Rückfragen benötigt.'), Zend_Validate_NotEmpty::IS_EMPTY);

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

        // email
        $this->addElement('text', 'email', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                'EmailAddress',
                array('validator' => 'Db_NoRecordExists', 'options' => array(
                        'table' => 'customers',
                        'field' => 'email',
                        'exclude' => "deleted = 0"
                    )
                )
            )
        ));
        $this->getElement('email')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib eine E-Mail-Adresse ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('email')->getValidator('Db_NoRecordExists')
                ->setMessage(__('Diese E-Mail-Adresse ist bereits registriert.'), Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);
        $this->getElement('email')->setErrorMessages(array(__('Bitte gib eine gültige E-Mail-Adresse ein.')));

        // password
        $this->addElement('password', 'password', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'StringLength', 'options' => array(5, 20))
            )
        ));
        $this->getElement('password')->getValidator('StringLength')
                ->setMessage(__('Dein Passwort ist zu lang. (max. %d Zeichen)',20), Zend_Validate_StringLength::TOO_LONG);
        $this->getElement('password')->getValidator('StringLength')
                ->setMessage(__('Dein Passwort ist zu kurz. (mind. %d Zeichen)',5), Zend_Validate_StringLength::TOO_SHORT);
        $this->getElement('password')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib ein Passwort ein.'), Zend_Validate_NotEmpty::IS_EMPTY);

        // company
        $this->addElement('text', 'companyName', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        // etage
        $this->addElement('text', 'etage', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        // comment
        $this->addElement('text', 'comment', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        // agb
        $this->addElement('checkbox', 'agb', array(
            'required' => true,
            'checkedValue' => 1,
            'validators' => array(
                array('validator' => 'GreaterThan', 'options' => array(0))
            )
        ));
        $this->getElement('agb')->setErrorMessages(array(
            Zend_Validate_NotEmpty::IS_EMPTY =>
            __('Bitte akzeptieren Sie unsere AGBs!')
        ));

        // neukundenaktion
        $this->addElement('checkbox', 'newcustomer', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));
    }

}
