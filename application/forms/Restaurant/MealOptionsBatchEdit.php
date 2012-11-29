<?php

class Yourdelivery_Form_Restaurant_MealOptionsBatchEdit extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'names', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'cost', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'mwst', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'optRow', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'status', array(
            'filters'    => array('StringTrim'),
        ));
    }
}