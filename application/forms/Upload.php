<?php

class Yourdelivery_Form_Upload extends Default_Forms_Base {


    public function init() {

        $this->addElement('file', 'file', array(
            'validators'    => array(
                array('validator' => 'Count', 'options' => array(false,1)),
                array('validator' => 'Size', 'options' => array(false, 1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'csv'))
            )
        ));

    }

}