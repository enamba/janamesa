<?php

class Yourdelivery_Form_Administration_Salesperson_Create extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'name', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'prename', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'email', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'password', array(
        ));

        $this->addElement('text', 'salary', array(
        ));

        $this->addElement('text', 'description', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'callcenter', array(
            'filters'    => array('StringTrim')
        ));
    }
}