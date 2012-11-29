<?php

class Yourdelivery_Form_Company_EditEmployee extends Default_Forms_Base {

    
    public function init() {

        $this->addElement('text', 'name', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'prename', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'email', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
                array('validator' => 'Db_NoRecordExists', 'options' => array('customers','email'))
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

        $this->addElement('text', 'newpass', array(
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

        $this->addElement('checkbox', 'allowCater',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'allowGreat',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'allowAlcohol',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'allowTabaco',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));

        $this->addElement('radio', 'admin',array(
            'registerInArrayValidator'    => false
        ));

        $this->addElement('select', 'budget', array(
            'registerInArrayValidator'    => false
        ));
 
    }

}