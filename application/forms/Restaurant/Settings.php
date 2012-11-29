<?php

class Yourdelivery_Form_Restaurant_Settings extends Default_Forms_Base {


    public function init() {

        $this->addElement('text', 'name', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'categoryId', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'Db_RecordExists', 'options' => array('restaurant_categories','id'))
            )
        ));
    
        $this->addElement('text', 'description', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'specialComment', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'statecomment', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'plz', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'street', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'hausnr', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
    
        $this->addElement('text', 'notify', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'tel', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'fax', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'email', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                'EmailAddress'
            )
        ));

        $this->addElement('text', 'ktoNr', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'ktoBlz', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'ktoName', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('checkbox', 'debit', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('text', 'isOnline', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'sodexo', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('checkbox', 'express', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'breakfast', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'bio', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', '24h', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'onlyPickup', array(
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('file', 'img', array(
            'validators'    => array(
                array('validator' => 'Count', 'options' => array(false,1)),
                array('validator' => 'Size', 'options' => array(false, 1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'jpg,png,gif'))
            )
        ));
    }
}