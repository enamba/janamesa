<?php

class Yourdelivery_Form_Administration_User_Addlocation extends Default_Forms_Base {

    public function init() {
        
        $config = Zend_Registry::get('configuration');

        $this->addElement('text', 'street', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine Strasse ein"))
                )
            )
        ));

        $this->addElement('text', 'hausnr', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie eine Hausnummer ein"))
                )
            )
        ));

        // for Brasil we need the plz input, for other domains we need a cityId
        if (strpos($config->domain->base, "janamesa") !== false) { 
            $this->addElement('text', 'plz', array(
                'required' => true,
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
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('NotEmpty', false, array(
                            'messages' => __b("Bitte wählen Sie eine PLZ aus"))
                    )
                )
            ));            
        }

        $this->addElement('text', 'comment', array(
            'filters'    => array('StringTrim'),
        ));
    }
}