<?php
/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 02.05.2011
 */
class Yourdelivery_Form_Info_ContactCompany extends Default_Forms_Base {

    public function init() {
        $this->addElement('text', 'name', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'StringLength', 'options' => array(5, 50))
            )
        ));
        $this->getElement('name')->getValidator('NotEmpty')
                ->setMessage('Name darf nicht leer sein.', Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('name')->getValidator('StringLength')
                ->setMessage('Der Name ist zu kurz. (mind. 5 Zeichen)', Zend_Validate_StringLength::TOO_SHORT);
        $this->getElement('name')->getValidator('StringLength')
                ->setMessage('Der Name ist zu lang. (max. 50 Zeichen)', Zend_Validate_StringLength::TOO_LONG);


        $this->addElement('text', 'email', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                'EmailAddress',
            )
        ));

        $this->getElement('email')->getValidator('NotEmpty')
                ->setMessage('Email darf nicht leer sein.', Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage('Keine gültige Email-Adresse.', Zend_Validate_EmailAddress::INVALID);
        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage('Ungültiges Email-Format', Zend_Validate_EmailAddress::INVALID_FORMAT);


        $this->addElement('text', 'msg', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('msg')->getValidator('NotEmpty')
                ->setMessage('Bitte geben Sie eine Nachricht ein.', Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'tel', array(
            'required' => false
        ));

        $this->addElement('text', 'comp', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('comp')->getValidator('NotEmpty')
                ->setMessage('Bitte geben Sie einen Firmennamen ein.', Zend_Validate_NotEmpty::IS_EMPTY);


        $this->addElement('text', 'ort', array(
            'required' => false
        ));
    }

}