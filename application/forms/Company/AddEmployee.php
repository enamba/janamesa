<?php

class Yourdelivery_Form_Company_AddEmployee extends Default_Forms_Base {

    
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
                'EmailAddress'
            )
        ));

        $this->addElement('text', 'personalnumber', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
        
        $this->addElement('text', 'tel', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'costcenter', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('checkbox', 'notify',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'admin',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'cater',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'great',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));

        $this->addElement('select', 'budget', array(
            'registerInArrayValidator'    => false
        ));
 
    }

}