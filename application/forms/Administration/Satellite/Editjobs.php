<?php

/**
 * Satellite form
 *
 * @author alex
 * @since 07.06.2011
 */
class Yourdelivery_Form_Administration_Satellite_Editjobs extends Default_Forms_Base {

    public function init() {
        $this->addElement('text', 'id', array(
            'required'   => true,
            'validators' => array(
                array('NotEmpty', true, array('messages' => __b("Satellite Id was not defined! Very strange.")))
            )
        ));

        $this->addElement('text', 'jobText', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Job text was not defined!")))
            )
        ));

        $this->addElement('file', '_jobTextImg', array(
            'validators' => array(
                array('validator' => 'Count', 'options' => array(false, 1)),
                array('validator' => 'Size', 'options' => array(false, 1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'jpg,png,gif'))
            )
        ));
        
        $this->addElement('file', '_jobFormularImg', array(
            'validators' => array(
                array('validator' => 'Count', 'options' => array(false, 1)),
                array('validator' => 'Size', 'options' => array(false, 1024000)),
                array('validator' => 'Extension', 'options' => array(false, false, 'jpg,png,gif'))
            )
        ));
    }

}