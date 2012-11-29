<?php

class Yourdelivery_Form_Suggest extends Default_Forms_Base {

    public function init() {
        $this->addElement('text', 'email', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
                array('validator' => 'Db_NoRecordExists', 'options' => array('customers','email'))
            )
        ));

        $this->addElement('text', 'name', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'address', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'phone', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'city', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('textarea', 'descr', array(
            'required'   => true,
            'filters'    => array('StringTrim')
        ));

    }

}