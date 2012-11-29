<?php

/**
 * @author mlaug
 */
class Yourdelivery_Form_Administration_Billing_Customized extends Default_Forms_Base {

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

        $this->addElement('text', 'content', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'preamble', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'reminder', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        
        $this->addElement('text', 'template', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('checkbox', 'verbose',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'showEmployee',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'showCostcenter',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));
        
        $this->addElement('checkbox', 'showProject',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'projectSub',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'costcenterSub',array(
                'checkedValue' => 1,
                'uncheckedValue' => 0
        ));
    }

}