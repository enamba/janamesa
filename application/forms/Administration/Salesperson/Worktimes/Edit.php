<?php

class Yourdelivery_Form_Administration_Salesperson_Worktimes_Edit extends Default_Forms_Base {

    public function init() {
        $this->addElement('text', 'day', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'hours', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

    }
}