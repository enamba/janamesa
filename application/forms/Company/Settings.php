<?php

class Yourdelivery_Form_Company_Settings extends Default_Forms_Base {


    public function init() {

        $this->addElement('text', 'cstreet', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('cstreet')->getValidator('NotEmpty')
            ->setMessage('<br />Bitte geben Sie eine Straße ein.', Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'chausnr', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'StringLength', 'options' => array(1, 5))
            )
        ));
        $this->getElement('chausnr')->getValidator('NotEmpty')
            ->setMessage('<br />Bitte geben Sie eine Hausnummer ein.', Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('chausnr')->getValidator('StringLength')
            ->setMessage('<br />Die Hausnummer ist zu lang.', Zend_Validate_StringLength::TOO_LONG);

        $this->addElement('text', 'cplz', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty')
        ));
        $this->getElement('cplz')->getValidator('NotEmpty')
            ->setMessage('<br />Bitte geben Sie eine Postleitzahl ein.', Zend_Validate_NotEmpty::IS_EMPTY);
       

        $this->addElement('hidden', 'cityId', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty')

        ));
        $this->getElement('cityId')->getValidator('NotEmpty')
            ->setMessage('<br />Bitte geben Sie eine Postleitzahl ein.', Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'csteuerNr', array(
            'filters'    => array('StringTrim'),
        ));
    
        $this->addElement('text', 'cindustry', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'cwebsite', array(
            'filters'    => array('StringTrim'),
        ));

        /*
        $this->addElement('select', 'billInform', array(
            'registerInArrayValidator'    => false
        ));
         * 
         */

        $this->addElement('text', 'cktoName', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'cktoNr', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'cktoBlz', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('checkbox', 'informAdmin',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));

    }

}