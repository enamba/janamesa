<?php

class Yourdelivery_Form_Company_Address extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'street', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('street')->getValidator('NotEmpty')
                ->setMessage('<br />Bitte geben Sie eine Straße ein.', Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'hausnr', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'StringLength', 'options' => array(1, 5))
            )
        ));
        $this->getElement('hausnr')->getValidator('StringLength')
                ->setMessage('<br />Die HausNr ist zu lang. (max. 5 Zeichen)', Zend_Validate_StringLength::TOO_LONG);
        $this->getElement('hausnr')->getValidator('StringLength')
                ->setMessage(null, Zend_Validate_StringLength::TOO_SHORT);
        $this->getElement('hausnr')->getValidator('NotEmpty')
                ->setMessage('<br />Bitte geben Sie eine Hausnummer ein.', Zend_Validate_NotEmpty::IS_EMPTY);



        $this->addElement('text', 'cityId', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'Db_RecordExists', 'options' => array('city', 'id'))
            )
        ));
        $this->getElement('cityId')->getValidator('Db_RecordExists')
                ->setMessage('<br />Diese PLZ ist nicht korrekt.', Zend_Validate_Db_NoRecordExists::ERROR_NO_RECORD_FOUND);
        $this->getElement('cityId')->getValidator('NotEmpty')
                ->setMessage('<br />Diese PLZ ist nicht korrekt.', Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'plz', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('plz')->getValidator('NotEmpty')
                ->setMessage('<br />Diese PLZ ist nicht korrekt.', Zend_Validate_NotEmpty::IS_EMPTY);


        $this->addElement('text', 'tel', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->getElement('tel')->getValidator('NotEmpty')
                ->setMessage('<br />Bitte geben Sie eine Telefonnummer ein.', Zend_Validate_NotEmpty::IS_EMPTY);

        $this->addElement('text', 'etage', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('text', 'comment', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('multiCheckbox', 'budgets', array(
            'registerInArrayValidator' => false,
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));
    }

}