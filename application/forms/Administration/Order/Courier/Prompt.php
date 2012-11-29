<?php
/**
 * @author vpriem
 * @since 29.09.2010
 */
class Yourdelivery_Form_Administration_Order_Courier_Prompt extends Default_Forms_Base{

    public function init() {

        $this->addElement('text', 'orderId', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));
        
        $this->addElement('text', 'street', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'hausnr', array(
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'plz', array(
            'filters'    => array('StringTrim')
        ));

    }
    
}
