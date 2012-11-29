<?php

class Yourdelivery_Form_Administration_Salesperson_Contract_Edit extends Default_Forms_Base {

    public function init() {
        $this->addElement('text', 'restaurantId', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'affirmed', array(
        ));

        $this->addElement('checkbox', 'restaurantCalled',array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('text', 'salespersonId', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
    }
}