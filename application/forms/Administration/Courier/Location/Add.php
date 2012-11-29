<?php
/**
 * @author vpriem
 * @since 10.08.2010
 */
class Yourdelivery_Form_Administration_Courier_Location_Add extends Default_Forms_Base{

    /**
     * Initialize form
     * @author vpriem
     * @since 10.08.2010
     * @return void
     */
    public function init(){

        $this->addElement('text', 'courierId', array(
            'required'   => true,
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'range', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'cost', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'deliverTime', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

    }
    
}