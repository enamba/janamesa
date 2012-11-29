<?php

class Yourdelivery_Form_RegisterDiscount extends Default_Forms_Base {

    public function init() {


        // prename
        $this->addElement('text', 'prename', array(
            'required' => true,
            'filters' => array(
                'StringTrim',
                array('filter' => 'PregReplace', 'options' => array('match' => "/" . __('Vorname') . "/", 'replace' => ''))
            ),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'StringLength', 'options' => array(3, 50)),
            )
        ));
        $this->getElement('prename')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib einen Vornamen ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('prename')->getValidator('StringLength')
                ->setMessage(__('Der Vorname ist zu kurz. (mind. 3 Zeichen)'), Zend_Validate_StringLength::TOO_SHORT);
        $this->getElement('prename')->getValidator('StringLength')
                ->setMessage(__('Der Vorname ist zu lang. (max. 50 Zeichen)'), Zend_Validate_StringLength::TOO_LONG);

        // name
        $this->addElement('text', 'name', array(
            'required' => true,
            'filters' => array(
                'StringTrim',
                array('filter' => 'PregReplace', 'options' => array('match' => "/" . __('Nachname') . "/", 'replace' => ''))
            ),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'StringLength', 'options' => array(3, 50)),
            )
        ));
        $this->getElement('name')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib einen Namen ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('name')->getValidator('StringLength')
                ->setMessage(__('Der Name ist zu kurz. (mind. 3 Zeichen)'), Zend_Validate_StringLength::TOO_SHORT);
        $this->getElement('name')->getValidator('StringLength')
                ->setMessage(__('Der Name ist zu lang. (max. 50 Zeichen)'), Zend_Validate_StringLength::TOO_LONG);


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
                ),
                new Default_Forms_Validate_EmailBlacklisted()
            )
        ));
        $this->getElement('email')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_EmailAddress::INVALID);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_EmailAddress::INVALID_FORMAT);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_EmailAddress::INVALID_HOSTNAME);
        $this->getElement('email')->getValidator('Db_NoRecordExists')
                ->setMessage(__('Deine E-Mail-Adresse konnte nicht verifiziert werden'), Zend_Validate_Db_RecordExists::ERROR_RECORD_FOUND);
        $this->getElement('email')->getValidator('EmailBlacklisted')
                ->setMessage(__('Deine E-Mail-Adresse konnte nicht verifiziert werden'), Default_Forms_Validate_EmailBlacklisted::BLACKLISTED);
    }

}
