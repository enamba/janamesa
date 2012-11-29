<?php

/**
 * Description of FinishRegistered
 *
 * @author mlaug
 */
class Yourdelivery_Form_Order_Finish_Company extends Yourdelivery_Form_Order_Finish_Abstract {
    
    public function init() {
        
        $this->additional();
        $this->contact();
                   
        $this->addElement('text', 'pnumber', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'new-pnumber', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
        
        $this->addElement('text', 'projectAddition', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'projectAddition2', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));             
        
    }
}
