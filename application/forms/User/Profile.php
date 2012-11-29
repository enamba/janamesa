<?php

class Yourdelivery_Form_User_Profile extends Default_Forms_Base {


    public function init() {

        $this->addElement('file', 'img', array(
            'validators'    => array(
                array('validator' => 'Count', 'options' => array(false,1)),
                array('validator' => 'Size', 'options' => array(false, 5*1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'jpg,jpeg,png,gif'))
            )
        ));

    }

}