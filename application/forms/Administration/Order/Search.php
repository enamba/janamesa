<?php

/**
 * Form for search orders
 * @author Alex Vait <vait@lieferando.de>
 * @since 13.12.2011
 * @see YD-709
 */
class Yourdelivery_Form_Administration_Order_Search extends Default_Forms_Base {

    public function init() {
        
        $this->addElement('text', 'searchfor', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Bitte definieren sie die Suchparameter"))
                )
            )
        ));
        
        $this->addElement('text', 'cancel', array(
            'required' => false,
            'filters' => array('StringTrim'))
        );
        
        $this->addElement('text', 'notify_user', array(
            'required' => false,
            'filters' => array('StringTrim'))
        );
        
        $this->addElement('text', 'notify_restaurant', array(
            'required' => false,
            'filters' => array('StringTrim'))
        );
    }
}
