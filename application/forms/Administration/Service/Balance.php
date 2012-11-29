<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Balance
 *
 * @author matthiaslaug
 */
class Yourdelivery_Form_Administration_Service_Balance extends Default_Forms_Base {

    public function init() {
        
        $this->addElement('text', 'restaurantId', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Kein Restaurant wurde definiert"))
                )
            )
        ));
        
        $this->addElement('text', 'sign', array(
            'required' => true,
            'filters' => array('StringTrim')
        ));
        
        
        $this->addElement('text', 'comment', array(
            'required' => true,
            'filters' => array('StringTrim')
        ));
        
        $this->addElement('text', 'amount', array(
            'required' => true,
            'filters' => array('Digits'),
            'validators' => array(
                array('NotEmpty', false, array(
                        'messages' => __b("Kein Betrag wurde angegeben"))
                )
            )
        ));
        
        $this->addElement('text', 'restaurantId', array(
            'required' => false,
            'filters' => array('StringTrim')
        ));
    }

}

?>
