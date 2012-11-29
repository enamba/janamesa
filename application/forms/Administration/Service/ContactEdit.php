<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Fotm for editing company contact
 *
 * @author vait
 */
class Yourdelivery_Form_Administration_Service_ContactEdit extends Default_Forms_Base {

    public function init() {

        $this->addElement('text', 'name', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen Namen ein"))
                )
            )
        ));

        $this->addElement('text', 'prename', array(
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('NotEmpty', false, array(
                    'messages' => __b("Bitte geben Sie einen Vornamen ein"))
                )
            )
        ));

        $this->addElement('text', 'street', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'hausnr', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'plz', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'Db_RecordExists', 'options' => array('city','plz'))
            )
        ));

        $this->addElement('text', 'cityId', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));

        $this->addElement('text', 'email', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'EmailAddress'
            )
        ));

        $this->addElement('text', 'tel', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'fax', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->addElement('text', 'position', array(
            'required'   => false,
            'filters'    => array('StringTrim')
        ));
    }
}
