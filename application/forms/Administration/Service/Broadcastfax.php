<?php

class Yourdelivery_Form_Administration_Service_Broadcastfax extends Default_Forms_Base {
    public function init() {
        $this->addElement('file', 'pdf', array(
            'validators'    => array(
                array('validator' => 'Count', 'options' => array(false,1)),
                array('validator' => 'Size', 'options' => array(false, 10024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'jpg,pdf'))
            )
        ));

       $this->addElement('text', 'sendto', array(
            'filters'    => array('StringTrim')
        ));
    }
}