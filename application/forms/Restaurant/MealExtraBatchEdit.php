<?php

class Yourdelivery_Form_Restaurant_MealExtraBatchEdit extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'names', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'status', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'groupId', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
    
        $this->addElement('text', 'mwst', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
    }
}