<?php

class Yourdelivery_Form_Administration_Contact_Edit extends Default_Forms_Base {

    public function init() {
        
        $config = Zend_Registry::get('configuration');

        $this->addElement('text', 'name', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen Namen ein"))
                )
            )
        ));

        $this->addElement('text', 'prename', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen Vornamen ein"))
                )
            )
        ));

        $this->addElement('text', 'email', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                'EmailAddress'
            )
        ));

        $this->addElement('text', 'street', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'hausnr', array(
            'filters'    => array('StringTrim')
        ));

        // for Brasil we need the plz input, for other domains we need a cityId
        if (strpos($config->domain->base, "janamesa") !== false) {        
            $this->addElement('text', 'plz', array(
                'required' => false,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('NotEmpty', false, array(
                            'messages' => __b("Bitte geben Sie eine PLZ ein"))
                    )
                )
            ));
        }
        else {
            $this->addElement('text', 'cityId', array(
                'required' => false,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('NotEmpty', false, array(
                            'messages' => __b("Bitte wählen Sie eine PLZ aus"))
                    )
                )
            ));            
        }
        
        $this->addElement('text', 'tel', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'fax', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'position', array(
            'filters'    => array('StringTrim')
        ));
    }

}