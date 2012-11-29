<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 20.12.2011
 */
class Yourdelivery_Form_Api_UserRegister extends Yourdelivery_Form_Register {

    /**
     * init form to register from parent
     * exclude some fields which are not used in the register-process via api
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.12.2011
     */
    public function init() {
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
                ->setMessage(__('Bitte gib eine E-Mail-Adresse ein.'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('email')->getValidator('Db_NoRecordExists')
                ->setMessage(__('Diese E-Mail-Adresse ist bereits registriert.'), Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);
        $this->getElement('email')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_EmailAddress::INVALID);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_EmailAddress::INVALID_FORMAT);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_EmailAddress::INVALID_HOSTNAME);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_EmailAddress::INVALID_LOCAL_PART);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage(__('Bitte gib eine gültige E-Mail-Adresse ein'), Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED);
        $this->getElement('email')->getValidator('EmailBlacklisted')
                ->setMessage(__('Deine E-Mail-Adresse konnte nicht verifiziert werden.'), Default_Forms_Validate_EmailBlacklisted::BLACKLISTED);

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
                ->setMessage(__('Dein Passwort ist zu lang. (max. %d Zeichen)', 20), Zend_Validate_StringLength::TOO_LONG);
        $this->getElement('password')->getValidator('StringLength')
                ->setMessage(__('Dein Passwort ist zu kurz. (mind. %d Zeichen)', 5), Zend_Validate_StringLength::TOO_SHORT);
        $this->getElement('password')->getValidator('NotEmpty')
                ->setMessage(__('Bitte gib ein Passwort ein.'), Zend_Validate_NotEmpty::IS_EMPTY);

        parent::initTel(true, true, true);
    }

}
