<?php

class Yourdelivery_Form_Restaurant_MealCategoryEdit extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'name', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'description', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'mwst', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('checkbox', 'excludeFromMinCost',array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'hasPfand',array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'main',array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));
        
        $this->addElement('text', 'top', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'from', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'to', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'parentMealCategoryId', array(
            'filters'    => array('StringTrim'),
        ));
    }
}