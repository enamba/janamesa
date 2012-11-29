<?php

class Yourdelivery_Form_Restaurant_MealCreate extends Default_Forms_Base {

    public function init() {
        $this->addElement('text', 'categoryId', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'name', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'minAmount', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'mwst', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'minNr', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'description', array(
            'filters'    => array('StringTrim'),
        ));


        $this->addElement('text', 'tags', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'nr', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'status', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'vegetarian', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'bio', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'tabaco', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'excludeFromMinCost', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'priceType', array(
            'filters'    => array('StringTrim'),
        ));
    }
}