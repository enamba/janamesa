<?php

/**
 * Form validation for pyszne plz feature
 *
 * @author mlaug
 */
class Yourdelivery_Form_Order_Start_Citystreet extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'city', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'Db_RecordExists', 'options' => array('city', 'city'))
            )
        ));
        
        $this->addElement('text', 'street', array(
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'Db_RecordExists', 'options' => array('city_verbose', 'street'))
            )
        ));

        $this->addElement('text', 'hausnr', array(
            'required' => false,
            'filters' => array('StringTrim'),
        ));
    }

}
