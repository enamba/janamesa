<?php

class Yourdelivery_Form_Administration_Service_Upload extends Default_Forms_Base {
    public function init() {
        $this->addElement('file', 'document', array(
            'validators'    => array(
                array('validator' => 'Count', 'options' => array(false,1)),
                array('validator' => 'Size', 'options' => array(false, 20024000))
            )
        ));

        $this->addElement('text', 'alternativeName', array(
            'filters'    => array('StringTrim'),
        ));
    }
}