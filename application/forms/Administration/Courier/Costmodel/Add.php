<?php
/**
 * @author vpriem
 * @since 31.08.2010
 */
class Yourdelivery_Form_Administration_Courier_Costmodel_Add extends Default_Forms_Base{

    /**
     * Initialize form
     * @author vpriem
     * @since 31.08.2010
     * @return void
     */
    public function init(){

        $this->addElement('text', 'courierId', array(
            'required'   => true,
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'startCost', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'kmInclusive', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'kmCost', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'tax', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'taxInclusive', array(
            'filters'    => array('StringTrim'),
        ));

    }
    
}