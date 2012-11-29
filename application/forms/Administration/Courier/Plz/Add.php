<?php
/**
 * @author vpriem
 * @since 31.08.2010
 */
class Yourdelivery_Form_Administration_Courier_Plz_Add extends Default_Forms_Base{

    /**
     * Initialize form
     * @author vpriem
     * @since 31.08.2010
     * @return void
     */
    public function init(){

        $this->addElement('text', 'cid', array(
            'required'   => true,
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'cityId', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'city', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'deliverTime', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'delcost', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'mincost', array(
            'filters'    => array('StringTrim'),
        ));

    }
    
}