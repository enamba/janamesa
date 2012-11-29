<?php

class Yourdelivery_Form_Administration_User_Edit extends Default_Forms_Base {

    public function init() {
        
        parent::initName();
        parent::initPrename();
        

        $this->addElement('text', 'birthday', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie ein Geburtsdatum ein"))
                )
            )
        ));

        $this->addElement('text', 'tel', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'sex', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'newpass', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
            )
        ));
        
        $this->addElement('checkbox', 'whitelist', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));
    }

}