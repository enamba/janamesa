<?php

/**
 * Edit picture for satellite
 * @author alex
 * @since 02.05.2011
 */
class Yourdelivery_Form_Administration_Satellite_Editpicture extends Default_Forms_Base {

    public function init() {
        $this->addElement('file', '_picture', array(
            'validators' => array(
                array('validator' => 'Count', 'options' => array(false, 1)),
                array('validator' => 'Size', 'options' => array(false, 1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'jpg,png,gif'))
            )
        ));

        $this->addElement('text', 'description', array(
            'filters' => array('StringTrim')
        ));
    }

}