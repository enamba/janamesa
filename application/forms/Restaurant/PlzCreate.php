<?php

class Yourdelivery_Form_Restaurant_PlzCreate extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'cityId', array(
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

        $this->addElement('text', 'mincost', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'delcost', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'noDeliverCostAbove', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'status', array(
            'filters'    => array('StringTrim'),
        ));

        $this->addElement('text', 'comment', array(
            'filters'    => array('StringTrim'),
        ));
    }
}