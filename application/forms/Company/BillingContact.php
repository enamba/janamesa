<?php

/**
 * @author mlaug
 */
class Yourdelivery_Form_Company_BillingContact extends Default_Forms_Base {

    /**
     * @author mlaug
     */
    public function init() {

        $this->addElement('text', 'heading', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->addElement('text', 'street', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        $this->addElement('text', 'hausnr', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'plz', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'city', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'addition', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
    }

}