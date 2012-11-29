<?php

/**
 * Form for service password
 * @author Alex Vait
 * @since 04.04.2012
 */
class Yourdelivery_Form_Administration_Service_Password extends Default_Forms_Base {

    public function init() {        
        $this->addElement('text', 'password', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie ein Passwort ein"))
                )
            )
        ));        
    }
}
