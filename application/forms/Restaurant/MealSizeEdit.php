<?php

class Yourdelivery_Form_Restaurant_MealSizeEdit extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'name', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'status', array(
            'filters'    => array('StringTrim'),
        ));
    }
}