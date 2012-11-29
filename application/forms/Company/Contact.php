<?php

class Yourdelivery_Form_Company_Contact extends Default_Forms_Base {

    public function init() {

        /**
         * edit contact
         */
        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'validators' => array('NotEmpty'),
        ));

        $this->getElement('name')->getValidator('NotEmpty')
                ->setMessage('<br />Bitte geben Sie einen Namen ein.', Zend_Validate_NotEmpty::IS_EMPTY);


        $this->addElement('text', 'prename', array(
            'filters' => array('StringTrim'),
            'validators' => array('NotEmpty'),
        ));

        $this->getElement('prename')->getValidator('NotEmpty')
                ->setMessage('<br />Bitte geben Sie einen Nachnamen ein.', Zend_Validate_NotEmpty::IS_EMPTY);


        $this->addElement('text', 'position', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'street', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'hausnr', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'cityId', array(
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'plz', array(
            'filters' => array('StringTrim'),
        )); 

        $this->addElement('text', 'tel', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'fax', array(
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array('NotEmpty', 'EmailAddress'),
        ));

        $this->getElement('email')->getValidator('NotEmpty')
                ->setMessage('<br />Bitte geben Sie eine Emailadresse ein.', Zend_Validate_NotEmpty::IS_EMPTY);

        $this->getElement('email')->getValidator('EmailAddress')
                ->setMessage('<br />Bitte geben Sie eine gültige Emailadresse ein.', Zend_Validate_EmailAddress::INVALID);
    }

}