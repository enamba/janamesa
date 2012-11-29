<?php

class Yourdelivery_Form_Administration_User_Create extends Default_Forms_Base {

    public function init() {
        
        $this->addElement('text', 'email', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine Email Adresse ein"))
                ),
                'EmailAddress'
            )
        ));

        $this->addElement('text', 'name', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen Namen ein"))
                )
            )
        ));

        $this->addElement('text', 'prename', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen Vornamen ein"))
                )
            )
        ));

        $this->addElement('text', 'tel', array(
            'filters'    => array('StringTrim'),
        ));        

        $this->addElement('text', 'sex', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('password', 'password', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen Passwort ein"))
                )
            )
        ));

        $this->addElement('text', 'street', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'hausnr', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'cityId', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte wählen Sie eine PLZ aus"))
                )
            )
        ));
        
        $this->addElement('text', 'comment', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'company', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'company_admin', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'service_admin', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'discount', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'budgetId', array(
            'filters'    => array('StringTrim')
        ));
    }
}