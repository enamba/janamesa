<?php

class Yourdelivery_Form_Registerobstkorb extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'company', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'StringLength', 'options' => array(5, 30))
            )
        ));

        $this->getElement('company')->getValidator('NotEmpty')
            ->setMessage('<br />Der Firmenname darf nicht leer sein', Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('company')->getValidator('StringLength')
            ->setMessage('<br />Der Firmenname ist zu lang. (max. 30 Zeichen)', Zend_Validate_StringLength::TOO_LONG);
        $this->getElement('company')->getValidator('StringLength')
            ->setMessage('<br />Der Firmenname ist zu kurz. (mind. 5 Zeichen)', Zend_Validate_StringLength::TOO_SHORT);
       

        $this->addElement('text', 'name', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->getElement('name')->getValidator('NotEmpty')
            ->setMessage('<br />Bitte geben Sie einen Namen ein.', Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'prename', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('prename')->getValidator('NotEmpty')
            ->setMessage('<br />Bitte geben Sie einen Vornamen ein.', Zend_Validate_NotEmpty::IS_EMPTY);


        $this->addElement('text', 'email', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                'EmailAddress'
            )
        ));

        $this->getElement('email')->getValidator('NotEmpty')
            ->setMessage('<br />Die Email-Adresse darf nicht leer sein', Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('email')->getValidator('EmailAddress')
            ->setMessage('<br />Bitte geben Sie eine gültige Email-Adresse ein.', Zend_Validate_EmailAddress::INVALID);
        $this->getElement('email')->getValidator('EmailAddress')
            ->setMessage('<br />Ungültiges Format der Emailadresse',Zend_Validate_EmailAddress::INVALID_FORMAT);


        $this->addElement('text', 'tel', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'plz', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty')
        ));

        $this->getElement('plz')->getValidator('NotEmpty')
            ->setMessage('<br />Die PLZ darf nicht leer sein', Zend_Validate_NotEmpty::IS_EMPTY);

         $this->addElement('text', 'street', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty')
        ));

         $this->getElement('street')->getValidator('NotEmpty')
            ->setMessage('<br />Die Strasse darf nicht leer sein', Zend_Validate_NotEmpty::IS_EMPTY);

    }

}