<?php

class Yourdelivery_Form_Administration_Adminrights_Create extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'email', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine Email Adresse ein"))
                )
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

        $this->addElement('text', 'password', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie ein Passwort ein"))
                )
            )
        ));
    }
}