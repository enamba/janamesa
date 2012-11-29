<?php

/**
 * Form for service log upload
 * @author Alex Vait <vait@lieferando.de>
 */
class Yourdelivery_Form_Administration_Service_EditLogo extends Default_Forms_Base {

    public function init() {
        $this->addElement('file', 'img', array(
            'validators' => array(
                array('validator' => 'Count', 'options' => array(false, 1)),
                array('validator' => 'Size', 'options' => array(false, 1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'jpg,png,gif'))
            )
        ));
    }
}
